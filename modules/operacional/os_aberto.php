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
        <title>BID - OS em aberto</title>

        <meta charset="utf-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
    </head>
    <body>
        <?php
            /** Lista de OS abertas no mês atual e ainda não concluidas */
            $sql =
                "SELECT
                    O.NUMORDEM numero, O.PLACA, DAYS(CURRENT DATE) - DAYS(DATA_ABRE) dias, F.DESCRICAO oficina, O.OBSERVACAO obs,
                    DECODE(O.STATUS, 'P', 'Pendente', 'L', 'Liberada', 'A', 'Aberta') status,
                    DECODE(O.TIPMANUT, 'P', 'Preventiva', 'C', 'Corretiva', 'Indefinido') tipo, (O.DATA_PREVISAO || ' ' || O.HORA_PREVISAO) previsao
                 FROM ORDEMSER O
                 JOIN OFICINA F ON O.ID_OFICINA = F.ID_OFICINA
                 WHERE O.STATUS IN ('A','L','P')";

            $order = "DAYS(CURRENT DATE) - DAYS(DATA_ABRE) DESC, O.TIPMANUT DESC, O.NUMORDEM";

            $listaOS = $dbcDB2->select($sql, $params, $order);

            foreach ($listaOS as $os){
                $linhaTabela .=
                    "<tr class='gradeA'>
                        <td class='text-right'>$os[NUMERO]</td>
                        <td>$os[PLACA]</td>
                        <td>$os[TIPO]</td>
                        <td>$os[STATUS]</td>
                        <td>$os[OFICINA]</td>
                        <td>$os[OBS]</td>
                        <td>" . $hoUtils->dateFormat($os['PREVISAO'], 'Y-m-d H:i', 'd/m/Y H:i') . "</td>
                        <td class='text-center'>$os[DIAS]</td>
                    </tr>";

                if ($os['TIPO'] === 'Preventiva') $totalP++;
                if ($os['TIPO'] === 'Corretiva')  $totalC++;
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
                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-wrench"></span>
                                <h3 class="icon chart">Ordens de serviço em aberto</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Nº</th>
                                            <th>Placa</th>
                                            <th>Tipo</th>
                                            <th>Status</th>
                                            <th width="17%">Oficina</th>
                                            <th>Observação</th>
                                            <th width="12%">Previsão</th>
                                            <th width="8%">Dias parado</th>
                                        </tr>
                                    </thead>
                                    <tbody><?php echo $linhaTabela; ?></tbody>
                                </table>
                            </div> <!-- .widget-content -->
                        </div> <!-- .widget -->

                        <div class="box plain">
                            <div class="widget-header">
                                <span class="icon-info"></span>
                                <h3 class="icon chart">Cumulativo</h3>
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
                                        <td><?php echo $_SESSION['nomeEmpresa']; ?></td>
                                        <td class='text-center'><?php echo $totalP ?: 0; ?></td>
                                        <td class='text-center'><?php echo $totalC ?: 0; ?></td>
                                        <td class='text-center'><?php echo $totalP + $totalC ?: 0; ?></td>
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