<?php
    namespace Modulos\SemParar;

    require $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();
    $dbcDB2  = new \Library\Scripts\scriptDB2();

    $_SESSION['modulo'] = basename(dirname(dirname($_SERVER['PHP_SELF'])));
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Saldos a pagar</title>

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

            $post = filter_input_array(INPUT_POST);
            $get  = filter_input_array(INPUT_GET);

            $dtIni = $post['dtIni'] ?: $get['dtIni'] ?: date('Y-m-01');
            $dtFin = $post['dtFin'] ?: $get['dtFin'] ?: date('Y-m-d');

            $cgc = $get['cgc'];

            $params = array(
                $dbcSQL->whereParam("C.DATAEMIS", $dtIni, ">="), $dbcSQL->whereParam("C.DATAEMIS", $dtFin, "<="),
                $dbcSQL->whereParam("G.OPERACAO", "Saldo"), $dbcSQL->whereParam("G.SITUACAO", "EFE", "<>"),
                $dbcSQL->whereParam("G.VALOR", "0.01", ">"), $dbcSQL->whereParam("P.CNPJ_CPF", $cgc)
            );

            /** Subquery que verifica quantos comprovantes das notas da viagem já estão baixados - Usada para filtro opcional e coluna da consulta */
            $subQueryCompNF =
                "SELECT COUNT(1) FROM NFTRANSP NF
                WHERE NF.ID_CT IN (
                    SELECT CT.ID_CT FROM CT WHERE CT.IDCADBIPE = C.IDCADBIPE
                )
                AND NF.DATABX IS NULL";

            $sql = "SELECT
                        P.CNPJ_CPF cgc, P.RAZAO_SOCIAL nome, VE.ID_FILIAL filial, G.VALOR saldo,
                        (F.SIGLA_FILIAL || ' - ' || C.NUMBIPE) bipe, C.DATAEMIS dtViagem,
                        (CASE WHEN ($subQueryCompNF) > 0 THEN 'Pendente' ELSE 'Ok' END) comprovantes
                    FROM PROGVIAG  G
                    JOIN CADBIPE   C  ON G.IDCADBIPE = C.IDCADBIPE
                    JOIN FILIAL    F  ON C.ID_FILIAL = F.ID_FILIAL
                    JOIN HVEICULO  V  ON C.ID_HVEICULO = V.ID_HVEICULO
                    JOIN VEICEMP   VE ON (V.ID_VEICULO = VE.ID_VEICULO AND VE.ID_EMPRESA = 1)
                    JOIN HPROPRIET P  ON V.IDHPROPRIET = P.IDHPROPRIET
                    JOIN HPROPEMP  PE ON (P.IDHPROPRIET = PE.IDHPROPRIET AND PE.ID_EMPRESA = 1)
                    WHERE 1 = 1";

            /** Appenda o filtro com subquery para trazer apenas os CT-es com comprovante baixado */
            if ($post['baixados']) $sql .= " AND ($subQueryCompNF) = 0 ";

            $viagens = $dbcDB2->select($sql, $params, "C.DATAEMIS, C.NUMBIPE", null, false);

            $totViagens = 0; $totGeral = 0; $totOk = 0; $totPendente = 0;

            foreach ($viagens as $registro){
                $contratado = $registro['CGC'] . ' - ' . $registro['NOME'];

                $filial = $dbcDB2->dadosFilial($registro['FILIAL']);

                $comprovantes = $registro['COMPROVANTES'];

                $bgcolorComp = ($comprovantes == 'Ok') ? "#82CD9B" : "#FF4D4D";

                /** Formatação necessária para retirar a vírgula do DB2 e fazer a soma correta dos totais */
                $saldo = $hoUtils->numberFormat($registro['SALDO'], 0, 2, '.', '');

                $linhaTabela .=
                    "<tr class='gradeA'>
                        <td>$contratado</td>
                        <td>$filial[SIGLA]</td>
                        <td>$registro[BIPE]</td>
                        <td>" . $hoUtils->dateFormat($registro['DTVIAGEM'], 'Y-m-d', 'd/m/Y') . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($saldo) . "</td>
                        <td style='background-color: $bgcolorComp;'>$comprovantes</td>
                    </tr>";

                $totViagens++;
                $totGeral += $saldo;

                if ($comprovantes == 'Ok')
                    $totOk += $saldo;
                else
                    $totPendente += $saldo;
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
                        <form action="#" method="post" id="frmSaldoAgr" name="frmSaldoAgr" class="form uniformForm">
                            <div class="field">
                                <label>Selecione o período de emissão das viagens:&nbsp;</label>
                                <input type="date" id="dtIni" name="dtIni" style="width: 130px" maxlength="10" value="<?php echo $dtIni; ?>" />
                                <input type="date" id="dtFin" name="dtFin" style="width: 130px" maxlength="10" value="<?php echo $dtFin; ?>" />
                                &nbsp;
                                <input type="checkbox" name="baixados" id="baixados" <?php echo $post['baixados'] ? "checked" : ""; ?> /> Exibir somente os baixados
                                &nbsp;
                                <button class="btn btn-primary"><span class="icon-magnifying-glass"></span>Buscar</button>
                            </div>
                        </form>

                        <div class="grid-24 box notify-info" style="margin: 0px 0px 15px 0px; padding: 10px; width: 98%;">
                            <b>&#9679; Período de busca:</b> Corresponde à data em que a viagem foi emitida no sistema e não às datas de vencimento dos saldos. <br />
                            <b>&#9679;</b> Ordenado pela data da viagem e número do BIPE. <br />
                            <b>&#9679;</b> A coluna <b>Filial</b> refere-se à filial alocada do veículo, que determina a data de seus vencimentos. 
                            <b>HR = Ortigueira (5 e 20), MC = Kimberly (10 e 20) e LE = Geral (5 e 25).</b><br />
                            <b>&#9679;</b> A coluna <b>Comprovantes</b> indica se as notas do conhecimento estão baixadas. Para ver apenas os saldos com comprovante baixado utilize o filtro acima.
                        </div>

                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-minus"></span>
                                <h3 class="icon chart">Resumo de saldos pendentes a pagar para agregados</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Contratado</th>
                                            <th width="5%">Filial</th>
                                            <th>BIPE</th>
                                            <th width="15%">Dt. viagem</th>
                                            <th width="10%">Saldo (R$)</th>
                                            <th width="10%">Comprovantes</th>
                                        </tr>
                                    </thead>
                                    <tbody><?php echo $linhaTabela; ?></tbody>
                                </table>
                            </div> <!-- .widget-content -->
                        </div> <!-- .widget -->

                        <div class="box plain">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th width="25%">Qtd. de viagens</th>
                                        <th width="25%">Total geral (R$)</th>
                                        <th width="25%">Baixado (R$)</th>
                                        <th width="25%">Pendente (R$)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="odd gradeX">
                                        <td align='right'><?php echo $totViagens; ?></td>
                                        <td align='right'><?php echo $hoUtils->numberFormat($totGeral); ?></td>
                                        <td align='right'><?php echo $hoUtils->numberFormat($totOk); ?></td>
                                        <td align='right'><?php echo $hoUtils->numberFormat($totPendente); ?></td>
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