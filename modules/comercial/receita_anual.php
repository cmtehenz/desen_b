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
        <title>BID - Receita anual</title>

        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>

        <script type="text/javascript">
            $(document).ready(function(){
                $("#ano")   .change(function(){ $("#formRecAnual").submit(); });
                $("#imob")  .change(function(){ $("#formRecAnual").submit(); });
                $("#filial").change(function(){ $("#formRecAnual").submit(); });
            });
        </script>
    </head>
    <body>
        <?php
            /********* Variáveis *********/
            date_default_timezone_set('America/sao_paulo');
            $ano  = $_POST['ano'] ?: date('Y');
            $imob = $_POST['imob'];

            if ($_POST['filial']){
                $dbcDB2->selectTopOne("SELECT F.IDCTCUSTO result FROM FILIAL F", array( $dbcDB2->whereParam("SIGLA_FILIAL", $_POST['filial'] )));

                $idCtCusto = $dbcDB2->getResultCell();
            }

            // Monta o gráfico por mês
            $listaMesesGrafico = $dbcSQL->select("SELECT id_mes idMes, descricao FROM mes");

            $previsto = $dbcDB2->receitaPrevisto($ano, $idCtCusto);
            $realizado = $dbcDB2->faturamentoAnual($ano, $imob, $idCtCusto);

            foreach ($listaMesesGrafico as $dadosMes){
                $meses .= "<th>" . substr($dadosMes['descricao'], 0, 3) . "</th>";

                $valPrev = $previsto  ? $previsto [($dadosMes['idMes'] - 1)]['VALOR'] : 0;
                $valReal = $realizado ? $realizado[($dadosMes['idMes'] - 1)]['VALOR'] : 0;

                $grfPrev .= "<td>" . $hoUtils->numberFormat($valPrev / 1000, 0, 0, '', '') . "</td>";
                $grfReal .= "<td>" . $hoUtils->numberFormat($valReal / 1000, 0, 0, '', '') . "</td>";

                $linhaTabela = $linhaTabela .
                    "<tr class='odd gradeX'>
                        <td>$dadosMes[descricao]</td>
                        <td align='right'>" . $hoUtils->numberFormat($valPrev) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($valReal) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat(($valReal / $valPrev) * 100) . "</td>
                    </tr>";

                $totPrev += $valPrev;
                $totReal += $valReal;
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
                        <form action="#" method="post" id="formRecAnual">
                            <div class="field">
                                <label>Selecione o ano:</label>
                                <select id="ano" name="ano"><?php echo $hoUtils->getOptionsSelectAno($ano); ?></select>
                                &nbsp;
                                <label>Filial:</label>
                                <select id="filial" name="filial"><?php echo $hoUtils->getOptionsSelect($dbcDB2->listaFiliais(), $_POST['filial'], 'Todas', true); ?></select>
                                &nbsp;
                                <input type="checkbox" id="imob" name="imob" value="S" <?php echo ($imob ? "checked" : ""); ?>>
                                <label>Calcular venda de imobilizado</label>
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
                                    <caption><?php echo $ano; ?> - Milhões</caption>
                                    <thead>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <?php echo $meses; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th>Previsto</th>
                                            <?php echo $grfPrev; ?>
                                        </tr>
                                        <tr>
                                            <th>Realizado</th>
                                            <?php echo $grfReal; ?>
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
                                            <th>Previsto</th>
                                            <th>Realizado</th>
                                            <th width="10%">Atingido %</th>
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
                                            <th>Previsto</th>
                                            <th>Realizado</th>
                                            <th width="10%">Atingido %</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><?php echo $ano; ?></td>
                                            <td align="right"><?php echo $hoUtils->numberFormat($totPrev); ?></td>
                                            <td align="right"><?php echo $hoUtils->numberFormat($totReal); ?></td>
                                            <td align="right"><?php echo $hoUtils->numberFormat(($totReal / $totPrev) * 100); ?></td>
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