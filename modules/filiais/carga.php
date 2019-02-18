<?php
    namespace Modulos\Filiais\Cargas;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptMSSQL.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptSap.php';

    use Library\Classes\KeyDictionary as DD;

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();

    $_SESSION['modulo'] = basename(dirname(dirname($_SERVER['PHP_SELF'])));
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Cadastro de Cargas </title>

        <meta http-equiv="Content-type" content="text/html" charset="UTF-8" />
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
        <script src="<?php echo $hoUtils->getURLDestino("library/javascripts/jquery.maskMoney.js"); ?>"></script>
        <script type="text/javascript">
            $(document).ready(function(){
                $('.valor')
                    .maskMoney( {thousands: ""} )
                    .on("click", function (){
                        $(this).select();
                    });
            });
        </script>
    </head>
    <body>
        <?php
        if(isset($_POST[updateIdCarga])){
            echo "<script>
                    alert('Altera o registro.');
                  </script>";
            //unset($_GET[idCarga]);
            if(alteraCarga($_POST[updateIdCarga], $_POST[contrato], $_POST[placa])){
                echo "<script>
                        alert('Registro Alterado com SUCESSO.');
                      </script>";
            }else{
                echo "<script>
                        alert('ERRO! Favor entrar em contato com o Administrador do sistema.');
                      </script>";
            }
        }
        
        
        if(isset($_GET[idCarga])){
            $idCarga = $_GET['idCarga'];
            $dadosCarga = buscaCarga($idCarga);
            
            $naoEditavel = "disabled='disabled'";
            $editarPlaca = null;
            $campoId = "<input type='hidden' name='updateIdCarga' value='$idCarga' >";
            
            $opcoesContrato = "<option value='null'></option>
                               <option value='F'>FROTA</option>
                               <option value='A'>AGREGADO</option>
                               <option value='T'>TERCEIRO</option>";
            echo "<script>
                    alert('Carregando dados.');
                  </script>";
        }else{
            $listaClientes = listaCliente();
            $campoId = null;
            $opcoesContrato = "<option value='null'></option>
                               <option value='T'>TERCEIRO</option>";
            
        }
        
        if(!isset($_POST['idCarga']) and $_POST['filial'] != NULL){
            if($_POST[idFormaPagamento] != 0){
                $descricaoFormaPagamentoBipe = buscaNomeFormaPagamentoBipe($_POST[idFormaPagamento]);
            }else{
                $descricaoFormaPagamentoBipe=0;
            }
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
                           $_POST[observacao], $_POST[dataAgendamentoCliente], $_POST[horaAgendamentoCliente],
                           $_POST[idFormaPagamento], $descricaoFormaPagamentoBipe, $_POST[numPedido])){
                
                echo "<script>
                            alert('Resgitro cadastrado com sucesso.');
                      </script>";
            }else{
                echo "<script>
                            alert('ERRO.Problemas para salvar o registro.');
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
                                                <th>Num Pedido:</th>
                                                <td>
                                                    <input class="text" name="numPedido" value="<?php if(isset($dadosCarga[NUMPEDIDO])){ echo $dadosCarga[NUMPEDIDO]; } ?>" id="numPedido" <?php echo $naoEditavel; ?>>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Tipo Cte:</th>
                                                <td>
                                                    <select name="tipoCte" <?php echo $naoEditavel; ?> >
                                                        <option value="normal" <?php echo $dadosCarga[TIPOCTE]=='normal'?'selected':'';?> >NORMAL</option>
                                                        <option value="substituto" <?php echo $dadosCarga[TIPOCTE]=='substituto'?'selected':'';?> >SUBSTITUTO</option>
                                                        <option value="complementar" <?php echo $dadosCarga[TIPOCTE]=='complementar'?'selected':'';?> >COMPLEMENTAR</option>
                                                        <option value="anulacao" <?php echo $dadosCarga[TIPOCTE]=='anulacao'?'selected':'';?> >ANULAÇÃO</option>
                                                    </select>
                                                    </td>
                                                <th>Mod do Frete:</th>
                                                <td>
                                                    <select name="modalidade" <?php echo $naoEditavel; ?>>
                                                        <option value="CIF" <?php echo $dadosCarga[MODALIDADE]=='CIF'?'selected':'';?>>CIF</option>
                                                        <option value="FOB" <?php echo $dadosCarga[MODALIDADE]=='FOB'?'selected':'';?>>FOB</option>
                                                    </select>
                                                </td>
                                                <th>Filial:</th>
                                                    <td>
                                                        <select name="filial" required="required" <?php echo $naoEditavel; ?> >
                                                            <option></option>
                                                            <?php 
                                                                foreach (listaFiliais() as $dados){
                                                                    if($dadosCarga['IDFILIAL'] == $dados['ID']){
                                                                        $selected = "selected";
                                                                    }else{
                                                                        $selected = null;
                                                                    }
                                                                        
                                                                    echo "<option value='$dados[ID]' ".$selected.">$dados[NOME]</option>";
                                                                }
                                                            ?>
                                                        </select>
                                                    </td>
                                            </tr>
                                            <tr>
                                                <th>Remetente:</th>
                                                <td colspan="6">
                                                    <input class="text" name="remetente" id="remetente" value="<?php if(isset($dadosCarga[REMETENTE])){ echo $dadosCarga[REMETENTE]; } ?>" <?php echo $naoEditavel; ?> >                                                    
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Destinatário:</th>
                                                <td colspan="6">
                                                    <input class="text" name="destinatario" id="destinatario" value="<?php if(isset($dadosCarga[DESTINATARIO])){ echo $dadosCarga[DESTINATARIO]; } ?>" <?php echo $naoEditavel; ?> >
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Cobrança:</th>
                                                <td colspan="6">
                                                    <input class="text" name="cobranca" id="cobranca" value="<?php if(isset($dadosCarga[COBRANCA])){ echo $dadosCarga[COBRANCA]; } ?>" <?php echo $naoEditavel; ?> >
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Redespacho:</th>
                                                <td colspan="6">
                                                    <input class="text" name="redespacho" id="redespacho" value="<?php if(isset($dadosCarga[REDESPACHO])){ echo $dadosCarga[REDESPACHO]; } ?>" <?php echo $naoEditavel; ?> >
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Expedidor:</th>
                                                <td colspan="6">
                                                    <input class="text" name="expedidor" id="expedidor" value="<?php if(isset($dadosCarga[EXPEDIDOR])){ echo $dadosCarga[EXPEDIDOR]; } ?>" <?php echo $naoEditavel; ?> >
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Modalidade ICMS:</th>
                                                <td>
                                                    <select name="modIcms" <?php echo $naoEditavel; ?>>
                                                        <option value="null"></option>
                                                        <option value="ISENTO" <?php echo $dadosCarga[MODICMS]=='ISENTO'?'selected':''; ?>>ISENTO</option>
                                                        <option value="SubsTributaria" <?php echo $dadosCarga[MODICMS]=='SubsTributaria'?'selected':''; ?>>SUBSTITUIÇÃO TRIBUTÁRIA</option>
                                                        <option value="NORMAL" <?php echo $dadosCarga[MODICMS]=='NORMAL'?'selected':''; ?>>NORMAL</option>
                                                        <option value="DIFERIDO" <?php echo $dadosCarga[MODICMS]=='DIFERIDO'?'selected':''; ?>>DIFERIDO</option>
                                                        <option value="NaoTributado" <?php echo $dadosCarga[MODICMS]=='NaoTributado'?'selected':''; ?>>NÃO TRIBUTADO</option>
                                                        <option value="DevidoOutrosEstados" <?php echo $dadosCarga[MODICMS]=='DevidoOutrosEstados'?'selected':''; ?>>DEVIDO A OUTROS ESTADOS</option>
                                                    </select>
                                                </td>
                                                <th>Contrato:</th>
                                                <td>
                                                    <select name="contrato">
                                                        <?php
                                                            echo $opcoesContrato;
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                                                               
                                            </tr>
                                            <tr>
                                                <th>Placa:</th>
                                                <td>
                                                    <input class="text" name="placa" id="placa">
                                                </td>
                                                <th>Reboque:</th>
                                                <td>
                                                    <input class="text" name="reboque" id="semiReboque">
                                                </td>
                                                <th>Semi-Reboque:</th>
                                                <td>
                                                    <input class="text" name="semiReboque" id="semiReboque">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Peso Calculo:</th>
                                                <td colspan="6">
                                                    <input class="form-control valor" name="pesoCalculo" id="pesoCalculo" value="<?php if(isset($dadosCarga[PESOCALCULO])){ echo $dadosCarga[PESOCALCULO]; } ?>" <?php echo $naoEditavel; ?> >
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Frete Peso:</th>
                                                <td>
                                                    <input class="form-control valor" name="fretePeso" id="fretePeso" value="<?php if(isset($dadosCarga[FRETEPESO])){ echo $dadosCarga[FRETEPESO]; } ?>" <?php echo $naoEditavel; ?> >
                                                </td>
                                                <th>Escolta:</th>
                                                <td>
                                                    <input class="form-control valor" name="escolta" id="escolta" value="<?php if(isset($dadosCarga[ESCOLTA])){ echo $dadosCarga[ESCOLTA]; } ?>" <?php echo $naoEditavel; ?> >
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Advaloren:</th>
                                                <td>
                                                    <input class="form-control valor" name="advaloren" id="advaloren" value="<?php if(isset($dadosCarga[ADVALOREN])){ echo $dadosCarga[ADVALOREN]; } ?>" <?php echo $naoEditavel; ?> >
                                                </td>
                                                <th>SEC/CAT:</th>
                                                <td>
                                                    <input class="form-control valor" name="seccat" id="seccat" value="<?php if(isset($dadosCarga[SECCAT])){ echo $dadosCarga[SECCAT]; } ?>" <?php echo $naoEditavel; ?> >
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Carga:</th>
                                                <td>
                                                    <input class="form-control valor" name="carga" id="carga" value="<?php if(isset($dadosCarga[CARGA])){ echo $dadosCarga[CARGA]; } ?>" <?php echo $naoEditavel; ?> >
                                                </td>
                                                <th>Despacho:</th>
                                                <td>
                                                    <input class="form-control valor" name="despacho" id="despacho" value="<?php if(isset($dadosCarga[DESPACHO])){ echo $dadosCarga[DESPACHO]; } ?>" <?php echo $naoEditavel; ?> >
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Descarga:</th>
                                                <td>
                                                    <input class="form-control valor" name="descarga" id="descarga" value="<?php if(isset($dadosCarga[DESCARGA])){ echo $dadosCarga[DESCARGA]; } ?>" <?php echo $naoEditavel; ?> >
                                                </td>
                                                <th>Gris:</th>
                                                <td>
                                                    <input class="form-control valor" name="gris" id="gris" value="<?php if(isset($dadosCarga[GRIS])){ echo $dadosCarga[GRIS]; } ?>" <?php echo $naoEditavel; ?> >
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Enlonamento:</th>
                                                <td>
                                                    <input class="form-control valor" name="enlonamento" id="enlonamento" value="<?php if(isset($dadosCarga[ENLONAMENTO])){ echo $dadosCarga[ENLONAMENTO]; } ?>" <?php echo $naoEditavel; ?> >
                                                </td>
                                                <th>Adic Entrega:</th>
                                                <td>
                                                    <input class="form-control valor" name="adicionalEntrega" id="adicionalEntrega" value="<?php if(isset($dadosCarga[ADICIONALENTREGA])){ echo $dadosCarga[ADICIONALENTREGA]; } ?>" <?php echo $naoEditavel; ?>>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Frete Total:</th>
                                                <td>
                                                    <input name="freteTotal" id="freteTotal" class="form-control valor" value="<?php if(isset($dadosCarga[FRETETOTAL])){ echo $dadosCarga[FRETETOTAL]; } ?>" <?php echo $naoEditavel; ?> >
                                                </td>
                                                <th>Pedágio:</th>
                                                <td>
                                                    <input class="form-control valor" name="pedagio" id="pedagio" value="<?php if(isset($dadosCarga[PEDAGIO])){ echo $dadosCarga[PEDAGIO]; } ?>" <?php echo $naoEditavel; ?> >
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Frete Bruto Desejado:</th>
                                                <td>
                                                    <input class="form-control valor" name="freteBrutoDesejado" id="freteBrutoDesejado" value="<?php if(isset($dadosCarga[FRETEBRUTODESEJADO])){ echo $dadosCarga[FRETEBRUTODESEJADO]; } ?>" <?php echo $naoEditavel; ?> >
                                                </td>
                                                <th>Responsável Seguro:</th>
                                                <td>
                                                    <select name="responsavelSeguro" <?php echo $naoEditavel; ?>>
                                                        <option value="null"></option>
                                                        <option value="REMETENTE" <?php echo $dadosCarga[RESPONSAVELSEGURO]=='REMETENTE'?'selected':''; ?>>REMETENTE</option>
                                                        <option value="EXPEDIDOR" <?php echo $dadosCarga[RESPONSAVELSEGURO]=='EXPEDIDOR'?'selected':''; ?>>EXPEDIDOR</option>
                                                        <option value="RECEBEDOR" <?php echo $dadosCarga[RESPONSAVELSEGURO]=='RECEBEDOR'?'selected':''; ?>>RECEBEDOR</option>
                                                        <option value="DESTINATARIO" <?php echo $dadosCarga[RESPONSAVELSEGURO]=='DESTINATARIO'?'selected':''; ?>>DESTINATÁRIO</option>
                                                        <option value="EMITENTE DO CTE" <?php echo $dadosCarga[RESPONSAVELSEGURO]=='EMITENTE DO CTE'?'selected':''; ?>>EMITENTE DO CTE</option>
                                                        <option value="TOMADOR DO SERVIÇO" <?php echo $dadosCarga[RESPONSAVELSEGURO]=='TOMADOR DO SERVIÇO'?'selected':''; ?>>TOMADOR DO SERVIÇO</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Forma de Pagamento:</th>
                                                <td>
                                                    <select name="idFormaPagamento" <?php echo $naoEditavel; ?>>
                                                        <option value="0"></option>
                                                        <?php
                                                         foreach (listaFormaPagamentoBipe() as $dadosF) {
                                                             $chek = null;
                                                             if($dadosCarga[IDFORMAPAGAMENTO] == $dadosF[ID]){
                                                                 $chek = "selected";
                                                             }
                                                             echo "<option value='$dadosF[ID]' $chek>$dadosF[DESCRICAO]</option>";
                                                         }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Agendamento Cliente:</th>
                                                <td colspan="2">
                                                    Data <input type="date" name="dataAgendamentoCliente" id="dataAgendamentoCliente" required="required" value="<?php if(isset($dadosCarga[AGENCLI])){ echo $dadosCarga[AGENCLI]; } ?>" <?php echo $naoEditavel; ?> >
                                                    Hora <input type="time" name="horaAgendamentoCliente" id="horaAgendamentoCliente" required="required" value="<?php if(isset($dadosCarga[HORACLI])){ echo $dadosCarga[HORACLI]; } ?>" <?php echo $naoEditavel; ?> >
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Observação:</th>
                                                <td colspan="4">
                                                    <textarea class="form-control" rows="5" style="min-width: 100%" id="observacao" name="observacao"><?php if(isset($dadosCarga[OBSERVACAO])) { echo $dadosCarga[OBSERVACAO]; } ?> </textarea>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th colspan="8">
                                                    <div align="center">
                                                        <?php echo $campoId; ?>
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
            <div style="float: left;">VersÃ£o <?php echo $_SESSION['version']; ?></div> Copyright &copy; <?php echo date('Y'); ?>, Case Electronic Ltda.
        </div> <!-- #footer -->
    </body>
</html>