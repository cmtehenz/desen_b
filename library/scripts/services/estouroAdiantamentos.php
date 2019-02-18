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
     * Busca os BIPEs cujo adiantamento foi maior que o parametrizado na filial
     */
    $sql = "SELECT
                (TRIM(F.SIGLA_FILIAL) || ' - ' || C.NUMBIPE) bipe, C.VALFRETEPAGOTOT valFrete, P.VALOR valAdto, (((P.VALOR - C.VALCREDVE) / C.VALFRETEPAGOTOT) * 100) prcAdto, F.PERCADTOTER paramFilial,
                (C.DATAEMIS || ' ' || RIGHT(('00' || C.HORAEMIS), 2) || ':' || RIGHT(('00' || C.MINEMIS), 2)) emissao, U.USUARIO usuEmis
            FROM CADBIPE C
            JOIN FILIAL F ON C.ID_FILIAL = F.ID_FILIAL
            JOIN USUARIO U ON C.IDUSUARIOEMIS = U.ID_USUARIO
            JOIN PROGVIAG P ON (C.IDCADBIPE = P.IDCADBIPE AND P.SEQ = 1 AND P.OPERACAO = 'Adiantamento')
            WHERE
                C.DATAEMIS = (CURRENT DATE - 1 DAYS) AND (ROUND(((P.VALOR - C.VALCREDVE) / C.VALFRETEPAGOTOT), 2) > (F.PERCADTOTER / 100))";

    $result = $dbcDB2->select($sql, null, "C.IDCADBIPE");

    foreach ($result as $bipe)
        $table .=
            "<tr>
                <td>$bipe[BIPE]</td>
                <td>" . $hoUtils->numberFormat($bipe['VALFRETE']) . "</td>
                <td>" . $hoUtils->numberFormat($bipe['VALADTO']) . "</td>
                <td>" . $hoUtils->numberFormat($bipe['PRCADTO']) . "</td>
                <td>" . $hoUtils->numberFormat($bipe['PARAMFILIAL']) . "</td>
                <td>$bipe[USUEMIS]</td>
            </tr>";

    /** Configurações do envio de e-mail (cabeçalho e corpo da mensagem) */
    $subject = utf8_decode("Estouro de adiantamentos (" . date('d/m/Y') . ")");

    $body = utf8_decode(
        "<div class='body'>
            <b>Bom dia, o BID informa:</b>
            <p>Os seguintes BIPEs foram emitidos com adiantamento superior ao permitido no cadastro de filiais:</p>
            <table class='table'>
                <thead>
                    <tr>
                        <th>BIPE</th>
                        <th>Tot. frete</th>
                        <th>Val. adto.</th>
                        <th>% adto.</th>
                        <th>% filial</th>
                        <th>Expedidor</th>
                    </tr>
                </thead>
                <tbody>$table</tbody>
            </table>
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