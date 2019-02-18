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
     * Busca todos os CT-es sem BIPE abertos há mais de 3 horas
     */
    $sql = "SELECT
                (F.SIGLA_FILIAL || ' - ' || C.NUMERO) cte, V.PLACA, U.USUARIO expedidor,
                (C.DATAEMISSAO || ' ' || RIGHT(('00' || C.HORAEMI), 2) || ':' || RIGHT(('00' || C.MINEMI), 2)) emissao
            FROM CT C
            JOIN FILIAL   F ON C.ID_FILIAL = F.ID_FILIAL
            JOIN USUARIO  U ON C.IDUSUARIOEMI = U.ID_USUARIO
            JOIN HVEICULO V ON C.ID_HVEICULO = V.ID_HVEICULO
            WHERE
                C.STATUSCT <> 'C' AND  C.TIPOCTRC <> 'A' AND C.ID_CTANU IS NULL AND C.IDCADBIPE IS NULL AND
                (TIMESTAMP(C.DATAEMISSAO || '-' || RIGHT(('00' || C.HORAEMI), 2) || '.' || RIGHT(('00' || C.MINEMI), 2) || '.00.0') <= (CURRENT TIMESTAMP - 3 HOURS))
                ";

    $orderBy = "(C.DATAEMISSAO || ' ' || RIGHT(('00' || C.HORAEMI), 2) || ':' || RIGHT(('00' || C.MINEMI), 2)), F.SIGLA_FILIAL, C.NUMERO, U.USUARIO";

    $ctes = $dbcDB2->select($sql, null, $orderBy);
    $count = count($ctes);

    foreach ($ctes as $cte){
        $emissao = $hoUtils->dateFormat($cte['EMISSAO'], 'Y-m-d H:i', 'd/m/Y H:i');

        $lista .= $cte['CTE'] . " - " . $cte['PLACA'] . " - " . $emissao . " - " . $cte['EXPEDIDOR'] . "<br />";
    }

    /** Configurações do envio de e-mail (cabeçalho e corpo da mensagem) */
    $subject = ("CT-es sem BIPE (" . date('d/m/Y H:i') . ")");

    $body = utf8_decode(
        "<div class='body'>
            <b>Bom dia, o BID informa:</b>
            <p>Existem $count CT-es emitidos há mais de 3 horas e sem BIPE vinculado no sistema:</p>
            <div class='lista'>$lista</div>
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