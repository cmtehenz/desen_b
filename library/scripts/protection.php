<?php
    session_start();

    if(!isset($_SESSION['idUsuario'])){
        header("Location: /");
    }else{
        include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
        
        $data       = date('Y-m-d H:i:s');
        $idUsuario  = $_SESSION['idUsuario'];
        $ip         = getenv('REMOTE_ADDR');
        $usuario    = $_SESSION['nomeUsuario'];
        $pagina     = $_SERVER['REQUEST_URI'];
        
        $sql = "INSERT INTO logAcesso ( ipOrigem, pagina, usuario, idUsuario, data ) "
            . "VALUES ('$ip' , '$pagina', '$usuario', '$idUsuario', '$data')";
        
            
        $reseult = mssql_query($sql);
    }
?>