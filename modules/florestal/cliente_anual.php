<?php
    namespace Modulos\Florestal;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();
    $dbcDB2  = new \Library\Scripts\scriptDB2();

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Análise anual por cliente</title>

        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>

        <script type="text/javascript">
            $(document).ready(function(){
                $("#ano")    .change(function(){ $("#frmAnaliseAnualCli").submit(); });
                $("#cliente").change(function(){ $("#frmAnaliseAnualCli").submit(); });
            });
        </script>
    </head>
    <body>
        <?php
            /********* Variáveis *********/
            date_default_timezone_set('America/sao_paulo');

            $post = filter_input_array(INPUT_POST);

            $ano     = $post['ano'] ?: date('Y');
            $cliente = $post['cliente'];

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

                $posMes['nome'] = $dadosMes['descricao'];

                array_push($analise, $posMes);
            }

            /** Escreve a tabela com base no novo array consolidado que possui os 12 meses */
            foreach ($analise as $mes){
                $viagens     = $mes['viagens'];
                $peso        = $mes['peso'];
                $faturamento = $mes['faturamento'];
                $quinzena1   = $mes['quinzena1'];
                $quinzena2   = $mes['quinzena2'];

                $totViagens += $viagens;
                $totPeso    += $peso;
                $totFat     += $faturamento;

                $linhaTabela .=
                    "<tr class='odd gradeX'>
                        <td>$mes[nome]</td>
                        <td align='right'>" . $hoUtils->numberFormat($viagens, 0, 0) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($peso   , 0, 0) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat(($peso / $viagens), 0, 0) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($faturamento) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat(($faturamento / ($peso / 1000)), 0, 0) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($quinzena1) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($quinzena2) . "</td>
                    </tr>";
            }

            /** Monta o <select> de clientes */
            $listaClientes = $dbcSQL->select("SELECT idCliente '0', descricao '1' FROM flr.cliente", null, "descricao");

            $selectClientes = $hoUtils->getOptionsSelect($listaClientes, $cliente, 'Todos', true);

            /** Parâmetros para QuertString da impressão A4 */
            $qs = array('ano' => $ano);

            if ($cliente) $qs['cliente'] = $cliente;
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
                        <form action="#" method="post" id="frmAnaliseAnualCli">
                            <div class="field" style="float: left;">
                                <label>Selecione o ano:</label>
                                <select id="ano" name="ano"><?php echo $hoUtils->getOptionsSelectAno($ano); ?></select>
                                &nbsp;
                                <label>Cliente:</label>
                                <select id="cliente" name="cliente"><?php echo $selectClientes; ?></select>
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
                                <h3 class="icon chart">Análise mensal por cliente</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width="20%">Mês</th>
                                            <th>Viagens</th>
                                            <th>Peso (T)</th>
                                            <th>Média T/V</th>
                                            <th>Faturamento</th>
                                            <th>Média F/T</th>
                                            <th>1ª Quinzena</th>
                                            <th>2ª Quinzena</th>
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
                                            <th>Peso (T)</th>
                                            <th>Média T/V</th>
                                            <th>Faturamento</th>
                                            <th>Média F/T</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><?php echo $ano; ?></td>
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