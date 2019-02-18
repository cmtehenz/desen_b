<?php
    namespace Library\Scripts\Services;

    /** Inicializando manualmente a variável de sessão com ROOT do projeto (para execuções via CLI) */
    $_SERVER['DOCUMENT_ROOT'] = dirname(dirname(dirname(__DIR__)));

    date_default_timezone_set('America/sao_paulo');
    header('Content-Type: text/html; charset=utf-8');

    include $_SERVER['DOCUMENT_ROOT'] . '/library/classes/PHPMailer/PHPMailerAutoload.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/autoloader.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/startSession.php';

    use Library\Classes\KeyDictionary as DD;

    $hoUtils = new \Library\Classes\Utils();

    $dtIni = date('Y-m-d', strtotime("-7 days"));
    $dtFin = date('Y-m-d', strtotime("-1 days"));

    $params = array( $dbcSQL->whereParam("e.data", $dtIni, ">="), $dbcSQL->whereParam("e.data", $dtFin, "<=") );

    /** Busca todos os adiantamentos de frota feitos e importados no sistema no dia anterior */
    $adiantamentos =
        $dbcSQL->select(
            "SELECT
                a.idViagem, a.cpf, a.nome, dbo.DateFormat103(e.data) data, e.numBradesco, e.debito valor
            FROM pcd.adiantamento a
            JOIN pcd.extratobrd e ON a.numBradesco = e.numBradesco",
            $params, "e.data, a.nome");

    $queryTpContrato = "SELECT E.STAFT result FROM MOTEMP E JOIN MOTORIS M ON (E.IDMOTORIS = M.IDMOTORIS AND E.ID_EMPRESA = 1)";

    foreach ($adiantamentos as $adiantamento){
        /** Busca no GetOne o tipo de contrato e só relaciona se não for frota */
        $paramTpCon = array($dbcDB2->whereParam("M.CPF", $adiantamento['cpf']));

        $dbcDB2->selectTopOne($queryTpContrato, $paramTpCon);
        $tpCon = $dbcDB2->getResultCell();

        if ($tpCon != 'F'){
            $favorecido = $hoUtils->cnpjCpfFormat($adiantamento['cpf']) . " - " . $adiantamento['nome'];

            $tbodyAdtos .=
                "<tr>
                    <td>$favorecido</td>
                    <td>" . $hoUtils->numberFormat($adiantamento['valor']) . "</td>
                    <td>$adiantamento[data]</td>
                    <td>" . $hoUtils->numberFormat($adiantamento['numBradesco'], 0, 0, '', '') . "</td>
                    <td>" . $hoUtils->numberFormat($adiantamento['idViagem'], 0, 0, '', '') . "</td>
                </tr>";
        }
    }

    /** Busca todos os registros de contratos de frete dos arquivos Pamcard duplicados */
    $contratos =
        $dbcSQL->select(
            "SELECT
                c.idViagem, c.cte, c.filial, c.bipe, c.tipoParcela, e.numBradesco, e.debito valor, e.lancamento descricao, dbo.DateFormat103(e.data) data
            FROM pcd.contrato c
            LEFT JOIN pcd.extratobrd e ON c.numBradesco = e.numBradesco
            WHERE (
                    SELECT COUNT(t.idContrato) FROM pcd.contrato t
                    WHERE t.cte = c.cte AND t.filial = c.filial AND t.tipoParcela = c.tipoParcela
                ) > 1
                AND e.data BETWEEN '$dtIni' AND '$dtFin'",
            null, "e.data, c.cte, c.bipe");

    foreach ($contratos as $contrato){
        $descricao = preg_replace('/[0-9]+/', '', $contrato['descricao']);

        $tbodyCont .=
            "<tr>
                <td>$contrato[data]</td>
                <td>$descricao</td>
                <td>" . $contrato['filial'] . ' - ' . $contrato['cte'] . "</td>
                <td>" . $contrato['filial'] . ' - ' . $contrato['bipe'] . "</td>
                <td>" . $hoUtils->tipoParcelaPamcard($contrato['tipoParcela']) . "</td>
                <td>$contrato[idViagem]</td>
                <td>$contrato[numBradesco]</td>
                <td>" . $hoUtils->numberFormat($contrato['valor']) . "</td>
            </tr>";
    }

    /** Busca todos os registros dos arquivos Bradesco duplicados */
    $listaBradesco =
        $dbcSQL->select(
            "SELECT
                dbo.DateFormat103(e.data) data, e.lancamento descricao, e.numBradesco, e.documento, e.debito valor
            FROM pcd.extratobrd e
            WHERE (
                    SELECT COUNT(b.idExtratoBrd) FROM pcd.extratobrd b
                    WHERE e.numBradesco = b.numBradesco AND e.documento = b.documento
                ) > 1
                AND e.data BETWEEN '$dtIni' AND '$dtFin'",
            null, "e.data, e.numBradesco, e.lancamento");

    foreach ($listaBradesco as $lancamento){
        $tbodyLanc .=
            "<tr>
                <td>$lancamento[data]</td>
                <td>$lancamento[descricao]</td>
                <td>$lancamento[numBradesco]</td>
                <td>$lancamento[documento]</td>
                <td>" . $hoUtils->numberFormat($lancamento['valor']) . "</td>
            </tr>";
    }

    /** Configurações do envio de e-mail (cabeçalho e corpo da mensagem) */
    $subject = ("Resumo pgtos. Pamcard (" . date('d/m/Y') . ")");

    if (!$tbodyAdtos) $tbodyAdtos = "<tr><td colspan='5'>Nenhuma irregularidade encontrada</td></tr>";

    $tableAdtos =
        "<table class='table'>
            <thead>
                <tr>
                    <th>Favorecido</th>
                    <th>Valor</th>
                    <th>Data</th>
                    <th>Nº aut. Bradesco</th>
                    <th>ID viagem</th>
                </tr>
            </thead>
            <tbody>$tbodyAdtos</tbody>
        </table>";

    if (!$tbodyCont) $tbodyCont = "<tr><td colspan='8'>Nenhuma irregularidade encontrada</td></tr>";

    $tableCont =
        "<table class='table'>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Descrição</th>
                    <th>CT-e</th>
                    <th>BIPE</th>
                    <th>Parcela</th>
                    <th>ID viagem</th>
                    <th>Nº bradesco</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>$tbodyCont</tbody>
        </table>";

    if (!$tbodyLanc) $tbodyLanc = "<tr><td colspan='5'>Nenhuma irregularidade encontrada</td></tr>";

    $tableLanc =
        "<table class='table'>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Descrição</th>
                    <th>Nº autorização</th>
                    <th>Nº documento</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>$tbodyLanc</tbody>
        </table>";

    $body = utf8_decode(
        "<div class='body'>
            <b>Bom dia, o BID informa seu resumo sobre os pagamentos Pamcard dos últimos 7 dias:</b>
            <p>Relação de pagamentos sem CT-e (avulsos em cartão) realizados para terceiros ou agregados:</p>
            $tableAdtos
            <p>Relação de registros duplicados no arquivo Pamcard (mesmo CT-e e tipo de parcela):</p>
            $tableCont
            <p>Relação de registros duplicados no arquivo de conciliação do Bradesco (mesmo Nº autorização e Nº documento):</p>
            $tableLanc
            <p>Para informações mais datalhadas acesse no BID as opções de menu <i>Utilitários > Pagamentos Pamcard</i> e <i>Bradesco x Pamcard</i>.</p>
            <p style='font-weight: bold;'>
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