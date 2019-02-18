<?php
    namespace Modulos\Utilitarios;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Importação Pamcard</title>

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
                        <form method="post" action="post/importaDados.php" enctype="multipart/form-data">
                            <div class="widget widget-table">
                                <div class="widget-header">
                                    <span class="icon-document-alt-stroke"></span>
                                    <h3 class="icon chart">Importação de arquivos Pamcard e Bradesco</h3>
                                </div>

                                <div class="widget-content">
                                    <table class="table table-bordered table-striped cadastroBID">
                                        <thead>
                                            <tr>
                                                <th width="18%">Informações</th>
                                                <td width="82%">
                                                    &#9679; Informe os arquivos a serem importados em seus respectivos campos. <br />
                                                    &#9679; Se nenhum arquivo for informado, nenhum dado será atualizado ou perdido.
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Conciliação (*.txt)</th>
                                                <td><input type="file" name="file[]" style="width: 100%;"></td>
                                            </tr>
                                            <tr>
                                                <th>Extrato Bradesco (*.csv)</th>
                                                <td><input type="file" name="file[]" style="width: 100%;"></td>
                                            </tr>
                                            <tr>
                                                <th colspan="2">
                                                    <div align="center"><button class="btn btn-success"><span class="icon-download"></span>Importar</button></div>
                                                </th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div> <!-- .widget-content -->
                            </div> <!-- .widget -->
                        </form>
                    </div> <!-- .grid -->

                    <div class="grid-24">
                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Log de mensagens da importação</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped cadastroBID">
                                    <thead>
                                        <tr><th>Mensagem</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($_SESSION['retLog'] as $log) echo "<tr><td>&#9679; $log[msg].</td></tr>"; unset($_SESSION['retLog']); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
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

        <div id="footer"><div style="float: left;">Versão <?php echo $_SESSION['version']; ?></div> Copyright &copy; <?php echo date('Y'); ?>, Case Electronic Ltda.</div> <!-- #footer -->
    </body>
</html>