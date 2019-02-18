<?php
include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
if($cone_mssqlZap){
    echo "conectado";
}
$sql = mssql_query("select cast(BPLId as int) from obpl");
while ($dados = mssql_fetch_array($sql)){
    echo $dados[0];
}

?>  