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
        <title>BID - Receita por cliente</title>

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
            include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptMSSQL.php';
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

            if (isset($_POST['ano'])){
                $ano = $_POST['ano'];
            }
            if (isset($_POST['mes'])){
                $mes_atual = $_POST['mes'];
            }

            /*             * ***************************** */
            /*  CONSULTA TOTAL DA EMPRESA    *
              /******************************** */
            $realizado = number_format(str_replace(',', '.', faturamentoAnoMes($ano, $mes_atual)), 0, ',', '.');
            $dados_total[0] = faturamentoAnoMes($ano, $mes_atual);
            /*             * ******************************************************************** */

            /*             * ****************************************************
             *   RECEITA TOTAL OUTROS - SEM PLACA
             * *************************************************** */
            $scriptOutros = "SELECT SUM(FPESO) FROM
(SELECT SUM(CT.VALTOTFRETE) AS FPESO
FROM CT
WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes_atual AND CT.ID_HVEICULO IS NULL

UNION
SELECT SUM(VALFRETE) AS FPESO
FROM CARRETO
WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes_atual AND CARRETO.ID_HVEICULO IS NULL

UNION
SELECT SUM(VLR_TOTAL) AS FPESO
FROM NOTAFAT
WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes_atual AND NOTAFAT.ID_VEICULO IS NULL
AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

UNION
SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
FROM NOTASER
WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes_atual AND NOTASER.ID_HVEICULO IS NULL

UNION
SELECT SUM(NOTADEB.VALOR) AS FPESO
FROM NOTADEB
WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano AND MONTH(NOTADEB.DATAEMISSAO)=$mes_atual

)";
            $db2_outros = db2_exec($hDbcDB2, $scriptOutros);
            $dados_outros_tt = db2_fetch_array($db2_outros);
            $outrosTotal = number_format(($dados_outros_tt[0] / $dados_total[0]) * 100, 0, ',', '.');

            /*             * ************************************************************* */


            /*             * ************************************************
              //BUSCA RECEITA TOTAL AGREGADO                    *
              /************************************************* */
            $sql_receitaAgregadoTotal = "SELECT SUM(FPESO) FROM
(SELECT SUM(VALTOTFRETE) AS FPESO
FROM CT
    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes_atual AND HVEICEMP.STAFT='A'

UNION
SELECT SUM(VALFRETE) AS FPESO
FROM CARRETO
    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes_atual AND HVEICEMP.STAFT='A'

UNION
SELECT SUM(VLR_TOTAL) AS FPESO
FROM NOTAFAT
    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTAFAT.ID_HVEICULO)
    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTAFAT.ID_HVEICULO)
WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes_atual AND HVEICEMP.STAFT='A'
AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

UNION
SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
FROM NOTASER
    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes_atual AND HVEICEMP.STAFT='A'

)";
            $db2_receitaAgregadoTotal = db2_exec($hDbcDB2, $sql_receitaAgregadoTotal);
            $dados_receitaAgregadoTotal = db2_fetch_array($db2_receitaAgregadoTotal);
            $a_porcTotal = number_format(($dados_receitaAgregadoTotal[0] / $dados_total[0]) * 100, 0, ',', '.');
//FIM CALCULO TOTAL AGREGADO

            /*             * ************************************************
              //BUSCA RECEITA TOTAL FROTA                       *
              /************************************************* */
            $sql_receitaFrotaTotal = "SELECT SUM(FPESO) FROM
(SELECT SUM(VALTOTFRETE) AS FPESO
FROM CT
    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes_atual AND HVEICEMP.STAFT='F'

UNION
SELECT SUM(VALFRETE) AS FPESO
FROM CARRETO
    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes_atual AND HVEICEMP.STAFT='F'

UNION
SELECT SUM(VLR_TOTAL) AS FPESO
FROM NOTAFAT
    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTAFAT.ID_HVEICULO)
    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTAFAT.ID_HVEICULO)
WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes_atual AND HVEICEMP.STAFT='F'
AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

