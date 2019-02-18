<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));
?>
<!doctype html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Receita por contrato</title>

        <meta charset="utf-8" />
    </head>

    <body>
        <?php
            include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
            include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_db2_bino.php';
            include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptdb2.php';
            include $_SERVER['DOCUMENT_ROOT'] . '/old/funcoes.php';

        $sql = "SELECT FILIAL.SIGLA_FILIAL, CT.NUMERO, CT.DATAEMISSAO, HCLIENTE.CNPJ_CPF, HCLIENTE.RAZAO_SOCIAL, CT.VALTOTFRETE, CT.VALFPESO, CT.VALFPESOSICMS,CT.VALPED,CT.VALPEDSICMS,CT.VALADVALOREM,CT.VALICMS,CT.ALIQICMS, HDEST.CNPJ_CPF DESTINATARIOCNPJ_CPF, HDEST.RAZAO_SOCIAL DESTINATARIORAZAO_SOCIAL
        FROM CT 
            JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = CT.IDHCLIENTE)
            JOIN HCLIENTE HDEST ON (HDEST.IDHCLIENTE = CT.IDHCLIENTEDEST) 
            JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
            WHERE CT.STATUSCT <> 'C' AND CT.DATAEMISSAO BETWEEN '01/01/2016' AND '31/12/2016'"     ;
        $consulta = db2_exec($hDbcDB2, $sql);
        while($dados = db2_fetch_array($consulta)){
            echo $dados[0];
            echo ";";
            echo $dados[1];
            echo ";";
            echo $dados[2];
            echo ";";
            echo $dados[3];
            echo ";";
            echo $dados[4];
            echo ";";
            echo $dados[5];
            echo ";";
            echo $dados[6];
            echo ";";
            echo $dados[7];
            echo ";";
            echo $dados[8];
            echo ";";
            echo $dados[9];
            echo ";";
            echo $dados[10];
            echo ";";
            echo $dados[11];
            echo ";";
            echo $dados[12];
            echo ";";
            echo $dados[13];
            echo ";";
            echo $dados[14];
            echo ";";
            echo "<br>";
        }
        ?>
        
</body>
</html>