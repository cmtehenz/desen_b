<?php
    namespace Modulos\Comercial\Sublists;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();
    $dbcDB2  = new \Library\Scripts\scriptDB2();

    $_SESSION['modulo'] = basename(str_replace("sublists", "", dirname($_SERVER['PHP_SELF'])));
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Análise de placa</title>

        <meta charset="utf-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>

        <script type="text/javascript">
            $(document).ready(function () { $("#ano").change(function () { $("#frmAnalisePlaca").submit(); }); });
        </script>
    </head>
    <body>
        <?php
            include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
            include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_db2_bino.php';
            include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptdb2.php';

            $post = filter_input_array(INPUT_POST);
            $get  = filter_input_array(INPUT_GET);

            $placa = $post['placa'] ?: $get['placa'];
            $ano   = $post['ano']   ?: $get['ano'];

            $idVeiculo = $get['id'];

            $sql_mes = mssql_query("SELECT id_mes, descricao FROM mes");

            while ($dados_mes = mssql_fetch_array($sql_mes)){
                //TITULO DO GRAFICO
                $meses = $meses . '<th>' . substr($dados_mes[1], 0, 3) . '</th>';

                //RELACAO DE PLACA
                $sql_placa = "SELECT SUM(FRETEP) FROM
        (SELECT SUM(CT.VALFPESOSICMS) AS FRETEP
        FROM CT
            JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
            JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
                JOIN MODELO ON (HVEICULO.CODEMODE = MODELO.CODEMODE)
        WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$dados_mes[0] AND HVEICULO.PLACA='$placa'

        UNION
        SELECT SUM(CARRETO.VALFRETE) AS FRETEP
        FROM CARRETO
            JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
            JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
                JOIN MODELO ON (HVEICULO.CODEMODE = MODELO.CODEMODE)
        WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$dados_mes[0] AND HVEICULO.PLACA='$placa'

        UNION
        SELECT SUM(NOTAFAT.VLR_TOTAL) AS FRETEP
        FROM NOTAFAT
            JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTAFAT.ID_HVEICULO)
            JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTAFAT.ID_HVEICULO)
                JOIN MODELO ON (HVEICULO.CODEMODE = MODELO.CODEMODE)
        WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$dados_mes[0] AND HVEICULO.PLACA='$placa'
        AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

        UNION
        SELECT SUM(NOTASER.VALTOTSERV) AS FRETEP
        FROM NOTASER
            JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
            JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
            JOIN MODELO ON (HVEICULO.CODEMODE = MODELO.CODEMODE)
        WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$dados_mes[0] AND HVEICULO.PLACA='$placa'
        )
            ";
                $db2_placa = db2_exec($hDbcDB2, $sql_placa);
                $dados_placa = db2_fetch_array($db2_placa);
                if ($dados_placa[0] == 0){
                    $grfFaturamento = $grfFaturamento . '<td>0</td>';
                }
                if ($dados_placa[0] != 0){
                    $realizadoDiario = number_format(str_replace(',', '.', $dados_placa[0]), 0, ',', '');
                    $grfFaturamento = $grfFaturamento . '<td>' . $realizadoDiario . '</td>';
                }


                //MEDIAS DO ANO
                //lista o km rodado
                $sqlRodado = "SELECT SUM(ABAST.ODOMETRO-ODOANTER), SUM(ABAST.LITROSABAST)
                        FROM ABAST
                        JOIN VEICULO ON (VEICULO.ID_VEICULO=ABAST.ID_VEICULO)
                            WHERE MONTH(ABAST.DATAABAST)=$dados_mes[0] and YEAR(ABAST.DATAABAST)=$ano and VEICULO.PLACA='$placa'";
                $db2_rodado = db2_exec($hDbcDB2, $sqlRodado);
                $dados_rodado = db2_fetch_array($db2_rodado);
                $kml = str_replace('.', '', number_format($dados_rodado[0] / $dados_rodado[1], 2));
                $km = $dados_rodado[0];

                if ($kml == 0){
                    $grfMedia = $grfMedia . '<td>0</td>';
                }
                if ($kml != 0){
                    $grfMedia = $grfMedia . '<td>' . $kml . '</td>';
                }

                if ($km == 0){
                    $grfKmRodado = $grfKmRodado . '<td>0</td>';
                }
                if ($km != 0){
                    $grfKmRodado = $grfKmRodado . '<td>' . $km . '</td>';
                }
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
                        <form action="#" method="post" id="frmAnalisePlaca" name="frmAnalisePlaca">
                            <div class="field">
                                <label>Selecione o período:</label>
                                <select id="ano" name="ano"><?php echo $hoUtils->getOptionsSelectAno($ano); ?></select>
                                &nbsp;
                                <label>Placa:&nbsp;</label>
                                <input type="text" id="placa" name="placa" value="<?php echo $placa; ?>">
                                &nbsp;
                                <button class="btn btn-primary"><span class="icon-magnifying-glass"></span>Buscar</button>
                            </div>
                        </form>
                        <br />

                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-steering-wheel"></span>
                                <h3 class="icon chart">Análise de veículo para o mês atual - <?php echo $placa; ?></h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped ">
                                    <thead>
                                        <tr>
                                            <th>Frete total</th>
                                            <th>Frete peso</th>
                                            <th>Km rodado</th>
                                            <th>Km / L</th>
                                            <th>Despesa</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class='gradeA'>
                                            <td><?php echo number_format(freteTotalAnoMesPlaca(date('Y'), date('m'), $placa, $imob), 2, ',', '.'); ?></td>
                                            <td><?php echo number_format(fretePesoAnoMesPlaca(date('Y'), date('m'), $placa, $imob), 2, ',', '.'); ?></td>
                                            <td><?php echo kmRodadoVeiculo(date('Y'), date('m'), $idVeiculo); ?></td>
                                            <td><?php echo number_format(mediaVeiculo(date('Y'), date('m'), $idVeiculo), 2, ',', '.'); ?></td>
                                            <td><?php echo number_format(custoAnoMesPlaca(date('Y'), date('m'), $placa), 2, ',', '.'); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div> <!-- .widget-content -->
                        </div> <!-- .widget -->

                        <div class="widget">
                            <div class="widget-header">
                                <span class="icon-chart"></span>
                                <h3 class="icon chart">Análise gráfica para o ano de <?php echo $ano; ?></h3>
                            </div>

                            <div class="widget-content">
                                <table class="stats" data-chart-type="line" data-chart-colors="">
                                    <thead>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <?php echo $meses; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th>Faturamento (frete peso)</th>
                                            <?php echo $grfFaturamento; ?>
                                        </tr>
                                    </tbody>
                                </table>
                                <br />
                                <table class="stats" data-chart-type="line" data-chart-colors="">
                                    <thead>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <?php echo $meses; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th>Km rodado</th>
                                            <?php echo $grfKmRodado; ?>
                                        </tr>
                                    </tbody>
                                </table>
                                <br />
                                <table class="stats" data-chart-type="line" data-chart-colors="">
                                    <thead>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <?php echo $meses; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th>Média realizada</th>
                                            <?php echo $grfMedia; ?>
                                        </tr>
                                    </tbody>
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