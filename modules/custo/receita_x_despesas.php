<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL = new \Library\Scripts\scriptSQL();

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));
?>
<!doctype html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Receita x despesas</title>

        <meta charset="utf-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
    </head>

    <body>
        <?php
            include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
            include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_db2_bino.php';
            include $_SERVER['DOCUMENT_ROOT'] . '/old/funcoes.php';

            /*             * *******************************
             *   VARIAVEIS                   *
             * ****************************** */
            date_default_timezone_set('America/sao_paulo');
            $wanted_week = date('W');
            $abrev_mes = date('M');
            $nome_mes = date('F');
            $dia = date('d');
            $ano = date('Y');
            $mes_atual = date('m');

            /*             * ************************************************
              //BUSCA RECEITA FROTA              *
              /************************************************* */
            $sql_receitaFrota = "SELECT SUM(FPESO) FROM
    (SELECT SUM(VALTOTFRETE) AS FPESO
    FROM CT
        JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
        JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
    WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes_atual AND HVEICEMP.STAFT='F'

    UNION
    SELECT SUM(VALFRETE) AS FPESO
    FROM CARRETO
        JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
        JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
    WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes_atual AND HVEICEMP.STAFT='F'

    UNION
    SELECT SUM(VLR_TOTAL) AS FPESO
    FROM NOTAFAT
        JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTAFAT.ID_HVEICULO)
        JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTAFAT.ID_HVEICULO)
    WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes_atual AND HVEICEMP.STAFT='F'
    AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

    UNION
    SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
    FROM NOTASER
        JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
        JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
    WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes_atual AND HVEICEMP.STAFT='F'

    )";
            $db2_receitaFrota = db2_exec($hDbcDB2, $sql_receitaFrota);
            $dados_receitaFrota = db2_fetch_array($db2_receitaFrota);
            $receitaFrota = number_format(($dados_receitaFrota[0]), 0, ',', '.');
            //FIM CALCULO FROTA
// **************************************************
// *    DESPESAS FROTA                              *
// **************************************************
            $listaConta = "SELECT SUM(VLR_LANCTO)
                FROM CTCONTAB
                JOIN LANCTO ON (LANCTO.ID_CTCONTABIL = CTCONTAB.ID_CTCONTABIL)
                WHERE CTCONTAB.BLOQUEADO <> 'S' AND CTCONTAB.CLASSIF = 'D' AND CTCONTAB.TIPO_CONTA = 'A' AND YEAR(LANCTO.DAT_LANCTO)=$ano AND MONTH(LANCTO.DAT_LANCTO)=$mes_atual AND LANCTO.CCUSTO LIKE '1009%' ";
            $db2Conta = db2_exec($hDbcDB2, $listaConta);
            $dadosConta = db2_fetch_array($db2Conta);
            $despesaFrota = number_format($dadosConta[0], 0, ',', '.');
            $porc_despesa = number_format(($dadosConta[0] / $dados_receitaFrota[0]) * 100, 0, ',', '.');
            $linhaTabela = $linhaTabela . "<tr class='gradeA'>
                                    <td>FROTA (cc:1009)</td>
                                    <td align='right'>$receitaFrota</td>
                                    <td align='right'>$despesaFrota</td>
                                    <td>$porc_despesa</td>
                                </tr>";


            /*             * ************************************************
              //BUSCA RECEITA AGREGADO              *
              /************************************************* */
            $sql_receitaAgregado = "SELECT SUM(FPESO) FROM
    (SELECT SUM(VALTOTFRETE) AS FPESO
    FROM CT
        JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
        JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
    WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes_atual AND HVEICEMP.STAFT='A'

    UNION
    SELECT SUM(VALFRETE) AS FPESO
    FROM CARRETO
        JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
        JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
    WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes_atual AND HVEICEMP.STAFT='A'

    UNION
    SELECT SUM(VLR_TOTAL) AS FPESO
    FROM NOTAFAT
        JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTAFAT.ID_HVEICULO)
        JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTAFAT.ID_HVEICULO)
    WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes_atual AND HVEICEMP.STAFT='A'
    AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

    UNION
    SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
    FROM NOTASER
        JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
        JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
    WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes_atual AND HVEICEMP.STAFT='A'

    )";
            $db2_receitaAgregado = db2_exec($hDbcDB2, $sql_receitaAgregado);
            $dados_receitaAgregado = db2_fetch_array($db2_receitaAgregado);
            $receitaAgregado = number_format(($dados_receitaAgregado[0]), 0, ',', '.');
            //FIM CALCULO AGREGADO
// **************************************************
// *    DESPESAS AGREGADO                             *
// **************************************************
            $listaAgregado = "SELECT SUM(VLR_LANCTO)
                FROM CTCONTAB
                JOIN LANCTO ON (LANCTO.ID_CTCONTABIL = CTCONTAB.ID_CTCONTABIL)
                WHERE CTCONTAB.BLOQUEADO <> 'S' AND CTCONTAB.CLASSIF = 'D' AND CTCONTAB.TIPO_CONTA = 'A' AND YEAR(LANCTO.DAT_LANCTO)=$ano AND MONTH(LANCTO.DAT_LANCTO)=8 AND LANCTO.CCUSTO LIKE '1020%' ";
            $db2Agregado = db2_exec($hDbcDB2, $listaAgregado);
            $dadosAgregado = db2_fetch_array($db2Agregado);
            $despesaAgregado = number_format($dadosAgregado[0], 0, ',', '.');
            $porc_despesa = number_format(($dadosAgregado[0] / $dados_receitaAgregado[0]) * 100, 0, ',', '.');
            $linhaTabela = $linhaTabela . "<tr class='gradeA'>
                                    <td>AGREGADO (cc:1020)</td>
                                    <td align='right'>$receitaAgregado</td>
                                    <td align='right'>$despesaAgregado</td>
                                    <td>$porc_despesa</td>
                                </tr>";
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
                        <br>
                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">RESUMO CONTRATO RECEITA x DESPESA</h3>
                            </div>

                            <div class="widget-content">

                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>CONTRATO</th>
                                            <th>RECEITA</th>
                                            <th>DESPESA</th>
                                            <th>%</th>
                                        </tr>
                                    </thead>
                                    <tbody>

