<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL = new \Library\Scripts\scriptSQL();

    $_SESSION['modulo'] = basename(str_replace("sublists", "", dirname($_SERVER['PHP_SELF'])));
?>
<!doctype html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Receita cliente x filial</title>

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
            include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptdb2.php';
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
            $mes_atual = date('m');
            $imob = 0;

            if (isset($_GET['f'])){
                $filial = $_GET['f'];
            }
            if (isset($_POST['filial'])){
                $filial = $_POST['filial'];
            }

            if (isset($_GET['mes'])){
                $mes_atual = $_GET['mes'];
            }
            if (isset($_POST['mes'])){
                $mes_atual = $_POST['mes'];
            }

            if (isset($_GET['ano'])){
                $ano = $_GET['ano'];
            }
            if (isset($_POST['ano'])){
                $ano = $_POST['ano'];
            }

            if (isset($_POST['imob'])){
                $imob = $_POST['imob'];
            }

            $sqlAno = mssql_query("SELECT * FROM ano WHERE ano <> $ano ORDER BY ano DESC");
            $listaAno = $listaAno . "<option value='$ano'>$ano</option>";
            while ($dadosAno = mssql_fetch_array($sqlAno)){
                $listaAno = $listaAno . "<option value='$dadosAno[0]'>$dadosAno[0]</option>";
            }

//NOME DO MES SELECIONADO
            $sqlMesSelecionado = mssql_query("SELECT * FROM mes WHERE id_mes=$mes_atual");
            $dadosMesSelecionado = mssql_fetch_array($sqlMesSelecionado);

            $sqlMes = mssql_query("SELECT * FROM mes WHERE id_mes <> $mes_atual");
            $listaMes = $listaMes . "<option value='$mes_atual'>$dadosMesSelecionado[1]</option>";
            while ($dadosMes = mssql_fetch_array($sqlMes)){
                $listaMes = $listaMes . "<option value='$dadosMes[0]'>$dadosMes[1]</option>";
            }

//NOME DA FILIAL
            $sql_filial = "SELECT * FROM CTCUSTO WHERE IDCTCUSTO = $filial FETCH FIRST 1 ROWS ONLY";
            $db2_filial = db2_exec($hDbcDB2, $sql_filial);
            $dadosFilial = db2_fetch_array($db2_filial);
            $nomeFilial = htmlentities($dadosFilial[3]);

            /*             * ***************************** */
            /*  CONSULTA TOTAL DA FILIAL     *
              /******************************** */
            $freteTotalFilial = freteTotalAnoMesFilial($ano, $mes_atual, $imob, $filial);
            $realizado = number_format($freteTotalFilial, 0, ',', '.');
            /*             * ******************************************************************** */

            /*             * ****************************************************
             *   RECEITA TOTAL OUTROS - SEM PLACA
             * *************************************************** */
            $freteTotalOutros = faturamentoAnoMesOutrosFilial($ano, $mes_atual, $imob, filial);
            $outrosTotal = number_format(($freteTotalOutros / $freteTotalFilial) * 100, 0, ',', '.');
            /*             * ************************************************************* */

            /*             * ************************************************
              //BUSCA RECEITA TOTAL AGREGADO                    *
              /************************************************* */
            $freteTotalAgregado = freteTotalAnoMesAFTOFilial($ano, $mes_atual, 'A', $imob, $filial);
            $a_porcTotal = number_format(($freteTotalAgregado / $freteTotalFilial) * 100, 0, ',', '.');
//FIM CALCULO TOTAL AGREGADO

            /*             * ************************************************
              //BUSCA RECEITA TOTAL FROTA                       *
              /************************************************* */
            $freteTotalFrota = freteTotalAnoMesAFTOFilial($ano, $mes_atual, 'F', $imob, $filial);
            $f_porcTotal = number_format(($freteTotalFrota / $freteTotalFilial) * 100, 0, ',', '.');
//FIM CALCULO TOTAL FROTA

            /*             * ************************************************
              //BUSCA RECEITA TOTAL TERCEIRO                    *
              /************************************************* */
            $freteTotalTerceiro = freteTotalAnoMesAFTOFilial($ano, $mes_atual, 'T', $imob, $filial);
            $t_porcTotal = number_format(($freteTotalTerceiro / $freteTotalFilial) * 100, 0, ',', '.');
