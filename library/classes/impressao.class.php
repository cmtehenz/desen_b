<?php
    namespace Library\Classes;

    /**
     * Classe para implementação de páginas de impressão no BID
     *
     * @author Paulo Silva
     * @date 13/04/2016
     * @version 1.04
     * @package Library/Classes
     */
    class impressao {
        /**
         * Título do relatório
         * @var string Título da página que aparecerá no topo da aba e no cabeçalho
         */
        private $_title;

        /**
         * Colunas da tabela principal
         * @var array Título das colunas que compõe o cabeçalho da tabela principal
         */
        private $_columns;

        /**
         * Alinhamento do conteúdo nas células de cada coluna
         * @var string Define a posição do texto para cada coluna da tabela principal. Padrão de todos ao inicializar = Left.
         */
        private $_columnsAlignment;

        /**
         * Define se a impressão será em modo Retrato (True) ou Paisagem (False). Padrão = False.
         * @var bool Formato da impressão (retrato ou paisagem)
         */
        public $Portrait = false;

        /** Funcções de criação e controle da classe */

        /**
         * Construtor da classe, que obriga a setar o título da página e permite configurar as colunas da impressão
         *
         * @author Paulo Silva
         * @date 13/04/2016
         * @param string $title Título da página HTML
         * @param array $columns Títulos das colunas que estarão no topo da tabela de impressão
         */
        public function __construct($title, $columns = null) {
            $this->_title   = $title;
            $this->_columns = $columns;

            $this->_columnsAlignment = array();

            for ($i = 0; $i < count($columns); $i++) $this->setAlign($i, 'left');
        }

        /**
         * Define o alinhamento do conteúdo das células de uma determinada coluna
         * @param int $colNumber Número da coluna (baseado em zero)
         * @param string $align Alinhamento horizontal do texto (left, center ou right)
         */
        public function setAlign($colNumber, $align){ $this->_columnsAlignment[$colNumber] = $align; }

        /**
         * Define o alinhamento do conteúdo das células de uma determinada coluna para a direita
         * @param int $colNumber Número da coluna (baseado em zero)
         */
        public function setAlignRight ($colNumber){ $this->setAlign($colNumber, "right"); }

        /**
         * Define o alinhamento do conteúdo das células de uma determinada coluna para centralizado
         * @param int $colNumber Número da coluna (baseado em zero)
         */
        public function setAlignCenter($colNumber){ $this->setAlign($colNumber, "center"); }

        /** Funções de uso da classe */

        /**
         * Título da página HTML
         *
         * @return string Apenda o prefixo "BID" ao título da página e retorna
         */
        public function titulo(){ return ("BID - " . $this->_title); }

        /**
         * CSS padrão da página, incluindo style básico para as tabelas e definições para impressão em A4
         * @param bool $portrait Cooleano opcional para mudar a impressão em A4 para retrato
         * @return string Código CSS para inclusão no HTML do relatório
         */
        public function defaultStyles($portrait = false){
            if ($portrait) $this->Portrait = $portrait;

            return
                '<style type="text/css">
                    body {
                        background: rgb(255,255,255);
                        font-family: Verdana, Geneva, sans-serif;
                        font-size: 11px;
                        font-style: normal;
                    }

                    @page {
                        margin-top: 1cm;
                        margin-right: 1cm;
                        margin-bottom:2cm;
                        margin-left: 2cm;
                        size: ' . ($this->Portrait ? "portrait" : "landscape") . ';
                        -webkit-transform: rotate(-90deg); -moz-transform: rotate(-90deg);
                        filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=3);
                    }

                    @media print {
                        body, page[size="A4"] {
                            margin: 1cm;
                            box-shadow: 0;
                        }

                        table { page-break-after: auto; }
                        tr    { page-break-inside: avoid; }
                        td    { page-break-inside: auto; }

                        thead { display: table-header-group; }
                        tbody { display: table-row-group; }
                        tfoot { display: table-footer-group; }
                    }

                    @media screen {
                        thead { display: block; }
                        tfoot { display: block; }
                    }

                    .header { border: 3px solid #000; min-height: 30px; margin-bottom: 10px; text-align: center; vertical-align: middle; padding: 10px 0px 10px 0px; }
                    .title { font-size: 25px; font-weight: bold; }

                    table { width: 100%; page-break-inside: auto; border: solid 1px #000; border-collapse: collapse; }
                    table thead { display: table-header-group; }
                    table tbody { display: table-footer-group; }
                    table tr {  page-break-inside: avoid; page-break-after: auto; }
                    table th { padding: 5px; background-color: #cccccc; border: solid 1px #000; }
                    table td { padding: 5px; border: solid 1px #000; }

                    table.totalizador { margin-top: 15px; }
                </style>';
        }

        /**
         * JavaScript padrão para o relatório, responsável pela impressão automática da página
         * @return string Código JS para inclusão no HTML do relatório
         */
        public function defaultJS(){
            return
                "<script type='text/javascript'>
                    window.onload = function () { window.print(); setTimeout(function(){ window.close(); }, 1); };
                </script>";
        }

        /**
         * Cabeçalho padrão da página
         * @return string Código HTML para criar o quadro com título do relatório
         */
        public function header(){
            return '<div class="header"><span class="title">' . (mb_strtoupper($this->_title, 'UTF-8')) . '</span></div>';
        }

        /**
         * Cabeçalho de tabela contendo os títulos de colunas do relatório
         * @return string Código HTML para inclusão no <thead> da tabela principal
         */
        public function tableColumns($columns = null){
            $columns = $columns ?: $this->_columns;

            $thead = "";
            $thead .= "<tr>";

            foreach ($columns as $col) $thead .= "<th>$col</th>";

            $thead .= "</tr>";

            return $thead;
        }

        /**
         * Corpo de tabela com os dados passados por parâmetro
         * @param mixed $dataInfo Array multi-dimensional com as linhas e dados de cada célula (posição no array) para que sejam escritos no relatório
         * @return string Código HTML para inclusão no <tbody> da tabela principal
         */
        public function tableBody($dataInfo){
            $tbody = "";

            foreach ($dataInfo as $result){
                $row = "";
                $row .= "<tr>";

                /** Verifica a quantidade de posições (colunas, e o nome de cada uma) no array para escrever os valores em cada célula da linha */
                $keys = array_keys($result);
                $sizeof = sizeof($result);

                for ($i = 0; $i < $sizeof; $i++){
                    $keyName = $keys[$i]; // Identifica o nome da coluna para recuperar o valor no array
                    $align = $this->_columnsAlignment[$i];

                    $row .= "<td align='$align'>$result[$keyName]</td>";
                }

                $row .= "</tr>";

                $tbody .= $row;
            }

            return $tbody;
        }
    }
?>
