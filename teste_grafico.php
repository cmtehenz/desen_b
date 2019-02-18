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

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
    </head>
    <body>
        <?php
            
            $imob = $_POST['imob'] ?: 0;

            $hoje = date('Y-m-d');
            $ano = date('Y');
            $mes = date('m');
            $dia = date('d');

            /** Monta o gráfico por mês */
            $listaMesesGrafico = $dbcSQL->select("SELECT id_mes idMes, convert(varchar(3), descricao) nome FROM mes");

            //$orcamentoAnual   = $dbcDB2->receitaPrevisto($ano);
            //$faturamentoAnual = $dbcDB2->faturamentoAnual($ano, $imob);

            /** Faz os cálculos de valores das estatísticas */
            foreach(dashboardFretePeso($ano, $mes, $dia) as $dados){
                $realizadoMes = $dados[FRETEPESOMES];
                $fretepesoAno = $dados[FRETEPESOANO];
                $realizadoDia = $dados[FRETEPESODIA];
            }
            
            
            //$realizadoMes = receitaFretePeso($ano, $mes);
            $previstoMes  = receitaPrevistoOperadorLogistico($ano, $mes);
            //$realizadoDia = receitaFretePeso($ano, $mes, $dia);
            $fretePesoAgregadoAnoMes = receitaFretePeso($ano, $mes, null, 'A');
            $fretePesoFrotaAnoMes    = receitaFretePeso($ano, $mes, null, 'F');
            $fretePesoTerceiroAnoMes = receitaFretePeso($ano, $mes, null, 'T');
            $fretePesoAgregadoAnoMesDia = receitaFretePeso($ano, $mes, $dia, 'A');
            $fretePesoFrotaAnoMesDia    = receitaFretePeso($ano, $mes, $dia, 'F');
            $fretePesoTerceiroAnoMesDia = receitaFretePeso($ano, $mes, $dia, 'T');

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
                        <div class="widget widget-plain">
                            <div class="widget-content">
                                    <h2 class="dashboard_title" style="width: 410px; float: left">
                                        Estatísticas mensais
                                        <span><?php echo 'Data: ' . $dia . ' ' . strftime('%b') . ' ' . $ano . ' - Semana Nº ' . date('W'); ?></span>
                                    </h2>
                                <br /><br />

                                <div class="dashboard_report first activeState">
                                    <div class="pad">
                                        <span class="value"><?php echo number_format($realizadoMes); ?></span> Receita Frete Peso Mês
                                    </div> <!-- .pad -->
                                </div>

                                <div class="dashboard_report defaultState">
                                    <div class="pad">
                                        <span class="value"><?php echo number_format($previstoMes); ?></span> Previsto mês
                                    </div> <!-- .pad -->
                                </div>

                                <div class="dashboard_report activeState">
                                    <div class="pad">
                                        <span class="value"><?php echo number_format($realizadoDia); ?></span> Frete Peso Dia
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
                                        MIX Faturamento Dia
                                    </h2>
                                </form>
                                <br /><br />

                                <div class="dashboard_report first defaultState">
                                    <div class="pad">
                                        <span class="value"><?php echo number_format($fretePesoFrotaAnoMesDia); ?></span> FROTA
                                    </div> <!-- .pad -->
                                </div>

                                <div class="dashboard_report defaultState">
                                    <div class="pad">
                                        <span class="value"><?php echo number_format($fretePesoAgregadoAnoMesDia); ?></span> AGREGADO
                                    </div> <!-- .pad -->
                                </div>

                                <div class="dashboard_report defaultState">
                                    <div class="pad">
                                        <span class="value"><?php echo number_format($fretePesoTerceiroAnoMesDia); ?></span> TERCEIRO
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
                                        MIX Faturamento Mensal
                                    </h2>
                                </form>
                                <br /><br />

                                <div class="dashboard_report first defaultState">
                                    <div class="pad">
                                        <span class="value"><?php echo number_format($fretePesoFrotaAnoMes); ?></span> FROTA
                                    </div> <!-- .pad -->
                                </div>

                                <div class="dashboard_report defaultState">
                                    <div class="pad">
                                        <span class="value"><?php echo number_format($fretePesoAgregadoAnoMes); ?></span> AGREGADO
                                    </div> <!-- .pad -->
                                </div>

                                <div class="dashboard_report defaultState">
                                    <div class="pad">
                                        <span class="value"><?php echo number_format($fretePesoTerceiroAnoMes); ?></span> TERCEIRO
                                    </div> <!-- .pad -->
                                </div>

                            </div> <!-- .widget-content -->
                            <br>
                                * Valores Calculados de acordo com o frete peso sem icms;
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
        </div> <!-- #wrapper -->

        <div id="footer"><div style="float: left;">Versão <?php echo $_SESSION['version']; ?></div> Copyright &copy; <?php echo date('Y'); ?>, Case Electronic Ltda.</div>
    </body>    
</html>

