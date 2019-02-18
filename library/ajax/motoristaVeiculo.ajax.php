<?php
    /**
     * Ajax para buscar o motorista de um determinado veículo (pela placa e data do histórico) na base do GetOne Enterprise
     */
    header('Cache-Control: no-cache');

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/old/scriptSap.php';

    $request = filter_input_array(INPUT_GET);

    $dbcDB2 = new \Library\Scripts\scriptDB2();
    
    if($request['data'] <= '2017-08-01'){
        $retorno = $dbcDB2->motoristaVeiculo(strtoupper($request['placa']), $request['data']);
    }
    
    if($request['data'] > '2017-08-01'){
        $retorno = buscaMotoristaMulta(strtoupper($request['placa']), $request['data']);
    }    

    echo json_encode($retorno);
?>