<?php
    /**
     * Script para inicializar as variáveis de sessão responsáveis pela conexão com banco, deve ser utilizado para conexão em scripts stand-alone fora do BID,
     * como o montar_monitoramento.php (ver arquivo para exemplo de utilização)
     */
    $_SESSION = array();

    $xml = simplexml_load_file($_SERVER['DOCUMENT_ROOT'] . '/.config.xml') or die ("Erro ao carregar configurações, informe o administrador.");

    $_SESSION['idEmpresa'] = $xml->idEmpresa->__toString();
    $_SESSION['dbHost']    = $xml->database->host->__toString();
    $_SESSION['dbName']    = $xml->database->name->__toString();
    $_SESSION['dbUser']    = $xml->database->user->__toString();
    $_SESSION['dbPswd']    = $xml->database->password->__toString();

    $dbcSQL = new \Library\Scripts\scriptSQL();

    // Dados empresa logada
    $empresa = $dbcSQL->selectTopOne("SELECT TOP 1 * FROM empresa", array( $dbcSQL->whereParam("id", $_SESSION['idEmpresa']) ));

    $_SESSION['nomeEmpresa']  = $empresa['nomeFantasia'];
    $_SESSION['dbERPHost']    = $empresa['dbHost'];
    $_SESSION['dbERPName']    = $empresa['dbName'];
    $_SESSION['dbERPUser']    = $empresa['dbUser'];
    $_SESSION['dbERPPswd']    = $empresa['dbPswd'];
    $_SESSION['anoInicioERP'] = $empresa['anoInicioERP'];

    $_SESSION['smtp']['host'] = $empresa['smtpHost'];
    $_SESSION['smtp']['mail'] = $empresa['smtpMail'];
    $_SESSION['smtp']['pswd'] = $empresa['smtpPswd'];
    $_SESSION['smtp']['name'] = $empresa['smtpName'];

    $dbcDB2 = new \Library\Scripts\scriptDB2();
?>