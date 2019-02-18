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
        <title>BID - Registros Bradesco inconsistentes</title>

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
            $post = filter_input_array(INPUT_POST);

            $dtIni = $post['dtIni'] ?: date('Y-m-01');
            $dtFin = $post['dtFin'] ?: date('Y-m-d');

            /**
             * Busca todos os registros importados do Bradesco que não possuem correspondência nos importados da Pamcard (contratos, adiantamentos e pagamentos)
             */
            $registros = $dbcSQL->select(
                "SELECT
                    e.lancamento descricao, dbo.DateFormat103(e.data) data, e.debito, e.credito, e.numBradesco, e.documento, e.nomeArquivo
                FROM pcd.extratobrd e
                WHERE
                    e.numBradesco IS NOT NULL AND e.data BETWEEN '$dtIni' AND '$dtFin' AND
                    NOT EXISTS (SELECT 1 FROM pcd.contrato c WHERE e.numBradesco = c.numBradesco) AND
                    NOT EXISTS (SELECT 1 FROM pcd.adiantamento a WHERE e.numBradesco = a.numBradesco) AND
                    NOT EXISTS (SELECT 1 FROM pcd.pagamento p WHERE e.numBradesco = p.numBradesco)",
                null, "e.data, e.numBradesco, e.lancamento");

            foreach ($registros as $registro){
                $linhaRegistros .=
                    "<tr>
                        <td>$registro[descricao]</td>
                        <td>$registro[data]</td>
                        <td class='text-right'>" . $hoUtils->numberFormat($registro['debito']) . "</td>
                        <td class='text-right'>" . $hoUtils->numberFormat($registro['credito']) . "</td>
                        <td class='text-center'>$registro[numBradesco]</td>
                        <td class='text-center'>$registro[documento]</td>
                        <td>$registro[nomeArquivo]</td>
                    </tr>";
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
                    <div class="grid-24">
                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-info"></span>
                                <h3>Relação de registros inconsistentes nos arquivos de conciliação do Bradesco</h3>
                            </div>
                            <div style="padding: 10px">
                                &#9679; Abaixo constarão todos os registros importados de arquivos do Bradesco que não possuem correspondente nos arquivos da Pamcard <br />
                                &#9679; Os registros estarão ordenados por data, Nº autorização e descrição <br />
                                &#9679; O motivo mais provável para as inconsistências listadas é a falta de importação do arquivo Pamcard correspodente <br />
                            </div>
                        </div>

                        <form method="post" action="#" enctype="multipart/form-data" id="frmBrdInconsistente">
                            <div class="field">
                                <label>Selecione o período:&nbsp;</label>
                                <input type="date" id="dtIni" name="dtIni" style="width: 130px" maxlength="10" value="<?php echo $dtIni; ?>" />
                                <input type="date" id="dtFin" name="dtFin" style="width: 130px" maxlength="10" value="<?php echo $dtFin; ?>" />
                                &nbsp;
                                <button class="btn btn-primary"><span class="icon-magnifying-glass"></span>Buscar</button>
                            </div>
                        </form>
                        <br />

                        <table class="table table-bordered table-striped" style="<?php if (!$registros) echo "display: none;" ?>">
                            <thead>
                                <tr>
                                    <th>Descrição</th>
                                    <th width="8%">Data</th>
                                    <th width="8%">Débito</th>
                                    <th width="8%">Crédito</th>
                                    <th width="10%">Nº autorização</th>
                                    <th width="10%">Nº documento</th>
                                    <th>Nome do arquivo importado</th>
                                </tr>
                            </thead>
                            <tbody><?php echo $linhaRegistros; ?></tbody>
                        </table>

                        <!-- Se não encontrou nenhum registro inconsistente exibimos um box com mensagem apenas para não deixar a tela vazia -->
                        <div class="notify notify-error" style="<?php if ($registros) echo "display: none;" ?>">
                            <b>&#9679; Nenhum registro inconsistente para o período indicado</b>
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
                                    <li><a href="javascript:;">Edit Profile</a></li>
                                    <li><a href="javascript:;">Suspend Account</a></li>
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