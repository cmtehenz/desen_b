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
            $idCarga = $_GET['idCarga'];
            $dadosCarga = buscaCarga($idCarga);
        }
        
        if($_POST[tipoCte]){
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
                                                    <select name="tipoCte">
                                                        <option value="normal">Normal</option>
                                                        <option value="substituto">Subtituto</option>
                                                        <option value="complementar">Complementar</option>
                                                        <option value="anulacao">Anulação</option>
                                                    </select>
                                                    </td>
                                                <th>Mod do Frete:</th>
                                                <td>
                                                    <select name="modalidade">
                                                        <option value="CIF">CIF</option>
                                                        <option value="FOB">FOB</option>
                                                    </select>
                                                </td>
                                                <th>Filial:</th>
                                                    <td>
                                                        <select name="filial">
                                                            <?php 
                                                                foreach (listaFiliais() as $dados){
                                                                    echo "<option value='$dados[ID]'>$dados[NOME]</option>";
                                                                }
                                                            ?>
                                                        </select>
                                                    </td>
                                            </tr>
                                            <tr>
                                                <th>Remetente:</th>
                                                <td colspan="6">
                                                    <select name="remetente">
                                                        <option value="null"></option>
                                                        <?php
                                                            foreach($listaClientes as $dadosC){
                                                                echo "<option value='$dadosC[U_SIE_CNPJCPF]'>$dadosC[U_SIE_CNPJCPF] - $dadosC[CardName]</option>";
                                                            }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Destinatário:</th>
                                                <td colspan="6">
                                                    <select name="destinatario">
                                                        <option value="null"></option>
                                                        <?php
                                                            foreach($listaClientes as $dadosC){
                                                                echo "<option value='$dadosC[U_SIE_CNPJCPF]'>$dadosC[U_SIE_CNPJCPF] - $dadosC[CardName]</option>";
                                                            }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Cobrança:</th>
                                                <td colspan="6">
                                                    <select name="cobranca">
                                                        <option value="null"></option>
                                                        <?php
                                                            foreach($listaClientes as $dadosC){
                                                                echo "<option value='$dadosC[U_SIE_CNPJCPF]'>$dadosC[U_SIE_CNPJCPF] - $dadosC[CardName]</option>";
                                                            }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Redespacho:</th>
                                                <td colspan="6">
                                                    <select name="redespacho">
                                                        <option value="null"></option>
                                                        <?php
                                                            foreach($listaClientes as $dadosC){
                                                                echo "<option value='$dadosC[U_SIE_CNPJCPF]'>$dadosC[U_SIE_CNPJCPF] - $dadosC[CardName]</option>";
                                                            }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Expedidor:</th>
                                                <td colspan="6">
                                                    <select name="expedidor">
                                                        <option value="null"></option>
                                                        <?php
                                                            foreach($listaClientes as $dadosC){
                                                                echo "<option value='$dadosC[U_SIE_CNPJCPF]'>$dadosC[U_SIE_CNPJCPF] - $dadosC[CardName]</option>";
                                                            }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Modalidade ICMS:</th>
                                                <td>
                                                    <select name="modIcms">
                                                        <option value="null"></option>
                                                        <option value="ISENTO">ISENTO</option>
                                                        <option value="SubsTributaria">SUBSTITUIÇÃO TRIBUTÁRIA</option>
                                                        <option value="NORMAL">NORMAL</option>
                                                        <option value="DIFERIDO">DIFERIDO</option>
                                                        <option value="NaoTributado">NÃO TRIBUTADO</option>
                                                        <option value="DevidoOutrosEstados">DEVIDO A OUTROS ESTADOS</option>
                                                    </select>
                                                </td>
                                                <th>Peso Calculo:</th>
                                                <td colspan="6">
                                                    <input class="numeric-value" name="pesoCalculo" id="pesoCalculo" value="<?php echo number_format($dadosCarga['PESOCALCULO'], 2, '.', ','); ?>">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Contrato:</th>
                                                <td>
                                                    <select name="contrato">
                                                        <option value="null"></option>
                                                        <option value="F">FROTA</option>
                                                        <option value="A">AGREGADO</option>
                                                        <option value="T">TERCEIRO</option>
                                                        
                                                    </select>
                                                </td>                                               
                                            </tr>
                                            <tr>
                                                <th>Placa:</th>
                                                <td>
                                                    <input class="text" name="placa" id="placa" disabled="disabled">
                                                </td>
                                                <th>Reboque:</th>
                                                <td>
                                                    <input class="text" name="reboque" id="semiReboque" disabled="disabled">
                                                </td>
                                                <th>Semi-Reboque:</th>
                                                <td>
                                                    <input class="text" name="semiReboque" id="semiReboque" disabled="disabled">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Frete Peso:</th>
                                                <td>
                                                    <input class="numeric-value" name="fretePeso" id="fretePeso">
                                                </td>
                                                <th>Escolta:</th>
                                                <td>
                                                    <input class="numeric-value" name="escolta" id="escolta">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Advaloren:</th>
                                                <td>
                                                    <input class="numeric-value" name="advaloren" id="advaloren">
                                                </td>
                                                <th>SEC/CAT:</th>
                                                <td>
                                                    <input class="numeric-value" name="seccat" id="seccat">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Carga:</th>
                                                <td>
                                                    <input class="numeric-value" name="carga" id="carga">
                                                </td>
                                                <th>Despacho:</th>
                                                <td>
                                                    <input class="numeric-value" name="despacho" id="despacho">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Descarga:</th>
                                                <td>
                                                    <input class="numeric-value" name="descarga" id="descarga">
                                                </td>
                                                <th>Despacho:</th>
                                                <td>
                                                    <input class="numeric-value" name="gris" id="gris">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Enlonamento:</th>
                                                <td>
                                                    <input class="numeric-value" name="enlonamento" id="enlonamento">
                                                </td>
                                                <th>Adic Entrega:</th>
                                                <td>
                                                    <input class="numeric-value" name="adicionalEntrega" id="adicionalEntrega">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Frete Total:</th>
                                                <td>
                                                    <input class="numeric-value" name="freteTotal" id="freteTotal">
                                                </td>
                                                <th>Pedágio:</th>
                                                <td>
                                                    <input class="numeric-value" name="pedagio" id="pedagio">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Frete Bruto Desejado:</th>
                                                <td>
                                                    <input class="numeric-value" name="freteBrutoDesejado" id="freteBrutoDesejado">
                                                </td>
                                                <th>Responsável Seguro:</th>
                                                <td>
                                                    <select name="responsavelSeguro">
                                                        <option value="null"></option>
                                                        <option value="REMETENTE">REMETENTE</option>
                                                        <option value="EXPEDIDOR">EXPEDIDOR</option>
                                                        <option value="RECEBEDOR">RECEBEDOR</option>
                                                        <option value="DESTINATARIO">DESTINATÁRIO</option>
                                                        <option value="EMITENTE DO CTE">EMITENTE DO CTE</option>
                                                        <option value="TOMADOR DO SERVIÇO">TOMADOR DO SERVIÇO</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Observacão:</th>
                                                <td colspan="4">
                                                    <textarea class="form-control" rows="5" style="min-width: 100%" id="observacao" name="observacao"></textarea>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th colspan="8">
                                                    <div align="center">
                                                        <input name="id" id="id" hidden value="<?php echo $registro['idInfracao']; ?>">
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