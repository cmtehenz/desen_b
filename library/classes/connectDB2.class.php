<?php
    /**
     * Arquivo com classe utilizada para conexão PDO com bancos IBM DB2
     *
     * @author Paulo Silva
     * @date 29/07/2015
     * @version 1.13
     * @package Library/Classes
     * @subpackage DB2
     */

    namespace Library\Classes;

    /**
     * Classe de conexão PDO com bancos IBM DB2
     *
     * @author Paulo Silva
     * @date 29/07/2015
     * @version 1.13
     * @package Library/Classes
     * @subpackage Connection Classes
     */
    class connectDB2 extends connectDatabase {
        /**
         * Construtor responsável por setar as configurações de conexão do objeto criado
         */
        public function __construct(){ $this->setConfig(); $this->setDSN("ibm:DRIVER={IBM DB2 ODBC DRIVER};DATABASE=$this->_db;HOSTNAME=$this->_host;PORT=50000;CurrentSchema=DB2;"); }

        /**
         * Função extendida da classe mãe que chama a mesma passando como parâmetro as variáveis de sessão contendo os dados de conexão ao SQL Server
         *
         * @access protected
         */
        protected function setConfig(){
            parent::setConfig($_SESSION['dbERPHost'], $_SESSION['dbERPUser'], $_SESSION['dbERPPswd'], $_SESSION['dbERPName']);
        }

        /**
         * Executa uma consulta no banco de dados
         *
         * Função modificada pois o DB2 quando não encontra nada retorna uma string "null", e isto estava atrpalhando a manipulação de resultados DB2 no sistema
         *
         * @author Paulo Silva
         * @date 08/10/2015
         * @version 1.0
         * @param string $sql Instrução a ser executada
         * @param mixed $params Os parâmetros que serão inseridos no WHERE da consulta (o array ser montado com a função whereParam). Default: NULL = Sem filtro
         * @param string $orderBy Indica o conteúdo da cláusula ORDER BY
         * @param string $groupBy Indica o conteúdo da cláusula GROUP BY
         * @param bool $addWhere Indica se a função deve incluir a cláusula WHERE desde o início ou se ela já está presente na query e apenas os parâmetros devem ser adicionados
         * @return PDOException|mixed Exceção caso ocorra ou um array PDO::FETCH_ASSOC contendo as linhas e colunas da consulta em caso de sucesso
         */
        public function select($sql, $params = null, $orderBy = null, $groupBy = null, $addWhere = true){
            $result = parent::select($sql, $params, $orderBy, $groupBy, $addWhere);

            return is_array($result) ? $result : null;
        }

        /**
         * Executa e retorna um SELECT simples no banco, como "SELECT 1 FROM table" para determinar se certa condição é atendida
         *
         * Função modificada para trazer uma única célula do DB2 sem usar o "TOP 1" do MSSQL, que gerava erro
         *
         * @author Paulo Silva
         * @date 14/01/2015
         * @version 1.0
         * @param string $table O nome da tabela onde deve ser executada a consulta
         * @param string $column O nome da coluna que será consultada. Por default, adotará "1" para usar em SELECTs de caráter booleano
         * @param array $params Os parâmetros que serão inseridos no WHERE da consulta
         * @param string $orderBy Indica o conteúdo da cláusula ORDER BY
         * @return mixed O valor da consulta realizada (apenas primeira célula da primeira linha - que deve ser a única por tratar-se de uma consulta simples)
         */
        public function simpleSelect($table, $column = null, $params = null, $orderBy = null){
            $column = $column ?: "1";

            $sql = "SELECT $column result FROM $table";

            $this->select($sql, $params, $orderBy);

            return $this->getResultCell();
        }
    }
?>