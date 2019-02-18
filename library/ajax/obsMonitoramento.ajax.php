<?php
	header('Cache-Control: no-cache');

    include "../../old/connect_mssql.php";

    $request = filter_input_array(INPUT_GET);

    $retorno = mssql_query("update MONITORAMENTO set OBS = '" . utf8_decode($request['obs']) . "' WHERE placa = '$request[placa]'");

    echo json_encode($retorno);
?>