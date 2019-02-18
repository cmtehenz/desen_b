<?php
    namespace Modulos\SemParar;

    require $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();
    $dbcDB2  = new \Library\Classes\connectDB2();

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Resumo do Sem Parar</title>

        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/modernizr.js"); ?>"></script>
        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
        <script>
            Modernizr.load({
                test: Modernizr.inputtypes.date,
                nope: ['http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.7/jquery-ui.min.js', 'jquery-ui.css'],
                complete: function () {
                    $('input[type=date]').datepicker({ dateFormat: 'yy-mm-dd' });
                }
            });
        </script>
    </head>
    <body>
        <?php
            /********* Variáveis *********/
            date_default_timezone_set('America/sao_paulo');
            $ano = date('Y');

            $dtIni = $_POST['dtIni'] ?: date('Y-m-01');
            $dtFin = $_POST['dtFin'] ?: date('Y-m-d');

            $dbcDB2->connect();

            $listaPlacas = $dbcDB2->select(
                   "SELECT PLACA placa, SUM(C.VALPEDSICMS) pedagio FROM CT C
                    JOIN HVEICULO V ON V.ID_HVEICULO = C.ID_HVEICULO
                    JOIN HVEICEMP E ON (E.IDHVEICEMP = C.IDHVEICEMP AND E.ID_EMPRESA = 1)
                    WHERE E.STAFT = 'F' AND C.DATAEMISSAO BETWEEN '$dtIni' AND '$dtFin'
                    GROUP BY V.PLACA ORDER BY V.PLACA");

            foreach ($listaPlacas as $cte){
                $resumo = $dbcSQL->resumoSemParar($dtIni, $dtFin, $cte['PLACA']);

                // Removendo a vírgula do DB2 para somar corretamente no saldo
                $cte['PEDAGIO'] = $hoUtils->numberFormat($cte['PEDAGIO'], 0, 2, '.', '');

                $saldo = $cte['PEDAGIO'] - $resumo['vlrPsg'] + $resumo['vlrCrd'];

                $color = $saldo >= 0 ? "#66C285" : "#FF4D4D";

                $linkPlaca = "sublists/placa.php?dtIni=$dtIni&dtFin=$dtFin&placa=$cte[PLACA]";

                $linhaTabela .=
                    "<tr class='gradeA'>
                        <td><a href='$linkPlaca'>$cte[PLACA]</a></td>
                        <td align='right'>" . $hoUtils->numberFormat($cte['PEDAGIO']) . "</td>
                        <td align='right'>" . ($resumo[qtdPsg] ?: 0) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($resumo['vlrPsg']) . "</td>
                        <td align='right'>" . ($resumo[qtdCrd] ?: 0) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($resumo['vlrCrd']) . "</td>
                        <td align='right' style='background-color: $color;'>" . $hoUtils->numberFormat($saldo) . "</td>
                    </tr>";

                $totalVlrS += $saldo;
                $totalQtdP += $resumo['qtdPsg'];
                $totalVlrP += $resumo['vlrPsg'];
                $totalQtdC += $resumo['qtdCrd'];
                $totalVlrC += $resumo['vlrCrd'];
                $totalCTe  += $cte['PEDAGIO'];
            }

            $dbcDB2->disconnect();
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
                        <form action="#" method="post" name="formResumo">
                            <div class="field">
                                <label>Selecione o período:&nbsp;</label>
                                <input type="date" id="dtIni" name="dtIni" style="width: 130px" maxlength="10" value="<?php echo $dtIni; ?>" />
                                <input type="date" id="dtFin" name="dtFin" style="width: 130px" maxlength="10" value="<?php echo $dtFin; ?>" />
                                &nbsp;
                                <button class="btn btn-primary">Buscar</button>
                            </div>
                        </form>

                        <div class="grid-24 box notify-info" style="margin: 10px 0px 15px 0px; padding: 10px; width: 98%;">
                            <b>&#9679; Saldo = CTe - Passagens + Créditos (reembolsos)</b>
                        </div>

                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Resumo de pedágios por placa</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width="10%">Placa</th>
                                            <th width="15%">Pago no CT-e (R$)</th>
                                            <th width="15%">Passagens</th>
                                            <th width="15%">Passagens (R$)</th>
                                            <th width="15%">Créditos de reenvio</th>
                                            <th width="15%">Créditos (R$)</th>
                                            <th width="15%">Saldo (R$)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php echo $linhaTabela; ?>
                                    </tbody>
                                </table>
                            </div> <!-- .widget-content -->
                        </div> <!-- .widget -->

                        <div class="box plain">
                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Cumulativo</h3>
                            </div>
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th width="10%">Empresa</th>
                                        <th width="15%">CT-e (R$)</th>
                                        <th width="15%">Passagens</th>
                                        <th width="15%">Valor (R$)</th>
                                        <th width="15%">Créditos de reenvio</th>
                                        <th width="15%">Valor (R$)</th>
                                        <th width="15%">Saldo (R$)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="odd gradeX">
                                        <td><?php echo $_SESSION['nomeEmpresa']; ?></td>
                                        <td align="right"><?php echo $hoUtils->numberFormat($totalCTe); ?></td>
                                        <td align="right"><?php echo $hoUtils->numberFormat($totalQtdP, 0, 0); ?></td>
                                        <td align="right"><?php echo $hoUtils->numberFormat($totalVlrP); ?></td>
                                        <td align="right"><?php echo $hoUtils->numberFormat($totalQtdC, 0, 0); ?></td>
                                        <td align="right"><?php echo $hoUtils->numberFormat($totalVlrC); ?></td>
                                        <td align="right" style="background-color: <?php echo ($totalVlrS >= 0) ? "#66C285" : "#FF4D4D"; ?>;">
                                            <?php echo $hoUtils->numberFormat($totalVlrS); ?>
                                        </td>
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
        </div> <!-- #wrapper -->

        <div id="footer">
            <div style="float: left;">Versão <?php echo $_SESSION['version']; ?></div> Copyright &copy; <?php echo date('Y'); ?>, Case Electronic Ltda.
        </div> <!-- #footer -->
    </body>
</html>