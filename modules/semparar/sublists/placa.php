<?php
    namespace Modulos\SemParar;

    require $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();
    $dbcDB2  = new \Library\Scripts\scriptDB2();

    $_SESSION['modulo'] = basename(str_replace("sublists", "", dirname($_SERVER['PHP_SELF'])));
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Sem Parar x CT-e</title>

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
            $dtIni = $_POST['dtIni'] ?: $_GET['dtIni'] ?: date('Y-m-01');
            $dtFin = $_POST['dtFin'] ?: $_GET['dtFin'] ?: date('Y-m-d');
            $placa = $_POST['placa'] ?: $_GET['placa'];

            $listaPassagens = $dbcSQL->passagensSemParar(
                array(
                    $dbcSQL->whereParam("g.data", $dtIni, ">="),
                    $dbcSQL->whereParam("g.data", $dtFin, "<="),
                    $dbcSQL->whereParam("v.placa", $placa)
                )
            );

            $listaCreditos = $dbcSQL->creditosSemParar(
                array(
                    $dbcSQL->whereParam("c.data", $dtIni, ">="),
                    $dbcSQL->whereParam("c.data", $dtFin, "<="),
                    $dbcSQL->whereParam("v.placa", $placa)
                )
            );

            $listaCte = $dbcDB2->ctesSemParar(
                array(
                    $dbcDB2->whereParam("c.dataemissao", $dtIni, ">="),
                    $dbcDB2->whereParam("c.dataemissao", $dtFin, "<="),
                    $dbcDB2->whereParam("v.placa", $placa)
                )
            );
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
                        <form action="#" method="post" id="formFaturas" name="formFaturas">
                            <div class="field field-group inline">
                                <label>Período:&nbsp;</label>
                                <input type="date" id="dtIni" name="dtIni" style="width: 130px" maxlength="10" value="<?php echo $dtIni; ?>" />
                                <input type="date" id="dtFin" name="dtFin" style="width: 130px" maxlength="10" value="<?php echo $dtFin; ?>" />
                                &nbsp;
                                <label>Placa:&nbsp;</label>
                                <input id="placa" name="placa" maxlength="7" value="<?php echo $placa; ?>" />
                                &nbsp;
                                <button class="btn btn-primary">Buscar</button>
                            </div>
                        </form>
                        <br />

                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-steering-wheel"></span>
                                <h3 class="icon">Sem Parar x CT-es por placa - <?php echo $placa; ?></h3>
                            </div>
                        </div>

                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-arrow-up"></span>
                                <h3 class="icon">Passagens</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width="15%">Data</th>
                                            <th width="12%">Valor</th>
                                            <th width="34%">Concessionária</th>
                                            <th width="34%">Praça</th>
                                            <th width="5%">TAG</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            $vlrPsg = 0;

                                            foreach ($listaPassagens as $passagem){
                                                echo
                                                    "<tr>
                                                        <td>$passagem[data]</td>
                                                        <td align='right'>" . $hoUtils->numberFormat($passagem['valor']) . "</td>
                                                        <td>$passagem[concessionaria]</td>
                                                        <td>$passagem[praca]</td>
                                                        <td>$passagem[tag]</td>
                                                    </tr>";

                                                $vlrPsg += $passagem['valor'];
                                            }
                                        ?>
                                    </tbody>
                                </table>
                            </div> <!-- .widget-content -->
                        </div> <!-- .widget -->

                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-reload"></span>
                                <h3 class="icon">Créditos de reenvio / reembolso</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width="10%">Valor</th>
                                            <th>Data crédito</th>
                                            <th>Data importação</th>
                                            <th width="5%">TAG</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            $vlrCrd = 0;

                                            foreach ($listaCreditos as $credito){
                                                echo
                                                    "<tr>
                                                        <td align='right'>" . $hoUtils->numberFormat($credito['valor']) . "</td>
                                                        <td>$credito[dtCredito]</td>
                                                        <td>$credito[dtImportacao]</td>
                                                        <td>$credito[tag]</td>
                                                    </tr>";

                                                $vlrCrd += $credito['valor'];
                                            }
                                        ?>
                                    </tbody>
                                </table>
                            </div> <!-- .widget-content -->
                        </div> <!-- .widget -->

                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-document-alt-stroke"></span>
                                <h3 class="icon">CT-es</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width="10%">Número</th>
                                            <th width="10%">Data emissão</th>
                                            <th width="10%">BIPE</th>
                                            <th width="10%">Total Frete</th>
                                            <th width="10%">Vlr. Pedágio</th>
                                            <th>Cliente</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            $vlrCte = 0;

                                            foreach ($listaCte as $cte){
                                                $valorPedagio = str_replace(",", ".", $cte['PEDAGIO']);

                                                if ($cte['PEDAGIO'] > 0) echo
                                                    "<tr>
                                                        <td>$cte[NUMERO]</td>
                                                        <td>$cte[EMISSAO]</td>
                                                        <td>$cte[BIPE]</td>
                                                        <td align='right'>" . $hoUtils->numberFormat($cte['FRETE']) . "</td>
                                                        <td align='right'>" . $hoUtils->numberFormat($valorPedagio) . "</td>
                                                        <td>$cte[CLIENTE]</td>
                                                    </tr>";

                                                $vlrCte += $valorPedagio;
                                            }
                                        ?>
                                    </tbody>
                                </table>
                            </div> <!-- .widget-content -->
                        </div> <!-- .widget -->

                        <div class="box plain">
                            <div class="widget-header">
                                <span class="icon-layers"></span>
                                <h3 class="icon">Cumulativo</h3>
                            </div>
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Empresa</th>
                                        <th>Passagens (Qtd. / Valor)</th>
                                        <th>Créditos (Qtd. / Valor)</th>
                                        <th>CT-es (Qtd. / Valor)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="odd gradeX">
                                        <td><?php echo $_SESSION['nomeEmpresa']; ?></td>
                                        <td align='right'>
                                            <?php echo $hoUtils->numberFormat(count($listaPassagens), 0, 0) . " / R$ " . $hoUtils->numberFormat($vlrPsg); ?>
                                        </td>
                                        <td align='right'>
                                            <?php echo $hoUtils->numberFormat(count($listaCreditos), 0, 0) . " / R$ " . $hoUtils->numberFormat($vlrCrd); ?>
                                        </td>
                                        <td align='right'>
                                            <?php echo $hoUtils->numberFormat(count($listaCte), 0, 0) . " / R$ " . $hoUtils->numberFormat($vlrCte); ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div> <!-- .box -->
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
        </div> <!-- #footer -->
    </body>
</html>