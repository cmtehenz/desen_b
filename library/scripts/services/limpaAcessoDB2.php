<?php
    namespace Library\Scripts\Services;

    /** Inicializando manualmente a variável de sessão com ROOT do projeto (para execuções via CLI) */
    $_SERVER['DOCUMENT_ROOT'] = dirname(dirname(dirname(__DIR__)));

    date_default_timezone_set('America/sao_paulo');
    header('Content-Type: text/html; charset=utf-8');

    include $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/autoloader.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/startSession.php';

    while (true){
        try {
            $dbcDB2->connect();
            $dbcDB2->execute("DELETE FROM ACESSO");
            $dbcDB2->disconnect();
        
            sleep(1);
        } catch (Exception $ex) {
            sleep(1);
        }
    }
?>