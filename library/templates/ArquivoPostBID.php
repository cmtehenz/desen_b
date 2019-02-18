<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    header('Content-Type: text/html; charset=utf-8');

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Classes\connectMSSQL();

    /**
     * Validações e retornos
     */
    $params = filter_input_array(INPUT_POST);

    $id = $params['id'];

    $field = $params['field'];
    if ((!isset($field)) || (strlen($field) == 0)) return printf($hoUtils->alertScript(""));

    /**
     * Prepara as transações que serão executadas no banco (INSERT, UPDATE, ...)
     */
    if ($id != NULL){
        $sql = "UPDATE table SET field = :field WHERE id = :id";

        $values['id'] = $id;

        $msg = "Registro alterado com sucesso!";
    }
    else
    {
        $sql = "INSERT INTO table VALUES (:field)";

        $msg = "Registro inserido com sucesso!";
    }

    /**
     * Prepara os valores da inserção / atualização e seus filtros (bindParam do PDO) e exeuta as transações
     */
    $dbcSQL->connect();

    $values['field'] = $field;

    $result = $dbcSQL->execute($sql, $values);

    $dbcSQL->disconnect();

    if ($result) $msg = "Erro na transação: $result";

    /**
     * Recupera o caminho do arquivo que chamou este POST e retorna para ele alertando a mensagem de retorno.
     * O explode serve para remover qualquer parâmetro adjacente ao caminho do arquivo (i.e. file.php?id=1 se torna file.php)
     */
    $location = explode(".php", $_SERVER[HTTP_REFERER]);

    echo "<script language='JavaScript'>alert('$msg'); window.location = '$location[0].php';</script>";
?>