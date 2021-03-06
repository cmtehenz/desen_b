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
        <title>BID - Carregamentos por motorista</title>

        <meta charset="utf-8" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
    </head>

    <body>
        <?php
            include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
            include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_db2_bino.php';

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

            if (isset($_GET['motorista'])){
                $motorista = $_GET['motorista'];
            }
            if (isset($_POST['motorista'])){
                $motorista = $_POST['motorista'];
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

//NOME DO MOTORISTA
            $sql_motorista = mssql_query("SELECT TOP 1 nome FROM motorista
                                WHERE matricula=$motorista
                             ");
            $dadosNomeMotorista = mssql_fetch_array($sql_motorista);


//NOME DO MES SELECIONADO
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
            //DIAS PARA O GRAFICO
            $dias_mes = cal_days_in_month(CAL_GREGORIAN, $mes_atual, $ano);
            for ($i = 1; $i <= $dias_mes; $i++){
                //BUSCAR DIA DA SEMANA
                $dia = "22/03/2010";
                $diaa = $i . "-";
                $mes = $mes_atual . "-";
                $diasemana = date("w", mktime(0, 0, 0, $mes, $diaa, $ano));
                switch ($diasemana){
                    case"0": $dia_semana = "domingo";
                        break;
                    case"1": $dia_semana = "segunda";
                        break;
                    case"2": $dia_semana = "terca";
                        break;
                    case"3": $dia_semana = "quarta";
                        break;
                    case"4": $dia_semana = "quinta";
                        break;
                    case"5": $dia_semana = "sexta";
                        break;
                    case"6": $dia_semana = "sabado";
                        break;
                }
                $diaSemanaAbreviado = strtoupper(substr($dia_semana, 0, 1));

                $tituloGraf = $tituloGraf . '<th>' . $i . '' . $diaSemanaAbreviado . '</th>';


                $script_carregamentoDiario = mssql_query("SELECT sum(peso), count(*), SUM((peso/1000)*valor) FROM carregamento where motorista=$motorista AND year(data)=$ano and month(data)=$mes_atual and day(data) = $i");
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
                $graf_realizado = $graf_realizado . '<td>' . ($realizadoDiario / 1000) . '</td>';

                $graf_realizado2 = $graf_realizado2 . '<td>' . $realizadoDiario2 . '</td>';
                /*                 * **************************** */


                $linhaTabela = $linhaTabela . "<tr class='gradeA'>
                                    <td><a href='carregamentos.php?motorista=$motorista&ano=$ano&mes=$mes_atual&dia=$i'>$i - $dia_semana</a></td>
                                    <td align='right'>$cargas</td>
                                    <td align='right'>" . number_format(($pesoCarregado / 1000), 0, ',', '.') . "</td>
                                    <td align='right'>" . number_format(($pesoCarregado / 1000) / $cargas, 0, ',', '.') . "</td>
                                    <td align='right'>" . number_format($dados_carregamentoDiario[2] / ($pesoCarregado / 1000), 0, ',', '.') . "</td>
                                    <td align='right'>" . number_format($dados_carregamentoDiario[2], 2, ',', '.') . "</td>
                                </tr>";
            }

            $script_carregamentoMensal = mssql_query("SELECT SUM(peso), COUNT(*), SUM((peso/1000)*valor) FROM carregamento where motorista=$motorista AND year(data)=$ano AND month(data)=$mes_atual");
            $dados_soma = mssql_fetch_array($script_carregamentoMensal);
            $totalPeso = $dados_soma[0];
            $totalViagens = $dados_soma[1];
            $totalReais = $dados_soma[2];
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
                                <input type="hidden" id="origem" name="origem" value="<?php echo $id_placa; ?>"/>
                                <label>Selecione o MES:</label>
                                <select id="mes" name="mes" onchange="document.form1.submit()">
<?php echo $listaMes; ?>
                                </select>

                                <label>Selecione o ANO:</label>
                                <select id="ano" name="ano" onchange="document.form1.submit()">
<?php echo $listaAno; ?>
                                </select>

                                <br><br><b>Periodo Selecionado: <?php echo $dadosMesSelecionado[1] . '/ ' . $ano . ' <br><h2> Motorista: ' . $dadosNomeMotorista[0] . '</h2>'; ?></b>


                            </div>
                        </form>
                        <br>
                        <div class="widget">

                            <div class="widget-header">
                                <span class="icon-chart"></span>
                                <h3 class="icon chart">Peso Carregado por Dia</h3>
                            </div>

                            <div class="widget-content">
                                <table class="stats" data-chart-type="line" data-chart-colors="">
                                    <caption><?php echo $mes_atual . '/' . $ano; ?> Peso Diario (T)</caption>
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
                                <h3 class="icon chart">Total Diario</h3>
                            </div>

                            <div class="widget-content">

                                <table class="table table-bordered table-striped ">
                                    <thead>
                                        <tr>
                                            <th width="12%">DIA</th>
                                            <th>QUANTIDADE DE CARGAS</th>
                                            <th>PESO (T)</th>
                                            <th>MEDIA T/V</th>
                                            <th>MEDIA F/T</th>
                                            <th>FATURAMENTO</th>
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

                            <h3>TOTAL CARREGAMENTO</h3>

                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>EMPRESA</th>
                                        <th>TOTAL VIAGENS</th>
                                        <th>TOTAL PESO</th>
                                        <th>TOTAL MEDIA T/V</th>
                                        <th>TOTAL MEDIA F/T</th>
                                        <th>TOTAL R$</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="odd gradeX">
                                        <td><?php echo $dados_empresa[2]; ?></td>
                                        <td align='right'><?php echo $totalViagens; ?></td>
                                        <td align='right'><?php echo number_format($totalPeso / 1000, 0, ',', '.'); ?> T</td>
                                        <td align='right'><?php echo number_format(($totalPeso / 1000) / $totalViagens, 0, ',', '.'); ?></td>
                                        <td align='right'><?php echo number_format($totalReais / ($totalPeso / 1000), 0, ',', '.'); ?></td>
                                        <td align='right'><?php echo number_format($totalReais, 2, ',', '.'); ?></td>
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
                                    <li><a href="javascript:;">Edit Profile</a></li>
                                    <li><a href="javascript:;">Suspend Account</a></li>
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