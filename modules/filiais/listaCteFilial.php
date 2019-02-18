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
        <title>BID - Receita por filial</title>

        <meta charset="utf-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
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
            if (isset($_POST['dia'])){
                $diaSelect = $_POST['dia'];
            }else{
                $diaSelect = $dia;
            }

            $sqlAno = mssql_query("SELECT * FROM ano WHERE ano <> $ano ORDER BY ano DESC");
            $listaAno = $listaAno . "<option value='$ano'>$ano</option>";
            while ($dadosAno = mssql_fetch_array($sqlAno)){
                $listaAno = $listaAno . "<option value='$dadosAno[0]'>$dadosAno[0]</option>";
            }

            $sqlMesSelecionado = mssql_query("SELECT * FROM mes WHERE id_mes=$mes_atual");
            $dadosMesSelecionado = mssql_fetch_array($sqlMesSelecionado);

            $sqlMes = mssql_query("SELECT * FROM mes WHERE id_mes <> $mes_atual");
            $listaMes = $listaMes . "<option value='$mes_atual'>$dadosMesSelecionado[1]</option>";
            while ($dadosMes = mssql_fetch_array($sqlMes)){
                $listaMes = $listaMes . "<option value='$dadosMes[0]'>$dadosMes[1]</option>";
            }
            
                      
            $diaAnterior = $dia - 1;
            $dias = array(
                "$dia"          => 'Hoje',
                "$diaAnterior"  => 'Ontem'
            );
            
            foreach ($dias as $dataSelect => $valor){
                $selected = null;
                if ($diaSelect == $dataSelect ) {
                    $selected = "selected";
                }
                $listaDia = $listaDia . "<option value='$dataSelect' $selected>$valor</option>";
                //echo $listaDia;
            }

            /*********************************** */

            /******************************************/
            /*  CONSULTA POR FILIAIS                   *
            * PROGRAMADOR: Gabriel Luis                *
            * DATA: 10/07/2017                         *
            *                                          *
            * Lista de Sigla de Filiais cadastradas    *
            * para o usuario corrente.                 *
            *                                          *
            /******************************************/
            $sql = mssql_query("SELECT * FROM usuarioFilial WHERE idUsuario=$idUsuario");
            while($listaFilial = mssql_fetch_array($sql)){
                $nomeFilial = nomeFilialSAP($listaFilial[idFilial]);
                $diretorio = diretorioPDF($listaFilial[idFilial]);
                //echo $diretorio;
                $limpar = "\\zapsapnew\Cte$";
                $dir = str_replace($limpar, "", $diretorio);
                
                
                $diretorioMdfe = diretorioMDFE($listaFilial[idFilial]);
                $limpar = "C:\SAP\MDFe";
                $dirMdfe = str_replace($limpar, "", $diretorioMdfe);
                foreach (listaCte($listaFilial[idFilial], $ano, $mes_atual, $diaSelect) as $dados){
                    $dados[CNPJDEST] = str_replace('.', "", $dados[CNPJDEST]);
                    $dados[CNPJDEST] = str_replace('/', "", $dados[CNPJDEST]);
                    $dados[CNPJDEST] = str_replace('-', "", $dados[CNPJDEST]);
                    
                    $listaNfe = null;
                    foreach(listaNfeCliente($dados[IDDOCTO]) as $lista){
                        $listaNfe .= $lista[U_NUMNF].'-';
                    }
                    $listaNfe = substr($listaNfe, 0, -1);
                    
                    //MDFE
                    foreach (buscaMdfe($dados[IDFILIAL], $dados[NUMERODOCTO]) as $dadosMdfe){
                        $numMdfe = $dadosMdfe[MDFE];
                        $dia     = $dadosMdfe[DIA];
                        $mes     = $dadosMdfe[MES];
                    }
                    
                    $dataDia = date('d-m-Y', strtotime($dados[DATAEMISDOCTO]));
                    $linhaTabela = $linhaTabela . "<tr class='gradeA'>
                                    <td>$dados[NOMEFILIAL]</td>
                                    <td><a href='logUsuarioCte.php?idUsuario=".$_SESSION['idUsuario']."&usuario=".$_SESSION['nomeUsuario']."&link=\cteSap".$dir."/PDF".$dados[CNPJDEST].$dataDia.$dados[NUMERODOCTO].".PDF ' target='_blank'>$dados[NUMERODOCTO]</a></td>
                                    <td>".date('d/m/Y', strtotime($dados[DATAEMISDOCTO]))."</td>
                                    <td>$dados[PLACA]</td>
                                    <td>$dados[TIPOCONTRATO]</td>
                                    <td>$dados[NOMECLIENTE]</td>
                                    <td>$listaNfe</td>
                                    <td><a href='logUsuarioCte.php?idUsuario=".$_SESSION['idUsuario']."&usuario=".$_SESSION['nomeUsuario']."&link=\mdfeSap".$dirMdfe."/DAMDFE ".$dia."-".$mes."-".$ano." ".$numMdfe.".PDF"."' target='_blank'>$numMdfe</a></td>
                                </tr>";
                }
            }
        ?>
        <!--
        <a href='..\cteSap".$dir."/PDF".$dados[CNPJDEST].$dataDia.$dados[NUMERODOCTO].".PDF ' target='_blank'> -->
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
                        <form action="#" method="post" name="form1">
                            <div class="field">
                                <label>Selecione o dia:</label>
                                <select id="dia" name="dia" onchange="document.form1.submit()">
                                    <?php echo $listaDia; ?>
                                </select>
                            </div>
                        </form>
                        <br>
                    </div>
                
                    <div class="grid-24">

                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Relatorio CT-E Emitidos Diario</h3>
                            </div>

                            <div class="widget-content">

                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>FILIAL</th>
                                            <th>DOCUMENTO</th>
                                            <th>DATA EMISSAO</th>
                                            <th>PLACA</th>
                                            <th>CONTRATO</th>
                                            <th>CLIENTE</th>
                                            <th>NOTA FISCAL</th>
                                            <th>Mdf-E</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php echo $linhaTabela; ?>
                                    </tbody>
                                </table>


                            </div> <!-- .widget-content -->

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

            <div id="quickNav">
                <ul>
                    <li class="quickNavMail">
                        <a href="#menuAmpersand" class="menu"><span class="icon-book"></span></a>

                        <span class="alert">3</span>

                        <div id="menuAmpersand" class="menu-container quickNavConfirm">
                            <div class="menu-content cf">

                                <div class="qnc qnc_confirm">

                                    <h3>Confirm</h3>

                                    <div class="qnc_item">
                                        <div class="qnc_content">
                                            <span class="qnc_title">Confirm #1</span>
                                            <span class="qnc_preview">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do.</span>
                                            <span class="qnc_time">3 hours ago</span>
                                        </div> <!-- .qnc_content -->

                                        <div class="qnc_actions">
                                            <button class="btn btn-primary btn-small">Accept</button>
                                            <button class="btn btn-quaternary btn-small">Not Now</button>
                                        </div>
                                    </div>

                                    <div class="qnc_item">
                                        <div class="qnc_content">
                                            <span class="qnc_title">Confirm #2</span>
                                            <span class="qnc_preview">Duis aute irure dolor in henderit in voluptate velit esse cillum dolore.</span>
                                            <span class="qnc_time">3 hours ago</span>
                                        </div> <!-- .qnc_content -->

                                        <div class="qnc_actions">
                                            <button class="btn btn-primary btn-small">Accept</button>
                                            <button class="btn btn-quaternary btn-small">Not Now</button>
                                        </div>
                                    </div>

                                    <div class="qnc_item">
                                        <div class="qnc_content">
                                            <span class="qnc_title">Confirm #3</span>
                                            <span class="qnc_preview">Duis aute irure dolor in henderit in voluptate velit esse cillum dolore.</span>
                                            <span class="qnc_time">3 hours ago</span>
                                        </div> <!-- .qnc_content -->

                                        <div class="qnc_actions">
                                            <button class="btn btn-primary btn-small">Accept</button>
                                            <button class="btn btn-quaternary btn-small">Not Now</button>
                                        </div>
                                    </div>

                                    <a href="javascript:;" class="qnc_more">View all Confirmations</a>

                                </div> <!-- .qnc -->
                            </div>
                        </div>
                    </li>
                    <li class="quickNavNotification">
                        <a href="#menuPie" class="menu"><span class="icon-chat"></span></a>

                        <div id="menuPie" class="menu-container">
                            <div class="menu-content cf">

                                <div class="qnc">

                                    <h3>Notifications</h3>

                                    <a href="javascript:;" class="qnc_item">
                                        <div class="qnc_content">
                                            <span class="qnc_title">Notification #1</span>
                                            <span class="qnc_preview">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do.</span>
                                            <span class="qnc_time">3 hours ago</span>
                                        </div> <!-- .qnc_content -->
                                    </a>

                                    <a href="javascript:;" class="qnc_item">
                                        <div class="qnc_content">
                                            <span class="qnc_title">Notification #2</span>
                                            <span class="qnc_preview">Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu.</span>
                                            <span class="qnc_time">3 hours ago</span>
                                        </div> <!-- .qnc_content -->
                                    </a>

                                    <a href="javascript:;" class="qnc_more">View all Confirmations</a>

                                </div> <!-- .qnc -->
                            </div>
                        </div>
                    </li>
                </ul>
            </div> <!-- .quickNav -->


        </div> <!-- #wrapper -->

        <div id="footer">
            <div style="float: left;">Versão <?php echo $_SESSION['version']; ?></div> Copyright &copy; <?php echo date('Y'); ?>, Case Electronic Ltda.
        </div>
    </body>
</html>