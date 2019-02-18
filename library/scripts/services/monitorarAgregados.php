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
     * Busca todos os veículos Agregados e não bloqueados que não realizam uma viajam dentro dos últimos 2 dias (NOT EXISTS <subselect>)
     */
    $sql = "SELECT
                V.PLACA, P.RAZAO_SOCIAL proprietario, MAX(C.DATABAIXA) ultimaBaixa
            FROM CADBIPE   C
            JOIN HVEICULO  H ON C.ID_HVEICULO = H.ID_HVEICULO
            JOIN VEICULO   V ON H.ID_VEICULO = V.ID_VEICULO
            JOIN VEICEMP   E ON (V.ID_VEICULO = E.ID_VEICULO AND E.ID_EMPRESA = 1)
            JOIN HPROPRIET P ON V.IDHPROPRIET = P.IDHPROPRIET
            WHERE
                E.BLOQUEADO = 'N' AND E.STAFT = 'A'
                AND NOT EXISTS (
                    SELECT 1 FROM CADBIPE B JOIN HVEICULO HV ON B.ID_HVEICULO = HV.ID_HVEICULO
                    WHERE HV.PLACA = V.PLACA AND (B.DATAEMIS >= (CURRENT DATE - 2 DAYS) OR B.DATABAIXA IS NULL)
                )
                AND NOT EXISTS (
                    SELECT 1 FROM CADBIPE B JOIN HVEICULO HV ON B.ID_HVEICULO = HV.ID_HVEICULO
                    WHERE HV.PLACA = V.PLACA AND B.DATABAIXA >= (CURRENT DATE - 2 DAYS)
                )
                AND V.PLACA NOT IN ('AGR1512')";

    $veiculos = $dbcDB2->select($sql, null, "V.PLACA", "V.PLACA, P.RAZAO_SOCIAL");

    $count = count($veiculos);

    foreach ($veiculos as $veiculo){
        $data = $veiculo['ULTIMABAIXA'];
        $days = $hoUtils->diffDateDays($data, date('Y-m-d'));

        /** Destaca em vermelho quando a diferença for maior que 5 dias */
        $bgcolor = ($days >= 5) ? "#FF4D4D" : "#FFFFFF";

        $table .=
            "<tr>
                <td>$veiculo[PLACA]</td>
                <td>$veiculo[PROPRIETARIO]</td>
                <td style='background-color: $bgcolor;'> " . $hoUtils->dateFormat($data, 'Y-m-d', 'd/m/Y') . " - $days dias atrás</td>
            </tr>";
    }

    /** Configurações do envio de e-mail (cabeçalho e corpo da mensagem) */
    $subject = ("Agregados inativos (" . date('d/m/Y') . ")");

    $body = utf8_decode(
        "<div class='body'>
            <b>Bom dia, o BID informa:</b>
            <p>Existem $count veículos agregados, não bloqueados e que não tem BIPE no sistema / não carregam há mais de 2 dias:</p>
            <table class='table'>
                <thead>
                    <tr>
                        <th>Placa</th>
                        <th>Proprietário</th>
                        <th>Última viagem</th>
                    </tr>
                </thead>
                <tbody>$table</tbody>
            </table>
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