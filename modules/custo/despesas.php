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
        <title>BID - Resumo geral despesas</title>

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

            if (isset($_POST['ano'])){
                $ano = $_POST['ano'];
            }
            if (isset($_POST['mes'])){
                $mes_atual = $_POST['mes'];
            }


            $sqlAno = mssql_query("SELECT * FROM ano ORDER BY ano DESC");
            while ($dadosAno = mssql_fetch_array($sqlAno)){
                $listaAno = $listaAno . "<option value='$dadosAno[0]'>$dadosAno[0]</option>";
            }

            $sqlMes = mssql_query("SELECT * FROM mes");
            while ($dadosMes = mssql_fetch_array($sqlMes)){
                $listaMes = $listaMes . "<option value='$dadosMes[0]'>$dadosMes[1]</option>";
            }

            //NOME DO MES SELECIONADO
            $sqlMesSelecionado = mssql_query("SELECT * FROM mes WHERE id_mes=$mes_atual");
            $dadosMesSelecionado = mssql_fetch_array($sqlMesSelecionado);

            /*             * ***************************** */
            /*  CONSULTA TOTAL DA EMPRESA     *
              /******************************** */
            $script_total = "SELECT  SUM(FPESO) FROM
(SELECT SUM(CT.VALTOTFRETE) AS FPESO
FROM CT
WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes_atual

UNION
SELECT SUM(VALFRETE) AS FPESO
FROM CARRETO
WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes_atual

UNION
SELECT SUM(VLR_TOTAL) AS FPESO
FROM NOTAFAT
WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes_atual
AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')


UNION
SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
FROM NOTASER
WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes_atual


UNION
SELECT SUM(NOTADEB.VALOR) AS FPESO
FROM NOTADEB
WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano AND MONTH(NOTADEB.DATAEMISSAO)=$mes_atual


)";
            $db2_total = db2_exec($hDbcDB2, $script_total);
            $dados_total = db2_fetch_array($db2_total);
            $realizado = number_format(str_replace(',', '.', $dados_total[0]), 2, ',', '.');
            /*             * ******************************************************************** */


// **************************************************
// *    VALOR TOTAL CONTAS CUSTO                    *
// **************************************************
            $listaContaTotal = "SELECT SUM(VLR_LANCTO)
                FROM CTCONTAB
                JOIN LANCTO ON (LANCTO.ID_CTCONTABIL = CTCONTAB.ID_CTCONTABIL)
                WHERE CTCONTAB.BLOQUEADO <> 'S' AND CTCONTAB.CLASSIF = 'D' AND CTCONTAB.TIPO_CONTA = 'A' AND YEAR(LANCTO.DAT_LANCTO)=$ano AND MONTH(LANCTO.DAT_LANCTO)=$mes_atual AND LANCTO.FLG_STATUS='D'
        ";
            $db2ContaTotal = db2_exec($hDbcDB2, $listaContaTotal);
            $dadosContaTotal = db2_fetch_array($db2ContaTotal);


// **************************************************
// *    LISTA DE CONTAS CUSTO                       *
// **************************************************
            $listaConta = "SELECT CTCONTAB.ID_CTCONTABIL, CTCONTAB.MASCARA, CTCONTAB.DESCRICAO, SUM(VLR_LANCTO)
                FROM CTCONTAB
                JOIN LANCTO ON (LANCTO.ID_CTCONTABIL = CTCONTAB.ID_CTCONTABIL)
                WHERE CTCONTAB.BLOQUEADO <> 'S' AND CTCONTAB.CLASSIF = 'D' AND CTCONTAB.TIPO_CONTA = 'A' AND YEAR(LANCTO.DAT_LANCTO)=$ano AND MONTH(LANCTO.DAT_LANCTO)=$mes_atual AND LANCTO.FLG_STATUS='D'
                GROUP BY CTCONTAB.ID_CTCONTABIL, CTCONTAB.MASCARA, CTCONTAB.DESCRICAO ";
            $db2Conta = db2_exec($hDbcDB2, $listaConta);
            while ($dadosConta = db2_fetch_array($db2Conta)){
                $descricao = htmlentities($dadosConta[2]);
                $linhaTabela = $linhaTabela . "<tr class='gradeA'>
                                    <td><a href='sublists/custo_conta.php?conta=$dadosConta[0]&ano=$ano&mes=$mes_atual'>$dadosConta[1]</a></td>
                                    <td>$descricao</td>
                                    <td align='right'>$dadosConta[3]</td>
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

            <?php echo $hoUtils->menuUsuario($_SESSION['idUsuario']); ?>

            <div id="content">

                <div id="contentHeader">
                    <h1><?php echo $_SESSION['nomeEmpresa']; ?></h1>
                </div> <!-- #contentHeader -->

                <div class="container">
                    <div class="grid-24">
                        <form action="#" method="post">
                            <div class="field">
                                <label>Selecione o MES:</label>
                                <select id="mes" name="mes">
                                    <?php echo $listaMes; ?>
                                </select>

                                <label>Selecione o ANO:</label>
                                <select id="ano" name="ano">
                                    <?php echo $listaAno; ?>
                                </select>

                                <input type="submit" value="IR">
                                Periodo Selecionado: <?php echo $dadosMesSelecionado[1] . '/ ' . $ano; ?>.
                            </div>
                        </form>
                        <br>
                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">RESUMO GERAL DESPESAS</h3>
                            </div>

                            <div class="widget-content">

                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>CONTA</th>
                                            <th>DESCRICAO</th>
                                            <th>VALOR</th>
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

                            <h3>TOTAL DESPESAS DA EMPRESA</h3>

                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>EMPRESA</th>
                                        <th>TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="odd gradeX">
                                        <td width="20%"><?php echo $_SESSION['nomeEmpresa']; ?></td>
                                        <td align='right'><?php echo number_format($dadosContaTotal[0], 2, ',', '.'); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div> <!-- .box -->

                        <div class="box plain">

                            <h3>TOTAL RECEITAS DA EMPRESA</h3>

                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>EMPRESA</th>
                                        <th>TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="odd gradeX">
                                        <td width="20%"><?php echo $_SESSION['nomeEmpresa']; ?></td>
                                        <td align='right'><?php echo $realizado; ?></td>
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