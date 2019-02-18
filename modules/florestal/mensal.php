<?php
    namespace Modulos\Florestal;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL = new \Library\Scripts\scriptSQL();

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));

    date_default_timezone_set('America/sao_paulo');
    setlocale(LC_ALL, "ptb");
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Análise mensal</title>

        <meta charset="utf-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>

        <script type="text/javascript">
            $(document).ready(function() {
                $("#ano").change(function() { $("#frmAnaliseMensal").submit(); });
                $("#mes").change(function() { $("#frmAnaliseMensal").submit(); });
            });
        </script>
    </head>
    <body>
        <?php
            $post = filter_input_array(INPUT_POST);

            $ano = $post['ano'] ? : date('Y');
            $mes = $post['mes'] ? : date('m');

            $analise = $dbcSQL->analiseMensal($ano, $mes);

            /** Percorre os dias do mês para escrever as linhas do gráfico e tabela */
            for ($dia = 1; $dia <= cal_days_in_month(CAL_GREGORIAN, $mes, $ano); $dia++){
                $labelDias .= "<th>" . str_pad($dia, 2, "0", STR_PAD_LEFT) . "</th>"; // Label do gráfico (eixo X)

                /**
                 * Verifica se o dia corrente existe no array de consulta (posição zero). Caso haja, pegamos seu valores e
                 * removemos do array para que as próximas linhas "desçam"
                 */
                $viagens     = ($analise[0]['dia'] == $dia) ? $analise[0]['viagens'] : 0;
                $peso        = ($analise[0]['dia'] == $dia) ? $analise[0]['peso'] : 0;
                $faturamento = ($analise[0]['dia'] == $dia) ? $analise[0]['faturamento'] : 0;

                if ($analise[0]['dia'] == $dia) array_shift($analise);

                $linhaTabela .=
                    "<tr class='gradeA'>
                        <td>$dia (" . utf8_encode(strftime('%a', mktime(0, 0, 0, $mes, $dia, $ano))) . ")</td>
                        <td align='right'>" . $hoUtils->numberFormat($viagens, 0, 0) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($peso, 0, 0) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($peso / $viagens, 0, 0) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($faturamento, 0, 0) . "</td>
                    </tr>";

                $grfViagens .= "<td>$viagens</td>";
                $grfPeso    .= "<td>$peso</td>";

                $totViag += $viagens;
                $totPeso += $peso;
                $totFat  += $faturamento;
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
                        <form action="#" method="post" id="frmAnaliseMensal">
                            <div class="field">
                                <label>Selecione o período:</label>
                                <select id="ano" name="ano"><?php echo $hoUtils->getOptionsSelectAno($ano); ?></select>
                                <select id="mes" name="mes"><?php echo $hoUtils->getOptionsSelectMes($mes, false); ?></select>
                            </div>
                        </form>
                        <br />

                        <div class="widget">
                            <div class="widget-header">
                                <span class="icon-chart"></span>
                                <h3 class="icon">Viagens</h3>
                            </div>

                            <div class="widget-content">
                                <table class="stats" data-chart-type="line">
                                    <caption><?php echo ($mes . " / " . $ano); ?> - Quantidade</caption>
                                    <thead>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <?php echo $labelDias; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th>Realizado</th>
                                            <?php echo $grfViagens; ?>
                                        </tr>
                                    </tbody>
                                </table>
                            </div> <!-- .widget-content -->
                        </div> <!-- .widget -->

                        <div class="widget">
                            <div class="widget-header">
                                <span class="icon-chart"></span>
                                <h3 class="icon">Peso carregado</h3>
                            </div>

                            <div class="widget-content">
                                <table class="stats" data-chart-type="line">
                                    <caption><?php echo ($mes . " / " . $ano); ?> - Toneladas</caption>
                                    <thead>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <?php echo $labelDias; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th>Realizado</th>
                                            <?php echo $grfPeso; ?>
                                        </tr>
                                    </tbody>
                                </table>
                            </div> <!-- .widget-content -->
                        </div> <!-- .widget -->

                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Análise mensal</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width="20%">Dia</th>
                                            <th>Viagens</th>
                                            <th>Peso bruto (T)</th>
                                            <th>Média T/V</th>
                                            <th>Faturamento</th>
                                        </tr>
                                    </thead>
                                    <tbody><?php echo $linhaTabela; ?></tbody>
                                </table>
                            </div> <!-- .widget-content -->
                        </div> <!-- .widget -->

                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-layers"></span>
                                <h3 class="icon">Cumulativo</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width="20%">Mês</th>
                                            <th>Viagens</th>
                                            <th>Peso bruto (T)</th>
                                            <th>Faturamento</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><?php echo $hoUtils->monthName($mes) . " / " . $ano; ?></td>
                                            <td align="right"><?php echo $hoUtils->numberFormat($totViag, 0, 0); ?></td>
                                            <td align="right"><?php echo $hoUtils->numberFormat($totPeso, 0, 0); ?></td>
                                            <td align="right"><?php echo $hoUtils->numberFormat($totFat); ?></td>
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
        </div> <!-- #wrapper -->

        <div id="footer">
            <div style="float: left;">Versão <?php echo $_SESSION['version']; ?></div> Copyright &copy; <?php echo date('Y'); ?>, Case Electronic Ltda.
        </div>
    </body>
</html>