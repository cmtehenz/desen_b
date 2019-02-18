<?php
    namespace Modulos\Comercial\Sublists;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();

    $_SESSION['modulo'] = basename(str_replace("sublists", "", dirname($_SERVER['PHP_SELF'])));

    $get = filter_input_array(INPUT_GET);

    $contrato = $get['contrato'];

    $title = ($contrato == "F" ? "Frota" : ($contrato == "A" ? "Agregado" : "Terceiro"));
    
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptSap.php';
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Receita <?php echo strtolower($title); ?></title>

        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
        <script src="<?php echo $hoUtils->getURLDestino("js/sorttable.js"); ?>"></script>

        <script type="text/javascript">
            $(document).ready(function(){ $("#imob").change(function(){ $("#frmRecContrato").submit(); }); });
        </script>

        <style type="text/css">
            /** Cria classes CSS personalizadas para ocultar ou exibir algumas das colunas de acordo com o tipo de contrato */
            .frt { <?php if ($contrato != "F") echo "display: none;"; ?> } /* Colunas apenas para frotas */
            .agr { <?php if ($contrato == "T") echo "display: none;"; ?> } /* Colunas para frotas ou agregados */
        </style>
    </head>
    <body>
        <?php
            
            date_default_timezone_set('America/sao_paulo');

            $ano = $get['ano'] ?: date('Y');
            $mes = $get['mes'] ?: date('m');

            $imob = $_POST['imob'] ?: 0;

            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);
            
            /** ************************************************
            //BUSCA RECEITA FROTA              *
            /************************************************* */
            //calculo do total do frete peso sem icms.
            
            $receitaFretePesoSicmsTotal = receitaFretePeso($ano, $mes, null, $contrato);
            //calculo do total do frete peso com icms
            
            $receitaFretePesoCicmsTotal = receitaFretePesoCIcms($ano, $mes, null, $contrato);
                       
                                  
            
            foreach (listaVeiculosFaturamento($ano, $mes, null, $contrato) as $veiculo){
                $idVeiculo = 1;
                $placa     = $veiculo['PLACA'];
                $conjunto  = $veiculo['CONJUNTO'];
                $receitaFretePesoSicms = $veiculo['FRETEPESO'];
                $receitaFretePesoCicms = $veiculo['FRETEPESOCICMS'];
                
                $bgColor = "";
                $prcMeta = 0;
                if ($contrato != "T"){
                    $valMeta  = $dbcSQL->metaConjunto($conjunto);
                    //$prcMeta  = ($receitaFretePesoSicms / $valMeta) * 100;
                    $prcMeta = ($receitaFretePesoSicms *100)/$valMeta;
                    
                    //$metaDia    = $valMeta / $daysInMonth;
                    //$valMetaDia = $metaDia * date('d');
                    //$prcMetaDia = ($receitaFretePesoSicms / $valMetaDia) * 100;

                    switch (true){
                        case $prcMeta < 75:
                            $bgColor = "#FF4D4D"; break;

                        case $prcMeta >= 75 && $prcMeta < 100:
                            $bgColor = "#FFFF66"; break;

                        default: $bgColor = "#82CD9B"; break;
                    }
                }
                
                         
                $linkAnalise = "analise_placa.php?ano=$ano&placa=$placa&id=$idVeiculo";

                $linhaTabela .=
                    "<tr>
                        <td style='background-color: $bgColor;'>$veiculo[PLACA]</td>
                        <td>$conjunto</td>
                        <td ><a href='meta.php?conj=$conjunto'>" . $hoUtils->numberFormat($valMeta) . "</a></td>
                        <td ><a href='documentosSAP.php?placa=$placa&ano=$ano&mes=$mes'>" . number_format($receitaFretePesoSicms, 2, ',', '.') . "</a></td>
                        <td >" . $hoUtils->numberFormat($prcMeta, 0, 0) . "</td>
                        <td >" .  number_format($receitaFretePesoCicms, 2, ',', '.') .  "</td>
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
                                <span class="icon-layers"></span>
                                <h3 class="icon">Cumulativo</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Total de veículos</th>
                                            <th>Frete peso</th>
                                            <th>Frete Peso C/ ICMS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="odd gradeX">
                                            <td align='right'><?php echo count(listaVeiculosFaturamento($ano, $mes, null, $contrato)); ?></td>
                                            <td align='right'><?php echo number_format($receitaFretePesoSicmsTotal, 2, ',', '.'); ?></td>
                                            <td align='right'><?php echo number_format($receitaFretePesoCicmsTotal, 2, ',', '.'); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="agr grid-11 box notify-info" style="margin: 10px 0px 15px 0px; padding: 10px; width: 47%; float: left;">
                            <b>&#9679; Meta (%) = Frete peso / (meta diária * dia do mês)</b>
                        </div>
                        <div class="agr grid-11 box notify-info" style="margin: 10px 0px 15px 0px; padding: 10px; width: 47%; float: right;">
                            <b>&#9679; Meta diária = Meta do conjunto / quantidade de dias no mês</b>
                        </div>

                        <!-- Legenda para cores das metas -->
                        <div class="agr grid-7 box notify-success" style="margin: 0px 3% 15px 0px; padding: 10px; width: 28%; float: left;">
                            <b>&#9679; Placas em verde = Meta até hoje atingida</b>
                        </div>
                        <div class="agr grid-7 box notify-warning" style="margin-bottom: 15px; padding: 10px; width: 30%; float: left;">
                            <b>&#9679; Placas em amarelo = Meta até hoje entre 75% e 100%</b>
                        </div>
                        <div class="agr grid-7 box notify-error" style="margin-bottom: 15px; padding: 10px; width: 30%; float: right;">
                            <b>&#9679; Placas em vermelho = Meta até hoje abaixo de 75%</b>
                        </div>

                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Faturamento por tipo de contrato - <?php echo $title; ?></h3>
                            </div>

                            <div class="widget-content">
                                <table class="sortable table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Placa</th>
                                            <th width="28%">Conjunto</th>
                                            <th >Meta</th>
                                            <th width="15%">Frete peso</th>
                                            <th width="7%">Meta (%)</th>
                                            <th width="15%">Frete peso c/ ICMS</th>
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