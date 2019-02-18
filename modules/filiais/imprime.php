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
            
            $cond = " id = " . $_GET['idCarga'];  
            
            $sql = mssql_query("SELECT * FROM cargas WHERE  $cond order by 1");
            while($listaFilial = mssql_fetch_array($sql)){
                $nomeFilial = nomeFilialSAP($listaFilial[idFilial]);
                $tipoCte = $listaFilial[tipoCte];
                $remetente = nomeClienteSAP($listaFilial[remetente]) . " - " . $listaFilial[remetente];
                $destinatario =  nomeClienteSAP($listaFilial[destinatario]) . " - " . $listaFilial[destinatario];
                $cobranca =  nomeClienteSAP($listaFilial[cobranca]) . " - " . $listaFilial[cobranca];
                if((nomeClienteSAP($listaFilial[cobranca]) == 'null') or ($listaFilial[cobranca] == 'null')){
                    $cobranca = " ";
                }
                $redespacho = $listaFilial[redespacho];
                if($redespacho == 'null'){
                    $redespacho = " ";
                }
                $expedidor = $listaFilial[expedidor];
                if($expedidor == 'null'){
                    $expedidor = " ";
                }
                $placa = $listaFilial[placa];
                if($placa == 'null'){
                    $placa = " ";
                }
                $reboque = $listaFilial[reboque];
                if($reboque == 'null'){
                    $reboque = " ";
                }
                $semireboque = $listaFilial[semireboque];
                if($semireboque == 'null'){
                    $semireboque = " ";
                }
                $idCarga = $listaFilial[id];
                $modIcms = $listaFilial[modIcms];
                if($modIcms == 'null'){
                    $modIcms = " ";
                }
                $contrato = $listaFilial[contrato];
                if($contrato == "A"){
                    $contrato = "Agregado";
                }elseif($contrato == "T"){
                    $contrato = "Terceiro";
                }elseif($contrato == "F"){
                    $contrato = "Frota";
                }else{
                    $contrato = "";
                }
                $modalidadeFrete = $listaFilial[modalidadeFrete];
                $pesoCalculo = $listaFilial[pesoCalculo];
                $fretePeso = $listaFilial[fretePeso];
                $escolta = $listaFilial[escolta];
                $advaloren = $listaFilial[advaloren];
                $seccat = $listaFilial[seccat];
                $carga = $listaFilial[carga];
                $despacho = $listaFilial[despacho];
                $descarga = $listaFilial[descarga];
                $gris = $listaFilial[gris];
                $enlonamento = $listaFilial[enlonamento];
                $adicionalEntrega = $listaFilial[adicionalEntrega];
                $freteTotal = $listaFilial[freteTotal];
                $pedagio = $listaFilial[pedagio];
                $freteBrutoDesejado = $listaFilial[freteBrutoDesejado];
                $responsavelSeguro = $listaFilial[responsavelSeguro];
                $agendamentoCliente = $listaFilial[agendamentoCliente];
                $observacao = $listaFilial[observacao];
                $decricaoFormaPagamentoBipe = $listaFilial['descricaoSAPOCTG'];
                $numPedido = $listaFilial['numPedido'];

                if($responsavelSeguro == 'null'){
                    $responsavelSeguro = " ";
                }                
                
                $partes = explode("/", $agendamentoCliente);
                $dia = $partes[0];
                $mes = $partes[1];
                $ano = $partes[2];
                //$agendamentoCliente = $dia . "/" . $mes . "/" . $ano;
                //$agendamentoCliente = date('d/m/Y H:i:s', strtotime($agendamentoCliente)); 
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
    <table align="right">
            <tr>
                <td> <font size="1"> <?php echo date('d') . "/" . date('m') . "/" . date('Y');?></td>
            </tr>
     </table>
    <br>
     <table align="center">
            <tr>
                <td> <font size="5"><b> Transporte de Cargas Zappellini </b></font></td>
            </tr>
     </table>
    <HR>            
    <br>Num Pedido: <b><?php echo $numPedido; ?></b>
    <br>Filial: <b><?php echo $nomeFilial; ?></b> - Tipo: <b><?php echo $tipoCte; ?></b> - Mod do Frete: <b><?php echo $modalidadeFrete; ?></b>
    <br>Remetente: <b><?php echo $remetente; ?></b>
    <br>Destinatário: <b><?php echo $destinatario; ?></b>
    <br>Cobrança: <b><?php echo $cobranca; ?></b>
    <br>Redespacho: <b><?php echo $redespacho; ?></b>                
    <br>Expedidor: <b><?php echo $expedidor; ?></b> - Mod ICMS: <b><?php echo $modIcms; ?></b> - Contrato: <b><?php echo $contrato; ?></b>
    <br>Placa: <b><?php echo $placa; ?></b> - Reboque: <b><?php echo $reboque; ?></b> - Semi-reboque: <b><?php echo $semireboque; ?></b>
    <br>Peso Cálculo: <b><?php echo $pesoCalculo; ?></b>  
    <br>Frete Peso: <b><?php echo $fretePeso; ?></b>  
    <br>Escolta: <b><?php echo $escolta; ?></b>  
    <br>Advaloren: <b><?php echo $advaloren; ?></b>
    <br>SEC/CAT: <b><?php echo $seccat; ?></b>  
    <br>Carga: <b><?php echo $carga; ?></b>
    <br>Despacho: <b><?php echo $despacho; ?></b>
    <br>Descarga: <b><?php echo $descarga; ?></b>
    <br>Gris: <b><?php echo $gris; ?></b>
    <br>Advaloren: <b><?php echo $advaloren; ?></b>
    <br>Enlonamento: <b><?php echo $enlonamento; ?></b>
    <br>Adicional Entrega: <b><?php echo $adicionalEntrega; ?></b>
    <br>Frete Total: <b><?php echo $freteTotal; ?></b>
    <br>Pedágio: <b><?php echo $pedagio; ?></b>
    <br>Frete Bruto Desejado: <b><?php echo $freteBrutoDesejado; ?></b>
    <br>Responsável Seguro: <b><?php echo $responsavelSeguro; ?></b>
    <br>Forma de Pagament Bipe: <b><?php echo $decricaoFormaPagamentoBipe; ?></b>
    <br>Agendamento Cliente: <b><?php echo date('d/m/Y H:i:s', strtotime($agendamentoCliente)); ?></b>
    <br>Observação: <b><?php echo $observacao; ?></b>
    
</div>
</div>
<!--
<script>
    setTimeout("window.close();", 10);
</script>
-->
</body>

</html>
