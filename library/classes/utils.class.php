<?php
    /**
     * <p>
     *  Package de funções para facilitar o dia a dia (formatações, includes, etc) e funções para manipulação automatizada
     *  de dados utilizados em massa no produto
     * </p>
     *
     * @author Paulo Silva
     * @date 10/06/2015
     * @version 2.3
     * @package Library/Classes
     */

    namespace Library\Classes;

    use Library\Classes\connectMSSQL as conSQL;
    use PDO;
    use DateTime;

    /**
     * Classe para utilização das funções de utilidade no sistema, como formatações de valores
     *
     * @author Paulo Silva
     * @date 20/06/2015
     * @version 2.05
     * @package Library/Classes
     * @subpackage Utils
     */
    final class Utils {
        /**
         * Funções para formatação de valores e resultados
         */

        /**
         * Número de dias existentes entre 2 datas
         *
         * @author Unknown
         * @date Unknown
         * @version 1.3
         * @param date $dtIni Data inicial
         * @param date $dtFin Data final
         * @return int Dias de diferença entre a data inicial e final
         */
        function diffDateDays($dtIni, $dtFin){
            // Calcula a diferença em segundos entre as datas
            $diferenca = strtotime($dtFin) - strtotime($dtIni);

            //Calcula a diferença em dias
            return floor($diferenca / (60 * 60 * 24));
        }

        /**
         * Verifica se uma data em qualquer formato é valida
         *
         * @author PHP.net
         * @date isValid? HA gotcha
         * @param string $date A data para verificação
         * @param string $format Formato para verificação da data (por exemplo, se ela é válida no formato Y-m-d)
         * @return bool True caso seja uma data válida e existente, ou False em casos contrários
         */
        function isValidDate($date, $format = 'Y-m-d H:i:s'){
            $d = DateTime::createFromFormat($format, $date);

            return $d && $d->format($format) == $date;
        }

        /**
         * Converte o formato de datas usando a classe DateTime, útil para conversões que não são nativamente feitas com a função 'date($format, $input)'
         *
         * @author Internet
         * @date 10/06/2015
         * @version 1.2
         * @param type $date
         * @param type $from
         * @param type $to
         * @return type
         */
        function dateFormat($date, $from = 'd/m/Y', $to = 'Y-m-d'){
            $datetime = DateTime::createFromFormat($from, $date);

            return $datetime ? $datetime->format($to) : $date;
        }

        /**
         * Substitui o number_format nativo que utiliza 0 decimais (aqui serão 2 por padrão), facilitando o uso das formatações numéricas
         *
         * @author Paulo Silva
         * @date 19/06/2015
         * @version 1.4
         * @param float $number Número para formatação
         * @param string $escape String de escape para retorno caso haja falha na formatação ou $number for NULL
         * @param int $decimals Quantidade de casas decimais. Default: 2
         * @param string $dec_point Separador das casas decimais. Default: Vírgula
         * @param string $thousands_sep Separador das casas de milhares. Default: Ponto
         * @return string A string numérica formatada de acordo com os parâmetros escolhidos
         */
        function numberFormat($number, $escape = 0, $decimals = 2, $dec_point = ",", $thousands_sep = "."){
            if ($number == NULL) return ($escape != NULL) ? $escape : number_format(0, $decimals, $dec_point, $thousands_sep);

            $number = str_replace(',', '.', $number); // Substitui a vírgula decimal que vem do DB2

            $result = number_format($number, $decimals, $dec_point, $thousands_sep);

            return $result;
        }

        /**
         * Corrige um decimal formatando-o para o padrão usado para cálculos no PHP (número com ponto decimal e sem separador de milhar).
         * Muito útil para os valores que vem de consultas DB2
         *
         * @author Paulo Silva
         * @date 10/06/2016
         * @param string $number Variável numérica para correção
         * @param int $decimals Quantidade de casas decimais
         * @return decimal Valor formatado corretamente no padrão XXXXXX.YYYY
         */
        function numberCorrect($number, $decimals = 2){ return $this->numberFormat($number, 0, $decimals, '.', ''); }

        /**
         * Insere uma máscara em um valor
         *
         * @author Internet
         * @date 01/07/2015
         * @version 1.0
         * @param string $value Valor
         * @param string $mask Máscara a ser aplicada
         * @return string O valor mascarado
         */
        function maskFormat($value, $mask){
            $valorMascarado = "";
            $k = 0;

            for ($i = 0; $i <= strlen($mask) - 1; $i++){
                if ($mask[$i] == '#'){
                    if (isset($value[$k])) $valorMascarado .= $value[$k++];
                }
                else
                {
                    if (isset($mask[$i]))$valorMascarado .= $mask[$i];
                }
            }

            return $valorMascarado;
        }

        /**
         * Retorna no formato YYYY-MM-DD o dia 1º do mês / ano desejados
         *
         * @author Paulo Silva
         * @date 26/06/2015
         * @version 1.0
         * @param int $ano Ano
         * @param int $mes Mês
         * @return date Data no formato Y-m-d
         */
        function firstDayOfMonth($ano, $mes){ return date("$ano-" . str_pad($mes, 2, "0", STR_PAD_LEFT) . "-01"); }

        /**
         * Retorna no formato YYYY-MM-DD o último dia do mês / ano desejados
         *
         * @author Paulo Silva
         * @date 26/06/2015
         * @version 1.0
         * @param int $ano Ano
         * @param int $mes Mês
         * @return date Data no formato Y-m-d
         */
        function lastDayOfMonth($ano, $mes){ return ($ano . "-" . str_pad($mes, 2, "0", STR_PAD_LEFT) . "-" . cal_days_in_month(CAL_GREGORIAN, $mes, $ano)); }

        /**
         * Quantidade de dias úteis no mês / ano
         *
         * @author Paulo Silva
         * @date 06/07/2015
         * @version 1.3
         * @param int $ano Ano desejado
         * @param int $mes Mês desejado
         * @param int $diaLimite Utilizar para definir um dia limite para contagem dos úteis, como por exemplo 15 (calculará a qtd. de dias úteis até o dia 15)
         * @return int Quantidade de dias úteis encontrados no mês e ano
         */
        function weekDays($ano, $mes, $diaLimite = null) {
            // Recupera o último dia do mês especificado ou utiliza o parâmetro passado
            $lastDay  = $diaLimite ?: date("t", mktime(0, 0, 0, $mes, 1, $ano));
            $weekDays = 0;

            for ($d = 1; $d <= $lastDay; $d++) {
                $util = date("w", mktime(0, 0, 0, $mes, $d, $ano));

                // Se o dia na semana for maior que zero (Domingo) e menor que seis (Sábado), é um dia útil
                if ($util > 0 && $util < 6) $weekDays++;
            }

            return $weekDays;
        }

        /**
         * Nome do mês
         *
         * @author Paulo Silva
         * @date 10/08/2015
         * @version 1.0
         * @param int $mes Mês desejado
         * @return string Retorna o nome do mês formatado com a primeira letra em maiúsculo
         */
        function monthName($mes){
            $nome = strftime('%B', mktime(0, 0, 0, $mes, 1, date('Y')));

            return utf8_encode(strtoupper(substr($nome, 0, 1)) . substr($nome, 1, strlen($nome) - 1));
        }

        /**
         * Funções para geração automática de comandos / strings
         */

        /**
         * Monta a URL correta para o caminho desejado dentro do site, colocando o domínio no começo
         * <p>
         *  Exemplo:
         *  Se você está dentro do diretório "administracao" e precisa montar o link para o arquivo "pagar/anual.php não precisa
         *  se preocupar em subir diretórios manualmente (href='../pagar/anual.php'), basta chamar esta função e ela lhe devolverá http://HOST/pagar/anual.php
         * </p>
         * @param string $caminho Caminho do arquivo desejado dentro da estrutura do sistema
         * @return string URL completa do arquivo desejado, incluindo o domínio do sistema
         */
        function getURLDestino($caminho){ return "http://$_SERVER[HTTP_HOST]/$caminho"; }

        /**
         * Prepara código em JavaScript para execução de um alerta
         *
         * @author Paulo Silva
         * @date 17/06/2015
         * @param type $msg Mensagem a ser exibida no modal
         * @param string $title Título da mensagem
         * @param string $callbackAction Ação a ser realizada no CallBack do script. Por padrão executa um history.back()
         * @return string Modal personalizada com a mensagem a ser exibida
         */
        function alertScript($msg, $title = "Atenção", $callbackAction = "history.back();"){
            return
                "<script>$(function () {
                    $.alert ({
                        type: 'alert',
                        title: '$title',
                        text: '<p>$msg</p>',
                        callback: function () { $callbackAction }
                    });
                });</script>";
        }

        /**
         * Popula o array de logs na sessão
         *
         * @author Paulo Silva
         * @date 23/09/2015
         * @param string $msg Mensagem de erro / aviso / sucesso
         * @param string $type Tipo de log (implementação futura)
         */
        function pushLog($msg, $type = "sucesso"){ array_push($_SESSION['retLog'], array("msg" => $msg, "type" => $type)); }

        /**
         * Funções para manipulação de dados e informações
         */

        /**
         * Busca, monta e retorna em HTML o menu do usuário que for passado por parâmetro, destacando o menu do módulo ativo
         *
         * @author Paulo Silva
         * @date 12/06/2015
         * @version 2.5
         * @param int $idUsuario ID do usuário atual
         * @param int $active ID do módulo ativo
         * @return string
         */
        function menuUsuario($idUsuario, $active = null){
            $active = $active ?: $_SESSION['modulo'];

            // Busca registros e monta linhas dos menus
            $sql = "SELECT
                        o.nome nomeModulo, o.url modulo, (o.url + '/' + m.url) url, m.nome nomeMenu, o.icon iconModulo
                    FROM menu_usuario u
                    JOIN menu   m ON m.id_menu = u.id_menu
                    JOIN modulo o ON m.idModulo = o.idModulo
                    WHERE
                        u.id_usuario = :idUsuario AND o.idModulo IS NOT NULL AND o.produto = 'B'
                    ORDER BY
                        o.ordenacao, m.ordenacao";

            $dbc = new conSQL();
            $dbc->connect();

            $pdo = $dbc->getPDO();

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":idUsuario", $idUsuario);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt->closeCursor();

            $modulo = array();

            foreach ($result as $dadosMenu){
                // Verifica se o módulo do registro corrente é diferente do último, se for adiciona ele ao array de módulos
                // e começa a escrever seus sub-menus
                $nomeModulo = $dadosMenu[nomeModulo];

                if (($modulo[count($modulo) - 1][nome]) != $nomeModulo)
                    array_push($modulo, array("nome" => $nomeModulo, "icon" => $dadosMenu[iconModulo]));

                $menu[count($modulo) - 1] .=
                    "<li><a href='" . $this->getURLDestino($dadosMenu[url]) . "'>$dadosMenu[nomeMenu]</a></li>";

                // Monta a class do li de cada menu, colocando a 'nav active' no que foi parametrizado ($active == x)
                $navClass[count($modulo) - 1] = 'nav' . (($active == $dadosMenu[modulo]) ? ' active' : '');
            }

            // Monta o código HTML e retorna
            $echoMenu .= "<div id='sidebar'><ul id='mainNav'>";
            $echoMenu .= "<li class='nav'>
                              <span class='icon-home'></span>
                              <a href='" . $this->getURLDestino("dashboard.php") . "'>Dashboard</a>
                          </li>";

            for ($i = 0; $i < count($menu); $i++){
                $echoMenu .=
                    "<li class='$navClass[$i]'>
                        <span class='" . $modulo[$i][icon] . "'></span>
                        <a href=''>" . $modulo[$i][nome] . "</a>
                        <ul class='subNav'>
                            $menu[$i]
                        </ul>
                    </li>";
            }

            $echoMenu .= "</ul></div>";

            $dbc->disconnect();

            return $echoMenu;
        }

        /**
         * Gera log de erro aos ocorrer uma tenativa de login mal-sucedida no sistema
         *
         * @author Paulo Silva
         * @date 17/06/2015
         * @version 2.0
         * @param string $login Login utilizado
         * @param string $senha Senha utilizada
         * @param string $msg Mensagem de erro disparada
         */
        function gerarHistoricoLogin($login, $senha, $msg){
            $ip = getenv('REMOTE_ADDR');

            $dbc = new conSQL();
            $dbc->connect();

            $params = array("login" => $login, "senha" => $senha, "msg" => $msg, "ip" => $ip);

            $dbc->execute("INSERT INTO hlogin VALUES (:login, :senha, :msg, :ip, GETDATE())", $params);

            $dbc->disconnect();

            die ($this->alertScript($msg));
        }

        /**
         * Gera HTML de options para um select de anos
         *
         * @author Paulo Silva
         * @date 24/06/2015
         * @version 1.22
         * @param int $selected Indica a opção que deverá estar selecionada
         * @return string Código HTML para ser usado em um <select>
         */
        function getOptionsSelectAno($selected = null){
            for ($ano = $_SESSION['anoInicioERP']; $ano <= date('Y'); $ano++)
                $lista .= "<option value='$ano' " . (($ano == $selected) ? "selected" : "") . ">$ano</option>";

            return $lista;
        }

        /**
         * Gera HTML de options para um select de meses
         *
         * @author Paulo Silva
         * @date 24/06/2015
         * @version 1.4
         * @param int $selected Indica a opção que deverá estar selecionada
         * @param bool $optionTodos Incluir a opção 'Todos'. Default: True
         * @return string Código HTML para ser usado em um <select>
         */
        function getOptionsSelectMes($selected = null, $optionTodos = true){
            $dbc = new conSQL();
            $dbc->connect();

            $dados = $dbc->select("SELECT id_mes '0', descricao '1' FROM mes ORDER BY id_mes");

            $dbc->disconnect();

            /* Corrigindo erro de codificação do cecedilha ao vir do SQL Server */
            $select = str_replace("MarÃ§o", "Março", $this->getOptionsSelect($dados, $selected, ($optionTodos ? "Todos" : null)));

            return $select;
        }

        /**
         * Transforma um array multidimensional em um <select> HTML
         *
         * @author Paulo Silva
         * @date 02/07/2015
         * @version 1.3
         * @param array $dados Array de dados, sendo as posições 0 o valor das options e 1 o texto exibido
         * @param mixed $selected Valor da posição que deverá vir selecionada
         * @param string $optionTodos Valor para ser utilizado como option 'Neutra', 'Todos', a opção sem valor do <select>
         * @param bool $utf8encode Indica se deve aplicar a codificação UTF-8 ao valor das opções
         * @return string Código HTML para ser usado em um <select>
         */
        function getOptionsSelect($dados, $selected = null, $optionTodos = null, $utf8encode = false){
            if ($optionTodos) $lista .= "<option value=''>$optionTodos</option>";

            foreach ($dados as $option)
                $lista .=
                    "<option value='" . trim($option[0]) . "' " . ((trim($option[0]) == $selected) ? "selected" : "") . ">"
                        . ($utf8encode ? utf8_encode($option[1]) : $option[1]) .
                    "</option>";

            return $lista;
        }

        /**
         * Formata CNPJs e CPFs de acordo com tamanho da string (se > 11 então CNPJ)
         *
         * @author Paulo Silva
         * @date 01/07/2015
         * @version 1.2
         * @param string $valor String a ser formatada
         * @return string String na máscara de CNPJ ou CPF
         */
        function cnpjCpfFormat($valor){
            $valor = trim($valor);

            return $this->maskFormat($valor, ((strlen($valor) > 11) ? "##.###.###/####-##" : "###.###.###-##"));
        }

        /**
         * Link de impressão padrão do produto, composto pela subpasta "impressao", nome do arquivo originário e parâmetros da QueryString
         *
         * @author Paulo Silva
         * @date 03/06/2016
         * @param string $file Nome do arquivo originário da impressão
         * @param array $qs Array com parâmetros para a QueryString
         * @return string URL de redirecionamento para o script responsável pela impressão A4 da tela originária
         */
        function getLinkImpressao($file, $qs){ return "impressao/" . basename($file) . "?" . http_build_query($qs); }

        /**
         * Funções referentes ao produto / sistema e suas particularidades
         */

        /**
         * Nome da unidade federativa brasileira de acordo
         *
         * @param string $uf Sigla da UF desejada
         * @return string Nome da UF / estado
         */
        function nomeUf($uf){
            switch ($uf){
                case "AC": $nome = "Acre"; break;
                case "AL": $nome = "Alagoas"; break;
                case "AP": $nome = "Amapá"; break;
                case "AM": $nome = "Amazonas"; break;
                case "BA": $nome = "Bahia"; break;
                case "CE": $nome = "Ceará"; break;
                case "DF": $nome = "Distrito Federal"; break;
                case "ES": $nome = "Espírito Santo"; break;
                case "GO": $nome = "Goiás"; break;
                case "MA": $nome = "Maranhão"; break;
                case "MG": $nome = "Minas Gerais"; break;
                case "MS": $nome = "Matos Grosso do Sul"; break;
                case "MT": $nome = "Mato Grosso"; break;
                case "PA": $nome = "Pará"; break;
                case "PB": $nome = "Paraíba"; break;
                case "PE": $nome = "Pernambuco"; break;
                case "PI": $nome = "Piauí"; break;
                case "PR": $nome = "Paraná"; break;
                case "RJ": $nome = "Rio de Janeiro"; break;
                case "RN": $nome = "Rio Grande do Norte"; break;
                case "RO": $nome = "Rondônia"; break;
                case "RR": $nome = "Roraima"; break;
                case "RS": $nome = "Rio Grande do Sul"; break;
                case "SC": $nome = "Santa Catarina"; break;
                case "SE": $nome = "Sergipe"; break;
                case "SP": $nome = "São Paulo"; break;
                case "TO": $nome = "Tocantins"; break;

                default: $nome = "Erro";
            }

            return $nome;
        }

        /**
         * Retorna por extenso o status de um veículo baseado nas flags para o seu estado de viagem e manutenção
         *
         * @param string $statusViagem Flag com status de viagem do veículo (campo amonitoramento.statusBipe)
         * @param string $manutencao Flag indicando se o veículo está em manutenção (S para true e todo o resto para false)
         * @return string Descrição do status do veículo
         */
        function getStatusMonitoramento($statusViagem, $manutencao){
            if ($statusViagem == "D") $status = "Disponível";
            if ($statusViagem == "V") $status = "Em viagem";
            if ($statusViagem == "Z") $status = "Viagem vazia";

            return $status . ($manutencao == "S" ? " / Manutenção" : "");
        }

        /**
         * Descrição dos tipos de parcela do sistema Pamcard
         *
         * @author Paulo Silva
         * @date 09/11/2015
         * @param int $parcela Inteiro representando o tipo de parcela
         * @return string Descrição do tipo passado por parâmetro
         */
        function tipoParcelaPamcard($parcela){
            switch ($parcela){
                case "1": $tipo = "Adiantamento"; break;
                case "2": $tipo = "Intermediária"; break;
                case "3": $tipo = "Saldo"; break;
                case "4": $tipo = "Pedágio"; break;
                default : $tipo = "Indefinida"; break;
            }

            return $tipo;
        }
    }
?>