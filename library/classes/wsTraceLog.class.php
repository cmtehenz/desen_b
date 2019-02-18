<?php
    /**
     * Classe para implementação e leitura do WebService de rastreamento de caminhões da TraceLog
     *
     * @author Paulo Silva
     * @date 11/01/2016
     * @version 1.01
     * @package Library/Classes
     */

    namespace Library\Classes;

    /**
     * Classe para implementação e leitura do WebService de rastreamento de caminhões da TraceLog
     *
     * @author Paulo Silva
     * @date 11/01/2016
     * @version 1.01
     * @package Library/Classes
     */
    final class wsTraceLog extends WebService {
        /**
         * Usuário de acesso ao WS
         * @var string Usuário de acesso ao WS
         */
        private $_login;

        /**
         * Senha de acesso ao WS
         * @var string Senha de acesso ao WS
         */
        private $_senha;

        /**
         * Construtor que instancia o client SOAP com o endereço da TraceLog e configura o usuário e senha para logar no webservice
         *
         * @author Paulo Silva
         * @date 11/01/2016
         * @param string $login Usuário para acessar o WS
         * @param string $senha Senha para acessar o WS
         */
        public function __construct($login, $senha){
            $this->_login = $login;
            $this->_senha = $senha;

            parent::__construct("http://186.215.191.101/WS/Logws.asmx?WSDL");
        }

        /**
         * Parâmetros padrão para todos os métodos da TraceLog
         *
         * @return array Array com os parâmetros de login dos métodos do WS da TraceLog
         */
        private function defaultParams(){ return array('Usuario' => $this->_login, 'Senha' => $this->_senha); }

        /**
         * Última posição registrada para um veículo ou todos vinculados ao usuário e senha da TraceLog.
         *
         * Manual: http://186.215.191.101/WS/Logws.asmx?op=RecebePosicaoAtual
         *
         * @author Paulo Silva
         * @param string $placa Parâmetro opcional para consulta de uma placa em específico
         * @return mixed Response obtido pelo método já tratado para leitura com foreach, ou nulo em caso de erro (consultar última exceção)
         */
        public function RecebePosicaoAtual($placa = null){
            $params = $this->defaultParams();

            if ($placa) $params['Placa'] = $placa;

            $this->configMethod("RecebePosicaoAtual", array('parameters' => $params));

            $result = $this->soapCall();

            return $result ? $this->lastResult()->RecebePosicaoAtualResult->Posicao : null;
        }
    }
?>