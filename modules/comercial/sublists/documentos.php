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
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
    <head>
        <title>BID - Documentos</title>

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

            if (isset($_GET['mes'])){
                $mes_atual = $_GET['mes'];
            }
            if (isset($_GET['ano'])){
                $ano = $_GET['ano'];
            }
            /*             * ********************************* */

            /*             * ************************************************
              //BUSCA DOCUMENTOS PLACA                           *
              /************************************************* */
            if (isset($_GET['placa'])){
                $placa = $_GET['placa'];
                //LINK PARA RELATORIO DETALHADO
                $link_detalhado = NULL;

                $sql_total = "SELECT SUM(FT), SUM(FPESO) FROM
        (SELECT SUM(CT.VALTOTFRETE) AS FT, SUM(CT.VALFPESOSICMS) AS FPESO
        FROM CT
            JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
            JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
            JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
            JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = CT.IDHCLIENTE)
        WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes_atual AND HVEICULO.PLACA='$placa'

        UNION
        SELECT SUM(CARRETO.VALFRETE) AS FT, SUM(CARRETO.VALFRETE) AS FPESO
        FROM CARRETO
            JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
            JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
            JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
            JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = CARRETO.ID_CLIENTE)
        WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes_atual AND HVEICULO.PLACA='$placa'

        UNION
        SELECT SUM(NOTAFAT.VLR_TOTAL) AS FT, SUM(NOTAFAT.VLR_TOTAL) AS FPESO
        FROM NOTAFAT
            JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTAFAT.ID_HVEICULO)
            JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTAFAT.ID_HVEICULO)
            JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTAFAT.ID_FILIAL)
            JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = NOTAFAT.ID_CLIENTE)
        WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes_atual AND HVEICULO.PLACA='$placa'
        AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

        UNION
        SELECT SUM(NOTASER.VALTOTSERV) AS FT, SUM(NOTASER.VALTOTSERV) AS FPESO
        FROM NOTASER
            JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
            JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
            JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTASER.ID_FILIAL)
            JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTASER.IDHCLIENTE)
        WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes_atual AND HVEICULO.PLACA='$placa'

        )";
                /*                 * ************************************************
                  //BUSCA DOCUMENTOS                                *
                  /************************************************* */

                $sql_documentos = "SELECT TIPO, FILIAL, NUMERO, DATA, PLACA, CONTRATO, FT, CLIENTE, DESTINO, FPESO FROM
        (SELECT 'CT' AS TIPO, FILIAL.SIGLA_FILIAL AS FILIAL, CT.NUMERO AS NUMERO, CT.DATAEMISSAO AS DATA, HVEICULO.PLACA AS PLACA,
                HVEICEMP.STAFT AS CONTRATO, CT.VALTOTFRETE AS FT, HCLIENTE.RAZAO_SOCIAL AS CLIENTE, CIDADE.NOME_CIDADE AS DESTINO,
                CT.VALFPESOSICMS AS FPESO
        FROM CT
            JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
            JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
            JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
            JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = CT.IDHCLIENTE)
            JOIN HCLIENTE HCD ON (HCD.IDHCLIENTE = CT.IDHCLIENTEDEST)
            JOIN CIDADE ON (CIDADE.ID_CIDADE = HCD.ID_CIDADE)
        WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes_atual AND HVEICULO.PLACA='$placa'

        UNION
        SELECT 'CR' AS TIPO, FILIAL.SIGLA_FILIAL AS FILIAL, CARRETO.NUMERO AS NUMERO, CARRETO.DATASAIDA AS DATA, HVEICULO.PLACA AS PLACA,
                HVEICEMP.STAFT AS CONTRATO, CARRETO.VALFRETE AS FT, CLIENTE.RAZAO_SOCIAL AS CLIENTE, CIDADE.NOME_CIDADE AS DESTINO,
                CARRETO.VALFRETE AS FPESO
        FROM CARRETO
            JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
            JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
            JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
            JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = CARRETO.ID_CLIENTE)
            JOIN ROTA ON (ROTA.ID_ROTA = CARRETO.ID_ROTA)
            JOIN CIDADE ON (CIDADE.ID_CIDADE = ROTA.ID_CIDADEDEST)
        WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes_atual AND HVEICULO.PLACA='$placa'

        UNION
        SELECT 'NF' AS TIPO, FILIAL.SIGLA_FILIAL AS FILIAL, NOTAFAT.NUMNOTA AS NUMERO, NOTAFAT.DATA_EMIS AS DATA, HVEICULO.PLACA AS PLACA,
                HVEICEMP.STAFT AS CONTRATO, NOTAFAT.VLR_TOTAL AS FT, CLIENTE.RAZAO_SOCIAL AS CLIENTE, '-' AS DESTINO,
                NOTAFAT.VLR_TOTAL AS FPESO
        FROM NOTAFAT
            JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTAFAT.ID_HVEICULO)
            JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTAFAT.ID_HVEICULO)
            JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTAFAT.ID_FILIAL)
            JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = NOTAFAT.ID_CLIENTE)
        WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes_atual AND HVEICULO.PLACA='$placa'
        AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

        UNION
        SELECT 'NS' AS TIPO, FILIAL.SIGLA_FILIAL AS FILIAL, NOTASER.NUMERO AS NUMERO, NOTASER.DATAEMIS AS DATA, HVEICULO.PLACA AS PLACA,
                HVEICEMP.STAFT AS CONTRATO, NOTASER.VALTOTSERV AS FT, HCLIENTE.RAZAO_SOCIAL AS CLIENTE, '-' AS DESTINO,
                NOTASER.VALTOTSERV AS FPESO
        FROM NOTASER
            JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
            JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
            JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTASER.ID_FILIAL)
            JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTASER.IDHCLIENTE)
        WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes_atual AND HVEICULO.PLACA='$placa'

        UNION ALL

        SELECT 'VZ' AS TIPO, '' AS FILIAL, Z.ID_VIAGVZ AS NUMERO, Z.DATACAD AS DATA, V.PLACA AS PLACA,
                E.STAFT AS CONTRATO, 0 AS FT, '' AS CLIENTE, D.NOME_CIDADE AS DESTINO,
                0 AS FPESO
        FROM VIAGVZ Z
        JOIN HVEICULO V ON (V.ID_HVEICULO = Z.ID_HVEICULO)
        JOIN HVEICEMP E ON (E.IDHVEICEMP = Z.IDHVEICEMP)
        JOIN ROTA R ON R.ID_ROTA = Z.ID_ROTA
        JOIN CIDADE D ON D.ID_CIDADE = R.ID_CIDADEDEST
        WHERE YEAR(Z.DATACAD) = $ano AND MONTH(Z.DATACAD) = $mes_atual AND V.PLACA = '$placa'

        )
        ORDER BY DATA DESC";
            }


