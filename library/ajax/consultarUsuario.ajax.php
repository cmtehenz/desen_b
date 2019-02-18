<?php
    header('Cache-Control: no-cache');

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $dbcSQL  = new \Library\Classes\connectMSSQL();

    $sqlUsuario = "SELECT TOP 1 u.id_usuario idUsuario, u.nome, u.login, u.password, u.email FROM usuario u WHERE u.login ='" . $_GET['username'] . "'";

    $dbcSQL->connect();

    $result = $dbcSQL->select($sqlUsuario);

    $arrayDados = null;

    foreach ($result as $dadosUsuario){
        $arrayDados = $dadosUsuario;

        $resultMenus = $dbcSQL->select("SELECT m.id_menu idMenu FROM menu_usuario m WHERE m.id_usuario = " . $dadosUsuario['idUsuario'] . "");

        foreach ($resultMenus as $listaMenus)
            $arrayMenu[count($arrayMenu)] = $listaMenus['idMenu'];

        $arrayDados['menus'] = $arrayMenu;
    }

    $dbcSQL->disconnect();

    echo json_encode($arrayDados);
?>