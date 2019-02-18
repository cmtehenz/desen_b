<?php
    namespace Modulos\Operacional;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    use Library\Classes\KeyDictionary as KeyDictionary;

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();
    $dbcDB2  = new \Library\Scripts\scriptDB2();

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Controle de revisões</title>

        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>

        <script type="text/javascript">
            $(document).ready(function(){
                $("#operacao").change(function(){ $("#frmConRevisao").submit(); });
                $("#vendidos").change(function(){ $("#frmConRevisao").submit(); });
            });
        </script>
    </head>
    <body>
        <?php
            date_default_timezone_set('America/sao_paulo');

            $sql = "SELECT r.placa, r.operacao, r.kmUltima, (r.kmUltima + r.periodo) kmProxima, dbo.DateFormat103(r.dtUltima) dtUltima, r.vendido, r.parado FROM revisao r";

            $params = array();

            /** Filtra por operação apenas se não estiver fixo no cadastro do usuário */
            $usuOperacao = $dbcSQL->simpleSelect("usuario", "operacao", array( $dbcSQL->whereParam("id_usuario", $_SESSION['idUsuario']) ));

            $paramOperacao = $usuOperacao ?: $_POST['operacao'];

            if ($paramOperacao) array_push($params, $dbcSQL->whereParam("operacao", $paramOperacao));

            /** Filtrar apenas os veículos não vendidos e não parados caso o parâmetro não esteja marcado */
            $paramVendidos = $_POST['vendidos'];

            if (!$paramVendidos){
                array_push($params, $dbcSQL->whereParam("vendido", 0));
                array_push($params, $dbcSQL->whereParam("parado", 0));
            }

            $veiculos = $dbcSQL->select($sql, $params, "r.placa");

            foreach ($veiculos as $veiculo){
                $infoKm = $dbcDB2->kmAtual($veiculo['placa']);

                $diferenca = $veiculo['kmProxima'] - $infoKm['ATUAL'];

                /** Se o veículo foi vendido / parado, a situação e o bgcolor do mesmo destacam essa informação. Caso contrário, faz o switch para atribuir a situação atual do mesmo */
                if ($veiculo['vendido'] || $veiculo['parado']){
                    $situacao = $veiculo['vendido'] ? "Vendido" : "Parado";
                    $bgcolor = "#B2B2FF";
                    $totVendido++;
                }
                else
                {
                    switch (true){
                        case $diferenca <= 2000 && $diferenca >= -2000:
                            $situacao = "Em período";
                            $bgcolor = "#FFFF66";
                            $totPeriodo++;
                            break;

                        case $diferenca < -2000:
                            $situacao = "Estouro";
                            $bgcolor = "#FF4D4D";
                            $totEstouro++;
                            break;

                        default:
                            $situacao = "Ok";
                            $bgcolor = "#82CD9B";
                            $totOk++;
                            break;
                    }
                }

                /** Compara o odômetro atual com o anterior para indicar se há uma discrepância muito grande entre eles, destacando possível informação incorreta */
                $bgKm = (($infoKm['ATUAL'] - $infoKm['ANTERIOR']) > 1700) ? "#FFFF66" : "";

                $linhaTabela .=
                    "<tr>
                        <td>$veiculo[placa]</td>
                        <td>" . KeyDictionary::valueOperacao($veiculo['operacao']) . "</td>
                        <td class='text-right' style='background-color: $bgKm;'>" . $hoUtils->numberFormat($infoKm['ATUAL'], 0, 0) . "</td>
                        <td class='text-right'>" . $hoUtils->numberFormat($veiculo['kmUltima'], 0, 0) . "</td>
                        <td class='text-right'>" . $hoUtils->numberFormat($veiculo['kmProxima'], 0, 0) . "</td>
                        <td class='text-right'>" . $hoUtils->numberFormat($diferenca, 0, 0) . "</td>
                        <td style='background-color: $bgcolor;'>$situacao</td>
                        <td>$veiculo[dtUltima]</td>
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
                        <form action="#" method="post" id="frmConRevisao" class="form uniformForm">
                            <div class="field-group control-group" style="margin-bottom: 30px; margin-top: -10px; <?php if ($usuOperacao) echo "display: none;" ?>">
                                <label>Operação</label>

                                <div class="field">
                                    <select id="operacao" name="operacao">
                                        <?php echo $hoUtils->getOptionsSelect(KeyDictionary::arrayOperacao(), $_POST['operacao'], 'Todas'); ?>
                                    </select>
                                </div>
                            </div>

                            <div class="field-group control-group" style="margin-bottom: 30px; margin-top: -10px;">
                                <input type="checkbox" name="vendidos" id="vendidos" <?php echo $paramVendidos ? "checked" : ""; ?> /> Exibir veículos vendidos e parados
                            </div>
                        </form>

                        <div class="widget widget-table" style="margin-top: -15px;">
                            <div class="widget-header">
                                <span class="icon-wrench"></span>
                                <h3 class="icon chart">Controle de revisão por placa</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th width="6%">Placa</th>
                                            <th width="9%">Operação</th>
                                            <th width="15%">Km atual</th>
                                            <th width="15%">Km última</th>
                                            <th width="15%">Km próxima</th>
                                            <th width="15%">Diferença</th>
                                            <th width="15%">Situação</th>
                                            <th width="10%">Realizado</th>
                                        </tr>
                                    </thead>
                                    <tbody> <?php echo $linhaTabela; ?> </tbody>
                                </table>
                            </div> <!-- .widget-content -->
                        </div> <!-- .widget -->

                        <div class="box plain">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th width="20%">Total de veículos</th>
                                        <th width="20%">Ok</th>
                                        <th width="20%">Em período</th>
                                        <th width="20%">Estouro</th>
                                        <th width="20%">Vendido / parado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="odd gradeX">
                                        <td align='right'><?php echo count($veiculos); ?></td>
                                        <td align='right'><?php echo $totOk ?: 0; ?></td>
                                        <td align='right'><?php echo $totPeriodo ?: 0; ?></td>
                                        <td align='right'><?php echo $totEstouro ?: 0; ?></td>
                                        <td align='right'><?php echo $totVendido ?: 0; ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div> <!-- .box -->
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
        </div>
    </body>
</html>