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
        <title>BID - Lista de Cargas Pendentes</title>

        <meta charset="utf-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />
        
        <meta http-equiv="refresh" content="600;url=./telaCarga.php">

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
        
        
        
        
        <script type="text/javascript">
            <!--
            i = 0;
            tempo = 50;
            tamanho = 826; // tamanho da barra de rolagem  >> Ver arquivo Leiame.txt

            function Rolar() {
              document.getElementById('painel').scrollTop = i;
              i++;
              t = setTimeout("Rolar()", tempo);
              if (i == tamanho) {
                i = 0;
              }
            }
            function Parar() {
              clearTimeout(t);
            }
            //-->
        </script>
        
    </head>

    <body>
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
            $sql = mssql_query("SELECT  day(agendamentoCliente) as dia,month(agendamentoCliente) as mes,"
                    . "                 year(agendamentoCliente) as ano,DATEPART(HOUR, agendamentoCliente) AS  hora,"
                    . "                 DATEPART(minute, agendamentoCliente) AS  minuto, agendamentoCliente as agenda,* "
                    . " FROM cargas order by agendamentoCliente ");
            while($listaFilial = mssql_fetch_array($sql)){
                $nomeFilial = nomeFilialSAP($listaFilial[idFilial]);
                $tipoCte = $listaFilial[tipoCte];
                $remetente = nomeClienteSAP($listaFilial[remetente]) . " - " . $listaFilial[remetente];
                $destinatario = nomeClienteSAP($listaFilial[destinatario]) . " - " . $listaFilial[destinatario];
                $cobranca = $listaFilial[cobranca];
                $redespacho = $listaFilial[redespacho];
                $expedidor = $listaFilial[expedidor];
                $placa = $listaFilial[placa];
                $contrato = $listaFilial[contrato];
                $numBipe = $listaFilial[numBIPE];
                if($contrato == "null"){
                    $contrato = "";
                }
                $idCarga = $listaFilial[id];
                $fretePeso = $listaFilial[fretePeso];
                //$agendamentoCliente = $listaFilial[agendamentoCliente];
                $dia = $listaFilial[dia];
                $mes = $listaFilial[mes];
                $ano = $listaFilial[ano];
                
                if(strlen($listaFilial[hora])<2){
                    $hora = "0" . $listaFilial[hora];
                }
                if(strlen($listaFilial[minuto])<2){
                    $min = "0" . $listaFilial[minuto];
                }
                $horaAgend =  $hora . ":" . $min;
                $agendamentoCliente = $dia . "/" . $mes . "/" . $ano . " - " . $horaAgend;
               
               
                if(($placa <> "") and ($placa <> " ")){
                    $status = "PROGRAMADA";
                }else{
                    $status = "AGUARDANDO PROGRAMAÇÃO";
                }
                if($numBipe <> ""){
                    $status = "EXPEDIDA";
                }
                $coratual = "MediumBlue";
                if($status <> "PROGRAMADA"){
                    $coratual = "SaddleBrown";
                }
                if($status == "EXPEDIDA"){
                    $coratual = "#006600";
                }
                $linhaTabela = $linhaTabela . "
                                        
                                        <tr>
                                            <td width = '20%'><font color='".$coratual."'>".$nomeFilial."</td>
                                            <td width = '20%'><font color='".$coratual."'>".$remetente."</td>
                                            <td width = '20%'><font color='".$coratual."'>".$destinatario."</td>
                                            <td width = '6%' align='right'><font color='".$coratual."'>".$fretePeso."</td>
                                            <td width = '12%' align = 'right'><font color='".$coratual."'>".$agendamentoCliente."</td>
                                            <td width = '7%' align='center'><font color='".$coratual."'>".$placa."</td>
                                            <td width = '5%' align='center'><font color='".$coratual."'>".$contrato."</td>
                                            <td width = '10%'><font color='".$coratual."'>".$status."</td>
                                        </tr>";
                
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

                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Agendamento de Cargas </h3>
                            </div>

                            <div class="widget-content">
                                <table width="100%" border="1" cellspacing="1" cellpadding="1">
                                        <tr>
                                            <th width="20%" align="center"><b>FILIAL</b></th>
                                            <th width="20%" align="center"><b>REMETENTE</b></th>
                                            <th width="20%" align="center"><b>DESTINATÁRIO</b></th>
                                            <th width="6%" align="center"><b>FRETE PESO</b></th>
                                            <th width="12%" align="center"><b>DATA AGENDA</b></th>
                                            <th width="7%" align="center"><b>PLACA</b></th>
                                            <th width="5%" align="center"><b>TIPO</b></th>
                                            <th width="10%" align="center"><b>STATUS</b></th>
                                        </tr>                                    
                                </table>
                            </div> <!-- .widget-content -->
                            
                            <body onload="Rolar()">
                                <div id="painel" style=" cursor: default; height: 400px; width: 100%; overflow: hidden; padding-left: 10px;  padding-right: 10px" onmouseover="Parar()" onmouseout="Rolar()">
                                    <!-- INÍCIO DO CONTEÚDO DA DIV -->
                                    <br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />

                                    <div class="widget-content">
                                        <table class="table table-bordered table-striped">
                                            <tbody>
                                                <?php echo $linhaTabela; ?>
                                            </tbody>
                                        </table>
                                    </div> <!-- .widget-content -->
                                                    
                                    <br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
                                    <!-- FIM DO CONTEÚDO DA DIV -->
                                </div>
                            </body> 
                        </div> <!-- .widget -->                       
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
        </div>
    </body>
</html>