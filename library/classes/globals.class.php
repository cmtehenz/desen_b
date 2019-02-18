<?php
    namespace Library\Classes;

    /**
     * Classe estática para métodos genéricos que podem ser utilizados no projeto
     *
     * @author Paulo Silva
     * @date 24/09/2015
     * @version 0.1
     */
    class Globals {
        /**
         * Habilita as configurações do ambiente de desenvolvimento para debug e apuração de erros
         *
         * @param bool $status Indica se deverá ser ativado ou desativado
         */
        public static function setDevelopEnvironment($status = true){
            if ($status) {
                error_reporting(E_ALL);
                ini_set("display_errors", 1);
            }
            else
            {
                error_reporting(E_ERROR);
                ini_set("display_errors", 0);
            }
        }
    }
?>