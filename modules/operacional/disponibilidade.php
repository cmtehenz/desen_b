<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();
    $dbcDB2  = new \Library\Scripts\scriptDB2();

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Disponibilidade de veículos</title>

        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>

        <script>
            $(document).ready(function () {
                var rdGroup = "<?php echo $_POST['rdGroup']; ?>";
                var rdOrder = "<?php echo $_POST['rdOrder']; ?>";

                if (rdGroup != "E") $("#rdNenhum").prop("checked", true); else $("#rdEstado").prop("checked", true);
                if (rdOrder != "D") $("#rdPlaca").prop("checked", true); else $("#rdData").prop("checked", true);

                $("a.editObs").click(function (e) {
                    e.preventDefault();

                    var placa = $(this).attr('id');

                    var obs = prompt("Insira a nova observação");

                    if (obs == null) return;

                    updateObs(placa, obs);
                });

                function updateObs(placa, obs) {
                    var json = {
                        placa: placa,
                        obs: obs
                    };

                    $.getJSON('../../library/ajax/obsMonitoramento.ajax.php', json, function (retorno) {
                        if (retorno)
                            $("#obs_" + placa).text(obs);
                        else
                            alert("Erro ao atualizar observação, contate o admnistrador do sistema");
                    });
                }
            });
        </script>
    </head>
    <body>
        <?php
            date_default_timezone_set('America/sao_paulo');
            $dataAtual = new \DateTime(date('Y-m-d H:i:00'));

            $resOperLog = $dbcSQL->simpleSelect("usuario", "operLog", array($dbcSQL->whereParam("id_usuario", $_SESSION['idUsuario'])));

            $idOperLog = $_POST['operLog'] ?: $resOperLog ?: 7;
            $rdGroup   = $_POST['rdGroup'] ?: "N";
            $rdOrder   = $_POST['rdOrder'] ?: "P";

            $totalVeiculos = 0;
            $totalD = 0; // Disponíveis
            $totalM = 0; // Menutenção
            $totalV = 0; // Viagem
            $totalZ = 0; // Viagem vazia
            $totalVM = 0; // Viagem / manutenção
            $totalZM = 0; // Viagem vazia / manutenção

            $groupEstados = array();

            $params = array($dbcSQL->whereParam("m.operLog", $idOperLog));

            $orderBy = ($rdGroup == "E" ? "m.ufDes, " : "") . ($rdOrder == "P" ? "m.placa" : "m.dataStatus DESC");

            $listaVeiculos = $dbcSQL->select(
                "SELECT
                    m.placa, m.statusBipe, m.statusOS, m.obs, m.dataStatus,
                    CASE m.statusBipe WHEN 'D' THEN '' ELSE m.ufOri + ' - ' + m.origem END origem, m.ufDes + ' - ' + m.destino destino,
                    m.ufDes, m.motorista, m.ignicao, (CONVERT(VARCHAR, m.idMacro) + ' - ' + m.macro) macro, m.ponto, m.latitude, m.longitude
                 FROM monitoramento m", $params, $orderBy);

            foreach ($listaVeiculos as $veiculo){
                $placa      = $veiculo['placa'];
                $statusBipe = $veiculo['statusBipe'];
                $statusOS   = $veiculo['statusOS'];

                $status = $hoUtils->getStatusMonitoramento($statusBipe, $statusOS);

                $ultMov = date_diff($dataAtual, new \DateTime($veiculo['dataStatus']))->format('%ad %Hhrs %im');
                $dtMov  = date('d/m/Y', strtotime($veiculo['dataStatus']));

                switch ($statusBipe){
                    case "D": $color = "#82CD9B";
                        if ($statusOS == "S") $totalM++;
                        else $totalD++;
                        break;
                    case "V": $color = "#FFFF66";
                        if ($statusOS == "S") $totalVM++;
                        else $totalV++;
                        break;
                    case "Z": $color = "#B2B2FF";
                        if ($statusOS == "S") $totalZM++;
                        else $totalZ++;
                        break;
                }

                if ($statusOS == "S") $color = "#FF4D4D";

                /** Monta link para o Google Maps utilizando as coordenadas gravas do monitoramento */
                $latitude  = $hoUtils->numberFormat($veiculo['latitude'] , 0, 4, ".", ".");
                $longitude = $hoUtils->numberFormat($veiculo['longitude'], 0, 4, ".", ".");

                $linkPonto = ($latitude <> 0 && $longitude <> 0)
                    ? "<a href='http://maps.google.com/maps?z=8&t=m&q=loc:$latitude+$longitude' target='_blank'>$veiculo[ponto]</a>"
                    : $veiculo['ponto'];

                $ignicao = $veiculo['ignicao'] ? "Ligado" : "Desligado";
                $infos   = $status . " - " . $ignicao . " - " . $veiculo['motorista'];

                /** Layout com as informações do rastreador - 2 linhas para cada veículo */
                $linhaTabela .=
                    "<tr class='gradeA'>
                        <td style='background-color: $color; font-weight: bold;'><a href='ultima_viagem.php?placa=$placa'>$placa</a></td>
                        <td colspan='5'>$infos</td>
                    </tr>
                    <tr class='gradeA'>
                        <td>&nbsp;</td>
                        <td>$veiculo[origem]</td>
                        <td>$veiculo[destino]</td>
                        <td>$dtMov - $ultMov</td>
                        <td>$linkPonto</td>
                        <td>
                            <a href='' class='editObs tooltip' id='$placa' title='Editar'><i class='icon-pen-alt-fill'></i></a>&nbsp
                            <span id='obs_$placa'>" . ($veiculo['obs']) . "</span>
                        </td>
                    </tr>";

                /**
                 * Tratativas para o layout agrupado por estado de entrega
                 */
                if ($rdGroup == "E"){
                    // Se encontrou um novo estado na listagem, adicionamos o atual ao array e reinicializamos a variável
                    if ($estado['uf'] != $veiculo['ufDes']){
                        if ($estado) array_push($groupEstados, $estado);

                        $estado = array("uf" => $veiculo['ufDes']);
                    }

                    $estado['linhas'] .= $linhaTabela; $linhaTabela = "";
                }

                $totalVeiculos++;
            }

            if ($estado) array_push($groupEstados, $estado);

            $linkImpressao = "sublists/impressaoDisponibilidade.php?idOperLog=$idOperLog&rdGroup=$rdGroup&rdOrder=$rdOrder";

            $thead =
                "<tr>
                    <th width='7%'>&nbsp;</th>
                    <th width='10%'>Origem</th>
                    <th width='10%'>Último destino</th>
                    <th width='18%'>Última mov.</th>
                    <th width='20%'>Posição</th>
                    <th width='20%'>Observação</th>
                </tr>";
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
                        <form action="#" method="post" name="formDisp" class="form uniformForm">
                            <div class="field-group control-group inline" style="float: left; margin-right: 10px;">
                                <label>Operador logístico</label>

                                <div class="field">
                                    <select id="operLog" name="operLog" onchange="document.formDisp.submit()">
                                        <?php
                                            $listaOperLog = $dbcDB2->select("SELECT IDOPERLOG id, NOME FROM OPERLOG WHERE BLOQUEADO = 'N'");

                                            foreach ($listaOperLog as $operLog)
                                                echo
                                                    "<option value='$operLog[ID]' " . ($operLog[ID] == $idOperLog ? "selected" : "") . ">"
                                                        . utf8_encode($operLog['NOME']) .
                                                    "</option>";
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="field-group control-group inline" style="float: left; margin-right: 10px;">
                                <label>Agrupamento</label>

                                <div class="field">
                                    <input type="radio" name="rdGroup" id="rdNenhum" value="N" onchange="document.formDisp.submit()"
                                        <?php if ($rdGroup != "E") echo "checked"; ?> />
                                    <label for="rdNenhum">Nenhum</label>
                                </div>

                                <div class="field">
                                    <input type="radio" name="rdGroup" id="rdEstado" value="E" onchange="document.formDisp.submit()"
                                           <?php if ($rdGroup == "E") echo "checked"; ?> />
                                    <label for="rdEstado">Estado de entrega / destino</label>
                                </div>
                            </div>

                            <div class="field-group control-group inline" style="float: left; margin-right: 10px;">
                                <label>Ordenação</label>

                                <div class="field">
                                    <input type="radio" name="rdOrder" id="rdPlaca" value="P" onchange="document.formDisp.submit()"
                                        <?php if ($rdOrder != "D") echo "checked"; ?> />
                                    <label for="rdPlaca">Placa</label>
                                </div>

                                <div class="field">
                                    <input type="radio" name="rdOrder" id="rdData" value="D" onchange="document.formDisp.submit()"
                                           <?php if ($rdOrder == "D") echo "checked"; ?> />
                                    <label for="rdData">Última movimentação</label>
                                </div>
                            </div>
                        </form>

                        <div style="float: right;">
                            <a href="<?php echo $linkImpressao; ?>" target="_blank"><button class="btn btn-black btn-large">Imprimir</button></a>
                        </div>

                        <div class="widget widget-table" style="<?php if ($rdGroup == "E") echo "display: none"; ?>">
                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Disponibilidade de veículos</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered">
                                    <thead> <?php echo $thead; ?> </thead>
                                    <tbody> <?php echo $linhaTabela; ?> </tbody>
                                </table>
                            </div> <!-- .widget-content -->
                        </div> <!-- .widget -->

                        <div class="widget widget-table" style="<?php if ($rdGroup != "E") echo "display: none"; ?>">
                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Disponibilidade de veículos por estado de entrega</h3>
                            </div>
                        </div>

                        <?php
                            /**
                             * Layout diferenciado para o agrupamento por estado de entrega
                             */
                            foreach ($groupEstados as $estado){
                                echo
                                    "<div class='widget widget-table'>
                                        <div class='widget-header'>
                                            <span class='icon-arrow-right'></span>
                                        <h3 class='icon chart'>" . $estado['uf'] . " - " . $hoUtils->nomeUf($estado['uf']) . "</h3>
                                    </div>";

                                echo
                                    "<table class='table table-bordered'>
                                        <thead>$thead</thead>
                                        <tbody>$estado[linhas]</tbody>
                                    </table>";

                                echo "</div>";
                            }
                        ?>

                        <div class="box plain">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Empresa</th>
                                        <th>Total de veículos</th>
                                        <th>Disponíveis</th>
                                        <th>Manutenção</th>
                                        <th>Viagem</th>
                                        <th>Viagem / manutenção</th>
                                        <th>Viagem vazia</th>
                                        <th>Viagem vazia / manutenção</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="odd gradeX">
                                        <td width="20%"><?php echo $_SESSION['nomeEmpresa']; ?></td>
                                        <td align='right'><?php echo $totalVeiculos; ?></td>
                                        <td align='right'><?php echo $totalD; ?></td>
                                        <td align='right'><?php echo $totalM; ?></td>
                                        <td align='right'><?php echo $totalV; ?></td>
                                        <td align='right'><?php echo $totalVM; ?></td>
                                        <td align='right'><?php echo $totalZ; ?></td>
                                        <td align='right'><?php echo $totalZM; ?></td>
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