<?php
    echo $linhaTabela;
?>

                                    </tbody>
                                </table>
                            </div> <!-- .widget-content -->

                        </div> <!-- .widget -->

                        <div class="box plain">

                            <h3>TOTAL DA EMRPESA</h3>

                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>EMPRESA</th>
                                        <th><?php echo $primeira_data; ?></th>
                                        <th><?php echo $segunda_data; ?></th>
                                        <th><?php echo $terceira_data; ?></th>
                                        <th><?php echo $quarta_data; ?></th>
                                        <th><?php echo $quinta_data; ?></th>
                                        <th><?php echo $sexta_data; ?></th>
                                        <th><?php echo $ultima_data; ?></th>
                                        <th>TOTAL SEMANA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="odd gradeX">
                                        <td width="20%"><?php echo $_SESSION['nomeEmpresa']; ?></td>
                                        <td align='right'><?php echo $dados_dia1Total[0]; ?></td>
                                        <td align='right'><?php echo $dados_dia2Total[0]; ?></td>
                                        <td align='right'><?php echo $dados_dia3Total[0]; ?></td>
                                        <td align='right'><?php echo $dados_dia4Total[0]; ?></td>
                                        <td align='right'><?php echo $dados_dia5Total[0]; ?></td>
                                        <td align='right'><?php echo $dados_dia6Total[0]; ?></td>
                                        <td align='right'><?php echo $dados_dia7Total[0]; ?></td>
                                        <td align='right'><?php echo $dadosTotal[0]; ?></td>
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

            <div id="quickNav">
                <ul>
                    <li class="quickNavMail">
                        <a href="#menuAmpersand" class="menu"><span class="icon-book"></span></a>

                        <span class="alert">3</span>

                        <div id="menuAmpersand" class="menu-container quickNavConfirm">
                            <div class="menu-content cf">

                                <div class="qnc qnc_confirm">

                                    <h3>Confirm</h3>

                                    <div class="qnc_item">
                                        <div class="qnc_content">
                                            <span class="qnc_title">Confirm #1</span>
                                            <span class="qnc_preview">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do.</span>
                                            <span class="qnc_time">3 hours ago</span>
                                        </div> <!-- .qnc_content -->

                                        <div class="qnc_actions">
                                            <button class="btn btn-primary btn-small">Accept</button>
                                            <button class="btn btn-quaternary btn-small">Not Now</button>
                                        </div>
                                    </div>

                                    <div class="qnc_item">
                                        <div class="qnc_content">
                                            <span class="qnc_title">Confirm #2</span>
                                            <span class="qnc_preview">Duis aute irure dolor in henderit in voluptate velit esse cillum dolore.</span>
                                            <span class="qnc_time">3 hours ago</span>
                                        </div> <!-- .qnc_content -->

                                        <div class="qnc_actions">
                                            <button class="btn btn-primary btn-small">Accept</button>
                                            <button class="btn btn-quaternary btn-small">Not Now</button>
                                        </div>
                                    </div>

                                    <div class="qnc_item">
                                        <div class="qnc_content">
                                            <span class="qnc_title">Confirm #3</span>
                                            <span class="qnc_preview">Duis aute irure dolor in henderit in voluptate velit esse cillum dolore.</span>
                                            <span class="qnc_time">3 hours ago</span>
                                        </div> <!-- .qnc_content -->

                                        <div class="qnc_actions">
                                            <button class="btn btn-primary btn-small">Accept</button>
                                            <button class="btn btn-quaternary btn-small">Not Now</button>
                                        </div>
                                    </div>

                                    <a href="javascript:;" class="qnc_more">View all Confirmations</a>

                                </div> <!-- .qnc -->
                            </div>
                        </div>
                    </li>
                    <li class="quickNavNotification">
                        <a href="#menuPie" class="menu"><span class="icon-chat"></span></a>

                        <div id="menuPie" class="menu-container">
                            <div class="menu-content cf">

                                <div class="qnc">

                                    <h3>Notifications</h3>

                                    <a href="javascript:;" class="qnc_item">
                                        <div class="qnc_content">
                                            <span class="qnc_title">Notification #1</span>
                                            <span class="qnc_preview">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do.</span>
                                            <span class="qnc_time">3 hours ago</span>
                                        </div> <!-- .qnc_content -->
                                    </a>

                                    <a href="javascript:;" class="qnc_item">
                                        <div class="qnc_content">
                                            <span class="qnc_title">Notification #2</span>
                                            <span class="qnc_preview">Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu.</span>
                                            <span class="qnc_time">3 hours ago</span>
                                        </div> <!-- .qnc_content -->
                                    </a>

                                    <a href="javascript:;" class="qnc_more">View all Confirmations</a>

                                </div> <!-- .qnc -->
                            </div>
                        </div>
                    </li>
                </ul>
            </div> <!-- .quickNav -->


        </div> <!-- #wrapper -->

        <div id="footer">
            <div style="float: left;">Versão <?php echo $_SESSION['version']; ?></div> Copyright &copy; <?php echo date('Y'); ?>, Case Electronic Ltda.
        </div>
    </body>
</html>