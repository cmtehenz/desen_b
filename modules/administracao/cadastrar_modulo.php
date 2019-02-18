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
        <title>BID - Cadastrar módulo de menu</title>

        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />
        <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css"/>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
        <script>
            $(document).ready(function(){
                $("#salvar").click(function(){ saveList(); });
            });

            jQuery(function($){
                $('table.sortable').sortable({
                    items: ".modulo",
                    axis: "y",

                    stop: function(event, ui) {
                        ui.modulo.effect('highlight');
                    },

                    update: function(event, ui) {
                        //saveList();
                    }
                });
            });

            function saveList() {
                var lista = document.querySelectorAll('.modulo');
                var registro = new Array();

                [].forEach.call(lista, function(modulo, i) {
                    registro[i] = {};

                    registro[i]['id'] = modulo.id;
                    registro[i]['ordenacao'] = i + 1;

                    var celulas = modulo.querySelectorAll(".nomeModulo");
                    [].forEach.call(celulas, function(celula) { registro[i]['nome'] = celula.innerHTML; });

                    var celulas = modulo.querySelectorAll(".urlModulo");
                    [].forEach.call(celulas, function(celula) { registro[i]['url'] = celula.innerHTML; });
                });

                $.ajax({
                    url: 'post/gravaModulo.php',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(registro),
                    dataType: "JSON",
                    success: function(){
                        location.reload();
                    }
                });
            }
        </script>
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
                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-list"></span><h3 class="icon chart">Módulos de menu</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped sortable">
                                    <thead>
                                        <tr>
                                            <th width='50%'><b>Nome</b></th>
                                            <th width='50%'><b>URL</b></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            $dbcSQL->connect();

                                            foreach ($dbcSQL->select("SELECT * FROM modulo WHERE produto = 'B' ORDER BY ordenacao") as $modulo)
                                                echo
                                                    "<tr class='modulo' id='" . $modulo[idModulo] . "'>
                                                        <td class='nomeModulo' contenteditable='true' oncontextmenu='return false'>$modulo[nome]</td>
                                                        <td class='urlModulo' contenteditable='true' oncontextmenu='return false'>$modulo[url]</td>
                                                    </tr>";

                                            // Cria uma linha em branco para inserção de novo módulo
                                            echo
                                                "<tr class='modulo' id='0'>
                                                    <td class='nomeModulo' contenteditable='true' oncontextmenu='return false' style='font-style: italic;'>Novo módulo...</td>
                                                    <td class='urlModulo'  contenteditable='true' oncontextmenu='return false' style='font-style: italic;'>URL</td>
                                                </tr>";

                                            $dbcSQL->disconnect();
                                        ?>
                                        <tr>
                                            <td align="center" colspan="2"><button type="button" id="salvar" class="btn btn-success">Salvar</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div> <!-- .widget-content -->
                        </div> <!-- .widget -->
                        &#9679; Clique com o botão direito nas células para editá-las <br />
                        &#9679; Arraste e solte os módulos para reorganizá-los
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

        <div id="footer">
            <div style="float: left;">Versão <?php echo $_SESSION['version']; ?></div> Copyright &copy; <?php echo date('Y'); ?>, Case Electronic Ltda.
        </div> <!-- #footer -->
    </body>
</html>