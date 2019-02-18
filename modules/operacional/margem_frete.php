<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL = new \Library\Scripts\scriptSQL();

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));
?>
<!doctype html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Margens de frete</title>

        <meta charset="utf-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
    </head>

    <body>
        <?php
            include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
            include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_db2_bino.php';
            include $_SERVER['DOCUMENT_ROOT'] . '/old/funcoes.php';

            /*             * *******************************
             *   VARIAVEIS                   *
             * ****************************** */
            date_default_timezone_set('America/sao_paulo');
            $wanted_week = date('W');
            $abrev_mes = date('M');
            $nome_mes = date('F');
            $dia = date('d');
            $ano = date('Y');
            $anoAnterior = $ano - 1;
            $mes_atual = date('m');
            $totalFreteAgregado = 0;
            $totalPAgoAgregado = 0;
            $totalFreteTerceiro = 0;
            $totalPagoTerceiro = 0;

            if (isset($_POST['ano'])){
                $ano = $_POST['ano'];
                $anoAnterior = $ano - 1;
            }
            if (isset($_POST['mes'])){
                $mes_atual = $_POST['mes'];
            }

            $sqlMes = mssql_query("SELECT * FROM mes");
            while ($dadosMes = mssql_fetch_array($sqlMes)){
                $listaMes = $listaMes . "<option value='$dadosMes[0]'>$dadosMes[1]</option>";
            }

            $sqlAno = mssql_query("SELECT * FROM ano ORDER BY ano DESC");
            while ($dadosAno = mssql_fetch_array($sqlAno)){
                $listaAno = $listaAno . "<option value='$dadosAno[0]'>$dadosAno[0]</option>";
            }

            $sqlMesSelecionado = mssql_query("SELECT * FROM mes WHERE id_mes=$mes_atual");
            $dadosMesSelecionado = mssql_fetch_array($sqlMesSelecionado);

            /*             * ********************************* */
            /*   RELACAO DE FILIAIS
             * /****************************** */
            $sql_relacaoFiliais = "SELECT FILIAL.ID_FILIAL, FILIAL.SIGLA_FILIAL
                    FROM CADBIPE
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CADBIPE.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = FILIAL.IDCTCUSTO)
                    WHERE YEAR(CADBIPE.DATAEMIS)=$ano AND MONTH(CADBIPE.DATAEMIS)=$mes_atual
                        AND CADBIPE.STAFT != 'F'
                    GROUP BY FILIAL.ID_FILIAL, FILIAL.SIGLA_FILIAL";
            $db2_relacaoFiliais = db2_exec($hDbcDB2, $sql_relacaoFiliais);
            /*             * *********************************************************************************** */
            /*     FIM RELACAO FILIAIS
              /******************************************************* */
            while ($dados_relacaoFiliais = db2_fetch_array($db2_relacaoFiliais)){

                $sql_filialAgregado = "SELECT FILIAL.ID_FILIAL, SUM(CADBIPE.VALFRETEPAGOTOT), SUM(CADBIPE.VALFPESOPAGO)
                    FROM CADBIPE
                    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CADBIPE.ID_HVEICULO)
                    JOIN VEICEMP ON (VEICEMP.ID_VEICULO = HVEICULO.ID_VEICULO)
                    JOIN HPROPRIET ON (HPROPRIET.IDHPROPRIET = HVEICULO.IDHPROPRIET)
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CADBIPE.ID_FILIAL)
                    WHERE YEAR(CADBIPE.DATAEMIS)=$ano AND MONTH(CADBIPE.DATAEMIS)=$mes_atual AND FILIAL.ID_FILIAL = $dados_relacaoFiliais[0]
                        AND CADBIPE.STAFT = 'A'
                    GROUP BY FILIAL.ID_FILIAL
                    ";
                $db2_filialAgregado = db2_exec($hDbcDB2, $sql_filialAgregado);
                $dados_filialAgregado = db2_fetch_array($db2_filialAgregado);
                $dados_filialAgregado[2] = str_replace(',', '.', $dados_filialAgregado[2]);
                $dados_filialAgregado[1] = str_replace(',', '.', $dados_filialAgregado[1]);

                $sql_filialTerceiro = "SELECT FILIAL.ID_FILIAL, SUM(CADBIPE.VALFRETEPAGOTOT), SUM(CADBIPE.VALFPESOPAGO)
                    FROM CADBIPE
                    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CADBIPE.ID_HVEICULO)
                    JOIN VEICEMP ON (VEICEMP.ID_VEICULO = HVEICULO.ID_VEICULO)
                    JOIN HPROPRIET ON (HPROPRIET.IDHPROPRIET = HVEICULO.IDHPROPRIET)
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CADBIPE.ID_FILIAL)
                    WHERE YEAR(CADBIPE.DATAEMIS)=$ano AND MONTH(CADBIPE.DATAEMIS)=$mes_atual AND FILIAL.ID_FILIAL = $dados_relacaoFiliais[0]
                        AND CADBIPE.STAFT = 'T'
                    GROUP BY FILIAL.ID_FILIAL
                    ";
                $db2_filialTerceiro = db2_exec($hDbcDB2, $sql_filialTerceiro);
                $dados_filialTerceiro = db2_fetch_array($db2_filialTerceiro);
                $dados_filialTerceiro[1] = str_replace(',', '.', $dados_filialTerceiro[1]);
                $dados_filialTerceiro[2] = str_replace(',', '.', $dados_filialTerceiro[2]);


                $dados_relacaoFiliais[2] = htmlentities($dados_relacaoFiliais[2]);
                $margemAgregado = number_format(($dados_filialAgregado[1] / $dados_filialAgregado[2]) * 100, 0, ',', '.');
                $margemTerceiro = number_format(($dados_filialTerceiro[1] / $dados_filialTerceiro[2]) * 100, 0, ',', '.');
                $linhaTabela = $linhaTabela . "<tr class='gradeA' bgcolor='F3F781'>
                                    <td>$dados_relacaoFiliais[1]</td>
                                    <td>" . number_format($dados_filialAgregado[2], 2, ',', '.') . "</td>
                                    <td>" . number_format($dados_filialAgregado[1], 2, ',', '.') . "</td>
                                    <td>$margemAgregado %</td>
                                    <td>" . number_format($dados_filialTerceiro[2], 2, ',', '.') . "</td>
                                    <td>" . number_format($dados_filialTerceiro[1], 2, ',', '.') . "</td>
                                    <td>$margemTerceiro %</td>
                                </tr>";

                $totalFreteAgregado = $totalFreteAgregado + $dados_filialAgregado[2];
                $totalPAgoAgregado = $totalPAgoAgregado + $dados_filialAgregado[1];
                $totalFreteTerceiro = $totalFreteTerceiro + $dados_filialTerceiro[2];
                $totalPagoTerceiro = $totalPagoTerceiro + $dados_filialTerceiro[1];
            }

            /*             * *********************************************
             *   GERA NUMEROS PARA GRAFICO ANUAL
             * ******************************************** */
            $sql_mesGrafico = mssql_query("SELECT * FROM mes");
            while ($dadosMesGrafico = mssql_fetch_array($sql_mesGrafico)){
                $tituloGraf = $tituloGraf . '<th>' . substr($dadosMesGrafico[1], 0, 3) . '</th>';
                $sql_filialAgregadoGrafico = "SELECT SUM(CADBIPE.VALFRETEPAGOTOT), SUM(CADBIPE.VALFPESOPAGO)
                    FROM CADBIPE
                    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CADBIPE.ID_HVEICULO)
                    JOIN VEICEMP ON (VEICEMP.ID_VEICULO = HVEICULO.ID_VEICULO)
                    JOIN HPROPRIET ON (HPROPRIET.IDHPROPRIET = HVEICULO.IDHPROPRIET)
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CADBIPE.ID_FILIAL)
                    WHERE YEAR(CADBIPE.DATAEMIS)=$ano AND MONTH(CADBIPE.DATAEMIS)=$dadosMesGrafico[0]
                        AND CADBIPE.STAFT = 'A'
                    ";
                $db2_filialAgregadoGrafico = db2_exec($hDbcDB2, $sql_filialAgregadoGrafico);
                $dados_filialAgregadoGrafico = db2_fetch_array($db2_filialAgregadoGrafico);
                $margemAgregadoGrafico = number_format(($dados_filialAgregadoGrafico[0] / $dados_filialAgregadoGrafico[1]) * 100, 0, ',', '.') . "<br>";
                $graf_realizado = $graf_realizado . '<td>' . $margemAgregadoGrafico . '</td>';

                $sql_filialAgregadoGrafico = "SELECT SUM(CADBIPE.VALFRETEPAGOTOT), SUM(CADBIPE.VALFPESOPAGO)
                    FROM CADBIPE
                    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CADBIPE.ID_HVEICULO)
                    JOIN VEICEMP ON (VEICEMP.ID_VEICULO = HVEICULO.ID_VEICULO)
                    JOIN HPROPRIET ON (HPROPRIET.IDHPROPRIET = HVEICULO.IDHPROPRIET)
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CADBIPE.ID_FILIAL)
                    WHERE YEAR(CADBIPE.DATAEMIS)=$anoAnterior AND MONTH(CADBIPE.DATAEMIS)=$dadosMesGrafico[0]
                        AND CADBIPE.STAFT = 'A'
                    ";
                $db2_filialAgregadoGrafico = db2_exec($hDbcDB2, $sql_filialAgregadoGrafico);
                $dados_filialAgregadoGrafico = db2_fetch_array($db2_filialAgregadoGrafico);
                $margemAgregadoGraficoAnt = number_format(($dados_filialAgregadoGrafico[0] / $dados_filialAgregadoGrafico[1]) * 100, 0, ',', '.') . "<br>";
                $graf_realizadoAnt = $graf_realizadoAnt . '<td>' . $margemAgregadoGraficoAnt . '</td>';

                //VALORES DA TABELA AGREGADO
                $linhaTabelaAnual = $linhaTabelaAnual . "<tr class='gradeA'>
                                    <td>$dadosMesGrafico[1]</td>
                                    <td>" . $margemAgregadoGraficoAnt . "</td>
                                    <td>" . $margemAgregadoGrafico . "</td>
                                </tr>";

                //VALORES TERCEIROS
                $sql_filialTerceiroGrafico = "SELECT SUM(CADBIPE.VALFRETEPAGOTOT), SUM(CADBIPE.VALFPESOPAGO)
                    FROM CADBIPE
                    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CADBIPE.ID_HVEICULO)
                    JOIN VEICEMP ON (VEICEMP.ID_VEICULO = HVEICULO.ID_VEICULO)
                    JOIN HPROPRIET ON (HPROPRIET.IDHPROPRIET = HVEICULO.IDHPROPRIET)
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CADBIPE.ID_FILIAL)
                    WHERE YEAR(CADBIPE.DATAEMIS)=$ano AND MONTH(CADBIPE.DATAEMIS)=$dadosMesGrafico[0]
                        AND CADBIPE.STAFT = 'T'
                    ";
                $db2_filialTerceiroGrafico = db2_exec($hDbcDB2, $sql_filialTerceiroGrafico);
                $dados_filialTerceiroGrafico = db2_fetch_array($db2_filialTerceiroGrafico);
                $margemTerceiroGrafico = number_format(($dados_filialTerceiroGrafico[0] / $dados_filialTerceiroGrafico[1]) * 100, 0, ',', '.') . "<br>";
                $graf_realizadoTerceiro = $graf_realizadoTerceiro . '<td>' . $margemTerceiroGrafico . '</td>';

                $sql_filialTerceiroGrafico = "SELECT SUM(CADBIPE.VALFRETEPAGOTOT), SUM(CADBIPE.VALFPESOPAGO)
                    FROM CADBIPE
                    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CADBIPE.ID_HVEICULO)
                    JOIN VEICEMP ON (VEICEMP.ID_VEICULO = HVEICULO.ID_VEICULO)
                    JOIN HPROPRIET ON (HPROPRIET.IDHPROPRIET = HVEICULO.IDHPROPRIET)
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CADBIPE.ID_FILIAL)
                    WHERE YEAR(CADBIPE.DATAEMIS)=$anoAnterior AND MONTH(CADBIPE.DATAEMIS)=$dadosMesGrafico[0]
                        AND CADBIPE.STAFT = 'T'
                    ";
                $db2_filialTerceiroGrafico = db2_exec($hDbcDB2, $sql_filialTerceiroGrafico);
                $dados_filialTerceiroGrafico = db2_fetch_array($db2_filialTerceiroGrafico);
                $margemTerceiroGraficoAnt = number_format(($dados_filialTerceiroGrafico[0] / $dados_filialTerceiroGrafico[1]) * 100, 0, ',', '.') . "<br>";
                $graf_realizadoAntTerceiro = $graf_realizadoAntTerceiro . '<td>' . $margemTerceiroGraficoAnt . '</td>';

                //VALORES DA TABELA TERCEIRO
                $linhaTabelaAnualTerceiro = $linhaTabelaAnualTerceiro . "<tr class='gradeA'>
                                    <td>$dadosMesGrafico[1]</td>
                                    <td>" . $margemTerceiroGraficoAnt . "</td>
                                    <td>" . $margemTerceiroGrafico . "</td>
                                </tr>";
            }
            $sql_AgregadoAnoAnt = "SELECT SUM(CADBIPE.VALFRETEPAGOTOT), SUM(CADBIPE.VALFPESOPAGO)
                    FROM CADBIPE
                    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CADBIPE.ID_HVEICULO)
                    JOIN VEICEMP ON (VEICEMP.ID_VEICULO = HVEICULO.ID_VEICULO)
                    JOIN HPROPRIET ON (HPROPRIET.IDHPROPRIET = HVEICULO.IDHPROPRIET)
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CADBIPE.ID_FILIAL)
                    WHERE YEAR(CADBIPE.DATAEMIS)=$anoAnterior AND CADBIPE.STAFT = 'A'
                    ";
            $db2_AgregadoAnoAnt = db2_exec($hDbcDB2, $sql_AgregadoAnoAnt);
            $dados_AgregadoAnoAnt = db2_fetch_array($db2_AgregadoAnoAnt);


            $sql_AgregadoAno = "SELECT SUM(CADBIPE.VALFRETEPAGOTOT), SUM(CADBIPE.VALFPESOPAGO)
                    FROM CADBIPE
                    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CADBIPE.ID_HVEICULO)
                    JOIN VEICEMP ON (VEICEMP.ID_VEICULO = HVEICULO.ID_VEICULO)
                    JOIN HPROPRIET ON (HPROPRIET.IDHPROPRIET = HVEICULO.IDHPROPRIET)
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CADBIPE.ID_FILIAL)
                    WHERE YEAR(CADBIPE.DATAEMIS)=$ano AND CADBIPE.STAFT = 'A'
                    ";
            $db2_AgregadoAno = db2_exec($hDbcDB2, $sql_AgregadoAno);
            $dados_AgregadoAno = db2_fetch_array($db2_AgregadoAno);
            $linhaTabelaAgregadoAno = $linhaTabelaAgregadoAno . "<tr class='gradeA'>
                                                        <td>" . number_format(($dados_AgregadoAnoAnt[0] / $dados_AgregadoAnoAnt[1]) * 100, 0, ',', '.') . "</td>
                                                        <td>" . number_format(($dados_AgregadoAno[0] / $dados_AgregadoAno[1]) * 100, 0, ',', '.') . "</td>
                                                    </tr>";


            $sql_TerceiroAnoAnt = "SELECT SUM(CADBIPE.VALFRETEPAGOTOT), SUM(CADBIPE.VALFPESOPAGO)
                    FROM CADBIPE
                    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CADBIPE.ID_HVEICULO)
                    JOIN VEICEMP ON (VEICEMP.ID_VEICULO = HVEICULO.ID_VEICULO)
                    JOIN HPROPRIET ON (HPROPRIET.IDHPROPRIET = HVEICULO.IDHPROPRIET)
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CADBIPE.ID_FILIAL)
                    WHERE YEAR(CADBIPE.DATAEMIS)=$anoAnterior AND CADBIPE.STAFT = 'T'
                    ";
            $db2_TerceiroAnoAnt = db2_exec($hDbcDB2, $sql_TerceiroAnoAnt);
            $dados_TerceiroAnoAnt = db2_fetch_array($db2_TerceiroAnoAnt);


            $sql_TerceiroAno = "SELECT SUM(CADBIPE.VALFRETEPAGOTOT), SUM(CADBIPE.VALFPESOPAGO)
                    FROM CADBIPE
                    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CADBIPE.ID_HVEICULO)
                    JOIN VEICEMP ON (VEICEMP.ID_VEICULO = HVEICULO.ID_VEICULO)
                    JOIN HPROPRIET ON (HPROPRIET.IDHPROPRIET = HVEICULO.IDHPROPRIET)
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CADBIPE.ID_FILIAL)
                    WHERE YEAR(CADBIPE.DATAEMIS)=$ano AND CADBIPE.STAFT = 'T'
                    ";
            $db2_TerceiroAno = db2_exec($hDbcDB2, $sql_TerceiroAno);
            $dados_TerceiroAno = db2_fetch_array($db2_TerceiroAno);
            $linhaTabelaTerceiroAno = $linhaTabelaTerceiroAno . "<tr class='gradeA'>
                                                        <td>" . number_format(($dados_TerceiroAnoAnt[0] / $dados_TerceiroAnoAnt[1]) * 100, 0, ',', '.') . "</td>
                                                        <td>" . number_format(($dados_TerceiroAno[0] / $dados_TerceiroAno[1]) * 100, 0, ',', '.') . "</td>
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
                        <form action="#" method="post">
                            <div class="field">
                                <label>Selecione o MES:</label>
                                <select id="mes" name="mes">
                                    <?php echo $listaMes; ?>
                                </select>

                                <label>Selecione o ANO:</label>
                                <select id="ano" name="ano">
                                    <?php echo $listaAno; ?>
                                </select>

                                <input type="submit" value="IR">
                                Periodo Selecionado: <?php echo $dadosMesSelecionado[1] . '/ ' . $ano; ?>.
                            </div>
                        </form>
                        <br>
                        <a href="sublists/margem.php?ano=<?php echo $ano; ?>&mes=<?php echo $mes_atual; ?>">Relacao Todos os Fretes</a>
                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">MARGEM FRETE AGRUPADO POR FILIAL</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>NUMERO</th>
                                            <th>FRETE AGREGADO</th>
                                            <th>PAGO AGREGADO</th>
                                            <th>MARGEM AGREGADO</th>
                                            <th>FRETE TERCEIRO</th>
                                            <th>PAGO TERCEIRO</th>
                                            <th>MARGEM TERCEIRO</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <?php
                                            echo $linhaTabela;
                                        ?>

                                    </tbody>
                                </table>
                            </div> <!-- .widget-content -->

                        </div> <!-- .widget -->

                        <div class="box plain">

                            <h3>TOTAL DA EMRPESA</h3>

                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>EMPRESA</th>
                                        <th>FRETE AGREGADO</th>
                                        <th>PAGO AGREGDO</th>
                                        <th>MARGEM AGREGADO</th>
                                        <th>FRETE TERCEIRO</th>
                                        <th>PAGO TERCEIRO</th>
                                        <th>MARGEM TERCEIRO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="odd gradeX">
                                        <td><?php echo $_SESSION['nomeEmpresa']; ?></td>
                                        <td><?php echo number_format($totalFreteAgregado, 2, ',', '.'); ?></td>
                                        <td><?php echo number_format($totalPAgoAgregado, 2, ',', '.'); ?></td>
                                        <td><?php echo number_format(($totalPAgoAgregado / $totalFreteAgregado) * 100, 0, ',', '.'); ?></td>
                                        <td><?php echo number_format($totalFreteTerceiro, 2, ',', '.'); ?></td>
                                        <td><?php echo number_format($totalPagoTerceiro, 2, ',', '.'); ?></td>
                                        <td><?php echo number_format(($totalPagoTerceiro / $totalFreteTerceiro) * 100, 0, ',', '.'); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div> <!-- .box -->

                        <div class="widget">

                            <div class="widget-header">
                                <span class="icon-chart"></span>
                                <h3 class="icon chart">Margem Agregado Anual</h3>
                            </div>

                            <div class="widget-content">
                                <table class="stats" data-chart-type="line" data-chart-colors="">
                                    <caption><?php echo $ano; ?> Margem Agregado Anual (%)</caption>
                                    <thead>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <?php echo $tituloGraf; ?>
                                        </tr>

                                    </thead>

                                    <tbody>

                                        <tr>
                                            <th>Realizado</th>
                                            <?php echo $graf_realizado; ?>
                                        </tr>
                                        <tr>
                                            <th>Ano Anterior</th>
                                            <?php echo $graf_realizadoAnt; ?>
                                        </tr>

                                    </tbody>
                                </table>
                            </div> <!-- .widget-content -->

                        </div> <!-- .widget -->

                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">MARGEM FRETE AGREGADO ANUAL</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>MES</th>
                                            <th>ANO ANTERIOR</th>
                                            <th>ANO ATUAL</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <?php
                                            echo $linhaTabelaAnual;
                                        ?>

                                    </tbody>
                                </table>
                            </div> <!-- .widget-content -->

                        </div> <!-- .widget -->

                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">TOTAL ANO MARGEM FRETE AGREGADO </h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ANO ANTERIOR</th>
                                            <th>ANO ATUAL</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <?php
                                            echo $linhaTabelaAgregadoAno;
                                        ?>

                                    </tbody>
                                </table>
                            </div> <!-- .widget-content -->

                        </div> <!-- .widget -->

                        <div class="widget">

                            <div class="widget-header">
                                <span class="icon-chart"></span>
                                <h3 class="icon chart">MARGEM FRETE TERCEIRO ANUAL</h3>
                            </div>

                            <div class="widget-content">
                                <table class="stats" data-chart-type="line" data-chart-colors="">
                                    <caption><?php echo $ano; ?> Margem Terceiro Anual (%)</caption>
                                    <thead>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <?php echo $tituloGraf; ?>
                                        </tr>

                                    </thead>

                                    <tbody>

                                        <tr>
                                            <th>Realizado</th>
                                            <?php echo $graf_realizadoTerceiro; ?>
                                        </tr>
                                        <tr>
                                            <th>Ano Anterior</th>
                                            <?php echo $graf_realizadoAntTerceiro; ?>
                                        </tr>
                                    </tbody>
                                </table>
                            </div> <!-- .widget-content -->

                        </div> <!-- .widget -->

                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">MARGEM FRETE TERCEIRO ANUAL</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>MES</th>
                                            <th>ANO ANTERIOR</th>
                                            <th>ANO ATUAL</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <?php
                                            echo $linhaTabelaAnualTerceiro;
                                        ?>

                                    </tbody>
                                </table>
                            </div> <!-- .widget-content -->

                        </div> <!-- .widget -->

                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">TOTAL ANO MARGEM FRETE TERCEIRO </h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ANO ANTERIOR</th>
                                            <th>ANO ATUAL</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <?php
                                            echo $linhaTabelaTerceiroAno;
                                        ?>

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

            <div id="quickNav">
                <ul>
                    <li class="quickNavMail">
                        <a href="#menuAmpersand" class="menu"><span class="icon-book"></span></a>

                        <span class="alert">3</span>

                        <div id="menuAmpersand" class="menu-container quickNavConfirm">
                            <div class="menu-content cf">

                                <div class="qnc qnc_confirm">

                                    <h3>Confirm</h3>

                                    <div class="qnc_item">
                                        <div class="qnc_content">
                                            <span class="qnc_title">Confirm #1</span>
                                            <span class="qnc_preview">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do.</span>
                                            <span class="qnc_time">3 hours ago</span>
                                        </div> <!-- .qnc_content -->

                                        <div class="qnc_actions">
                                            <button class="btn btn-primary btn-small">Accept</button>
                                            <button class="btn btn-quaternary btn-small">Not Now</button>
                                        </div>
                                    </div>

                                    <div class="qnc_item">
                                        <div class="qnc_content">
                                            <span class="qnc_title">Confirm #2</span>
                                            <span class="qnc_preview">Duis aute irure dolor in henderit in voluptate velit esse cillum dolore.</span>
                                            <span class="qnc_time">3 hours ago</span>
                                        </div> <!-- .qnc_content -->

                                        <div class="qnc_actions">
                                            <button class="btn btn-primary btn-small">Accept</button>
                                            <button class="btn btn-quaternary btn-small">Not Now</button>
                                        </div>
                                    </div>

                                    <div class="qnc_item">
                                        <div class="qnc_content">
                                            <span class="qnc_title">Confirm #3</span>
                                            <span class="qnc_preview">Duis aute irure dolor in henderit in voluptate velit esse cillum dolore.</span>
                                            <span class="qnc_time">3 hours ago</span>
                                        </div> <!-- .qnc_content -->

                                        <div class="qnc_actions">
                                            <button class="btn btn-primary btn-small">Accept</button>
                                            <button class="btn btn-quaternary btn-small">Not Now</button>
                                        </div>
                                    </div>

                                    <a href="javascript:;" class="qnc_more">View all Confirmations</a>

                                </div> <!-- .qnc -->
                            </div>
                        </div>
                    </li>
                    <li class="quickNavNotification">
                        <a href="#menuPie" class="menu"><span class="icon-chat"></span></a>

                        <div id="menuPie" class="menu-container">
                            <div class="menu-content cf">

                                <div class="qnc">

                                    <h3>Notifications</h3>

                                    <a href="javascript:;" class="qnc_item">
                                        <div class="qnc_content">
                                            <span class="qnc_title">Notification #1</span>
                                            <span class="qnc_preview">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do.</span>
                                            <span class="qnc_time">3 hours ago</span>
                                        </div> <!-- .qnc_content -->
                                    </a>

                                    <a href="javascript:;" class="qnc_item">
                                        <div class="qnc_content">
                                            <span class="qnc_title">Notification #2</span>
                                            <span class="qnc_preview">Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu.</span>
                                            <span class="qnc_time">3 hours ago</span>
                                        </div> <!-- .qnc_content -->
                                    </a>

                                    <a href="javascript:;" class="qnc_more">View all Confirmations</a>

                                </div> <!-- .qnc -->
                            </div>
                        </div>
                    </li>
                </ul>
            </div> <!-- .quickNav -->


        </div> <!-- #wrapper -->

        <div id="footer">
            <div style="float: left;">Versão <?php echo $_SESSION['version']; ?></div> Copyright &copy; <?php echo date('Y'); ?>, Case Electronic Ltda.
        </div>
    </body>
</html>