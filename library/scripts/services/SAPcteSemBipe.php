<?php
    namespace Library\Scripts\Services;

    /** Inicializando manualmente a variável de sessão com ROOT do projeto (para execuções via CLI) */
    $_SERVER['DOCUMENT_ROOT'] = dirname(dirname(dirname(__DIR__)));

    date_default_timezone_set('America/sao_paulo');
    header('Content-Type: text/html; charset=utf-8');

    include $_SERVER['DOCUMENT_ROOT'] . '/library/classes/PHPMailer/PHPMailerAutoload.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/autoloader.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/startSession.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptSap.php';

    use Library\Classes\KeyDictionary as DD;

    $hoUtils = new \Library\Classes\Utils();

    /**
     * Busca todos os CT-es sem BIPE abertos há mais de 3 horas
     */
       
    //$emissao = $hoUtils->dateFormat($cte['EMISSAO'], 'Y-m-d H:i', 'd/m/Y H:i');
    
    $count = 0;
    foreach (cteSemBipeDiario() as $dados){
        $lista .= $dados['FILIAL'] ."-". $dados[NUMERO] ." -> ". $dados[PLACA] ." -> ". $dados[DATA]." ".$dados[HORA] ." -> ". $dados[USUARIO] ."<br />";
        $count++;
    }

    /** Configurações do envio de e-mail (cabeçalho e corpo da mensagem) */
    $subject = ("CT-es sem BIPE (" . date('d/m/Y H:i') . ")");

    $body = utf8_decode(
        "<div class='body'>
            <b>Bom dia, o BID informa:</b>
            <p>Existem $count CT-es emitidos sem BIPE vinculado no sistema:</p>
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