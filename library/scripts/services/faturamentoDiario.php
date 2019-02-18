<?php
    namespace Library\Scripts\Services;

    /** Inicializando manualmente a variável de sessão com ROOT do projeto (para execuções via CLI) */
    $_SERVER['DOCUMENT_ROOT'] = dirname(dirname(dirname(__DIR__)));

    date_default_timezone_set('America/sao_paulo');
    setlocale(LC_ALL, "ptb");
    header('Content-Type: text/html; charset=utf-8');

    include $_SERVER['DOCUMENT_ROOT'] . '/library/classes/PHPMailer/PHPMailerAutoload.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/autoloader.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/startSession.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptSap.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptMSSQL.php';

    use Library\Classes\KeyDictionary as DD;
    use DateTime;
    use DateInterval;
    use DatePeriod;

    $hoUtils = new \Library\Classes\Utils();

    /**
     * Busca o valor de faturamento do dia atual e os últimos 7 para montagem do e-mail
     */
    $ano = date('Y'); $mes = date('m');
    $dia = date('d');

    $hoje = new DateTime($hoje);
    

    $firstDay = $hoUtils->firstDayOfMonth($ano, $mes);

    //$fatHoje  = $dbcDB2->faturamentoPeriodo($hoje->format('Y-m-d'), $hoje->format('Y-m-d'));
    $fatHoje = receitaFretePeso($ano, $mes, $dia);
    //$fatMes   = $dbcDB2->faturamentoPeriodo($firstDay, $hoje->format('Y-m-d'));
    $fatMes = receitaFretePeso($ano, $mes);
    $mediaMes = $fatMes / $hoje->format('d');
    //$prevAno  = $dbcDB2->receitaPrevisto($ano);
    $prevMes  = receitaPrevistoOperadorLogistico($ano, $mes);
    $prcMes   = $hoUtils->numberFormat(($fatMes / $prevMes) * 100, 0, 0);

    /** Por tipo de contrato */
    //$fatFrtHoje = $dbcDB2->faturamentoContrato(DD::TP_CONTRATO_FROTA,    $hoje->format('Y-m-d'), $hoje->format('Y-m-d'));
    $fatFrtHoje = receitaFretePeso($ano, $mes, $dia, 'F');
    //$fatAgrHoje = $dbcDB2->faturamentoContrato(DD::TP_CONTRATO_AGREGADO, $hoje->format('Y-m-d'), $hoje->format('Y-m-d'));
    $fatAgrHoje = receitaFretePeso($ano, $mes, $dia, 'A');
    //$fatTerHoje = $dbcDB2->faturamentoContrato(DD::TP_CONTRATO_TERCEIRO, $hoje->format('Y-m-d'), $hoje->format('Y-m-d'));
    $fatTerHoje = receitaFretePeso($ano, $mes, $dia, 'T');

    //$fatFrtMes = $dbcDB2->faturamentoContrato(DD::TP_CONTRATO_FROTA,    $firstDay, $hoje->format('Y-m-d'));
    $fatFrtMes = receitaFretePeso($ano, $mes, null, 'F');
    //$fatAgrMes = $dbcDB2->faturamentoContrato(DD::TP_CONTRATO_AGREGADO, $firstDay, $hoje->format('Y-m-d'));
    $fatAgrMes = receitaFretePeso($ano, $mes, null, 'A');
    //$fatTerMes = $dbcDB2->faturamentoContrato(DD::TP_CONTRATO_TERCEIRO, $firstDay, $hoje->format('Y-m-d'));
    $fatTerMes = receitaFretePeso($ano, $mes, null, 'T');

    $prcFrtHoje = ($fatFrtHoje / $fatHoje) * 100; $prcFrtMes = ($fatFrtMes / $fatMes) * 100;
    $prcAgrHoje = ($fatAgrHoje / $fatHoje) * 100; $prcAgrMes = ($fatAgrMes / $fatMes) * 100;
    $prcTerHoje = ($fatTerHoje / $fatHoje) * 100; $prcTerMes = ($fatTerMes / $fatMes) * 100;

    /** Prepara as datas para loop da busca (período dos últimos 7 dias) */
    $dtAux = strtotime($hoje->format('Y-m-d'));
    $dtAux = strtotime("-7 day", $dtAux); // Subtrai 7 dias

    $inicio = new DateTime(date('Y-m-d', $dtAux));

    $dtAux = strtotime($hoje->format('Y-m-d'));
    $dtAux = strtotime("-1 day", $dtAux);

    $fim = new DateTime(date('Y-m-d', $dtAux));
    $fim->setTime(23, 59, 59); // Necessário para corrigir 'bug' do PHP

    $intervalo = DateInterval::createFromDateString('1 day');
    $periodo   = new DatePeriod($inicio, $intervalo, $fim); // Cria um DateInterval de 8 dias atrás até ontem

    /** Inverte a ordem do período criado para que seja escrito do dia anterior para trás, e não o contrário */
    $reverse = array();
    foreach ($periodo as $dt) $reverse[] = $dt->format('Y-m-d');
    $datas = array_reverse($reverse);

    foreach ($datas as $dia){
        $data = $hoUtils->dateFormat($dia, 'Y-m-d', 'd/m/Y') . " - " . utf8_encode(strftime("%A", strtotime($dia)));

        $table .=
                "<tr>
                    <td align='left'>$data</td>
                    <td>" . number_format(receitaFretePeso($ano, $mes, date('d', strtotime($dia))), 0, ',', '.') . "</td>
                </tr>";
    }

    /** Configurações do envio de e-mail (cabeçalho e corpo da mensagem) */
    $subject = utf8_decode("Faturamento diário (" . $hoje->format('d/m/Y') . ")");

    $body = utf8_decode(
        "<div class='body'>
            <b>Boa noite, o BID informa o resumo sobre seu faturamento:</b><br /><br />
            <div class='lista'>
                O dia de hoje encerra com valor de <b>R$ " . $hoUtils->numberFormat($fatHoje, 0, 0) . "</b>.<br />
                Total previsto no mês: <b>R$ " . $hoUtils->numberFormat($prevMes, 0, 0) . "</b>.<br />
                Total atingido no mês: <b>R$ " . $hoUtils->numberFormat($fatMes, 0, 0) . " ($prcMes %)</b>.<br />
                Média por dia: <b>R$ " . $hoUtils->numberFormat($mediaMes, 0, 0) . "</b>.<br />
            </div><br />
            <table class='table'>
                <thead>
                    <tr><th colspan='3'>Valores por tipo de contrato</th></tr>
                    <tr>
                        <th>Contrato</th>
                        <th>Hoje</th>
                        <th>No mês</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>" . DD::valueTipoContrato(DD::TP_CONTRATO_FROTA) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($fatFrtHoje, 0, 0) . " (" . $hoUtils->numberFormat($prcFrtHoje) . " %)</td>
                        <td align='right'>" . $hoUtils->numberFormat($fatFrtMes , 0, 0) . " (" . $hoUtils->numberFormat($prcFrtMes) . " %)</td>
                    </tr>
                    <tr>
                        <td>" . DD::valueTipoContrato(DD::TP_CONTRATO_AGREGADO) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($fatAgrHoje, 0, 0) . " (" . $hoUtils->numberFormat($prcAgrHoje) . " %)</td>
                        <td align='right'>" . $hoUtils->numberFormat($fatAgrMes , 0, 0) . " (" . $hoUtils->numberFormat($prcAgrMes) . " %)</td>
                    </tr>
                    <tr>
                        <td>" . DD::valueTipoContrato(DD::TP_CONTRATO_TERCEIRO) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($fatTerHoje, 0, 0) . " (" . $hoUtils->numberFormat($prcTerHoje) . " %)</td>
                        <td align='right'>" . $hoUtils->numberFormat($fatTerMes , 0, 0) . " (" . $hoUtils->numberFormat($prcTerMes) . " %)</td>
                    </tr>
                </tbody>
            </table>
            <p>Segue abaixo tabela dos últimos 7 dias para acompanhamento:</p>
            <table class='table'>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Val. faturamento</th>
                    </tr>
                </thead>
                <tbody>$table</tbody>
            </table>
            <p>Para informações datalhadas sobre seu faturamento, acesse no BID o módulo <i>Comercial</i>.</p>
            <p class='bolder'>
                <span class='red'>Atenção:</span> Este é um e-mail automático, em caso de erros ou dúvidas responda diretamente para o departamento de Informática.
            </p>
        </div>");

    /** Instancia um objeto da classe Mailer responsável pelo envio das notificações */
    $mailer = new \Mailer();
    $mailer->setConfig();

    /** Lê no banco a lista de endereços para serem colocados como destinatários, cópia e cópia oculta */
    $fileName = basename(__FILE__);

    foreach ($dbcSQL->emailsServico($fileName, DD::TP_ENVMAIL_TO) as $to) $mailer->addAddress($to['email'], $to['nome']);
    foreach ($dbcSQL->emailsServico($fileName, DD::TP_ENVMAIL_CC) as $cc) $mailer->addCC($cc['email'], $cc['nome']);
    foreach ($dbcSQL->emailsServico($fileName, DD::TP_ENVMAIL_BC) as $bc) $mailer->addBCC($bc['email'], $bc['nome']);

    echo $mailer->send($subject, $body);
?>