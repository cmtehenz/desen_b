<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    header('Content-Type: text/html; charset=utf-8');

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Classes\connectMSSQL();

    /** Necessário para o alertScript personalizado */
    echo "<link rel='stylesheet' href=" . $hoUtils->getURLDestino('stylesheets/all.css') . " type='text/css' />";
    echo "<script src=" . $hoUtils->getURLDestino("js/all.js") . "></script>";

    $razaoSocial = $_POST['razaoSocial'];
    if ((!isset($razaoSocial)) || (strlen($razaoSocial) == 0)) return printf($hoUtils->alertScript("Informe a razão social"));

    $nomeFantasia = $_POST['nomeFantasia'];
    if ((!isset($nomeFantasia)) || (strlen($nomeFantasia) == 0)) return printf($hoUtils->alertScript("Informe o nome fantasia"));

    $cfopVendas = $_POST['listaCFOP'] ?: "";
    $cfopVendas = str_replace(" ", "", $cfopVendas);

    $dbcSQL->connect();

    $result = $dbcSQL->execute(
        "UPDATE empresa SET
            razaoSocial = '" . $razaoSocial . "',
            nomeFantasia = '" . $nomeFantasia . "'
        WHERE id = $_SESSION[idEmpresa]");

    $dbcSQL->disconnect();

    return printf($hoUtils->alertScript(($result ?: "Registro alterado com sucesso!"), "Pronto", "window.location = '../configurar_empresa.php'"));
?>