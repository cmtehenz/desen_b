<?php
    namespace Modulos\Utilitarios;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Bradesco x Pamcard</title>

        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/modernizr.js"); ?>"></script>
        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
        <script>
            Modernizr.load({
                test: Modernizr.inputtypes.date,
                nope: ['http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.7/jquery-ui.min.js', 'jquery-ui.css'],
                complete: function () {
                    $('input[type=date]').datepicker({ dateFormat: 'yy-mm-dd' });
                }
            });
        </script>
    </head>
    <body>
        <?php
            date_default_timezone_set('America/sao_paulo');

            $dtIni = $_POST['dtIni'] ?: date('Y-m-01');
            $dtFin = $_POST['dtFin'] ?: date('Y-m-d');

            $params = array( $dbcSQL->whereParam("e.data", $dtIni, ">="), $dbcSQL->whereParam("e.data", $dtFin, "<=") );

            /** Contratos de frete */
            $sql =
                "SELECT
                    (c.filial + ' - ' + RTRIM(c.bipe)) bipe, c.cte, c.placa, c.idViagem, c.tipoParcela,
                    e.numBradesco, dbo.DateFormat103(e.data) data, e.debito valor, c.cpf, c.nome
                FROM pcd.contrato c
                JOIN pcd.extratobrd e ON c.numBradesco = e.numBradesco";

            $contratos = $dbcSQL->select($sql, $params, "c.filial, c.bipe, c.tipoParcela");

            foreach ($contratos as $contrato){
                $favorecido = $hoUtils->cnpjCpfFormat($contrato['cpf']) . " - " . $contrato['nome'];

                $linhaContratos .=
                    "<tr>
                        <td>$contrato[bipe]</td>
                        <td>$contrato[cte]</td>
                        <td>$contrato[placa]</td>
                        <td class='text-right'>$contrato[idViagem]</td>
                        <td class='text-right'>$contrato[numBradesco]</td>
                        <td>$contrato[data]</td>
                        <td class='text-right'>" . $hoUtils->numberFormat($contrato['valor']) . "</td>
                        <td>" . $hoUtils->tipoParcelaPamcard($contrato['tipoParcela']) . "</td>
                        <td>$favorecido</td>
                    </tr>";

                $totCont++;
                $valCont += $contrato['valor'];
            }

            /** Adiantamentos de frota */
            $sql =
                "SELECT
                    a.idViagem, a.cpf, a.nome, e.numBradesco, dbo.DateFormat103(e.data) data, e.debito valor
                FROM pcd.adiantamento a
                JOIN pcd.extratobrd e ON a.numBradesco = e.numBradesco";

            $adiantamentos = $dbcSQL->select($sql, $params, "e.data, a.nome");

            foreach ($adiantamentos as $adiantamento){
                $favorecido = $hoUtils->cnpjCpfFormat($adiantamento['cpf']) . " - " . $adiantamento['nome'];

                $linhaAdiantamentos .=
                    "<tr>
                        <td>$favorecido</td>
                        <td>$adiantamento[data]</td>
                        <td class='text-right'>" . $hoUtils->numberFormat($adiantamento['valor']) . "</td>
                        <td class='text-right'>$adiantamento[numBradesco]</td>
                        <td class='text-right'>$adiantamento[idViagem]</td>
                    </tr>";

                $totAdto++;
                $valAdto += $adiantamento['valor'];
            }

            /** Pagamentos manuais */
            $sql =
                "SELECT
                    (p.filial + ' - ' + RTRIM(p.cte)) cte, p.placa, p.idViagem,
                    e.numBradesco, dbo.DateFormat103(e.data) data, e.debito valor, p.cpf, p.nome
                FROM pcd.pagamento p
                JOIN pcd.extratobrd e ON p.numBradesco = e.numBradesco";

            $pagamentos = $dbcSQL->select($sql, $params, "p.filial, p.cte, e.data, p.nome");

            foreach ($pagamentos as $pagamento){
                $favorecido = $hoUtils->cnpjCpfFormat($pagamento['cpf']) . " - " . $pagamento['nome'];

                $linhaPagamentos .=
                    "<tr>
                        <td>$pagamento[cte]</td>
                        <td>$pagamento[placa]</td>
                        <td class='text-right'>$pagamento[idViagem]</td>
                        <td class='text-right'>$pagamento[numBradesco]</td>
                        <td>$pagamento[data]</td>
                        <td class='text-right'>" . $hoUtils->numberFormat($pagamento['valor']) . "</td>
                        <td>$favorecido</td>
                    </tr>";

                $totPgto++;
                $valPgto += $pagamento['valor'];
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
                                <span class="icon-download"></span>
                                <h3>Lista de transações financeiras importadas dos arquivos do Bradesco e Pamcard</h3>
                            </div>
                        </div>

                        <form method="post" action="#" enctype="multipart/form-data" id="frmBrdXPam">
                            <div class="field">
                                <label>Selecione o período:&nbsp;</label>
                                <input type="date" id="dtIni" name="dtIni" style="width: 130px" maxlength="10" value="<?php echo $dtIni; ?>" />
                                <input type="date" id="dtFin" name="dtFin" style="width: 130px" maxlength="10" value="<?php echo $dtFin; ?>" />
                                &nbsp;
                                <button class="btn btn-primary"><span class="icon-magnifying-glass"></span>Buscar</button>
                            </div>
                        </form>
                        <br />

                        <!-- Contratos de frete -->
                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-document-alt-stroke"></span>
                                <h3>Contratos de frete integrados pelo sistema</h3>
                            </div>
                            <div style="padding: 10px">&#9679; Registros referentes aos BIPEs emitidos pela expedição e integrados com a Pamcard via webservice</div>
                        </div>

                        <table class="table table-bordered table-striped" style="<?php if (!$contratos) echo "display: none;" ?>">
                            <thead>
                                <tr>
                                    <th width="8%">BIPE</th>
                                    <th width="5%">CT-e</th>
                                    <th width="8%">Placa</th>
                                    <th width="8%">ID viagem</th>
                                    <th width="8%">Aut. Brad.</th>
                                    <th width="8%">Data</th>
                                    <th width="8%">Valor</th>
                                    <th width="10%">Parcela</th>
                                    <th width="30%">Favorecido</th>
                                </tr>
                            </thead>
                            <tbody><?php echo $linhaContratos; ?></tbody>
                        </table>

                        <table class="table table-bordered table-striped" style="<?php if (!$contratos) echo "display: none;" ?>">
                            <thead>
                                <tr>
                                    <th width="50%">Total de transações</th>
                                    <th width="50%">Valor total (R$)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-right"><?php echo $totCont; ?></td>
                                    <td class="text-right"><?php echo $hoUtils->numberFormat($valCont); ?></td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="notify notify-error" style="<?php if ($contratos) echo "display: none;" ?>">
                            &#9679; Não há contratos para o período selecionado
                        </div>

                        <hr style="background: #000; height: 3px; margin-bottom: 10px; <?php if (!$contratos) echo "display: none;" ?>">

                        <!-- Adiantamentos de frota -->
                        <div class="widget widget-table" style="margin-top: 15px;">
                            <div class="widget-header">
                                <span class="icon-undo"></span>
                                <h3>Adiantamentos de frota</h3>
                            </div>
                            <div style="padding: 10px">&#9679; Registros referentes aos adiantamentos feitos à frota</div>
                        </div>

                        <table class="table table-bordered table-striped" style="<?php if (!$adiantamentos) echo "display: none;" ?>">
                            <thead>
                                <tr>
                                    <th width="50%">Favorecido</th>
                                    <th width="10%">Data</th>
                                    <th width="10%">Valor</th>
                                    <th width="10%">Aut. Brad.</th>
                                    <th width="10%">ID viagem</th>
                                </tr>
                            </thead>
                            <tbody><?php echo $linhaAdiantamentos; ?></tbody>
                        </table>

                        <table class="table table-bordered table-striped" style="<?php if (!$adiantamentos) echo "display: none;" ?>">
                            <thead>
                                <tr>
                                    <th width="50%">Total de transações</th>
                                    <th width="50%">Valor total (R$)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-right"><?php echo $totAdto; ?></td>
                                    <td class="text-right"><?php echo $hoUtils->numberFormat($valAdto); ?></td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="notify notify-error" style="<?php if ($adiantamentos) echo "display: none;" ?>">
                            &#9679; Não há adiantamentos para o período selecionado
                        </div>

                        <hr style="background: #000; height: 3px; margin-bottom: 10px; <?php if (!$adiantamentos) echo "display: none;" ?>">

                        <!-- Pagamentos manuais -->
                        <div class="widget widget-table" style="margin-top: 15px;">
                            <div class="widget-header">
                                <span class="icon-cloud"></span>
                                <h3>Pagamentos manuais</h3>
                            </div>
                            <div style="padding: 10px">&#9679; Registros referentes aos pagamentos feitos manualmente pelo financeiro no Portal Web da Pamcard</div>
                        </div>

                        <table class="table table-bordered table-striped" style="<?php if (!$pagamentos) echo "display: none;" ?>">
                            <thead>
                                <tr>
                                    <th width="10%">CT-e</th>
                                    <th width="10%">Placa</th>
                                    <th width="10%">ID viagem</th>
                                    <th width="10%">Aut. Brad.</th>
                                    <th width="10%">Data</th>
                                    <th width="10%">Valor</th>
                                    <th width="30%">Favorecido</th>
                                </tr>
                            </thead>
                            <tbody><?php echo $linhaPagamentos; ?></tbody>
                        </table>

                        <table class="table table-bordered table-striped" style="<?php if (!$pagamentos) echo "display: none;" ?>">
                            <thead>
                                <tr>
                                    <th width="50%">Total de transações</th>
                                    <th width="50%">Valor total (R$)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-right"><?php echo $totPgto; ?></td>
                                    <td class="text-right"><?php echo $hoUtils->numberFormat($valPgto); ?></td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="notify notify-error" style="<?php if ($pagamentos) echo "display: none;" ?>">
                            &#9679; Não há pagamentos para o período selecionado
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
                                    <li><a href="javascript:;">Edit Profile</a></li>
                                    <li><a href="javascript:;">Suspend Account</a></li>
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