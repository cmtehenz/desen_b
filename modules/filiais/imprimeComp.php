<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));
?>
        <?php
            include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptSap.php';
            include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
            //include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptMSSQL.php';

            /**********************************
             *   VARIAVEIS                    *
             * ********************************/
            date_default_timezone_set('America/sao_paulo');
            $wanted_week = date('W');
            $abrev_mes = date('M');
            $nome_mes = date('F');
            $dia = date('d');
            $ano = date('Y');
            $mes_atual = date('m');
            $mes = date('m');
            $imob = 0;
            $idUsuario = $_SESSION[idUsuario];

            if (isset($_POST['ano'])){
                $ano = $_POST['ano'];
            }
            if (isset($_POST['mes'])){
                $mes_atual = $_POST['mes'];
                $mes = $_POST['mes'];
            }
            if (isset($_POST['imob'])){
                $imob = $_POST['imob'];
            }

            /******************************************/
            /******************************************/
           // $sql = mssql_query("SELECT * FROM usuarioFilial WHERE idUsuario=$idUsuario");
            
            if(isset($_POST['semPlaca'])){
                $cond = " 1=1 ";  
            }else{
                $cond = " (placa = ' ' or placa = '') ";
            }
            if($_POST['filialBusca']){
                $cond = $cond . " and idFilial = " . $_POST['filialBusca'];       
            }
            $sql = mssql_query("SELECT * FROM cargas WHERE  $cond order by 1");
            while($listaFilial = mssql_fetch_array($sql)){
                $nomeFilial = nomeFilialSAP($listaFilial[idFilial]);
                $tipoCte = $listaFilial[tipoCte];
                $remetente = $listaFilial[remetente];
                $destinatario = $listaFilial[destinatario];
                $cobranca = $listaFilial[cobranca];
                $redespacho = $listaFilial[redespacho];
                $expedidor = $listaFilial[expedidor];
                $placa = $listaFilial[placa];
                $idCarga = $listaFilial[id];
                
                $linhaTabela = $linhaTabela . "<tr class='gradeA'>
                                                <td>".$nomeFilial."</td>
                                                <td>".$tipoCte."</td>
                                                <td>".$remetente."</td>
                                                <td>".$destinatario."</td>
                                                <td>".$cobranca."</td>
                                                <td>".$redespacho."</td>
                                                <td>".$expedidor."</td>
                                                <td>".$placa."</td>
                                        </tr>";
                
            }
        ?>

<!doctype html>
<html>
<font size="3">
<head>
<meta charset="utf-8">

<style type="text/css">
img { float:left; }
	body{
		font-size:10px;
	}
	#geral{
		width: 1527px;
		margin: 0 auto;
	}
	#topo {
		border: 1px solid #000;
		margin-bottom: 10px;
	}
	.requisitante{
	 	border: 1px solid;
	}
	.informacao{
		height: 15px;
		background-color: #CCC;
		font-size: 10px;
		text-align:center;
	}
	.exames{
		height: 20px;
		background-color: #CCC;
		border-top: 1px solid;
		border-left: 1px solid;
		border-right: 1px solid;
		font-size: 14px;
		text-align:center;
		margin-top:20px;
	}
	#identificacao{
		background-color:#CCC;
		width:360px;
		border-color: #000;
		border-top: 1px solid;
		border-right: 1px solid;
		border-bottom: 1px solid;
		border-left: 3px solid;
		float:right;
		margin-top: 20px;
		font-size: 10px;
	}
	.titulos{
		margin-top: 20px;
	 	border: 1px solid;
	}
	#assinatura{
		margin-top: 5%;
		width: 100%;
	}
	.historico{
	 	border: 1px solid;
		height: 60px;
	}
	#exames_solicitados{
		font-size:10px;
	}

	#rodape{
		font-size:9px;
	}

	table.linhasimples {border-collapse: collapse;}
	table.linhasimples tr td {border-top:1px solid #000000;}
	table.bordasimples {border-collapse: collapse;}
	table.bordasimples tr td {border:1px solid #000000;}
</style>

</head>

<body>

<script>
    window.print();
</script>

<div style="width:100%; float:left;">
    <?php
        $sql = mssql_query("SELECT * FROM cargas WHERE  $cond order by 1");
        while($listaFilial = mssql_fetch_array($sql)){
            $nomeFilial = nomeFilialSAP($listaFilial[idFilial]);
            $tipoCte = $listaFilial[tipoCte];
            $remetente = $listaFilial[remetente];
            $destinatario = $listaFilial[destinatario];
            $cobranca = $listaFilial[cobranca];
            $redespacho = $listaFilial[redespacho];
            $expedidor = $listaFilial[expedidor];
            $placa = $listaFilial[placa];
            $idCarga = $listaFilial[id];

            $linhaTabela = $linhaTabela . "<tr class='gradeA'>
                                            <td>".$nomeFilial."</td>
                                            <td>".$tipoCte."</td>
                                            <td>".$remetente."</td>
                                            <td>".$destinatario."</td>
                                            <td>".$cobranca."</td>
                                            <td>".$redespacho."</td>
                                            <td>".$expedidor."</td>
                                            <td>".$placa."</td>
                                    </tr>";

        }
        echo ">>>>>>>>>>>>>>> TESTE <<<<<<<<<<<<<<<<<";
    
    ?>	
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>FILIAL</th>
                <th>TIPO </th>
                <th>REMETENTE</th>
                <th>DESTINATÁRIO</th>
                <th>COBRANÇA</th>
                <th>REDESPACHO</th>
                <th>EXPEDITOR</th>
                <th>PLACA</th>
            </tr>
        </thead>
        <tbody>
        <?php echo $linhaTabela; ?>
        </tbody>
    </table>
</div>
</div>
<!--
<script>
    setTimeout("window.close();", 10);
</script>
-->
</body>

</html>
