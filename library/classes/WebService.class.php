<?php
    /**
     * Classe abstrata para implementação de webservices em PHP
     *
     * @author Paulo Silva
     * @date 11/01/2016
     * @version 1.01
     * @package Library/Classes
     */

    namespace Library\Classes;

    use SoapClient;
    use Exception;

    /**
     * Classe abstrata para implementação de webservices em PHP
     *
     * @author Paulo Silva
     * @date 11/01/2016
     * @version 1.01
     * @package Library/Classes
     */
    abstract class WebService {
        /**
         * Localização do WS
         * @var string Endereço / URL de utilização do serviço
         */
        private $_location;

        /**
         * Localização da classe WSDL do WS
         * @var string WSDL do serviço
         */
        private $_wsdl;

        /**
         * Instância do cliente SOAP da classe
         * @var SoapClient Objeto para conexão SOAP com o serviço
         */
        private $_soap;

        /**
         * Informações sobre a última exceção gerada
         * @var string Mensagem retornada pela última exceção ocorrida
         */
        public $_exception;

        /**
         * Método atual a ser chamado pelo serviço
         * @var string Nome do método no webservice a ser invocado pelo client
         */
        private $_callMethod;

        /**
         * Parâmetros a serem passados para o método de execução atual
         * @var array Array com os parâmetros para o método que está sendo executado
         */
        private $_params;

        /**
         * Último resultado recebido pelo client
         * @var mixed Informações do último resultado recebido
         */
        private $_result;

        /** Funcções de criação e controle da classe */

        /**
         * Preenche os parâmetros de configuração do client para conexão com o WS
         *
         * @author Paulo Silva
         * @date 11/01/2016
         * @param string $wsdl URL das classes WSDL do serviço
         * @param string $location URL do serviço
         */
        protected function setConfig($wsdl, $location){
            $this->_location = $location;
            $this->_wsdl     = $wsdl;
        }

        /**
         * Construtor do objeto SOAP da classe
         *
         * @author Paulo Silva
         * @date 11/01/2016
         * @param string $wsdl URL das classes WSDL do serviço
         * @param string $location Endereço do WS, que caso não informado será assumido o mesmo do WSDL
         * @throws Exception Gera exceção em caso de erro com a criação do SoapClient
         */
        public function __construct($wsdl, $location = null){
            if (!$location) $location = str_replace("?WSDL", "", $wsdl);

            $this->setConfig($wsdl, $location);

            try {
                $this->_soap = new SoapClient($this->_wsdl, array('trace' => 1, 'exception' => 1));
            }
            catch (Exception $e){
                $this->_exception = $e->getMessage();
            }
        }

        /**
         * Configura o nome do próximo método a ser chamado pelo client e os seus parâmetros
         *
         * @author Paulo Silva
         * @date 11/01/2016
         * @param string $name Nome do método que será consumido pelo serviço
         * @param array $params Parâmetros utilizados pelo método do webservice para busca dos dados
         */
        public function configMethod($name, $params){
            $this->_callMethod = $name;
            $this->_params     = $params;
        }

        /** Funções de uso da classe (chamadas de métodos, leituras, etc) */

        /**
         * Invoca o método parametrizado no client, processando seu resultado e erros para alimentar as properties da classe
         *
         * @author Paulo Silva
         * @date 11/01/2016
         * @return boolean True em caso de sucesso, alimentando a property $_result com o response gerado
         * @throws Exception Gera exceção em caso de erro com a chamada via SOAP e alimenta a property $_exception com informações do erro
         */
        public function soapCall(){
            try {
                $result = $this->_soap->__soapCall($this->_callMethod, $this->_params);

                $this->_result = $result;

                return true;
            } catch (Exception $e) {
                $this->_exception = $e->getMessage();

                return false;
            }
        }

        /**
         * Último resultado obtido pelo client
         *
         * @author Paulo Silva
         * @date 11/01/2016
         * @return mixed Objeto com os dados da última request respondida pelo serviço
         */
        public function lastResult(){ return $this->_result; }

        /**
         * Última exceção gerada
         *
         * @author Paulo Silva
         * @date 11/01/2016
         * @return string Mensagem do último erro ocorrido nos processos da classe
         */
        public function lastException(){ return $this->_exception; }

        /**
         * Último SOAP Request executado pelo client
         *
         * @author Paulo Silva
         * @date 02/06/2016
         * @return string Último XML enviado ao WS, em forma de texto
         */
        public function lastRequest() { return $this->_soap->__getLastRequest(); }

        /**
         * Último SOAP Response recebido pelo client
         *
         * @author Paulo Silva
         * @date 02/06/2016
         * @return string Último XML recebido do WS, em forma de texto
         */
        public function lastResponse() { return $this->_soap->__getLastResponse(); }
    }
?>