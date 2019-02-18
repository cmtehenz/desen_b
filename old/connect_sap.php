<?php
    $host        = "zapsapdb";
    $user        = "sa";
    $passwd      = "siesanta_123";
    $basededados = "SBO_Zappellini";

    $cone_mssqlZap  = mssql_connect($host, $user, $passwd);

    if(!mssql_select_db($basededados, $cone_mssqlZap))
    {
        echo "Problemas ao conectar o banco de dados SAP, favor informar o administrador do sistema. ";
    }
?>
