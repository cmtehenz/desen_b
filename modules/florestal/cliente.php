<?php
    namespace Modulos\Florestal;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));

    date_default_timezone_set('America/sao_paulo');
    setlocale(LC_ALL, "ptb");
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Análise por cliente</title>

        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>

        <script type="text/javascript">
            $(document).ready(function(){
                $("#ano").change(function(){ $("#frmAnaliseCliente").submit(); });
                $("#mes").change(function(){ $("#frmAnaliseCliente").submit(); });
            });
        </script>
    </head>
    <body>
        <?php
            $post = filter_input_array(INPUT_POST);

            $ano = $post['ano'] ?: date('Y');
            $mes = $post['mes'] ?: ($post ? null : date('m')); // Coloca o mês atual como padrão apenas se não houver POST (pois pode vir o POST de todos os meses) 

            $params = array();

            if ($mes) array_push($params, $dbcSQL->whereParam("MONTH(c.data)", $mes));

            /** Busca os dados sobre os clientes de destino dos carregamentos */
            $analise = $dbcSQL->analiseClientes($ano, $params);

            /** Escreve a tabela com base no novo array consolidado que possui os 12 meses */
            foreach ($analise as $cliente){
                $viagens     = $cliente['viagens'];
                $peso        = $cliente['peso'];
                $faturamento = $cliente['faturamento'];
                $quinzena1   = $cliente['quinzena1'];
                $quinzena2   = $cliente['quinzena2'];

                $totViagens += $viagens;
                $totPeso    += $peso;
                $totFat     += $faturamento;

                $linhaClientes .=
                    "<tr class='odd gradeX'>
                        <td>$cliente[nome]</td>
                        <td align='right'>" . $hoUtils->numberFormat($viagens, 0, 0) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($peso   , 0, 0) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat(($peso / $viagens), 0, 0) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($faturamento) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat(($faturamento / ($peso / 1000)), 0, 0) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($quinzena1) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($quinzena2) . "</td>
                    </tr>";
                
                /** Dados do gráfico */
                $labelClientes .= "<th>" . substr($cliente['nome'], 0, 20) . "</th>";
                $grfPeso       .= "<td>" . ($peso / 1000) . "</td>";
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
                        <form action="#" method="post" id="frmAnaliseCliente">
                            <div class="field">
                                <label>Selecione o período:</label>
                                <select id="ano" name="ano"><?php echo $hoUtils->getOptionsSelectAno($ano); ?></select>
                                <select id="mes" name="mes"><?php echo $hoUtils->getOptionsSelectMes($mes); ?></select>
                            </div>
                        </form>
                        <br />

                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Análise de clientes</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width="40%">Cliente</th>
                                            <th>Viagens</th>
                                            <th>Peso (T)</th>
                                            <th>Média T/V</th>
                                            <th>Faturamento</th>
                                            <th>Média F/T</th>
                                            <th>1ª Quinzena</th>
                                            <th>2ª Quinzena</th>
                                        </tr>
                                    </thead>
                                    <tbody><?php echo $linhaClientes; ?></tbody>
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
                                            <th width="20%">Período</th>
                                            <th>Viagens</th>
                                            <th>Peso (T)</th>
                                            <th>Tonelada / viagem</th>
                                            <th>Faturamento</th>
                                            <th>Faturamento / tonelada</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><?php echo ($mes ? ($hoUtils->monthName($mes) . " / ") : "") . $ano; ?></td>
                                            <td align="right"><?php echo $hoUtils->numberFormat($totViagens, 0, 0); ?></td>
                                            <td align="right"><?php echo $hoUtils->numberFormat($totPeso, 0, 0); ?></td>
                                            <td align="right"><?php echo $hoUtils->numberFormat($totPeso / $totViagens, 0, 0); ?></td>
                                            <td align="right"><?php echo $hoUtils->numberFormat($totFat); ?></td>
                                            <td align="right"><?php echo $hoUtils->numberFormat($totFat / ($totPeso / 1000), 0, 0); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div> <!-- .widget-content -->
                        </div> <!-- .widget -->
                        
                        <div class="widget">
                            <div class="widget-header">
                                <span class="icon-bars"></span>
                                <h3 class="icon">Peso carregado</h3>
                            </div>

                            <div class="widget-content">
                                <table class="stats" data-chart-type="bar">
                                    <caption><?php echo ($mes . " / " . $ano); ?> - Toneladas</caption>
                                    <thead>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <?php echo $labelClientes; ?>
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