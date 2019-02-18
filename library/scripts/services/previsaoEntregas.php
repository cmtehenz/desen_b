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

    /**
     * Busca todos os CT-es com BIPE não baixado e verifica a data de agendamento para classificá-los como atrasados ou nao
     */

    /** Em atraso - Independente de ser fracionada ou não - Compara o SEQENTBIPE com a qtd. de entregas da viagem pra saber se é a última */
    $sql = "SELECT COUNT(C.ID_CT) qtd FROM CT C
            JOIN CADBIPE B ON C.IDCADBIPE = B.IDCADBIPE
            WHERE
                B.DATABAIXA IS NULL AND C.SEQENTBIPE = (SELECT COUNT(T.ID_CT) FROM CT T WHERE T.IDCADBIPE = C.IDCADBIPE)
                AND
                (
                    (TIMESTAMP(C.DATAAGENDA || '-' || RIGHT(('00' || C.HORAAGENDA), 2) || '.' || RIGHT(('00' || C.MINAGENDA), 2) || '.00.0') <= (CURRENT TIMESTAMP))
                    OR
                    C.DATAAGENDA IS NULL
                )";

    $result = $dbcDB2->selectTopOne($sql);
    $atraso = $result['QTD'];
    
    /** Fracionadas "em atraso" - Compara o SEQENTBIPE com a qtd. de entregas da viagem pra saber se é diferente da última e acusa pendência */
    $sql = "SELECT COUNT(C.ID_CT) qtd FROM CT C
            JOIN CADBIPE B ON C.IDCADBIPE = B.IDCADBIPE
            WHERE
                B.DATABAIXA IS NULL AND C.SEQENTBIPE <> (SELECT COUNT(T.ID_CT) FROM CT T WHERE T.IDCADBIPE = C.IDCADBIPE)
                AND
                (
                    (TIMESTAMP(C.DATAAGENDA || '-' || RIGHT(('00' || C.HORAAGENDA), 2) || '.' || RIGHT(('00' || C.MINAGENDA), 2) || '.00.0') <= (CURRENT TIMESTAMP))
                    OR
                    C.DATAAGENDA IS NULL
                )";

    $result    = $dbcDB2->selectTopOne($sql);
    $pendencia = $result['QTD'];

    /** Em dia - Independente de ser fracionada ou não, se a data está em dia, busca nesse SELECT */
    $sql = "SELECT COUNT(C.ID_CT) qtd FROM CT C
            JOIN CADBIPE  B ON C.IDCADBIPE = B.IDCADBIPE
            WHERE
                B.DATABAIXA IS NULL AND
                (TIMESTAMP(C.DATAAGENDA || '-' || RIGHT(('00' || C.HORAAGENDA), 2) || '.' || RIGHT(('00' || C.MINAGENDA), 2) || '.00.0') > (CURRENT TIMESTAMP))";

    $result = $dbcDB2->selectTopOne($sql);
    $emdia  = $result['QTD'];

    $total = $atraso + $emdia + $pendencia;

    /** Resumo de atrasos por filial */
    $sql = "SELECT
                COUNT(C.ID_CT) qtd, F.SIGLA_FILIAL sigla, F.NOME_FILIAL filial
            FROM CT C
            JOIN FILIAL  F ON C.ID_FILIAL = F.ID_FILIAL
            JOIN CADBIPE B ON C.IDCADBIPE = B.IDCADBIPE
            WHERE
                B.DATABAIXA IS NULL AND C.SEQENTBIPE = (SELECT COUNT(T.ID_CT) FROM CT T WHERE T.IDCADBIPE = C.IDCADBIPE) AND 
                (
                    (TIMESTAMP(C.DATAAGENDA || '-' || RIGHT(('00' || C.HORAAGENDA), 2) || '.' || RIGHT(('00' || C.MINAGENDA), 2) || '.00.0') <= (CURRENT TIMESTAMP))
                    OR
                    C.DATAAGENDA IS NULL
                )";

    $result = $dbcDB2->select($sql, null, "COUNT(C.ID_CT) DESC", "F.SIGLA_FILIAL, F.NOME_FILIAL");

    foreach ($result as $filial)
        $lista .= $filial['SIGLA'] . " - " . $filial['QTD'] . " - " . $filial['FILIAL'] . "<br />";

    /** Configurações do envio de e-mail (cabeçalho e corpo da mensagem) */
    $subject = utf8_decode("Previsão de entregas (" . date('d/m/Y H:i') . ")");

    $body = utf8_decode(
        "<div class='body'>
            <b>Bom dia, o BID informa:</b>
            <p>Resumo de entregas programadas no sistema:</p>
            <div class='lista'>
                <div class='bolder'>Total = $total cargas em andamento (BIPE aberto no sistema)</div>
                <div class='red-bolder'>$atraso cargas em atraso (data de agendamento inferior à atual)</div>
                <div class='green-bolder'>$emdia cargas em dia (data de agendamento superior à atual)</div>
                <div class='yellow-bolder'>$pendencia cargas fracionadas (possuem uma ou mais entregas que aguardam baixa do BIPE)</div>
            </div>
            <p>Resumo de atrasos por filial:</p>
            <div class='lista'>$lista</div>
            <p>Para informações datalhadas sobre cada filial acesse no BID a opção de menu <i>Operacional > Previsão de entregas</i>.</p>
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