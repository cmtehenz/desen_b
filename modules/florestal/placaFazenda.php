<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL = new \Library\Scripts\scriptSQL();
    $dbcDB2  = new \Library\Scripts\scriptDB2();

    $_SESSION['modulo'] = basename(str_replace("sublists", "", dirname($_SERVER['PHP_SELF'])));
?>
<!doctype html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Carregamentos por placa</title>

        <meta charset="utf-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
    </head>

    <body>
        <?php
            include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
            include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptMSSQL.php';

            /*             * *******************************
             *   VARIAVEIS                   *
             * ****************************** */
            date_default_timezone_set('America/sao_paulo');
            $wanted_week = date('W');
            $abrev_mes = date('M');
            $nome_mes = date('F');
            $dia = date('d');
            $ano = date('Y');
            $mes_atual = date('m');
            $mes = date('m');

            if (isset($_GET['mes'])){
                $mes_atual = $_GET['mes'];
                $mes = $_GET['mes'];
            }
            if (isset($_POST['mes'])){
                $mes_atual = $_POST['mes'];
                $mes = $_POST['mes'];
            }

            if (isset($_GET['ano'])){
                $ano = $_GET['ano'];
            }
            if (isset($_POST['ano'])){
                $ano = $_POST['ano'];
            }            
                        
            foreach (listaAno() as $dadosAno){
                $selected = null;
                if($_POST['ano'] == $dadosAno[ANO]){
                    $selected = "selected";
                }
                $listaAno .= "<option value='$dadosAno[ANO]' $selected>$dadosAno[ANO]</option>";
            }

            foreach (listaMes() as $dadosMes){
                $selected = null;
                if($mes == $dadosMes[ID_MES]){
                    $selected = "selected";
                }
                $listaMes = $listaMes . "<option value='$dadosMes[ID_MES]' $selected>$dadosMes[MES]</option>";
            }            
            
            foreach(florestalListaPlacas($ano, $mes) as $dadosPlacas){
                $placa = $dbcDB2->placaVeiculo($dadosPlacas[IDPLACA]);
                $linhaFazenda = null;
                foreach (florestalListaFazendaPlaca($ano, $mes, $dadosPlacas[IDPLACA]) as $dadosFazenda){
                    $linhaFazenda .= "<tr class='gradeA'>
                                            <td>".florestalNomeFazenda($dadosFazenda[IDFAZENDA])."</td>
                                            <td align='right'>$dadosFazenda[VIAGEM]</td>
                                            <td align='right'>".number_format($dadosFazenda[PESO], 0, ',', '.')."</td>
                                            <td align='right'>".number_format($dadosFazenda[PESO]/$dadosFazenda[VIAGEM], 0, ',', '.')."</td>
                                            <td align='right'>".number_format($dadosFazenda[FATURAMENTO], 2, ',', '.')."</td>
                                            <td align='right'>".number_format($dadosFazenda[FATURAMENTO] / ($dadosFazenda[PESO]/1000), 0, ',', '.')."</td>
                                        </tr>";
                }
                $tabelaPlaca .= "<div class='widget widget-table'>
                                    <div class='widget-header'>
                                        <span class='icon-list'></span>
                                        <h3 class='icon chart'>Placa: $placa</h3>
                                    </div>
                                    <div class='widget-content'>
                                        <table class='table table-bordered table-striped '>
                                            <thead>
                                                <tr>
                                                    <th>Fazenda</th>
                                                    <th>Viagens</th>
                                                    <th>Peso Kg</th>
                                                    <th>Kg / viagem</th>
                                                    <th>Faturamento</th>
                                                    <th>Faturamento / Tonelada</th>
                                                </tr>
                                            </thead>
                                            <tbody>".$linhaFazenda."</tbody>
                                        </table>
                                    </div> <!-- .widget-content -->
                                </div> <!-- .widget -->";
            }
            //NOME DO MES SELECIONADO
            $sqlMesSelecionado = mssql_query("SELECT * FROM mes WHERE id_mes=$mes_atual");
            $dadosMesSelecionado = mssql_fetch_array($sqlMesSelecionado);
            
            $totalCarga = florestalTotalCargas($ano, $mes);
            $totalPeso = florestalTotalPeso($ano, $mes);
            $totalFaturmaneto = florestalTotalFaturamento($ano, $mes);
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

                        <form action="#" method="post" name="form1">
                            <div class="field">
                                <label>Selecione o MES:</label>
                                <select id="mes" name="mes" onchange="document.form1.submit()">
                                    <?php echo $listaMes; ?>
                                </select>

                                <label>Selecione o ANO:</label>
                                <select id="ano" name="ano" onchange="document.form1.submit()">
                                    <?php echo $listaAno; ?>
                                </select>

                                

                            </div>
                        </form>
                        <br>  
                        <?php echo $tabelaPlaca; ?>
                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">TOTAL</h3>
                            </div>
                            <div class="widget-content">
                                <table class="table table-bordered table-striped ">
                                    <thead>
                                        <tr>
                                            <th>Fazenda</th>
                                            <th>Viagens</th>
                                            <th>Peso Kg</th>
                                            <th>Kg / viagem</th>
                                            <th>Faturamento</th>
                                            <th>Faturamento / Tonelada</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class='gradeA'>
                                            <td><?php echo $dadosMesSelecionado[1].' / '.$ano; ?></td>
                                            <td align='right'><?php echo $totalCarga; ?></td>
                                            <td align='right'><?php echo number_format($totalPeso, 0, ',', '.'); ?></td>
                                            <td align='right'><?php echo number_format($totalPeso/$totalCarga, 0, ',', '.'); ?></td>
                                            <td align='right'><?php echo number_format($totalFaturmaneto, 2, ',', '.'); ?></td>
                                            <td align='right'><?php echo number_format($totalFaturmaneto / ($totalPeso/1000), 0, ',', '.'); ?></td>
                                        </tr>
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