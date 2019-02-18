<?php
    namespace Modulos\Operacional;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    use Library\Classes\KeyDictionary as KeyDictionary;

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Cad. controles de revisão</title>

        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/modernizr.js"); ?>"></script>
        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
        <script>
            Modernizr.load({
                test: Modernizr.inputtypes.date,
                nope: ['http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.7/jquery-ui.min.js', 'jquery-ui.css'],
                complete: function () {
                    $('input[type=date]').datepicker({ dateFormat: 'yy-mm-dd' });
                }
            });
        </script>
    </head>
    <body>
        <?php
            $params = filter_input_array(INPUT_GET);

            $registro = $dbcSQL->selectTopOne("SELECT * FROM revisao", array($dbcSQL->whereParam("idRevisao", $params['id'])));

            $lista = $dbcSQL->select("SELECT r.*, dbo.DateFormat103(dtUltima) data FROM revisao r", null, "r.placa");
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
                        <form method="post" action="post/gravaContRevisao.php" enctype="multipart/form-data">
                            <div class="widget widget-table">
                                <div class="widget-header">
                                    <span class="icon-document-alt-stroke"></span>
                                    <h3 class="icon chart">Cadastro de placas para o controle de revisões</h3>
                                </div>

                                <div class="widget-content">
                                    <table class="table table-bordered table-striped cadastroBID">
                                        <thead>
                                            <tr>
                                                <th width="15%">Placa:</th>
                                                <td width="20%"><input class="perc50" name="placa" id="placa" maxlength="7" value="<?php echo $registro['placa']; ?>" /></td>
                                                <td width="15%"><input type="checkbox" name="vendido" id="vendido" value="1" <?php echo $registro['vendido'] ? "checked" : ""; ?> /> Veículo vendido</td>
                                                <td><input type="checkbox" name="parado" id="parado" value="1" <?php echo $registro['parado'] ? "checked" : ""; ?> /> Veículo parado</td>
                                            </tr>
                                            <tr>
                                                <th>Km últ. revisão:</th>
                                                <td colspan="3"><input class="perc20 text-right" name="kmUltima" id="kmUltima" value="<?php echo $hoUtils->numberFormat($registro['kmUltima'], 0, 0, "", ""); ?>"></td>
                                            </tr>
                                            <tr>
                                                <th>Período:</th>
                                                <td colspan="3"><input class="perc20 text-right" name="periodo" id="periodo" value="<?php echo $hoUtils->numberFormat($registro['periodo'], 0, 0, "", ""); ?>"></td>
                                            </tr>
                                            <tr>
                                                <th>Data últ. revisão:</th>
                                                <td colspan="3"><input class="perc20" type="date" name="dtUltima" id="dtUltima" value="<?php echo substr($registro['dtUltima'], 0, 10); ?>"></td>
                                            </tr>
                                            <tr>
                                                <th>Operação:</th>
                                                <td colspan="3"><select name="operacao" style="width: 12%;"><?php echo $hoUtils->getOptionsSelect(KeyDictionary::arrayOperacao(), $registro['operacao']); ?></select></td>
                                            </tr>
                                            <tr>
                                                <th colspan="5">
                                                    <div align="center">
                                                        <input name="id" id="id" hidden value="<?php echo $registro['idRevisao']; ?>">
                                                        <button class="btn btn-success"><span class="icon-check"></span>Salvar</button>
                                                    </div>
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
                                <h3 class="icon chart">Lista de placas cadastradas</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width="5%">Ações</th>
                                            <th>Placa</th>
                                            <th width="12%">Km últ. revisão</th>
                                            <th>Data</th>
                                            <th width="12%">Período</th>
                                            <th>Operação</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            foreach ($lista as $linha)
                                                echo
                                                    "<tr>
                                                        <td><a class='icon-pen-alt-fill tooltip' href='?id=$linha[idRevisao]' title='Editar'></a></td>
                                                        <td>$linha[placa]</td>
                                                        <td class='text-center'>" . $hoUtils->numberFormat($linha['kmUltima'], 0, 0) . "</td>
                                                        <td>$linha[data]</td>
                                                        <td class='text-center'>" . $hoUtils->numberFormat($linha['periodo'], 0, 0) . "</td>
                                                        <td>" . KeyDictionary::valueOperacao($linha['operacao']) . "</td>
                                                    </tr>";
                                        ?>
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

        <div id="footer">
            <div style="float: left;">Versão <?php echo $_SESSION['version']; ?></div> Copyright &copy; <?php echo date('Y'); ?>, Case Electronic Ltda.
        </div> <!-- #footer -->
    </body>
</html>