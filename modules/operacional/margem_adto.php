<?php
    namespace Modulos\Operacional;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcDB2  = new \Library\Scripts\scriptDB2();
    $dbcSQL  = new \Library\Scripts\scriptSQL();

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Margens de adto.</title>

        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>

        <script type="text/javascript">
            $(document).ready(function(){
                $("#ano").change(function(){ $("#frmMargAdto").submit(); });
                $("#mes").change(function(){ $("#frmMargAdto").submit(); });
            });
        </script>
    </head>
    <body>
        <?php
            date_default_timezone_set('America/sao_paulo');

            $post = filter_input_array(INPUT_POST);

            $ano = $post['ano'] ?: date('Y');
            $mes = $post['mes'];

            /** Busca a média de valores de adiantamento praticados por cada filial */
            $filiais = $dbcDB2->margensAdto($ano, $mes);

            foreach ($filiais as $filial){
                $valFrete   = $hoUtils->numberCorrect($filial['VALFRETE']);
                $valAdto    = $hoUtils->numberCorrect($filial['VALADTO']);
                $prcFilial  = $hoUtils->numberCorrect($filial['PRCFILIAL']);
                $margemAdto = $hoUtils->numberCorrect($filial['PRCADTO']);

                $margemValor  = ($valAdto / $valFrete) * 100;
                $bgcolorValor = ($margemValor > $prcFilial) ? "#FF4D4D" : "#82CD9B";
                $bgcolorAdto  = ($margemAdto  > $prcFilial) ? "#FF4D4D" : "#82CD9B";

                $linhaTabela .=
                    "<tr class='gradeA'>
                        <td>$filial[FILIAL]</td>
                        <td class='text-right'>" . $hoUtils->numberFormat($filial['VALFRETE']) . "</td>
                        <td class='text-right'>" . $hoUtils->numberFormat($filial['VALADTO']) . "</td>
                        <td class='text-center' style='background-color: $bgcolorValor;'>" . $hoUtils->numberFormat($margemValor) . "</td>
                        <td class='text-center' style='background-color: $bgcolorAdto;'>"  . $hoUtils->numberFormat($margemAdto) . "</td>
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
                        <form action="#" method="post" id="frmMargAdto" name="frmMargAdto" class="form uniformForm">
                            <div class="field-group control-group inline" style="float: left; margin-right: 10px; margin-bottom: 10px;">
                                <label>Período</label>
                                <div class="field">
                                    <select id="ano" name="ano"><?php echo $hoUtils->getOptionsSelectAno($ano); ?></select>
                                    <select id="mes" name="mes"><?php echo $hoUtils->getOptionsSelectMes($mes); ?></select>
                                </div>
                            </div>
                        </form>

                        <div class="grid-24 box notify-info" style="margin: 10px 0px 15px 0px; padding: 10px; width: 98%;">
                            <b>Legenda de informações</b><br /><br />
                            <b>&#9679; Média de vlr. do frete -</b> Valor médio de fretes encontrados na filial. <br />
                            <b>&#9679; Média de vlr. do adto. -</b> Valor médio dos adiantamentos pagos pela filial para seus fretes. <br />
                            <b>&#9679; Média % (total) -</b> Percentual médio de adiantamento calculado em cima dos valores totais
                                (<u>Média de vlr. do adto.</u> dividido pela <u>Média de vlr. do frete</u>). <br />
                            <b>&#9679; Média % (individual) -</b> Percentual médio de adiantamento calculado caso a caso
                                (<u>Valor do adto.</u> dividido pelo <u>Valor do frete</u> de cada BIPE).
                        </div>

                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-info"></span>
                                <h3 class="icon chart">Margens de adiantamento por filial</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width="40%">Filial</th>
                                            <th>Média vlr. do frete</th>
                                            <th>Média vlr. do adto.</th>
                                            <th>Média % (total)</th>
                                            <th>Média % (individual)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php echo $linhaTabela; ?>
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

        <div id="footer"><div style="float: left;">Versão <?php echo $_SESSION['version']; ?></div> Copyright &copy; <?php echo date('Y'); ?>, Case Electronic Ltda.</div>
    </body>
</html>