UNION
SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
FROM NOTASER
    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes_atual AND HVEICEMP.STAFT='F'

)";
            $db2_receitaFrotaTotal = db2_exec($hDbcDB2, $sql_receitaFrotaTotal);
            $dados_receitaFrotaTotal = db2_fetch_array($db2_receitaFrotaTotal);
            $f_porcTotal = number_format(($dados_receitaFrotaTotal[0] / $dados_total[0]) * 100, 0, ',', '.');
//FIM CALCULO TOTAL FROTA

            /*             * ************************************************
              //BUSCA RECEITA TOTAL TERCEIRO                    *
              /************************************************* */
            $sql_receitaTerceiroTotal = "SELECT SUM(FPESO) FROM
(SELECT SUM(VALTOTFRETE) AS FPESO
FROM CT
    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes_atual AND HVEICEMP.STAFT='T'

UNION
SELECT SUM(VALFRETE) AS FPESO
FROM CARRETO
    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes_atual AND HVEICEMP.STAFT='T'

UNION
SELECT SUM(VLR_TOTAL) AS FPESO
FROM NOTAFAT
    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTAFAT.ID_HVEICULO)
    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTAFAT.ID_HVEICULO)
WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes_atual AND HVEICEMP.STAFT='T'
AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

UNION
SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
FROM NOTASER
    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes_atual AND HVEICEMP.STAFT='T'

)";
            $db2_receitaTerceiroTotal = db2_exec($hDbcDB2, $sql_receitaTerceiroTotal);
            $dados_receitaTerceiroTotal = db2_fetch_array($db2_receitaTerceiroTotal);
            $t_porcTotal = number_format(($dados_receitaTerceiroTotal[0] / $dados_total[0]) * 100, 0, ',', '.');
//FIM CALCULO TOTAL TERCEIRO


            /*             * *******************************
             *   CALCULO PREVISTO TOTAL   *
             * ****************************** */
            $orcamento_tt = number_format(receitaPrevistoAnoMes($ano, $mes_atual), 0, ',', '.');
            /*             * ***************************** */


            /*             * ***************************** */
            /*  CONSULTA POR CLIENTE        *
              /******************************** */
            $script_cliente = "SELECT CNPJ, SUM(FPESO) FROM
(SELECT CAST(HCLIENTE.CNPJ_CPF as VARCHAR(8)) AS CNPJ, SUM(CT.VALTOTFRETE) AS FPESO
FROM CT
    JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = CT.IDHCLIENTE)
WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes_atual
GROUP BY CAST(HCLIENTE.CNPJ_CPF as VARCHAR(8))


UNION
SELECT CAST(CLIENTE.CNPJ_CPF as VARCHAR(8)) AS CNPJ, SUM(VALFRETE) AS FPESO
FROM CARRETO
    JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = CARRETO.ID_CLIENTE)
WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes_atual
GROUP BY CAST(CLIENTE.CNPJ_CPF as VARCHAR(8))

UNION
SELECT CAST(CLIENTE.CNPJ_CPF as VARCHAR(8)) AS CNPJ, SUM(VLR_TOTAL) AS FPESO
FROM NOTAFAT
    JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = NOTAFAT.ID_CLIENTE)
WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes_atual
AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')
GROUP BY CAST(CLIENTE.CNPJ_CPF as VARCHAR(8))

UNION
SELECT CAST(HCLIENTE.CNPJ_CPF as VARCHAR(8)) AS CNPJ, SUM(NOTASER.VALTOTSERV) AS FPESO
FROM NOTASER
JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTASER.IDHCLIENTE)
WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes_atual
GROUP BY CAST(HCLIENTE.CNPJ_CPF as VARCHAR(8))

