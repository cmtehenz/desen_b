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

    $field = $params['descricao'];
    if ((!isset($field)) || (strlen($field) == 0)) return printf($hoUtils->alertScript("Informe uma descrição para a infração!"));

    $field = $params['codigo'];
    if ((!isset($field)) || ($field == 0) || (!is_numeric($field))) return printf($hoUtils->alertScript("Código da infração inválido!"));

    $field = $params['digito'];
    if ((!isset($field)) || (!is_numeric($field))) return printf($hoUtils->alertScript("Dígito do código de infração inválido!"));

    /** Caso já exista o código informado, encontramos o registro para atualização */
    $dbcSQL->connect();

    $params['codigo'] = str_pad(trim($params['codigo']), 3, '0', STR_PAD_LEFT);
    $params['digito'] = str_pad(trim($params['digito']), 2, '0', STR_PAD_LEFT);

    $filter = array($dbcSQL->whereParam("codigo", $params['codigo']), $dbcSQL->whereParam("digito", $params['digito']));

    $params['id'] = $params['id'] ?: $dbcSQL->simpleSelect("mlt.infracao", "idInfracao", $filter);

    /**
     * Prepara as transações que serão executadas no banco (INSERT, UPDATE, ...)
     */
    if ($params['id'] != null){
        $sql = "UPDATE mlt.infracao SET
                    codigo = :codigo, digito = :digito, descricao = UPPER(:descricao), classificacao = :classificacao
                WHERE idInfracao = :id";

        $msg = "Registro alterado com sucesso!";
    }
    else
    {
        $sql = "INSERT INTO mlt.infracao (codigo, digito, descricao, classificacao) VALUES (:codigo, :digito, UPPER(:descricao), :classificacao)";

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