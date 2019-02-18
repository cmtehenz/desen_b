<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    header('Content-type: application/json');

    $dbcSQL = new \Library\Classes\connectMSSQL();
    $dbcSQL->connect();

    $json = file_get_contents('php://input');

    $listaModulos = json_decode($json, true);

    foreach ($listaModulos as $modulo) {
        if ($modulo['id'] != 0)
            $sql = "UPDATE modulo SET
                        nome      = '$modulo[nome]',
                        ordenacao =  $modulo[ordenacao],
                        url       = '$modulo[url]'
                    WHERE idModulo = $modulo[id]";
        else if ($modulo['url'] != "URL")
            $sql = "INSERT INTO modulo (nome, ordenacao, url)
                    VALUES (
                        '$modulo[nome]',
                         $modulo[ordenacao],
                        '$modulo[url]'
                    )";
    
        $dbcSQL->execute($sql);
    }

    $dbcSQL->disconnect();
?>