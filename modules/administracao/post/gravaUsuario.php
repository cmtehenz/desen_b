<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    header('Content-Type: text/html; charset=utf-8');

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Classes\connectMSSQL();

    /** Necessário para o alertScript personalizado */
    echo "<link rel='stylesheet' href=" . $hoUtils->getURLDestino('stylesheets/all.css') . " type='text/css' />";
    echo "<script src=" . $hoUtils->getURLDestino("js/all.js") . "></script>";

    $nome = $_POST['txtnome'];
    if ((!isset($nome)) || (strlen($nome) == 0)) return printf($hoUtils->alertScript("Informe o nome do usuário!"));

    $login = $_POST['txtlogin'];
    if ((!isset($login)) || (strlen($login) == 0)) return printf($hoUtils->alertScript("Informe o login do usuário!"));

    $senha = $_POST['txtsenha'];
    if ((!isset($senha)) || (strlen($senha) == 0)) return printf($hoUtils->alertScript("Informe a senha do usuário!"));

    $email = $_POST['txtmail'];
    if ((!isset($email)) || (strlen($email) == 0)) return printf($hoUtils->alertScript("Informe o e-mail do usuário!"));

    $dbcSQL->connect();
    $idUsuario = $dbcSQL->simpleSelect("usuario", "id_usuario", array($dbcSQL->whereParam("login", $login)));

    if ($idUsuario != null){
        $sql =
            "UPDATE usuario SET nome = '" . $nome . "', email = '" . $email . "'"
            . (($_POST["alteraSenha"] == "S") ? (", password = '" . sha1(strtolower($login) . $senha) . "' ") : "") .
            "WHERE login = '" . $login . "'";
        
        $msg = "Registro alterado com sucesso!";
    }
    else
    {
        $sql =
            "INSERT INTO usuario (nome, login, password, email)
             VALUES ('$nome','$login','" . sha1(strtolower($login) . $senha) . "','$email')";

        $msg = "Registro inserido com sucesso!";
    }

    $result = $dbcSQL->execute($sql);

    if (!$result){
        // Deletar apenas os menus do BID (que possuem cadastro de módulos)
        $delete = $dbcSQL->execute(
            "DELETE m FROM menu_usuario m WHERE m.id_usuario = $idUsuario AND
             EXISTS (SELECT 1 FROM menu u JOIN modulo o ON o.idModulo = u.idModulo WHERE u.id_menu = m.id_menu AND o.produto = 'B')");

        foreach ($_POST['menu'] as $menu){
            $insert = $dbcSQL->execute("INSERT INTO menu_usuario (id_usuario, id_menu) VALUES ($idUsuario, $menu)");

            if ($result) $msg .= "<br />Erro na inserção do menu: $menu";
        }
        
        
        $delete = $dbcSQL->execute(
            "DELETE FROM usuarioFilial WHERE idUsuario = $idUsuario");
             
        foreach($_POST['filial'] as $dados){
            $insertFilial = $dbcSQL->execute("INSERT INTO usuarioFilial (idUsuario, idFilial) VALUES ($idUsuario, $dados)");
            if ($result) $msg .= "<br />Erro na inserção de filiais: $dados";
        }

    }
    else $msg = "Erro na transação: $result";

    return printf($hoUtils->alertScript($msg, "Pronto", "window.location = '../cadastrar_usuario.php'"));
?>