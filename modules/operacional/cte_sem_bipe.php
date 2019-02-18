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
        <title>BID - CT-es sem BIPE</title>

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

            $sqlGroupBipe = "SELECT SIGLA_FILIAL, COUNT(id) FROM
		(SELECT FILIAL.SIGLA_FILIAL, CT.ID_CT AS id
                FROM CT
                JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
                JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = CT.IDHCLIENTE)
                WHERE CT.IDCADBIPE IS NULL
                    AND CT.STATUSCT <> 'C' AND CT.TIPOCTRC <> 'A' AND CT.ID_CTANU IS NULL
                UNION
                SELECT FILIAL.SIGLA_FILIAL, CARRETO.ID_CARRETO AS id
                FROM CARRETO
                JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
                JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = CARRETO.ID_CLIENTE)
                WHERE CARRETO.IDCADBIPE IS NULL
                    AND CARRETO.STATUS <> 'C')
                GROUP BY SIGLA_FILIAL
                ORDER BY COUNT(*) DESC";
            $db2GroupBipe = db2_exec($hDbcDB2, $sqlGroupBipe);
            while ($dadosGroupBipe = db2_fetch_array($db2GroupBipe)){
                $linhaTabela = $linhaTabela . "<tr class='gradeA'>
                                    <td><a href='sublists/cte_sem_bipe_filial.php?filial=$dadosGroupBipe[0]'>$dadosGroupBipe[0]</a></td>
                                    <td align='right'>$dadosGroupBipe[1]</td>
                                </tr>";

                //TITULOS PARA GRAFICO
                $titulo = $titulo . "<th>$dadosGroupBipe[0]</th>";
                //VALORES PARA GRAFICO
                $graf_realizadoMes = $graf_realizadoMes . "<td>$dadosGroupBipe[1]</td>";
            }

            $sqlTotalBipe = "SELECT COUNT(id) FROM
		(SELECT CT.ID_CT AS id
                FROM CT
                JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
                JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = CT.IDHCLIENTE)
                WHERE CT.IDCADBIPE IS NULL
                    AND CT.STATUSCT <> 'C' AND CT.TIPOCTRC <> 'A' AND CT.ID_CTANU IS NULL
                UNION
                SELECT CARRETO.ID_CARRETO AS id
                FROM CARRETO
                JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
                JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = CARRETO.ID_CLIENTE)
                WHERE CARRETO.IDCADBIPE IS NULL
                    AND CARRETO.STATUS <> 'C')";
            $db2TotalBipe = db2_exec($hDbcDB2, $sqlTotalBipe);
            $dadosTotalBipe = db2_fetch_array($db2TotalBipe);
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

                        <a href='sublists/cte_sem_bipe.php'>Relatorio Geral</a>
                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Quantidade de Ctes sem Bipe</h3>
                            </div>

                            <div class="widget-content">

                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>FILIAL</th>
                                            <th>QUANTIDADE</th>
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
                                        <th width="7%">PORC %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="odd gradeX">
                                        <td><?php echo $_SESSION['nomeEmpresa']; ?></td>
                                        <td align='right'><?php echo $dadosTotalBipe[0]; ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div> <!-- .box -->


                        <div class="widget widget-tabs">

                            <div class="widget-header">
                                <span class="icon-chart"></span>
                                <h3 class="">Grafico Filial Cte Sem Bipe</h3>

                                <ul class="tabs right">
                                    <li class="active"><a href="#">Mensal</a></li>
                                </ul>
                            </div>

                            <div id="yearly" class="widget-content">
                                <table class="stats" data-chart-type="bar" data-chart-colors="">
                                    <caption><?php echo $ano; ?> Ctes Sem Bipe</caption>
                                    <thead>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <?php
                                                echo $titulo;
                                            ?>
                                        </tr>

                                    </thead>

                                    <tbody>

                                        <tr>
                                            <th>Ctes Sem Bipe</th>
                                            <?php
                                                echo $graf_realizadoMes;
                                            ?>
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