<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';
    
    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();
    //$dbcDB2  = new \Library\Scripts\scriptDB2();

    $_SESSION['modulo'] = null;

    date_default_timezone_set('America/Sao_Paulo');
    setlocale(LC_ALL, "ptb");
    
    //ini_set('display_errors', 1);
    //ini_set('log_errors', 1);
    //ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
    //error_reporting(E_ALL);
    
    include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptMSSQL.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptSap.php';
    //include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptDB2.php';
   
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Dashboard</title>

        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />
        <?php
        $perfilUsuario = rtrim(buscaPerfilUsuario($_SESSION['idUsuario']));
        if($perfilUsuario == 'A'){
            $tempoTela = buscaTempoTela($_SESSION['idUsuario']);
            echo "<meta http-equiv='refresh' content='$tempoTela;url=./dashboard1.php'>";
        }
        ?>

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

            foreach(dashboardFretePeso($ano, $mes, $dia) as $dados){
                $fretepesoMes = $dados[FRETEPESOMES];
                $fretepesoAno = $dados[FRETEPESOANO];
                $fretepesodia = $dados[FRETEPESODIA];
            }            
            
            /** Faz os cálculos de valores das estatísticas */
            $realizadoMes = number_format($fretepesoMes, 0, '.', '');
            $previstoMes  = number_format(receitaPrevistoOperadorLogistico($ano, $mes), 0, '.', '');
            $porcentagemRealPrev = number_format(($realizadoMes / $previstoMes)*100, 0, '', '');
            /** Prepara as barras do gráfico, formatando os valores para escrita */
            
            foreach (dashboardGraf($ano) as $dados){
                $meses        .= "<th>".substr($dados[NomeMes], 0, 3)."</th>";
                $grfPrevisto .= "<td>".(receitaPrevistoOperadorLogistico($ano, $dados['IdMes'])/100000)."</td>";
                $grfRealizado .= "<td>".(number_format(($dados[FRETEPESOMES]), 2, ',', '.') ?: 0) . "</td>";
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
                    <div class="grid-17">
                        <div class="widget widget-plain">
                            <div class="widget-content">
                                <form action="#" name="formIndex" method="POST" onchange="document.formIndex.submit()">
                                    <h2 class="dashboard_title" style="width: 410px; float: left">
                                        Estatísticas mensais
                                        <span><?php echo 'Data: ' . $dia . ' ' . strftime('%b') . ' ' . $ano . ' - Semana Nº ' . date('W'); ?></span>
                                    </h2>
                                    <div style="float: right;">
                                        Calcular venda de imobilizado
                                        <input type="checkbox" name="imob" value="1" <?php if ($imob) echo "checked"; ?> />
                                    </div>
                                </form>
                                <br /><br />

                                <div class="dashboard_report first activeState">
                                    <div class="pad">
                                        <span class="value"><?php echo number_format($fretepesoMes, 0, '.', ','); ?></span> Receita Frete Peso
                                    </div> <!-- .pad -->
                                </div>

                                <div class="dashboard_report defaultState">
                                    <div class="pad">
                                        <span class="value"><?php echo number_format(receitaPrevistoOperadorLogistico($ano, $mes), 0, '.', ','); ?></span> Previsto mês
                                    </div> <!-- .pad -->
                                </div>

                                <div class="dashboard_report defaultState">
                                    <div class="pad">
                                        <span class="value"><?php echo number_format($fretepesodia, 0, '.', ','); ?></span> Hoje
                                    </div> <!-- .pad -->
                                </div>

                                <div class="dashboard_report defaultState last">
                                    <div class="pad">
                                        <span class="value"><?php echo number_format($fretepesoAno, 0, '.', ',');; ?></span> Acumulado ano
                                    </div> <!-- .pad -->
                                </div>
                            </div> <!-- .widget-content -->
                        </div> <!-- .widget -->

                        <div class="widget widget-tabs">
                            <div class="widget-header">
                                <span class="icon-bars"></span>
                                <h3 class="">Receita x previsto</h3>

                                <ul class="tabs right">
                                    <li class="active"><a href="#yearly">Anual</a></li>
                                </ul>
                            </div>

                            <div class="widget-content">
                                <table class="stats" data-chart-type="bar" data-chart-colors="">
                                    <caption><?php echo $ano; ?> - Receita bruta (milhões)</caption>
                                    <thead>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <?php echo $meses; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th>Previsto</th>
                                            <?php echo $grfPrevisto; ?>
                                        </tr>
                                        <tr>
                                            <th>Realizado</th>
                                            <?php echo $grfRealizado; ?>
                                        </tr>
                                    </tbody>
                                </table>
                            </div> <!-- .widget-content -->
                        </div> <!-- .widget -->
                    </div> <!-- .grid -->

                    <div class="grid-7">
                        <div id="gettingStarted" class="box">
                            <h3>Receita</h3>

                            <p>Percentual para atingir o previsto</p>

                            <div class="progress-bar secondary">
                                <div class="bar" style="width: <?php echo (($porcentagemRealPrev > 100) ? 100 : $porcentagemRealPrev); ?>%;"><?php echo $porcentagemRealPrev . '%'; ?></div>
                            </div>
                        </div>

                        <div class="box" style="visibility: hidden">
                            <h3>Segurança</h3>

                            <ul style="margin-bottom: 0px;"><li>Há <?php echo $dbcSQL->simpleSelect("ocorrencia", "datediff(d, dtOcorrencia, getdate())", "", "dtOcorrencia DESC") ?: 0; ?> dias sem acidente</li></ul>
                        </div>

                        <div class="box" style="visibility: hidden">
                            <h3>Atividades recentes</h3>

                            <ul style="margin-bottom: 0px;">
                                <?php
                                    foreach ($dbcSQL->select("SELECT TOP 10 atividade FROM atividades", null, "data DESC") as $atividade)
                                        echo "<li>$atividade[atividade]</li>"
                                ?>
                            </ul>
                        </div>
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