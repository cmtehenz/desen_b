<?php
    namespace Modulos\Filiais\Cargas;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptMSSQL.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptSap.php';

    use Library\Classes\KeyDictionary as DD;

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();

    $_SESSION['modulo'] = basename(dirname(dirname($_SERVER['PHP_SELF'])));
    
    $listaClientes = listaCliente();
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Cadastro de Cargas </title>

        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <style type="text/css">
            #codigo { width: 60px; text-align: center; }
            #digito { width: 22px; text-align: center; }

            textarea { overflow: hidden; word-wrap: break-word; resize: none; height: 80px; max-height: 80px; }
        </style>

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
    </head>
    <body>
        <?php
        
        if(isset($_GET[idCarga])){
            //$dadosCarga = buscaCarga($idCarga);
            $editavel = "disabled='disabled'";
            $sql = mssql_query("SELECT * FROM cargas WHERE id = $_GET[idCarga]");
            while($listaFilial = mssql_fetch_array($sql)){
                $id =  $listaFilial[id];
                $nomeFilial = nomeFilialSAP($listaFilial[idFilial]);
                $tipoCte = $listaFilial[tipoCte];
                $remetente = $listaFilial[remetente];
                $destinatario = $listaFilial[destinatario];
                $cobranca = $listaFilial[cobranca];
                $redespacho = $listaFilial[redespacho];
                $expedidor = $listaFilial[expedidor];
                $placa = $listaFilial[placa];
                $idCarga = $listaFilial[id];  
                $reboque = $listaFilial[reboque];
                $semiReboque = $listaFilial[semiReboque];
                $escolta = $listaFilial[escolta];
                $fretePeso = $listaFilial[fretePeso];
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
                $observacao = $listaFilial[observacao];
                $modIcms = $listaFilial[modIcms];
                $pesoCalculo = $listaFilial[pesoCalculo];
                $contrato = $listaFilial[contrato];
                $modalidadeFrete = $listaFilial[modalidadeFrete];
            }
        }
        
        if(isset($_POST[id])){
            $idCarga = $_GET['idCarga'];
            $fretePeso = $_POST[fretePeso];
            $fretePeso = str_replace(".", "", $fretePeso);
            $fretePeso = str_replace(",", ".", $fretePeso);
            $escolta = $_POST[escolta];
            $escolta = str_replace(".", "", $escolta);
            $escolta = str_replace(",", ".", $escolta);
            $advaloren = $_POST[advaloren];
            $advaloren = str_replace(".", "", $advaloren);
            $advaloren = str_replace(",", ".", $advaloren);
            $seccat = $_POST[seccat];
            $seccat = str_replace(".", "", $seccat);
            $seccat = str_replace(",", ".", $seccat);
            $carga = $_POST[carga];
            $carga = str_replace(".", "", $carga);
            $carga = str_replace(",", ".", $carga);
            $despacho = $_POST[despacho];
            $despacho = str_replace(".", "", $despacho);
            $despacho = str_replace(",", ".", $despacho);
            $descarga = $_POST[descarga];
            $descarga = str_replace(".", "", $descarga);
            $descarga = str_replace(",", ".", $descarga);
            $gris = $_POST[gris];
            $gris = str_replace(".", "", $gris);
            $gris = str_replace(",", ".", $gris);
            $enlonamento = $_POST[enlonamento];
            $enlonamento = str_replace(".", "", $enlonamento);
            $enlonamento = str_replace(",", ".", $enlonamento);
            $adicionalEntrega = $_POST[adicionalEntrega];
            $adicionalEntrega = str_replace(".", "", $adicionalEntrega);
            $adicionalEntrega = str_replace(",", ".", $adicionalEntrega);
            $freteTotal = $_POST[freteTotal];
            $freteTotal = str_replace(".", "", $freteTotal);
            $freteTotal = str_replace(",", ".", $freteTotal);
            $pedagio = $_POST[pedagio];
            $pedagio = str_replace(".", "", $pedagio);
            $pedagio = str_replace(",", ".", $pedagio);
            $freteBrutoDesejado = $_POST[freteBrutoDesejado];
            $freteBrutoDesejado = str_replace(".", "", $freteBrutoDesejado);
            $freteBrutoDesejado = str_replace(",", ".", $freteBrutoDesejado);
            $pesoCalculo = $_POST[pesoCalculo];
            $pesoCalculo = str_replace(".", "", $pesoCalculo);
            $pesoCalculo = str_replace(",", ".", $pesoCalculo);

            if(isset($_GET[idCarga])){
                        $up = "UPDATE cargas SET tipoCte='$_POST[tipoCte]', idFilial='$_POST[filial]', remetente='$_POST[remetente]', "
                                        . "destinatario = '$_POST[destinatario]', observacao='$_POST[observacao]', "
                                        ." modalidadeFrete = '$_POST[modalidade]', redespacho =  '$_POST[redespacho]', cobranca = '$_POST[cobranca]',"
                                        . "expedidor = '$_POST[expedidor]', modIcms = '$_POST[modIcms]', pesoCalculo = '$pesoCalculo',"
                                        . "contrato = '$_POST[contrato]', placa = '$_POST[placa]', reboque = '$_POST[reboque]', semiReboque = '$_POST[semiReboque]',"
                                        . "fretePeso = '$fretePeso', escolta = '$escolta', advaloren = '$advaloren', seccat = '$seccat',"
                                        . "carga = '$carga', despacho = '$despacho', descarga = '$descarga', gris = '$gris', "
                                        . "enlonamento = '$enlonamento', adicionalEntrega = '$adicionalEntrega', freteTotal = '$freteTotal',"
                                        . "pedagio = '$pedagio', freteBrutoDesejado = '$freteBrutoDesejado', "
                                        . "responsavelSeguro = '$_POST[responsavelSeguro]' "
                                        ." WHERE id=$_GET[idCarga]";
            }
            
            if(isset($_POST[placa])){
                $up = "UPDATE cargas SET placa = '$_POST[placa]', reboque = '$_POST[reboque]', semiReboque = '$_POST[semiReboque]'"						
                      ." WHERE id=$_GET[idCarga]";
                
                
                //echo $up;
                $altera = mssql_query($up) or die (mssql_error());
                $alterado = 1;
            }
                
            
            
            
            echo "<script>
                        alert('Resgitro alterado com sucesso.');
                  </script>";
            
        }
        
        
        if(!isset($_GET[idCarga])){
            
            if(insertCarga($_POST[tipoCte], $_POST[filial], $_POST[modalidade], $_POST[remetente],
                           $_POST[destinatario], $_POST[cobranca], $_POST[redespacho], $_POST[expedidor],
                           $_POST[modIcms], $_POST[pesoCalculo], $_POST[contrato], $_POST[placa],
                           $_POST[reboque], $_POST[semiReboque], $_POST[fretePeso],
                           $_POST[escolta], $_POST[advaloren], $_POST[seccat],
                           $_POST[carga],
                           $_POST[despacho], $_POST[descarga], $_POST[gris],
                           $_POST[enlonamento], $_POST[adicionalEntrega],
                           $_POST[freteTotal], $_POST[pedagio],
                           $_POST[freteBrutoDesejado], $_POST[responsavelSeguro],
                           $_POST[observacao])){
                
                echo "<script>
                        alert('Resgitro cadastrado com sucesso.');
                  </script>";
            }
        }
	
        ?>

        <div id="wrapper">
            <div id="header">
                <h1><a href="<?php echo $hoUtils->getURLDestino("dashboard.php"); ?>">BID</a></h1>

                <a href="javascript:;" id="reveal-nav">
                    <span class="reveal-bar"></span>
                    <span class="reveal-bar"></span>
                    <span class="reveal-bar"></span>
                </a>
            </div> <!-- #header -->

            <div id="empLogo"></div>

            <?php echo $hoUtils->menuUsuario($_SESSION['idUsuario']); ?>

            <div id="content">
                <div id="contentHeader">
                    <h1><?php echo $_SESSION['nomeEmpresa']; ?></h1>
                </div> <!-- #contentHeader -->

                <div class="container">
                    <div class="grid-24">
                        <form method="post" action="#" enctype="multipart/form-data">
                            <div class="widget widget-table">
                                <div class="widget-header">
                                    <span class="icon-document-alt-stroke"></span>
                                    <h3 class="icon chart">Cadastro de cargas </h3>
                                </div>

                                <div class="widget-content">
                                    <table class="table table-bordered table-striped cadastroBID">
                                        <thead>
                                            <tr>
                                                <th>Tipo Conhecimento:</th>
                                                <td>
                                                    <select name="tipoCte"<?php echo $editavel; ?>>
                                                        <option value="normal"<?php echo $tipoCte=='normal'?'selected':'';?> >Normal</option>
                                                        <option value="substituto"<?php echo $tipoCte=='substituto'?'selected':'';?> >Subtituto</option>
                                                        <option value="complementar"<?php echo $tipoCte=='complementar'?'selected':'';?> >Complementar</option>
                                                        <option value="anulacao"<?php echo $tipoCte=='anulacao'?'selected':'';?> >Anulação</option>
                                                    </select>
                                                    </td>
                                                <th>Mod do Frete:</th>
                                                <td>
                                                    <select name="modalidade"<?php echo $editavel; ?>>
                                                        <option value="CIF"<?php echo $modalidadeFrete=='CIF'?'selected':'';?> >CIF</option>
                                                        <option value="FOB"<?php echo $modalidadeFrete=='FOB'?'selected':'';?> >FOB</option>
                                                    </select>
                                                </td>
                                                <th>Filial:</th>
                                                    <td>
                                                        <select name="filial"<?php echo $editavel; ?>>
                                                            <?php 
                                                                foreach (listaFiliais() as $dados){
                                                                    if($nomeFilial == $dados[NOME]){
                                                                        echo "<option value='$dados[ID]' selected='selected'>$dados[NOME]</option>";
                                                                    }else{
                                                                        echo "<option value='$dados[ID]'>$dados[NOME]</option>";
                                                                    }
                                                                }
                                                            ?>
                                                        </select>
                                                    </td>
                                            </tr>
                                            <tr>
                                                <th>Remetente:</th>
                                                <td colspan="6">
                                                    <select name="remetente"<?php echo $editavel; ?>>
                                                        <option value="null"></option>
                                                        <?php
                                                            foreach($listaClientes as $dadosC){
                                                                if($remetente == $dadosC[U_SIE_CNPJCPF]){
                                                                    echo "<option value='$dadosC[U_SIE_CNPJCPF]' selected = 'selected'>$dadosC[U_SIE_CNPJCPF] - $dadosC[CardName]</option>";
                                                                }else{
                                                                    echo "<option value='$dadosC[U_SIE_CNPJCPF]'>$dadosC[U_SIE_CNPJCPF] - $dadosC[CardName]</option>";
                                                                }
                                                            }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Destinatário:</th>
                                                <td colspan="6">
                                                    <select name="destinatario"<?php echo $editavel; ?>>
                                                        <option value="null"></option>
                                                        <?php
                                                            foreach($listaClientes as $dadosC){
                                                                if($destinatario == $dadosC[U_SIE_CNPJCPF]){
                                                                    echo "<option value='$dadosC[U_SIE_CNPJCPF]' selected = 'selected'>$dadosC[U_SIE_CNPJCPF] - $dadosC[CardName]</option>";
                                                                }else{
                                                                    echo "<option value='$dadosC[U_SIE_CNPJCPF]'>$dadosC[U_SIE_CNPJCPF] - $dadosC[CardName]</option>";
                                                                }
                                                            }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Cobrança:</th>
                                                <td colspan="6">
                                                    <select name="cobranca"<?php echo $editavel; ?>>
                                                        <option value="null"></option>
                                                        <?php
                                                            foreach($listaClientes as $dadosC){
                                                                if($cobranca == $dadosC[U_SIE_CNPJCPF]){
                                                                        echo "<option value='$dadosC[U_SIE_CNPJCPF]' selected = 'selected'>$dadosC[U_SIE_CNPJCPF] - $dadosC[CardName]</option>";
                                                                }else{
                                                                        echo "<option value='$dadosC[U_SIE_CNPJCPF]'>$dadosC[U_SIE_CNPJCPF] - $dadosC[CardName]</option>";
                                                                }
                                                            }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Redespacho:</th>
                                                <td colspan="6">
                                                    <select name="redespacho"<?php echo $editavel; ?>>
                                                        <option value="null"></option>
                                                        <?php
                                                            foreach($listaClientes as $dadosC){
                                                                if($redespacho == $dadosC[U_SIE_CNPJCPF]){
                                                                    echo "<option value='$dadosC[U_SIE_CNPJCPF]' selected = 'selected'>$dadosC[U_SIE_CNPJCPF] - $dadosC[CardName]</option>";
                                                                }else{
                                                                    echo "<option value='$dadosC[U_SIE_CNPJCPF]'>$dadosC[U_SIE_CNPJCPF] - $dadosC[CardName]</option>";
                                                                }
                                                            }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Expedidor:</th>
                                                <td colspan="6">
                                                    <select name="expedidor"<?php echo $editavel; ?>>
                                                        <option value="null"></option>
                                                        <?php
                                                            foreach($listaClientes as $dadosC){
                                                                if($expedidor == $dadosC[U_SIE_CNPJCPF]){
                                                                    echo "<option value='$dadosC[U_SIE_CNPJCPF]' selected = 'selected'>$dadosC[U_SIE_CNPJCPF] - $dadosC[CardName]</option>";
                                                                }else{
                                                                    echo "<option value='$dadosC[U_SIE_CNPJCPF]'>$dadosC[U_SIE_CNPJCPF] - $dadosC[CardName]</option>";
                                                                }
                                                            }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Modalidade ICMS:</th>
                                                <td>
                                                    <select name="modIcms"<?php echo $editavel; ?>>
                                                        <option value="null"<?php echo $modIcms==''?'selected':'';?> ></option>
                                                        <option value="ISENTO"<?php echo $modIcms=='ISENTO'?'selected':'';?> >ISENTO</option>
                                                        <option value="SubsTributaria"<?php echo $modIcms=='SubsTributaria'?'selected':'';?> >SUBSTITUIÇÃO TRIBUTÁRIA</option>
                                                        <option value="NORMAL"<?php echo $modIcms=='NORMAL'?'selected':'';?> >NORMAL</option>
                                                        <option value="DIFERIDO"<?php echo $modIcms=='DIFERIDO'?'selected':'';?> >DIFERIDO</option>
                                                        <option value="NaoTributado"<?php echo $modIcms==''?'selected':'NaoTributado';?> >NÃO TRIBUTADO</option>
                                                        <option value="DevidoOutrosEstados"<?php echo $modIcms=='DevidoOutrosEstados'?'selected':'';?> >DEVIDO A OUTROS ESTADOS</option>
                                                    </select>
                                                </td>
                                                <th>Peso Calculo:</th>
                                                <td colspan="6">
                                                    <input class="numeric-value" name="pesoCalculo" id="pesoCalculo" value="<?php echo number_format($pesoCalculo, 2, ',', '.'); ?>"<?php echo $editavel; ?>>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Contrato: </th>
                                                <td>
                                                    <select name="contrato"<?php echo $editavel; ?>>
                                                        <option value="null"<?php echo $contrato==''?'selected':'';?> ></option>
                                                        <option value="F"<?php echo $contrato=='F'?'selected':'';?> >FROTA</option>
                                                        <option value="A"<?php echo $contrato=='A'?'selected':'';?> >AGREGADO</option>
                                                        <option value="T"<?php echo $contrato=='T'?'selected':'';?> >TERCEIRO</option>
                                                        
                                                    </select>
                                                </td>                                               
                                            </tr>
                                            <tr>
                                                <th>Placa:</th>
                                                <td>
                                                    <input class="text" name="placa" id="placa" value="<?php echo $placa; ?>">
                                                </td>
                                                <th>Reboque:</th>
                                                <td>
                                                    <input class="text" name="reboque" id="reboque" value="<?php echo $reboque; ?>">
                                                </td>
                                                <th>Semi-Reboque:</th>
                                                <td>
                                                    <input class="text" name="semiReboque" id="semiReboque" value="<?php echo $semiReboque; ?>">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Frete Peso:</th>
                                                <td>
                                                    <input class="numeric-value" name="fretePeso" id="fretePeso" value="<?php echo number_format($fretePeso,2,",","."); ?>"<?php echo $editavel; ?>>
                                                </td>
                                                <th>Escolta:</th>
                                                <td>
                                                    <input class="numeric-value" name="escolta" id="escolta" value="<?php echo number_format($escolta, 2, ',', '.'); ?>"<?php echo $editavel; ?>>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Advaloren:</th>
                                                <td>
                                                    <input class="numeric-value" name="advaloren" id="advaloren" value="<?php echo number_format($advaloren, 2, ',', '.'); ?>"<?php echo $editavel; ?>>
                                                </td>
                                                <th>SEC/CAT:</th>
                                                <td>
                                                    <input class="numeric-value" name="seccat" id="seccat" value="<?php echo number_format($seccat, 2, ',', '.'); ?>"<?php echo $editavel; ?>>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Carga:</th>
                                                <td>
                                                    <input class="numeric-value" name="carga" id="carga"value="<?php echo number_format($carga, 2, ',', '.'); ?>"<?php echo $editavel; ?>>
                                                </td>
                                                <th>Despacho:</th>
                                                <td>
                                                    <input class="numeric-value" name="despacho" id="despacho" value="<?php echo number_format($despacho, 2, ',', '.'); ?>"<?php echo $editavel; ?>>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Descarga:</th>
                                                <td>
                                                    <input class="numeric-value" name="descarga" id="descarga" value="<?php echo number_format($descarga, 2, ',', '.'); ?>"<?php echo $editavel; ?>>
                                                </td>
                                                <th>Despacho:</th>
                                                <td>
                                                    <input class="numeric-value" name="gris" id="gris" value="<?php echo number_format($gris, 2, ',', '.'); ?>"<?php echo $editavel; ?>>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Enlonamento:</th>
                                                <td>
                                                    <input class="numeric-value" name="enlonamento" id="enlonamento" value="<?php echo number_format($enlonamento, 2, ',', '.'); ?>"<?php echo $editavel; ?>>
                                                </td>
                                                <th>Adic Entrega:</th>
                                                <td>
                                                    <input class="numeric-value" name="adicionalEntrega" id="adicionalEntrega" value="<?php echo number_format($adicionalEntrega, 2, ',', '.'); ?>"<?php echo $editavel; ?>>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Frete Total:</th>
                                                <td>
                                                    <input class="numeric-value" name="freteTotal" id="freteTotal" value="<?php echo number_format($freteTotal, 2, ',', '.'); ?>"<?php echo $editavel; ?>>
                                                </td>
                                                <th>Pedágio:</th>
                                                <td>
                                                    <input class="numeric-value" name="pedagio" id="pedagio" value="<?php echo number_format($pedagio, 2, ',', '.'); ?>"<?php echo $editavel; ?>>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Frete Bruto Desejado:</th>
                                                <td>
                                                    <input class="numeric-value" name="freteBrutoDesejado" id="freteBrutoDesejado" value="<?php echo number_format($freteBrutoDesejado, 2, ',', '.'); ?>"<?php echo $editavel; ?>>
                                                </td>
                                                <th>Responsável Seguro:</th>
                                                <td>
                                                    <select name="responsavelSeguro"<?php echo $editavel; ?>>
                                                        <option value="null"<?php echo $responsavelSeguro==''?'selected':'';?>></option>
                                                        <option value="REMETENTE"<?php echo $responsavelSeguro=='REMETENTE'?'selected':'';?>>REMETENTE</option>
                                                        <option value="EXPEDIDOR"<?php echo $responsavelSeguro=='EXPEDIDOR'?'selected':'';?>>EXPEDIDOR</option>
                                                        <option value="RECEBEDOR"<?php echo $responsavelSeguro=='RECEBEDOR'?'selected':'';?>>RECEBEDOR</option>
                                                        <option value="DESTINATARIO"<?php echo $responsavelSeguro=='DESTINATARIO'?'selected':'';?>>DESTINATÁRIO</option>
                                                        <option value="EMITENTE DO CTE"<?php echo $responsavelSeguro=='EMITENTE DO CTE'?'selected':'';?>>EMITENTE DO CTE</option>
                                                        <option value="TOMADOR DO SERVIÇO"<?php echo $responsavelSeguro=='TOMADOR DO SERVIÇO'?'selected':'';?>>TOMADOR DO SERVIÇO</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Observação:</th>
                                                <td colspan="4">
                                                    <textarea class="form-control" rows="5" style="min-width: 100%" id="observacao" name="observacao"<?php echo $editavel; ?>> <?php echo $observacao; ?></textarea>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th colspan="8">
                                                    <div align="center">
                                                        <input name="id" id="id" hidden value="<?php echo $id; ?>">
                                                        <button class="btn btn-success"><span class="icon-check"></span>Salvar</button>
                                                    </div>
                                                </th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div> <!-- .widget-content -->
                            </div> <!-- .widget -->
                        </form>
                    </div> <!-- .grid -->                    
                </div> <!-- .container -->
            </div> <!-- #content -->

            <div id="topNav">
                <ul>
                    <li>
                        <a href="#menuProfile" class="menu"><?php echo $_SESSION['nomeUsuario']; ?></a>
                        <div id="menuProfile" class="menu-container menu-dropdown">
                            <div class="menu-content">
                                <ul class="">
                                    <li><a href="javascript:;">Editar perfil</a></li>
                                    <li><a href="javascript:;">Suspender conta</a></li>
                                </ul>
                            </div>
                        </div>
                    </li>
                    <li><a href="<?php echo $hoUtils->getURLDestino("logout.php"); ?>">Sair</a></li>
                </ul>
            </div> <!-- #topNav -->
        </div> <!-- #wrapper -->

        <div id="footer">
            <div style="float: left;">Versão <?php echo $_SESSION['version']; ?></div> Copyright &copy; <?php echo date('Y'); ?>, Case Electronic Ltda.
        </div> <!-- #footer -->
    </body>
</html>