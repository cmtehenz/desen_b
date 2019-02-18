<?php
    include $_SERVER['DOCUMENT_ROOT'] . '/old/funcoesDB2.php';

    /******************************************
    *   TOTAL RECEITA PREVISTO ANO            *
    *******************************************/
    function receitaPrevistoAno($ano){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        $script_orcamento = "SELECT SUM(ORCAREC.VAL_PREV)
                    FROM
                        ORCAREC
                    WHERE
                        ORCAREC.ANO = $ano
                    ";
         $sql_orcamento = db2_exec($hDbcDB2, $script_orcamento);
         $dados_orcamento = db2_fetch_array($sql_orcamento);
         return $dados_orcamento[0];
    }

    /******************************************
    *   TOTAL RECEITA PREVISTO ANO MES        *
    *******************************************/
    function receitaPrevistoAnoMes($ano, $mes){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        $script_orcamento = "SELECT SUM(ORCAREC.VAL_PREV)
                    FROM
                        ORCAREC
                    WHERE
                        ORCAREC.ANO = $ano
                    AND
                        ORCAREC.MES = $mes
                    ";
        $sql_orcamento = db2_exec($hDbcDB2, $script_orcamento);
        $dados_orcamento = db2_fetch_array($sql_orcamento);
        if($dados_orcamento[0] == null){
            $dados_orcamento[0] = 0;
        }
        return str_replace(",", ".", $dados_orcamento[0]);
    }

    /************************************************
    *CHAVE: ORCAMENTO                               *
    *TOTAL RECEITA PREVISTO ANO MES IDCTCUSTO       *
    *PROGRAMADOR: Gabriel Machado Luis              *
    *DATA: 25/05/2015                               *
    ************************************************/
    function receitaPrevistoAnoMesIdctcusto($ano, $mes, $id){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        $script_orcamento = "SELECT SUM(ORCAREC.VAL_PREV)
                    FROM
                        ORCAREC
                    WHERE
                        ORCAREC.ANO = $ano
                    AND
                        ORCAREC.MES = $mes
                    AND
                        ORCAREC.IDCTCUSTO = $id";
        $sql_orcamento = db2_exec($hDbcDB2, $script_orcamento);
        $dados_orcamento = db2_fetch_array($sql_orcamento);
        if($dados_orcamento[0] == null){
            $dados_orcamento[0] = 0;
        }
        return $dados_orcamento[0];
    }
    
        /************************************************
    *CHAVE: ORCAMENTO                                   *
    *TOTAL RECEITA PREVISTO ANO MES SIGLAFILIAL         *
    *PROGRAMADOR: Gabriel Machado Luis                  *   
    *DATA: 10/07/2017                                   *
    *****************************************************/
    function receitaPrevistoAnoMesFilial($ano, $mes, $idFilial){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        $script_orcamento = "SELECT SUM(ORCAREC.VAL_PREV)
                                FROM FILIAL
                                JOIN ORCAREC ON (ORCAREC.IDCTCUSTO = FILIAL.IDCTCUSTO)
                                    WHERE FILIAL.ID_FILIAL=$idFilial AND ORCAREC.ANO = $ano AND ORCAREC.MES = $mes
                                    GROUP BY FILIAL.SIGLA_FILIAL ";
        $sql_orcamento = db2_exec($hDbcDB2, $script_orcamento);
        $dados_orcamento = db2_fetch_array($sql_orcamento);
        if($dados_orcamento[0] == null){
            $dados_orcamento[0] = 0;
        }
        return $dados_orcamento[0];
    }


    /***************************************************
    *CHAVE: RECEITA                                    *
    *FATURAMENTO RELACAO DE FILIAIS FATURANDO ANO MES  *
    *   IDCUSTO RECEITA DA FILIAL(R$)                  *
    *PROGRAMADOR: Gabriel Machado Luis                 *
    *DATA: 26/05/2015                                  *
    ***************************************************/
    function listaFiliaisFaturando($ano, $mes, $imob){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        $i = 0;
        if(!$imob){
            $sql = "SELECT NOMEFILIAL, IDCUSTO, SUM(FPESO) FROM
                (SELECT CTCUSTO.DESCRICAO AS NOMEFILIAL, CTCUSTO.IDCTCUSTO AS IDCUSTO, SUM(VALTOTFRETE) AS FPESO
                FROM CT
                JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = CT.IDHCLIENTE)
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = CT.IDCTCUSTO)
                WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes
                GROUP BY CTCUSTO.DESCRICAO, CTCUSTO.IDCTCUSTO

                UNION
                SELECT CTCUSTO.DESCRICAO AS NOMEFILIAL, CTCUSTO.IDCTCUSTO AS IDCUSTO, SUM(VALFRETE) AS FPESO
                FROM CARRETO
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = FILIAL.IDCTCUSTO)
                WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes
                GROUP BY CTCUSTO.DESCRICAO, CTCUSTO.IDCTCUSTO

                UNION
                SELECT CTCUSTO.DESCRICAO AS NOMEFILIAL, CTCUSTO.IDCTCUSTO AS IDCUSTO, SUM(NOTASER.VALTOTSERV) AS FPESO
                FROM NOTASER
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTASER.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
                WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes
                GROUP BY CTCUSTO.DESCRICAO, CTCUSTO.IDCTCUSTO

                UNION
                SELECT CTCUSTO.DESCRICAO AS NOMEFILIAL, CTCUSTO.IDCTCUSTO AS IDCUSTO, SUM(NOTADEB.VALOR) AS FPESO
                FROM NOTADEB
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTADEB.ID_FILIAL)
                    JOIN LANCTO ON (LANCTO.NUM_DOCUMENTO = NOTADEB.NUM_DOCUMENTO AND LANCTO.NUM_LOTE = NOTADEB.NUM_LOTE AND LANCTO.NUM_SUBLOTE = NOTADEB.NUM_SUBLOTE AND LANCTO.CCUSTO <> '')
                    JOIN CTCUSTO ON (CTCUSTO.COD_CTCUSTO = LANCTO.CCUSTO)
                WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano AND MONTH(NOTADEB.DATAEMISSAO)=$mes
                GROUP BY CTCUSTO.DESCRICAO, CTCUSTO.IDCTCUSTO
                )GROUP BY NOMEFILIAL, IDCUSTO
                ORDER BY SUM(FPESO) DESC ";
        }
        if($imob){
            $sql = "SELECT NOMEFILIAL, IDCUSTO, SUM(FPESO) FROM
                (SELECT CTCUSTO.DESCRICAO AS NOMEFILIAL, CTCUSTO.IDCTCUSTO AS IDCUSTO, SUM(VALTOTFRETE) AS FPESO
                FROM CT
                JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = CT.IDHCLIENTE)
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = CT.IDCTCUSTO)
                WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes
                GROUP BY CTCUSTO.DESCRICAO, CTCUSTO.IDCTCUSTO

                UNION
                SELECT CTCUSTO.DESCRICAO AS NOMEFILIAL, CTCUSTO.IDCTCUSTO AS IDCUSTO, SUM(VALFRETE) AS FPESO
                FROM CARRETO
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = FILIAL.IDCTCUSTO)
                WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes
                GROUP BY CTCUSTO.DESCRICAO, CTCUSTO.IDCTCUSTO

                UNION
                SELECT CTCUSTO.DESCRICAO AS NOMEFILIAL, CTCUSTO.IDCTCUSTO AS IDCUSTO, SUM(VLR_TOTAL) AS FPESO
                FROM NOTAFAT
                JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = NOTAFAT.ID_CLIENTE)
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTAFAT.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTAFAT.IDCTCUSTO)
                WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes
                AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')
                GROUP BY CTCUSTO.DESCRICAO, CTCUSTO.IDCTCUSTO

                UNION
                SELECT CTCUSTO.DESCRICAO AS NOMEFILIAL, CTCUSTO.IDCTCUSTO AS IDCUSTO, SUM(NOTASER.VALTOTSERV) AS FPESO
                FROM NOTASER
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTASER.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
                WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes
                GROUP BY CTCUSTO.DESCRICAO, CTCUSTO.IDCTCUSTO

                UNION
                SELECT CTCUSTO.DESCRICAO AS NOMEFILIAL, CTCUSTO.IDCTCUSTO AS IDCUSTO, SUM(NOTADEB.VALOR) AS FPESO
                FROM NOTADEB
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTADEB.ID_FILIAL)
                    JOIN LANCTO ON (LANCTO.NUM_DOCUMENTO = NOTADEB.NUM_DOCUMENTO AND LANCTO.NUM_LOTE = NOTADEB.NUM_LOTE AND LANCTO.NUM_SUBLOTE = NOTADEB.NUM_SUBLOTE AND LANCTO.CCUSTO <> '')
                    JOIN CTCUSTO ON (CTCUSTO.COD_CTCUSTO = LANCTO.CCUSTO)
                WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano AND MONTH(NOTADEB.DATAEMISSAO)=$mes
                GROUP BY CTCUSTO.DESCRICAO, CTCUSTO.IDCTCUSTO
                )GROUP BY NOMEFILIAL, IDCUSTO
                ORDER BY SUM(FPESO) DESC ";
        }

        $db2 = db2_exec($hDbcDB2, $sql);
        while($dados = db2_fetch_array($db2)){
            $registro[$i][NOMEFILIAL] = $dados[0];
            $registro[$i][IDCUSTO] = $dados[1];
            $registro[$i][FPESO] = $dados[2];
            $i++;
        }

        return $registro;
    }

    /***************************************************
    *CHAVE: RECEITA                                    *
    *LISTA DE CLIENTES FATURANDO ANO MES FILIAL        *
    *PROGRAMADOR: Gabriel Machado Luis                 *
    *DATA: 27/05/2015                                  *
    ***************************************************/
    function listaClientesFilial($ano, $mes, $imob, $id){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        $i = 0;
            if(!$imob){
            $sql = "SELECT CNPJ, NOMECLIENTE, SUM(FPESO) FROM
                    (SELECT CAST(HCLIENTE.CNPJ_CPF as VARCHAR(8)) AS CNPJ, HCLIENTE.RAZAO_SOCIAl AS NOMECLIENTE, SUM(VALTOTFRETE) AS FPESO
                    FROM CT
                        JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = CT.IDHCLIENTE)
                        JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
                        JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = CT.IDCTCUSTO)
                    WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes AND CTCUSTO.IDCTCUSTO=$id
                    GROUP BY CAST(HCLIENTE.CNPJ_CPF as VARCHAR(8)), HCLIENTE.RAZAO_SOCIAl

                    UNION
                    SELECT CAST(CLIENTE.CNPJ_CPF as VARCHAR(8)) AS CNPJ, CLIENTE.RAZAO_SOCIAl AS NOMECLIENTE, SUM(VALFRETE) AS FPESO
                    FROM CARRETO
                        JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = CARRETO.ID_CLIENTE)
                        JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
                        JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = FILIAL.IDCTCUSTO)
                    WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes AND CTCUSTO.IDCTCUSTO=$id
                    GROUP BY CAST(CLIENTE.CNPJ_CPF as VARCHAR(8)), CLIENTE.RAZAO_SOCIAl

                    UNION
                    SELECT CAST(HCLIENTE.CNPJ_CPF as VARCHAR(8)) AS CNPJ, HCLIENTE.RAZAO_SOCIAl AS NOMECLIENTE, SUM(NOTASER.VALTOTSERV) AS FPESO
                    FROM NOTASER
                        JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTASER.IDHCLIENTE)
                        JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
                    WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes AND CTCUSTO.IDCTCUSTO=$id
                    GROUP BY CAST(HCLIENTE.CNPJ_CPF as VARCHAR(8)), HCLIENTE.RAZAO_SOCIAl

                    UNION
                    SELECT CAST(HCLIENTE.CNPJ_CPF as VARCHAR(8)) AS CNPJ, HCLIENTE.RAZAO_SOCIAl AS NOMECLIENTE, SUM(NOTADEB.VALOR) AS FPESO
                    FROM NOTADEB
                        JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTADEB.IDHCLIENTE)
                        JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTADEB.ID_FILIAL)
                        JOIN LANCTO ON (LANCTO.NUM_DOCUMENTO = NOTADEB.NUM_DOCUMENTO AND LANCTO.NUM_LOTE = NOTADEB.NUM_LOTE AND LANCTO.NUM_SUBLOTE = NOTADEB.NUM_SUBLOTE AND LANCTO.CCUSTO <> '')
                        JOIN CTCUSTO ON (CTCUSTO.COD_CTCUSTO = LANCTO.CCUSTO)
                    WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano AND MONTH(NOTADEB.DATAEMISSAO)=$mes AND CTCUSTO.IDCTCUSTO=$id
                    GROUP BY CAST(HCLIENTE.CNPJ_CPF as VARCHAR(8)), HCLIENTE.RAZAO_SOCIAl
                    )GROUP BY CNPJ, NOMECLIENTE
                    ORDER BY SUM(FPESO) DESC";
        }
        if($imob){
            $sql = "SELECT CNPJ, NOMECLIENTE, SUM(FPESO) FROM
                    (SELECT CAST(HCLIENTE.CNPJ_CPF as VARCHAR(8)) AS CNPJ, HCLIENTE.RAZAO_SOCIAl AS NOMECLIENTE, SUM(VALTOTFRETE) AS FPESO
                    FROM CT
                        JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = CT.IDHCLIENTE)
                        JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
                        JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = CT.IDCTCUSTO)
                    WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes AND CTCUSTO.IDCTCUSTO=$id
                    GROUP BY CAST(HCLIENTE.CNPJ_CPF as VARCHAR(8)), HCLIENTE.RAZAO_SOCIAl

                    UNION
                    SELECT CAST(CLIENTE.CNPJ_CPF as VARCHAR(8)) AS CNPJ, CLIENTE.RAZAO_SOCIAl AS NOMECLIENTE, SUM(VALFRETE) AS FPESO
                    FROM CARRETO
                        JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = CARRETO.ID_CLIENTE)
                        JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
                        JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = FILIAL.IDCTCUSTO)
                    WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes AND CTCUSTO.IDCTCUSTO=$id
                    GROUP BY CAST(CLIENTE.CNPJ_CPF as VARCHAR(8)), CLIENTE.RAZAO_SOCIAl

                    UNION
                    SELECT CAST(CLIENTE.CNPJ_CPF as VARCHAR(8)) AS CNPJ, CLIENTE.RAZAO_SOCIAl AS NOMECLIENTE, SUM(VLR_TOTAL) AS FPESO
                    FROM NOTAFAT
                        JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = NOTAFAT.ID_CLIENTE)
                        JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTAFAT.ID_FILIAL)
                        JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTAFAT.IDCTCUSTO)
                    WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes AND CTCUSTO.IDCTCUSTO=$id
                    AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')
                    GROUP BY CAST(CLIENTE.CNPJ_CPF as VARCHAR(8)), CLIENTE.RAZAO_SOCIAl

                    UNION
                    SELECT CAST(HCLIENTE.CNPJ_CPF as VARCHAR(8)) AS CNPJ, HCLIENTE.RAZAO_SOCIAl AS NOMECLIENTE, SUM(NOTASER.VALTOTSERV) AS FPESO
                    FROM NOTASER
                        JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTASER.IDHCLIENTE)
                        JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
                    WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes AND CTCUSTO.IDCTCUSTO=$id
                    GROUP BY CAST(HCLIENTE.CNPJ_CPF as VARCHAR(8)), HCLIENTE.RAZAO_SOCIAl

                    UNION
                    SELECT CAST(HCLIENTE.CNPJ_CPF as VARCHAR(8)) AS CNPJ, HCLIENTE.RAZAO_SOCIAl AS NOMECLIENTE, SUM(NOTADEB.VALOR) AS FPESO
                    FROM NOTADEB
                        JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTADEB.IDHCLIENTE)
                        JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTADEB.ID_FILIAL)
                        JOIN LANCTO ON (LANCTO.NUM_DOCUMENTO = NOTADEB.NUM_DOCUMENTO AND LANCTO.NUM_LOTE = NOTADEB.NUM_LOTE AND LANCTO.NUM_SUBLOTE = NOTADEB.NUM_SUBLOTE AND LANCTO.CCUSTO <> '')
                        JOIN CTCUSTO ON (CTCUSTO.COD_CTCUSTO = LANCTO.CCUSTO)
                    WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano AND MONTH(NOTADEB.DATAEMISSAO)=$mes AND CTCUSTO.IDCTCUSTO=$id
                    GROUP BY CAST(HCLIENTE.CNPJ_CPF as VARCHAR(8)), HCLIENTE.RAZAO_SOCIAl
                    )GROUP BY CNPJ, NOMECLIENTE
                    ORDER BY SUM(FPESO) DESC";
        }
        $db2 = db2_exec($hDbcDB2, $sql);
        while($dados = db2_fetch_array($db2)){
            $registro[$i][CNPJ] = $dados[0];
            $registro[$i][NOMECLIENTE] = $dados[1];
            $registro[$i][FRETETOTAL] = $dados[2];
            $i++;
        }

        return $registro;
    }

    function freteTotalAnoMesFilial($ano, $mes, $imob, $id){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        if(!$imob){
            $sql = "SELECT SUM(FPESO) FROM
                (SELECT SUM(VALTOTFRETE) AS FPESO
                FROM CT
                JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = CT.IDHCLIENTE)
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = CT.IDCTCUSTO)
                WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes AND CTCUSTO.IDCTCUSTO=$id

                UNION
                SELECT SUM(VALFRETE) AS FPESO
                FROM CARRETO
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = FILIAL.IDCTCUSTO)
                WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes AND CTCUSTO.IDCTCUSTO=$id

                UNION
                SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
                FROM NOTASER
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTASER.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
                WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes AND CTCUSTO.IDCTCUSTO=$id

                UNION
                SELECT SUM(NOTADEB.VALOR) AS FPESO
                FROM NOTADEB
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTADEB.ID_FILIAL)
                    JOIN LANCTO ON (LANCTO.NUM_DOCUMENTO = NOTADEB.NUM_DOCUMENTO AND LANCTO.NUM_LOTE = NOTADEB.NUM_LOTE AND LANCTO.NUM_SUBLOTE = NOTADEB.NUM_SUBLOTE AND LANCTO.CCUSTO <> '')
                    JOIN CTCUSTO ON (CTCUSTO.COD_CTCUSTO = LANCTO.CCUSTO)
                WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano AND MONTH(NOTADEB.DATAEMISSAO)=$mes AND CTCUSTO.IDCTCUSTO=$id
                )
                ORDER BY SUM(FPESO) DESC ";
        }
        if($imob){
            $sql = "SELECT SUM(FPESO) FROM
                (SELECT SUM(VALTOTFRETE) AS FPESO
                FROM CT
                JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = CT.IDHCLIENTE)
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = CT.IDCTCUSTO)
                WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes AND CTCUSTO.IDCTCUSTO=$id

                UNION
                SELECT SUM(VALFRETE) AS FPESO
                FROM CARRETO
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = FILIAL.IDCTCUSTO)
                WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes AND CTCUSTO.IDCTCUSTO=$id

                UNION
                SELECT SUM(VLR_TOTAL) AS FPESO
                FROM NOTAFAT
                JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = NOTAFAT.ID_CLIENTE)
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTAFAT.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTAFAT.IDCTCUSTO)
                WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes AND CTCUSTO.IDCTCUSTO=$id
                AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

                UNION
                SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
                FROM NOTASER
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTASER.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
                WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes AND CTCUSTO.IDCTCUSTO=$id

                UNION
                SELECT SUM(NOTADEB.VALOR) AS FPESO
                FROM NOTADEB
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTADEB.ID_FILIAL)
                    JOIN LANCTO ON (LANCTO.NUM_DOCUMENTO = NOTADEB.NUM_DOCUMENTO AND LANCTO.NUM_LOTE = NOTADEB.NUM_LOTE AND LANCTO.NUM_SUBLOTE = NOTADEB.NUM_SUBLOTE AND LANCTO.CCUSTO <> '')
                    JOIN CTCUSTO ON (CTCUSTO.COD_CTCUSTO = LANCTO.CCUSTO)
                WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano AND MONTH(NOTADEB.DATAEMISSAO)=$mes AND CTCUSTO.IDCTCUSTO=$id
                )
                ORDER BY SUM(FPESO) DESC ";
        }

        $db2 = db2_exec($hDbcDB2, $sql);
        $dados = db2_fetch_array($db2);
        return $dados[0];
    }
    
    /***************************************************************
    * Função: fretePesoAnoMesFilial                          *
    * Programador: Gabriel Luis                                    *
    * Data: 10/07/2017                                             *
    * Parâmetros:                                                  *
    *     $ano = ano
    *     $mes = mes
    *     $imob = venda imobilizado
    *     $id - id do centro de custo filial                       *
    * Descrição:                                                   *
    *     Retorna valor total do fretepeso sem icms                *
    ****************************************************************/
    function fretePesoAnoMesFilial($ano, $mes, $imob, $id){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        if(!$imob){
            $sql = "SELECT SUM(FPESO) FROM
                (SELECT SUM(VALFPESOSICMS) AS FPESO
                FROM CT
                JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = CT.IDHCLIENTE)
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = CT.IDCTCUSTO)
                WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes AND CTCUSTO.IDCTCUSTO=$id

                UNION
                SELECT SUM(VALFRETE) AS FPESO
                FROM CARRETO
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = FILIAL.IDCTCUSTO)
                WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes AND CTCUSTO.IDCTCUSTO=$id

                UNION
                SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
                FROM NOTASER
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTASER.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
                WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes AND CTCUSTO.IDCTCUSTO=$id

                UNION
                SELECT SUM(NOTADEB.VALOR) AS FPESO
                FROM NOTADEB
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTADEB.ID_FILIAL)
                    JOIN LANCTO ON (LANCTO.NUM_DOCUMENTO = NOTADEB.NUM_DOCUMENTO AND LANCTO.NUM_LOTE = NOTADEB.NUM_LOTE AND LANCTO.NUM_SUBLOTE = NOTADEB.NUM_SUBLOTE AND LANCTO.CCUSTO <> '')
                    JOIN CTCUSTO ON (CTCUSTO.COD_CTCUSTO = LANCTO.CCUSTO)
                WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano AND MONTH(NOTADEB.DATAEMISSAO)=$mes AND CTCUSTO.IDCTCUSTO=$id
                )
                ORDER BY SUM(FPESO) DESC ";
        }
        if($imob){
            $sql = "SELECT SUM(FPESO) FROM
                (SELECT SUM(VALFPESOSICMS) AS FPESO
                FROM CT
                JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = CT.IDHCLIENTE)
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = CT.IDCTCUSTO)
                WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes AND CTCUSTO.IDCTCUSTO=$id

                UNION
                SELECT SUM(VALFRETE) AS FPESO
                FROM CARRETO
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = FILIAL.IDCTCUSTO)
                WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes AND CTCUSTO.IDCTCUSTO=$id

                UNION
                SELECT SUM(VLR_TOTAL) AS FPESO
                FROM NOTAFAT
                JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = NOTAFAT.ID_CLIENTE)
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTAFAT.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTAFAT.IDCTCUSTO)
                WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes AND CTCUSTO.IDCTCUSTO=$id
                AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

                UNION
                SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
                FROM NOTASER
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTASER.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
                WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes AND CTCUSTO.IDCTCUSTO=$id

                UNION
                SELECT SUM(NOTADEB.VALOR) AS FPESO
                FROM NOTADEB
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTADEB.ID_FILIAL)
                    JOIN LANCTO ON (LANCTO.NUM_DOCUMENTO = NOTADEB.NUM_DOCUMENTO AND LANCTO.NUM_LOTE = NOTADEB.NUM_LOTE AND LANCTO.NUM_SUBLOTE = NOTADEB.NUM_SUBLOTE AND LANCTO.CCUSTO <> '')
                    JOIN CTCUSTO ON (CTCUSTO.COD_CTCUSTO = LANCTO.CCUSTO)
                WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano AND MONTH(NOTADEB.DATAEMISSAO)=$mes AND CTCUSTO.IDCTCUSTO=$id
                )
                ORDER BY SUM(FPESO) DESC ";
        }

        $db2 = db2_exec($hDbcDB2, $sql);
        $dados = db2_fetch_array($db2);
        return $dados[0];
    }

    /************************************************
    *CHAVE: RECEITA                                 *
    *FATURAMENTO REALIZADO ANO MES DIA              *
    *PROGRAMADOR: Gabriel Machado Luis              *
    *DATA: 25/05/2015                               *
    ************************************************/
    function faturamentoAnoMesDia($ano, $mes, $dia, $imob){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        if(!$imob){
            $script_faturamento_dia = "SELECT SUM(FPESO) FROM
                (SELECT SUM(VALFPESOSICMS) AS FPESO
                FROM CT
                WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes AND DAY(CT.DATAEMISSAO)=$dia

                UNION
                SELECT SUM(VALFRETE) AS FPESO
                FROM CARRETO
                WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes AND DAY(CARRETO.DATASAIDA)=$dia

                UNION
                SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
                FROM NOTASER
                WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes AND DAY(NOTASER.DATAEMIS)=$dia

                UNION
                SELECT SUM(NOTADEB.VALOR) AS FPESO
                FROM NOTADEB
                WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano AND MONTH(NOTADEB.DATAEMISSAO)=$mes AND DAY(NOTADEB.DATAEMISSAO)=$dia
                )";
        }
        if($imob){
            $script_faturamento_dia = "SELECT SUM(FPESO) FROM
                (SELECT SUM(VALFPESOSICMS) AS FPESO
                FROM CT
                WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes AND DAY(CT.DATAEMISSAO)=$dia

                UNION
                SELECT SUM(VALFRETE) AS FPESO
                FROM CARRETO
                WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes AND DAY(CARRETO.DATASAIDA)=$dia

                UNION
                SELECT SUM(VLR_TOTAL) AS FPESO
                FROM NOTAFAT
                WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes AND DAY(NOTAFAT.DATA_EMIS)=$dia
                AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

                UNION
                SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
                FROM NOTASER
                WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes AND DAY(NOTASER.DATAEMIS)=$dia

                UNION
                SELECT SUM(NOTADEB.VALOR) AS FPESO
                FROM NOTADEB
                WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano AND MONTH(NOTADEB.DATAEMISSAO)=$mes AND DAY(NOTADEB.DATAEMISSAO)=$dia
                )";
        }

        $db2_faturamento_dia = db2_exec($hDbcDB2, $script_faturamento_dia);
        $dados_faturamento_dia = db2_fetch_array($db2_faturamento_dia);
        if($dados_faturamento_dia[0] == NULL){
            $dados_faturamento_dia[0] = 0;
        }
        return str_replace(",", ".", $dados_faturamento_dia[0]);
    }

    /************************************************
    *CHAVE: RECEITA                                 *
    *FATURAMENTO REALIZADO ANO MES DIA FILIAL       *
    *PROGRAMADOR: Gabriel Machado Luis              *
    *DATA: 27/05/2015                               *
    ************************************************/
    function faturamentoAnoMesDiaFilial($ano, $mes, $dia, $imob, $id){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        if(!$imob){
            $sql = "SELECT SUM(FPESO) FROM
                (SELECT SUM(VALTOTFRETE) AS FPESO
                FROM CT
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = CT.IDCTCUSTO)
                WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes AND DAY(CT.DATAEMISSAO)=$dia AND CT.IDCTCUSTO=$id

                UNION
                SELECT SUM(VALFRETE) AS FPESO
                FROM CARRETO
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = FILIAL.IDCTCUSTO)
                WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes AND DAY(CARRETO.DATASAIDA)=$dia AND CTCUSTO.IDCTCUSTO=$id

                UNION
                SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
                FROM NOTASER
                JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTASER.IDHCLIENTE)
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTASER.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
                WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes AND DAY(NOTASER.DATAEMIS)=$dia AND CTCUSTO.IDCTCUSTO=$id

                UNION
                SELECT SUM(NOTADEB.VALOR) AS FPESO
                FROM NOTADEB
                JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTADEB.ID_FILIAL)
                JOIN LANCTO ON (LANCTO.NUM_DOCUMENTO = NOTADEB.NUM_DOCUMENTO AND LANCTO.NUM_LOTE = NOTADEB.NUM_LOTE AND LANCTO.NUM_SUBLOTE = NOTADEB.NUM_SUBLOTE AND LANCTO.CCUSTO <> '')
                JOIN CTCUSTO ON (CTCUSTO.COD_CTCUSTO = LANCTO.CCUSTO)
                WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano AND MONTH(NOTADEB.DATAEMISSAO)=$mes AND DAY(NOTADEB.DATAEMISSAO)=$dia AND CTCUSTO.IDCTCUSTO=$id
                )";
        }
        if($imob){
            $sql = "SELECT SUM(FPESO) FROM
                (SELECT SUM(VALTOTFRETE) AS FPESO
                FROM CT
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = CT.IDCTCUSTO)
                WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes AND DAY(CT.DATAEMISSAO)=$dia AND CT.IDCTCUSTO=$id

                UNION
                SELECT SUM(VALFRETE) AS FPESO
                FROM CARRETO
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = FILIAL.IDCTCUSTO)
                WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes AND DAY(CARRETO.DATASAIDA)=$dia AND CTCUSTO.IDCTCUSTO=$id

                UNION
                SELECT SUM(VLR_TOTAL) AS FPESO
                FROM NOTAFAT
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTAFAT.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTAFAT.IDCTCUSTO)
                WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes AND DAY(NOTAFAT.DATA_EMIS)=$dia AND CTCUSTO.IDCTCUSTO=$id
                AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

                UNION
                SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
                FROM NOTASER
                JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTASER.IDHCLIENTE)
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTASER.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
                WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes AND DAY(NOTASER.DATAEMIS)=$dia AND CTCUSTO.IDCTCUSTO=$id

                UNION
                SELECT SUM(NOTADEB.VALOR) AS FPESO
                FROM NOTADEB
                JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTADEB.ID_FILIAL)
                JOIN LANCTO ON (LANCTO.NUM_DOCUMENTO = NOTADEB.NUM_DOCUMENTO AND LANCTO.NUM_LOTE = NOTADEB.NUM_LOTE AND LANCTO.NUM_SUBLOTE = NOTADEB.NUM_SUBLOTE AND LANCTO.CCUSTO <> '')
                JOIN CTCUSTO ON (CTCUSTO.COD_CTCUSTO = LANCTO.CCUSTO)
                WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano AND MONTH(NOTADEB.DATAEMISSAO)=$mes AND DAY(NOTADEB.DATAEMISSAO)=$dia AND CTCUSTO.IDCTCUSTO=$id
                )";
        }
        $db2 = db2_exec($hDbcDB2, $sql);
        $dados = db2_fetch_array($db2);
        if($dados[0] == NULL){
            $dados[0] = 0;
        }
        return $dados[0];
    }

    /******************************************
    *   FATURAMENTO REALIZADO ANO MES         *
    *******************************************/
    function faturamentoAnoMes($ano, $mes, $imob){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        if(!$imob){
            $script_faturamento = "SELECT SUM(FPESO) FROM
                (SELECT SUM(VALTOTFRETE) AS FPESO
                FROM CT
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = CT.IDCTCUSTO)
                WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes

                UNION
                SELECT SUM(VALFRETE) AS FPESO
                FROM CARRETO
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = FILIAL.IDCTCUSTO)
                WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes

                UNION
                SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
                FROM NOTASER
                JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTASER.IDHCLIENTE)
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTASER.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
                WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes

                UNION
                SELECT SUM(NOTADEB.VALOR) AS FPESO
                FROM NOTADEB
                JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTADEB.ID_FILIAL)
                WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano AND MONTH(NOTADEB.DATAEMISSAO)=$mes
                )";
        }
        if($imob){
            $script_faturamento = "SELECT SUM(FPESO) FROM
                (SELECT SUM(VALTOTFRETE) AS FPESO
                FROM CT
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = CT.IDCTCUSTO)
                WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes

                UNION
                SELECT SUM(VALFRETE) AS FPESO
                FROM CARRETO
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = FILIAL.IDCTCUSTO)
                WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes

                UNION
                SELECT SUM(VLR_TOTAL) AS FPESO
                FROM NOTAFAT
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTAFAT.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTAFAT.IDCTCUSTO)
                WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes
                AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

                UNION
                SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
                FROM NOTASER
                JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTASER.IDHCLIENTE)
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTASER.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
                WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes

                UNION
                SELECT SUM(NOTADEB.VALOR) AS FPESO
                FROM NOTADEB
                JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTADEB.ID_FILIAL)
                WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano AND MONTH(NOTADEB.DATAEMISSAO)=$mes
                )";
        }

        $db2_faturamento = db2_exec($hDbcDB2, $script_faturamento);
        $dados_faturamento = db2_fetch_array($db2_faturamento);

        if($dados_faturamento[0] == NULL){
            $dados_faturamento[0] = 0;
        }

        return str_replace(",", ".", $dados_faturamento[0]);
    }

    /******************************************
    *   FATURAMENTO REALIZADO ANO             *
    *******************************************/
    function faturamentoAno($ano, $imob){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        if(!$imob){
            $script_faturamento = "SELECT SUM(FPESO) FROM
                (SELECT SUM(VALTOTFRETE) AS FPESO
                FROM CT
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = CT.IDCTCUSTO)
                WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano

                UNION
                SELECT SUM(VALFRETE) AS FPESO
                FROM CARRETO
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = FILIAL.IDCTCUSTO)
                WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano

                UNION
                SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
                FROM NOTASER
                JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTASER.IDHCLIENTE)
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTASER.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
                WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano

                UNION
                SELECT SUM(NOTADEB.VALOR) AS FPESO
                FROM NOTADEB
                JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTADEB.ID_FILIAL)
                WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano
                )";
        }
        if($imob){
            $script_faturamento = "SELECT SUM(FPESO) FROM
                (SELECT SUM(VALTOTFRETE) AS FPESO
                FROM CT
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = CT.IDCTCUSTO)
                WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano

                UNION
                SELECT SUM(VALFRETE) AS FPESO
                FROM CARRETO
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = FILIAL.IDCTCUSTO)
                WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano

                UNION
                SELECT SUM(VLR_TOTAL) AS FPESO
                FROM NOTAFAT
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTAFAT.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTAFAT.IDCTCUSTO)
                WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano
                AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

                UNION
                SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
                FROM NOTASER
                JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTASER.IDHCLIENTE)
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTASER.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
                WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano

                UNION
                SELECT SUM(NOTADEB.VALOR) AS FPESO
                FROM NOTADEB
                JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTADEB.ID_FILIAL)
                WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano
                )";
        }
        $db2_faturamento = db2_exec($hDbcDB2, $script_faturamento);
        $dados_faturamento = db2_fetch_array($db2_faturamento);
        if($dados_faturamento[0] == NULL){
            $dados_faturamento[0] = 0;
        }
        return str_replace(",", ".", $dados_faturamento[0]);
    }

    /******************************************
    *   FATURAMENTO REALIZADO ANO MES AFTO    *
    *******************************************/
    function faturamentoAnoMesAFTO($ano, $mes, $afto, $imob){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        if(!$imob){
            $sql = "SELECT SUM(FPESO) FROM
            (SELECT SUM(VALTOTFRETE) AS FPESO
            FROM CT
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
            WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes AND HVEICEMP.STAFT='$afto'

            UNION
            SELECT SUM(VALFRETE) AS FPESO
            FROM CARRETO
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
            WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes AND HVEICEMP.STAFT='$afto'

            UNION
            SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
            FROM NOTASER
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
            WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes AND HVEICEMP.STAFT='$afto'

            )";
        }
        if($imob){
            $sql = "SELECT SUM(FPESO) FROM
            (SELECT SUM(VALTOTFRETE) AS FPESO
            FROM CT
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
            WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes AND HVEICEMP.STAFT='$afto'

            UNION
            SELECT SUM(VALFRETE) AS FPESO
            FROM CARRETO
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
            WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes AND HVEICEMP.STAFT='$afto'

            UNION
            SELECT SUM(VLR_TOTAL) AS FPESO
            FROM NOTAFAT
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTAFAT.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTAFAT.ID_HVEICULO)
            WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes AND HVEICEMP.STAFT='$afto'
            AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

            UNION
            SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
            FROM NOTASER
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
            WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes AND HVEICEMP.STAFT='$afto'

            )";
        }
        $db2 = db2_exec($hDbcDB2, $sql);
        $dados = db2_fetch_array($db2);
        if($dados[0] == NULL){
            $dados[0] = 0;
        }
        return $dados[0];
    }

    /******************************************
    *   FATURAMENTO FRETE PESO ANO MES AFT    *
    *******************************************/
    function fretePesoAnoMesAFTO($ano, $mes, $afto, $imob){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        if(!$imob){
            $sql = "SELECT SUM(FPESO) FROM
            (SELECT SUM(VALFPESOSICMS) AS FPESO
            FROM CT
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
            WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes AND HVEICEMP.STAFT='$afto'

            UNION
            SELECT SUM(VALFRETE) AS FPESO
            FROM CARRETO
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
            WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes AND HVEICEMP.STAFT='$afto'

            UNION
            SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
            FROM NOTASER
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
            WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes AND HVEICEMP.STAFT='$afto'

            )";
        }
        if($imob){
            $sql = "SELECT SUM(FPESO) FROM
            (SELECT SUM(VALFPESOSICMS) AS FPESO
            FROM CT
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
            WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes AND HVEICEMP.STAFT='$afto'

            UNION
            SELECT SUM(VALFRETE) AS FPESO
            FROM CARRETO
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
            WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes AND HVEICEMP.STAFT='$afto'

            UNION
            SELECT SUM(VLR_TOTAL) AS FPESO
            FROM NOTAFAT
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTAFAT.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTAFAT.ID_HVEICULO)
            WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes AND HVEICEMP.STAFT='$afto'
            AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

            UNION
            SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
            FROM NOTASER
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
            WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes AND HVEICEMP.STAFT='$afto'

            )";
        }
        $db2 = db2_exec($hDbcDB2, $sql);
        $dados = db2_fetch_array($db2);
        if($dados[0] == NULL){
            $dados[0] = 0;
        }
        return $dados[0];
    }

    /*************************************************
    *   FATURAMENTO REALIZADO ANO OUTROS "SEM PLACA" *
    *************************************************/
    function faturamentoAnoMesOutros($ano, $mes, $imob){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        if(!$imob){
            $sql = "SELECT SUM(FPESO) FROM
                (SELECT SUM(CT.VALTOTFRETE) AS FPESO
                FROM CT
                WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes AND CT.ID_HVEICULO IS NULL

                UNION
                SELECT SUM(VALFRETE) AS FPESO
                FROM CARRETO
                WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes AND CARRETO.ID_HVEICULO IS NULL

                UNION
                SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
                FROM NOTASER
                WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes AND NOTASER.ID_HVEICULO IS NULL

                UNION
                SELECT SUM(NOTADEB.VALOR) AS FPESO
                FROM NOTADEB
                WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano AND MONTH(NOTADEB.DATAEMISSAO)=$mes

                )";
        }
        if($imob){
            $sql = "SELECT SUM(FPESO) FROM
                (SELECT SUM(CT.VALTOTFRETE) AS FPESO
                FROM CT
                WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes AND CT.ID_HVEICULO IS NULL

                UNION
                SELECT SUM(VALFRETE) AS FPESO
                FROM CARRETO
                WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes AND CARRETO.ID_HVEICULO IS NULL

                UNION
                SELECT SUM(VLR_TOTAL) AS FPESO
                FROM NOTAFAT
                WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes AND NOTAFAT.ID_VEICULO IS NULL
                AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

                UNION
                SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
                FROM NOTASER
                WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes AND NOTASER.ID_HVEICULO IS NULL

                UNION
                SELECT SUM(NOTADEB.VALOR) AS FPESO
                FROM NOTADEB
                WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano AND MONTH(NOTADEB.DATAEMISSAO)=$mes

                )";
        }
        $db2 = db2_exec($hDbcDB2, $sql);
        $dados = db2_fetch_array($db2);
        if($dados[0] == NULL){
            $dados[0] = 0;
        }
        return $dados[0];
    }

    /************************************************************
    *   FATURAMENTO REALIZADO ANO OUTROS "SEM PLACA" POR FILIAL *
    ************************************************************/
    function faturamentoAnoMesOutrosFilial($ano, $mes, $imob, $id){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        if(!$imob){
            $sql = "SELECT SUM(FPESO) FROM
                (SELECT SUM(CT.VALTOTFRETE) AS FPESO
                FROM CT
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = CT.IDCTCUSTO)
                WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes AND CT.ID_HVEICULO IS NULL AND CTCUSTO.IDCTCUSTO=$id

                UNION
                SELECT SUM(VALFRETE) AS FPESO
                FROM CARRETO
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = FILIAL.IDCTCUSTO)
                WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes AND CARRETO.ID_HVEICULO IS NULL AND CTCUSTO.IDCTCUSTO=$id

                UNION
                SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
                FROM NOTASER
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
                WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes AND NOTASER.ID_HVEICULO IS NULL AND CTCUSTO.IDCTCUSTO=$id

                UNION
                SELECT SUM(NOTADEB.VALOR) AS FPESO
                FROM NOTADEB
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTADEB.ID_FILIAL)
                    JOIN LANCTO ON (LANCTO.NUM_DOCUMENTO = NOTADEB.NUM_DOCUMENTO AND LANCTO.NUM_LOTE = NOTADEB.NUM_LOTE AND LANCTO.NUM_SUBLOTE = NOTADEB.NUM_SUBLOTE AND LANCTO.CCUSTO <> '')
                    JOIN CTCUSTO ON (CTCUSTO.COD_CTCUSTO = LANCTO.CCUSTO)
                WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano AND MONTH(NOTADEB.DATAEMISSAO)=$mes AND CTCUSTO.IDCTCUSTO=$id

                )";
        }
        if($imob){
            $sql = "SELECT SUM(FPESO) FROM
                (SELECT SUM(CT.VALTOTFRETE) AS FPESO
                FROM CT
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = CT.IDCTCUSTO)
                WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes AND CT.ID_HVEICULO IS NULL AND CTCUSTO.IDCTCUSTO=$id

                UNION
                SELECT SUM(VALFRETE) AS FPESO
                FROM CARRETO
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = FILIAL.IDCTCUSTO)
                WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes AND CARRETO.ID_HVEICULO IS NULL AND CTCUSTO.IDCTCUSTO=$id

                UNION
                SELECT SUM(VLR_TOTAL) AS FPESO
                FROM NOTAFAT
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTAFAT.IDCTCUSTO)
                WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes AND NOTAFAT.ID_VEICULO IS NULL AND CTCUSTO.IDCTCUSTO=$id
                AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

                UNION
                SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
                FROM NOTASER
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
                WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes AND NOTASER.ID_HVEICULO IS NULL AND CTCUSTO.IDCTCUSTO=$id

                UNION
                SELECT SUM(NOTADEB.VALOR) AS FPESO
                FROM NOTADEB
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTADEB.ID_FILIAL)
                    JOIN LANCTO ON (LANCTO.NUM_DOCUMENTO = NOTADEB.NUM_DOCUMENTO AND LANCTO.NUM_LOTE = NOTADEB.NUM_LOTE AND LANCTO.NUM_SUBLOTE = NOTADEB.NUM_SUBLOTE AND LANCTO.CCUSTO <> '')
                    JOIN CTCUSTO ON (CTCUSTO.COD_CTCUSTO = LANCTO.CCUSTO)
                WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano AND MONTH(NOTADEB.DATAEMISSAO)=$mes AND CTCUSTO.IDCTCUSTO=$id

                )";
        }
        $db2 = db2_exec($hDbcDB2, $sql);
        $dados = db2_fetch_array($db2);
        if($dados[0] == NULL){
            $dados[0] = 0;
        }
        return $dados[0];
    }

    function freteTotalAnoMesAFTOFilial($ano, $mes, $afto, $imob, $id){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        if(!$imob){
            $sql = "SELECT SUM(FPESO) FROM
            (SELECT SUM(VALTOTFRETE) AS FPESO
            FROM CT
                JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
                JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = CT.IDCTCUSTO)
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
            WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes AND HVEICEMP.STAFT='$afto' AND CTCUSTO.IDCTCUSTO=$id

            UNION
            SELECT SUM(VALFRETE) AS FPESO
            FROM CARRETO
                JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
                JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = FILIAL.IDCTCUSTO)
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
            WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes AND HVEICEMP.STAFT='$afto' AND CTCUSTO.IDCTCUSTO=$id

            UNION
            SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
            FROM NOTASER
                JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTASER.ID_FILIAL)
                JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
            WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes AND HVEICEMP.STAFT='$afto' AND CTCUSTO.IDCTCUSTO=$id

            )";
        }
        if($imob){
            $sql = "SELECT SUM(FPESO) FROM
            (SELECT SUM(VALTOTFRETE) AS FPESO
            FROM CT
                JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
                JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = CT.IDCTCUSTO)
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
            WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes AND HVEICEMP.STAFT='$afto' AND CTCUSTO.IDCTCUSTO=$id

            UNION
            SELECT SUM(VALFRETE) AS FPESO
            FROM CARRETO
                JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
                JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = FILIAL.IDCTCUSTO)
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
            WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes AND HVEICEMP.STAFT='$afto' AND CTCUSTO.IDCTCUSTO=$id

            UNION
            SELECT SUM(VLR_TOTAL) AS FPESO
            FROM NOTAFAT
                JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTAFAT.ID_FILIAL)
                JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTAFAT.IDCTCUSTO)
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTAFAT.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTAFAT.ID_HVEICULO)
            WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes AND HVEICEMP.STAFT='$afto' AND CTCUSTO.IDCTCUSTO=$id
            AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

            UNION
            SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
            FROM NOTASER
                JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTASER.ID_FILIAL)
                JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
            WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes AND HVEICEMP.STAFT='$afto' AND CTCUSTO.IDCTCUSTO=$id

            )";
        }
        $db2 = db2_exec($hDbcDB2, $sql);
        $dados = db2_fetch_array($db2);
        if($dados[0] == NULL){
            $dados[0] = 0;
        }
        return $dados[0];
    }
    
    
    /***************************************************************
    * Função: fretePesoAnoMesAFTOidFilial                          *
    * Programador: Gabriel Luis                                    *
    * Data: 10/07/2017                                             *
    * Parâmetros:                                                  *
    *     $ano = ano
    *     $mes = mes
    *     $afto = Agregado, Frota, teceiro e OUTROS
    *     $imob = venda imobilizado
    *     $idFilial - id da filial                                 *
    * Descrição:                                                   *
    *     Retorna valor total do fretepeso sem icms                *
    ****************************************************************/

        function fretePesoAnoMesAFTOidFilial($ano, $mes, $afto, $imob, $idFilial){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        if(!$imob){
            $sql = "SELECT SUM(FPESO) FROM
            (SELECT SUM(VALFPESOSICMS) AS FPESO
            FROM CT
                JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
                JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = CT.IDCTCUSTO)
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
            WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes AND HVEICEMP.STAFT='$afto' AND FILIAL.ID_FILIAL=$idFilial

            UNION
            SELECT SUM(VALFRETE) AS FPESO
            FROM CARRETO
                JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
                JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = FILIAL.IDCTCUSTO)
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
            WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes AND HVEICEMP.STAFT='$afto' AND FILIAL.ID_FILIAL=$idFilial

            UNION
            SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
            FROM NOTASER
                JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTASER.ID_FILIAL)
                JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
            WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes AND HVEICEMP.STAFT='$afto' AND FILIAL.ID_FILIAL=$idFilial

            )";
        }
        if($imob){
            $sql = "SELECT SUM(FPESO) FROM
            (SELECT SUM(VALFPESOSICMS) AS FPESO
            FROM CT
                JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
                JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = CT.IDCTCUSTO)
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
            WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes AND HVEICEMP.STAFT='$afto' AND FILIAL.ID_FILIAL=$idFilial

            UNION
            SELECT SUM(VALFRETE) AS FPESO
            FROM CARRETO
                JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
                JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = FILIAL.IDCTCUSTO)
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
            WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes AND HVEICEMP.STAFT='$afto' AND FILIAL.ID_FILIAL=$idFilial

            UNION
            SELECT SUM(VLR_TOTAL) AS FPESO
            FROM NOTAFAT
                JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTAFAT.ID_FILIAL)
                JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTAFAT.IDCTCUSTO)
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTAFAT.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTAFAT.ID_HVEICULO)
            WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes AND HVEICEMP.STAFT='$afto' AND FILIAL.ID_FILIAL=$idFilial
            AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

            UNION
            SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
            FROM NOTASER
                JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTASER.ID_FILIAL)
                JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
            WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes AND HVEICEMP.STAFT='$afto' AND FILIAL.ID_FILIAL=$idFilial

            )";
        }
        $db2 = db2_exec($hDbcDB2, $sql);
        $dados = db2_fetch_array($db2);
        if($dados[0] == NULL){
            $dados[0] = 0;
        }
        return $dados[0];
    }
    
    /***************************************************************
    * Função: fretePesoAnoMesAFTOidFilial                          *
    * Programador: Gabriel Luis                                    *
    * Data: 10/07/2017                                             *
    * Parâmetros:                                                  *
    *     $ano = ano
    *     $mes = mes
    *     $imob = venda imobilizado
    *     $idFilial - id da filial                                 *
    * Descrição:                                                   *
    *     Retorna valor total do fretepeso sem icms da filail no   *
     * mes e ano                                                    *
    ****************************************************************/
        function fretePesoAnoMesidFilial($ano, $mes, $imob, $idFilial){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        if(!$imob){
            $sql = "SELECT SUM(FPESO) FROM
            (SELECT SUM(VALFPESOSICMS) AS FPESO
            FROM CT
                JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
                JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = CT.IDCTCUSTO)
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
            WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes AND FILIAL.ID_FILIAL=$idFilial

            UNION
            SELECT SUM(VALFRETE) AS FPESO
            FROM CARRETO
                JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
                JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = FILIAL.IDCTCUSTO)
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
            WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes AND FILIAL.ID_FILIAL=$idFilial

            UNION
            SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
            FROM NOTASER
                JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTASER.ID_FILIAL)
                JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
            WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes AND FILIAL.ID_FILIAL=$idFilial

            )";
        }
        if($imob){
            $sql = "SELECT SUM(FPESO) FROM
            (SELECT SUM(VALFPESOSICMS) AS FPESO
            FROM CT
                JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
                JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = CT.IDCTCUSTO)
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
            WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes AND FILIAL.ID_FILIAL=$idFilial

            UNION
            SELECT SUM(VALFRETE) AS FPESO
            FROM CARRETO
                JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
                JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = FILIAL.IDCTCUSTO)
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
            WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes AND FILIAL.ID_FILIAL=$idFilial

            UNION
            SELECT SUM(VLR_TOTAL) AS FPESO
            FROM NOTAFAT
                JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTAFAT.ID_FILIAL)
                JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTAFAT.IDCTCUSTO)
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTAFAT.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTAFAT.ID_HVEICULO)
            WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes AND FILIAL.ID_FILIAL=$idFilial
            AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

            UNION
            SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
            FROM NOTASER
                JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTASER.ID_FILIAL)
                JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
            WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes AND FILIAL.ID_FILIAL=$idFilial

            )";
        }
        $db2 = db2_exec($hDbcDB2, $sql);
        $dados = db2_fetch_array($db2);
        if($dados[0] == NULL){
            $dados[0] = 0;
        }
        return $dados[0];
    }
    
    /***************************************************************
    * Função: fretePesoAnoMesOutrosidFilial                        *
    * Programador: Gabriel Luis                                    *
    * Data: 10/07/2017                                             *
    * Parâmetros:                                                  *
    *     $ano = ano
    *     $mes = mes
    *     $afto = Agregado, Frota, teceiro e OUTROS
    *     $imob = Outros sem placa
    *     $idFilial - id da filial                                 *
    * Descrição:                                                   *
    *     Retorna valor total do fretepeso sem icms                *
    ****************************************************************/
    function faturamentoAnoMesOutrosidFilial($ano, $mes, $imob, $idFilial){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        if(!$imob){
            $sql = "SELECT SUM(FPESO) FROM
                (SELECT SUM(CT.VALTOTFRETE) AS FPESO
                FROM CT
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = CT.IDCTCUSTO)
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
                WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes AND CT.ID_HVEICULO IS NULL AND FILIAL.ID_FILIAL=$idFilial

                UNION
                SELECT SUM(VALFRETE) AS FPESO
                FROM CARRETO
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = FILIAL.IDCTCUSTO)
                WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes AND CARRETO.ID_HVEICULO IS NULL AND FILIAL.ID_FILIAL=$idFilial

                UNION
                SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
                FROM NOTASER
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTASER.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
                WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes AND NOTASER.ID_HVEICULO IS NULL AND FILIAL.ID_FILIAL=$idFilial

                UNION
                SELECT SUM(NOTADEB.VALOR) AS FPESO
                FROM NOTADEB
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTADEB.ID_FILIAL)
                    JOIN LANCTO ON (LANCTO.NUM_DOCUMENTO = NOTADEB.NUM_DOCUMENTO AND LANCTO.NUM_LOTE = NOTADEB.NUM_LOTE AND LANCTO.NUM_SUBLOTE = NOTADEB.NUM_SUBLOTE AND LANCTO.CCUSTO <> '')
                    JOIN CTCUSTO ON (CTCUSTO.COD_CTCUSTO = LANCTO.CCUSTO)
                WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano AND MONTH(NOTADEB.DATAEMISSAO)=$mes AND FILIAL.ID_FILIAL=$idFilial

                )";
        }
        if($imob){
            $sql = "SELECT SUM(FPESO) FROM
                (SELECT SUM(CT.VALTOTFRETE) AS FPESO
                FROM CT
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = CT.IDCTCUSTO)
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
                WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes AND CT.ID_HVEICULO IS NULL AND FILIAL.ID_FILIAL=$idFilial

                UNION
                SELECT SUM(VALFRETE) AS FPESO
                FROM CARRETO
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = FILIAL.IDCTCUSTO)
                WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes AND CARRETO.ID_HVEICULO IS NULL AND FILIAL.ID_FILIAL=$idFilial

                UNION
                SELECT SUM(VLR_TOTAL) AS FPESO
                FROM NOTAFAT
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTAFAT.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTAFAT.IDCTCUSTO)
                WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes AND NOTAFAT.ID_VEICULO IS NULL AND FILIAL.ID_FILIAL=$idFilial
                AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

                UNION
                SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
                FROM NOTASER
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTASER.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
                WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes AND NOTASER.ID_HVEICULO IS NULL AND FILIAL.ID_FILIAL=$idFilial

                UNION
                SELECT SUM(NOTADEB.VALOR) AS FPESO
                FROM NOTADEB
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTADEB.ID_FILIAL)
                    JOIN LANCTO ON (LANCTO.NUM_DOCUMENTO = NOTADEB.NUM_DOCUMENTO AND LANCTO.NUM_LOTE = NOTADEB.NUM_LOTE AND LANCTO.NUM_SUBLOTE = NOTADEB.NUM_SUBLOTE AND LANCTO.CCUSTO <> '')
                    JOIN CTCUSTO ON (CTCUSTO.COD_CTCUSTO = LANCTO.CCUSTO)
                WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano AND MONTH(NOTADEB.DATAEMISSAO)=$mes AND FILIAL.ID_FILIAL=$idFilial

                )";
        }
        $db2 = db2_exec($hDbcDB2, $sql);
        $dados = db2_fetch_array($db2);
        if($dados[0] == NULL){
            $dados[0] = 0;
        }
        return $dados[0];
    }

    /*******************************************
     *    RELACAO DE PLACAS FATURANDO NO MES   *
     *******************************************/
    function listaPlacasFaturando($ano, $mes_atual, $afto, $imob){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        $i = 0;
        if(!$imob){
            $script = "SELECT ID, PL,MODE, SUM(FPESO), SUM(FRETEP) FROM
                (SELECT HVEICULO.ID_VEICULO AS ID, HVEICULO.PLACA AS PL, MODELO.NAME AS MODE, SUM(VALTOTFRETE) AS FPESO, SUM(CT.VALFPESOSICMS) AS FRETEP
                FROM CT
                    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
                    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
                    JOIN MODELO ON (HVEICULO.CODEMODE = MODELO.CODEMODE)
                WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes_atual AND HVEICEMP.STAFT='$afto'
                GROUP BY HVEICULO.ID_VEICULO, HVEICULO.PLACA, MODELO.NAME
                UNION
                SELECT HVEICULO.ID_VEICULO AS ID, HVEICULO.PLACA AS PL, MODELO.NAME AS MODE,SUM(VALFRETE) AS FPESO, SUM(CARRETO.VALFRETE) AS FRETEP
                FROM CARRETO
                    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
                    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
                    JOIN MODELO ON (HVEICULO.CODEMODE = MODELO.CODEMODE)
                WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes_atual AND HVEICEMP.STAFT='$afto'
                GROUP BY HVEICULO.ID_VEICULO, HVEICULO.PLACA, MODELO.NAME

                UNION
                SELECT HVEICULO.ID_VEICULO AS ID, HVEICULO.PLACA AS PL, MODELO.NAME AS MODE, SUM(NOTASER.VALTOTSERV) AS FPESO, SUM(NOTASER.VALTOTSERV) AS FRETEP
                FROM NOTASER
                    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
                    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
                    JOIN MODELO ON (HVEICULO.CODEMODE = MODELO.CODEMODE)
                WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes_atual AND HVEICEMP.STAFT='$afto'
                GROUP BY HVEICULO.ID_VEICULO, HVEICULO.PLACA, MODELO.NAME
                )GROUP BY ID, PL, MODE
                 ORDER BY SUM(FPESO) DESC";
        }
        if($imob){
            $script = "SELECT ID, PL,MODE, SUM(FPESO), SUM(FRETEP) FROM
                (SELECT HVEICULO.ID_VEICULO AS ID, HVEICULO.PLACA AS PL, MODELO.NAME AS MODE, SUM(VALTOTFRETE) AS FPESO, SUM(CT.VALFPESOSICMS) AS FRETEP
                FROM CT
                    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
                    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
                    JOIN MODELO ON (HVEICULO.CODEMODE = MODELO.CODEMODE)
                WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes_atual AND HVEICEMP.STAFT='$afto'
                GROUP BY HVEICULO.ID_VEICULO, HVEICULO.PLACA, MODELO.NAME
                UNION
                SELECT HVEICULO.ID_VEICULO AS ID, HVEICULO.PLACA AS PL, MODELO.NAME AS MODE,SUM(VALFRETE) AS FPESO, SUM(CARRETO.VALFRETE) AS FRETEP
                FROM CARRETO
                    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
                    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
                        JOIN MODELO ON (HVEICULO.CODEMODE = MODELO.CODEMODE)
                WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes_atual AND HVEICEMP.STAFT='$afto'
                GROUP BY HVEICULO.ID_VEICULO, HVEICULO.PLACA, MODELO.NAME

                UNION
                SELECT HVEICULO.ID_VEICULO AS ID, HVEICULO.PLACA AS PL, MODELO.NAME AS MODE, SUM(VLR_TOTAL) AS FPESO, SUM(NOTAFAT.VLR_TOTAL) AS FRETEP
                FROM NOTAFAT
                    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTAFAT.ID_HVEICULO)
                    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTAFAT.ID_HVEICULO)
                    JOIN MODELO ON (HVEICULO.CODEMODE = MODELO.CODEMODE)
                WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes_atual AND HVEICEMP.STAFT='$afto'
                AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')
                GROUP BY HVEICULO.ID_VEICULO, HVEICULO.PLACA,MODELO.NAME

                UNION
                SELECT HVEICULO.ID_VEICULO AS ID, HVEICULO.PLACA AS PL, MODELO.NAME AS MODE, SUM(NOTASER.VALTOTSERV) AS FPESO, SUM(NOTASER.VALTOTSERV) AS FRETEP
                FROM NOTASER
                    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
                    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
                    JOIN MODELO ON (HVEICULO.CODEMODE = MODELO.CODEMODE)
                WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes_atual AND HVEICEMP.STAFT='$afto'
                GROUP BY HVEICULO.ID_VEICULO, HVEICULO.PLACA, MODELO.NAME
                )GROUP BY ID, PL, MODE
                 ORDER BY SUM(FPESO) DESC";
        }
        $db2_script = db2_exec($hDbcDB2, $script);
        while($dados = db2_fetch_array($db2_script)){
            $registro[$i][ID] = $dados[0];
            $registro[$i][PL] = $dados[1];
            $registro[$i][MODE] = $dados[2];
            $registro[$i][FPESO] = $dados[3];
            $registro[$i][FRETEP] = $dados[4];
            $i++;
        }

        return $registro;
    }

    /************************************************
    *CHAVE: RECEITA                                 *
    *FRETE TOTAL ANO MES PLACA
    *PROGRAMADOR: Gabriel Machado Luis              *
    *DATA: 29/05/2015                               *
    ************************************************/
    function freteTotalAnoMesPlaca($ano, $mes_atual, $placa, $imob){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        if(!$imob){
            $script = "SELECT SUM(FPESO) FROM
                (SELECT SUM(VALTOTFRETE) AS FPESO
                FROM CT
                    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
                    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
                    JOIN MODELO ON (HVEICULO.CODEMODE = MODELO.CODEMODE)
                WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes_atual AND HVEICULO.PLACA='$placa'

                UNION
                SELECT SUM(VALFRETE) AS FPESO
                FROM CARRETO
                    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
                    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
                    JOIN MODELO ON (HVEICULO.CODEMODE = MODELO.CODEMODE)
                WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes_atual AND HVEICULO.PLACA='$placa'

                UNION
                SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
                FROM NOTASER
                    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
                    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
                    JOIN MODELO ON (HVEICULO.CODEMODE = MODELO.CODEMODE)
                WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes_atual AND HVEICULO.PLACA='$placa'
                )";
        }
        if($imob){
            $script = "SELECT SUM(FPESO) FROM
                (SELECT SUM(VALTOTFRETE) AS FPESO
                FROM CT
                    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
                    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
                    JOIN MODELO ON (HVEICULO.CODEMODE = MODELO.CODEMODE)
                WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes_atual AND HVEICULO.PLACA='$placa'

                UNION
                SELECT SUM(VALFRETE) AS FPESO
                FROM CARRETO
                    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
                    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
                        JOIN MODELO ON (HVEICULO.CODEMODE = MODELO.CODEMODE)
                WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes_atual AND HVEICULO.PLACA='$placa'

                UNION
                SELECT SUM(VLR_TOTAL) AS FPESO
                FROM NOTAFAT
                    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTAFAT.ID_HVEICULO)
                    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTAFAT.ID_HVEICULO)
                    JOIN MODELO ON (HVEICULO.CODEMODE = MODELO.CODEMODE)
                WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes_atual AND HVEICULO.PLACA='$placa'
                AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

                UNION
                SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
                FROM NOTASER
                    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
                    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
                    JOIN MODELO ON (HVEICULO.CODEMODE = MODELO.CODEMODE)
                WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes_atual AND HVEICULO.PLACA='$placa'
                )";
        }
        $db2 = db2_exec($hDbcDB2, $script);
        $dados = db2_fetch_array($db2);
        if($dados[0] == NULL){
            $dados[0] = 0;
        }
        return $dados[0];
    }

    /************************************************
    *CHAVE: RECEITA                                 *
    *FRETE PESO ANO MES PLACA
    *PROGRAMADOR: Gabriel Machado Luis              *
    *DATA: 29/05/2015                               *
    ************************************************/
    function fretePesoAnoMesPlaca($ano, $mes_atual, $placa, $imob){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        if(!$imob){
            $script = "SELECT SUM(FPESO) FROM
                (SELECT SUM(CT.VALFPESOSICMS) AS FPESO
                FROM CT
                    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
                    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
                    JOIN MODELO ON (HVEICULO.CODEMODE = MODELO.CODEMODE)
                WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes_atual AND HVEICULO.PLACA='$placa'

                UNION
                SELECT SUM(VALFRETE) AS FPESO
                FROM CARRETO
                    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
                    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
                    JOIN MODELO ON (HVEICULO.CODEMODE = MODELO.CODEMODE)
                WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes_atual AND HVEICULO.PLACA='$placa'

                UNION
                SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
                FROM NOTASER
                    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
                    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
                    JOIN MODELO ON (HVEICULO.CODEMODE = MODELO.CODEMODE)
                WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes_atual AND HVEICULO.PLACA='$placa'
                )";
        }
        if($imob){
            $script = "SELECT SUM(FPESO) FROM
                (SELECT SUM(CT.VALFPESOSICMS) AS FPESO
                FROM CT
                    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
                    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
                    JOIN MODELO ON (HVEICULO.CODEMODE = MODELO.CODEMODE)
                WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes_atual AND HVEICULO.PLACA='$placa'

                UNION
                SELECT SUM(VALFRETE) AS FPESO
                FROM CARRETO
                    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
                    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
                        JOIN MODELO ON (HVEICULO.CODEMODE = MODELO.CODEMODE)
                WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes_atual AND HVEICULO.PLACA='$placa'

                UNION
                SELECT SUM(VLR_TOTAL) AS FPESO
                FROM NOTAFAT
                    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTAFAT.ID_HVEICULO)
                    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTAFAT.ID_HVEICULO)
                    JOIN MODELO ON (HVEICULO.CODEMODE = MODELO.CODEMODE)
                WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes_atual AND HVEICULO.PLACA='$placa'
                AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

                UNION
                SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
                FROM NOTASER
                    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
                    JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
                    JOIN MODELO ON (HVEICULO.CODEMODE = MODELO.CODEMODE)
                WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes_atual AND HVEICULO.PLACA='$placa'
                )";
        }
        $db2 = db2_exec($hDbcDB2, $script);
        $dados = db2_fetch_array($db2);
        if($dados[0] == NULL){
            $dados[0] = 0;
        }
        return $dados[0];
    }

    /******************************************
    *   QUANTIDADE DE SOLICITACAO DE COMPRA   *
    *******************************************/
    function qtdaSolicitacaoCompra($ano, $mes){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';

        $scriptSc = "SELECT count(*) FROM reqcusu WHERE year(data_geracao)=$ano AND month(data_geracao)=$mes";
        $db2_scriptSc = db2_exec($hDbcDB2, $scriptSc);
        $numeroLinhas = db2_fetch_array($db2_scriptSc);

        return $numeroLinhas[0];
    }

    /******************************************
     *      LISTA DE SOLICITACAO DE COMPRA    *
     *****************************************/
    //$scriptSc = "SELECT numero, status, data_geracao, datacancela FROM reqcusu where year(data_geracao) = $ano and month(data_geracao)=$mes_atual";

    /**********************************************************
     *   Localizar ordem de compra da solicitacao informada   *
     **********************************************************/
    function localizaOrdemCompra($numeroSolicitacao){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';

        $scriptOC = "SELECT numero, dat_emissao FROM reqccomp WHERE mapa=$numeroSolicitacao FETCH FIRST 1 ROWS ONLY";
        $db2_scriptOc = db2_exec($hDbcDB2, $scriptOC);
        $dados_scriptOc = db2_fetch_array($db2_scriptOc);

        return $dados_scriptOc;
    }

    /******************************
     *    LISTA DE FORNECEDORES   *
     *****************************/
    function  listaFornecedor(){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        $i = 0;
        $script = "SELECT RAZAO_SOCIAL, CNPJ_CPF FROM FORNECED ORDER BY RAZAO_SOCIAL ASC";
        $db2_script = db2_exec($hDbcDB2, $script);
        while($dados = db2_fetch_array($db2_script)){
            $registro[$i][RAZAO_SOCIAL] = $dados[0];
            $registro[$i][CNPJ_CPF] = $dados[1];
            $i++;
        }

        return $registro;
    }

    /****************************************
     *    DADOS DE FORNECEDOR SELECIONADO   *
     ***************************************/
    function dadosFornecedor($cnpj_cpf){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        $script = "SELECT RAZAO_SOCIAL FROM FORNECED WHERE CNPJ_CPF LIKE '$cnpj_cpf' ";
        $db2_script = db2_exec($hDbcDB2, $script);
        $registro = db2_fetch_array($db2_script);

        return $registro[0];
    }

    function relacaoPagamento($cnpj_cpf){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        $i = 0;
        $script = "SELECT NOTAENT.NUMNOTA, NOTAENT.TOTAL_NOTAFISCAL, NOTAENT.DATA_EMISSAO
                        FROM PARCENT
                        JOIN NOTAENT ON (NOTAENT.ID_NOTAENT = PARCENT.ID_NOTAENT)
                        JOIN FORNECED ON (FORNECED.ID_FORNECED = NOTAENT.ID_FORNECED)
                        WHERE PARCENT.STATUS <> 'C' AND FORNECED.CNPJ_CPF like '$cnpj_cpf'
                        ";
        $db2_script = db2_exec($hDbcDB2, $script);
        while($dados = db2_fetch_array($db2_script)){
            $registro[$i][NOTA]             = $dados[0];
            $registro[$i][TOTAL_NOTAFISCAL] = $dados[1];
            $registro[$i][DATA_EMISSAO]     = $dados[2];
            $i++;
        }
        return $registro;
    }

    function mediaVeiculo($ano, $mes, $idVeiculo){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        $sqlRodado = "SELECT SUM(ODOMETRO-ODOANTER), SUM(LITROSABAST) FROM ABAST WHERE MONTH(DATAABAST)=$mes and YEAR(DATAABAST)=$ano and ID_VEICULO=$idVeiculo";
        $db2_rodado = db2_exec($hDbcDB2, $sqlRodado);
        $dados_rodado = db2_fetch_array($db2_rodado);
        return $dados_rodado[0]/$dados_rodado[1];
    }

    function kmRodadoVeiculo($ano, $mes, $idVeiculo){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        $sqlRodado = "SELECT SUM(ODOMETRO-ODOANTER) FROM ABAST WHERE MONTH(DATAABAST)=$mes and YEAR(DATAABAST)=$ano and ID_VEICULO=$idVeiculo";
        $db2_rodado = db2_exec($hDbcDB2, $sqlRodado);
        $dados_rodado = db2_fetch_array($db2_rodado);
        return $dados_rodado[0];
    }

    /************************************************
    *CHAVE: CUSTO                                   *
    *CUSTO MENSAL POR PLACA                         *
    *PROGRAMADOR: Gabriel Machado Luis              *
    *DATA: 28/05/2015                               *
    ************************************************/
    function custoAnoMesPlaca($ano, $mes, $placa){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        $sql = "SELECT SUM(LANCTO.VLR_LANCTO)
                    FROM LANCTO
                    JOIN CTCUSTO ON (CTCUSTO.COD_CTCUSTO = LANCTO.CCUSTO)
                    WHERE CTCUSTO.PLACA='$placa' AND MONTH(LANCTO.DAT_LANCTO)=$mes AND YEAR(LANCTO.DAT_LANCTO)=$ano
            ";
        $db2 = db2_exec($hDbcDB2, $sql);
        $dados = db2_fetch_array($db2);
        if($dados[0] == NULL){
            $dados[0] = 0;
        }
        return $dados[0];
    }

   /****************************************************************************
    *                         CONSULTAS DO CONTAS A PAGAR                      *
    ****************************************************************************/

   /****************************************************************
    * Função: contasPagarSumAnoMes                                 *
    * Programador: Paulo Silva                                     *
    * Data: 11/06/2015                                             *
    * Descrição:                                                   *
    *     Totaliza (SUM) uma coluna escolhida da tabela de         *
    *     parcelas da nota.                                        *
    ****************************************************************/
    function contasPagarSumAnoMes($coluna, $ano, $mes = NULL){
        $sql = "SELECT SUM($coluna)
                FROM PARCENT
                JOIN NOTAENT ON (NOTAENT.ID_NOTAENT = PARCENT.ID_NOTAENT)
                WHERE YEAR(DT_VENCIMENTO) = $ano AND STATUS <> 'C'";

        if ($mes != NULL) $sql .= " AND MONTH(DT_VENCIMENTO) = $mes ";

        return getConsultaSQLNumber($sql);
    }

   /****************************************************************
    * Função: contasPagarSumFornecedorAnoMes                       *
    * Programador: Paulo Silva                                     *
    * Data: 11/06/2015                                             *
    * Descrição:                                                   *
    *     Totaliza (SUM) uma coluna escolhida da tabela de         *
    *     parcelas da nota e retorna agrupado por fornecedor       *
    ****************************************************************/
    function contasPagarSumFornecedorAnoMes($coluna, $ano, $mes, $filtro = NULL){
        $sql =
            "SELECT
                F.ID_FORNECED, F.RAZAO_SOCIAL, SUM($coluna)
            FROM PARCENT P
            JOIN NOTAENT N ON N.ID_NOTAENT = P.ID_NOTAENT
            JOIN FORNECED F ON N.ID_FORNECED = F.ID_FORNECED
            WHERE
                YEAR(DT_VENCIMENTO) = $ano AND MONTH(DT_VENCIMENTO) = $mes AND STATUS <> 'C' AND $coluna > 0
                $filtro
            GROUP BY F.ID_FORNECED, F.RAZAO_SOCIAL
            ORDER BY SUM($coluna) DESC";

        return getConsultaSQL($sql);
    }

   /****************************************************************
    * Função: contasPagarLancadoAnoMes                             *
    * Programador: Paulo Silva                                     *
    * Data: 08/06/2015                                             *
    * Descrição:                                                   *
    *     Retorna o valor total de notas lançadas e não canceladas *
    *     para o ano e mês desejados                               *
    ****************************************************************/
    function contasPagarLancadoAnoMes($ano, $mes = NULL){
        return contasPagarSumAnoMes('VALOR_PARCELA', $ano, $mes);
    }

   /****************************************************************
    * Função: contasPagarDescontoAnoMes                            *
    * Programador: Paulo Silva                                     *
    * Data: 08/06/2015                                             *
    * Descrição:                                                   *
    *     Retorna o valor total de desconto nas notas para o ano e *
    *     mês desejados                                            *
    ****************************************************************/
    function contasPagarDescontoAnoMes($ano, $mes = NULL){
        return contasPagarSumAnoMes('DESCONTO', $ano, $mes);
    }

   /****************************************************************
    * Função: contasPagarJurosAnoMes                               *
    * Programador: Paulo Silva                                     *
    * Data: 08/06/2015                                             *
    * Descrição:                                                   *
    *     Retorna o valor total de juros nas notas para o ano e    *
    *     mês desejados                                            *
    ****************************************************************/
    function contasPagarJurosAnoMes($ano, $mes = NULL){
        return contasPagarSumAnoMes('JUROS', $ano, $mes);
    }

   /****************************************************************
    * Função: contasPagarPagarAnoMes                               *
    * Programador: Paulo Silva                                     *
    * Data: 08/06/2015                                             *
    * Descrição:                                                   *
    *     Retorna o valor total de notas a pagar para o ano e mês  *
    *     desejados                                                *
    ****************************************************************/
    function contasPagarPagarAnoMes($ano, $mes = NULL){
        $sql = "SELECT SUM(VALOR_PARCELA + COALESCE(JUROS, 0) - COALESCE(DESCONTO, 0))
                FROM PARCENT
                JOIN NOTAENT ON (NOTAENT.ID_NOTAENT = PARCENT.ID_NOTAENT)
                JOIN FORNECED ON (FORNECED.ID_FORNECED = NOTAENT.ID_FORNECED)
                WHERE
                YEAR(DT_VENCIMENTO) = $ano AND STATUS NOT IN ('C','P')";

        if ($mes != NULL) $sql .= " AND MONTH(DT_VENCIMENTO) = $mes ";

        return getConsultaSQLNumber($sql);
    }

   /****************************************************************
    * Função: contasPagarPagoAnoMes                                *
    * Programador: Paulo Silva                                     *
    * Data: 08/06/2015                                             *
    * Descrição:                                                   *
    *     Retorna o valor total de notas pagas para o ano e mês    *
    *     desejados                                                *
    ****************************************************************/
    function contasPagarPagoAnoMes($ano, $mes = NULL){
        $sql = "SELECT SUM(VALOR_PARCELA + COALESCE(JUROS, 0) - COALESCE(DESCONTO, 0))
                FROM PARCENT
                JOIN NOTAENT ON (NOTAENT.ID_NOTAENT = PARCENT.ID_NOTAENT)
                JOIN FORNECED ON (FORNECED.ID_FORNECED = NOTAENT.ID_FORNECED)
                WHERE
                YEAR(DT_VENCIMENTO) = $ano AND STATUS = 'P'";

        if ($mes != NULL) $sql .= " AND MONTH(DT_VENCIMENTO) = $mes ";

        return getConsultaSQLNumber($sql);
    }

   /****************************************************************
    * Função: contasPagarListarNotas                               *
    * Programador: Paulo Silva                                     *
    * Data: 08/06/2015                                             *
    * Parâmetros:                                                  *
    *     $ano - Ano de vencimento da parcela                      *
    *     $mes - Mês de vencimento da parcela                      *
    *     $idForneced - Fornecedor da nota                         *
    *     $filtroAdicional - String adicional para o WHERE         *
    * Descrição:                                                   *
    *     Lista as parcelas de notas não canceladas de acordo com  *
    *     ano e mês desejados e parâmetros adicionais livres       *
    ****************************************************************/
    function contasPagarListarNotas($ano = NULL, $mes = NULL, $idForneced = NULL, $filtroAdicional = NULL){
        $sql =
            "SELECT N.NUMNOTA, P.NUMERO_PARCELA, P.DT_VENCIMENTO, P.DT_PAGAMENTO,
                P.DESCONTO, P.JUROS, P.VALOR_PARCELA + COALESCE(P.JUROS, 0) - COALESCE(P.DESCONTO, 0),
                DECODE(P.STATUS, 'P', 'Pago', 'A pagar')
            FROM PARCENT P
            JOIN NOTAENT N ON N.ID_NOTAENT = P.ID_NOTAENT
            JOIN FORNECED F ON N.ID_FORNECED = F.ID_FORNECED
            WHERE
                P.STATUS <> 'C'";

        if ($ano != NULL) $sql .= " AND YEAR(P.DT_VENCIMENTO)  = $ano ";
        if ($mes != NULL) $sql .= " AND MONTH(P.DT_VENCIMENTO) = $mes ";

        if ($idForneced != NULL) $sql .= " AND F.ID_FORNECED = $idForneced ";

        if ($filtroAdicional != NULL) $sql .= " " . $filtroAdicional . " ";

        $sql .= " ORDER BY P.VALOR_PARCELA + COALESCE(P.JUROS, 0) - COALESCE(P.DESCONTO, 0) DESC";

        return getConsultaSQL($sql);
    }

   /****************************************************************
    * Função: contasPagarCumulativoNotas                           *
    * Programador: Paulo Silva                                     *
    * Data: 08/06/2015                                             *
    * Parâmetros:                                                  *
    *     $ano - Ano de vencimento da parcela                      *
    *     $mes - Mês de vencimento da parcela                      *
    *     $opcao - Define se deseja as notas pagas ou a pagar      *
    *     $idForneced - Fornecedor da nota                         *
    *     $filtroAdicional - String adicional para o WHERE         *
    * Descrição:                                                   *
    *     Lista os totais de notas não canceladas de acordo com    *
    *     ano e mês desejados e parâmetros adicionais livres       *
    ****************************************************************/
    function contasPagarCumulativoNotas($ano = NULL, $mes = NULL, $opcao, $idForneced = NULL, $filtroAdicional = NULL){
        $sql =
            "SELECT
                F.RAZAO_SOCIAL, SUM(P.DESCONTO), SUM(P.JUROS),
                SUM(P.VALOR_PARCELA + COALESCE(P.JUROS, 0) - COALESCE(P.DESCONTO, 0))
            FROM PARCENT P
            JOIN NOTAENT N ON N.ID_NOTAENT = P.ID_NOTAENT
            JOIN FORNECED F ON N.ID_FORNECED = F.ID_FORNECED
            WHERE";

        // Se optar pelas pagas, filtra status = P, senão tudo diferente de P e Cancelado
        if ($opcao == 'pago')
            $sql .= " P.STATUS = 'P' ";
        else
            $sql .= " P.STATUS NOT IN ('P','C') ";

        if ($ano != NULL) $sql .= " AND YEAR(P.DT_VENCIMENTO)  = $ano ";
        if ($mes != NULL) $sql .= " AND MONTH(P.DT_VENCIMENTO) = $mes ";

        if ($idForneced != NULL) $sql .= " AND F.ID_FORNECED = $idForneced ";

        if ($filtroAdicional != NULL) $sql .= " " . $filtroAdicional . " ";

        $sql .= " GROUP BY F.RAZAO_SOCIAL ";

        return getConsultaSQL($sql);
    }

   /****************************************************************
    * Função: contasPagarPagamentosPeriodo                         *
    * Programador: Paulo Silva                                     *
    * Data: 10/06/2015                                             *
    * Parâmetros:                                                  *
    *     $dtIni - Data de início da busca                         *
    *     $dtFin - Data de término da busca. Se não informada será *
    *              adotada a data inicial                          *
    *     $cnpjCpf - Filtro e agrupamento por trunco de CNPJ/CPF   *
    * Descrição:                                                   *
    *     Totaliza as notas pagas entre certo período, permitindo  *
    *     filtrar também por trunco de CNPJ/CPF                    *
    ****************************************************************/
    function contasPagarPagamentosPeriodo($dtIni, $dtFin = NULL, $opcao = NULL, $cnpjCpf = NULL){
        // Se não for passado dia final, adotamos o mesmo que o inicial
        if ($dtFin == NULL) $dtFin = $dtIni;

        $sql =
            "SELECT
                SUM(P.VALOR_PARCELA + COALESCE(P.JUROS, 0) - COALESCE(P.DESCONTO, 0))
            FROM PARCENT P
            JOIN NOTAENT N ON N.ID_NOTAENT = P.ID_NOTAENT
            JOIN FORNECED F ON N.ID_FORNECED = F.ID_FORNECED
            WHERE
                P.STATUS <> 'C'
                AND P.VALOR_PARCELA + COALESCE(P.JUROS, 0) - COALESCE(P.DESCONTO, 0) > 0";

        if ($opcao != NULL)
            $sql .= ($opcao == 'pago') ?
                " AND P.DT_PAGAMENTO  BETWEEN '$dtIni' AND '$dtFin' AND P.STATUS = 'P' " :
                " AND P.DT_VENCIMENTO BETWEEN '$dtIni' AND '$dtFin' AND P.STATUS <> 'P' ";
        else $sql .= " AND P.DT_VENCIMENTO BETWEEN '$dtIni' AND '$dtFin' ";

        // Se foi informado CNPJ/CPF, filtra pelo trunco do mesmo e agrupa
        if ($cnpjCpf != NULL)
            $sql .=  " AND F.CNPJ_CPF LIKE '$cnpjCpf%'
                       GROUP BY CAST(F.CNPJ_CPF AS VARCHAR(8))";

        return getConsultaSQLNumber($sql, NULL, '-');
    }

   /****************************************************************************
    *                        CONSULTAS DO CONTAS A RECEBER                     *
    ****************************************************************************/

   /****************************************************************
    * Função: contasReceberSumAnoMes                               *
    * Programador: Paulo Silva                                     *
    * Data: 11/06/2015                                             *
    * Descrição:                                                   *
    *     Totaliza (SUM) uma coluna escolhida da tabela de         *
    *     parcelas da fatura.                                      *
    ****************************************************************/
    function contasReceberSumAnoMes($coluna, $ano, $mes = NULL, $filtro = NULL){
        $sql = "SELECT SUM($coluna)
                FROM PARCDUP P
                JOIN DUPLIC D ON D.IDDUPLIC = P.IDDUPLIC
                WHERE YEAR(D.DATAEMIS) = $ano AND D.STATUS <> 'C'";

        if ($mes != NULL) $sql .= " AND MONTH(D.DATAEMIS) = $mes ";

        if ($filtro != NULL) $sql .= " " . $filtro . " ";

        return getConsultaSQLNumber($sql);
    }

   /****************************************************************
    * Função: contasReceberSumClienteAnoMes                        *
    * Programador: Paulo Silva                                     *
    * Data: 11/06/2015                                             *
    * Descrição:                                                   *
    *     Totaliza (SUM) uma coluna escolhida da tabela de         *
    *     parcelas da fatura e retorna agrupado por cliente        *
    ****************************************************************/
    function contasReceberSumClienteAnoMes($coluna, $ano, $mes, $filtro = NULL){
        $sql =
            "SELECT
                C.ID_CLIENTE, C.RAZAO_SOCIAL, SUM($coluna)
            FROM PARCDUP P
            JOIN DUPLIC D ON D.IDDUPLIC = P.IDDUPLIC
            JOIN HCLIENTE C ON C.IDHCLIENTE = D.IDHCLIENTE
            WHERE
                YEAR(D.DATAEMIS) = $ano AND MONTH(D.DATAEMIS) = $mes AND D.STATUS <> 'C' AND $coluna > 0
                $filtro
            GROUP BY C.ID_CLIENTE, C.RAZAO_SOCIAL
            ORDER BY SUM($coluna) DESC";

        return getConsultaSQL($sql);
    }

   /****************************************************************
    * Função: contasReceberFaturadoAnoMes                          *
    * Programador: Paulo Silva                                     *
    * Data: 11/06/2015                                             *
    * Descrição:                                                   *
    *     Retorna o valor total de faturas lançadas e não cancela- *
    *     das para o ano e mês desejados                           *
    ****************************************************************/
    function contasReceberFaturadoAnoMes($ano, $mes = NULL){
        return contasReceberSumAnoMes('VLR_PARCELA', $ano, $mes);
    }

   /****************************************************************
    * Função: contasReceberDescontoAnoMes                          *
    * Programador: Paulo Silva                                     *
    * Data: 11/06/2015                                             *
    * Descrição:                                                   *
    *     Retorna o desconto de faturas lançadas e não canceladas  *
    *     para o ano e mês desejados                               *
    ****************************************************************/
    function contasReceberDescontoAnoMes($ano, $mes = NULL){
        return contasReceberSumAnoMes('VLR_DESC', $ano, $mes);
    }

   /****************************************************************
    * Função: contasReceberJurosAnoMes                             *
    * Programador: Paulo Silva                                     *
    * Data: 11/06/2015                                             *
    * Descrição:                                                   *
    *     Retorna o juros de faturas lançadas e não canceladas     *
    *     para o ano e mês desejados                               *
    ****************************************************************/
    function contasReceberJurosAnoMes($ano, $mes = NULL){
        return contasReceberSumAnoMes('VLR_JUROS', $ano, $mes);
    }

   /****************************************************************
    * Função: contasReceberVencerAnoMes                            *
    * Programador: Paulo Silva                                     *
    * Data: 11/06/2015                                             *
    * Descrição:                                                   *
    *     Retorna o valor total de faturas lançadas e abertas para *
    *     o ano e mês desejados, que estão para vencer             *
    ****************************************************************/
    function contasReceberVencerAnoMes($ano, $mes = NULL){
        $filtro = " AND D.STATUS <> 'B' AND P.DATA_VENCTO >= CURRENT DATE AND P.DATA_PAGTO IS NULL ";

        return contasReceberSumAnoMes('VLR_PARCELA + VLR_JUROS - VLR_DESC', $ano, $mes, $filtro);
    }

   /****************************************************************
    * Função: contasReceberVencidoAnoMes                           *
    * Programador: Paulo Silva                                     *
    * Data: 11/06/2015                                             *
    * Descrição:                                                   *
    *     Retorna o valor total de faturas lançadas e abertas para *
    *     o ano e mês desejados, que estão vencidas                *
    ****************************************************************/
    function contasReceberVencidoAnoMes($ano, $mes = NULL){
        $filtro = " AND D.STATUS <> 'B' AND P.DATA_VENCTO < CURRENT DATE AND P.DATA_PAGTO IS NULL ";

        return contasReceberSumAnoMes('VLR_PARCELA + VLR_JUROS - VLR_DESC', $ano, $mes, $filtro);
    }

   /****************************************************************
    * Função: contasReceberPagoAnoMes                              *
    * Programador: Paulo Silva                                     *
    * Data: 11/06/2015                                             *
    * Descrição:                                                   *
    *     Retorna o valor total de faturas lançadas e abertas para *
    *     o ano e mês desejados, que estão pagas                   *
    ****************************************************************/
    function contasReceberPagoAnoMes($ano, $mes = NULL){
        $filtro = " AND P.DATA_PAGTO IS NOT NULL ";

        return contasReceberSumAnoMes('VLR_PARCELA + VLR_JUROS - VLR_DESC', $ano, $mes, $filtro);
    }

   /****************************************************************
    * Função: contasReceberRecebimentosPeriodo                     *
    * Programador: Paulo Silva                                     *
    * Data: 12/06/2015                                             *
    * Parâmetros:                                                  *
    *     $dtIni - Data de início da busca                         *
    *     $dtFin - Data de término da busca. Se não informada será *
    *              adotada a data inicial                          *
    *     $opcao - Indica se deseja as recebidas ou a receber      *
    *              (NULL = Todos filtrando por data de vencimento) *
    *     $cnpjCpf - Filtro e agrupamento por trunco de CNPJ/CPF   *
    * Descrição:                                                   *
    *     Totaliza as faturas entre certo período, permitindo      *
    *     filtrar também por trunco de CNPJ/CPF                    *
    ****************************************************************/
    function contasReceberRecebimentosPeriodo($dtIni, $dtFin = NULL, $opcao = NULL, $cnpjCpf = NULL){
        // Se não for passado dia final, adotamos o mesmo que o inicial
        if ($dtFin == NULL) $dtFin = $dtIni;

        $sql =
            "SELECT
                SUM(P.VLR_PARCELA + P.VLR_JUROS - P.VLR_DESC)
            FROM PARCDUP P
            JOIN DUPLIC D ON D.IDDUPLIC = P.IDDUPLIC
            JOIN HCLIENTE C ON C.IDHCLIENTE = D.IDHCLIENTE
            WHERE
                D.STATUS <> 'C'
                AND P.VLR_PARCELA + P.VLR_JUROS - P.VLR_DESC > 0";

        if ($opcao != NULL)
            $sql .= ($opcao == 'recebido') ?
                " AND P.DATA_PAGTO BETWEEN '$dtIni' AND '$dtFin' " :
                " AND P.DATA_VENCTO BETWEEN '$dtIni' AND '$dtFin' AND P.DATA_PAGTO IS NULL ";
        else $sql .= " AND P.DATA_VENCTO BETWEEN '$dtIni' AND '$dtFin' ";

        // Se foi informado CNPJ/CPF, filtra pelo trunco do mesmo e agrupa
        if ($cnpjCpf != NULL)
            $sql .=  " AND C.CNPJ_CPF LIKE '$cnpjCpf%'
                       GROUP BY CAST(C.CNPJ_CPF AS VARCHAR(8))";

        return getConsultaSQLNumber($sql, NULL, '-');
    }

   /****************************************************************
    * Função: contasReceberListarFaturas                           *
    * Programador: Paulo Silva                                     *
    * Data: 11/06/2015                                             *
    * Parâmetros:                                                  *
    *     $ano - Ano de vencimento da parcela                      *
    *     $mes - Mês de vencimento da parcela                      *
    *     $idCliente - Cliente da fatura                           *
    *     $filtroAdicional - String adicional para o WHERE         *
    * Descrição:                                                   *
    *     Lista as parcelas de faturas não canceladas de acordo    *
    *     com ano e mês desejados e parâmetros adicionais livres   *
    ****************************************************************/
    function contasReceberListarFaturas($ano = NULL, $mes = NULL, $idCliente = NULL, $filtroAdicional = NULL){
        $sql =
            "SELECT
                D.NUMERO, P.PARCELA, P.DATA_VENCTO, P.DATA_PAGTO,
                P.VLR_DESC, P.VLR_JUROS, P.VLR_PARCELA + P.VLR_JUROS - P.VLR_DESC,
                DECODE(P.DATA_PAGTO, NULL, 'A receber', 'Recebido'), D.IDDUPLIC, D.TIPODUP
            FROM PARCDUP P
            JOIN DUPLIC D ON D.IDDUPLIC = P.IDDUPLIC
            JOIN HCLIENTE C ON C.IDHCLIENTE = D.IDHCLIENTE
            WHERE
                D.STATUS <> 'C'";

        if ($ano != NULL) $sql .= " AND YEAR(D.DATAEMIS)  = $ano ";
        if ($mes != NULL) $sql .= " AND MONTH(D.DATAEMIS) = $mes ";

        if ($idCliente != NULL) $sql .= " AND C.ID_CLIENTE = $idCliente ";

        if ($filtroAdicional != NULL) $sql .= " " . $filtroAdicional . " ";

        $sql .= " ORDER BY P.VLR_PARCELA + P.VLR_JUROS - P.VLR_DESC DESC";

        return getConsultaSQL($sql);
    }

   /****************************************************************
    * Função: contasReceberCumulativoFaturas                       *
    * Programador: Paulo Silva                                     *
    * Data: 11/06/2015                                             *
    * Parâmetros:                                                  *
    *     $ano - Ano de vencimento da parcela                      *
    *     $mes - Mês de vencimento da parcela                      *
    *     $idCliente - Cliente da fatura                           *
    *     $filtroAdicional - String adicional para o WHERE         *
    * Descrição:                                                   *
    *     Lista os totais de faturas não canceladas de acordo      *
    *     com ano e mês desejados e parâmetros adicionais livres   *
    ****************************************************************/
    function contasReceberCumulativoFaturas($ano = NULL, $mes = NULL, $idCliente = NULL, $filtroAdicional = NULL){
        $sql =
            "SELECT
                C.RAZAO_SOCIAL, SUM(P.VLR_DESC), SUM(P.VLR_JUROS),
                SUM(P.VLR_PARCELA + COALESCE(P.VLR_JUROS, 0) - COALESCE(P.VLR_DESC, 0))
            FROM PARCDUP P
            JOIN DUPLIC D ON D.IDDUPLIC = P.IDDUPLIC
            JOIN HCLIENTE C ON C.IDHCLIENTE = D.IDHCLIENTE
            WHERE
                D.STATUS <> 'C'";

        if ($ano != NULL) $sql .= " AND YEAR(D.DATAEMIS)  = $ano ";
        if ($mes != NULL) $sql .= " AND MONTH(D.DATAEMIS) = $mes ";

        if ($idCliente != NULL) $sql .= " AND C.ID_CLIENTE = $idCliente ";

        if ($filtroAdicional != NULL) $sql .= " " . $filtroAdicional . " ";

        $sql .= " GROUP BY C.ID_CLIENTE, C.RAZAO_SOCIAL ";

        return getConsultaSQL($sql);
    }

   /****************************************************************
    * Função: contasReceberCtFaturado                              *
    * Programador: Paulo Silva                                     *
    * Data: 16/06/2015                                             *
    * Parâmetros:                                                  *
    *     $ano - Ano de emissão do documento                       *
    *     $mes - Mês de emissão do documento                       *
    *     $opcao - Escolhe entre trazer os faturados ou não        *
    *              (NULL = Todos)                                  *
    *     $groupCnpjCpf - Agrupar por CNPJ/CPF do cliente          *
    *     $filtroAdicional - String adicional para o WHERE         *
    *     $retSql - Se TRUE, em vez de executar a consulta irá     *
    *               retornar o SQL para ser rodado em outro lugar  *
    * Descrição:                                                   *
    *     Lista o total de conhecimentos não faturados, podendo    *
    *     agrupar por Cliente                                      *
    ****************************************************************/
    function contasReceberCtFaturado($ano, $mes = NULL, $opcao = NULL, $groupCnpjCpf = NULL, $filtroAdicional = NULL, $retSql = NULL){
        $sql = "SELECT SUM(DOC.VALTOTFRETE) VALOR ";

        if ($groupCnpjCpf != NULL) $sql .= ", CAST(C.CNPJ_CPF AS VARCHAR(8)) CNPJ ";

        $sql .=
            "FROM CT DOC
            JOIN HCLIENTE C ON (C.IDHCLIENTE = DOC.IDHCLIENTE)
            WHERE
                DOC.STATUSCT <> 'C'
                AND YEAR(DOC.DATAEMISSAO) = $ano ";

        if ($mes != NULL) $sql .= " AND MONTH(DOC.DATAEMISSAO) = $mes ";

        if ($opcao != NULL)
            $sql .= " AND DOC.IDDUPLIC IS " . (($opcao == 'faturado') ? " NOT NULL " : " NULL ");

        if ($filtroAdicional != NULL) $sql .= " " . $filtroAdicional . " ";

        if ($groupCnpjCpf != NULL) $sql .= " GROUP BY CAST(C.CNPJ_CPF AS VARCHAR(8)) ";

        return ($retSql != NULL) ? $sql : getConsultaSQLSimples($sql);
    }

   /****************************************************************
    * Função: contasReceberCarretoFaturado                         *
    * Programador: Paulo Silva                                     *
    * Data: 16/06/2015                                             *
    * Parâmetros:                                                  *
    *     $ano - Ano de emissão do documento                       *
    *     $mes - Mês de emissão do documento                       *
    *     $opcao - Escolhe entre trazer os faturados ou não        *
    *              (NULL = Todos)                                  *
    *     $groupCnpjCpf - Agrupar por CNPJ/CPF do cliente          *
    *     $filtroAdicional - String adicional para o WHERE         *
    *     $retSql - Se TRUE, em vez de executar a consulta irá     *
    *               retornar o SQL para ser rodado em outro lugar  *
    * Descrição:                                                   *
    *     Lista o total de fretes carreto não faturados, podendo   *
    *     agrupar por Cliente                                      *
    ****************************************************************/
    function contasReceberCarretoFaturado($ano, $mes = NULL, $opcao = NULL, $groupCnpjCpf = NULL, $filtroAdicional = NULL, $retSql = NULL){
        $sql = "SELECT SUM(DOC.VALFRETE) VALOR ";

        if ($groupCnpjCpf != NULL) $sql .= ", CAST(C.CNPJ_CPF AS VARCHAR(8)) CNPJ ";

        $sql .= "
            FROM CARRETO DOC
            JOIN CLIENTE C ON (C.ID_CLIENTE = DOC.ID_CLIENTE)
            WHERE
                DOC.STATUS <> 'C' AND DOC.IDDUPLIC IS NULL
                AND YEAR(DOC.DATASAIDA) = $ano ";

        if ($mes != NULL) $sql .= " AND MONTH(DOC.DATASAIDA) = $mes ";

        if ($opcao != NULL)
            $sql .= " AND DOC.IDDUPLIC IS " . (($opcao == 'faturado') ? " NOT NULL " : " NULL ");

        if ($filtroAdicional != NULL) $sql .= " " . $filtroAdicional . " ";

        if ($groupCnpjCpf != NULL) $sql .= " GROUP BY CAST(C.CNPJ_CPF AS VARCHAR(8)) ";

        return ($retSql != NULL) ? $sql : getConsultaSQLSimples($sql);
    }

   /****************************************************************
    * Função: contasReceberNFVendaFaturado                         *
    * Programador: Paulo Silva                                     *
    * Data: 16/06/2015                                             *
    * Parâmetros:                                                  *
    *     $ano - Ano de emissão do documento                       *
    *     $mes - Mês de emissão do documento                       *
    *     $opcao - Escolhe entre trazer os faturados ou não        *
    *              (NULL = Todos)                                  *
    *     $groupCnpjCpf - Agrupar por CNPJ/CPF do cliente          *
    *     $filtroAdicional - String adicional para o WHERE         *
    *     $retSql - Se TRUE, em vez de executar a consulta irá     *
    *               retornar o SQL para ser rodado em outro lugar  *
    * Descrição:                                                   *
    *     Lista o total de notas de venda não faturadas, podendo   *
    *     agrupar por Cliente                                      *
    ****************************************************************/
    function contasReceberNFVendaFaturado($ano, $mes = NULL, $opcao = NULL, $groupCnpjCpf = NULL, $filtroAdicional = NULL, $retSql = NULL){
        $sql = "SELECT SUM(DOC.VLR_TOTAL) VALOR ";

        if ($groupCnpjCpf != NULL) $sql .= ", CAST(C.CNPJ_CPF AS VARCHAR(8)) CNPJ ";

        $sql .= "
            FROM NOTAFAT DOC
            JOIN CLIENTE C ON (C.ID_CLIENTE = DOC.ID_CLIENTE)
            WHERE
                DOC.STATUS <> 'C'
                AND DOC.CODIGO_CFOP IN ('5.551', '6.551', '5.102', '6.102')
                AND YEAR(DOC.DATA_EMIS) = $ano ";

        if ($mes != NULL) $sql .= " AND MONTH(DOC.DATA_EMIS) = $mes ";

        if ($opcao != NULL)
            $sql .= " AND DOC.IDDUPLIC IS " . (($opcao == 'faturado') ? " NOT NULL " : " NULL ");

        if ($filtroAdicional != NULL) $sql .= " " . $filtroAdicional . " ";

        if ($groupCnpjCpf != NULL) $sql .= " GROUP BY CAST(C.CNPJ_CPF AS VARCHAR(8)) ";

        return ($retSql != NULL) ? $sql : getConsultaSQLSimples($sql);
    }

   /****************************************************************
    * Função: contasReceberNFServFaturado                          *
    * Programador: Paulo Silva                                     *
    * Data: 16/06/2015                                             *
    * Parâmetros:                                                  *
    *     $ano - Ano de emissão do documento                       *
    *     $mes - Mês de emissão do documento                       *
    *     $opcao - Escolhe entre trazer os faturados ou não        *
    *              (NULL = Todos)                                  *
    *     $groupCnpjCpf - Agrupar por CNPJ/CPF do cliente          *
    *     $filtroAdicional - String adicional para o WHERE         *
    *     $retSql - Se TRUE, em vez de executar a consulta irá     *
    *               retornar o SQL para ser rodado em outro lugar  *
    * Descrição:                                                   *
    *     Lista o total de notas de serviço não faturadas, podendo *
    *     agrupar por Cliente                                      *
    ****************************************************************/
    function contasReceberNFServFaturado($ano, $mes = NULL, $opcao = NULL, $groupCnpjCpf = NULL, $filtroAdicional = NULL, $retSql = NULL){
        $sql = "SELECT SUM(DOC.VALTOTSERV) VALOR ";

        if ($groupCnpjCpf != NULL) $sql .= ", CAST(C.CNPJ_CPF AS VARCHAR(8)) CNPJ ";

        $sql .= "
            FROM NOTASER DOC
            JOIN HCLIENTE C ON (C.IDHCLIENTE = DOC.IDHCLIENTE)
            WHERE
                DOC.STATUS <> 'C'
                AND YEAR(DOC.DATAEMIS) = $ano ";

        if ($mes != NULL) $sql .= " AND MONTH(DOC.DATAEMIS) = $mes ";

        if ($opcao != NULL)
            $sql .= " AND DOC.IDDUPLIC IS " . (($opcao == 'faturado') ? " NOT NULL " : " NULL ");

        if ($filtroAdicional != NULL) $sql .= " " . $filtroAdicional . " ";

        if ($groupCnpjCpf != NULL) $sql .= " GROUP BY CAST(C.CNPJ_CPF AS VARCHAR(8)) ";

        return ($retSql != NULL) ? $sql : getConsultaSQLSimples($sql);
    }

   /****************************************************************
    * Função: contasReceberNFDebFaturado                           *
    * Programador: Paulo Silva                                     *
    * Data: 16/06/2015                                             *
    * Parâmetros:                                                  *
    *     $ano - Ano de emissão do documento                       *
    *     $mes - Mês de emissão do documento                       *
    *     $opcao - Escolhe entre trazer os faturados ou não        *
    *              (NULL = Todos)                                  *
    *     $groupCnpjCpf - Agrupar por CNPJ/CPF do cliente          *
    *     $filtroAdicional - String adicional para o WHERE         *
    *     $retSql - Se TRUE, em vez de executar a consulta irá     *
    *               retornar o SQL para ser rodado em outro lugar  *
    * Descrição:                                                   *
    *     Lista o total de notas de debito não faturadas, podendo  *
    *     agrupar por Cliente                                      *
    ****************************************************************/
    function contasReceberNFDebFaturado($ano, $mes = NULL, $opcao = NULL, $groupCnpjCpf = NULL, $filtroAdicional = NULL, $retSql = NULL){
        $sql = "SELECT SUM(DOC.VALOR) VALOR ";

        if ($groupCnpjCpf != NULL) $sql .= ", CAST(C.CNPJ_CPF AS VARCHAR(8)) CNPJ ";

        $sql .= "
            FROM NOTADEB DOC
            JOIN HCLIENTE C ON (C.IDHCLIENTE = DOC.IDHCLIENTE)
            WHERE
                DOC.STATUS <> 'C'
                AND YEAR(DOC.DATAEMISSAO) = $ano ";

        if ($mes != NULL) $sql .= " AND MONTH(DOC.DATAEMISSAO) = $mes ";

        if ($opcao != NULL)
            $sql .= " AND DOC.IDDUPLIC IS " . (($opcao == 'faturado') ? " NOT NULL " : " NULL ");

        if ($filtroAdicional != NULL) $sql .= " " . $filtroAdicional . " ";

        if ($groupCnpjCpf != NULL) $sql .= " GROUP BY CAST(C.CNPJ_CPF AS VARCHAR(8)) ";

        return ($retSql != NULL) ? $sql : getConsultaSQLSimples($sql);
    }

   /****************************************************************
    * Função: contasReceberDocsFaturado                            *
    * Programador: Paulo Silva                                     *
    * Data: 16/06/2015                                             *
    * Parâmetros:                                                  *
    *     $ano - Ano de emissão dos documentos                     *
    *     $mes - Mês de emissão dos documentos                     *
    *     $opcao - Escolhe entre trazer os faturados ou não        *
    *              (NULL = Todos)                                  *
    *     $filtroAdicional - String adicional para o WHERE         *
    * Descrição:                                                   *
    *     Lista o total de documentos não faturados agrupando por  *
    *     cliente e permitindo filtrar se desejado                 *
    ****************************************************************/
    function contasReceberDocsFaturado($ano, $mes = NULL, $opcao = NULL, $filtroAdicional = NULL){
        $sql =
            "SELECT
                SUM(VALOR), CNPJ
            FROM ("
                . contasReceberCtFaturado     ($ano, $mes, $opcao, true, $filtroAdicional, true) . " UNION ALL "
                . contasReceberCarretoFaturado($ano, $mes, $opcao, true, $filtroAdicional, true) . " UNION ALL "
                . contasReceberNFVendaFaturado($ano, $mes, $opcao, true, $filtroAdicional, true) . " UNION ALL "
                . contasReceberNFServFaturado ($ano, $mes, $opcao, true, $filtroAdicional, true) . " UNION ALL "
                . contasReceberNFDebFaturado  ($ano, $mes, $opcao, true, $filtroAdicional, true) .
            ")
            GROUP BY CNPJ
            ORDER BY SUM(VALOR) DESC";

        return getConsultaSQL($sql);
    }

   /****************************************************************
    * Função: contasReceberListarCtNaoFaturado                     *
    * Programador: Paulo Silva                                     *
    * Data: 16/06/2015                                             *
    * Parâmetros:                                                  *
    *     $ano - Ano de emissão do documento                       *
    *     $mes - Mês de emissão do documento                       *
    *     $opcao - Escolhe entre trazer os faturados ou não        *
    *              (NULL = Todos)                                  *
    *     $filtroAdicional - String adicional para o WHERE         *
    *     $retSql - Se TRUE, em vez de executar a consulta irá     *
    *               retornar o SQL para ser rodado em outro lugar  *
    * Descrição:                                                   *
    *     Lista conhecimentos não faturados                        *
    ****************************************************************/
    function contasReceberListarCtNaoFaturado($ano, $mes = NULL, $opcao = NULL, $filtroAdicional = NULL, $retSql = NULL){
        $sql =
            "SELECT 'CT-e' TIPO, (F.SIGLA_FILIAL || ' - ' || DOC.NUMERO) DOCUMENTO,
             DOC.VALTOTFRETE AS VALOR, C.RAZAO_SOCIAL CLIENTE, DOC.ID_CT ID,
             DOC.TIPOCTRC TIPOCT, DOC.DATAEMISSAO EMISSAO ";

        $sql .=
            "FROM CT DOC
            JOIN HCLIENTE C ON (C.IDHCLIENTE = DOC.IDHCLIENTE)
            JOIN FILIAL F ON (F.ID_FILIAL = DOC.ID_FILIAL)
            WHERE
                DOC.STATUSCT <> 'C'
                AND YEAR(DOC.DATAEMISSAO) = $ano ";

        if ($mes != NULL) $sql .= " AND MONTH(DOC.DATAEMISSAO) = $mes ";

        if ($opcao != NULL)
            $sql .= " AND DOC.IDDUPLIC IS " . (($opcao == 'faturado') ? " NOT NULL " : " NULL ");

        if ($filtroAdicional != NULL) $sql .= " " . $filtroAdicional . " ";

        return ($retSql != NULL) ? $sql : getConsultaSQL($sql);
    }

   /****************************************************************
    * Função: contasReceberListarCtFatura                          *
    * Programador: Paulo Silva -> Gabriel Luis                                    *
    * Data: 26/09/2015                                             *
    * Parâmetros:                                                  *
    *     $idFatura - numero identificador da fatura               *
    *     $retSql - Se TRUE, em vez de executar a consulta irá     *
    *               retornar o SQL para ser rodado em outro lugar  *
    * Descrição:                                                   *
    *     Lista conhecimentos de uma determinada fatura            *
    ****************************************************************/
    function contasReceberListarCtFatura($idFatura = NULL, $retSql = NULL){
        $sql =
            "SELECT 'CT-e' TIPO, (F.SIGLA_FILIAL || ' - ' || DOC.NUMERO) DOCUMENTO,
             DOC.VALTOTFRETE AS VALOR, C.RAZAO_SOCIAL CLIENTE, DOC.ID_CT ID,
             DOC.TIPOCTRC TIPOCT, DOC.DATAEMISSAO EMISSAO ";

        $sql .=
            "FROM CT DOC
            JOIN HCLIENTE C ON (C.IDHCLIENTE = DOC.IDHCLIENTE)
            JOIN FILIAL F ON (F.ID_FILIAL = DOC.ID_FILIAL)
            WHERE
                DOC.IDDUPLIC='$idFatura'
                 ";

        return ($retSql != NULL) ? $sql : getConsultaSQL($sql);
    }

   /****************************************************************
    * Função: contasReceberListarCarretoFatura                     *
    * Programador: Paulo Silva -> Gabriel Luis                                    *
    * Data: 26/09/2015                                             *
    * Parâmetros:                                                  *
    *     $idFatura - numero identificador da fatura               *
    *     $retSql - Se TRUE, em vez de executar a consulta irá     *
    *               retornar o SQL para ser rodado em outro lugar  *
    * Descrição:                                                   *
    *     Lista Carretos de uma determinada fatura                 *
    ****************************************************************/
    function contasReceberListarCarretoFatura($idFatura, $retSql = NULL){
        $sql =
            "SELECT 'Carreto' TIPO, (F.SIGLA_FILIAL || ' - ' || DOC.NUMERO) DOCUMENTO,
             DOC.VALFRETE AS VALOR, C.RAZAO_SOCIAL CLIENTE, DOC.ID_CARRETO ID,
             '' TIPOCT, DOC.DATASAIDA EMISSAO ";

        $sql .=
            "FROM CARRETO DOC
            JOIN CLIENTE C ON (C.ID_CLIENTE = DOC.ID_CLIENTE)
            JOIN FILIAL F ON (F.ID_FILIAL = DOC.ID_FILIAL)
            WHERE
                DOC.IDDUPLIC = '$idFatura'
                ";

        return ($retSql != NULL) ? $sql : getConsultaSQL($sql);
    }

   /****************************************************************
    * Função: contasReceberListarNFServFatura                      *
    * Programador: Paulo Silva -> Gabriel Luis                     *
    * Data: 26/06/2015                                             *
    * Parâmetros:                                                  *
    *     $idFatura - numero identificador da fatura               *
    *     $retSql - Se TRUE, em vez de executar a consulta irá     *
    *               retornar o SQL para ser rodado em outro lugar  *
    * Descrição:                                                   *
    *     Lista as notas de serviço de uma determinada fatura      *
    ****************************************************************/
    function contasReceberListarNFServFatura($idFatura, $retSql = NULL){
        $sql =
            "SELECT 'NF Serviço' TIPO, (F.SIGLA_FILIAL || ' - ' || DOC.NUMERO) DOCUMENTO,
             DOC.VALTOTSERV AS VALOR, C.RAZAO_SOCIAL CLIENTE, DOC.ID_NOTASER ID,
             '' TIPOCT, DOC.DATAEMIS EMISSAO ";

        $sql .=
            "FROM NOTASER DOC
            JOIN HCLIENTE C ON (C.IDHCLIENTE = DOC.IDHCLIENTE)
            JOIN FILIAL F ON (F.ID_FILIAL = DOC.ID_FILIAL)
            WHERE
                DOC.IDDUPLIC = '$idFatura'
                ";

        return ($retSql != NULL) ? $sql : getConsultaSQL($sql);
    }

   /****************************************************************
    * Função: contasReceberListarNFVendaFatura                     *
    * Programador: Paulo Silva -> Gabriel Luis                     *
    * Data: 26/09/2015                                             *
    * Parâmetros:                                                  *
    *     $idFatura - numero identificador da fatura               *
    *     $retSql - Se TRUE, em vez de executar a consulta irá     *
    *               retornar o SQL para ser rodado em outro lugar  *
    * Descrição:                                                   *
    *     Lista as notas de venda de uma determinada fatura        *
    ****************************************************************/
    function contasReceberListarNFVendaFatura($idFatura, $retSql = NULL){
        $sql =
            "SELECT 'NF Venda' TIPO, (F.SIGLA_FILIAL || ' - ' || DOC.NUMERO) DOCUMENTO,
             DOC.VLR_TOTAL AS VALOR, C.RAZAO_SOCIAL CLIENTE, DOC.ID_NOTAFAT ID,
             '' TIPOCT, DOC.DATA_EMIS EMISSAO ";

        $sql .=
            "FROM NOTAFAT DOC
            JOIN CLIENTE C ON (C.ID_CLIENTE = DOC.ID_CLIENTE)
            JOIN FILIAL F ON (F.ID_FILIAL = DOC.ID_FILIAL)
            WHERE
                DOC.IDDUPLIC = '$idFatura'
                ";

        return ($retSql != NULL) ? $sql : getConsultaSQL($sql);
    }

   /****************************************************************
    * Função: contasReceberListarNFDebFatura                       *
    * Programador: Paulo Silva -> Gabriel Luis                     *
    * Data: 26/09/2015                                             *
    * Parâmetros:                                                  *
    *     $idFatura - numero identificador da fatura               *
    *     $retSql - Se TRUE, em vez de executar a consulta irá     *
    *               retornar o SQL para ser rodado em outro lugar  *
    * Descrição:                                                   *
    *     Lista as notas de debito de uma determinada fatura       *
    ****************************************************************/
    function contasReceberListarNFDebFatura($idFatura, $retSql = NULL){
        $sql =
            "SELECT 'NF Debito' TIPO, (F.SIGLA_FILIAL || ' - ' || DOC.NUMERO) DOCUMENTO,
             DOC.VALOR AS VALOR, C.RAZAO_SOCIAL CLIENTE, DOC.ID_NOTADEB ID,
             '' TIPOCT, DOC.DATAEMISSAO EMISSAO ";

        $sql .=
            "FROM NOTADEB DOC
            JOIN HCLIENTE C ON (C.IDHCLIENTE = DOC.IDHCLIENTE)
            JOIN FILIAL F ON (F.ID_FILIAL = DOC.ID_FILIAL)
            WHERE
                DOC.IDDUPLIC = '$idFatura'
                ";

        return ($retSql != NULL) ? $sql : getConsultaSQL($sql);
    }

   /****************************************************************
    * Função: contasReceberListarCarretoNaoFaturado                *
    * Programador: Paulo Silva                                     *
    * Data: 16/06/2015                                             *
    * Parâmetros:                                                  *
    *     $ano - Ano de emissão do documento                       *
    *     $mes - Mês de emissão do documento                       *
    *     $opcao - Escolhe entre trazer os faturados ou não        *
    *              (NULL = Todos)                                  *
    *     $filtroAdicional - String adicional para o WHERE         *
    *     $retSql - Se TRUE, em vez de executar a consulta irá     *
    *               retornar o SQL para ser rodado em outro lugar  *
    * Descrição:                                                   *
    *     Lista os fretes carreto não faturados                    *
    ****************************************************************/
    function contasReceberListarCarretoNaoFaturado($ano, $mes = NULL, $opcao = NULL, $filtroAdicional = NULL, $retSql = NULL){
        $sql =
            "SELECT 'Carreto' TIPO, (F.SIGLA_FILIAL || ' - ' || DOC.NUMERO) DOCUMENTO,
             DOC.VALFRETE AS VALOR, C.RAZAO_SOCIAL CLIENTE, DOC.ID_CARRETO ID,
             '' TIPOCT, DOC.DATASAIDA EMISSAO ";

        $sql .=
            "FROM CARRETO DOC
            JOIN CLIENTE C ON (C.ID_CLIENTE = DOC.ID_CLIENTE)
            JOIN FILIAL F ON (F.ID_FILIAL = DOC.ID_FILIAL)
            WHERE
                DOC.STATUS <> 'C'
                AND YEAR(DOC.DATASAIDA) = $ano ";

        if ($mes != NULL) $sql .= " AND MONTH(DOC.DATASAIDA) = $mes ";

        if ($opcao != NULL)
            $sql .= " AND DOC.IDDUPLIC IS " . (($opcao == 'faturado') ? " NOT NULL " : " NULL ");

        if ($filtroAdicional != NULL) $sql .= " " . $filtroAdicional . " ";

        return ($retSql != NULL) ? $sql : getConsultaSQL($sql);
    }

   /****************************************************************
    * Função: contasReceberListarNFVendaNaoFaturado                *
    * Programador: Paulo Silva                                     *
    * Data: 16/06/2015                                             *
    * Parâmetros:                                                  *
    *     $ano - Ano de emissão do documento                       *
    *     $mes - Mês de emissão do documento                       *
    *     $opcao - Escolhe entre trazer os faturados ou não        *
    *              (NULL = Todos)                                  *
    *     $filtroAdicional - String adicional para o WHERE         *
    *     $retSql - Se TRUE, em vez de executar a consulta irá     *
    *               retornar o SQL para ser rodado em outro lugar  *
    * Descrição:                                                   *
    *     Lista as notas de venda não faturadas                    *
    ****************************************************************/
    function contasReceberListarNFVendaNaoFaturado($ano, $mes = NULL, $opcao = NULL, $filtroAdicional = NULL, $retSql = NULL){
        $sql =
            "SELECT 'NF Venda' TIPO, (F.SIGLA_FILIAL || ' - ' || DOC.NUMERO) DOCUMENTO,
             DOC.VLR_TOTAL AS VALOR, C.RAZAO_SOCIAL CLIENTE, DOC.ID_NOTAFAT ID,
             '' TIPOCT, DOC.DATA_EMIS EMISSAO ";

        $sql .=
            "FROM NOTAFAT DOC
            JOIN CLIENTE C ON (C.ID_CLIENTE = DOC.ID_CLIENTE)
            JOIN FILIAL F ON (F.ID_FILIAL = DOC.ID_FILIAL)
            WHERE
                DOC.STATUS <> 'C'
                AND DOC.CODIGO_CFOP IN ('5.551', '6.551', '5.102', '6.102')
                AND YEAR(DOC.DATA_EMIS) = $ano ";

        if ($mes != NULL) $sql .= " AND MONTH(DOC.DATA_EMIS) = $mes ";

        if ($opcao != NULL)
            $sql .= " AND DOC.IDDUPLIC IS " . (($opcao == 'faturado') ? " NOT NULL " : " NULL ");

        if ($filtroAdicional != NULL) $sql .= " " . $filtroAdicional . " ";

        return ($retSql != NULL) ? $sql : getConsultaSQL($sql);
    }

   /****************************************************************
    * Função: contasReceberListarNFServNaoFaturado                 *
    * Programador: Paulo Silva                                     *
    * Data: 16/06/2015                                             *
    * Parâmetros:                                                  *
    *     $ano - Ano de emissão do documento                       *
    *     $mes - Mês de emissão do documento                       *
    *     $opcao - Escolhe entre trazer os faturados ou não        *
    *              (NULL = Todos)                                  *
    *     $filtroAdicional - String adicional para o WHERE         *
    *     $retSql - Se TRUE, em vez de executar a consulta irá     *
    *               retornar o SQL para ser rodado em outro lugar  *
    * Descrição:                                                   *
    *     Lista as notas de serviço não faturadas                  *
    ****************************************************************/
    function contasReceberListarNFServNaoFaturado($ano, $mes = NULL, $opcao = NULL, $filtroAdicional = NULL, $retSql = NULL){
        $sql =
            "SELECT 'NF Serviço' TIPO, (F.SIGLA_FILIAL || ' - ' || DOC.NUMERO) DOCUMENTO,
             DOC.VALTOTSERV AS VALOR, C.RAZAO_SOCIAL CLIENTE, DOC.ID_NOTASER ID,
             '' TIPOCT, DOC.DATAEMIS EMISSAO ";

        $sql .=
            "FROM NOTASER DOC
            JOIN HCLIENTE C ON (C.IDHCLIENTE = DOC.IDHCLIENTE)
            JOIN FILIAL F ON (F.ID_FILIAL = DOC.ID_FILIAL)
            WHERE
                DOC.STATUS <> 'C'
                AND YEAR(DOC.DATAEMIS) = $ano ";

        if ($mes != NULL) $sql .= " AND MONTH(DOC.DATAEMIS) = $mes ";

        if ($opcao != NULL)
            $sql .= " AND DOC.IDDUPLIC IS " . (($opcao == 'faturado') ? " NOT NULL " : " NULL ");

        if ($filtroAdicional != NULL) $sql .= " " . $filtroAdicional . " ";

        return ($retSql != NULL) ? $sql : getConsultaSQL($sql);
    }

   /****************************************************************
    * Função: contasReceberListarNFDebNaoFaturado                  *
    * Programador: Paulo Silva                                     *
    * Data: 16/06/2015                                             *
    * Parâmetros:                                                  *
    *     $ano - Ano de emissão do documento                       *
    *     $mes - Mês de emissão do documento                       *
    *     $opcao - Escolhe entre trazer os faturados ou não        *
    *              (NULL = Todos)                                  *
    *     $filtroAdicional - String adicional para o WHERE         *
    *     $retSql - Se TRUE, em vez de executar a consulta irá     *
    *               retornar o SQL para ser rodado em outro lugar  *
    * Descrição:                                                   *
    *     Lista as notas de debito não faturadss                   *
    ****************************************************************/
    function contasReceberListarNFDebNaoFaturado($ano, $mes = NULL, $opcao = NULL, $filtroAdicional = NULL, $retSql = NULL){
        $sql =
            "SELECT 'NF Debito' TIPO, (F.SIGLA_FILIAL || ' - ' || DOC.NUMERO) DOCUMENTO,
             DOC.VALOR AS VALOR, C.RAZAO_SOCIAL CLIENTE, DOC.ID_NOTADEB ID,
             '' TIPOCT, DOC.DATAEMISSAO EMISSAO ";

        $sql .=
            "FROM NOTADEB DOC
            JOIN HCLIENTE C ON (C.IDHCLIENTE = DOC.IDHCLIENTE)
            JOIN FILIAL F ON (F.ID_FILIAL = DOC.ID_FILIAL)
            WHERE
                DOC.STATUS <> 'C'
                AND YEAR(DOC.DATAEMISSAO) = $ano ";

        if ($mes != NULL) $sql .= " AND MONTH(DOC.DATAEMISSAO) = $mes ";

        if ($opcao != NULL)
            $sql .= " AND DOC.IDDUPLIC IS " . (($opcao == 'faturado') ? " NOT NULL " : " NULL ");

        if ($filtroAdicional != NULL) $sql .= " " . $filtroAdicional . " ";

        return ($retSql != NULL) ? $sql : getConsultaSQL($sql);
    }

   /****************************************************************
    * Função: contasReceberListarDocNaoFaturado                    *
    * Programador: Paulo Silva                                     *
    * Data: 16/06/2015                                             *
    * Parâmetros:                                                  *
    *     $ano - Ano de emissão dos documentos                     *
    *     $mes - Mês de emissão dos documentos                     *
    *     $opcao - Escolhe entre trazer os faturados ou não        *
    *              (NULL = Todos)                                  *
    *     $filtroAdicional - String adicional para o WHERE         *
    * Descrição:                                                   *
    *     Lista os documentoso não faturados incluindo seu número, *
    *     filial, cliente, valor total e data de emissão           *
    ****************************************************************/
    function contasReceberListarDocNaoFaturado($ano, $mes = NULL, $opcao = NULL, $filtroAdicional = NULL){
        $sql =
            "SELECT
                TIPO, DOCUMENTO, VALOR, CLIENTE, ID, TIPOCT, EMISSAO
            FROM ("
                . contasReceberListarCtNaoFaturado     ($ano, $mes, $opcao, $filtroAdicional, true) . " UNION ALL "
                . contasReceberListarCarretoNaoFaturado($ano, $mes, $opcao, $filtroAdicional, true) . " UNION ALL "
                . contasReceberListarNFVendaNaoFaturado($ano, $mes, $opcao, $filtroAdicional, true) . " UNION ALL "
                . contasReceberListarNFServNaoFaturado ($ano, $mes, $opcao, $filtroAdicional, true) . " UNION ALL "
                . contasReceberListarNFDebNaoFaturado  ($ano, $mes, $opcao, $filtroAdicional, true) .
            ")
            ORDER BY TIPO, DOCUMENTO";

        return getConsultaSQL($sql);
    }

    /****************************************************************
    * Função: abastecimentosListar                                 *
    * Programador: Gabriel Luis                                    *
    * Data: 01/09/2015                                             *
    * Parâmetros:                                                  *
    *     $ano - Ano de vencimento da parcela                      *
    *     $mes - Mês de vencimento da parcela                      *
    *     $placa - Placa do veiculo                                *
    *     $filtroAdicional - String adicional para o WHERE         *
    * Descrição:                                                   *
    *     Lista os abastecimentos efetuados no                     *
    *     ano e mês desejados e parâmetros adicionais livres       *
    ****************************************************************/
    function abastecimentoListar($ano = NULL, $mes = NULL, $placa = NULL){
        $sql =
            "SELECT IDABAST, DATAABAST, LITROSABAST, VALORABAST, ODOMETRO, ODOANTER
                FROM ABAST
                JOIN VEICULO ON (VEICULO.ID_VEICULO=ABAST.ID_VEICULO)
                WHERE VEICULO.PLACA='$placa'";

        if ($ano != NULL) $sql .= " AND YEAR(DATAABAST)  = $ano ";
        if ($mes != NULL) $sql .= " AND MONTH(DATAABAST) = $mes ";

        $sql .= " ORDER BY DATAABAST ASC";

        return getConsultaSQL($sql);
    }
    
    /***************************************************************
    * Função: faturamentoFretePesoAnoMesSicms                      *
    * Programador: Gabriel Luis                                    *
    * Data: 04/07/2017                                             *
    * Parâmetros:                                                  *
    *     $ano - Ano de vencimento da parcela                      *
    *     $mes - Mês de vencimento da parcela                      *
    *     $imob - imobilizado                                      *
    * Descrição:                                                   *
    *     total faturamento FretePeso Ano e Mes                    *
    ****************************************************************/
    function faturamentoFretePesoAnoMesSicms($ano, $mes, $imob){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        if(!$imob){
            $script_faturamento = "SELECT SUM(FPESO) FROM
                (SELECT SUM(VALFPESOSICMS) AS FPESO
                FROM CT
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = CT.IDCTCUSTO)
                WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes

                UNION
                SELECT SUM(VALFRETE) AS FPESO
                FROM CARRETO
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = FILIAL.IDCTCUSTO)
                WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes

                UNION
                SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
                FROM NOTASER
                JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTASER.IDHCLIENTE)
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTASER.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
                WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes

                UNION
                SELECT SUM(NOTADEB.VALOR) AS FPESO
                FROM NOTADEB
                JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTADEB.ID_FILIAL)
                WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano AND MONTH(NOTADEB.DATAEMISSAO)=$mes
                )";
        }
        if($imob){
            $script_faturamento = "SELECT SUM(FPESO) FROM
                (SELECT SUM(VALFPESOSICMS) AS FPESO
                FROM CT
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = CT.IDCTCUSTO)
                WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes

                UNION
                SELECT SUM(VALFRETE) AS FPESO
                FROM CARRETO
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = FILIAL.IDCTCUSTO)
                WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes

                UNION
                SELECT SUM(VLR_TOTAL) AS FPESO
                FROM NOTAFAT
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTAFAT.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTAFAT.IDCTCUSTO)
                WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes
                AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

                UNION
                SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
                FROM NOTASER
                JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTASER.IDHCLIENTE)
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTASER.ID_FILIAL)
                    JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
                WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes

                UNION
                SELECT SUM(NOTADEB.VALOR) AS FPESO
                FROM NOTADEB
                JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTADEB.ID_FILIAL)
                WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano AND MONTH(NOTADEB.DATAEMISSAO)=$mes
                )";
        }

        $db2_faturamento = db2_exec($hDbcDB2, $script_faturamento);
        $dados_faturamento = db2_fetch_array($db2_faturamento);

        if($dados_faturamento[0] == NULL){
            $dados_faturamento[0] = 0;
        }

        return str_replace(",", ".", $dados_faturamento[0]);
    }
    
    /***************************************************************
    * Função: faturamentoFretePesoAnoMesSicmsAFTO                  *
    * Programador: Gabriel Luis                                    *
    * Data: 04/07/2017                                             *
    * Parâmetros:                                                  *
    *     $ano - Ano de vencimento da parcela                      *
    *     $mes - Mês de vencimento da parcela                      *
    *     $afto - contrato agregado, frota, terceiro, outros       *
    *     $imob - imobilizado                                      *
    * Descrição:                                                   *
    *     total faturamento FretePesoAFTO Ano e Mes                *
    ****************************************************************/
    function faturamentoAnoMesSicmsAFTO($ano, $mes, $afto, $imob){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        if(!$imob){
            $sql = "SELECT SUM(FPESO) FROM
            (SELECT SUM(VALFPESOSICMS) AS FPESO
            FROM CT
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
            WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes AND HVEICEMP.STAFT='$afto'

            UNION
            SELECT SUM(VALFRETE) AS FPESO
            FROM CARRETO
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
            WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes AND HVEICEMP.STAFT='$afto'

            UNION
            SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
            FROM NOTASER
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
            WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes AND HVEICEMP.STAFT='$afto'

            )";
        }
        if($imob){
            $sql = "SELECT SUM(FPESO) FROM
            (SELECT SUM(VALFPESOSICMS) AS FPESO
            FROM CT
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
            WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes AND HVEICEMP.STAFT='$afto'

            UNION
            SELECT SUM(VALFRETE) AS FPESO
            FROM CARRETO
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
            WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes AND HVEICEMP.STAFT='$afto'

            UNION
            SELECT SUM(VLR_TOTAL) AS FPESO
            FROM NOTAFAT
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTAFAT.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTAFAT.ID_HVEICULO)
            WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes AND HVEICEMP.STAFT='$afto'
            AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

            UNION
            SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
            FROM NOTASER
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
            WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes AND HVEICEMP.STAFT='$afto'

            )";
        }
        $db2 = db2_exec($hDbcDB2, $sql);
        $dados = db2_fetch_array($db2);
        if($dados[0] == NULL){
            $dados[0] = 0;
        }
        return $dados[0];
    }
    
    /***************************************************************
    * Função: faturamentoAnoMesOutrosSicms                         *
    * Programador: Gabriel Luis                                    *
    * Data: 04/07/2017                                             *
    * Parâmetros:                                                  *
    *     $ano - Ano de vencimento da parcela                      *
    *     $mes - Mês de vencimento da parcela                      *
    *     $imob - imobilizado                                      *
    * Descrição:                                                   *
    *     total faturamento OUTROS Ano e Mes                       *
    ****************************************************************/
    function faturamentoAnoMesOutrosSicms($ano, $mes, $imob){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        if(!$imob){
            $sql = "SELECT SUM(FPESO) FROM
                (SELECT SUM(CT.VALFPESOSICMS) AS FPESO
                FROM CT
                WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes AND CT.ID_HVEICULO IS NULL

                UNION
                SELECT SUM(VALFRETE) AS FPESO
                FROM CARRETO
                WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes AND CARRETO.ID_HVEICULO IS NULL

                UNION
                SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
                FROM NOTASER
                WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes AND NOTASER.ID_HVEICULO IS NULL

                UNION
                SELECT SUM(NOTADEB.VALOR) AS FPESO
                FROM NOTADEB
                WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano AND MONTH(NOTADEB.DATAEMISSAO)=$mes

                )";
        }
        if($imob){
            $sql = "SELECT SUM(FPESO) FROM
                (SELECT SUM(CT.VALFPESOSICMS) AS FPESO
                FROM CT
                WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes AND CT.ID_HVEICULO IS NULL

                UNION
                SELECT SUM(VALFRETE) AS FPESO
                FROM CARRETO
                WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes AND CARRETO.ID_HVEICULO IS NULL

                UNION
                SELECT SUM(VLR_TOTAL) AS FPESO
                FROM NOTAFAT
                WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes AND NOTAFAT.ID_VEICULO IS NULL
                AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

                UNION
                SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
                FROM NOTASER
                WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes AND NOTASER.ID_HVEICULO IS NULL

                UNION
                SELECT SUM(NOTADEB.VALOR) AS FPESO
                FROM NOTADEB
                WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano AND MONTH(NOTADEB.DATAEMISSAO)=$mes

                )";
        }
        $db2 = db2_exec($hDbcDB2, $sql);
        $dados = db2_fetch_array($db2);
        if($dados[0] == NULL){
            $dados[0] = 0;
        }
        return $dados[0];
    }
    
    /***************************************************************
    * Função: nomeFilial                                           *
    * Programador: Gabriel Luis                                    *
    * Data: 10/07/2017                                             *
    * Parâmetros:                                                  *
    *     $idFilial - id da filial                                 *
    * Descrição:                                                   *
    *     Retorna nome da filial                                   *
    ****************************************************************/
    function nomeFilial($idFilial){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        $sql = "SELECT * FROM FILIAL WHERE ID_FILIAL=$idFilial";
        $db2 = db2_exec($hDbcDB2, $sql);
        $dados = db2_fetch_array($db2);
        if($dados[0] == NULL){
            $dados[0] = 0;
        }
        return $dados[4];
    }
    
    /***************************************************************
    * Função: faturamentoFretePesoAnoMesDiaSicmsAFTO               *
    * Programador: Gabriel Luis                                    *
    * Data: 03/08/2017                                             *
    * Parâmetros:                                                  *
    *     $ano - Ano de emissao do documento                      *
    *     $mes - Mês de emissao do documento                      *
    *     $dia - dia de emissao do documento
    *     $afto - contrato agregado, frota, terceiro, outros       *
    *     $imob - imobilizado                                      *
    * Descrição:                                                   *
    *     total faturamento FretePesoAFTO Ano e Mes                *
    ****************************************************************/
    function faturamentoAnoMesDiaSicmsAFTO($ano, $mes, $dia, $afto, $imob){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        if(!$imob){
            $sql = "SELECT SUM(FPESO) FROM
            (SELECT SUM(VALFPESOSICMS) AS FPESO
            FROM CT
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
            WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes AND DAY(CT.DATAEMISSAO)=$dia AND HVEICEMP.STAFT='$afto'

            UNION
            SELECT SUM(VALFRETE) AS FPESO
            FROM CARRETO
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
            WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes AND DAY(CARRETO.DATASAIDA)=$dia AND HVEICEMP.STAFT='$afto'

            UNION
            SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
            FROM NOTASER
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
            WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes AND DAY(NOTASER.DATAEMIS)=$dia AND HVEICEMP.STAFT='$afto'

            )";
        }
        if($imob){
            $sql = "SELECT SUM(FPESO) FROM
            (SELECT SUM(VALFPESOSICMS) AS FPESO
            FROM CT
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CT.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CT.IDHVEICEMP)
            WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$mes AND DAY(CT.DATAEMISSAO)=$dia AND HVEICEMP.STAFT='$afto'

            UNION
            SELECT SUM(VALFRETE) AS FPESO
            FROM CARRETO
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CARRETO.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = CARRETO.IDHVEICEMP)
            WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$mes AND DAY(CARRETO.DATASAIDA)=$dia AND HVEICEMP.STAFT='$afto'

            UNION
            SELECT SUM(VLR_TOTAL) AS FPESO
            FROM NOTAFAT
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTAFAT.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTAFAT.ID_HVEICULO)
            WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$mes AND DAY(NOTAFAT.DATA_EMIS)=$dia AND HVEICEMP.STAFT='$afto'
            AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

            UNION
            SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
            FROM NOTASER
                JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = NOTASER.ID_HVEICULO)
                JOIN HVEICEMP ON (HVEICEMP.IDHVEICEMP = NOTASER.IDHVEICEMP)
            WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$mes AND DAY(NOTASER.DATAEMIS)=$dia AND HVEICEMP.STAFT='$afto'

            )";
        }
        $db2 = db2_exec($hDbcDB2, $sql);
        $dados = db2_fetch_array($db2);
        if($dados[0] == NULL){
            $dados[0] = 0;
        }
        return $dados[0];
    }
    
    /***************************************************************
    * Função: nomePlaca                                          *
    * Programador: Gabriel Luis                                    *
    * Data: 20/02/2018                                             *
    * Parâmetros:                                                  *
    *     $idPlaca - id da placa                                   *
    * Descrição:                                                   *
    *     Retorna nome da placa xxx-0000                           *
    ****************************************************************/
    function nomePlaca($idPlaca){
        include $_SERVER['DOCUMENT_ROOT'] .  '/old/connect_db2_bino.php';
        $sql = "SELECT PLACA FROM VEICULO WHERE ID_VEICULO='$idPlaca' FETCH FIRST 1 ROWS ONLY";
        $db2 = db2_exec($hDbcDB2, $sql);
        $dados = db2_fetch_array($db2);
        if($dados[0] == NULL){
            $dados[0] = 0;
        }
        return $dados[0];
    }
?>