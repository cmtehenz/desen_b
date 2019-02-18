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
        <title>BID - Análise anual</title>

        <meta charset="utf-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>

        <script type="text/javascript">
            $(document).ready(function() {
                $("#ano").change(function() { $("#frmAnaliseAnual").submit(); });
            });
        </script>
    </head>
    <body>
        <?php
            $post = filter_input_array(INPUT_POST);

            $ano = $post['ano'] ? : date('Y');

            $result = $dbcSQL->analiseAnual($ano, $cliente);

            /**
             * Consolida a tabela, montando um novo array onde hajam todos os meses (mesmo os que não possuem valores).
             * Essa rotina lê os 12 meses e atribui os valores corretos de cada um caso a posição zero do $result bata com o mês
             * atual no loop
             */
            $listaMeses = $dbcSQL->select("SELECT id_mes idMes, descricao FROM mes");

            $analise = array();

            foreach ($listaMeses as $dadosMes){
                $posMes = array();

                /** Verifica se a posição 0 corresponde ao mês lido atualmente e então remove-a do array de valores, jogando a mesma no novo array */
                if ($result[0]['mes'] == $dadosMes['idMes']) $posMes = array_shift($result);

                $posMes['id']   = $dadosMes['idMes'];
                $posMes['nome'] = $dadosMes['descricao'];

                array_push($analise, $posMes);
            }

            /** Escreve a tabela com base no novo array consolidado que possui os 12 meses */
            foreach ($analise as $mes){
                $meses .= "<th>" . substr($mes['nome'], 0, 3) . "</th>";

                $valPeso = $mes['peso'] / 1000;
                $valViag = $mes['viagens'];
                $valFat  = $mes['faturamento'];

                $grfPeso .= "<td>" . $hoUtils->numberFormat($valPeso, 0, 0, '', '') . "</td>";
                $grfViag .= "<td>" . $hoUtils->numberFormat($valViag, 0, 0, '', '') . "</td>";
                $grfFat  .= "<td>" . $hoUtils->numberFormat($valFat  / 1000, 0, 0, '', '') . "</td>";

                $linhaTabela = $linhaTabela .
                    "<tr class='odd gradeX'>
                        <td><a href='mensal.php?ano=$ano&mes=$mes[id]'>" . $mes['nome'] . "</a></td>
                        <td align='right'>" . $hoUtils->numberFormat($valViag, 0, 0) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($valPeso, 0, 0) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($valPeso / $valViag) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($valFat)  . "</td>
                    </tr>";

                $totPeso += $valPeso;
                $totViag += $valViag;
                $totFat  += $valFat;
            }

            /** Parâmetros para QueryString da impressão A4 */
            $qs = array('ano' => $ano);
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
                        <form action="#" method="post" id="frmAnaliseAnual">
                            <div class="field" style="float: left;">
                                <label>Selecione o ano:</label>
                                <select id="ano" name="ano"><?php echo $hoUtils->getOptionsSelectAno($ano); ?></select>
                            </div>
                        </form>

                        <div style="float: right;">
                            <a href="<?php echo $hoUtils->getLinkImpressao(__FILE__, $qs); ?>" target="_blank">
                                <button class="btn btn-black btn-large">Imprimir</button>
                            </a>
                        </div>
                        <div class="clear">&nbsp;</div>

                        <div class="widget">
                            <div class="widget-header">
                                <span class="icon-chart"></span>
                                <h3 class="icon">Peso bruto anual</h3>
                            </div>

                            <div class="widget-content">
                                <table class="stats" data-chart-type="line">
                                    <caption><?php echo $ano; ?> - Toneladas</caption>
                                    <thead>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <?php echo $meses; ?>
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

                        <div class="widget">
                            <div class="widget-header">
                                <span class="icon-chart"></span>
                                <h3 class="icon">Viagens realizadas</h3>
                            </div>

                            <div class="widget-content">
                                <table class="stats" data-chart-type="line">
                                    <caption><?php echo $ano; ?> - Quantidade</caption>
                                    <thead>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <?php echo $meses; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th>Realizado</th>
                                            <?php echo $grfViag; ?>
                                        </tr>
                                    </tbody>
                                </table>
                            </div> <!-- .widget-content -->
                        </div> <!-- .widget -->

                        <div class="widget">
                            <div class="widget-header">
                                <span class="icon-chart"></span>
                                <h3 class="icon">Faturamento anual</h3>
                            </div>

                            <div class="widget-content">
                                <table class="stats" data-chart-type="line">
                                    <caption><?php echo $ano; ?> - Milhares (R$)</caption>
                                    <thead>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <?php echo $meses; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th>Realizado</th>
                                            <?php echo $grfFat; ?>
                                        </tr>
                                    </tbody>
                                </table>
                            </div> <!-- .widget-content -->
                        </div> <!-- .widget -->

                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Análise anual</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width="20%">Mês</th>
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
                                            <th width="20%">Ano</th>
                                            <th>Viagens</th>
                                            <th>Peso bruto (T)</th>
                                            <th>Faturamento</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><?php echo $ano; ?></td>
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