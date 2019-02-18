<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL = new \Library\Scripts\scriptSQL();

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));
?>
<!doctype html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Receita por cliente</title>

        <meta charset="utf-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />       
        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
        <script src="<?php echo $hoUtils->getURLDestino("js/sorttable.js"); ?>"></script>
    </head>

    <body>
        <?php
            include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
            include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptMSSQL.php';
            include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptSAP.php';

            /*********************************
             *   VARIAVEIS                   *
             * ****************************** */
            date_default_timezone_set('America/sao_paulo');
            $wanted_week = date('W');
            $abrev_mes = date('M');
            $nome_mes = date('F');
            $dia = date('d');
            $ano = date('Y');
            $mes_atual = date('m');

            if (isset($_POST['ano'])){
                $ano = $_POST['ano'];
            }
            if (isset($_POST['mes'])){
                $mes_atual = $_POST['mes'];
            }
            
            $sqlAno = mssql_query("SELECT * FROM ano WHERE ano <> $ano ORDER BY ano DESC");
            $listaAno = $listaAno . "<option value='$ano'>$ano</option>";
            while ($dadosAno = mssql_fetch_array($sqlAno)){
                $listaAno = $listaAno . "<option value='$dadosAno[0]'>$dadosAno[0]</option>";
            }

            $sqlMes = mssql_query("SELECT * FROM mes WHERE id_mes <> $mes_atual");
            $listaMes = $listaMes . "<option value='$mes_atual'>" . mesSelecionado($mes_atual) . "</option>";
            while ($dadosMes = mssql_fetch_array($sqlMes)){
                $listaMes = $listaMes . "<option value='$dadosMes[0]'>$dadosMes[1]</option>";
            }
            
            $fretePeso = receitaFretePeso($ano, $mes_atual);
            $fretePesoCIcms = receitaFretePesoCIcms($ano, $mes_atual);
            
            
            //REceita Total sem ICMS Agregado
            $receitaAgregado = receitaFretePeso($ano, $mes_atual, null, 'A');
            $agregadoPorCento = $receitaAgregado * 100 / $fretePeso;
            //Receita Total SEM ICMS Frota
            $receitaFrota = receitaFretePeso($ano, $mes_atual, null, 'F');
            $frotaPorCento = $receitaFrota * 100 / $fretePeso;
            $terceiroPorCentro = 100 - $frotaPorCento - $agregadoPorCento;
            
            
                        
            foreach(listaClientesFaturamento($ano, $mes_atual, null, null) as $cliente){
                
                $percTotal    = $cliente[TOTAL] * 100 / $fretePeso;
                $percAgregado = $cliente[FRETEPESOAGREGAO] * 100 / $cliente[TOTAL];
                $percFrota    = $cliente[FRETEPESOFROTA] * 100 / $cliente[TOTAL];
                $percTerceiro = $cliente[FRETEPESOTERCEIRO] * 100 / $cliente[TOTAL];
                
                $linhaTabela .=
                    "<tr>
                        <td><a href='sublists/receita_fil_x_cliSAP.php?c=$cliente[CNPJ]&mes=$mes_atual&ano=$ano&nomeCliente=$cliente[NOMECLIENTE]'>$cliente[NOMECLIENTE]</a></td>
                        <td align='center'>".number_format($percAgregado, 0)."</td>
                        <td align='center'>".number_format($percFrota, 0)."</td>
                        <td align='center'>".number_format($percTerceiro, 0)."</td>
                        <td align='center'>".number_format($percTotal, 2)."</td>
                        <td align='center'>".number_format($cliente[FRETEPESO], 2, ',', '.') ."</td>
                        <td align='center'>".number_format($cliente[TOTAL], 2, ',', '.') ."</td>
                        
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
                                <br>
                                <br>
                                Periodo Selecionado: <?php echo mesSelecionado($mes_atual) . '/ ' . $ano; ?>.
                                <br>
                                * Calculo do total sem somar vendas de imobilizado.
                            </div>
                        </form>
                        <br>

                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Faturamento Clientes</h3>
                            </div>

                            <div class="widget-content">

                                <table class="sortable table table-bordered table-striped ">
                                    <thead>
                                        <tr>
                                            <th width="38%">CLIENTE</th>
                                            <th width="9%">AGREGADO %</th>
                                            <th width="9%">FROTA %</th>
                                            <th width="9%">TERCEIRO %</th>
                                            <th width="9%">PORC %</th>
                                            <th width="14%">FRETE PESO(R$)</th>
                                            <th>FRETE PESO(R$) C/ICMS</th>                                            
                                        </tr>
                                    </thead>
                                    <tbody>

<?php
    echo $linhaTabela;
?>

                                    </tbody>
                                </table>

                            </div> <!-- .widget-content -->

                        </div> <!-- .widget -->

                        <div class="box plain">

                            <h3>TOTAL DA EMRPESA</h3>

                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th width="38%">EMPRESA</th>
                                        <th width="12%">AGREGADO %</th>
                                        <th width="12%">FROTA %</th>
                                        <th width="12%">TERCEIRO %</th>
                                        <th width="14%">FRETE PESO(R$)</th>
                                        <th>FRETE PESO(R$) C/ICMS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="odd gradeX">
                                        <td><?php echo $_SESSION['nomeEmpresa']; ?></td>
                                        <td><?php echo number_format($agregadoPorCento); ?></td>
                                        <td><?php echo number_format($frotaPorCento); ?></td>
                                        <td><?php echo number_format($terceiroPorCentro); ?></td>
                                        <td align='right'><?php echo number_format($fretePeso, 2, ',', '.'); ?></td>
                                        <td align='right'><?php echo number_format($fretePesoCIcms, 2, ',', '.'); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div> <!-- .box -->


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