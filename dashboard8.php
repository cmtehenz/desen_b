<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();
    //$dbcDB2  = new \Library\Scripts\scriptDB2();

    $_SESSION['modulo'] = null;

    date_default_timezone_set('America/Sao_Paulo');
    setlocale(LC_ALL, "ptb");
    
    include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptSap.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptMSSQL.php';

    
//    ini_set('display_errors',1);
//ini_set('display_startup_erros',1);
//error_reporting(E_ALL);
?>
<!doctype html>
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
            echo "<meta http-equiv='refresh' content='$tempoTela;url=./dashboard9.php'>";
        }
        ?>

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
    </head>
    <body>
        <?php
            
            $hoje = date('Y-m-d');
            $ano = date('Y');
            $mes = date('m');
            $dia = date('d');
            $idOperLog = '008';


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
                                        Estatísticas mensais BASE <?php echo $idOperLog; ?>
                                    </h2>
                                <br /><br />

                                <div class="dashboard_report first activeState">
                                    <div class="pad">
                                        <span class="value"><?php echo number_format(totalReceitaOpeLog($idOperLog, $ano, $mes, null, null), 2, ',', '.'); ?></span> Receita Frete Peso Mês
                                    </div> <!-- .pad -->
                                </div>

                                <div class="dashboard_report defaultState">
                                    <div class="pad">
                                        <span class="value"><?php echo number_format(valorPrevistoOperLog($idOperLog, $ano, $mes), 2, ',',  '.'); ?></span> Previsto mês
                                    </div> <!-- .pad -->
                                </div>

                                <div class="dashboard_report activeState">
                                    <div class="pad">
                                        <span class="value"><?php echo number_format(totalReceitaOpeLog($idOperLog, $ano, $mes, null, $dia), 2, ',', '.'); ?></span> Frete Peso Dia
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
                                        KM Viagem Vazia
                                    </h2>
                                </form>
                                <br /><br />

                                <div class="dashboard_report first defaultState">
                                    <div class="pad">
                                        <span class="value"><?php echo number_format(kmviagemVazia(null, $idOperLog, $ano, $mes, null), 0, ',', '.'); ?></span> KM Viagem Vazia Mês
                                    </div> <!-- .pad -->
                                </div>

                                <div class="dashboard_report defaultState">
                                    <div class="pad">
                                        <span class="value"><?php echo number_format(kmPrevistoOperLog($idOperLog, $ano, $mes), 0, ',', '.'); ?></span> Previsto Mês    
                                    </div> <!-- .pad -->
                                </div>

                                <div class="dashboard_report defaultState">
                                    <div class="pad">
                                        <span class="value"><?php echo number_format(kmviagemVazia(null, $idOperLog, $ano, $mes, $dia), 0, ',', '.');; ?></span> KM Viagem Vazia Dia
                                    </div> <!-- .pad -->
                                </div>

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
        </div> <!-- #wrapper -->

        <div id="footer"><div style="float: left;">Versão <?php echo $_SESSION['version']; ?></div> Copyright &copy; <?php echo date('Y'); ?>, Case Electronic Ltda.</div>
    </body>
</html>