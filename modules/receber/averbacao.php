<?php
    namespace Modulos\Receber;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();
    $dbcDB2  = new \Library\Scripts\scriptDB2();
    
    include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptSap.php';

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));

    date_default_timezone_set('America/sao_paulo');

    $post = filter_input_array(INPUT_POST);
    $get  = filter_input_array(INPUT_GET);

    $dtIni = $post['dtIni'] ?: $get['dtIni'] ?: date('Y-m-d');
    $dtFin = $post['dtFin'] ?: $get['dtFin'] ?: date('Y-m-d');
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Averbação de CT-e</title>

        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>

        <script type="text/javascript">
            $(document).ready(function(){
                $(".btn-upload").click(function(e){
                    e.preventDefault();

                    var cte = $(this).attr('cte');
                    var idFilial = $(this).attr('idFilial');

                    var json = { cte: cte, idFilial: idFilial };

                    $.getJSON('../../library/ajax/uploadWsAverbacao.ajax.php', json, function (retorno) {
                        console.log(retorno);

                        // Recarrega a página com os parâmetros que já estavam nela para busca
                        window.location.href = window.location.href.split("?")[0] + "?dtIni=<?php echo $dtIni; ?>&dtFin=<?php echo $dtFin; ?>";
                    });
                });
            });
        </script>
    </head>
    <body>
        <?php

            $sql = "SELECT
                        C.ID_CT id, F.SIGLA_FILIAL filial, C.NUMERO cte, L.RAZAO_SOCIAL cliente
                    FROM CT C
                    JOIN FILIAL   F ON C.ID_FILIAL = F.ID_FILIAL
                    JOIN HCLIENTE L ON C.IDHCLIENTE = L.IDHCLIENTE";

            $params = array($dbcDB2->whereParam("C.DATAEMISSAO", $dtIni, ">="), $dbcDB2->whereParam("C.DATAEMISSAO", $dtFin, "<="));

            $ctes = $dbcDB2->select($sql, $params, "F.SIGLA_FILIAL, C.NUMERO");

            foreach (listaCteAverbacao($dtIni, $dtFin) as $cte){
                $numero = $cte['IDFILIAL'] . '-' . $cte['CTE'] . '-' . $cte['ID'];

                $paramsLog = array($dbcSQL->whereParam("sigla", $cte['IDFILIAL']), $dbcSQL->whereParam("numero", $cte['CTE']));

                $status = $dbcSQL->simpleSelect("logAverbacao", "retorno", $paramsLog, "idLogAverbacao DESC") ?: "Não enviado";

                $button = ($status == "Não enviado") ? "<button class='btn btn-tertiary btn-upload' cte='$numero'><span class='icon-upload'></span>Upload</button>" : "";

                $linhaTabela .=
                    "<tr class='gradeA'>
                        <td>$cte[FILIAL]</td>
                        <td class='text-center'>$cte[CTE]</td>
                        <td>$cte[CLIENTE]</td>
                        <td>$status</td>
                        <td>$button</td>
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
                        <form action="#" method="post" id="frmAverbaCte">
                            <div class="field">
                                <label>Selecione o período:&nbsp;</label>
                                <input type="date" id="dtIni" name="dtIni" style="width: 130px" maxlength="10" value="<?php echo $dtIni; ?>" />
                                <input type="date" id="dtFin" name="dtFin" style="width: 130px" maxlength="10" value="<?php echo $dtFin; ?>" />
                                &nbsp;
                                <button class="btn btn-primary"><span class="icon-magnifying-glass"></span>Buscar</button>
                            </div>
                        </form>

                        <div class="grid-24 box notify-info" style="margin: 10px 0px 15px 0px; padding: 10px; width: 98%;">
                            <b>&#9679; Informação:</b> Ao clicar na ação <b>Upload</b>, será feito envio do CT-e para averbação
                        </div>

                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-document-alt-stroke"></span>
                                <h3 class="icon chart">CT-es emitidos no período</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width="15%">Filial</th>
                                            <th width="6%">CT-e</th>
                                            <th>Cliente</th>
                                            <th width="20%">Status</th>
                                            <th width="10%">Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody><?php echo $linhaTabela; ?></tbody>
                                </table>
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