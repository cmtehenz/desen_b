<?php
    /**
     * Package de funções referentes ao MSSQL, como consultas de informações para o florestal
     *
     * @author Paulo Silva
     * @date 03/08/2015
     * @version 1.30
     * @package Library/Scripts
     */

    namespace Library\Scripts;

    /**
     * Package de funções referentes ao MSSQL, como consultas de informações para o florestal
     *
     * @author Paulo Silva
     * @date 03/08/2015
     * @version 1.30
     * @package Library/Scripts
     * @subpackage SQL
     */
    final class scriptSQL extends \Library\Classes\connectMSSQL {
        /**
         * Construtor responsável por setar as configurações de conexão do objeto criado e conectar automaticamente para uso facilitado da classe
         */
        public function __construct(){ parent::__construct(); $this->connect(); }

        /**
         * Método destrutor responsável por realizar a desconexão com o banco automaticamente
         */
        public function __destruct(){ $this->disconnect(); }

        /**
         * Resumo dos valores de pedágio do Sem Parar x GetOne agrupados por placa
         *
         * @author Paulo Silva
         * @date 21/09/2015
         * @param date $dtIni Data (emissão) inicial do período de busca
         * @param date $dtFin Data (emissão) final do período de busca
         * @param date $placa Placa para filtragem opcional de apenas um veículo
         * @return mixed Result Set com a tabela para exibição dos resultados
         */
        public function resumoSemParar($dtIni, $dtFin, $placa = null){
            if ($placa) $filtro = " AND v.placa = '$placa' ";

            $sql = "SELECT placa, SUM(qtdPsg) qtdPsg, SUM(vlrPsg) vlrPsg, SUM(qtdCrd) qtdCrd, SUM(vlrCrd) vlrCrd
                    FROM (
                        SELECT v.placa, COUNT(p.idPassagem) qtdPsg, SUM(p.valor) vlrPsg, 0 qtdCrd, 0 vlrCrd
                        FROM sp.passagens p
                        JOIN sp.veiculo v ON v.idVeiculo = p.idVeiculo
                        WHERE p.data BETWEEN '$dtIni' AND '$dtFin' $filtro
                        GROUP BY v.placa, v.idVeiculo

                        UNION ALL

                        SELECT v.placa, 0 qtdPsg, 0 vlrPsg, COUNT(c.idCredito) qtdCrd, SUM(c.valor) vlrCrd
                        FROM sp.credito c
                        JOIN sp.veiculo v ON v.idVeiculo = c.idVeiculo
                        WHERE c.data BETWEEN '$dtIni' AND '$dtFin' $filtro
                        GROUP BY v.placa, v.idVeiculo
                    ) AS resumo";

            $result = $placa ?
                $this->selectTopOne($sql, null, "vlrPsg DESC, vlrCrd DESC", "resumo.placa") :
                $this->select      ($sql, null, "vlrPsg DESC, vlrCrd DESC", "resumo.placa");

            return $result;
        }

        /**
         * Informações do cabeçalho de faturas Sem Parar cadastradas na base
         *
         * @author Paulo Silva
         * @date 28/09/2015
         * @param int $numFatura Número da fatura para filtragem opcional de apenas uma
         * @param int $ano Ano de vencimento das faturas
         * @return mixed Result Set com as informações da(s) fatura(s) encontrada(s)
         */
        public function faturas($numFatura = null, $ano = null){
            $params = array();

            if ($numFatura) array_push($params, $this->whereParam("f.numero", $numFatura));
            if ($ano)       array_push($params, $this->whereParam("YEAR(f.dtVencimento)", $ano));

            $sql = "SELECT
                        f.idFatura, f.numero, dbo.DateFormat103(f.dtEmissao) emissao, dbo.DateFormat103(f.dtVencimento) vencimento, f.valorTotal
                    FROM sp.fatura f";

            return ($numFatura ? $this->selectTopOne($sql, $params) : $this->select($sql, $params, "f.dtVencimento"));
        }

        /**
         * Lista de passagens no Sem Parar
         *
         * @param mixed $params Array com filtros para a busca
         * @return mixed Result Set contendo os dados de cada passagem encontrada
         */
        public function passagensSemParar($params){
            $sql = "SELECT
                        c.codigo + ' - ' + c.nome concessionaria, p.codigo + ' - ' + p.nome praca, dbo.DateTimeFormat103(g.data) data, g.valor, v.placa, g.tag
                    FROM sp.passagens g
                    JOIN sp.concessionaria c ON g.codConcessionaria = c.codigo
                    JOIN sp.pracped        p ON c.codigo = p.codConcessionaria AND g.codPracPed = p.codigo
                    JOIN sp.veiculo        v ON g.idVeiculo = v.idVeiculo";

            return $this->select($sql, $params, "v.placa, g.data");
        }

        /**
         * Lista de créditos / reembolsos no Sem Parar
         *
         * @param mixed $params Array com filtros para a busca
         * @return mixed Result Set contendo os dados de cada crédito encontrado
         */
        public function creditosSemParar($params){
            $sql = "SELECT
                        v.placa, c.valor, dbo.DateFormat103(c.data) dtCredito, CONVERT(CHAR(16), dbo.DateTimeFormat103(c.dataImportacao)) dtImportacao, c.tag
                    FROM sp.credito c
                    JOIN sp.veiculo v ON c.idVeiculo = v.idVeiculo";

            return $this->select($sql, $params, "v.placa, c.data");
        }

        /**
         * Valores de orçamento por mês cadastrados no BID, possibilitando filtrar por cliente (CGC truncado ou não)
         *
         * @author Paulo Silva
         * @date 20/10/2015
         * @param int $ano Ano dos orçamentos realizados
         * @param bool $trunco Indica se os valores devem ser agrupados por trunco de CGC (padrão: false)
         * @param string $cgc CGC para filtragem de valores de apenas um cliente ou trunco
         * @return mixed Result Set contendo os valores encontrados para cada conjunto de CGC / mês
         */
        public function orcamentoCliente($ano, $trunco = false, $cgc = null){
            $colCgc = $trunco ? "CONVERT(CHAR(8), cgc)" : "cgc";

            $params = array( $this->whereParam("ano", $ano) );

            if ($cgc) array_push($params, $this->whereParam($colCgc, $cgc));

            $groupBy = ($cgc ? "$colCgc," : "") . " ano, mes";

            return $this->select("SELECT mes, SUM(valor) valor FROM orccli", $params, "ano, mes", $groupBy);
        }

        /**
         * Meta cadastrada para o conjunto
         *
         * @author Paulo Silva
         * @date 27/10/2015
         * @param string $conjunto Descrição do conjunto
         * @return decimal Valor de meta cadastrado para o conjunto no BID
         */
        public function metaConjunto($conjunto){
            $params = array( $this->whereParam("descricao", $conjunto) );

            return $this->simpleSelect("meta", "valor", $params);
        }

        /**
         * Análise mês a mês do Florestal (viagens, peso carregado, médias e faturamento), permitindo filtrar por cliente
         *
         * @author Paulo Silva
         * @date 13/04/2016
         * @param int $ano Ano desejado
         * @param string $idCliente ID do cliente / fazenda para filtragem
         * @param array $params Parâmetros adicionais para a busca dos dados
         * @return mixed Result Set com os valores de cada mês
         */
        public function analiseAnual($ano, $idCliente = null, $params = null){
            $params = $params ?: array();

            array_push($params, $this->whereParam("YEAR(c.data)", $ano));

            if ($idCliente) array_push($params, $this->whereParam("l.idCliente", $idCliente));

            $sql = "SELECT
                        MONTH(c.data) mes, COUNT(c.idCarregamento) viagens, SUM(c.peso) peso, SUM(c.valor * (c.peso / 1000)) faturamento,
                        SUM(CASE WHEN (DAY(c.data) <= 15) THEN (c.valor * (c.peso / 1000)) ELSE 0 END) quinzena1,
                        SUM(CASE WHEN (DAY(c.data)  > 15) THEN (c.valor * (c.peso / 1000)) ELSE 0 END) quinzena2
                    FROM flr.carregamento c
                    JOIN flr.cliente l ON c.idCliente = l.idCliente";

            $result = $this->select($sql, $params, "MONTH(c.data)", "MONTH(c.data)");

            return $result;
        }

        /**
         * Análise de todos os dias de um mês do Florestal (viagens, peso carregado, médias e faturamento)
         *
         * @author Paulo Silva
         * @date 24/10/2016
         * @param int $ano Ano desejado
         * @param int $mes Mês desejado
         * @param array $params Parâmetros adicionais para a busca dos dados
         * @return mixed Result Set com os valores de cada dia
         */
        public function analiseMensal($ano, $mes, $params = null){
            $params = $params ?: array();

            array_push($params, $this->whereParam("YEAR (c.data)", $ano));
            array_push($params, $this->whereParam("MONTH(c.data)", $mes));

            $sql = "SELECT
                        DAY(c.data) dia, COUNT(c.idCarregamento) viagens, SUM(c.peso) peso, SUM(c.valor * (c.peso / 1000)) faturamento
                    FROM flr.carregamento c";

            $result = $this->select($sql, $params, "DAY(c.data)", "DAY(c.data)");

            return $result;
        }

        /**
         * Análise por cliente do Florestal (viagens, peso carregado, médias e faturamento)
         *
         * @author Paulo Silva
         * @date 19/05/2016
         * @param int $ano Ano desejado
         * @param array $params Parâmetros adicionais para a busca dos dados
         * @return mixed Result Set com os valores de cada cliente
         */
        public function analiseClientes($ano, $params = null){
            $params = $params ?: array();

            array_push($params, $this->whereParam("YEAR(c.data)", $ano));

            $sql = "SELECT
                        l.descricao nome, COUNT(c.idCarregamento) viagens, SUM(c.peso) peso, SUM(c.valor * (c.peso / 1000)) faturamento,
                        SUM(CASE WHEN (DAY(c.data) <= 15) THEN (c.valor * (c.peso / 1000)) ELSE 0 END) quinzena1,
                        SUM(CASE WHEN (DAY(c.data)  > 15) THEN (c.valor * (c.peso / 1000)) ELSE 0 END) quinzena2
                    FROM flr.carregamento c
                    JOIN flr.cliente l ON c.idCliente = l.idCliente";

            $result = $this->select($sql, $params, "SUM(c.valor * (c.peso / 1000)) DESC", "l.descricao");

            return $result;
        }

        /**
         * Análise por fazendas do Florestal (viagens e peso carregado)
         *
         * @author Paulo Silva
         * @date 19/05/2016
         * @param int $ano Ano desejado
         * @param array $params Parâmetros adicionais para a busca dos dados
         * @return mixed Result Set com os valores de cada fazenda
         */
        public function analiseFazendas($ano, $params = null){
            $params = $params ?: array();

            array_push($params, $this->whereParam("YEAR(c.data)", $ano));

            $sql = "SELECT
                        f.descricao nome, COUNT(c.idCarregamento) viagens, SUM(c.peso) peso
                    FROM flr.carregamento c
                    JOIN flr.fazenda f ON c.idFazenda = f.idFazenda";

            $result = $this->select($sql, $params, "SUM(c.peso) DESC", "f.descricao");

            return $result;
        }

        /**
         * Análise por itens do Florestal (viagens, valor médio praticado e valor atual do item)
         *
         * @author Paulo Silva
         * @date 24/10/2016
         * @param int $ano Ano desejado
         * @param array $params Parâmetros adicionais para a busca dos dados
         * @return mixed Result Set com os valores de cada item
         */
        public function analiseItens($ano, $params = null){
            $params = $params ?: array();

            array_push($params, $this->whereParam("YEAR(c.data)", $ano));

            $sql = "SELECT
                        i.descricao nome, COUNT(c.idCarregamento) viagens, SUM(c.valor * (c.peso / 1000)) faturamento, AVG(c.valor) vlrMedio
                    FROM flr.carregamento c
                    JOIN flr.item i ON c.idItem = i.idItem";

            $result = $this->select($sql, $params, "AVG(c.valor) DESC", "i.descricao");

            return $result;
        }

                /**
         * Análise por veículos do Florestal (viagens, peso carregado, médias e faturamento)
         *
         * @author Paulo Silva
         * @date 25/10/2016
         * @param int $ano Ano desejado
         * @param array $params Parâmetros adicionais para a busca dos dados
         * @return mixed Result Set com os valores de cada placa
         */
        public function analiseVeiculos($ano, $params = null){
            $params = $params ?: array();

            array_push($params, $this->whereParam("YEAR(c.data)", $ano));

            $sql = "SELECT
                        c.placa idVeiculo, COUNT(c.idCarregamento) viagens, SUM(c.peso) peso, SUM(c.valor * (c.peso / 1000)) faturamento
                    FROM flr.carregamento c";

            $result = $this->select($sql, $params, "SUM(c.valor * (c.peso / 1000)) DESC", "c.placa");

            return $result;
        }
        
        /**
         * Retorna a lista de endereçoes de e-mail cujo serviço selecionado deve enviar suas notificações
         *
         * @author Paulo Silva
         * @date 27/04/2016
         * @param string $servico Nome do serviço (arquivo PHP) para busca dos endereços
         * @param string $tipoEnvio Tipo de envio do endereço de e-mail. Usar as constantes TP_ENVMAIL da KeyDictionary. Padrão = T = Destinatário
         * @return array Lista de endereços encontrados para a condição desejada
         */
        public function emailsServico($servico, $tipoEnvio = "T"){
            $params = array(
                $this->whereParam("m.servico", $servico),
                $this->whereParam("m.tipoEnvio", $tipoEnvio)
            );

            return $this->select("SELECT m.email, m.nome FROM srv.srvmail m", $params, "m.email");
        }
    }
?>