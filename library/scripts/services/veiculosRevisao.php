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
     * Busca todos os veículos cadastrados no controle de revisão do BID para percorrê-los através de um loop que verifica o Km atual de cada um (GOE)
     * e determina quais estão em período de revisão
     */
    $sql = "SELECT r.placa, (r.kmUltima + r.periodo) kmProxima, r.operacao FROM revisao r";
    
    $params = array($dbcSQL->whereParam("r.parado", 0), $dbcSQL->whereParam("r.vendido", 0));
    
    $veiculos = $dbcSQL->select($sql, $params, "r.placa");

    foreach ($veiculos as $veiculo){
        $infoKm = $dbcDB2->kmAtual($veiculo['placa']);
        
        $diferenca = $veiculo['kmProxima'] - $infoKm['ATUAL'];
        
        switch (true){
            case $diferenca <= 2000 && $diferenca >= -2000: $bgcolor = "#FFFF66"; break; // Em período
            case $diferenca < -2000: $bgcolor = "#FF4D4D"; break; // Estouro
            default: break;
        }
        
        // Escreve apenas os que estão em período / estouro
        if ($diferenca <= 2000)
            $table .=
                "<tr>
                    <td>$veiculo[placa]</td>
                    <td>" . $hoUtils->numberFormat($infoKm['ATUAL'], 0, 0) . "</td>
                    <td>" . $hoUtils->numberFormat($veiculo['kmProxima'], 0, 0) . "</td>
                    <td style='background-color: $bgcolor;'>" . $hoUtils->numberFormat($diferenca, 0, 0) . "</td>
                    <td>" . DD::valueOperacao($veiculo['operacao']) . "</td>
                </tr>";
    }

    /** Configurações do envio de e-mail (cabeçalho e corpo da mensagem) */
    $subject = utf8_decode("Controle de revisões (" . date('d/m/Y') . ")");

    $body = utf8_decode(
        "<div class='body'>
            <b>Bom dia, o BID informa:</b>
            <p>Os seguintes veículos estão ou estouraram seu período de revisão:</p>
            <table class='table'>
                <thead>
                    <tr>
                        <th>Placa</th>
                        <th>Km atual</th>
                        <th>Km revisão</th>
                        <th>Diferença</th>
                        <th>Operação</th>
                    </tr>
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