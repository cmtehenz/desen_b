<?php
    /**
     * Package de funções referentes ao MySQL, como consultas de informações para o Sighra
     *
     * @author Paulo Silva
     * @date 07/01/2016
     * @version 1.0
     * @package Library/Scripts
     */

    namespace Library\Scripts;

    /**
     * Package de funções referentes ao MySQL, como consultas de informações para o Sighra
     *
     * @author Paulo Silva
     * @date 07/01/2016
     * @version 1.0
     * @package Library/Scripts
     * @subpackage SQL
     */
    final class scriptMySQL extends \Library\Classes\connectMySQL {
        /**
         * Construtor responsável por setar as configurações de conexão do objeto criado e conectar automaticamente para uso facilitado da classe
         */
        public function __construct(){ parent::__construct(); $this->connect(); }

        /**
         * Método destrutor responsável por realizar a desconexão com o banco automaticamente
         */
        public function __destruct(){ $this->disconnect(); }
    }
?>