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
        <title>BID - Contas a pagar</title>

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
            include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptDB2.php';
            include $_SERVER['DOCUMENT_ROOT'] . '/old/funcoes.php';

            /*             * ******* Variáveis ******** */
            date_default_timezone_set('America/sao_paulo');
            $ano = date('Y');

            if (isset($_POST['ano'])) $ano = $_POST['ano'];

            $sqlAno = mssql_query("SELECT * FROM ano ORDER BY ano DESC");

            while ($dadosAno = mssql_fetch_array($sqlAno)){
                $selected = ($dadosAno[0] == $ano) ? "selected" : "";

                $listaAno .= "<option value='$dadosAno[0]' $selected>$dadosAno[0]</option>";
            }

            $sqlMes = mssql_query("SELECT * FROM mes");

            // Cálculos do mês
            while ($dadosMes = mssql_fetch_array($sqlMes)){
                $lancadoMes = contasPagarLancadoAnoMes($ano, $dadosMes[0]);
                $jurosMes = contasPagarJurosAnoMes($ano, $dadosMes[0]);
                $descontoMes = contasPagarDescontoAnoMes($ano, $dadosMes[0]);
                $pagarMes = contasPagarPagarAnoMes($ano, $dadosMes[0]);
                $pagoMes = contasPagarPagoAnoMes($ano, $dadosMes[0]);

                $linhaTabela = $linhaTabela . "<tr class='gradeA'>
                                                <td>$dadosMes[1]</td>
                                                <td align='right'>" . $lancadoMes . "</td>
                                                <td align='right'><a href='sublists/desconto.php?ano=$ano&mes=$dadosMes[0]'>$descontoMes</a></td>
                                                <td align='right'><a href='sublists/juros.php?ano=$ano&mes=$dadosMes[0]'>$jurosMes</a></td>
                                                <td align='right'><a href='sublists/pagar.php?ano=$ano&mes=$dadosMes[0]'>$pagarMes</a></td>
                                                <td align='right'><a href='sublists/pago.php?ano=$ano&mes=$dadosMes[0]'>$pagoMes</a></td>
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

            <!-- Sidebar -->
            <?php echo $hoUtils->menuUsuario($_SESSION['idUsuario']); ?>

            <div id="content">
                <div id="contentHeader">
                    <h1><?php echo $_SESSION['nomeEmpresa']; ?></h1>
                </div> <!-- #contentHeader -->

                <div class="container">
                    <div class="grid-24">
                        <form action="#" method="post" name="formCP">
                            <div class="field">
                                <label>Selecione o ano:</label>
                                <select id="ano" name="ano" onchange="document.formCP.submit()">
                                    <?php echo $listaAno; ?>
                                </select>
                            </div>
                        </form>
                        <br />

                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">CONTAS A PAGAR ANUAL</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>MÊS</th>
                                            <th>LANÇADO</th>
                                            <th>DESCONTO</th>
                                            <th>JUROS</th>
                                            <th>A PAGAR</th>
                                            <th>PAGO</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php echo $linhaTabela; ?>
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
                                        <th>LANÇADO</th>
                                        <th>DESCONTO</th>
                                        <th>JUROS</th>
                                        <th>A PAGAR</th>
                                        <th>PAGO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="odd gradeX">
                                        <td><?php echo $_SESSION['nomeEmpresa']; ?></td>
                                        <td align='right'><?php echo contasPagarLancadoAnoMes($ano); ?></td>
                                        <td align='right'><?php echo contasPagarDescontoAnoMes($ano); ?></td>
                                        <td align='right'><?php echo contasPagarJurosAnoMes($ano); ?></a></td>
                                        <td align='right'><?php echo contasPagarPagarAnoMes($ano); ?></td>
                                        <td align='right'><?php echo contasPagarPagoAnoMes($ano); ?></td>
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
                                        </div> <!-- .qnc_actions -->
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
                                        </div> <!-- .qnc_actions -->
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
                                        </div> <!-- .qnc_actions -->
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
        </div> <!-- #footer -->
    </body>
</html>