//****************************************************
//     BUSCA DOCUMENTOS CLIENTE E FILIAL             *
//****************************************************
            if (isset($_GET['cliente']) && isset($_GET['filial'])){
                $cliente = $_GET['cliente'];
                $filial = $_GET['filial'];
                //LINK PARA RELATORIO DETALHADO
                $link_detalhado = "<a href='documentos_detalhado.php?cliente=$cliente&filial=$filial&mes=$mes_atual&ano=$ano'>Relatorio Documentos Detalhado</a>";

                $sql_total = "SELECT SUM(FT), SUM(FPESO) FROM
        (SELECT SUM(CT.VALTOTFRETE) AS FT, SUM(CT.VALFPESOSICMS) AS FPESO
        FROM CT
            JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
            JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
            JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
            JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = CT.IDHCLIENTE)
            JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = CT.IDCTCUSTO)
        WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes_atual AND CTCUSTO.IDCTCUSTO=$filial AND HCLIENTE.CNPJ_CPF like '$cliente%'

        UNION
        SELECT SUM(CARRETO.VALFRETE) AS FT, SUM(CARRETO.VALFRETE) AS FPESO
        FROM CARRETO
            JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
            JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
            JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
            JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = CARRETO.ID_CLIENTE)
            JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = CARRETO.IDCTCUSTO)
        WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes_atual AND CTCUSTO.IDCTCUSTO=$filial AND CLIENTE.CNPJ_CPF like '$cliente%'

        UNION
        SELECT SUM(NOTAFAT.VLR_TOTAL) AS FT, SUM(NOTAFAT.VLR_TOTAL) AS FPESO
        FROM NOTAFAT
            JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTAFAT.ID_HVEICULO)
            JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTAFAT.ID_HVEICULO)
            JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTAFAT.ID_FILIAL)
            JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = NOTAFAT.ID_CLIENTE)
            JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTAFAT.IDCTCUSTO)
        WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes_atual AND CTCUSTO.IDCTCUSTO=$filial AND CLIENTE.CNPJ_CPF like '$cliente%'
        AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

        UNION
        SELECT SUM(NOTASER.VALTOTSERV) AS FT, SUM(NOTASER.VALTOTSERV) AS FPESO
        FROM NOTASER
            JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
            JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
            JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTASER.ID_FILIAL)
            JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTASER.IDHCLIENTE)
            JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
        WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes_atual AND CTCUSTO.IDCTCUSTO=$filial AND HCLIENTE.CNPJ_CPF like '$cliente%'

        UNION
        SELECT SUM(NOTADEB.VALOR) AS FT, SUM(NOTADEB.VALOR) AS FPESO
        FROM NOTADEB
            JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTADEB.IDHCLIENTE)
            JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTADEB.ID_FILIAL)
            JOIN LANCTO ON (LANCTO.NUM_DOCUMENTO = NOTADEB.NUM_DOCUMENTO AND LANCTO.NUM_LOTE = NOTADEB.NUM_LOTE AND LANCTO.NUM_SUBLOTE = NOTADEB.NUM_SUBLOTE AND LANCTO.CCUSTO <> '')
            JOIN CTCUSTO ON (CTCUSTO.COD_CTCUSTO = LANCTO.CCUSTO)
        WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano AND MONTH(NOTADEB.DATAEMISSAO)=$mes_atual AND CTCUSTO.IDCTCUSTO=$filial AND HCLIENTE.CNPJ_CPF like '$cliente%'
        )";
                /*                 * ************************************************
                  //BUSCA DOCUMENTOS                                *
                  /************************************************* */

                $sql_documentos = "SELECT TIPO, FILIAL, NUMERO, DATA, PLACA, CONTRATO, FT, CLIENTE, DESTINO, FPESO FROM
        (SELECT 'CT' AS TIPO, FILIAL.SIGLA_FILIAL AS FILIAL, CT.NUMERO AS NUMERO, CT.DATAEMISSAO AS DATA, HVEICULO.PLACA AS PLACA,
                HVEICEMP.STAFT AS CONTRATO, CT.VALTOTFRETE AS FT, HCLIENTE.RAZAO_SOCIAL AS CLIENTE, CIDADE.NOME_CIDADE AS DESTINO,
                CT.VALFPESOSICMS AS FPESO
        FROM CT
            JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
            JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
            JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
            JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = CT.IDHCLIENTE)
            JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = CT.IDCTCUSTO)
            JOIN HCLIENTE HCD ON (HCD.IDHCLIENTE = CT.IDHCLIENTEDEST)
            JOIN CIDADE ON (CIDADE.ID_CIDADE = HCD.ID_CIDADE)
        WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes_atual AND CTCUSTO.IDCTCUSTO=$filial AND HCLIENTE.CNPJ_CPF like '$cliente%'

        UNION
        SELECT 'CR' AS TIPO, FILIAL.SIGLA_FILIAL AS FILIAL, CARRETO.NUMERO AS NUMERO, CARRETO.DATASAIDA AS DATA, HVEICULO.PLACA AS PLACA,
                HVEICEMP.STAFT AS CONTRATO, CARRETO.VALFRETE AS FT, CLIENTE.RAZAO_SOCIAL AS CLIENTE, CIDADE.NOME_CIDADE AS DESTINO,
                CARRETO.VALFRETE AS FPESO
        FROM CARRETO
            JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
            JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
            JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
            JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = CARRETO.ID_CLIENTE)
            JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = CARRETO.IDCTCUSTO)
            JOIN ROTA ON (ROTA.ID_ROTA = CARRETO.ID_ROTA)
            JOIN CIDADE ON (CIDADE.ID_CIDADE = ROTA.ID_CIDADEDEST)
        WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes_atual AND CTCUSTO.IDCTCUSTO=$filial AND CLIENTE.CNPJ_CPF like '$cliente%'

        UNION
        SELECT 'NF' AS TIPO, FILIAL.SIGLA_FILIAL AS FILIAL, NOTAFAT.NUMNOTA AS NUMERO, NOTAFAT.DATA_EMIS AS DATA, HVEICULO.PLACA AS PLACA,
                HVEICEMP.STAFT AS CONTRATO, NOTAFAT.VLR_TOTAL AS FT, CLIENTE.RAZAO_SOCIAL AS CLIENTE, '-' AS DESTINO,
                NOTAFAT.VLR_TOTAL AS FPESO
        FROM NOTAFAT
            JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTAFAT.ID_HVEICULO)
            JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTAFAT.ID_HVEICULO)
            JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTAFAT.ID_FILIAL)
            JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = NOTAFAT.ID_CLIENTE)
            JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTAFAT.IDCTCUSTO)
        WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes_atual AND CTCUSTO.IDCTCUSTO=$filial AND CLIENTE.CNPJ_CPF like '$cliente%'
        AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

        UNION
        SELECT 'NS' AS TIPO, FILIAL.SIGLA_FILIAL AS FILIAL, NOTASER.NUMERO AS NUMERO, NOTASER.DATAEMIS AS DATA, HVEICULO.PLACA AS PLACA,
                HVEICEMP.STAFT AS CONTRATO, NOTASER.VALTOTSERV AS FT, HCLIENTE.RAZAO_SOCIAL AS CLIENTE, '-' AS DESTINO,
                NOTASER.VALTOTSERV AS FPESO
        FROM NOTASER
            JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
            JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
            JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTASER.ID_FILIAL)
            JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTASER.IDHCLIENTE)
            JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
        WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes_atual AND CTCUSTO.IDCTCUSTO=$filial AND HCLIENTE.CNPJ_CPF like '$cliente%'

        UNION
        SELECT 'ND' AS TIPO, FILIAL.SIGLA_FILIAL AS FILIAL, NOTADEB.NUMERO AS NUMERO, NOTADEB.DATAEMISSAO AS DATA, '-' AS PLACA,
        '-' AS CONTRATO, NOTADEB.VALOR AS FT, HCLIENTE.RAZAO_SOCIAL AS CLIENTE, '-' AS DESTINO,
        NOTADEB.VALOR AS FPESO
        FROM NOTADEB
            JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTADEB.IDHCLIENTE)
            JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTADEB.ID_FILIAL)
            JOIN LANCTO ON (LANCTO.NUM_DOCUMENTO = NOTADEB.NUM_DOCUMENTO AND LANCTO.NUM_LOTE = NOTADEB.NUM_LOTE AND LANCTO.NUM_SUBLOTE = NOTADEB.NUM_SUBLOTE AND LANCTO.CCUSTO <> '')
            JOIN CTCUSTO ON (CTCUSTO.COD_CTCUSTO = LANCTO.CCUSTO)
        WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano AND MONTH(NOTADEB.DATAEMISSAO)=$mes_atual AND CTCUSTO.IDCTCUSTO=$filial AND HCLIENTE.CNPJ_CPF like '$cliente%'

        )
        ORDER BY DATA DESC";
            }

            /*             * ************************************************
              //BUSCA DOCUMENTOS MOTORISTA                      *
              /************************************************* */
            if (isset($_GET['motorista'])){
                $motorista = $_GET['motorista'];
                //LINK PARA RELATORIO DETALHADO
                $link_detalhado = NULL;

                $sql_total = "SELECT SUM(FT), SUM(FPESO) FROM
        (SELECT SUM(CT.VALTOTFRETE) AS FT, SUM(CT.VALFPESOSICMS) AS FPESO
        FROM CT
            JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
            JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
            JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
            JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = CT.IDHCLIENTE)
            JOIN HMOTORIS ON (HMOTORIS.IDHMOTORIS = HVEICULO.IDHMOTORIS)
        WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes_atual AND HMOTORIS.CPF = '$motorista'

        UNION
        SELECT SUM(CARRETO.VALFRETE) AS FT, SUM(CARRETO.VALFRETE) AS FPESO
        FROM CARRETO
            JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
            JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
            JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
            JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = CARRETO.ID_CLIENTE)
            JOIN HMOTORIS ON (HMOTORIS.IDHMOTORIS = HVEICULO.IDHMOTORIS)
        WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes_atual AND HMOTORIS.CPF = '$motorista'

        UNION
        SELECT SUM(NOTAFAT.VLR_TOTAL) AS FT, SUM(NOTAFAT.VLR_TOTAL) AS FPESO
        FROM NOTAFAT
            JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTAFAT.ID_HVEICULO)
            JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTAFAT.ID_HVEICULO)
            JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTAFAT.ID_FILIAL)
            JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = NOTAFAT.ID_CLIENTE)
            JOIN HMOTORIS ON (HMOTORIS.IDHMOTORIS = HVEICULO.IDHMOTORIS)
        WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes_atual AND HMOTORIS.CPF = '$motorista'
        AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

        UNION
        SELECT SUM(NOTASER.VALTOTSERV) AS FT, SUM(NOTASER.VALTOTSERV) AS FPESO
        FROM NOTASER
            JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
            JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
            JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTASER.ID_FILIAL)
            JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTASER.IDHCLIENTE)
            JOIN HMOTORIS ON (HMOTORIS.IDHMOTORIS = HVEICULO.IDHMOTORIS)
        WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes_atual AND HMOTORIS.CPF = '$motorista'

        )";
                /*                 * ************************************************
                  //BUSCA DOCUMENTOS                                *
                  /************************************************* */

                $sql_documentos = "SELECT TIPO, FILIAL, NUMERO, DATA, PLACA, CONTRATO, FT, CLIENTE, DESTINO, FPESO FROM
        (SELECT 'CT' AS TIPO, FILIAL.SIGLA_FILIAL AS FILIAL, CT.NUMERO AS NUMERO, CT.DATAEMISSAO AS DATA, HVEICULO.PLACA AS PLACA,
                HVEICEMP.STAFT AS CONTRATO, CT.VALTOTFRETE AS FT, HCLIENTE.RAZAO_SOCIAL AS CLIENTE, CIDADE.NOME_CIDADE AS DESTINO,
                CT.VALFPESOSICMS AS FPESO
        FROM CT
            JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
            JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
            JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
            JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = CT.IDHCLIENTE)
            JOIN HCLIENTE HCD ON (HCD.IDHCLIENTE = CT.IDHCLIENTEDEST)
            JOIN CIDADE ON (CIDADE.ID_CIDADE = HCD.ID_CIDADE)
            JOIN HMOTORIS ON (HMOTORIS.IDHMOTORIS = HVEICULO.IDHMOTORIS)
        WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes_atual AND HMOTORIS.CPF = '$motorista'

        UNION
        SELECT 'CR' AS TIPO, FILIAL.SIGLA_FILIAL AS FILIAL, CARRETO.NUMERO AS NUMERO, CARRETO.DATASAIDA AS DATA, HVEICULO.PLACA AS PLACA,
                HVEICEMP.STAFT AS CONTRATO, CARRETO.VALFRETE AS FT, CLIENTE.RAZAO_SOCIAL AS CLIENTE, CIDADE.NOME_CIDADE AS DESTINO,
                CARRETO.VALFRETE AS FPESO
        FROM CARRETO
            JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
            JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
            JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
            JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = CARRETO.ID_CLIENTE)
            JOIN ROTA ON (ROTA.ID_ROTA = CARRETO.ID_ROTA)
            JOIN CIDADE ON (CIDADE.ID_CIDADE = ROTA.ID_CIDADEDEST)
            JOIN HMOTORIS ON (HMOTORIS.IDHMOTORIS = HVEICULO.IDHMOTORIS)
        WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes_atual AND HMOTORIS.CPF = '$motorista'

        UNION
        SELECT 'NF' AS TIPO, FILIAL.SIGLA_FILIAL AS FILIAL, NOTAFAT.NUMNOTA AS NUMERO, NOTAFAT.DATA_EMIS AS DATA, HVEICULO.PLACA AS PLACA,
                HVEICEMP.STAFT AS CONTRATO, NOTAFAT.VLR_TOTAL AS FT, CLIENTE.RAZAO_SOCIAL AS CLIENTE, '-' AS DESTINO,
                NOTAFAT.VLR_TOTAL AS FPESO
        FROM NOTAFAT
            JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTAFAT.ID_HVEICULO)
            JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTAFAT.ID_HVEICULO)
            JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTAFAT.ID_FILIAL)
            JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = NOTAFAT.ID_CLIENTE)
            JOIN HMOTORIS ON (HMOTORIS.IDHMOTORIS = HVEICULO.IDHMOTORIS)
        WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes_atual AND HMOTORIS.CPF = '$motorista'
        AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

        UNION
        SELECT 'NS' AS TIPO, FILIAL.SIGLA_FILIAL AS FILIAL, NOTASER.NUMERO AS NUMERO, NOTASER.DATAEMIS AS DATA, HVEICULO.PLACA AS PLACA,
                HVEICEMP.STAFT AS CONTRATO, NOTASER.VALTOTSERV AS FT, HCLIENTE.RAZAO_SOCIAL AS CLIENTE, '-' AS DESTINO,
                NOTASER.VALTOTSERV AS FPESO
        FROM NOTASER
            JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
            JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
            JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTASER.ID_FILIAL)
            JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTASER.IDHCLIENTE)
            JOIN HMOTORIS ON (HMOTORIS.IDHMOTORIS = HVEICULO.IDHMOTORIS)
        WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes_atual AND HMOTORIS.CPF = '$motorista'
        )

        UNION ALL

        SELECT 'VZ' AS TIPO, '' AS FILIAL, Z.ID_VIAGVZ AS NUMERO, Z.DATACAD AS DATA, V.PLACA AS PLACA,
                E.STAFT AS CONTRATO, 0 AS FT, '' AS CLIENTE, D.NOME_CIDADE AS DESTINO,
                0 AS FPESO
        FROM VIAGVZ Z
        JOIN HVEICULO V ON (V.ID_HVEICULO = Z.ID_HVEICULO)
        JOIN HVEICEMP E ON (E.IDHVEICEMP = Z.IDHVEICEMP)
        JOIN HMOTORIS M ON (M.IDHMOTORIS = V.IDHMOTORIS)
        JOIN ROTA R ON R.ID_ROTA = Z.ID_ROTA
        JOIN CIDADE D ON D.ID_CIDADE = R.ID_CIDADEDEST
        WHERE YEAR(Z.DATACAD) = $ano AND MONTH(Z.DATACAD) = $mes_atual AND M.CPF = '$motorista'

        ORDER BY DATA DESC";
            }

            $db2_total = db2_exec($hDbcDB2, $sql_total);
            $dados_total = db2_fetch_array($db2_total);
