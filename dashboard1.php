<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();
    //$dbcDB2  = new \Library\Scripts\scriptDB2();

    $_SESSION['modulo'] = null;

    date_default_timezone_set('America/Sao_Paulo');
    setlocale(LC_ALL, "ptb");
    
    include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptMSSQL.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptSap.php';

?>

<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Dashboard</title>

        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        
        <?php
        if(rtrim(buscaPerfilUsuario($_SESSION['idUsuario'])) == 'A'){
            $tempoTela = buscaTempoTela($_SESSION['idUsuario']);
            echo "<meta http-equiv='refresh' content='$tempoTela;url=./dashboard2.php'>";
        }
        ?>

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />
        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/novo.css'); ?>" type="text/css" />
        
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js"></script>
        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
    </head>
    <body>
        <?php
            
            $imob = $_POST['imob'] ?: 0;

            $hoje = date('Y-m-d');
            $ano = date('Y');
            $mes = date('m');
            $dia = date('d');
            
            foreach (dashboardMixFaturamento($ano, $mes, null) as $d){
                //$fretePesoTotal     = number_format($d[TOTAL], 0 , '', '');
                $fretePesoFrota     = number_format($d[FRETEPESOFROTA], 0, '', '');
                $fretePesoAgregado  = number_format($d[FRETEPESOAGREGADO], 0, '', '');
                $fretePesoTerceiros = number_format($d[FRETEPESOTERCEIRO], 0, '', '');
            }
            
            foreach (dashboardMixFaturamento($ano, $mes, $dia) as $d){
                //$fretePesoTotal     = number_format($d[TOTAL], 0 , '', '');
                $fretePesoFrotaDia     = number_format($d[FRETEPESOFROTA], 0, '', '');
                $fretePesoAgregadoDia  = number_format($d[FRETEPESOAGREGADO], 0, '', '');
                $fretePesoTerceirosDia = number_format($d[FRETEPESOTERCEIRO], 0, '', '');
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
                    <div>
                        <div class="box-grafico">
                            <h2 class="dashboard_title" style="margin-left: 30%" >
                                Mix Faturamento dia
                            </h2>
                            <canvas id="grafico"></canvas>
                        </div> <!-- .container -->
                        <div class="box-grafico">
                            <h2 class="dashboard_title" style="margin-left: 25%" >
                                Mix Faturamento mensal
                            </h2>
                            <canvas id="graficoMes"></canvas>                        
                        </div>
                    </div>
                    <div>
                        <div class="grid-24">
                        <div class="widget widget-plain">
                            <div class="widget-content">
                                <form action="#" name="formIndex" method="POST" onchange="document.formIndex.submit()">
                                    <h2 class="dashboard_title" style="width: 410px; float: left">
                                        Mix Faturamento Dia
                                    </h2>
                                </form>
                                <br /><br />

                                <div class="dashboard_report first defaultState">
                                    <div class="pad">
                                        <span class="value"><?php echo number_format($fretePesoFrotaDia); ?></span> FROTA
                                    </div> <!-- .pad -->
                                </div>

                                <div class="dashboard_report defaultState">
                                    <div class="pad">
                                        <span class="value"><?php echo number_format($fretePesoAgregadoDia); ?></span> AGREGADO
                                    </div> <!-- .pad -->
                                </div>

                                <div class="dashboard_report defaultState" >
                                    <div class="pad" >
                                        <span class="value"><?php echo number_format($fretePesoTerceirosDia); ?></span> TERCEIRO
                                    </div> <!-- .pad -->
                                </div>

                            </div> <!-- .widget-content -->
                        </div> <!-- .widget -->
                    </div> <!-- .grid -->
                    
                    <div class="grid-24">
                        <div class="widget widget-plain">
                            <div class="widget-content">
                                <form action="#" name="formIndex" method="POST" onchange="document.formIndex.submit()">
                                    <h2 class="dashboard_title" style="width: 410px; float: left">
                                        Mix Faturamento Mensal
                                    </h2>
                                </form>
                                <br /><br />

                                <div class="dashboard_report first defaultState">
                                    <div class="pad">
                                        <span class="value"><?php echo number_format($fretePesoFrota); ?></span> FROTA
                                    </div> <!-- .pad -->
                                </div>

                                <div class="dashboard_report defaultState">
                                    <div class="pad">
                                        <span class="value"><?php echo number_format($fretePesoAgregado); ?></span> AGREGADO
                                    </div> <!-- .pad -->
                                </div>

                                <div class="dashboard_report defaultState">
                                    <div class="pad">
                                        <span class="value"><?php echo number_format($fretePesoTerceiros); ?></span> TERCEIRO
                                    </div> <!-- .pad -->
                                </div>

                            </div> <!-- .widget-content -->
                            <br>
                                * Valores Calculados de acordo com o frete peso sem icms;
                        </div> <!-- .widget -->
                    </div> <!-- .grid -->
                        
                    </div>
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

        <div id="footer"><div style="float: left;">Versão <?php echo $_SESSION['version']; ?></div> Copyright &copy; <?php echo date('Y'); ?>, Case Electronic Ltda.</div>
        <script>
            var ctx = document.getElementById('grafico').getContext('2d');
            var ctxMes = document.getElementById('graficoMes').getContext('2d');
            
            const freteAgregadoMes  = "<?php echo $fretePesoAgregado ?>";
            const freteFrotaMes     = "<?php echo $fretePesoFrota ?>";
            const freteTerceiroMes  = "<?php echo $fretePesoTerceiros ?>";
            
            const freteAgregadoDia  = "<?php echo $fretePesoAgregadoDia ?>";
            const freteFrotaDia     = "<?php echo $fretePesoFrotaDia ?>";
            const freteTerceiroDia  = "<?php echo $fretePesoTerceirosDia ?>";
            
            var chart = new Chart(ctx, {
                // The type of chart we want to create
                type: 'pie',

                // The data for our dataset
                data: {
                    labels: ["Frota", "Agregado", "Terceiro"],
                    datasets: [{
                        label: "Faturamento dia",
                        backgroundColor: ['#00BBBE', '#B33951', '#575A4B'],
                        data: [freteFrotaDia , freteAgregadoDia, freteTerceiroDia ]
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
                    }
                }
            });
            
            var chartMes = new Chart(ctxMes, {
                // The type of chart we want to create
                type: 'pie',

                // The data for our dataset
                data: {
                    labels: [`Frota`, `Agregado`, `Terceiro`],
                    datasets: [{
                        label: "Faturamento Mes",
                        backgroundColor: ['#00BBBE', '#B33951', '#575A4B'],
                        data: [freteFrotaMes , freteAgregadoMes, freteTerceiroMes ]
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
                                   
                        
                    }                    
                }
                
            });
        </script>
    </body>    
</html>

