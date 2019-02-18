<?php
    namespace Modulos\Administracao;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Classes\connectMSSQL();

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));
?>
<!doctype html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Lista de usuários</title>

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
                <div id="contentHeader" style="margin-bottom: 0px;">
                    <h1><?php echo $_SESSION['nomeEmpresa']; ?></h1>
                </div> <!-- #contentHeader -->

                <div class="container">
                    <div class="widget-content">
                        <div class="widget-header">
                            <span class="icon-list"></span><h3 class="icon chart">Relação de usuários</h3>
                        </div>
                        <table class="table table-bordered table-striped">
                            <tr>
                                <td width='30%'><b>Nome</b></td>
                                <td width='30%'><b>Login</b></td>
                                <td width='30%'><b>Email</b></td>
                                <td width='10%'><b>Ação</b></td>
                            </tr>

                            <?php
                                $dbcSQL->connect();

                                foreach ($dbcSQL->select("SELECT * FROM usuario ORDER BY nome") as $usuario)
                                    echo
                                        "<tr>
                                            <td>" . $usuario['nome'] . "</td>
                                            <td>" . $usuario['login'] . "</td>
                                            <td>" . $usuario['email'] . "</td>
                                            <td><a href='cadastrar_usuario.php?username=$usuario[login]&id=$usuario[id_usuario]' class='tooltip' title='Editar'><i class='icon-pen-alt-fill'></a></a></td>
                                        </tr>";

                                $dbcSQL->disconnect();
                            ?>
                        </table>
                    </div> <!-- .widget-content -->
                </div>
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

        <div id="footer">
            <div style="float: left;">Versão <?php echo $_SESSION['version']; ?></div> Copyright &copy; <?php echo date('Y'); ?>, Case Electronic Ltda.
        </div> <!-- #footer -->
    </body>
</html>