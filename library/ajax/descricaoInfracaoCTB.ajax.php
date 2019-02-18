<?php
    /**
     * Ajax para buscar a descrição da infração de trânsito no SQL Server
     */
    header('Cache-Control: no-cache');

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $request = filter_input_array(INPUT_GET);

    $dbcSQL = new \Library\Scripts\scriptSQL();

    $params = array($dbcSQL->whereParam('codigo', $request['codigo']), $dbcSQL->whereParam('digito', $request['digito']));

    $retorno = $dbcSQL->selectTopOne("SELECT i.descricao FROM mlt.infracao i", $params);

    echo json_encode($retorno['descricao'] ?: "Infração não encontrada");
?>