<?php

//echo $_SERVER['HTTP_HOST'];
$pdf        = $_GET['link'];
$usuario    = $_GET['usuario'];
$idUsuario  = $_GET['idUsuario'];
$data       = date('Y-m-d H:i:s');
if(false){
    //bloqueia usuario
    
}else{
    //salva o log do usuário
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
        
    $ip         = getenv('REMOTE_ADDR');
    $pagina     = $pdf;
        
    $sql = "INSERT INTO logAcesso ( ipOrigem, pagina, usuario, idUsuario, data ) "
            . "VALUES ('$ip' , '$pagina', '$usuario', '$idUsuario', '$data')";
    
    $reseult = mssql_query($sql);
    
    //redireciona usuário para o arquivo pdf.
    header("Location: $pdf");
}





