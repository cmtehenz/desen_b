<?php
    namespace Library\Scripts\Services;

    /** Inicializando manualmente a variável de sessão com ROOT do projeto (para execuções via CLI) */
    $_SERVER['DOCUMENT_ROOT'] = dirname(dirname(dirname(__DIR__)));

    date_default_timezone_set('America/sao_paulo');
    header('Content-Type: text/html; charset=utf-8');
    setlocale(LC_ALL, "ptb");

    include $_SERVER['DOCUMENT_ROOT'] . '/library/classes/PHPMailer/PHPMailerAutoload.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/autoloader.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/startSession.php';

    use Library\Classes\KeyDictionary as DD;

    $hoUtils = new \Library\Classes\Utils();

    $params = array();
    
    /** Caso o mês atual seja Janeiro, setamos o ano e mês para dezembro do ano passado. Caso contráro, pegamos apenas o mês anterior do ano atual */
    $ano = date('Y'); 
    $mes = date('m') - 1;
    
    if (date('m') == 1){
        $ano = $ano - 1;
        $mes = 12;
    }
    
    $monthName = $hoUtils->monthName($mes);
    
    $params['mes'] = $dbcSQL->whereParam("MONTH(c.data)", $mes);

    /** Busca os dados sobre os clientes de destino dos carregamentos */
    $result = $dbcSQL->analiseClientes($ano, $params);
    
    foreach ($result as $cliente){
        $viagens     = $cliente['viagens'];
        $peso        = $cliente['peso'];
        $faturamento = $cliente['faturamento'];
        $quinzena1   = $cliente['quinzena1'];
        $quinzena2   = $cliente['quinzena2'];

        $totViagens += $viagens;
        $totPeso    += $peso;
        $totFat     += $faturamento;

        $table .=
            "<tr>
                <td>$cliente[nome]</td>
                <td>" . $hoUtils->numberFormat($viagens, 0, 0) . "</td>
                <td>" . $hoUtils->numberFormat($peso   , 0, 0) . "</td>
                <td>" . $hoUtils->numberFormat(($peso / $viagens), 0, 0) . "</td>
                <td>" . $hoUtils->numberFormat($faturamento) . "</td>
                <td>" . $hoUtils->numberFormat(($faturamento / ($peso / 1000)), 0, 0) . "</td>
                <td>" . $hoUtils->numberFormat($quinzena1) . "</td>
                <td>" . $hoUtils->numberFormat($quinzena2) . "</td>
            </tr>";
    }

    /** Configurações do envio de e-mail (cabeçalho e corpo da mensagem) */
    $subject = utf8_decode("Florestal - Fechamento por cliente (" . $monthName . " / " . $ano . ")");

    $body = utf8_decode(
        "<div class='body'>
            <b>Bom dia,</b>
            <p>Segue abaixo o fechamento de $monthName / $ano dos carregamentos florestais por cliente:</p>
            <table class='table'>
                <thead>
                    <th width='40%'>Cliente</th>
                    <th>Viagens</th>
                    <th>Peso (T)</th>
                    <th>Média T/V</th>
                    <th>Faturamento</th>
                    <th>Média F/T</th>
                    <th>1ª Quinzena</th>
                    <th>2ª Quinzena</th>
                </thead>
                <tbody>$table</tbody>
            </table>
            <p>Para informações datalhadas sobre os carregamentos, acesse no BID a opção de menu <i>Florestal > Análise por cliente</i>.</p>
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