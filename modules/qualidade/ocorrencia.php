<?php
    namespace Modulos\Qualidade;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();
    $dbcDB2  = new \Library\Scripts\scriptDB2();

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));

    date_default_timezone_set('America/sao_paulo');

    /** Busca dados do registro para consulta caso seja informado ID via GET */
    $params = filter_input_array(INPUT_GET);

    $registro = $dbcSQL->selectTopOne(
        "SELECT o.*, CONVERT(CHAR(10), o.dtOcorrencia, 120) data, CONVERT(CHAR(5), o.dtOcorrencia, 108) hora FROM ocorrencia o",
        array($dbcSQL->whereParam("o.idOcorrencia", $params['id']))
    );

    /** Se é uma consulta (ID passado por GET), desabilitamos os campos para não permitir edição */
    $disabled = $params['id'] ? 'disabled' : '';

    /** Monta o <select> de clientes */
    $listaClientes = $dbcDB2->listaClientes();

    $selectClientes = "<option>Nenhum</option>";

    foreach ($listaClientes as $cliente){
        $selectClientes .=
            "<option value='" . trim($cliente['CGC']) . "' " . ((trim($cliente['CGC']) == $registro['cliente']) ? "selected" : "") . ">"
                . $hoUtils->cnpjCpfFormat($cliente['CGC']) . " - " . utf8_encode($cliente['RAZAO']) .
            "</option>";
    }
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Inclusão de ocorrência</title>

        <meta charset="utf-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
        <script src="http://cdn.jsdelivr.net/webshim/1.12.4/extras/modernizr-custom.js"></script>
        <script src="http://cdn.jsdelivr.net/webshim/1.12.4/polyfiller.js"></script>
        <script>
            webshims.setOptions('waitReady', false);
            webshims.setOptions('forms-ext', {types: 'date'});
            webshims.polyfill('forms forms-ext');
        </script>

        <script type="text/javascript">
            $(document).ready(function () {
                var rdAcionou = "<?php echo $registro['acionouMonitoramento']; ?>";

                if (rdAcionou != 1) $("#rdNao").prop("checked", true); else $("#rdSim").prop("checked", true);

                var rdClassif = "<?php echo $registro['classificacao']; ?>";

                if (rdClassif != "S") $("#rdQualidade").prop("checked", true); else $("#rdSeguranca").prop("checked", true);
            });
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
                        <form method="post" action="post/gravaOcorrencia.php" enctype="multipart/form-data">
                            <div class="widget widget-table">
                                <div class="widget-header">
                                    <span class="icon-document-alt-stroke"></span>
                                    <h3 class="icon chart">Inclusão de ocorrência</h3>
                                </div>

                                <div class="widget-content">
                                    <table class="table table-bordered table-striped cadastroBID">
                                        <thead>
                                            <tr>
                                                <th width="15%">CPF motorista:</th>
                                                <td width="85%">
                                                    <input class="perc20" name="cpf" id="cpf" maxlength="11" value="<?php echo $registro['cpf']; ?>" <?php echo $disabled; ?> />
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Placa:</th>
                                                <td><select name="idVeiculo" <?php echo $disabled; ?>><?php echo $dbcDB2->listaPlacas($registro['idVeiculo']); ?></select></td>
                                            </tr>
                                            <tr>
                                                <th>Tipo de ocorrência:</th>
                                                <td>
                                                    <select name="tipo" <?php echo $disabled; ?>>
                                                        <?php
                                                            echo $hoUtils->getOptionsSelect(
                                                                    $dbcSQL->select("SELECT id '0', descricao '1' FROM tipoOcorrencia", null, "descricao"),
                                                                    $registro['idTipoOcorrencia']);
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Data:</th>
                                                <td>
                                                    <input type="date" name="data" id="data" style="width: 140px;" value="<?php echo $registro['data']; ?>" <?php echo $disabled; ?>>
                                                    <input type="time" name="hora" id="hora" style="width: 100px;" value="<?php echo $registro['hora']; ?>" <?php echo $disabled; ?>>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Cliente:</th>
                                                <td><select name="cliente" <?php echo $disabled; ?>><?php echo $selectClientes; ?></select></td>
                                            </tr>
                                            <tr>
                                                <th>Observação:</th>
                                                <td><input name="obs" id="obs" maxlength="150" style="width: 100%;" value="<?php echo $registro['observacao']; ?>" <?php echo $disabled; ?> /></td>
                                            </tr>
                                            <tr>
                                                <th>Acionou monit.:</th>
                                                <td>
                                                    <div class="field" style="float: left;">
                                                        <input type="radio" name="rdAcMonit" id="rdSim" value="1" <?php echo $disabled; ?> />
                                                        <label for="rdSim">Sim</label>
                                                    </div>

                                                    <div class="field" style="float: left;">
                                                        <input type="radio" name="rdAcMonit" id="rdNao" value="0" <?php echo $disabled; ?> />
                                                        <label for="rdNao">Não</label>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Classificação:</th>
                                                <td>
                                                    <div class="field" style="float: left;">
                                                        <input type="radio" name="rdClassificacao" id="rdQualidade" value="Q" <?php echo $disabled; ?> />
                                                        <label for="rdQualidade">Qualidade</label>
                                                    </div>

                                                    <div class="field" style="float: left;">
                                                        <input type="radio" name="rdClassificacao" id="rdSeguranca" value="S" <?php echo $disabled; ?> />
                                                        <label for="rdSeguranca">Segurança</label>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Nº BIPE:</th>
                                                <td><input name="bipe" id="bipe" maxlength="20" class="perc20" value="<?php echo $registro['bipe']; ?>" <?php echo $disabled; ?> /></td>
                                            </tr>
                                            <tr>
                                                <th colspan="2" style="<?php if ($disabled) echo "display: none;"; ?>">
                                                    <div align="center">
                                                         <button class="btn btn-success" <?php echo $disabled; ?>><span class="icon-check"></span>Salvar</button>
                                                    </div>
                                                </th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div> <!-- .widget-content -->
                            </div> <!-- .widget -->
                        </form>
                    </div>
                </div>
            </div>

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