//FIM CALCULO TOTAL TERCEIRO


            /*             * ***************************** */
            /*  CONSULTA FILIAIS DO CLIENTE   *
              /******************************** */
            $script_cliente = listaClientesFilial($ano, $mes_atual, $imob, $filial);
            foreach ($script_cliente AS $dados_cliente){


                /*                 * *************************************************
                 *    NOME DA CLIENTE                              *
                 * ************************************************* */
                $nomeCliente = htmlentities($dados_cliente['NOMECLIENTE']);
                /*                 * *********************************************** */

                /*                 * ************************************************
                 *    VALOR TOTAL POR CLIENTE                     *
                 * *********************************************** */
                $freteTotalFilialCliente = $dados_cliente['FRETETOTAL'];
                $mostra_numero = number_format(str_replace(',', '.', $dados_cliente['FRETETOTAL']), 0, ',', '.');
                /*                 * *********************************************** */

                /*                 * ************************************************
                 *   PORCENTAGEM DO CLIENTE X FATURAMENTO TOTAL   *
                 * ************************************************ */
                $porc = number_format(@($dados_cliente['FRETETOTAL'] / $freteTotalFilial) * 100, 0, ',', '.');
                /*                 * ************************************************ */


                /*                 * ****************************************************
                 *   RECEITA FILAIL OUTROS - SEM PLACA
                 * *************************************************** */
                $scriptOutrosFilial = "SELECT SUM(FPESO) FROM
    (SELECT SUM(CT.VALTOTFRETE) AS FPESO
    FROM CT
        JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = CT.IDHCLIENTE)
        JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
    WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes_atual AND CT.ID_HVEICULO IS NULL AND HCLIENTE.CNPJ_CPF like '$dados_cliente[0]%' AND CT.IDCTCUSTO=$filial

    UNION
    SELECT SUM(VALFRETE) AS FPESO
    FROM CARRETO
        JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = CARRETO.ID_CLIENTE)
        JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
        JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = FILIAL.IDCTCUSTO)
    WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes_atual AND CARRETO.ID_HVEICULO IS NULL AND CLIENTE.CNPJ_CPF like '$dados_cliente[0]%' AND CTCUSTO.IDCTCUSTO=$filial

    UNION
    SELECT SUM(VLR_TOTAL) AS FPESO
    FROM NOTAFAT
        JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = NOTAFAT.ID_CLIENTE)
        JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTAFAT.ID_FILIAL)
        JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTAFAT.IDCTCUSTO)
    WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes_atual AND NOTAFAT.ID_VEICULO IS NULL AND CLIENTE.CNPJ_CPF like '$dados_cliente[0]%' AND CTCUSTO.IDCTCUSTO=$filial
    AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

    UNION
    SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
    FROM NOTASER
        JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTASER.IDHCLIENTE)
        JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
    WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes_atual AND NOTASER.ID_HVEICULO IS NULL AND HCLIENTE.CNPJ_CPF like '$dados_cliente[0]%' AND CTCUSTO.IDCTCUSTO=$filial

    UNION
    SELECT SUM(NOTADEB.VALOR) AS FPESO
    FROM NOTADEB
        JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTADEB.IDHCLIENTE)
        JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTADEB.ID_FILIAL)
        JOIN LANCTO ON (LANCTO.NUM_DOCUMENTO = NOTADEB.NUM_DOCUMENTO AND LANCTO.NUM_LOTE = NOTADEB.NUM_LOTE AND LANCTO.NUM_SUBLOTE = NOTADEB.NUM_SUBLOTE AND LANCTO.CCUSTO <> '')
        JOIN CTCUSTO ON (CTCUSTO.COD_CTCUSTO = LANCTO.CCUSTO)
    WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano AND MONTH(NOTADEB.DATAEMISSAO)=$mes_atual AND HCLIENTE.CNPJ_CPF like '$dados_cliente[0]%' AND CTCUSTO.IDCTCUSTO=$filial

    )";
                $db2_outrosFilial = db2_exec($hDbcDB2, $scriptOutrosFilial);
                $dados_outros_ttFilial = db2_fetch_array($db2_outrosFilial);
                $o_porc = number_format(($dados_outros_ttFilial[0] / $freteTotalFilialCliente) * 100, 0, ',', '.');

                /*                 * ************************************************************* */

                /*                 * ************************************************
                  //BUSCA RECEITA STAFT                             *
                  /************************************************* */
                $t_porc = 0;
                $a_porc = 0;
                $f_porc = 0;
                $sql_staft = "SELECT STAFT, SUM(FPESO) FROM
            (SELECT HVEICEMP.STAFT AS STAFT, SUM(VALTOTFRETE) AS FPESO
            FROM CT
                JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = CT.IDHCLIENTE)
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
            WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes_atual AND HCLIENTE.CNPJ_CPF like '$dados_cliente[CNPJ]%' AND CT.IDCTCUSTO=$filial
            GROUP BY STAFT

            UNION
            SELECT HVEICEMP.STAFT AS STAFT, SUM(VALFRETE) AS FPESO
            FROM CARRETO
                JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = CARRETO.ID_CLIENTE)
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
                JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
                JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = FILIAL.IDCTCUSTO)
            WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes_atual AND CLIENTE.CNPJ_CPF like '$dados_cliente[CNPJ]%' AND CTCUSTO.IDCTCUSTO=$filial
                GROUP BY STAFT
            UNION
            SELECT HVEICEMP.STAFT AS STAFT, SUM(VLR_TOTAL) AS FPESO
            FROM NOTAFAT
                JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = NOTAFAT.ID_CLIENTE)
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTAFAT.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTAFAT.ID_HVEICULO)
                JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTAFAT.ID_FILIAL)
                JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTAFAT.IDCTCUSTO)
            WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes_atual AND CLIENTE.CNPJ_CPF like '$dados_cliente[CNPJ]%' AND CTCUSTO.IDCTCUSTO=$filial
            AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')
                    GROUP BY STAFT

            UNION
            SELECT HVEICEMP.STAFT AS STAFT, SUM(NOTASER.VALTOTSERV) AS FPESO
            FROM NOTASER
                JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTASER.IDHCLIENTE)
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
                JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTASER.ID_FILIAL)
                JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
            WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes_atual AND HCLIENTE.CNPJ_CPF like '$dados_cliente[CNPJ]%' AND CTCUSTO.IDCTCUSTO=$filial
                    GROUP BY STAFT
        )
        GROUP BY STAFT";
                $db2_receitaStaft = db2_exec($hDbcDB2, $sql_staft);
                while ($dadosReceitaStaft = db2_fetch_array($db2_receitaStaft)){
                    if ($dadosReceitaStaft[0] == 'T'){
                        $t_porc = number_format(($dadosReceitaStaft[1] / $freteTotalFilialCliente) * 100, 0, ',', '.');
                    }
                    if ($dadosReceitaStaft[0] == 'A'){
                        $a_porc = number_format(($dadosReceitaStaft[1] / $freteTotalFilialCliente) * 100, 0, ',', '.');
                    }
                    if ($dadosReceitaStaft[0] == 'F'){
                        $f_porc = number_format(($dadosReceitaStaft[1] / $freteTotalFilialCliente) * 100, 0, ',', '.');
                    }
                }

                $linhaTabela = $linhaTabela . "<tr class='gradeA'>
                                    <td><a href='documentos.php?cliente=$dados_cliente[CNPJ]&filial=$filial&mes=$mes_atual&ano=$ano'>$nomeCliente</a></td>
                                    <td>$a_porc</td>
                                    <td>$f_porc</td>
                                    <td>$t_porc</td>
                                    <td>$o_porc</td>
                                    <td align='right'>$mostra_numero</td>
                                    <td align='right'>$porc</td>
                                </tr>";
                //ZERA VALORES
            }

