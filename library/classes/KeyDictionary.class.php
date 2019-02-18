<?php
    namespace Library\Classes;

    /**
     * Classe estática que contém pares de chave / valor e funções para manipulação dos mesmos
     *
     * @author Paulo Silva
     * @date 23/11/2015
     * @version 0.4
     */
    class KeyDictionary {
        /**
         * Constantes para alguns dos DataDictionaries
         */

        /** Tipo de envio de e-mail (Destinatário, cópia ou cópia oculta) */
        /**
         * Destinatário
         * @const "T"
         */
        const TP_ENVMAIL_TO = "T";

        /**
         * Cópia
         * @const "C"
         */
        const TP_ENVMAIL_CC = "C";

        /**
         * Cópia oculta
         * @const "B"
         */
        const TP_ENVMAIL_BC = "B";

        /** Tipo de contrato (Terceiro, Agregado, Frota) */
        /**
         * Frota
         * @const "F"
         */
        const TP_CONTRATO_FROTA = "F";

        /**
         * Agregado
         * @const "A"
         */
        const TP_CONTRATO_AGREGADO = "A";

        /**
         * Terceiro
         * @const "T"
         */
        const TP_CONTRATO_TERCEIRO = "T";

        /** Classificação de infrações ao CTB */
        /**
         * Leve
         * @const "L"
         */
        const CL_INFRACAO_LEVE = "L";

        /**
         * Média
         * @const "M"
         */
        const CL_INFRACAO_MEDIA = "M";

        /**
         * Grave
         * @const "G"
         */
        const CL_INFRACAO_GRAVE = "G";

        /**
         * Gravíssima
         * @const "V"
         */
        const CL_INFRACAO_GRAVISSIMA = "V";

        /** Códigos de evento da folha Questor */
        /**
         * Faltas / atrasos
         * @const 58
         */
        const EV_QUESTOR_FALTAS = 58;

        /**
         * DSR Trabalhado
         * @const 71
         */
        const EV_QUESTOR_DSRT = 71;

        /**
         * Adiantamentos
         * @const 99
         */
        const EV_QUESTOR_ADTO = 99;

        /**
         * Comissão
         * @const 100
         */
        const EV_QUESTOR_COMISSAO = 100;

        /**
         * Hora extra 50%
         * @const 102
         */
        const EV_QUESTOR_EXTRA50 = 102;

        /**
         * Hora extra 100%
         * @const 103
         */
        const EV_QUESTOR_EXTRA100 = 103;

        /**
         * Adicional noturno
         * @const 104
         */
        const EV_QUESTOR_ADNOTURNO = 104;

        /**
         * Horas espera
         * @const 105
         */
        const EV_QUESTOR_ESPERA = 105;

        /**
         * Créditos de despesa / descontos indevidos
         * @const 106
         */
        const EV_QUESTOR_DEBCRE = 106;

        /**
         * Diárias de alimentação
         * @const 102
         */
        const EV_QUESTOR_DIARIA = 108;

        /**
         * Converte o array de KeyValuePair para um multidimensional, visando ser utilizado na montagem de um <select>
         *
         * @param array $arrKeyValuePair Array simples de chaves e valores a serem convertidos
         * @return array Array multidimensional com as informações
         */
        private static function keyValuePairToArray($arrKeyValuePair){
            $return = array();

            foreach ($arrKeyValuePair as $key => $value) array_push($return, array(0 => $key, 1 => $value));

            return $return;
        }

        /**
         * Códigos de Operação para o Controle de Revisão de Veículos
         *
         * @return array Array KeyValuePair com os códigos e descrições
         */
        public static function operacao(){ return array('F' => 'Frota', 'L' => 'Limeira', 'S' => 'Suzano', 'O' => 'Ortigueira', 'P' => 'Veículo parado'); }

        /**
         * Array multidimensional com chave e valor dos códigos de Operação para o Controle de Revisão dos Veículos
         *
         * @return mixed Array Multidimensional com os códigos e descrições
         */
        public static function arrayOperacao(){
            $operacao = KeyDictionary::operacao();

            $return = array();

            foreach ($operacao as $key => $value) array_push($return, array(0 => $key, 1 => $value));

            return $return;
        }

        /**
         * Descrição do código de Operação
         *
         * @param char $key Chave desejada
         * @return string Valor para a chave informada
         */
        public static function valueOperacao($key){
            $operacao = KeyDictionary::operacao();

            return $operacao[$key];
        }

        /**
         * Códigos de classificação de ocorrências para o módulo de Qualidade
         *
         * @return array Array KeyValuePair com os códigos e descrições
         */
        public static function classificacao(){ return array('Q' => 'Qualidade', 'S' => 'Segurança', 'O' => 'Outros'); }

        /**
         * Array multidimensional com chave e valor dos códigos de Classificação de Ocorrência
         *
         * @return mixed Array Multidimensional com os códigos e descrições
         */
        public static function arrayClassificacao(){
            $classificacao = KeyDictionary::classificacao();

            $return = array();

            foreach ($classificacao as $key => $value) array_push($return, array(0 => $key, 1 => $value));

            return $return;
        }

        /**
         * Descrição do código de Classificação
         *
         * @param char $key Chave desejada
         * @return string Valor para a chave informada
         */
        public static function valueClassificacao($key){
            $classificacao = KeyDictionary::classificacao();

            return $classificacao[$key];
        }

        /**
         * Tipos de contrato do veículo
         *
         * @return array Array KeyValuePair com os códigos (char) e descrição dos tipos de contrato
         */
        public static function tipoContrato(){
            return array(
                self::TP_CONTRATO_FROTA => 'Frota',
                self::TP_CONTRATO_AGREGADO => 'Agregado',
                self::TP_CONTRATO_TERCEIRO => 'Terceiro'
            );
        }

        /**
         * Array multidimensional com chave e valor dos tipos de contrato
         *
         * @return mixed Array Multidimensional com os códigos e descrições
         */
        public static function arrayTipoContrato(){
            $arrKeyValuePair = KeyDictionary::tipoContrato();

            return self::keyValuePairToArray($arrKeyValuePair);
        }

        /**
         * Descrição do tipo de contrato a partir de seu código
         *
         * @param char $codigo Char (1) que representa o contrato
         * @return string Descrição para o código informado
         */
        public static function valueTipoContrato($codigo){
            $contrato = KeyDictionary::tipoContrato();

            return $contrato[$codigo];
        }

        /**
         * Classificações de infrações ao CTB
         *
         * @return array Array KeyValuePair com os códigos (char) e descrição das classificações de infrações de trânsito
         */
        public static function classificacaoInfracao(){
            return array(
                self::CL_INFRACAO_LEVE => 'Leve',
                self::CL_INFRACAO_MEDIA => 'Média',
                self::CL_INFRACAO_GRAVE => 'Grave',
                self::CL_INFRACAO_GRAVISSIMA => 'Gravíssima'
            );
        }

        /**
         * Array multidimensional com chave e valor das classificações de infração de trânsito
         *
         * @return mixed Array Multidimensional com os códigos e descrições
         */
        public static function arrayClassificacaoInfracao(){
            $arrKeyValuePair = KeyDictionary::classificacaoInfracao();

            return self::keyValuePairToArray($arrKeyValuePair);
        }

        /**
         * Descrição do tipo de classificação de infração a partir de seu código
         *
         * @param char $codigo Char (1) que representa a classificação
         * @return string Descrição para o código informado
         */
        public static function valueClassificacaoInfracao($codigo){
            $classificacao = KeyDictionary::classificacaoInfracao();

            return $classificacao[$codigo];
        }

        /**
         * Códigos de eventos para exportação da folha de pagamento Questor
         *
         * @return array Array KeyValuePair com os códigos (char) e descrição dos eventos da Questor
         */
        public static function eventosQuestor(){
            return array(
                self::EV_QUESTOR_FALTAS => 'Faltas / atrasos',
                self::EV_QUESTOR_DSRT => 'DSRT',
                self::EV_QUESTOR_ADTO => 'Adiantamentos',
                self::EV_QUESTOR_COMISSAO => 'Comissão',
                self::EV_QUESTOR_EXTRA50 => 'Hora extra 50%',
                self::EV_QUESTOR_EXTRA100 => 'Hora extra 100%',
                self::EV_QUESTOR_ADNOTURNO => 'Adicional noturno',
                self::EV_QUESTOR_ESPERA => 'Horas espera',
                self::EV_QUESTOR_DEBCRE => 'Crédito de despesas / descontos indevidos',
                self::EV_QUESTOR_DIARIA => 'Diárias alimentação'
            );
        }

        /**
         * Array multidimensional com chave e valor dos eventos da Questor
         *
         * @return mixed Array Multidimensional com os códigos e descrições
         */
        public static function arrayEventosQuestor(){
            $arrKeyValuePair = KeyDictionary::eventosQuestor();

            return self::keyValuePairToArray($arrKeyValuePair);
        }

        /**
         * Descrição do evento Questor a partir de seu código
         *
         * @param int $codigo Inteiro que representa o evento
         * @return string Descrição para o código informado
         */
        public static function valueEventosQuestor($codigo){
            $evento = KeyDictionary::eventosQuestor();

            return $evento[$codigo];
        }
    }
?>