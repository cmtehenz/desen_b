<?php
    namespace Modulos\Operacional\Post;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    header('Content-Type: text/html; charset=utf-8');

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Classes\connectMSSQL();

    /** Necessário para o alertScript personalizado */
    echo "<link rel='stylesheet' href=" . $hoUtils->getURLDestino('stylesheets/all.css') . " type='text/css' />";
    echo "<script src=" . $hoUtils->getURLDestino("js/all.js") . "></script>";

    /**
     * Validações e retornos
     */
    $params = filter_input_array(INPUT_POST);

    $field = $params['placa'];
    if ((!isset($field)) || (strlen($field) == 0)) return printf($hoUtils->alertScript("Informe placa do veículo!"));

    $field = $params['kmUltima'];
    if ((!isset($field)) || ($field <= 0) || (!is_numeric($field))) return printf($hoUtils->alertScript("Km da última revisão inválido!"));

    $field = $params['periodo'];
    if ((!isset($field)) || ($field <= 0) || (!is_numeric($field))) return printf($hoUtils->alertScript("Período de revisão inválido!"));

    /** Caso já exista a placa informada, encontramos o registro para atualização */
    $dbcSQL->connect();

    $filter = array($dbcSQL->whereParam("placa", $params['placa']));

    $params['id'] = $params['id'] ?: $dbcSQL->simpleSelect("revisao", "idRevisao", $filter);

    /** Default 0 para o checkbox caso não tenha sido marcado */
    $params['vendido'] = $params['vendido'] ?: 0;
    $params['parado']  = $params['parado']  ?: 0;

    /**
     * Prepara as transações que serão executadas no banco (INSERT, UPDATE, ...)
     */
    if ($params['id'] != null){
        $sql = "UPDATE revisao SET
                    placa = :placa,  periodo = :periodo, kmUltima = :kmUltima, dtUltima = :dtUltima, operacao = :operacao, vendido = :vendido, parado = :parado
                WHERE idRevisao = :id";

        $msg = "Registro alterado com sucesso!";
    }
    else
    {
        $sql = "INSERT INTO revisao VALUES (:placa, :periodo, :kmUltima, :dtUltima, :operacao, :vendido, :parado)";

        $msg = "Registro inserido com sucesso!";
    }

    /**
     * Prepara os valores da inserção / atualização e seus filtros (bindParam do PDO) e exeuta as transações
     */
    $result = $dbcSQL->execute($sql, $params);

    $dbcSQL->disconnect();

    if ($result) $msg = "Erro na transação: $result";

    /**
     * Recupera o caminho do arquivo que chamou este POST e retorna para ele alertando a mensagem de retorno.
     * O explode serve para remover qualquer parâmetro adjacente ao caminho do arquivo (i.e. file.php?id=1 se torna file.php)
     */
    $location = explode(".php", $_SERVER['HTTP_REFERER']);

    return printf($hoUtils->alertScript($msg, "Pronto", "window.location = '$location[0].php'"));
?>