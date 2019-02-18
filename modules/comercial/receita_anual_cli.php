<?php
    namespace Modulos\Comercial;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();
    $dbcDB2  = new \Library\Scripts\scriptDB2();

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Receita anual por cliente</title>

        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
    </head>

    <body>
        <?php
            /********* Variáveis *********/
            date_default_timezone_set('America/sao_paulo');

            $trunco = ($_GET['trunco'] == "S");

            $post = filter_input_array(INPUT_POST);

            $ano  = $post['ano'] ?: date('Y');
            $cliente = $post['cliente'];

            /** Monta o gráfico por mês */
            $listaMesesGrafico = $dbcSQL->select("SELECT id_mes idMes, descricao FROM mes");

            $realizado = $dbcDB2->faturamentoAnual($ano    , true, null, $cliente);
            $anterior  = $dbcDB2->faturamentoAnual($ano - 1, true, null, $cliente);
            $orcamento = $dbcSQL->orcamentoCliente($ano, $trunco, $cliente);

            foreach ($listaMesesGrafico as $dadosMes){
                $meses .= "<th>" . substr($dadosMes['descricao'], 0, 3) . "</th>";

                $valAntr = $anterior  ? $anterior [($dadosMes['idMes'] - 1)]['VALOR'] : 0;
                $valOrca = $orcamento ? $orcamento[($dadosMes['idMes'] - 1)]['valor'] : 0;
                $valReal = $realizado ? $realizado[($dadosMes['idMes'] - 1)]['VALOR'] : 0;

                $grfAntr .= "<td>" . $hoUtils->numberFormat($valAntr / 1000, 0, 0, '', '') . "</td>";
                $grfOrca .= "<td>" . $hoUtils->numberFormat($valOrca / 1000, 0, 0, '', '') . "</td>";
                $grfReal .= "<td>" . $hoUtils->numberFormat($valReal / 1000, 0, 0, '', '') . "</td>";

                $linhaTabela .=
                    "<tr class='odd gradeX'>
                        <td>$dadosMes[descricao]</td>
                        <td align='right'>" . $hoUtils->numberFormat($valAntr) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($valOrca) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($valReal) . "</td>
                    </tr>";

                $totAntr += $valAntr;
                $totOrca += $valOrca;
                $totReal += $valReal;
            }

            /** Monta o <select> de clientes */
            $listaClientes = $dbcDB2->listaClientes($trunco);

            if ($trunco) $selectClientes = $hoUtils->getOptionsSelect($listaClientes, $cliente, 'Todos', true);
            else {
                $selectClientes = "<option>Todos</option>";

                foreach ($listaClientes as $cliente){
                    $selectClientes .=
                        "<option value='" . trim($cliente['CGC']) . "' " . ((trim($cliente['CGC']) == $_POST['cliente']) ? "selected" : "") . ">"
                            . $hoUtils->cnpjCpfFormat($cliente['CGC']) . " - " . utf8_encode($cliente['RAZAO']) .
                        "</option>";
                }
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
                        <form action="#" method="post" id="formRecCliAnu">
                            <div class="field">
                                <label>Selecione o ano:</label>
                                <select id="ano" name="ano"><?php echo $hoUtils->getOptionsSelectAno($ano); ?></select>
                                &nbsp;
                                <label>Cliente:</label>
                                <select id="cliente" name="cliente"><?php echo $selectClientes; ?></select>
                                &nbsp;
                                <button class="btn btn-primary"><span class="icon-magnifying-glass"></span>Buscar</button>
                            </div>
                        </form>
                        <br />

                        <div class="widget">
                            <div class="widget-header">
                                <span class="icon-chart"></span>
                                <h3 class="icon">Receita bruta anual</h3>
                            </div>

                            <div class="widget-content">
                                <table class="stats" data-chart-type="line">
                                    <caption>Valores em milhões de reais</caption>
                                    <thead>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <?php echo $meses; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th>Realizado</th>
                                            <?php echo $grfReal; ?>
                                        </tr>
                                        <tr>
                                            <th>Orçamento</th>
                                            <?php echo $grfOrca; ?>
                                        </tr>
                                        <tr>
                                            <th>Ano anterior</th>
                                            <?php echo $grfAntr; ?>
                                        </tr>
                                    </tbody>
                                </table>
                            </div> <!-- .widget-content -->
                        </div> <!-- .widget -->

                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Receita bruta mensal</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width="20%">Mês</th>
                                            <th>Ano anterior</th>
                                            <th>Orçamento</th>
                                            <th>Realizado</th>
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
                                            <th>Ano anterior</th>
                                            <th>Orçamento</th>
                                            <th>Realizado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><?php echo $ano; ?></td>
                                            <td align="right"><?php echo $hoUtils->numberFormat($totAntr); ?></td>
                                            <td align="right"><?php echo $hoUtils->numberFormat($totOrca); ?></td>
                                            <td align="right"><?php echo $hoUtils->numberFormat($totReal); ?></td>
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