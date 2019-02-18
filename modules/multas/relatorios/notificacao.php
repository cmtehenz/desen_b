<?php
    namespace Modulos\Multas\Relatorios;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcDB2  = new \Library\Scripts\scriptDB2();
    $dbcSQL  = new \Library\Scripts\scriptSQL();

    $_SESSION['modulo'] = basename(dirname(dirname($_SERVER['PHP_SELF'])));
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Relatório de notificações</title>

        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
    </head>
    <body>
        <?php
            date_default_timezone_set('America/sao_paulo');

            /** Filtros para busca */
            $post = filter_input_array(INPUT_POST);

            $dtIni = $post['dtIni'] ?: date('Y-m-01');
            $dtFin = $post['dtFin'] ?: date('Y-m-d');

            $placa = $post['placa'];

            $params = array($dbcSQL->whereParam("n.dtInfracao", $dtIni, ">="), $dbcSQL->whereParam("n.dtInfracao", $dtFin, "<="));

            if ($placa) array_push($params, $dbcSQL->whereParam("n.placa", $placa));

            $sql =
                "SELECT
                    n.placa, n.numAuto, dbo.DateFormat103(n.dtInfracao) data, dbo.DateFormat103(n.dtRecurso) recurso, i.descricao infracao
                FROM mlt.notificacao n
                JOIN mlt.infracao i ON n.idInfracao = i.idInfracao
                WHERE NOT EXISTS (SELECT 1 FROM mlt.multa m WHERE n.idNotificacao = m.idNotificacao)";

            $lista = $dbcSQL->select($sql, $params, "n.placa, n.dtInfracao", null, false);

            foreach ($lista as $registro){
                $motorista = $dbcDB2->motoristaVeiculo($registro['placa'], $registro['data']);

                $linkAuto = "<a href='../cadastros/multa.php?numAuto=$registro[numAuto]' target='_blank'>$registro[numAuto]</a>";

                $linhaTabela .=
                    "<tr class='gradeA'>
                        <td>$registro[placa]</td>
                        <td>$motorista</td>
                        <td>" . substr($registro['infracao'], 0, 60) . "</td>
                        <td>$registro[data]</td>
                        <td>$registro[recurso]</td>
                        <td>$linkAuto</td>
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

            <!-- Sidebar -->
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
                                <h3>Informações</h3>
                            </div>
                            <div style="padding: 10px">
                                &#9679; Abaixo constarão todas as notificações digitados no BID que ainda não possuem multa vinculada. <br />
                                &#9679; Todas as informações básicas serão listadas, para maiores detalhes clique no Nº do auto e uma nova janela se abrirá
                                com o cadastro da multa. <br />
                                &#9679; As informações do motorista são encontradas no sistema GetOne, com base na placa do veículo e data da infração.
                                Em caso de dúvidas ou problemas, consulte o departamento de TI. <br />
                            </div>
                        </div>

                        <form action="#" method="post" id="frmRelMulta" name="frmRelMulta" class="form uniformForm" enctype="multipart/form-data">
                            <div class="field">
                                <label>Período de busca pela data da infração:&nbsp;</label>
                                <input type="date" id="dtIni" name="dtIni" style="width: 130px" maxlength="10" value="<?php echo $dtIni; ?>" />
                                <input type="date" id="dtFin" name="dtFin" style="width: 130px" maxlength="10" value="<?php echo $dtFin; ?>" />
                                &nbsp;

                                <label>Placa:&nbsp;</label>
                                <input type="text" id="placa" name="placa" style="width: 80px" maxlength="7" value="<?php echo $placa; ?>" />
                                &nbsp;

                                <button class="btn btn-primary"><span class="icon-magnifying-glass"></span>Buscar</button>
                            </div>
                        </form>

                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-info"></span>
                                <h3 class="icon chart">Relatório de notificações</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width="7%">Placa</th>
                                            <th>Motorista</th>
                                            <th>Infração</th>
                                            <th width="9%">Data</th>
                                            <th width="12%">Prazo p/ recurso</th>
                                            <th width="10%">Nº auto</th>
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