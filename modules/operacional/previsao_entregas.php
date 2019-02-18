<?php
    namespace Modulos\Operacional;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcDB2  = new \Library\Scripts\scriptDB2();
    $dbcSQL  = new \Library\Scripts\scriptSQL();

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));

    $post = filter_input_array(INPUT_POST);

    $rdGroup = $post['rdGroup'] ?: "F";
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Previsão de entregas</title>

        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>

        <script type="text/javascript">
            $(document).ready(function(){
                $("#filial, #rdNenhum, #rdFilial").change(function(){ $("#frmPrevEnt").submit(); });

                var rdGroup = "<?php echo $rdGroup; ?>";

                if (rdGroup != "F") $("#rdNenhum").prop("checked", true); else $("#rdFilial").prop("checked", true);
            });
        </script>
    </head>
    <body>
        <?php
            date_default_timezone_set('America/sao_paulo');

            $groupFilial = array();

            /** Busca a lista de CT-es com BIPE sem baixar (em aberto) e suas datas de agendamento */
            $filtro = ($post['filial'] ? " AND F.ID_FILIAL = $post[filial] " : "");

            $sql =
                "SELECT
                    F.SIGLA_FILIAL filial, F.NOME_FILIAL nomeFilial, C.NUMERO cte, B.NUMBIPE bipe, V.PLACA,
                    (D.UF || ' - ' || TRIM(D.NOME_CIDADE) || ' - ' || L.RAZAO_SOCIAL) destino,
                    C.DATAAGENDA data, C.HORAAGENDA hora, C.MINAGENDA min,
                    C.DATAPREVCHEG dataPrev, C.HORAPREVCHEG horaPrev, C.MINPREVCHEG minPrev,
                    (SELECT COUNT(T.ID_CT) FROM CT T WHERE T.IDCADBIPE = C.IDCADBIPE) numEntregasBipe, C.SEQENTBIPE
                FROM CT C
                JOIN FILIAL   F ON C.ID_FILIAL = F.ID_FILIAL
                JOIN HVEICULO V ON C.ID_HVEICULO = V.ID_HVEICULO
                JOIN HCLIENTE L ON C.IDHCLIENTEDEST = L.IDHCLIENTE
                JOIN CADBIPE  B ON C.IDCADBIPE = B.IDCADBIPE
                JOIN CIDADE   D ON C.ID_CIDADEENT = D.ID_CIDADE
                WHERE
                    B.DATABAIXA IS NULL
                    $filtro";

            /**
             * Order by com CASE para que ordene corretamente pela data / hora completa que irá mostrar na tela (do CT-e ou de previsão).
             * Se o agrupamento for por filial, colocamos a Sigla como primeiro campo de ordenação, senão ele virá após a data e hora
             */
            if ($rdGroup == "F") $orderBy = " F.SIGLA_FILIAL, ";

            $orderBy .= "
                    COALESCE(C.DATAAGENDA, C.DATAPREVCHEG),
                    (CASE C.DATAAGENDA WHEN NULL THEN C.HORAPREVCHEG ELSE C.HORAAGENDA END),
                    (CASE C.DATAAGENDA WHEN NULL THEN C.MINPREVCHEG ELSE C.MINAGENDA END),
                    F.SIGLA_FILIAL, C.NUMERO";

            $lista = $dbcDB2->select($sql, null, $orderBy);

            $atrasos = 0; $emdia = 0; $pendencias = 0;

            $arFilial = array();

            foreach ($lista as $cte){
                $data = $cte['DATA'] ?: $cte['DATAPREV'];

                if (!$data) $agendamento = null;
                else {
                    /** Se possuir a data de agendamento, completamos com as informações do mesmo, senão, usará a previsão do BIPE */
                    if ($cte['DATA']){
                        $hora = $cte['HORA'];
                        $min  = $cte['MIN'];
                    } else {
                        $hora = $cte['HORAPREV'];
                        $min  = $cte['MINPREV'];
                    }

                    $hora = str_pad($hora, 2, '0', STR_PAD_LEFT);
                    $min  = str_pad($min, 2, '0', STR_PAD_LEFT);

                    $agendamento = $data . ' ' . $hora . ':' . $min;
                }

                $entregas = $cte['NUMENTREGASBIPE'];

                if ((strtotime($agendamento) < strtotime(date('Y-m-d H:i:s')))){
                    if ($entregas > 1 && $entregas != $cte['SEQENTBIPE']){
                        $situacao = "Entrega fracionada";
                        $bgcolor = "#FFFF66";

                        $pendencias++;
                    } else {
                        $situacao = "Atrasado";
                        $bgcolor = "#FF4D4D";

                        $atrasos++;
                    }
                } else {
                    $situacao = "Em dia";
                    $bgcolor = "#82CD9B";

                    $emdia++;
                }

                $placa = $cte['PLACA'];

                /** Busca a localização atual do veículo no banco do BID */
                $filtroPlaca = array($dbcSQL->whereParam("m.placa", $placa));

                $dbcSQL->select("SELECT m.latitude, m.longitude FROM monitoramento m", $filtroPlaca);

                $rastreamento = $dbcSQL->getResultRow();

                /** Monta link para o Google Maps utilizando as coordenadas gravadas do monitoramento */
                $latitude  = $hoUtils->numberFormat($rastreamento['latitude'] , 0, 4, ".", ".");
                $longitude = $hoUtils->numberFormat($rastreamento['longitude'], 0, 4, ".", ".");

                $tdPlaca = ($latitude <> 0 && $longitude <> 0)
                    ? "<a href='http://maps.google.com/maps?z=8&t=m&q=loc:$latitude+$longitude' target='_blank'>$placa</a>"
                    : $placa;

                $linhaTabela .=
                    "<tr class='gradeA'>
                        <td>$cte[FILIAL]</td>
                        <td class='text-center'>$cte[CTE]</td>
                        <td class='text-center'><a href='ultima_viagem.php?placa=$placa'>$cte[BIPE]</a></td>
                        <td>$tdPlaca</td>
                        <td>" . $hoUtils->dateFormat($agendamento, 'Y-m-d H:i', 'd/m/Y H:i') . "</td>
                        <td>" . utf8_encode($cte['DESTINO']) . "</td>
                        <td style='background-color: $bgcolor;'>$situacao</td>
                    </tr>";

                if ($rdGroup == "F"){
                    if ($arFilial['sigla'] != $cte['FILIAL']){
                        if ($arFilial) array_push($groupFilial, $arFilial);

                        $arFilial = array("sigla" => $cte['FILIAL'], "nome" => $cte['NOMEFILIAL']);
                    }

                    $arFilial['linhas'] .= $linhaTabela; $linhaTabela = "";
                }
            }

            if ($arFilial) array_push($groupFilial, $arFilial);

            $thead =
                "<tr>
                    <th>Filial</th>
                    <th>CT-e</th>
                    <th>BIPE</th>
                    <th>Placa</th>
                    <th>Agendamento</th>
                    <th width='50%'>Destino</th>
                    <th>Situação</th>
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

            <!-- Sidebar -->
            <?php echo $hoUtils->menuUsuario($_SESSION['idUsuario']); ?>

            <div id="content">
                <div id="contentHeader">
                    <h1><?php echo $_SESSION['nomeEmpresa']; ?></h1>
                </div> <!-- #contentHeader -->

                <div class="container">
                    <div class="grid-24">
                        <div class="grid-7 box notify-error" style="float: left;">
                            <h2 style="text-align: center; font-weight: bold;"><?php echo $atrasos; ?> atrasos</h2>
                        </div>
                        <div class="grid-7 box notify-warning" style="float: left; margin-left: 40px;">
                            <h2 style="text-align: center; font-weight: bold;"><?php echo $pendencias; ?> entregas fracionadas</h2>
                        </div>
                        <div class="grid-7 box notify-success" style="float: right;">
                            <h2 style="text-align: center; font-weight: bold;"><?php echo $emdia; ?> em dia</h2>
                        </div>

                        <form action="#" method="post" id="frmPrevEnt" name="frmPrevEnt" class="form uniformForm">
                            <div class="field-group control-group inline" style="float: left; margin-right: 10px; margin-bottom: 10px;">
                                <label>Filial</label>
                                <div class="field">
                                    <select id="filial" name="filial">
                                        <?php
                                            $listaFiliais = $dbcDB2->select(
                                                    "SELECT
                                                        F.ID_FILIAL AS " . '"0"' . ", (TRIM(F.SIGLA_FILIAL) || ' - ' || F.NOME_FILIAL) AS  " . '"1"' . "
                                                    FROM FILIAL F
                                                    WHERE
                                                        F.ID_EMPRESA = 1 AND F.BLOQUEADO = 'N' AND F.CTE <> 'N'
                                                        AND EXISTS (SELECT 1 FROM CADBIPE B WHERE F.ID_FILIAL = B.ID_FILIAL AND B.DATABAIXA IS NULL)
                                                    ORDER BY F.SIGLA_FILIAL");

                                            echo $hoUtils->getOptionsSelect($listaFiliais, $post['filial'], "Todas", true);
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="field-group control-group inline" style="float: left; margin-right: 10px;">
                                <label>Agrupamento</label>

                                <div class="field">
                                    <input type="radio" name="rdGroup" id="rdNenhum" value="N" <?php if ($rdGroup != "F") echo "checked"; ?> />
                                    <label for="rdNenhum">Nenhum</label>
                                </div>

                                <div class="field">
                                    <input type="radio" name="rdGroup" id="rdFilial" value="F" <?php if ($rdGroup == "F") echo "checked"; ?> />
                                    <label for="rdFilial">Filial</label>
                                </div>
                            </div>
                        </form>

                        <div class="widget widget-table" style="<?php if ($rdGroup == "F") echo "display: none"; ?>">
                            <div class="widget-header">
                                <span class="icon-calendar-alt-stroke"></span>
                                <h3 class="icon chart">Agendamento das entregas</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead><?php echo $thead; ?></thead>
                                    <tbody><?php echo $linhaTabela; ?></tbody>
                                </table>
                            </div> <!-- .widget-content -->
                        </div> <!-- .widget -->

                        <div class="widget widget-table" style="<?php if ($rdGroup != "F") echo "display: none"; ?>">
                            <div class="widget-header">
                                <span class="icon-calendar-alt-stroke"></span>
                                <h3 class="icon chart">Agendamento das entregas por filial</h3>
                            </div>
                        </div>

                        <?php
                            /** Layout diferenciado para o agrupamento por filial */
                            foreach ($groupFilial as $filial){
                                echo
                                    "<div class='widget widget-table'>
                                        <div class='widget-header'>
                                            <span class='icon-arrow-right'></span>
                                        <h3 class='icon chart'>" . $filial['sigla'] . " - " . $filial['nome'] . "</h3>
                                    </div>";

                                echo
                                    "<table class='table table-bordered'>
                                        <thead>$thead</thead>
                                        <tbody>$filial[linhas]</tbody>
                                    </table>";

                                echo "</div>";
                            }
                        ?>
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