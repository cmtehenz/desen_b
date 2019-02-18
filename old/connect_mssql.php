<?php
    $host        = "192.168.0.18";
    $user        = "sa";
    $passwd      = "r9r7x9m6";
    $basededados = "bi";

    $cone_mssql = mssql_connect($host, $user, $passwd);

    if(!mssql_select_db($basededados, $cone_mssql))
    {
        echo "Problemas ao conectar o banco de dados, favor informar o administrador do sistema')";
    }
?>
