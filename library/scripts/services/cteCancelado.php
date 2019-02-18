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

    $ontem = date('d/m/Y', strtotime("-1 day"));

    /**
     * Busca os CT-es com data de cancelamento igual ao dia anterior
     */
    $sql = "SELECT
                (TRIM(F.SIGLA_FILIAL) || ' - ' || C.NUMERO) cte, L.RAZAO_SOCIAL cliente, U.USUARIO usuCanc,
                (RIGHT(('00' || C.HORACANC), 2) || ':' || RIGHT(('00' || C.MINCANC), 2)) horario
            FROM CT C
            JOIN FILIAL F ON C.ID_FILIAL = F.ID_FILIAL
            JOIN HCLIENTE L ON C.IDHCLIENTE = L.IDHCLIENTE
            JOIN USUARIO U ON C.IDUSUARIOCANC = U.ID_USUARIO
            WHERE
                C.DATACANC = (CURRENT DATE - 1 DAYS)";

    $result = $dbcDB2->select($sql, null, "F.SIGLA_FILIAL, C.HORACANC, C.MINCANC, C.NUMERO");

    foreach ($result as $cte)
        $table .=
            "<tr>
                <td>$cte[CTE]</td>
                <td>$cte[CLIENTE]</td>
                <td>$cte[HORARIO]</td>
                <td>$cte[USUCANC]</td>
            </tr>";

    if (!$table) $table = "<tr><td colspan='4'>Não houveram cancelamentos</td></tr>";

    /** Configurações do envio de e-mail (cabeçalho e corpo da mensagem) */
    $subject = utf8_decode("CT-es cancelados (" . $ontem . ")");

    $body = utf8_decode(
        "<div class='body'>
            <b>Bom dia, o BID informa:</b>
            <p>Os seguintes CT-es foram cancelados no dia de ontem ($ontem):</p>
            <table class='table'>
                <thead>
                    <tr>
                        <th>CT-e</th>
                        <th>Cliente</th>
                        <th>Horário</th>
                        <th>Expedidor</th>
                    </tr>
                </thead>
                <tbody>$table</tbody>
            </table>
            <p>Para mais informações acesse no BID a opção de menu <i>Operacional > CT-es cancelados</i>.</p>
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