<?php
    /**
     * Package de funções referentes ao DB2, como consultas de informações para o BI
     *
     * @author Paulo Silva
     * @date 05/08/2015
     * @version 1.24
     * @package Library/Scripts
     */

    namespace Library\Scripts;

    /**
     * Package de funções referentes ao DB2, como consultas de informações para o BI
     *
     * @author Paulo Silva
     * @date 05/08/2015
     * @version 1.24
     * @package Library/Scripts
     * @subpackage DB2
     */
    final class scriptDB2 extends \Library\Classes\connectDB2 {
        /**
         * Construtor responsável por setar as configurações de conexão do objeto criado e conectar automaticamente para uso facilitado da classe
         */
        public function __construct(){ parent::__construct(); $this->connect(); }

        /**
         * Método destrutor responsável por realizar a desconexão com o banco automaticamente
         */
        public function __destruct(){ $this->disconnect(); }

        /**
         * Lista de placas cadastradas no GOE
         *
         * @Author Paulo Silva
         * @date 05/08/2015
         * @version 1.0
         * @param int $selected ID da opção que deverá estar selecionada
         * @return string Código HTML para um <select> de placas
         */
        public function listaPlacas($selected = null){
            $dados = $this->select("SELECT ID_VEICULO ID, PLACA FROM VEICULO", null, "PLACA");

            $lista .= "<option value=''>Selecione...</option>";

            foreach ($dados as $option)
                $lista .= "<option value='" . trim($option['ID']) . "' " . ((trim($option['ID']) == $selected) ? "selected" : "") . ">$option[PLACA]</option>";

            return $lista;
        }

        /**
         * Lista de filiais cadastradas e não bloqueadas.
         * Função personalizadas para executar o FETCH_BOTH ao invés do ASSOC, para possibilitar a criação de um <select> com as informações encontradas posteriormente
         *
         * @author Paulo Silva
         * @date 08/10/2015
         * @return mixed Result Set com a lista de siglas e nomes das filiais encontradas
         */
        public function listaFiliais(){
            try {
                $sql = "SELECT F.SIGLA_FILIAL SIGLA, F.NOME_FILIAL FILIAL FROM FILIAL F WHERE F.BLOQUEADO = 'N' AND F.ID_EMPRESA = 1 ORDER BY F.NOME_FILIAL";

                $pdo = $this->getPDO();

                $stmt = $pdo->prepare($sql);

                $stmt->execute();

                $result = $stmt->fetchAll(\PDO::FETCH_BOTH);

                $errorInfo = $stmt->errorInfo();

                $stmt->closeCursor();

                $this->_lastResult = $result;

                if (!$result) error_log(print_r($errorInfo, true));

                return $result ?: $errorInfo[2];
            } catch (PDOException $e) { return array("errorMsg" => $e->getMessage()); }
        }

        /**
         * Lista de clientes cadastrados.
         * Função personalizadas para executar o FETCH_BOTH ao invés do ASSOC, para possibilitar a criação de um <select> com as informações encontradas posteriormente
         *
         * @author Paulo Silva
         * @date 20/10/2015
         * @param bool $trunco Indica se os resultados devem ser agrupados por trunco de CGC
         * @return mixed Result Set com CGC e Razão Social de cada cliente encontrado
         */
        public function listaClientes($trunco = false){
            try {
                $colCgc   = $trunco ? "CAST(C.CNPJ_CPF AS VARCHAR(8))" : "C.CNPJ_CPF";
                $colRazao = $trunco ? "CAST(C.RAZAO_SOCIAL AS VARCHAR(5))" : "CAST(C.RAZAO_SOCIAL AS VARCHAR(30))";

                $sql = "SELECT $colCgc cgc, $colRazao razao
                        FROM CLIENTE C
                        GROUP BY $colCgc, $colRazao
                        ORDER BY $colRazao ASC";

                $pdo = $this->getPDO();

                $stmt = $pdo->prepare($sql);

                $stmt->execute();

                $result = $stmt->fetchAll(\PDO::FETCH_BOTH);

                $errorInfo = $stmt->errorInfo();

                $stmt->closeCursor();

                /** Caso haja trunco por CGC, buscamos a razão social completa de cada cliente para retornar neste mesmo result */
                if ($result && $trunco) for ($i = 0; $i < count($result); $i++) $result[$i]['RAZAO'] = $result[$i][1] = $this->nomeCliente($result[$i]['CGC']);

                $this->_lastResult = $result;

                if (!$result) error_log(print_r($errorInfo, true));

                return $result ?: $errorInfo[2];
            } catch (PDOException $e) { return array("errorMsg" => $e->getMessage()); }
        }

        /**
         * Modelo do veículo no GOE
         *
         * @author Paulo Silva
         * @date 06/08/2015
         * @version 1.0
         * @param int $idVeiculo ID do veículo
         * @return string Modelo vinculado ao veículo no GetOne Enterprise
         */
        public function modeloVeiculo($idVeiculo){
            $params = array( $this->whereParam("ID_VEICULO", $idVeiculo) );

            $this->select("SELECT M.NAME RESULT FROM VEICULO V JOIN MODELO M ON M.CODEMODE = V.CODEMODE", $params, "NAME FETCH FIRST 1 ROWS ONLY");

            return $this->getResultCell() ?: "Modelo não encontrado";
        }

        /**
         * CPF e nome do motorista de certo veículo de acordo com a data
         *
         * @author Paulo Silva
         * @date 26/08/2016
         * @version 1.0
         * @param string $placa Placa do veículo
         * @param date $data Data do histórico desejado para busca (não informar para usar o mais recente)
         * @return string Nome do motorista vinculado ao veículo no GetOne Enterprise na data indicada
         */
        public function motoristaVeiculo($placa, $data = null){
            $params = array( $this->whereParam("V.PLACA", $placa) );

            if ($data) array_push($params, $this->whereParam("V.DATAALT", $data, "<="));

            $this->select("SELECT (M.CPF || ' - ' || M.NOME) RESULT FROM HVEICULO V JOIN HMOTORIS M ON V.IDHMOTORIS = M.IDHMOTORIS", $params, "V.ID_HVEICULO DESC FETCH FIRST 1 ROWS ONLY");

            /** Se não encontrou pela placa do veículo, vai na tabela de reboques fazer a busca */
            if (!$this->getResultCell()){
                $params = array( $this->whereParam("R.PLACA", $placa) );

                if ($data) array_push($params, $this->whereParam("R.DATAALT", $data, "<="));

                $this->select(
                        "SELECT
                            (M.CPF || ' - ' || M.NOME) RESULT
                        FROM HREBOQ R
                        JOIN HVEICULO V ON R.ID_HVEICULO = V.ID_HVEICULO
                        JOIN HMOTORIS M ON V.IDHMOTORIS = M.IDHMOTORIS",
                    $params,
                    "R.ID_HREBOQ DESC FETCH FIRST 1 ROWS ONLY");
            }

            return $this->getResultCell() ?: "Motorista não encontrado";
        }

        /**
         * Sigla e nome da filial no GetOne
         *
         * @author Paulo Silva
         * @date 19/08/2015
         * @version 1.0
         * @param int $idFilial ID da filial
         * @return array Sigla e nome da filial em um array de duas chaves
         */
        public function dadosFilial($idFilial){
            $params = array( $this->whereParam("ID_FILIAL", $idFilial) );

            $this->select("SELECT SIGLA_FILIAL SIGLA, NOME_FILIAL NOME FROM FILIAL", $params, "ID_FILIAL FETCH FIRST 1 ROWS ONLY");

            $result = $this->getResultRow();

            if (!$result) $result['SIGLA'] = "Filial não encontrada";

            return $result;
        }

        /**
         * Placa do veículo no GOE
         *
         * @author Paulo Silva
         * @date 07/08/2015
         * @version 1.0
         * @param int $idVeiculo ID do veículo
         * @return string Placa do veículo no GetOne Enterprise
         */
        public function placaVeiculo($idVeiculo){
            $params = array( $this->whereParam("ID_VEICULO", $idVeiculo) );

            $this->select("SELECT PLACA RESULT FROM VEICULO", $params, "ID_VEICULO FETCH FIRST 1 ROWS ONLY");

            return $this->getResultCell() ?: "Placa não encontrada";
        }

        /**
         * Conjunto do veículo no GOE
         *
         * @author Paulo Silva
         * @date 27/10/2015
         * @param int $idVeiculo ID do veículo
         * @return string Conjunto vinculado ao veículo no GetOne Enterprise
         */
        public function conjuntoVeiculo($idVeiculo){
            $params = array( $this->whereParam("ID_VEICULO", $idVeiculo) );

            $this->select("SELECT C.NAME RESULT FROM VEICULO V JOIN CONJUNTO C ON C.CODECONJ = V.CODECONJ", $params, "NAME FETCH FIRST 1 ROWS ONLY");

            return $this->getResultCell() ?: "Conjunto não encontrado";
        }

        /**
         * Nome do cliente no GOE
         *
         * @author Paulo Silva
         * @date 20/10/2015
         * @param string $cgc CGC do cliente a ser buscado
         * @return string Razão social cadastrada para o respectivo CGC no GetOne Enterprise
         */
        public function nomeCliente($cgc){
            $result = $this->select(
                "SELECT CAST(RAZAO_SOCIAL AS VARCHAR(30)) result FROM CLIENTE WHERE CNPJ_CPF LIKE '$cgc%' ORDER BY ID_CLIENTE FETCH FIRST 1 ROWS ONLY");

            return $this->getResultCell(0, 0, $result);
        }

        /**
         * Lista de CT-es com informações pertinentes ao módulo Sem Parar
         *
         * @param mixed $params Array com filtros para a busca
         * @return mixed Result Set contendo os dados de cada CT-e encontrado
         */
        public function ctesSemParar($params){
            $sql = "SELECT
                        V.PLACA, FC.SIGLA_FILIAL || ' - ' || C.NUMERO numero, VARCHAR_FORMAT(C.DATAEMISSAO, 'DD/MM/YYYY') emissao,
                        FB.SIGLA_FILIAL || ' - ' || B.NUMBIPE bipe, C.VALTOTFRETE frete, C.VALPEDSICMS pedagio, H.RAZAO_SOCIAL cliente
                    FROM CT C
                    JOIN FILIAL  FC ON C.ID_FILIAL = FC.ID_FILIAL
                    LEFT JOIN CADBIPE B  ON C.IDCADBIPE = B.IDCADBIPE
                    LEFT JOIN FILIAL  FB ON B.ID_FILIAL = FB.ID_FILIAL
                    JOIN HCLIENTE H ON C.IDHCLIENTE = H.IDHCLIENTE
                    JOIN HVEICULO V ON C.ID_HVEICULO = V.ID_HVEICULO";

            return $this->select($sql, $params, "C.DATAEMISSAO");
        }

        /**
         * Valor de receita prevista por mês
         *
         * @param int $ano Ano desejado
         * @param int $idCtCusto ID do centro de custo para filtragem dos orçamentos
         * @return mixed Result Set com os valores de receita prevista para cada mês
         */
        public function receitaPrevisto($ano, $idCtCusto = null){
            $sql = "SELECT O.MES mes, SUM(O.VAL_PREV) valor FROM ORCAREC O";

            $params = array( $this->whereParam("O.ANO", $ano) );

            if ($idCtCusto) array_push($params, $this->whereParam("O.IDCTCUSTO", $idCtCusto));

            return $this->select($sql, $params, "O.MES", "O.MES");
        }

        /**
         * Receita por placa e tipo de contrato
         *
         * @author Paulo Silva
         * @date 27/10/2015
         * @param int $ano Ano para filtragem dos documentos
         * @param int $mes Mês para filtragem dos documentos
         * @param string $staft Tipo de contrato (A - Agregado, F - Frota ou T - Terceiro)
         * @param bool $vendaImob Indica se devem-se incluir vendas de imobilizados no cálculo (NOTAFAT)
         * @return mixed Result Set contendo os valores de receita (CT, CARRETO, NOTASER e NOTAFAT) para o período e contrato indicados, agrupando por placa
         */
        public function receitaPorPlacas($ano, $mes, $staft, $vendaImob = false){
            /** Inclui SELECT na NOTAFAT caso necessite calcular as vendas de imobilizado */
            $unionNotaFat =
                $vendaImob ? "
                    UNION ALL

                    SELECT
                        V.ID_VEICULO idVeiculo, V.PLACA placa, SUM(DOC.VLR_TOTAL) frete, SUM(DOC.VLR_TOTAL) fpeso
                    FROM NOTAFAT DOC
                    JOIN HVEICULO V ON DOC.ID_HVEICULO = V.ID_HVEICULO
                    JOIN HVEICEMP E ON (V.ID_HVEICULO = E.ID_HVEICULO AND E.ID_EMPRESA = 1)
                    WHERE
                        DOC.STATUS <> 'C' AND YEAR(DOC.DATA_EMIS) = $ano AND MONTH(DOC.DATA_EMIS) = $mes AND E.STAFT = '$staft'
                        AND DOC.CODIGO_CFOP IN ('5.551', '6.551', '5.102', '6.102')
                    GROUP BY V.ID_VEICULO, V.PLACA"
                : "";

            $sql = "SELECT idVeiculo, placa, SUM(frete) valFrete, SUM(fpeso) valFPeso FROM (
                        SELECT
                            V.ID_VEICULO idVeiculo, V.PLACA placa, SUM(DOC.VALTOTFRETE) frete, SUM(DOC.VALFPESOSICMS) fpeso
                        FROM CT DOC
                        JOIN HVEICULO V ON DOC.ID_HVEICULO = V.ID_HVEICULO
                        JOIN HVEICEMP E ON DOC.IDHVEICEMP  = E.IDHVEICEMP
                        WHERE
                            DOC.STATUSCT <> 'C' AND YEAR(DOC.DATAEMISSAO) = $ano AND MONTH(DOC.DATAEMISSAO) = $mes AND E.STAFT = '$staft'
                        GROUP BY V.ID_VEICULO, V.PLACA

                        UNION ALL

                        SELECT
                            V.ID_VEICULO idVeiculo, V.PLACA placa, SUM(DOC.VALFRETE) frete, SUM(DOC.VALFRETE) fpeso
                        FROM CARRETO DOC
                        JOIN HVEICULO V ON DOC.ID_HVEICULO = V.ID_HVEICULO
                        JOIN HVEICEMP E ON DOC.IDHVEICEMP  = E.IDHVEICEMP
                        WHERE
                            DOC.STATUS <> 'C' AND YEAR(DOC.DATASAIDA) = $ano AND MONTH(DOC.DATASAIDA) = $mes AND E.STAFT = '$staft'
                        GROUP BY V.ID_VEICULO, V.PLACA

                        UNION ALL

                        SELECT
                            V.ID_VEICULO idVeiculo, V.PLACA placa, SUM(DOC.VALTOTSERV) frete, SUM(DOC.VALTOTSERV) fpeso
                        FROM NOTASER DOC
                        JOIN HVEICULO V ON DOC.ID_HVEICULO = V.ID_HVEICULO
                        JOIN HVEICEMP E ON DOC.IDHVEICEMP  = E.IDHVEICEMP
                        WHERE
                            DOC.STATUS <> 'C' AND YEAR(DOC.DATAEMIS) = $ano AND MONTH(DOC.DATAEMIS) = $mes AND E.STAFT = '$staft'
                        GROUP BY V.ID_VEICULO, V.PLACA

                        $unionNotaFat
                    )";

            return $this->select($sql, null, "valFPeso DESC", "idVeiculo, placa");
        }

        /**
         * Valor do faturamento para um período qualquer
         *
         * @author Paulo Silva
         * @param date $dtIni Data inicial para cálculo no formato Y-m-d
         * @param date $dtFin Data final para cálculo no formato Y-m-d
         * @param bool $vendaImob Indica se devem-se incluir vendas de imobilizados no cálculo (NOTAFAT)
         * @return mixed Result Set com o valor faturado no período
         */
        public function faturamentoPeriodo($dtIni, $dtFin, $vendaImob = false){
            /** Inclui SELECT na NOTAFAT caso necessite calcular as vendas de imobilizado */
            $unionNotaFat =
                $vendaImob ? "
                    UNION ALL

                    SELECT SUM(DOC.VLR_TOTAL) AS VALOR FROM NOTAFAT DOC
                    WHERE DOC.STATUS <> 'C' AND DOC.DATA_EMIS BETWEEN '$dtIni' AND '$dtFin'
                    AND DOC.CODIGO_CFOP IN ('5.551', '6.551', '5.102', '6.102')"
                : "";

            $sql = "SELECT SUM(VALOR) result FROM (
                        SELECT SUM(DOC.VALTOTFRETE) VALOR FROM CT DOC
                        WHERE DOC.STATUSCT <> 'C' AND DOC.DATAEMISSAO BETWEEN '$dtIni' AND '$dtFin'

                        UNION ALL

                        SELECT SUM(DOC.VALFRETE) VALOR FROM CARRETO DOC
                        WHERE DOC.STATUS <> 'C' AND DOC.DATASAIDA BETWEEN '$dtIni' AND '$dtFin'

                        UNION ALL

                        SELECT SUM(DOC.VALTOTSERV) VALOR FROM NOTASER DOC
                        WHERE DOC.STATUS <> 'C' AND DOC.DATAEMIS BETWEEN '$dtIni' AND '$dtFin'

                        UNION ALL

                        SELECT SUM(DOC.VALOR) VALOR FROM NOTADEB DOC
                        WHERE DOC.STATUS <> 'C' AND DOC.DATAEMISSAO BETWEEN '$dtIni' AND '$dtFin'

                        $unionNotaFat
                    )";

            $this->select($sql);

            return $this->getResultCell();
        }

        /**
         * Valor do faturamento anual dividido pelos 12 meses
         *
         * @author Paulo Silva
         * @param int $ano Ano desejado
         * @param bool $vendaImob Indica se devem-se incluir vendas de imobilizados no cálculo (NOTAFAT)
         * @param int $idCtCusto ID do centro de custo para filtragem dos documentos
         * @param string $cgc CPF / CNPJ de clientes para filtragem
         * @return mixed Result Set com os valores de cada mês
         */
        public function faturamentoAnual($ano, $vendaImob = false, $idCtCusto = null, $cgc = null){
            $filtro .= $idCtCusto ? " AND F.IDCTCUSTO = $idCtCusto " : "";
            $filtro .= $cgc       ? " AND C.CNPJ_CPF LIKE '$cgc%' "  : "";

            /** Inclui SELECT na NOTAFAT caso necessite calcular as vendas de imobilizado */
            $unionNotaFat =
                $vendaImob ? "
                    UNION ALL

                    SELECT MONTH(DOC.DATA_EMIS) MES, SUM(DOC.VLR_TOTAL) AS VALOR
                    FROM NOTAFAT DOC
                    JOIN FILIAL  F ON F.ID_FILIAL = DOC.ID_FILIAL
                    JOIN CLIENTE C ON C.ID_CLIENTE = DOC.ID_CLIENTE
                    WHERE DOC.STATUS <> 'C' AND YEAR(DOC.DATA_EMIS) = $ano AND DOC.CODIGO_CFOP IN ('5.551', '6.551', '5.102', '6.102') $filtro
                    GROUP BY MONTH(DOC.DATA_EMIS)"
                : "";

            $sql = "SELECT MES, SUM(VALOR) VALOR FROM (
                        SELECT MONTH(DOC.DATAEMISSAO) MES, SUM(DOC.VALTOTFRETE) VALOR FROM CT DOC
                        JOIN FILIAL   F ON F.ID_FILIAL = DOC.ID_FILIAL
                        JOIN HCLIENTE C ON C.IDHCLIENTE = DOC.IDHCLIENTE
                        WHERE DOC.STATUSCT <> 'C' AND YEAR(DOC.DATAEMISSAO) = $ano
                        $filtro
                        GROUP BY MONTH(DOC.DATAEMISSAO)

                        UNION ALL

                        SELECT MONTH(DOC.DATASAIDA) MES, SUM(DOC.VALFRETE) VALOR FROM CARRETO DOC
                        JOIN FILIAL  F ON F.ID_FILIAL = DOC.ID_FILIAL
                        JOIN CLIENTE C ON C.ID_CLIENTE = DOC.ID_CLIENTE
                        WHERE DOC.STATUS <> 'C' AND YEAR(DOC.DATASAIDA) = $ano
                        $filtro
                        GROUP BY MONTH(DOC.DATASAIDA)

                        UNION ALL

                        SELECT MONTH(DOC.DATAEMIS) MES, SUM(DOC.VALTOTSERV) VALOR FROM NOTASER DOC
                        JOIN FILIAL   F ON F.ID_FILIAL = DOC.ID_FILIAL
                        JOIN HCLIENTE C ON C.IDHCLIENTE = DOC.IDHCLIENTE
                        WHERE DOC.STATUS <> 'C' AND YEAR(DOC.DATAEMIS) = $ano
                        $filtro
                        GROUP BY MONTH(DOC.DATAEMIS)

                        UNION ALL

                        SELECT MONTH(DOC.DATAEMISSAO) MES, SUM(DOC.VALOR) VALOR FROM NOTADEB DOC
                        JOIN FILIAL   F ON F.ID_FILIAL = DOC.ID_FILIAL
                        JOIN HCLIENTE C ON C.IDHCLIENTE = DOC.IDHCLIENTE
                        WHERE DOC.STATUS <> 'C' AND YEAR(DOC.DATAEMISSAO) = $ano
                        $filtro
                        GROUP BY MONTH(DOC.DATAEMISSAO)

                        $unionNotaFat
                    )";

            return $this->select($sql, null, null, "MES");
        }

        /**
         * Valor do faturamento para um período qualquer filtrando por tipo de contrato
         *
         * @author Paulo Silva
         * @date 04/07/2016
         * @param char $staft Tipo de contrato (KeyDictionary::tipoContrato)
         * @param date $dtIni Data inicial para cálculo no formato Y-m-d
         * @param date $dtFin Data final para cálculo no formato Y-m-d
         * @param bool $vendaImob Indica se devem-se incluir vendas de imobilizados no cálculo (NOTAFAT)
         * @return mixed Result Set com o valor faturado no período
         */
        public function faturamentoContrato($staft, $dtIni, $dtFin, $vendaImob = false){
            /** Inclui SELECT na NOTAFAT caso necessite calcular as vendas de imobilizado */
            $unionNotaFat =
                $vendaImob ? "
                    UNION ALL

                    SELECT SUM(DOC.VLR_TOTAL) AS VALOR FROM NOTAFAT DOC
                    JOIN HVEICEMP V ON (V.IDHVEICEMP = DOC.ID_HVEICULO)
                    WHERE DOC.STATUS <> 'C' AND DOC.DATA_EMIS BETWEEN '$dtIni' AND '$dtFin' AND V.STAFT = '$staft'
                    AND DOC.CODIGO_CFOP IN ('5.551', '6.551', '5.102', '6.102')"
                : "";

            $sql = "SELECT SUM(VALOR) result FROM (
                        SELECT SUM(DOC.VALTOTFRETE) VALOR FROM CT DOC
                        JOIN HVEICEMP V ON (V.IDHVEICEMP = DOC.IDHVEICEMP)
                        WHERE DOC.STATUSCT <> 'C' AND DOC.DATAEMISSAO BETWEEN '$dtIni' AND '$dtFin' AND V.STAFT = '$staft'

                        UNION ALL

                        SELECT SUM(DOC.VALFRETE) VALOR FROM CARRETO DOC
                        JOIN HVEICEMP V ON (V.IDHVEICEMP = DOC.IDHVEICEMP)
                        WHERE DOC.STATUS <> 'C' AND DOC.DATASAIDA BETWEEN '$dtIni' AND '$dtFin' AND V.STAFT = '$staft'

                        UNION ALL

                        SELECT SUM(DOC.VALTOTSERV) VALOR FROM NOTASER DOC
                        JOIN HVEICEMP V ON (V.IDHVEICEMP = DOC.IDHVEICEMP)
                        WHERE DOC.STATUS <> 'C' AND DOC.DATAEMIS BETWEEN '$dtIni' AND '$dtFin' AND V.STAFT = '$staft'

                        $unionNotaFat
                    )";

            $this->select($sql);

            return $this->getResultCell();
        }

        /**
         * Último BIPE lançado para o veículo desejado
         *
         * @author Paulo Silva
         * @date 23/10/2015
         * @param string $placa Placa do veículo
         * @return mixed Array com as informações do BIPE encontrado
         */
        public function ultimoBipeVeiculo($placa){
            $sql = "SELECT
                        B.IDCADBIPE id, (F.SIGLA_FILIAL || ' - ' || B.NUMBIPE) numero, B.DATAEMIS emissao,
                        (O.UF || ' - ' || O.NOME_CIDADE) origem, (D.UF || ' - ' || D.NOME_CIDADE) destino,
                        DECODE(B.STAFT, 'F', 'Frota', 'A', 'Agregado', 'Terceiro') contrato
                    FROM CADBIPE B
                    JOIN HVEICULO V ON B.ID_HVEICULO = V.ID_HVEICULO
                    JOIN FILIAL   F ON B.ID_FILIAL = F.ID_FILIAL
                    JOIN ROTA     R ON B.ID_ROTA = R.ID_ROTA
                    JOIN CIDADE   O ON R.ID_CIDADEORIG = O.ID_CIDADE
                    JOIN CIDADE   D ON R.ID_CIDADEDEST = D.ID_CIDADE";

            $params = array( $this->whereParam("V.PLACA", $placa) );

            return $this->getResultRow(0, $this->select($sql, $params, "B.IDCADBIPE DESC FETCH FIRST 1 ROW ONLY"));
        }

        /**
         * Lista de CT-es vinculados a um BIPE
         *
         * @author Paulo Silva
         * @date 23/10/2015
         * @param int $idBipe ID do BIPE
         * @return mixed Result Set com as informações de cada CT-e encontrado
         */
        public function ctesPorBipe($idBipe){
            $sql = "SELECT
                        CT.ID_CT id, (F.SIGLA_FILIAL || ' - ' || CT.NUMERO) numero, CT.DATAEMISSAO emissao, C.RAZAO_SOCIAL cliente,
                        (D.UF || ' - ' || D.NOME_CIDADE) cidade, CT.DATAAGENDA dtAgnd, CT.HORAAGENDA hrAgnd, CT.MINAGENDA mnAgnd
                    FROM CT CT
                    JOIN FILIAL   F ON CT.ID_FILIAL = F.ID_FILIAL
                    JOIN HCLIENTE C ON CT.IDHCLIENTEDEST = C.IDHCLIENTE
                    JOIN CIDADE   D ON C.ID_CIDADE = D.ID_CIDADE";

            $params = array( $this->whereParam("CT.IDCADBIPE", $idBipe) );

            return $this->select($sql, $params);
        }

        /**
         * Notas fiscais de transporte vinculadas a um CT-e
         *
         * @author Paulo Silva
         * @date 23/10/2015
         * @param int $idCte ID do conhecimento
         * @return mixed Result Set da consulta contendo o número e série de cada nota
         */
        public function notasCte($idCte){
            return $this->select(
                "SELECT NRNF numero, SERIE serie FROM NFTRANSP", array($this->whereParam("ID_CT", $idCte)), "NRNF");
        }

        /**
         * Ordens de serviço em aberto
         *
         * @author Paulo Silva
         * @date 23/10/2015
         * @param string $placa Placa de um veículo específico para filtragem dos resultados
         * @return mixed Result Set com as informações da ordem de serviço
         */
        public function osAberto($placa = null){
            $sql = "SELECT
                        NUMORDEM numero, OBSERVACAO obs,
                        (O.DATA_ABRE || ' ' || O.HORA_ABRE) abertura, (O.DATA_PREVISAO || ' ' || O.HORA_PREVISAO) previsao,
                        DECODE(O.STATUS, 'A', 'Aberta', 'Liberada') status, DECODE(O.TIPMANUT, 'P', 'Preventiva', 'C', 'Corretiva', O.TIPMANUT) tipo
                    FROM ORDEMSER O
                    JOIN HVEICULO V ON V.ID_HVEICULO = O.ID_HVEICULO
                    WHERE O.STATUS IN ('A','L')";

            if ($placa) $sql .= " AND V.PLACA = '$placa'";

            return $this->select($sql, null, "O.ID_ORDEMSER");
        }

        /**
         * Informações de abastecimentos mensais para um determinado veículo
         *
         * @author Paulo Silva
         * @date 27/10/2015
         * @param int $idVeiculo ID do veículo
         * @param int $ano Ano para filtragem dos abastecimentos
         * @param int $mes Mês para filtragem dos abastecimentos
         * @return array Valor do Km rodado, litros abastecidos e média para o veículo
         */
        public function infoAbastecimentos($idVeiculo, $ano, $mes){
            $sql = "SELECT SUM(A.ODOMETRO - A.ODOANTER) kmRodado, SUM(A.LITROSABAST) litros, SUM(A.VALORABAST) valor FROM ABAST A";

            $params = array (
                $this->whereParam("YEAR(A.DATAABAST)", $ano),
                $this->whereParam("MONTH(A.DATAABAST)", $mes),
                $this->whereParam("A.ID_VEICULO", $idVeiculo)
            );

            $result = $this->getResultRow(0, $this->select($sql, $params));

            $result['MEDIA'] = $result['KMRODADO'] / $result['LITROS'];

            return $result;
        }

        /**
         * KM rodado pelo veículo em viagens vazias
         *
         * Buca nos BIPEs de viagem vazia a quilometragem de cada rota, totalizando o valor retornado por esta função. Pode ser adaptada para buscar não apenas
         * das viagens vazias.
         *
         * @author Paulo Silva
         * @date 28/10/2015
         * @param int $idVeiculo ID do veículo desejado
         * @param int $ano Ano para apuração
         * @param int $mes Mês para apuração
         * @return int Quilometragem total rodada enquanto em BIPEs de viagem vazia
         */
        public function kmVazio($idVeiculo, $ano, $mes){
            $sql = "SELECT SUM(R.U_DISTANCIA) result
                    FROM CADBIPE C
                    JOIN ROTA R ON C.ID_ROTA = R.ID_ROTA
                    JOIN HVEICULO V ON C.ID_HVEICULO = V.ID_HVEICULO";

            $params = array(
                $this->whereParam("C.TIPODOCTO", "Z"),
                $this->whereParam("V.ID_VEICULO", $idVeiculo),
                $this->whereParam("YEAR(C.DATAEMIS)", $ano),
                $this->whereParam("MONTH(C.DATAEMIS)", $mes)
            );

            $this->select($sql, $params);

            return $this->getResultCell();
        }

        /**
         * Odômetro atual do veículo com base no último abastecimento
         *
         * @author Paulo Silva
         * @date 25/11/2015
         * @param string $placa Placa do veículo
         * @return mixed Array com valor do odômetro atual e anterior registrados no abastecimento
         */
        public function kmAtual($placa){
            $sql = "SELECT A.ODOMETRO atual, A.ODOANTER anterior FROM ABAST A
                    JOIN VEICULO V ON A.ID_VEICULO = V.ID_VEICULO";

            $params = array( $this->whereParam("V.PLACA", $placa) );

            $this->select($sql, $params, "A.IDABAST DESC FETCH FIRST 1 ROWS ONLY");

            return $this->getResultRow();
        }

        /**
         * Quantidade total de ordens de embarque filtradas por período e status
         *
         * @author Paulo Silva
         * @date 15/01/2016
         * @param date $dtIni Data de busca inicial. Opcional.
         * @param date $dtFin Data de busca final. Opcional.
         * @param char $status Status da ordem no GetOne Enterprise (R, A, F, P, C)
         * @return int Total de ordens encontradas para os filtros indicados
         */
        public function qtdCargas($dtIni = null, $dtFin = null, $status = null){
            $params = array();

            if ($dtIni) array_push($params, $this->whereParam("O.DATADIG", $dtIni, ">="));
            if ($dtFin) array_push($params, $this->whereParam("O.DATADIG", $dtFin, "<="));
            if ($status) array_push($params, $this->whereParam("O.STATUS", $status));

            return $this->simpleSelect("ORDEMEMB O", "COUNT(O.ID_ORDEMEMB)", $params);
        }

        /**
         * Margens de adiantamento praticadas na empresa, agrupadas por filial
         *
         * @author Paulo Silva
         * @date 10/06/2016
         * @param int $ano Ano desejado para pesquisa
         * @param int $mes Mês desejado para busca. Parâmetro opcional
         * @return mixed Result Set com a consulta das filiais e seus respectivos valores e percentuais
         */
        public function margensAdto($ano, $mes = null){
            $colFilial = "(TRIM(F.SIGLA_FILIAL) || ' - ' || F.NOME_FILIAL)";

            $sql =
                "SELECT
                    $colFilial filial, F.PERCADTOTER prcFilial, AVG(C.VALFRETEPAGOTOT) valFrete, AVG(P.VALOR) valAdto,
                    AVG(((P.VALOR - C.VALCREDVE) / C.VALFRETEPAGOTOT) * 100) prcAdto
                FROM CADBIPE C
                JOIN FILIAL   F ON C.ID_FILIAL = F.ID_FILIAL
                JOIN PROGVIAG P ON (C.IDCADBIPE = P.IDCADBIPE AND P.SEQ = 1 AND P.OPERACAO = 'Adiantamento')";

            $params = array($this->whereParam("YEAR(C.DATAEMIS)", $ano));

            if ($mes) array_push($params, $this->whereParam("MONTH(C.DATAEMIS)", $mes));

            return $this->select($sql, $params, $colFilial, ($colFilial . ", F.PERCADTOTER"));
        }
    }
?>