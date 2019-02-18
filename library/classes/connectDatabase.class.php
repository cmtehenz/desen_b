<?php
    /**
     * Arquivo com classe utilizada para personalizar as conexões PDO com banco de dados
     *
     * @author Paulo Silva
     * @date 28/07/2015
     * @version 1.53
     * @package Library/Classes
     */

    namespace Library\Classes;

    use PDO;

    /**
     * Classe abstrata base para as personalizações de conexão PDO com banco de dados
     *
     * @author Paulo Silva
     * @date 28/07/2015
     * @version 1.42
     * @package Library/Classes
     * @subpackage Connection Classes
     * @abstract
     */
    abstract class connectDatabase {
        /**
         * Dados de conexão com o banco
         * @var string Hostname / address do banco de dados
         */
        protected $_host;

        /**
         * Dados de conexão com o banco
         * @var string Usuário de acesso ao banco de dados
         */
        protected $_user;

        /**
         * Dados de conexão com o banco
         * @var string Senha de acesso ao banco de dados
         */
        protected $_passwd;

        /**
         * Dados de conexão com o banco
         * @var string Nome da base no banco de dados
         */
        protected $_db;

        /**
         * Resultado da última consulta executada
         * @var mixed|array Objeto PDO::FETCH_ASSOC que contém as linhas retornadas na última consulta executada pela classe
         */
        protected $_lastResult;

        /**
         * Instância do objeto PDO da classe
         * @var PDO Armazena a referência ao objeto de conexão PDO usado pela classe
         * @access private
         */
        private $_PDO;

        /**
         * Connection String utilizada ao criar o objeto PDO
         *
         * @var string DSN correspondente ao SGBD utilizado
         */
        private $_dsn;

        /**
         * Funções de criação e controle da classe
         */

        /**
         * Configura os parâmetros de conexão da classe com o banco
         *
         * @author Paulo Silva
         * @date 28/07/2015
         * @version 1.2
         * @access protected
         * @param type $host
         * @param type $user
         * @param type $passwd
         * @param type $db
         */
        protected function setConfig($host, $user, $passwd, $db){
            $this->_host   = $host;
            $this->_user   = $user;
            $this->_passwd = $passwd;
            $this->_db     = $db;
        }

        /**
         * Cria a instância de conexão PDO da classe baseando-se nos parâmetros de conexão configurados
         *
         * @throws PDOException Caso haja erro com as informações ou DSN usada na conexão
         */
        public function connect(){
            try {
                $pdo = new PDO($this->_dsn, $this->_user, $this->_passwd);

                $this->setPDO($pdo);
            }
            catch (PDOException $e) { echo($e->getMessage()); }
        }

        /**
         * Fecha a conexão PDO da classe, setando nulo a sua instância
         *
         * @author Paulo Silva
         * @date 28/07/2015
         * @version 1.1
         */
        public function disconnect(){ $this->_PDO = null; }

        /**
         * Seta a instância PDO da classe
         *
         * @access protected
         * @param PDO $pdo Referência ao objeto PDO de conexão ao banco
         */
        protected function setPDO($pdo){ $this->_PDO = $pdo; }

        /**
         * Retorna o objeto de conexão PDO da classe para uso
         *
         * @return PDO Instância do objeto de conexão
         */
        public function getPDO(){ return $this->_PDO; }

        /**
         * Configura a Connection String que será usada ao abrir a conexão PDO
         *
         * @param string $dsn Connection String correspondente ao SGBD utilizado
         */
        protected function setDSN($dsn){ $this->_dsn = $dsn; }

        /**
         * Funções de banco
         */

        /**
         * Executa uma instrução sem retorno (INSERT, UPDATE, DELETE, etc) no banco
         *
         * @author Paulo Silva
         * @date 31/07/2015
         * @version 1.68
         * @param string $sql Instrução a ser executada
         * @param array $params Os parâmetros que serão substituídos na query preparada pelo PDOStatement
         * @return PDOException Exceção caso ocorra ou nulo em caso de sucesso
         */
        public function execute($sql, $params = null){
            try {
                $pdo = $this->getPDO();

                $stmt = $pdo->prepare($sql);

                if ($params) foreach ($params as $key => $value) { $stmt->bindValue(":$key", $value); }

                $result = $stmt->execute();

                $errorInfo = $stmt->errorInfo();

                $stmt->closeCursor();

                if (!$result){
                    error_log(print_r($errorInfo, true));

                    return $errorInfo[2];
                }
            } catch (PDOException $e) { return $e->getMessage(); }
        }

        /**
         * Executa uma consulta no banco de dados
         *
         * @author Paulo Silva
         * @date 31/07/2015
         * @version 1.7
         * @param string $sql Instrução a ser executada
         * @param mixed $params Os parâmetros que serão inseridos no WHERE da consulta (o array ser montado com a função whereParam). Default: NULL = Sem filtro
         * @param string $orderBy Indica o conteúdo da cláusula ORDER BY
         * @param string $groupBy Indica o conteúdo da cláusula GROUP BY
         * @param bool $addWhere Indica se a função deve incluir a cláusula WHERE desde o início ou se ela já está presente na query e apenas os parâmetros devem ser adicionados
         * @return PDOException|mixed Exceção caso ocorra ou um array PDO::FETCH_ASSOC contendo as linhas e colunas da consulta em caso de sucesso
         */
        public function select($sql, $params = null, $orderBy = null, $groupBy = null, $addWhere = true){
            try {
                $pdo = $this->getPDO();

                // Só adiciona filtro se houverem parâmetros para tal
                if ($params){
                    if ($addWhere) $sql .= " WHERE 1 = 1 "; // Jogadinha para preencher o primeiro espaço no WHERE e todos os filtros seguintes serem usados com "AND"

                    foreach ($params as $param) $sql .= " AND $param[column] $param[operator] ? ";
                }

                if ($groupBy) $sql .= " GROUP BY $groupBy ";
                if ($orderBy) $sql .= " ORDER BY $orderBy ";

                $stmt = $pdo->prepare($sql);

                $i = 0;

                if ($params) foreach ($params as $param) $stmt->bindParam(++$i, $param[value], $param[type]);

                $stmt->execute();

                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $errorInfo = $stmt->errorInfo();

                $stmt->closeCursor();

                $this->_lastResult = $result;

                if (!$result) error_log(print_r($errorInfo, true));

                return $result ?: $errorInfo[2];
            } catch (PDOException $e) { return array("errorMsg" => $e->getMessage()); }
        }

        /**
         * Executa e retorna a primeira linha de uma consulta no banco
         *
         * @author Paulo Silva
         * @date 23/06/2015
         * @version 1.4
         * @param string $sql Consulta a ser executada
         * @param mixed $params Os parâmetros que serão inseridos no WHERE da consulta (o array ser montado com a função whereParam). Default: NULL = Sem filtro
         * @param string $orderBy Indica o conteúdo da cláusula ORDER BY
         * @return mixed Linha da consulta caso exista ou NULO
         */
        public function selectTopOne($sql, $params = null, $orderBy = null, $groupBy = null){
            $this->select($sql, $params, $orderBy, $groupBy);

            return $this->getResultRow();
        }

        /**
         * Executa e retorna um SELECT simples no banco, como "SELECT 1 FROM table" para determinar se certa condição é atendida
         *
         * @author Paulo Silva
         * @date 03/08/2015
         * @version 1.3
         * @param string $table O nome da tabela onde deve ser executada a consulta
         * @param string $column O nome da coluna que será consultada. Por default, adotará "1" para usar em SELECTs de caráter booleano
         * @param array $params Os parâmetros que serão inseridos no WHERE da consulta
         * @param string $orderBy Indica o conteúdo da cláusula ORDER BY
         * @return mixed O valor da consulta realizada (apenas primeira célula da primeira linha - que deve ser a única por tratar-se de uma consulta simples)
         */
        public function simpleSelect($table, $column = null, $params = null, $orderBy = null){
            $column = $column ?: "1";

            $sql = "SELECT TOP 1 $column result FROM $table";

            $this->select($sql, $params, $orderBy);

            return $this->getResultCell();
        }

        /**
         * Inicia uma transação no SGDB
         *
         * @author Paulo Silva
         * @date 24/09/2015
         * @version 1.0
         */
        public function beginTransaction(){
            $this->_PDO->beginTransaction();
        }

        /**
         * Encerra a transação no SGDB
         *
         * @author Paulo Silva
         * @date 24/09/2015
         * @version 1.0
         * @param bool $commit Indica se a ação de commit deverá ocorrer (padrão: True), ou executará um Rollback cancelando as queries da transação atual
         */
        public function endTransaction($commit = true){
            if ($commit) $this->_PDO->commit(); else $this->_PDO->rollBack();
        }

        /**
         * Funções de utilidade
         */

        /**
         * Cria um array para ser usado como parâmetro de WHERE nas consultas desta classe
         *
         * @author Paulo Silva
         * @date 31/07/2015
         * @version 1.0
         * @param string $column Nome da coluna a ser filtrada
         * @param string $value Valor utilizado no filtro
         * @param string $operator Operador lógico usado na comparação. Default: Igualdade (=)
         * @param PDO::PARAM Tipo do valor utilizado para filtragem
         * @return array Array contendo as chaves column, value e operator, e seus respectivos valores
         */
        public function whereParam($column, $value, $operator = "=", $type = PDO::PARAM_STR){ return array("column" => $column, "value" => $value, "operator" => $operator, "type" => $type); }

        /**
         * Recupera uma única linha do resultado de uma consulta no banco
         *
         * @author Paulo Silva
         * @date 23/06/2015
         * @version 1.4
         * @param int $rowNumber Número da linha desejada no Result Set. Default: Zero, primeira linha
         * @param mixed $sqlResult Result Set de uma consulta ao banco. Caso não informado adotará o último armazenado na classe
         * @return array Array contendo as colunas da linha desejada
         */
        public function getResultRow($rowNumber = null, $sqlResult = null){
            // Adotamos a primeira como padrão (zero)
            if ($rowNumber == null) $rowNumber = 0;

            $sqlResult = $sqlResult ?: $this->_lastResult;

            return $sqlResult[$rowNumber];
        }

        /**
         * Recupera uma única célula do resultado de uma consulta no banco
         *
         * @author Paulo Silva
         * @date 23/06/2015
         * @version 1.45
         * @param int $rowNumber Número da linha desejada no Result Set. Default: Zero, primeira linha
         * @param int $columnNumber Número da coluna desejada no Result Set. Default: Zero, primeira coluna
         * @param mixed $sqlResult Result Set de uma consulta ao banco. Caso não informado adotará o último armazenado na classe. IMPORTANTE: A coluna desejada no Result Set deve possuir o alias 'result'
         * @param mixed $escape Retorno padrão para situações de escape (i.e. não haver Result Set)
         * @return mixed Valor da célula desejada
         */
        public function getResultCell($rowNumber = null, $columnNumber = null, $sqlResult = null, $escape = null){
            // Se não foram setadas linha e coluna, adotamos a primeira como padrão (zero)
            if ($rowNumber    == null) $rowNumber    = 0;
            if ($columnNumber == null) $columnNumber = 0;

            $sqlResult = $sqlResult ?: $this->_lastResult;

            // Recupera o valor da coluna quando encontrar a linha desejada ($key)
            $key = 0;

            foreach ($sqlResult as $value) if ($key++ == $rowNumber) return ($value['result'] ?: $value['RESULT']); // Em maiúsculo por causa do maldito DB2

            return $escape;
        }
    }
?>