//SELECAO DOS EQUIPAMENTOS USADO NO CLIENTE;
            $sql_ListaEquip = "SELECT EQUIP, SUM(TTEQUIP) AS TT FROM
            (SELECT CONJUNTO.NAME AS EQUIP, COUNT(CT.ID_CT) AS TTEQUIP
            FROM CT
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
                JOIN CONJUNTO ON (CONJUNTO.CODECONJ = HVEICULO.CODECONJ)
            WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes_atual AND CT.IDCTCUSTO=$filial
            GROUP BY CONJUNTO.NAME

            UNION
            SELECT CONJUNTO.NAME AS EQUIP,COUNT(*) AS TTEQUIP
            FROM CARRETO
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
                JOIN CONJUNTO ON (CONJUNTO.CODECONJ = HVEICULO.CODECONJ)
                JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
                JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = FILIAL.IDCTCUSTO)
            WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes_atual AND CTCUSTO.IDCTCUSTO=$filial
                GROUP BY CONJUNTO.NAME
            UNION
            SELECT CONJUNTO.NAME AS EQUIP, COUNT(*) AS TTEQUIP
            FROM NOTAFAT
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTAFAT.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTAFAT.ID_HVEICULO)
                JOIN CONJUNTO ON (CONJUNTO.CODECONJ = HVEICULO.CODECONJ)
                JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTAFAT.ID_FILIAL)
                JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTAFAT.IDCTCUSTO)
            WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes_atual
            AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102') AND CTCUSTO.IDCTCUSTO=$filial
                    GROUP BY CONJUNTO.NAME

            UNION
            SELECT CONJUNTO.NAME AS EQUIP, COUNT(*) AS TTEQUIP
            FROM NOTASER
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
                JOIN CONJUNTO ON (CONJUNTO.CODECONJ = HVEICULO.CODECONJ)
                JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTASER.ID_FILIAL)
                JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
            WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes_atual AND CTCUSTO.IDCTCUSTO=$filial
                    GROUP BY CONJUNTO.NAME
        )
        GROUP BY EQUIP
        ORDER BY TT DESC";
            $db2_ListaEquip = db2_exec($hDbcDB2, $sql_ListaEquip);
            while ($dados_listaEquip = db2_fetch_array($db2_ListaEquip)){
                $linhaListaEquip = $linhaListaEquip . "<tr class='gradeA'>
                                    <td>$dados_listaEquip[0]</td>
                                    <td>$dados_listaEquip[1]</td>
                                </tr>";
            }

            //DIAS PARA O GRAFICO
            $dias_mes = cal_days_in_month(CAL_GREGORIAN, $mes_atual, $ano);
            for ($i = 1; $i <= $dias_mes; $i++){
                $tituloGraf = $tituloGraf . '<th>' . $i . '</th>';


                $freteTotalAnoMesDiaFilial = faturamentoAnoMesDiaFilial($ano, $mes_atual, $i, $imob, $filial);
                $realizadoDiario = number_format($freteTotalAnoMesDiaFilial, 0, '', '');
                /*                 * **************************** */
                //REALIZADO PARA O GRAFICO
                $graf_realizado = $graf_realizado . '<td>' . $realizadoDiario . '</td>';
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

                        <form action="#" method="post" name="form1">
                            <div class="field">
                                <input type="hidden" id="filial" name="filial" value="<?php echo $filial; ?>"/>
                                <label>Selecione o MES:</label>
                                <select id="mes" name="mes" onchange="document.form1.submit()">
                                    <?php echo $listaMes; ?>
                                </select>

                                <label>Selecione o ANO:</label>
                                <select id="ano" name="ano" onchange="document.form1.submit()">
                                    <?php echo $listaAno; ?>
                                </select>
                                Calcular venda de imobilizado.
                                <input type="checkbox" name="imob" value="1" <?php if ($imob) echo "checked"; ?> onchange="document.form1.submit()">
                            </div>
                        </form>
                        <br>

                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Faturamento Cliente</h3>
                            </div>

                            <div class="widget-content">

                                <table class="table table-bordered table-striped ">
                                    <thead>
                                        <tr>
                                            <th width="45%">FILIAL: <?php echo $nomeFilial; ?></th>
                                            <th width="12%">AGREGADO %</th>
                                            <th width="12%">FROTA %</th>
                                            <th width="12%">TERCEIRO %</th>
                                            <th width="12%">OUTROS %</th>
                                            <th>RECEITA(R$)</th>
                                            <th width="9%">PORC %</th>
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

                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Total da Filial</h3>
                            </div>

                            <div class="widget-content">

                                <table class="table table-bordered table-striped ">
                                    <thead>
                                        <tr>
                                            <th width="45%">FILIAL</th>
                                            <th width="12%">AGREGADO %</th>
                                            <th width="12%">FROTA %</th>
                                            <th width="12%">TERCEIRO %</th>
                                            <th width="12%">OUTROS %</th>
                                            <th>RECEITA(R$)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="odd gradeX">
                                            <td><?php echo $nomeFilial; ?></td>
                                            <td><?php echo $a_porcTotal; ?></td>
                                            <td><?php echo $f_porcTotal; ?></td>
                                            <td><?php echo $t_porcTotal; ?></td>
                                            <td><?php echo $outrosTotal; ?></td>
                                            <td align='right'><?php echo $realizado; ?></td>
                                        </tr>
                                    </tbody>
                                </table>

                            </div> <!-- .widget-content -->

                        </div> <!-- .widget -->

                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Equipamentos</h3>
                            </div>

                            <div class="widget-content">

                                <table class="table table-bordered table-striped ">
                                    <thead>
                                        <tr>
                                            <th width="45%">EQUIPAMENTO</th>
                                            <th width="12%">QUANTIDADE</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php echo $linhaListaEquip; ?>
                                    </tbody>
                                </table>

                            </div> <!-- .widget-content -->

                        </div> <!-- .widget -->

                        <div class="widget">

                            <div class="widget-header">
                                <span class="icon-chart"></span>
                                <h3 class="icon chart">Receita Diaria Filial Mensal</h3>
                            </div>

                            <div class="widget-content">
                                <table class="stats" data-chart-type="line" data-chart-colors="">
                                    <caption><?php echo $ano; ?> Receita Diaria Filial Mensal</caption>
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