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

        <title>BID - Análise por fazenda</title>

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


            if (isset($_GET['mes'])){
                $mes_atual = $_GET['mes'];
            }
            if (isset($_POST['mes'])){
                $mes_atual = $_POST['mes'];
            }

            if (isset($_GET['ano'])){
                $ano = $_GET['ano'];
            }
            if (isset($_POST['ano'])){
                $ano = $_POST['ano'];
            }

            $sqlMesSelecionado = mssql_query("SELECT * FROM mes WHERE id_mes=$mes_atual");
            $dadosMesSelecionado = mssql_fetch_array($sqlMesSelecionado);
            /*             * ********************************* */

            $sqlAno = mssql_query("SELECT * FROM ano WHERE ano <> $ano ORDER BY ano DESC");
            $listaAno = $listaAno . "<option value='$ano'>$ano</option>";
            while ($dadosAno = mssql_fetch_array($sqlAno)){
                $listaAno = $listaAno . "<option value='$dadosAno[0]'>$dadosAno[0]</option>";
            }

            $sqlMes = mssql_query("SELECT * FROM mes where id_mes <> $mes_atual");
            $listaMes = $listaMes . "<option value='$dadosMesSelecionado[0]'>$dadosMesSelecionado[1]</option>";
            while ($dadosMes = mssql_fetch_array($sqlMes)){
                $listaMes = $listaMes . "<option value='$dadosMes[0]'>$dadosMes[1]</option>";
            }

            /*******************************************
            *    SELECAO DAS FAZENDAS
            * **************************************** */
            $sql_cliente = mssql_query("SELECT o.idFazenda, o.descricao
                                            FROM flr.carregamento c
                                            JOIN flr.item i ON (i.idItem = c.idItem)
                                            JOIN flr.fazenda o ON (o.idFazenda = c.idFazenda)
                                          WHERE MONTH(c.data)=$mes_atual AND YEAR(c.data)=$ano
                                          GROUP BY o.idFazenda, o.descricao");

            while ($dadosCliente = mssql_fetch_array($sql_cliente)){
                //CALCULO DO TOTAL DE CADA FAZENDA
                $sqlTotalFazenda = mssql_query("SELECT SUM(c.peso), COUNT(c.idCarregamento), SUM((c.peso/1000)*valor)
                                                FROM flr.carregamento c
                                                JOIN flr.fazenda o ON (o.idFazenda = c.idFazenda)
                                              WHERE c.idFazenda=$dadosCliente[0] AND MONTH(c.data)=$mes_atual AND YEAR(c.data)=$ano
                                              ");
                $linhaTotalFazenda = NULL;
                while($dadosTotalFazenda = mssql_fetch_array($sqlTotalFazenda)){
                    $totalFazendaPeso = $dadosTotalFazenda[0];
                    $linhaTotalFazenda = $linhaTotalFazenda."<tr class='gradeA'>
                                                        <td>TOTAL</td>
                                                        <td align='right'>".number_format($dadosTotalFazenda[1], 0, ',', '.')."</td>
                                                        <td align='right'>".number_format($dadosTotalFazenda[0], 0, ',', '.')."</td>
                                                        <td align='right'>".number_format($dadosTotalFazenda[2], 2, ',', '.')."</td>
                                                        <td align='right'>".number_format($dadosTotalFazenda[2]/($dadosTotalFazenda[0]/1000), 0, ',', '.')."</td>
                                                        <td align='right'></td>
                                                    </tr>";
                }

                $sqlItemFazenda = mssql_query("SELECT i.descricao, SUM(c.peso), COUNT(c.idCarregamento), SUM((c.peso/1000)*valor)
                                                FROM flr.carregamento c
                                                JOIN flr.item i ON (i.idItem = c.idItem)
                                                JOIN flr.fazenda o ON (o.idFazenda = c.idFazenda)
                                              WHERE c.idFazenda=$dadosCliente[0] AND MONTH(c.data)=$mes_atual AND YEAR(c.data)=$ano
                                              GROUP BY i.descricao ");
                $linhaTabela = NULL;
                while ($dadosItemFazenda = mssql_fetch_array($sqlItemFazenda)){
                    $linhaTabela = $linhaTabela."<tr class='gradeA'>
                                                        <td>$dadosItemFazenda[0]</td>
                                                        <td align='right'>".number_format($dadosItemFazenda[2], 0, ',', '.')."</td>
                                                        <td align='right'>".number_format($dadosItemFazenda[1], 0, ',', '.')."</td>
                                                        <td align='right'>".number_format($dadosItemFazenda[3], 0, ',', '.')."</td>
                                                        <td align='right'>".number_format($dadosItemFazenda[3]/($dadosItemFazenda[1]/1000), 0, ',', '.')."</td>
                                                        <td align='right'>".number_format(($dadosItemFazenda[1]/$totalFazendaPeso)*100, 0, ',', '.')."</td>
                                                    </tr>";

                }

                $tabela = $tabela . "<div class='widget widget-table'>

                                        <div class='widget-header'>
                                            <span class='icon-list'></span>
                                            <h3 class='icon chart'>$dadosCliente[1]</h3>
                                        </div>

                                        <div class='widget-content'>

                                            <table class='table table-bordered table-striped '>
                                                <thead>
                                                    <tr>
                                                        <th width='45%'>ITEM</th>
                                                        <th>VIAGENS</th>
                                                        <th>PESO Kg</th>
                                                        <th>VALOR R$</th>
                                                        <th>VALOR MEDIO R$</th>
                                                        <th>%</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    $linhaTabela
                                                    $linhaTotalFazenda
                                                </tbody>
                                            </table>

                                        </div> <!-- .widget-content -->

                                    </div> <!-- .widget -->";

            }

            $sqlTotal = mssql_query("SELECT sum(carregamento.peso), COUNT(*), SUM((carregamento.peso/1000)*carregamento.valor)
                                FROM flr.carregamento
                                WHERE YEAR(data)=$ano AND MONTH(data)=$mes_atual
                                ");
            $dadosTotal = mssql_fetch_array($sqlTotal);
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

                        <form action="#" method="post" name="form1">
                            <div class="field">
                                <input type="hidden" id="filial" name="filial" value="<?php echo $filial; ?>"/>
                                <label>Selecione o MES:</label>
                                <select id="mes" name="mes" onchange="document.form1.submit()">
                                    <?php echo $listaMes; ?>
                                </select>

                                <label>Selecione o ANO:</label>
                                <select id="ano" name="ano" onchange="document.form1.submit()">
                                    <?php echo $listaAno; ?>
                                </select>
                            </div>
                        </form>
                        <br>

                        <?php echo $tabela; ?>



                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Total Carregado</h3>
                            </div>

                            <div class="widget-content">

                                <table class="table table-bordered table-striped ">
                                    <thead>
                                        <tr>
                                            <th width="45%">EMPRESA</th>
                                            <th>VIAGENS</th>
                                            <th>PESO Kg</th>
                                            <th>VALOR R$</th>
                                            <th>VALOR MEDIO R$</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="odd gradeX">
                                            <td><?php echo $_SESSION['nomeEmpresa']; ?></td>
                                            <td align='right'><?php echo $dadosTotal[1]; ?></td>
                                            <td align='right'><?php echo number_format($dadosTotal[0], 0, ',', '.'); ?></td>
                                            <td align='right'><?php echo number_format($dadosTotal[2], 2, ',', '.'); ?></td>
                                            <td align='right'><?php echo number_format($dadosTotal[2]/($dadosTotal[0]/1000), 0, ',', '.'); ?></td>
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