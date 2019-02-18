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
     * Busca todos os veículos que possam ter inconsistência com o KM atual (diferença de 1700 km ou mais do atual para o odômetro anterior).
     * Possui um filtro fixo para ignorar certas placas que são usadas internamente na empresa (dentro do pátio ou para serviços internos)
     */
    $sql = "SELECT
                V.PLACA, A.ODOMETRO atual, A.ODOANTER anterior, (A.ODOMETRO - A.ODOANTER) diferenca
            FROM VEICULO V
            JOIN VEICEMP E ON (V.ID_VEICULO = E.ID_VEICULO AND E.ID_EMPRESA = 1)
            JOIN ABAST   A ON (V.ID_VEICULO = A.ID_VEICULO)
            WHERE
                E.STAFT = 'F' AND E.BLOQUEADO = 'N' AND (A.ODOMETRO - A.ODOANTER) > 1700
                AND A.IDABAST = (SELECT T.IDABAST FROM ABAST T WHERE T.ID_VEICULO = V.ID_VEICULO ORDER BY T.IDABAST DESC FETCH FIRST 1 ROWS ONLY)
                AND V.PLACA NOT IN ('MFF8387','MFZ1426','MGY7850')";

    $result = $dbcDB2->select($sql, null, "V.PLACA");

    foreach ($result as $veiculo)
        $table .=
            "<tr>
                <td>$veiculo[PLACA]</td>
                <td>" . $hoUtils->numberFormat($veiculo['ATUAL'], 0, 0) . "</td>
                <td>" . $hoUtils->numberFormat($veiculo['ANTERIOR'], 0, 0) . "</td>
                <td>" . $hoUtils->numberFormat($veiculo['DIFERENCA'], 0, 0) . "</td>
            </tr>";

    /** Configurações do envio de e-mail (cabeçalho e corpo da mensagem) */
    $subject = utf8_decode("Inconsistências de odômetro (" . date('d/m/Y') . ")");

    $body = utf8_decode(
        "<div class='body'>
            <b>Bom dia, o BID informa:</b>
            <p>Os seguintes veículos podem possuir inconsistência nos seus odômetros atuais, decorrente de erro no lançamento de notas de abastecimento:</p>
            <table class='table'>
                <thead>
                    <tr><th>Placa</th><th>Km atual</th><th>Km anterior</th><th>Diferença</th></tr>
                </thead>
                <tbody>$table</tbody>
            </table>
            <p>Para informações datalhadas sobre cada veículo acesse no BID a opção de menu <i>Operacional > Controle de revisões</i>.</p>
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