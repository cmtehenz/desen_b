<?php
    /**
     * Arquivo com classe utilizada para conexão PDO com bancos MySQL
     *
     * @author Paulo Silva
     * @date 07/01/2016
     * @version 1.0
     * @package Library/Classes
     * @subpackage MySQL
     */

    namespace Library\Classes;

    /**
     * Classe de conexão PDO com bancos MySQL
     *
     * @author Paulo Silva
     * @date 07/01/2016
     * @version 1.0
     * @package Library/Classes
     * @subpackage Connection Classes
     */
    class connectMySQL extends connectDatabase {
        /**
         * Construtor responsável por setar as configurações de conexão do objeto criado
         */
        public function __construct(){ $this->setConfig(); $this->setDSN("mysql:host=$this->_host;dbname=$this->_db"); }

        /**
         * Função extendida da classe mãe que chama a mesma passando como parâmetro as variáveis de sessão contendo os dados de conexão ao SQL Server
         *
         * @access protected
         */
        protected function setConfig(){ parent::setConfig($_SESSION['sighraDb']['host'], $_SESSION['sighraDb']['user'], $_SESSION['sighraDb']['pswd'], $_SESSION['sighraDb']['name']); }
    }
?>