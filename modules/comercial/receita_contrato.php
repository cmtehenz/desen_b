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
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
    </head>

    <body>
        <?php
            include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
            include $_SERVER['DOCUMENT_ROOT'] . '/old/funcoes.php';

            /*********************************
             *   VARIAVEIS                   *
             * *******************************/
            date_default_timezone_set('America/sao_paulo');
            $wanted_week = date('W');
            $abrev_mes = date('M');
            $nome_mes = date('F');
            $dia = date('d');
            $ano = date('Y');
            $mes_atual = date('m');
            $imob = 0;

            if (isset($_POST['ano'])){
                $ano = $_POST['ano'];
            }
            if (isset($_POST['mes'])){
                $mes_atual = $_POST['mes'];
            }
            if (isset($_POST['imob'])){
                $imob = $_POST['imob'];
            }
            /********************************* */


            /****************************** */
            /*  CONSULTA TOTAL DA EMPRESA     *
            /******************************** */
            $realizado = faturamentoAnoMes($ano, $mes_atual, $imob);
            $realizadoDecimal = number_format(str_replace(',', '.', $realizado), 0, ',', '.');
            /********************************** */
            
            $realizadoFretePesoSicms = faturamentoFretePesoAnoMesSicms($ano, $mes_atual, $imob);
            $realizadoDecimalFPesoSicms = number_format(str_replace(',', '.', $realizadoFretePesoSicms), 0, ',', '.');

            /*************************************************
            //BUSCA RECEITA AGREGADO                          *
            /************************************************* */
            $receitaAgregado = faturamentoAnoMesAFTO($ano, $mes_atual, 'A', $imob);
            $receitaAgregadoDecimal = number_format($receitaAgregado, 0, ',', '.');
            $receitaAgregadoSicms = faturamentoAnoMesSicmsAFTO($ano, $mes_atual, 'A', $imob);
            $receitaAgregadoDecimalSicms = number_format($receitaAgregadoSicms, 0, ',', '.');

            /** ************************************************
            //BUSCA RECEITA FROTA              *
            /************************************************* */
            $receitaFrota = faturamentoAnoMesAFTO($ano, $mes_atual, 'F', $imob);
            $receitaFrotaDecimal = number_format($receitaFrota, 0, ',', '.');
            $receitaFrotaSicms = faturamentoAnoMesSicmsAFTO($ano, $mes_atual, 'F', $imob);
            $receitaFrotaDecimalSicms = number_format($receitaFrotaSicms, 0, ',', '.');

            /** ************************************************
            //BUSCA RECEITA TERCEIRO                           *
            /************************************************* */
            $receitaTerceiro = faturamentoAnoMesAFTO($ano, $mes_atual, 'T', $imob);
            $receitaTerceiroDecimal = number_format($receitaTerceiro, 0, ',', '.');
            $receitaTerceiroSicms = faturamentoAnoMesSicmsAFTO($ano, $mes_atual, 'T', $imob);
            $receitaTerceiroDecimalSicms = number_format($receitaTerceiroSicms, 0, ',', '.');

            /** ************************************************
            //BUSCA RECEITA OUTROS    "SEM PLACA "            *
            /************************************************* */
            $receitaOutros = faturamentoAnoMesOutros($ano, $mes_atual, $imob);
            $receitaOutrosDecimal = number_format($receitaOutros, 0, ',', '.');
            $receitaOutrosSicms = faturamentoAnoMesOutrosSicms($ano, $mes_atual, $imob);
            $receitaOutrosDecimalSicms = number_format($receitaOutrosSicms, 0, ',', '.');
            /* * *********************************************** */

            $sqlAno = mssql_query("SELECT * FROM ano WHERE ano <> $ano ORDER BY ano DESC");
            $listaAno = $listaAno . "<option value='$ano'>$ano</option>";
            while ($dadosAno = mssql_fetch_array($sqlAno)){
                $listaAno = $listaAno . "<option value='$dadosAno[0]'>$dadosAno[0]</option>";
            }

            $sqlMesSelecionado = mssql_query("SELECT * FROM mes WHERE id_mes=$mes_atual");
            $dadosMesSelecionado = mssql_fetch_array($sqlMesSelecionado);

            $sqlMes = mssql_query("SELECT * FROM mes WHERE id_mes <> $mes_atual ");
            $listaMes = $listaMes . "<option value='$mes_atual'>$dadosMesSelecionado[1]</option>";
            while ($dadosMes = mssql_fetch_array($sqlMes)){
                $listaMes = $listaMes . "<option value='$dadosMes[0]'>$dadosMes[1]</option>";
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
                                Calcular venda de imobilizado.
                                <input type="checkbox" name="imob" value="1" <?php if ($imob) echo "checked"; ?> onchange="document.form1.submit()">
                            </div>
                        </form>
                        <br>
                    </div>

                    <div class="grid-14">
                        <div class="widget">

                            <div class="widget-header">
                                <span class="icon-chart"></span>
                                <h3 class="icon chart">MIX FATURAMENTO</h3>
                            </div>

                            <div class="widget-content">
                                <table class="stats" data-chart-type="pie" data-chart-colors="">
                                    <caption><?php echo $_SESSION['nomeEmpresa']; ?></caption>
                                    <thead>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <th>faturamento</th>
                                        </tr>

                                    </thead>

                                    <tbody>

                                        <tr>
                                            <th>AGREGADO</th>
                                            <td><?php echo number_format($receitaAgregado / $realizado * 100, 0, '', ''); ?> </td>
                                        </tr>

                                        <tr>
                                            <th>FROTA</th>
                                            <td><?php echo number_format($receitaFrota / $realizado * 100, 0, '', ''); ?></td>
                                        </tr>

                                        <tr>
                                            <th>TERCEIRO</th>
                                            <td><?php echo number_format($receitaTerceiro / $realizado * 100, 0, '', ''); ?></td>
                                        </tr>
                                        <tr>
                                            <th>OUTROS</th>
                                            <td><?php echo number_format($receitaOutros / $realizado * 100, 0, '', ''); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div> <!-- .widget-content -->

                        </div> <!-- .widget -->
                    </div>

                    <div class="grid-10">
                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Faturamento</h3>
                            </div>

                            <div class="widget-content">

                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>CONTRATO</th>
                                            <th>FRETE TOTAL(R$)</th>
                                            <th>FRETE PESO(R$)</th>
                                            <th>PORC %</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class='gradeA'>
                                            <td><a href='sublists/receita_contrato.php?contrato=A&ano=<?php echo $ano; ?>&mes=<?php echo $mes_atual; ?>'>AGREGADO</a></td>
                                            <td align='right'><?php echo $receitaAgregadoDecimal; ?></td>
                                            <td align='right'><?php echo $receitaAgregadoDecimalSicms; ?></td>
                                            <td><?php echo number_format($receitaAgregado / $realizado * 100, 0, ',', '.'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><a href='sublists/receita_contrato.php?contrato=F&ano=<?php echo $ano; ?>&mes=<?php echo $mes_atual; ?>'>FROTA</a></td>
                                            <td align='right'><?php echo $receitaFrotaDecimal; ?></td>
                                            <td align='right'><?php echo $receitaFrotaDecimalSicms; ?></td>
                                            <td><?php echo number_format($receitaFrota / $realizado * 100, 0, ',', '.'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><a href='sublists/receita_contrato.php?contrato=T&ano=<?php echo $ano; ?>&mes=<?php echo $mes_atual; ?>'>TERCEIRO</a></td>
                                            <td align='right'><?php echo $receitaTerceiroDecimal; ?></td>
                                            <td align='right'><?php echo $receitaTerceiroDecimalSicms; ?></td>
                                            <td><?php echo number_format($receitaTerceiro / $realizado * 100, 0, ',', '.'); ?></td>
                                        </tr>
                                        <tr>
                                            <td>OUTROS</td>
                                            <td align='right'><?php echo $receitaOutrosDecimal; ?></td>
                                            <td align='right'><?php echo $receitaOutrosDecimalSicms; ?></td>
                                            <td><?php echo number_format($receitaOutros / $realizado * 100, 0, ',', '.'); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div> <!-- .widget-content -->

                        </div> <!-- .widget -->

                        <div class="box plain">

                            <h3>TOTAL DA EMRPESA</h3>

                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>EMPRESA</th>
                                        <th>TOTAL FRETE R$</th>
                                        <th>FRETE PESO R$</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="odd gradeX">
                                        <td><?php echo $_SESSION['nomeEmpresa']; ?></td>
                                        <td align='right'><?php echo $realizadoDecimal; ?></td>
                                        <td align='right'><?php echo $realizadoDecimalFPesoSicms; ?></td>
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
    </script>
</body>
</html>