<?php
    namespace Modulos\Multas\Cadastros;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    use Library\Classes\KeyDictionary as DD;

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();

    $_SESSION['modulo'] = basename(dirname(dirname($_SERVER['PHP_SELF'])));
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Cad. infrações CTB</title>

        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <style type="text/css">
            #codigo { width: 60px; text-align: center; }
            #digito { width: 22px; text-align: center; }

            textarea { overflow: hidden; word-wrap: break-word; resize: none; height: 80px; max-height: 80px; }
        </style>

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
    </head>
    <body>
        <?php
            $params = filter_input_array(INPUT_GET);

            $registro = $dbcSQL->selectTopOne("SELECT * FROM mlt.infracao", array($dbcSQL->whereParam("idInfracao", $params['id'])));

            $lista = $dbcSQL->select("SELECT i.* FROM mlt.infracao i", null, "i.codigo");
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
                        <form method="post" action="post/gravaInfracao.php" enctype="multipart/form-data">
                            <div class="widget widget-table">
                                <div class="widget-header">
                                    <span class="icon-document-alt-stroke"></span>
                                    <h3 class="icon chart">Cadastro de códigos de infração ao CTB</h3>
                                </div>

                                <div class="widget-content">
                                    <table class="table table-bordered table-striped cadastroBID">
                                        <thead>
                                            <tr>
                                                <th width="15%">Código:</th>
                                                <td>
                                                    <input name="codigo" id="codigo" maxlength="3" value="<?php echo $registro['codigo']; ?>" />
                                                    -
                                                    <input name="digito" id="digito" maxlength="2" value="<?php echo $registro['digito']; ?>" />
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Descrição:</th>
                                                <td>
                                                    <textarea class="fill" name="descricao" id="descricao" maxlength="255"><?php echo $registro['descricao']; ?></textarea>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Classificação:</th>
                                                <td><select name="classificacao" style="width: 12%;"><?php echo $hoUtils->getOptionsSelect(DD::arrayClassificacaoInfracao(), $registro['classificacao']); ?></select></td>
                                            </tr>
                                            <tr>
                                                <th colspan="2">
                                                    <div align="center">
                                                        <input name="id" id="id" hidden value="<?php echo $registro['idInfracao']; ?>">
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
                                <h3 class="icon chart">Lista de infrações cadastradas</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width="5%">Ações</th>
                                            <th width="8%">Código</th>
                                            <th>Descrição resumida</th>
                                            <th width="10%">Classificação</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            foreach ($lista as $linha)
                                                echo
                                                    "<tr>
                                                        <td><a class='icon-pen-alt-fill tooltip' href='?id=$linha[idInfracao]' title='Editar'></a></td>
                                                        <td class='text-center'>" . $linha['codigo'] . " - " . $linha['digito'] . "</td>
                                                        <td>" . substr($linha['descricao'], 0, 100) . "</td>
                                                        <td>" . DD::valueClassificacaoInfracao($linha['classificacao']) . "</td>
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