UNION
SELECT CAST(HCLIENTE.CNPJ_CPF as VARCHAR(8)) AS CNPJ, SUM(NOTADEB.VALOR) AS FPESO
FROM NOTADEB
JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTADEB.IDHCLIENTE)
WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano AND MONTH(NOTADEB.DATAEMISSAO)=$mes_atual
GROUP BY CAST(HCLIENTE.CNPJ_CPF as VARCHAR(8))

)GROUP BY CNPJ
ORDER BY SUM(FPESO) DESC
FETCH FIRST 15 ROW ONLY ";
            $db2_cliente = db2_exec($hDbcDB2, $script_cliente);
            while ($dados_cliente = db2_fetch_array($db2_cliente)){

                /*                 * ************************************************
                  //BUSCA RECEITA DO CLIENTE E AGREGADO             *
                  /************************************************* */
                $sql_receitaAgregado = "SELECT SUM(FPESO) FROM
    (SELECT SUM(VALTOTFRETE) AS FPESO
    FROM CT
        JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = CT.IDHCLIENTE)
        JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
        JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
    WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes_atual AND HVEICEMP.STAFT='A' AND HCLIENTE.CNPJ_CPF LIKE '$dados_cliente[0]%'

    UNION
    SELECT SUM(VALFRETE) AS FPESO
    FROM CARRETO
        JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = CARRETO.ID_CLIENTE)
        JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
        JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
    WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes_atual AND HVEICEMP.STAFT='A' AND CLIENTE.CNPJ_CPF LIKE '$dados_cliente[0]%'

    UNION
    SELECT SUM(VLR_TOTAL) AS FPESO
    FROM NOTAFAT
        JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = NOTAFAT.ID_CLIENTE)
        JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTAFAT.ID_HVEICULO)
        JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTAFAT.ID_HVEICULO)
    WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes_atual AND HVEICEMP.STAFT='A' AND CLIENTE.CNPJ_CPF LIKE '$dados_cliente[0]%'
    AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

    UNION
    SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
    FROM NOTASER
        JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTASER.IDHCLIENTE)
        JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
        JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
    WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes_atual AND HVEICEMP.STAFT='A' AND HCLIENTE.CNPJ_CPF LIKE '$dados_cliente[0]%'

    )";
                $db2_receitaAgregado = db2_exec($hDbcDB2, $sql_receitaAgregado);
                $dados_receitaAgregado = db2_fetch_array($db2_receitaAgregado);
                $a_porc = number_format(($dados_receitaAgregado[0] * 100) / $dados_cliente[1], 0, ',', '.');
                //FIM CALCULO CLIENTE E AGREGADO

                /*                 * ************************************************
                  //BUSCA RECEITA DO CLIENTE E FROTA                *
                  /************************************************* */
                $sql_receitaFrota = "SELECT SUM(FPESO) FROM
    (SELECT SUM(VALTOTFRETE) AS FPESO
    FROM CT
        JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = CT.IDHCLIENTE)
        JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
        JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
    WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes_atual AND HVEICEMP.STAFT='F' AND HCLIENTE.CNPJ_CPF LIKE '$dados_cliente[0]%'

    UNION
    SELECT SUM(VALFRETE) AS FPESO
    FROM CARRETO
        JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = CARRETO.ID_CLIENTE)
        JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
        JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
    WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes_atual AND HVEICEMP.STAFT='F' AND CLIENTE.CNPJ_CPF LIKE '$dados_cliente[0]%'

    UNION
    SELECT SUM(VLR_TOTAL) AS FPESO
    FROM NOTAFAT
        JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = NOTAFAT.ID_CLIENTE)
        JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTAFAT.ID_HVEICULO)
        JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTAFAT.ID_HVEICULO)
    WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes_atual AND HVEICEMP.STAFT='F' AND CLIENTE.CNPJ_CPF LIKE '$dados_cliente[0]%'
    AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

    UNION
    SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
    FROM NOTASER
        JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTASER.IDHCLIENTE)
        JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
        JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
    WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes_atual AND HVEICEMP.STAFT='F' AND HCLIENTE.CNPJ_CPF LIKE '$dados_cliente[0]%'

    )";
                $db2_receitaFrota = db2_exec($hDbcDB2, $sql_receitaFrota);
                $dados_receitaFrota = db2_fetch_array($db2_receitaFrota);
                $f_porc = number_format(($dados_receitaFrota[0] * 100) / $dados_cliente[1], 0, ',', '.');
                //FIM CALCULO CLIENTE E FROTA

                /*                 * ************************************************
                  //BUSCA RECEITA DO CLIENTE E TERCEIRO             *
                  /************************************************* */
                $sql_receitaTerceiro = "SELECT SUM(FPESO) FROM
    (SELECT SUM(VALTOTFRETE) AS FPESO
    FROM CT
        JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = CT.IDHCLIENTE)
        JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
        JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
    WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes_atual AND HVEICEMP.STAFT='T' AND HCLIENTE.CNPJ_CPF LIKE '$dados_cliente[0]%'

    UNION
    SELECT SUM(VALFRETE) AS FPESO
    FROM CARRETO
        JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = CARRETO.ID_CLIENTE)
        JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
        JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
    WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes_atual AND HVEICEMP.STAFT='T' AND CLIENTE.CNPJ_CPF LIKE '$dados_cliente[0]%'

    UNION
    SELECT SUM(VLR_TOTAL) AS FPESO
    FROM NOTAFAT
        JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = NOTAFAT.ID_CLIENTE)
        JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTAFAT.ID_HVEICULO)
        JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTAFAT.ID_HVEICULO)
    WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes_atual AND HVEICEMP.STAFT='T' AND CLIENTE.CNPJ_CPF LIKE '$dados_cliente[0]%'
    AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

    UNION
    SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
    FROM NOTASER
        JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTASER.IDHCLIENTE)
        JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
        JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
    WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes_atual AND HVEICEMP.STAFT='T' AND HCLIENTE.CNPJ_CPF LIKE '$dados_cliente[0]%'

    )";
                $db2_receitaTerceiro = db2_exec($hDbcDB2, $sql_receitaTerceiro);
                $dados_receitaTerceiro = db2_fetch_array($db2_receitaTerceiro);
                $t_porc = number_format(($dados_receitaTerceiro[0] * 100) / $dados_cliente[1], 0, ',', '.');
                //FIM CALCULO CLIENTE E TERCEIRO

                /*                 * ****************************************************
                 *   RECEITA CLIENTE OUTROS - SEM PLACA
                 * *************************************************** */
                $sql_receitaOutros = "SELECT SUM(FPESO) FROM
    (SELECT SUM(CT.VALTOTFRETE) AS FPESO
    FROM CT
        JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = CT.IDHCLIENTE)
    WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes_atual AND CT.ID_HVEICULO IS NULL AND HCLIENTE.CNPJ_CPF LIKE '$dados_cliente[0]%'

    UNION
    SELECT SUM(VALFRETE) AS FPESO
    FROM CARRETO
        JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = CARRETO.ID_CLIENTE)
    WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes_atual AND CARRETO.ID_HVEICULO IS NULL AND CLIENTE.CNPJ_CPF LIKE '$dados_cliente[0]%'

    UNION
    SELECT SUM(VLR_TOTAL) AS FPESO
    FROM NOTAFAT
        JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = NOTAFAT.ID_CLIENTE)
    WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes_atual AND NOTAFAT.ID_VEICULO IS NULL AND CLIENTE.CNPJ_CPF LIKE '$dados_cliente[0]%'
    AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

    UNION
    SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
    FROM NOTASER
        JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTASER.IDHCLIENTE)
    WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes_atual AND NOTASER.ID_HVEICULO IS NULL AND HCLIENTE.CNPJ_CPF LIKE '$dados_cliente[0]%'

    UNION
    SELECT SUM(NOTADEB.VALOR) AS FPESO
    FROM NOTADEB
        JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTADEB.IDHCLIENTE)
    WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano AND MONTH(NOTADEB.DATAEMISSAO)=$mes_atual AND HCLIENTE.CNPJ_CPF LIKE '$dados_cliente[0]%'

    )";
                $db2_receitaOutros = db2_exec($hDbcDB2, $sql_receitaOutros);
                $dados_receitaOutros = db2_fetch_array($db2_receitaOutros);
                $o_porc = number_format(($dados_receitaOutros[0] / $dados_cliente[1]) * 100, 0, ',', '.');

                /*                 * ************************************************************* */


                /*                 * *************************************************
                 *    NOME DO CLIENTE                              *
                 * ************************************************* */
                $sql_cliente = "SELECT RAZAO_SOCIAL FROM DB2.CLIENTE WHERE CNPJ_CPF LIKE '$dados_cliente[0]%' FETCH FIRST 1 ROW ONLY";
                $db2_nomeCliente = db2_exec($hDbcDB2, $sql_cliente);
                $dados_nomeCliente = db2_fetch_array($db2_nomeCliente);
                $nomeCliente = htmlentities($dados_nomeCliente[0]);
                /*                 * *********************************************** */

                /*                 * ************************************************
                 *    VALOR TOTAL POR CLIENTE                     *
                 * *********************************************** */
                $mostra_numero = number_format(str_replace(',', '.', $dados_cliente[1]), 0, ',', '.');
                /*                 * *********************************************** */

                /*                 * ************************************************
                 *   PORCENTAGEM DO CLIENTE X FATURAMENTO TOTAL   *
                 * ************************************************ */
                $porc = number_format(@($dados_cliente[1] / $dados_total[0]) * 100, 0, ',', '.');
                /*                 * ************************************************ */

                $linhaTabela = $linhaTabela . "<tr class='gradeA'>
                                    <td><a href='sublists/receita_fil_x_cli.php?c=$dados_cliente[0]&mes=$mes_atual&ano=$ano'>$nomeCliente</a></td>
                                    <td>$a_porc</td>
                                    <td>$f_porc</td>
                                    <td>$t_porc</td>
                                    <td>$o_porc</td>
                                    <td align='right'>$mostra_numero</td>
                                    <td align='right'>$porc</td>
                                </tr>";
            }

            $sqlAno = mssql_query("SELECT * FROM ano WHERE ano <> $ano ORDER BY ano DESC");
            $listaAno = $listaAno . "<option value='$ano'>$ano</option>";
            while ($dadosAno = mssql_fetch_array($sqlAno)){
                $listaAno = $listaAno . "<option value='$dadosAno[0]'>$dadosAno[0]</option>";
            }

            $sqlMes = mssql_query("SELECT * FROM mes WHERE id_mes <> $mes_atual");
            $listaMes = $listaMes . "<option value='$mes_atual'>" . mesSelecionado($mes_atual) . "</option>";
            while ($dadosMes = mssql_fetch_array($sqlMes)){
                $listaMes = $listaMes . "<option value='$dadosMes[0]'>$dadosMes[1]</option>";
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
                                <label>Selecione o MES:</label>
                                <select id="mes" name="mes" onchange="document.form1.submit()">
                                    <?php echo $listaMes; ?>
                                </select>

                                <label>Selecione o ANO:</label>
                                <select id="ano" name="ano" onchange="document.form1.submit()">
                                    <?php echo $listaAno; ?>
                                </select>
                                <br>
                                <br>
                                Periodo Selecionado: <?php echo mesSelecionado($mes_atual) . '/ ' . $ano; ?>.
                            </div>
                        </form>
                        <br>

                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Faturamento Clientes</h3>
                            </div>

                            <div class="widget-content">

                                <table class="table table-bordered table-striped ">
                                    <thead>
                                        <tr>
                                            <th width="45%">CLIENTE</th>
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

                        <div class="box plain">

                            <h3>TOTAL DA EMRPESA</h3>

                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th width="45%">EMPRESA</th>
                                        <th width="12%">AGREGADO %</th>
                                        <th width="12%">FROTA %</th>
                                        <th width="12%">TERCEIRO %</th>
                                        <th width="12%">OUTROS %</th>
                                        <th>PREVISTO(R$)</th>
                                        <th>RECEITA(R$)</th>
                                        <th width="9%">PORC %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="odd gradeX">
                                        <td><?php echo $_SESSION['nomeEmpresa']; ?></td>
                                        <td><?php echo $a_porcTotal; ?></td>
                                        <td><?php echo $f_porcTotal; ?></td>
                                        <td><?php echo $t_porcTotal; ?></td>
                                        <td><?php echo $outrosTotal; ?></td>
                                        <td align='right'><?php echo $orcamento_tt; ?></td>
                                        <td align='right'><?php echo $realizado; ?></td>
                                        <td align='right'><?php echo number_format(@($dados_total[0] / $dados_orcamento_tt[0]) * 100, 0, ',', '.'); ?></td>
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