<?php
	header('Cache-Control: no-cache');

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $get = filter_input_array(INPUT_GET);

    $dbcSQL = new \Library\Classes\connectMSSQL();
    $dbcSQL->connect();

    $value = ($get['texto'] ? ("'" . $get['texto'] . "'") : "null");

    $result = $dbcSQL->execute("UPDATE pcd.contrato SET justificativa = $value WHERE idContrato = $get[id]");

    $dbcSQL->disconnect();

    echo json_encode($result);
?>