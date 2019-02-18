<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    header('Content-Type: text/html; charset=utf-8');

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Classes\connectMSSQL();

    /** Necessário para o alertScript personalizado */
    echo "<link rel='stylesheet' href=" . $hoUtils->getURLDestino('stylesheets/all.css') . " type='text/css' />";
    echo "<script src=" . $hoUtils->getURLDestino("js/all.js") . "></script>";

    $nome = $_POST[nome];
    if ((!isset($nome)) || (strlen($nome) == 0)) return printf($hoUtils->alertScript("Informe o nome do menu!"));

    $url = $_POST[url];
    if ((!isset($url)) || (strlen($url) == 0)) return printf($hoUtils->alertScript("Informe o caminho do arquivo que será usado no menu!"));

    $modulo = $_POST[modulo];

    $dbcSQL->connect();

    $idMenu = $dbcSQL->simpleSelect("menu", "id_menu", array( $dbcSQL->whereParam('url', $url), $dbcSQL->whereParam('idModulo', $modulo) ));

    if ($_POST['button'] == "salvar"){
        if ($idMenu != null){
            $sql = "UPDATE menu SET
                        nome = '$nome', url = '$url', idModulo = $modulo
                    WHERE id_menu = $idMenu";

            $msg = "Registro alterado com sucesso!";
        }
        else
        {
            $fetch = $dbcSQL->selectTopOne("SELECT TOP 1 COALESCE(ordenacao, 0) + 1 result FROM menu WHERE idModulo = $modulo ORDER BY ordenacao DESC");

            $ordenacao = $fetch[result] ?: 1;

            $sql = "INSERT INTO menu (nome, url, idModulo, ordenacao)
                    VALUES ('$nome','$url',$modulo,$ordenacao)";

            $msg = "Registro inserido com sucesso!";
        }
    }
    else
    {
        if ($idMenu != null){
            $sql = "DELETE FROM menu_usuario WHERE id_menu = $idMenu;
                    DELETE FROM menu WHERE id_menu = $idMenu";

            $msg = "Registro excluído com sucesso!";
        }
        else $msg = "Não há registro para exclusão!";
    }

    $dbcSQL->execute($sql);

    $dbcSQL->disconnect();

    return printf($hoUtils->alertScript($msg, "Pronto", "window.location = '../cadastrar_menu.php'"));
?>