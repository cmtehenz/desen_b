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
        <title>BID - Resumo geral</title>

        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>

        <script type="text/javascript">
            $(document).ready(function(){
                $("#ano").change(function(){ $("#frmResumoGeral").submit(); });
                $("#mes").change(function(){ $("#frmResumoGeral").submit(); });
            });
        </script>
    </head>
    <body>
        <?php
            $post = filter_input_array(INPUT_POST);

            $ano = $post['ano'] ?: date('Y');
            $mes = $post['mes'];

            $params = array();

            if ($mes) array_push($params, $dbcSQL->whereParam("MONTH(c.data)", $mes));

            /** Busca os dados sobre os clientes de destino dos carregamentos */
            $analiseClientes = $dbcSQL->analiseClientes($ano, $params);

            /** Escreve a tabela com base no novo array consolidado que possui os 12 meses */
            foreach ($analiseClientes as $cliente){
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
            }

            /** Busca os dados sobre as fazendas de origem dos carregamentos */
            $analiseFazendas = $dbcSQL->analiseFazendas($ano, $params);

            /** Escreve a tabela com base no novo array consolidado que possui os 12 meses */
            foreach ($analiseFazendas as $fazenda){
                $viagens = $fazenda['viagens'];
                $peso    = $fazenda['peso'];

                $linhaFazendas .=
                    "<tr class='odd gradeX'>
                        <td>$fazenda[nome]</td>
                        <td align='right'>" . $hoUtils->numberFormat($viagens, 0, 0) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($peso   , 0, 0) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat(($peso / $viagens), 0, 0) . "</td>
                    </tr>";
            }

            /** Busca os dados sobre os itens nos carregamentos */
            $analiseItens = $dbcSQL->analiseItens($ano, $params);
            foreach ($analiseItens as $item){
                $linhaItens .=
                    "<tr class='odd gradeX'>
                        <td>$item[nome]</td>
                        <td align='right'>" . $hoUtils->numberFormat($item['viagens']    , 0, 0) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($item['faturamento'], 0, 0) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($item['vlrMedio']) . "</td>
                    </tr>";
            }

            /** Parâmetros para QuertString da impressão A4 */
            $qs = array('ano' => $ano);

            if ($mes) $qs['mes'] = $mes;
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
                        <form action="#" method="post" id="frmResumoGeral">
                            <div class="field" style="float: left;">
                                <label>Selecione o período:</label>
                                <select id="ano" name="ano"><?php echo $hoUtils->getOptionsSelectAno($ano); ?></select>
                                <select id="mes" name="mes"><?php echo $hoUtils->getOptionsSelectMes($mes); ?></select>
                            </div>
                        </form>

                        <div style="float: right;">
                            <a href="<?php echo $hoUtils->getLinkImpressao(__FILE__, $qs); ?>" target="_blank">
                                <button class="btn btn-black btn-large">Imprimir</button>
                            </a>
                        </div>
                        <div class="clear">&nbsp;</div>

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
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Análise de fazendas</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width="40%">Fazenda</th>
                                            <th>Viagens</th>
                                            <th>Peso (T)</th>
                                            <th>Média T/V</th>
                                        </tr>
                                    </thead>
                                    <tbody><?php echo $linhaFazendas; ?></tbody>
                                </table>
                            </div> <!-- .widget-content -->
                        </div> <!-- .widget -->

                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Análise de itens</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width="40%">Item</th>
                                            <th>Viagens</th>
                                            <th>Faturamento</th>
                                            <th>Valor médio</th>
                                        </tr>
                                    </thead>
                                    <tbody><?php echo $linhaItens; ?></tbody>
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