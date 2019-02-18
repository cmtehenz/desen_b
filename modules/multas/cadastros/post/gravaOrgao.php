<?php
    namespace Modulos\Multas\Cadastros\Post;

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

    $field = $params['cnpj'];
    if ((!isset($field)) || (strlen($field) < 14)) return printf($hoUtils->alertScript("CNPJ inválido!"));

    /** Caso já exista o CNPJ informado, encontramos o registro para atualização */
    $dbcSQL->connect();

    $filter = array($dbcSQL->whereParam("cnpj", $params['cnpj']));

    $params['id'] = $params['id'] ?: $dbcSQL->simpleSelect("mlt.orgao", "idOrgao", $filter);

    /**
     * Prepara as transações que serão executadas no banco (INSERT, UPDATE, ...)
     */
    if ($params['id'] != null){
        $sql = "UPDATE mlt.orgao SET
                    cnpj = :cnpj, razaoSocial = UPPER(:razaoSocial)
                WHERE idOrgao = :id";

        $msg = "Registro alterado com sucesso!";
    }
    else
    {
        $sql = "INSERT INTO mlt.orgao VALUES (:cnpj, UPPER(:razaoSocial))";

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