//************************************************************

            $db2_documentos = db2_exec($hDbcDB2, $sql_documentos);

            while ($dados_documentos = db2_fetch_array($db2_documentos)){
                //LINHA DA TABELA
                $dados_documentos[6] = str_replace(',', '.', $dados_documentos[6]);
                $dados_documentos[6] = number_format($dados_documentos[6], 2, ',', '.');
                $dados_documentos[9] = str_replace(',', '.', $dados_documentos[9]);
                $dados_documentos[9] = number_format($dados_documentos[9], 2, ',', '.');
                $linhaTabela = $linhaTabela .
                    "<tr class='odd gradeX'>
                <td>" . $dados_documentos[0] . "</td>
                <td align='right'>$dados_documentos[1] $dados_documentos[2]</td>
                <td align='right'>$dados_documentos[3]</td>
                <td align='right'>$dados_documentos[4]</td>
                <td align='right'>$dados_documentos[5]</td>
                <td align='right'>$dados_documentos[6]</td>
                <td align='right'>$dados_documentos[9]</td>
                <td align='right'>$dados_documentos[8]</td>
                <td align='right'>$dados_documentos[7]</td>
            </tr>";
            }
//LINHA CUMULATIVO
            $linhaCumulativo = "<tr class='odd gradeX'>
                        <td align='right'>TOTAL VALOR FRETE</td>
                        <td align='right'>$dados_total[0]</td>
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
                        <?php echo $link_detalhado; ?>
                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Relatorio Documentos</h3>
                            </div>

                            <div class="widget-content">

                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>TIPO</th>
                                            <th>DOCUMENTO</th>
                                            <th>DATA EMISSAO</th>
                                            <th>PLACA</th>
                                            <th>CONTRATO</th>
                                            <th>TOTAL FRETE</th>
                                            <th>TOTAL PESO</th>
                                            <th>DESTINO</th>
                                            <th>CLIENTE</th>
                                        </tr>
                                    </thead>
                                    <tbody>
<?php echo $linhaTabela; ?>
                                    </tbody>
                                </table>


                            </div> <!-- .widget-content -->

                        </div> <!-- .widget -->

                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Cumulativo</h3>
                            </div>

                            <div class="widget-content">

                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>VALOR TOTAL FRETE</th>
                                            <th>VALOR TOTAL FRETE PESO</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <td><?php echo number_format($dados_total[0], 2, ',', '.'); ?></td>
                                    <td><?php echo number_format($dados_total[1], 2, ',', '.'); ?></td>
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