<?php
    namespace Modulos\Operacional;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/old/scriptSap.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcDB2  = new \Library\Scripts\scriptDB2();

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Disponibilidade de cargas</title>

        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <style type="text/css">
            .infobox-cargas { margin: 0px 0px 15px 0px; padding: 30px 0px 10px 0px; min-height: 40px; width: 100%; text-align: center; font-size: 25px; }
        </style>

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
    </head>
    <body>
        <?php
            date_default_timezone_set('America/sao_paulo');
            $dtIni = date('Y-m-01');
            $dtFin = date('Y-m-d');

            $groupDisp = array(); $groupProg = array();

            /** Apura os totais do topo, filtrando por status */
            $programada = quantidadeCargasProgramada();
            //$programada = $dbcDB2->qtdCargas(null, null, "R");
            $pendente = quantidadeCargasPendente();
            //$pendente   = $dbcDB2->qtdCargas(null, null, "P");
            $andamento  = quantidadeCargasEmAndamento();
            $finalizada = $dbcDB2->qtdCargas($dtIni, $dtFin, "F");

            $sql =
                "SELECT
                    (F.SIGLA_FILIAL || ' - ' || O.NUMEMB) ordem, O.PESO, O.DATACOL coleta, R.DESCRICAO rota, C.RAZAO_SOCIAL cliente, V.PLACA,
                    F.SIGLA_FILIAL sigla, F.NOME_FILIAL nomeFilial
                FROM ORDEMEMB O
                JOIN FILIAL   F ON O.ID_FILIAL = F.ID_FILIAL
                JOIN CLIENTE  C ON O.ID_DEST = C.ID_CLIENTE
                JOIN ROTA     R ON O.ID_ROTA = R.ID_ROTA
                LEFT JOIN HVEICULO V ON O.ID_HVEICULO = V.ID_HVEICULO";

            /** Busca a lista de ordens de embarque disponíveis no mês */
            $listaDisp = $dbcDB2->select($sql, array($dbcDB2->whereParam("O.STATUS", "P")), "F.SIGLA_FILIAL, O.NUMEMB");

            $filial = array();

            foreach ($listaDisp as $carga){
                $linhaDisponiveis .=
                    "<tr class='gradeA'>
                        <td class='text-right'>$carga[ORDEM]</td>
                        <td class='text-right'>" . $hoUtils->numberFormat($carga['PESO']) . "</td>
                        <td>" . $hoUtils->dateFormat($carga['COLETA'], 'Y-m-d', 'd/m/Y') . "</td>
                        <td>" . utf8_encode($carga['ROTA']) . "</td>
                        <td>" . utf8_encode($carga['CLIENTE']) . "</td>
                    </tr>";

                if ($filial['sigla'] != $carga['SIGLA']){
                    if ($filial) array_push($groupDisp, $filial);

                    $filial = array("sigla" => $carga['SIGLA'], "nome" => $carga['NOMEFILIAL']);
                }

                $filial['linhas'] .= $linhaDisponiveis; $linhaDisponiveis = "";
            }

            if ($filial) array_push($groupDisp, $filial);

            $theadDisp =
                "<tr>
                    <th>Nº ordem</th>
                    <th>Peso</th>
                    <th>Coleta</th>
                    <th>Rota</th>
                    <th width='40%'>Cliente</th>
                </tr>";

            /** Busca a lista de ordens de embarque programadas hoje */
            $listaProg = $dbcDB2->select($sql, array($dbcDB2->whereParam("O.STATUS", "R")), "F.SIGLA_FILIAL, O.NUMEMB");

            $filial = array();

            foreach ($listaProg as $carga){
                $linhaProgramadas .=
                    "<tr class='gradeA'>
                        <td class='text-right'>$carga[ORDEM]</td>
                        <td>$carga[PLACA]</td>
                        <td class='text-right'>" . $hoUtils->numberFormat($carga['PESO']) . "</td>
                        <td>" . $hoUtils->dateFormat($carga['COLETA'], 'Y-m-d', 'd/m/Y') . "</td>
                        <td>" . utf8_encode($carga['ROTA']) . "</td>
                        <td>" . utf8_encode($carga['CLIENTE']) . "</td>
                    </tr>";

                if ($filial['sigla'] != $carga['SIGLA']){
                    if ($filial) array_push($groupProg, $filial);

                    $filial = array("sigla" => $carga['SIGLA'], "nome" => $carga['NOMEFILIAL']);
                }

                $filial['linhas'] .= $linhaProgramadas; $linhaProgramadas = "";
            }

            if ($filial) array_push($groupProg, $filial);

            $theadProg =
                "<tr>
                    <th>Nº ordem</th>
                    <th>Placa</th>
                    <th>Peso</th>
                    <th>Coleta</th>
                    <th>Rota</th>
                    <th width='30%'>Cliente</th>
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
                        <div class="widget widget-plain" style="text-align: center;">
                            <div class="dashboard_report first defaultState">
                                <div class="pad">
                                    <span class="value"><?php echo $programada; ?></span> Programada
                                </div> <!-- .pad -->
                            </div>

                            <div class="dashboard_report defaultState">
                                <div class="pad">
                                    <span class="value"><?php echo $andamento; ?></span> Em andamento
                                </div> <!-- .pad -->
                            </div>                            

                            <div class="dashboard_report activeState last">
                                <div class="pad">
                                    <span class="value"><?php echo $pendente; ?></span> Pendente
                                </div> <!-- .pad -->
                            </div>
                        </div>

                        <div class="infobox-cargas box notify-success">
                            <b>&#x21E9; DISPONÍVEIS &#x21E9;</b>
                        </div>

                        <?php
                            foreach (listaFilialCargasPendente() as $filial){
                                echo
                                    "<div class='widget widget-table'>
                                        <div class='widget-header'>
                                            <span class='icon-box'></span>
                                        <h3 class='icon chart'>" . $filial['FILIAL']."</h3>
                                    </div>";
                                //lista de cargas
                                foreach (listaCargasPendenteFilial($filial['ID']) as $cargas){
                                    echo
                                    "<table class='table table-bordered'>
                                        <thead>
                                        <tr>
                                            <th>Nº ordem</th>
                                            <th>Peso</th>
                                            <th>Coleta</th>
                                            <th width='30%'>Rota</th>
                                            <th width='40%'>Cliente</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr class='gradeA'>
                                            <td class='text-right'>$cargas[DOCNUM]</td>
                                            <td class='text-right'>" . $hoUtils->numberFormat($cargas['PESO']) . "</td>
                                            <td>$cargas[COLETA]</td>
                                            <td>" . utf8_encode($cargas['REMETENTECIDADE']) .' X ' . utf8_encode($cargas['DESTINATARIOCIDADE']). "</td>
                                            <td>" . utf8_encode($cargas['CLIENTE']) . "</td>
                                        </tr>
                                        </tbody>
                                    </table>";
                                }
                                echo "</div>";
                            }
                        
                            
                        ?>

                        <div class="infobox-cargas box notify-info">
                            <b>&#x21E9; PROGRAMADAS &#x21E9;</b>
                        </div>

                        <?php
                            foreach (listaFilialCargasProgramada() as $filial){
                                echo
                                    "<div class='widget widget-table'>
                                        <div class='widget-header'>
                                            <span class='icon-box'></span>
                                        <h3 class='icon chart'>" . $filial['FILIAL']."</h3>
                                    </div>";
                                //lista de cargas
                                foreach (listaCargasProgramadaFilial($filial['ID']) as $cargas){
                                    echo
                                    "<table class='table table-bordered'>
                                        <thead>
                                        <tr>
                                            <th>Nº ordem</th>
                                            <th>PLACA</th>
                                            <th>Peso</th>
                                            <th>Coleta</th>
                                            <th width='30%'>Rota</th>
                                            <th width='40%'>Cliente</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr class='gradeA'>
                                            <td class='text-right'>$cargas[DOCNUM]</td>
                                            <td class='text-right'>$cargas[PLACA]</td>
                                            <td class='text-right'>" . $hoUtils->numberFormat($cargas['PESO']) . "</td>
                                            <td>$cargas[COLETA]</td>
                                            <td>" . utf8_encode($cargas['REMETENTECIDADE']) .' X ' . utf8_encode($cargas['DESTINATARIOCIDADE']). "</td>
                                            <td>" . utf8_encode($cargas['CLIENTE']) . "</td>
                                        </tr>
                                        </tbody>
                                    </table>";
                                }
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