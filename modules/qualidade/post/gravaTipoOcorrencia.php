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

    $field = $params['descricao'];
    if ((!isset($field)) || (strlen($field) == 0)) return printf($hoUtils->alertScript("Informe a descrição!"));

    if (!is_numeric($params['pontos'])) $params['pontos'] = 0;

    $params['descricao'] = trim($params['descricao']);

    /** Caso já exista a descrição informada, encontramos o registro para atualização */
    $dbcSQL->connect();

    $filter = array($dbcSQL->whereParam("descricao", $params['descricao']));

    $params['id'] = $params['id'] ?: $dbcSQL->simpleSelect("tipoOcorrencia", "id", $filter);

    /**
     * Prepara as transações que serão executadas no banco (INSERT, UPDATE, ...)
     */
    if ($params['id'] != null){
        $sql = "UPDATE tipoOcorrencia SET descricao = :descricao, pontos = :pontos WHERE id = :id";

        $msg = "Registro alterado com sucesso!";
    }
    else
    {
        $sql = "INSERT INTO tipoOcorrencia VALUES (:descricao, :pontos)";

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