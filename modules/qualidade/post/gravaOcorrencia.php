<?php
    namespace Modulos\Qualidade\Post;

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

    $field = $params['cpf'];
    if ((!isset($field)) || (strlen($field) == 0)) return printf($hoUtils->alertScript("Informe o CPF do motorista!"));

    $field = $params['idVeiculo'];
    if ((!isset($field)) || ($field <= 0) || (!is_numeric($field))) return printf($hoUtils->alertScript("Informe a placa do veículo!"));

    $field = $params['data'];
    if (!isset($field) || strlen($field) <= 0) return printf($hoUtils->alertScript("Informe a data da ocorrência!"));

    $field = $params['hora'];
    if (!isset($field) || strlen($field) <= 0) return printf($hoUtils->alertScript("Informe a hora da ocorrência!"));

    /** Caso já exista a placa informada, encontramos o registro para atualização */
    $dbcSQL->connect();

    /**
     * Prepara as transações que serão executadas no banco (INSERT, UPDATE, ...)
     */
    $sql = "INSERT INTO ocorrencia VALUES (:cpf, :idVeiculo, :tipo, :dtOcorrencia, :obs, :rdAcMonit, :rdClassificacao, :bipe, :cliente, $_SESSION[idUsuario], CURRENT_TIMESTAMP)";

    $msg = "Registro inserido com sucesso!";

    /**
     * Prepara os valores da inserção / atualização e seus filtros (bindParam do PDO) e exeuta as transações
     */
    $params['dtOcorrencia'] = $params['data'] . " " . $params['hora'] . ":00";

    unset($params['data']);
    unset($params['hora']);

    $params['cliente'] = $params['cliente'] ?: 'NULL';

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