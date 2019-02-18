<?php
    namespace Modulos\Comercial;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Classes\connectMSSQL();

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));

    setlocale(LC_ALL, "ptb");
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Cad. orçamento por cliente</title>

        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
    </head>
    <body>
        <?php
            $params = filter_input_array(INPUT_GET);

            $dbcSQL->connect();

            $registro = $dbcSQL->selectTopOne("SELECT * FROM orccli", array($dbcSQL->whereParam("idOrcCli", $params['id'])));

            $lista = $dbcSQL->select("SELECT * FROM orccli", null, "cgc, ano, mes");

            $dbcSQL->disconnect();
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
                        <form method="post" action="post/gravaOrcamento.php" enctype="multipart/form-data">
                            <div class="widget widget-table">
                                <div class="widget-header">
                                    <span class="icon-document-alt-stroke"></span>
                                    <h3 class="icon chart">Cadastro de orçamento por cliente</h3>
                                </div>

                                <div class="widget-content">
                                    <table class="table table-bordered table-striped cadastroBID">
                                        <thead>
                                            <tr>
                                                <th width="10%">CPF / CNPJ:</th>
                                                <td width="90%"><input class="fill" name="cgc" id="cgc" maxlength="14" value="<?php echo $registro['cgc']; ?>"></td>
                                            </tr>
                                            <tr>
                                                <th>Razão social:</th>
                                                <td><input class="fill" name="razaoSocial" id="razaoSocial" maxlength="100" value="<?php echo $registro['razaoSocial']; ?>"></td>
                                            </tr>
                                            <tr>
                                                <th>Ano / mês:</th>
                                                <td>
                                                    <select name="ano"><?php echo $hoUtils->getOptionsSelectAno($registro['ano'] ?: date('Y')); ?></select>
                                                    <select name="mes"><?php echo $hoUtils->getOptionsSelectMes($registro['mes'], false); ?></select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Valor:</th>
                                                <td><input class="perc20 text-right" name="valor" id="valor" value="<?php echo $hoUtils->numberFormat($registro['valor'], 0, 2, ".", ""); ?>"></td>
                                            </tr>
                                            <tr>
                                                <th colspan="2">
                                                    <div align="center">
                                                        <input name="id" id="id" hidden value="<?php echo $registro['idOrcCli']; ?>">
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
                                <h3 class="icon chart">Lista de orçamentos cadastrados</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width="5%">Ações</th>
                                            <th>CPF / CNPJ</th>
                                            <th>Razão social</th>
                                            <th>Ano</th>
                                            <th>Mês</th>
                                            <th>Valor</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                            foreach ($lista as $linha)
                                                echo
                                                    "<tr>
                                                        <td><a class='icon-pen-alt-fill tooltip' href='?id=$linha[idOrcCli]' title='Editar'></a></td>
                                                        <td>" . $hoUtils->cnpjCpfFormat($linha['cgc']) . "</td>
                                                        <td>$linha[razaoSocial]</td>
                                                        <td>$linha[ano]</td>
                                                        <td>" . $hoUtils->monthName($linha['mes']) . "</td>
                                                        <td class='text-right'>" . $hoUtils->numberFormat($linha['valor']) . "</td>
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