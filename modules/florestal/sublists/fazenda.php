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

        <title>BID - Carregamentos por fazenda</title>

        <meta charset="utf-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js"></script>
        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
    </head>

    <body>
        <?php
            include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptMSSQL.php';
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

            if (isset($_GET['origem'])){
                $origem = $_GET['origem'];
            }
            if (isset($_POST['origem'])){
                $origem = $_POST['origem'];
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
            $sql_origem = mssql_query("SELECT * FROM origem WHERE id=$origem");
            $DadosOrigem = mssql_fetch_array($sql_origem);


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
            
            for ($i = 1; $i <= $dias_mes; $i++) {
                $dias_grafico[] = $i;               
            }
            $p=0;
            
            foreach (florestalCarregamentoDiario($ano, $mes_atual, $origem) as $dados){
                
                $p++;
                $data_gr[$p] = $dados[DIA];
                $peso_dia[$p] = $dados[PESO];
                $viagens[$p] = $dados[VIAGENS];
                $totalViagens += $dados[VIAGENS];
                $totalPeso += $dados[PESO];
                
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
                                <input type="hidden" id="origem" name="origem" value="<?php echo $origem; ?>"/>
                                <label>Selecione o MES:</label>
                                <select id="mes" name="mes">
<?php echo $listaMes; ?>
                                </select>

                                <label>Selecione o ANO:</label>
                                <select id="ano" name="ano">
<?php echo $listaAno; ?>
                                </select>

                                <input type="submit" value="IR">
                                Periodo Selecionado: <?php echo $dadosMesSelecionado[1] . '/ ' . $ano . ' <br><br><h2> Fazenda: ' . $DadosOrigem[1] . '</h2>'; ?>


                            </div>
                        </form>
                        <br>
                        <div class="widget">

                            <div class="widget-header">
                                <span class="icon-chart"></span>
                                <h3 class="icon chart">Peso Carregado por Dia Kg</h3>
                            </div>

                            <div class="widget-content">
                                <canvas id="graficoCarDia"></canvas>
                                
                            </div> <!-- .widget-content -->

                        </div> <!-- .widget -->

                        <div class="widget">

                            <div class="widget-header">
                                <span class="icon-chart"></span>
                                <h3 class="icon chart">Quantidade de Cargas Realizadas por Dia</h3>
                            </div>

                            <div class="widget-content">
                                <canvas id="graficoViagens"></canvas>
                                
                            </div> <!-- .widget-content -->

                        </div> <!-- .widget -->

                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Total Peso Diario Kg</h3>
                            </div>

                            <div class="widget-content">
                                <div id="tabelaTodos"></div>
                                
                            </div> <!-- .widget-content -->

                        </div> <!-- .widget -->

                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Total Mensal por Fazenda</h3>
                            </div>

                            <div class="widget-content">
                                <div id="tabelaTotal"></div>
                                
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

        <script>
            var ctx = document.getElementById('graficoCarDia').getContext('2d');
            var ctx_viagens = document.getElementById('graficoViagens').getContext('2d');
            
            const dias =  [<?php echo '"'.implode('","', $dias_grafico).'"' ?>];
            const peso = [ ];
            const viagens = [ ];
            const diasValue = [<?php echo '"'.implode('","', $data_gr).'"' ?>];
            const pesoValue = [<?php echo '"'.implode('","', $peso_dia).'"' ?>];
            const viagensValue = [<?php echo '"'.implode('","', $viagens).'"' ?>];
            const totalViagens = [<?php echo $totalViagens; ?>];
            const totalPeso    = [<?php echo $totalPeso; ?>];
            
            
            for(var i = 0; i < dias.length; i++){
                peso[i] = 0;
                viagens[i] = 0;
            }
            
            
            for (i = 0; i < dias.length; i++) { 
                
               
                for(j = 0; j < diasValue.length; j++){
                    
                    if(dias[i] === diasValue[j]){
                        
                        peso.splice(i, 1, pesoValue[j] );
                        viagens.splice(i, 1, viagensValue[j]);
                        
                    }
                }
                
            }
            linhaTabela = '';
            for(var i = 1; i < dias.length + 1; i++){
                linhaTabela = linhaTabela + "<tr class='gradeA'>";
                linhaTabela = linhaTabela + "<td>"+ i +"</td>";
                linhaTabela = linhaTabela + "<td align='left'>"+viagens[i-1] +"</td>";
                linhaTabela = linhaTabela + "<td align='right'>"+peso[i-1] +"</td>";
                linhaTabela = linhaTabela + "</tr>";
                
            }
            
            
            var tabelaTodos = document.getElementById("tabelaTodos");
            
            tabelaTodos.innerHTML = [
                "<table class='table table-bordered table-striped '>",
                    "<thead>",
                        "<tr>",
                            "<th width='12%'>DIA</th>",
                            "<th align='rigth'>QUANTIDADE DE CARGAS</th>",
                            "<th>PESO Kg</th>",
                        "</tr>",
                    "</thead>",
                    "<tbody>",
                    linhaTabela,
                    "</tbody>",
                "</table>"
            ].join("\n");
            
            
          
            
            var tabela = document.getElementById("tabelaTotal");
            
            tabela.innerHTML = [
                "<table class='table table-bordered table-striped '>",
                    "<thead>",
                        "<tr>",
                            "<th width='12%'>MES</th>",
                            "<th>QUANTIDADE DE CARGAS</th>",
                            "<th>PESO Kg</th>",
                        "</tr>",
                    "</thead>",
                    "<tbody>",
                    "<tr>",
                    "<td> <?php echo $dadosMesSelecionado[1]; ?>  </td>",
                    "<td align='left'>"+ totalViagens +"</td>",
                    "<td align='right'>"+ totalPeso +"</td>",
                    "</tr>",
                    "</tbody>",
                "</table>"
            ].join("\n");
            
            
            
           
            
            
            
                                 
            
            var chart = new Chart(ctx, {
                // The type of chart we want to create
                type: 'line',

                // The data for our dataset
                data: {
                    labels: dias,
                    datasets: [{
                        label: "Peso diario",
                        backgroundColor: '#0066CC',
                        borderColor: '#0066CC',
                        data: peso,
                        fill: false
                    }]
                },

                // Configuration options go here
                options: {
                    legend:{
                        display: true,
                        labels: {
                            fontColor: '#666'
                        },
                        position: 'bottom'                        
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    }
                }
            });
            
            var chart = new Chart(ctx_viagens, {
                // The type of chart we want to create
                type: 'line',

                // The data for our dataset
                data: {
                    labels: dias,
                    datasets: [{
                        label: "Viagens Diarias",
                        backgroundColor: '#0066CC',
                        borderColor: '#0066CC',
                        data: viagens,
                        fill: false
                    }]
                },

                // Configuration options go here
                options: {
                    legend:{
                        display: true,
                        labels: {
                            fontColor: '#666'
                        },
                        position: 'bottom'                        
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    }
                }
            });
            
        
        
        </script>

    </body>
</html>