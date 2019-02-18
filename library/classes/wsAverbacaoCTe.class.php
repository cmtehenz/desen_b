<?php
    /**
     * Classe para implementação e leitura do WebService dde averbação dos conhecimentos na seguradora
     *
     * @author Paulo Silva
     * @date 02/06/2016
     * @version 1.00
     * @package Library/Classes
     */

    namespace Library\Classes;

    /**
     * Classe para implementação e leitura do WebService dde averbação dos conhecimentos na seguradora
     *
     * @author Paulo Silva
     * @date 02/06/2016
     * @version 1.00
     * @package Library/Classes
     */
    final class wsAverbacaoCTe extends WebService {
        /**
         * URL de execução do WebService, de acordo com o ambiente (Produção ou Homologação)
         * @var string Endereço para conexão ao WS
         */
        private $_url;

        /**
         * Seguradora de averbação do CT-e
         * @var string Valor padrão para a tag 'Seguradora', que muda de acordo com o ambiente de execução
         */
        private $_seguradora = 'BRD';

        /**
         * Construtor padrão do WebService
         * @param bool $producao Indica se deve instanciar o Client no ambiente de produção. Default = True.
         */
        public function __construct($producao = true){
            $this->_url = $producao ?
                "http://brd.qct.com.br:8080/webservice/wsdl/IReceiveFile?WSDL" :
                "http://qrc.qct.com.br:8090/webservice/wsdl/IReceiveFile?WSDL";

            $this->_seguradora = $producao ? "BRD" : "QRC";

            parent::__construct($this->_url);
        }

        /**
         * Parâmetros padrão para todos os métodos da BRD
         *
         * @return array Array com os parâmetros de login dos métodos do WS da BRD
         */
        private function defaultParams(){ return array('Seguradora' => $this->_seguradora); }

        /**
         * Upload de arquivos XML referente aos CT-es que serão averbados.
         *
         * Manual: Não disponível.
         *
         * @author Paulo Silva
         * @param string $email E-mail para receber o retorno do Request
         * @param string $xmlData Conteúdo do XML do CT-e
         * @return mixed Response obtido pelo método já tratado para leitura com foreach, ou nulo em caso de erro (consultar última exceção)
         */
        public function UploadFile($email, $xmlData){
            $params = $this->defaultParams();

            $params['Email']   = $email;
            $params['XMLData'] = $xmlData;

            $this->configMethod("UploadFile", $params);

            $result = $this->soapCall();

            return $result ? $this->lastResult() : null;
        }
    }
?>