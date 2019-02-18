<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL = new \Library\Scripts\scriptSQL();

    $_SESSION['modulo'] = basename(str_replace("sublists", "", dirname($_SERVER['PHP_SELF'])));
?>
<!doctype html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<html class="no-js" lang="pt-br">
    <head>

        <title>BID - Carregamentos por cliente</title>

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

            if (isset($_GET['destino'])){
                $destino = $_GET['destino'];
            }
            if (isset($_POST['destino'])){
                $destino = $_POST['destino'];
            }

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

//NOME DO DESTINO
            $sql_destino = mssql_query("SELECT * FROM cliente WHERE id=$destino");
            $dadosDestino = mssql_fetch_array($sql_destino);


//NOME DO MES SELECIONADO
            $sqlMesSelecionado = mssql_query("SELECT * FROM mes WHERE id_mes=$mes_atual");
            $dadosMesSelecionado = mssql_fetch_array($sqlMesSelecionado);
            /*             * ********************************* */

            $sqlAno = mssql_query("SELECT * FROM ano ORDER BY ano DESC");
            while ($dadosAno = mssql_fetch_array($sqlAno)){
                $listaAno = $listaAno . "<option value='$dadosAno[0]'>$dadosAno[0]</option>";
            }

            $sqlMes = mssql_query("SELECT * FROM mes");
            while ($dadosMes = mssql_fetch_array($sqlMes)){
                $listaMes = $listaMes . "<option value='$dadosMes[0]'>$dadosMes[1]</option>";
            }

            //DIAS PARA O GRAFICO
            $dias_mes = cal_days_in_month(CAL_GREGORIAN, $mes_atual, $ano);
            for ($i = 1; $i <= $dias_mes; $i++){
                $tituloGraf = $tituloGraf . '<th>' . $i . '</th>';


                $script_carregamentoDiario = mssql_query("SELECT sum(peso), count(*) FROM carregamento where destino=$destino AND YEAR(data)=$ano and MONTH(data)=$mes_atual AND day(data)=$i");
                $dados_carregamentoDiario = mssql_fetch_array($script_carregamentoDiario);

                $cargas = $dados_carregamentoDiario[1];
                $pesoCarregado = $dados_carregamentoDiario[0];


                if ($pesoCarregado == NULL){
                    $realizadoDiario = 0;
                }
                else{
                    $realizadoDiario = $pesoCarregado;
                }
                if ($cargas == NULL){
                    $realizadoDiario2 = 0;
                }
                else{
                    $realizadoDiario2 = $cargas;
                }
                /*                 * **************************** */
                //REALIZADO PARA O GRAFICO
                $graf_realizado = $graf_realizado . '<td>' . $realizadoDiario . '</td>';

                $graf_realizado2 = $graf_realizado2 . '<td>' . $realizadoDiario2 . '</td>';
                /*                 * **************************** */

                $relizadoLinha = number_format($pesoCarregado, 0, ',', '.');
                $linhaTabela = $linhaTabela . "<tr class='gradeA'>
                                    <td>$i</td>
                                    <td align='right'>$cargas</td>
                                    <td align='right'>$relizadoLinha</td>
                                </tr>";
            }

            $script_carregamentoMensal = mssql_query("SELECT sum(peso), count(*) FROM carregamento where destino=$destino AND YEAR(data)=$ano AND MONTH(data)=$mes_atual");
            $dados_carregamentoMensal = mssql_fetch_array($script_carregamentoMensal);
            $cargasTotal = $dados_carregamentoMensal[1];
            $pesoCarregadoTotal = $dados_carregamentoMensal[0];
            $relizadoLinhaTotal = number_format($pesoCarregadoTotal, 0, ',', '.');
            $totalTabela = $totalTabela . "<tr class='gradeA'>
                                    <td>$dadosMesSelecionado[1]</td>
                                    <td align='right'>$cargasTotal</td>
                                    <td align='right'>$relizadoLinhaTotal</td>
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

            <div id="empLogo">
                <form>

                </form>
            </div>

            <?php echo $hoUtils->menuUsuario($_SESSION['idUsuario']); ?>

            <div id="content">

                <div id="contentHeader">
                    <h1><?php echo $_SESSION['nomeEmpresa']; ?></h1>
                </div> <!-- #contentHeader -->

                <div class="container">
                    <div class="grid-24">

                        <form action="#" method="post">
                            <div class="field">
                                <input type="hidden" id="destino" name="destino" value="<?php echo $destino; ?>"/>
                                <label>Selecione o MES:</label>
                                <select id="mes" name="mes">
<?php echo $listaMes; ?>
                                </select>

                                <label>Selecione o ANO:</label>
                                <select id="ano" name="ano">
<?php echo $listaAno; ?>
                                </select>

                                <input type="submit" value="IR">
                                Periodo Selecionado: <?php echo $dadosMesSelecionado[1] . '/ ' . $ano . ' <br><br><h2> Destino: ' . $dadosDestino[1] . '</h2>'; ?>


                            </div>
                        </form>
                        <br>
                        <div class="widget">

                            <div class="widget-header">
                                <span class="icon-chart"></span>
                                <h3 class="icon chart">Peso Carregado por Dia Kg</h3>
                            </div>

                            <div class="widget-content">
                                <table class="stats" data-chart-type="line" data-chart-colors="">
                                    <caption><?php echo $mes_atual . '/' . $ano; ?> Peso Diario Kg</caption>
                                    <thead>
                                        <tr>
                                            <td>&nbsp;</td>
<?php echo $tituloGraf; ?>
                                        </tr>

                                    </thead>

                                    <tbody>

                                        <tr>
                                            <th>Realizado</th>
<?php echo $graf_realizado; ?>
                                        </tr>

                                    </tbody>
                                </table>
                            </div> <!-- .widget-content -->

                        </div> <!-- .widget -->

                        <div class="widget">

                            <div class="widget-header">
                                <span class="icon-chart"></span>
                                <h3 class="icon chart">Quantidade de Cargas Realizadas por Dia</h3>
                            </div>

                            <div class="widget-content">
                                <table class="stats" data-chart-type="line" data-chart-colors="">
                                    <caption><?php echo $mes_atual . '/' . $ano; ?> QTD de Cargas</caption>
                                    <thead>
                                        <tr>
                                            <td>&nbsp;</td>
<?php echo $tituloGraf; ?>
                                        </tr>

                                    </thead>

                                    <tbody>

                                        <tr>
                                            <th>Realizado</th>
<?php echo $graf_realizado2; ?>
                                        </tr>

                                    </tbody>
                                </table>
                            </div> <!-- .widget-content -->

                        </div> <!-- .widget -->

                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Total Peso Diario Kg</h3>
                            </div>

                            <div class="widget-content">

                                <table class="table table-bordered table-striped ">
                                    <thead>
                                        <tr>
                                            <th width="12%">DIA</th>
                                            <th>QUANTIDADE DE CARGAS</th>
                                            <th>PESO Kg</th>
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

                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Total Mensal por Cliente</h3>
                            </div>

                            <div class="widget-content">

                                <table class="table table-bordered table-striped ">
                                    <thead>
                                        <tr>
                                            <th width="12%">MES</th>
                                            <th>QUANTIDADE DE CARGAS</th>
                                            <th>PESO Kg</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <?php
                                            echo $totalTabela;
                                        ?>

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