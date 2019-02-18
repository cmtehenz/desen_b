<?php
    /**
     * Arquivo com classe utilizada para conexão PDO com bancos SQL Server
     *
     * @author Paulo Silva
     * @date 29/07/2015
     * @version 1.10
     * @package Library/Classes
     * @subpackage MSSQL
     */

    namespace Library\Classes;

    /**
     * Classe de conexão PDO com bancos SQL Server
     *
     * @author Paulo Silva
     * @date 29/07/2015
     * @version 1.10
     * @package Library/Classes
     * @subpackage Connection Classes
     */
    class connectMSSQL extends connectDatabase {
        /**
         * Construtor responsável por setar as configurações de conexão do objeto criado
         */
        public function __construct(){ $this->setConfig(); $this->setDSN("sqlsrv:Server=$this->_host;Database=$this->_db"); }

        /**
         * Função extendida da classe mãe que chama a mesma passando como parâmetro as variáveis de sessão contendo os dados de conexão ao SQL Server
         *
         * @access protected
         */
        protected function setConfig(){ parent::setConfig($_SESSION['dbHost'], $_SESSION['dbUser'], $_SESSION['dbPswd'], $_SESSION['dbName']); }
    }
?>