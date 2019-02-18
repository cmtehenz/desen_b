<?php
    namespace Modulos\Utilitarios;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));
	

?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Exportação para folha Questor</title>

        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
    </head>
    <body>
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
                        <form method="post" action="post/exportaFolhaBonusSap.php" enctype="multipart/form-data" >
                            <div class="widget widget-table">
                                <div class="widget-header">
                                    <span class="icon-document-alt-stroke"></span>
                                    <h3 class="icon chart">Exportação de dados para a folha Questor Bonus Motorista</h3>
                                </div>

                                <div class="widget-content">
                                    <table class="table table-bordered table-striped cadastroBID">
                                        <thead>
                                            <tr>
                                                <th width="18%">Informações</th>
                                                <td width="82%">
                                                    &#9679; Coloque a data inicial e final do relatório
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Período:</th>
                                                <td>
                                                    <input type="date" id="dtIni" name="dtIni" style="width: 130px" maxlength="10" value="<?php echo $dtIni; ?>" />
                                                    <input type="date" id="dtFin" name="dtFin" style="width: 130px" maxlength="10" value="<?php echo $dtFin; ?>" />
                                                </td>
                                            </tr>
                                            <tr>
                                                <th colspan="2">
                                                    <div align="center"><button class="btn btn-success"><span class="icon-upload"></span>Exportar</button></div>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th>Baixar o Arquivo </th>
                                                <td>  
                                                    <a href='post/bonusMotorista.txt' download="bonusMotorista.txt">Clique Aqui para Baixar o Arquivo</a>													
						</td>
                                            </tr>
                                        </thead>
                                    </table>
                                </div> <!-- .widget-content -->
                            </div> <!-- .widget -->
                        </form>
                        
                        <?php   
                            if(isset($_SESSION['mensagem'])){
                                if($_SESSION['notify'] === 'error'){
                                    echo "<div class='agr grid-7 box notify-error' style='margin-top: 15px; padding: 10px; width: 30%; float: left;'>
                                            <b>&#9679; ".$_SESSION['mensagem']."</b>
                                        </div>";
                                } elseif($_SESSION['notify'] === 'success') {
                                    echo "<div class='agr grid-7 box notify-success' style='margin-top: 15px; padding: 10px; width: 30%; float: left;'>
                                            <b>&#9679; ".$_SESSION['mensagem']."</b>
                                        </div>";
                                }
                            }   
                            $_SESSION['mensagem']= null;
                            $_SESSION['notify']  = null;
                        
                        ?>
                        
                        
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

        <div id="footer"><div style="float: left;">Versão <?php echo $_SESSION['version']; ?></div> Copyright &copy; <?php echo date('Y'); ?>, Case Electronic Ltda.</div> <!-- #footer -->
    </body>
</html>