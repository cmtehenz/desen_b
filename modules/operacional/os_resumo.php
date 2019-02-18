<?php
    namespace Modulos\Operacional;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcDB2  = new \Library\Scripts\scriptDB2();

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Resumo OS</title>

        <meta charset="utf-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>

        <script type="text/javascript">
            $(document).ready(function(){
                $("#ano").change(function(){ $("#formResumoOS").submit(); });
                $("#mes").change(function(){ $("#formResumoOS").submit(); });
            });
        </script>
    </head>
    <body>
        <?php
            $post = filter_input_array(INPUT_POST);

            $ano = $post['ano'] ?: date('Y');
            $mes = $post['mes'] ?: date('m');

            /** Lista de quantidades de OS que ficaram em aberto por N dias */
            $sql =
                "SELECT
                    DAYS(O.DATA_CONCLUI) - DAYS(O.DATA_ABRE) dias,
                     SUM(CASE O.TIPMANUT WHEN 'P' THEN 1 ELSE 0 END) preventivas,
                     SUM(CASE O.TIPMANUT WHEN 'C' THEN 1 ELSE 0 END) corretivas
                 FROM ORDEMSER O";

            $params = array (
                $dbcDB2->whereParam("YEAR(O.DATA_ABRE)", $ano), $dbcDB2->whereParam("MONTH(O.DATA_ABRE)", $mes), $dbcDB2->whereParam("O.STATUS", "B")
            );

            $listaOS = $dbcDB2->select($sql, $params, null, "DAYS(O.DATA_CONCLUI) - DAYS(O.DATA_ABRE)");

            foreach ($listaOS as $linha){
                $dias = $linha['DIAS'];

                $linkOSBaixada = "os_baixado.php?ano=$ano&mes=$mes&dias=$dias";

                $linhaTabela .=
                    "<tr class='gradeA'>
                        <td class='text-center'>$dias</td>
                        <td class='text-center'><a href='$linkOSBaixada&tipo=P'>$linha[PREVENTIVAS]</a></td>
                        <td class='text-center'><a href='$linkOSBaixada&tipo=C'>$linha[CORRETIVAS]</a></td>
                        <td class='text-center'><a href='$linkOSBaixada'>" . ($linha['PREVENTIVAS'] + $linha['CORRETIVAS']) . "</a></td>
                    </tr>";

                $totalP += $linha['PREVENTIVAS'];
                $totalC += $linha['CORRETIVAS'];
            }

            /** Total de ordens em aberto agora */
            $sql =
                "SELECT
                    SUM(CASE O.TIPMANUT WHEN 'P' THEN 1 ELSE 0 END) preventivas,
                    SUM(CASE O.TIPMANUT WHEN 'C' THEN 1 ELSE 0 END) corretivas
                 FROM ORDEMSER O
                 WHERE YEAR(O.DATA_ABRE) = $ano AND MONTH(O.DATA_ABRE) = $mes AND O.STATUS IN ('P', 'A', 'L')";

            $abertas = $dbcDB2->selectTopOne($sql);

            $totalP += $abertas['PREVENTIVAS'];
            $totalC += $abertas['CORRETIVAS'];
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
                        <form action="#" method="post" id="formResumoOS" name="formResumoOS">
                            <div class="field">
                                <label>Selecione o período:</label>
                                <select id="ano" name="ano"><?php echo $hoUtils->getOptionsSelectAno($ano); ?></select>
                                <select id="mes" name="mes"><?php echo $hoUtils->getOptionsSelectMes($mes, false); ?></select>
                            </div>
                        </form>
                        <br />

                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-wrench"></span>
                                <h3 class="icon chart">Resumo de ordens de serviço já concluídas</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th width="25%">Dias em aberto</th>
                                            <th>Preventivas</th>
                                            <th>Corretivas</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody><?php echo $linhaTabela; ?></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="box notify-info" style="margin: 10px 0px 15px 0px; padding: 10px; width: 98%; font-size: 15px;">
                            <b>&#9679; Em aberto agora:</b> <?php echo $abertas['PREVENTIVAS'] ?: 0; ?> preventivas e <?php echo $abertas['CORRETIVAS'] ?: 0; ?> corretivas
                        </div>

                        <div class="box plain">
                            <div class="widget-header">
                                <span class="icon-info"></span>
                                <h3 class="icon chart">Totais no mês</h3>
                            </div>
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th width="25%">Empresa</th>
                                        <th>Preventivas</th>
                                        <th>Corretivas</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="odd gradeX">
                                        <?php $linkOSBaixada = "os_baixado.php?ano=$ano&mes=$mes"; ?>
                                        <td><?php echo $_SESSION['nomeEmpresa']; ?></td>
                                        <td class='text-center'><?php echo "<a href='$linkOSBaixada&tipo=P'>$totalP</a>"; ?></td>
                                        <td class='text-center'><?php echo "<a href='$linkOSBaixada&tipo=C'>$totalC</a>"; ?></td>
                                        <td class='text-center'><?php echo "<a href='$linkOSBaixada'>" . ($totalP + $totalC) . "</a>"; ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
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