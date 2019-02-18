<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL = new \Library\Scripts\scriptSQL();

    $_SESSION['modulo'] = basename(str_replace("sublists", "", dirname($_SERVER['PHP_SELF'])));
?>
<!doctype html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Notas</title>

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

            if (isset($_GET['ano'])) $ano = $_GET['ano'];
            if (isset($_GET['mes'])) $mes = $_GET['mes'];

            if (isset($_GET['forneced'])) $idForneced = $_GET['forneced'];
            if (isset($_GET['opcao'])) $opcao = $_GET['opcao'];

            if (isset($_GET['dtIni'])) $dtIni = $_GET['dtIni'];
            if (isset($_GET['dtFin'])) $dtFin = $_GET['dtFin'];

            // Faz um switch na opção que será buscada para preencher o filtro adicional do SELECT
            switch ($opcao){
                case('desconto'):
                    $filtro = ' AND P.DESCONTO > 0 ';
                    break;

                case('juros'):
                    $filtro = ' AND P.JUROS > 0 ';
                    break;

                case('pagar'):
                    $filtro = " AND P.STATUS <> 'P' ";

                    if ($dtIni != NULL && $dtFin != NULL) $filtro .= " AND P.DT_VENCIMENTO BETWEEN '$dtIni' AND '$dtFin' ";

                    break;

                case('pago'):
                    $filtro = " AND P.STATUS = 'P' ";

                    if ($dtIni != NULL && $dtFin != NULL) $filtro .= " AND P.DT_PAGAMENTO BETWEEN '$dtIni' AND '$dtFin' ";

                    break;

                default:
                    $filtro .= '';
                    break;
            }

            /*             * ******************************************************
             *     Cálculo das notas por fornecedor / ano e mês      *
             * ******************************************************* */
            $notas = contasPagarListarNotas($ano, $mes, $idForneced, $filtro);

            foreach ($notas as $key => $nota){
                $linhaTabela .=
                    "<tr class='odd gradeX'>
                            <td>$nota[0]</td>
                            <td align='right'>$nota[1]</td>
                            <td align='left'>" . dateDB2($nota[2]) . "</td>
                            <td align='left'>" . dateDB2($nota[3]) . "</td>
                            <td align='right'>" . numberFormatDB2($nota[4]) . "</td>
                            <td align='right'>" . numberFormatDB2($nota[5]) . "</td>
                            <td align='right'>" . numberFormatDB2($nota[6]) . "</td>
                            <td>$nota[7]</td>
                        </tr>";
            }

            /*             * ******************************************************
             *     Cálculo dos totais por fornecedor / ano e mês     *
             * ******************************************************* */

            /* Se a opção não for 'A Pagar', busca o total de documentos Pagos para exibir no cumulativo.
             * Pois caso seja, apenas ela deve aparecer, se for Pago, apenas ele deve aparecer, e Juros ou
             * Desconto ambos aparecem */
            if ($opcao != 'pagar'){
                $totalPago = contasPagarCumulativoNotas($ano, $mes, 'pago', $idForneced, $filtro);

                if ($totalPago != null)
                        foreach ($totalPago as $key => $nota)
                            $linhaTotal .=
                            "<tr class='odd gradeX'>
                                <td>$nota[0]</td>
                                <td align='right'>" . numberFormatDB2($nota[1]) . "</td>
                                <td align='right'>" . numberFormatDB2($nota[2]) . "</td>
                                <td align='right'>" . numberFormatDB2($nota[3]) . "</td>
                                <td>Pago</td>
                            </tr>";
            }

            if ($opcao != 'pago'){
                $totalPagar = contasPagarCumulativoNotas($ano, $mes, 'pagar', $idForneced, $filtro);

                if ($totalPagar != null)
                        foreach ($totalPagar as $key => $nota)
                            $linhaTotal .=
                            "<tr class='odd gradeX'>
                                <td>$nota[0]</td>
                                <td align='right'>" . numberFormatDB2($nota[1]) . "</td>
                                <td align='right'>" . numberFormatDB2($nota[2]) . "</td>
                                <td align='right'>" . numberFormatDB2($nota[3]) . "</td>
                                <td>A pagar</td>
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
            <div id="empLogo">
                <form>

                </form>
            </div>

            <!-- Sidebar -->
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
                                <h3 class="icon chart">Relatorio Documentos</h3>
                            </div>
                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>NOTA</th>
                                            <th>PARCELA</th>
                                            <th>DATA VENCIMENTO</th>
                                            <th>DATA PAGAMENTO</th>
                                            <th>DESCONTO</th>
                                            <th>JUROS</th>
                                            <th>VALOR</th>
                                            <th>STATUS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
<?php echo $linhaTabela; ?>
                                    </tbody>
                                </table>
                            </div> <!-- .widget-content -->
                        </div> <!-- .widget -->

                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Cumulativo</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>FORNECEDOR</th>
                                            <th>DESCONTO</th>
                                            <th>JUROS</th>
                                            <th>TOTAL</th>
                                            <th>STATUS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
<?php echo $linhaTotal; ?>
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