<?php
   /***************************************************************************
    * Package de funções para facilitar o uso do DB2, como formatações padrão *
    * e scripts para rodar as consultas no banco com mais facilidade em um    *
    * único lugar. As funções com queries devem ser mantidas em outro arquivo *
    ***************************************************************************/

    /***************************************************************************
                 FUNÇÕES PARA FORMATAÇÃO DE VALORES E RESULTADOS
    ***************************************************************************/

    /**************************************************************
     * Função: numberFormatDB2                                    *
     * Programador: Paulo Silva                                   *
     * Data: 07/06/2015                                           *
     * Parâmetros:                                                *
     *     $number - Valor a ser formatado                        *
     *     $escape - Caracter a ser retornado caso seja nulo      *
     *     $decimals - Casas decimais. Padrão = 2                 *
     *     $dec_point - Separador decimal. Padrão = ","           *
     *     $thousands_sep - Separador de milhares. Padrão = "."   *
     * Descrição:                                                 *
     *     Realiza um number_format + str_replace automaticamente *
     *     pois o DB2 traz os resultados com ponto separando os   *
     *     decimais, e o PHP considera como string e acaba        *
     *     eliminando estas casas                                 *
     **************************************************************/
    function numberFormatDB2($number, $escape = NULL, $decimals = 2, $dec_point = ",", $thousands_sep = "."){
        if ($number == NULL) return ($escape != NULL) ? $escape : number_format(0, 2);

        $result = number_format(str_replace(',', '.', $number), $decimals, $dec_point, $thousands_sep);

        return $result;
    }

    /**************************************************************
     * Função: arraySumDB2                                        *
     * Programador: Paulo Silva                                   *
     * Data: 11/06/2015                                           *
     * Parâmetros:                                                *
     *     $numberArray - Array de números a ser totalizado       *
     * Descrição:                                                 *
     *     Totaliza um array de números que foram formatados para *
     *     o formato "T.HHH,DD", que não é numérico para ser      *
     *     somado corretamente com o array_sum.                   *
     **************************************************************/
    function arraySumDB2($numberArray){
        $i = 0;

        for ($i = 0; $i < count($numberArray); $i++)
            $result += str_replace(',', '.', str_replace('.', '', $numberArray[$i]));

        $result = numberFormatDB2($result);

        return $result;
    }

    /**************************************************************
     * Função: dateDB2                                            *
     * Programador: Paulo Silva                                   *
     * Data: 07/06/2015                                           *
     * Descrição:                                                 *
     *     Formata a data que vem do DB2 no formato YYYY-mm-dd    *
     *     para dd/mm/YYYY                                        *
     **************************************************************/
    function dateDB2($date){
        if ($date == NULL) return '-';

        return date('d/m/Y', strtotime($date));
    }

    /**************************************************************
     * Função: getCelulaConsulta                                  *
     * Programador: Paulo Silva                                   *
     * Data: 15/06/2015                                           *
     * Parâmetros:                                                *
     *     $sqlResult - Resultado de uma consulta já realizada    *
     *     $rowNumber - Número da linha desejada                  *
     *     $columnNumber - Número da coluna desejada              *
     * Descrição:                                                 *
     *     Recupera o valor de uma única célula (Row x Column) no *
     *     resultado de uma consulta no banco                     *
     **************************************************************/
    function getCelulaConsulta($sqlResult, $rowNumber = NULL, $columnNumber = NULL, $escape = NULL){
        // Se não foram setadas linha e coluna, adotamos a primeira como padrão (zero)
        if ($rowNumber    == NULL) $rowNumber    = 0;
        if ($columnNumber == NULL) $columnNumber = 0;

        // Recupera o valor da coluna quando encontrar a linha desejada ($key)
        $i = 0;

        foreach ($sqlResult as $value){
            if ($i == $rowNumber) return $value[$columnNumber];

            $i++;
        }

        return $escape;
    }

    /**************************************************************
     * Função: getLinhaConsulta                                   *
     * Programador: Paulo Silva                                   *
     * Data: 23/06/2015                                           *
     * Parâmetros:                                                *
     *     $sqlResult - Resultado de uma consulta já realizada    *
     *     $rowNumber - Número da linha desejada                  *
     * Descrição:                                                 *
     *     Recupera uma única linha (Row x Column) no resultado   *
     *     de uma consulta no banco                               *
     **************************************************************/
    function getLinhaConsulta($sqlResult, $rowNumber = NULL){
        // Adotamos a primeira como padrão (zero)
        if ($rowNumber == NULL) $rowNumber = 0;

        return $sqlResult[$rowNumber];
    }

    /***************************************************************************
                  FUNÇÕES PARA EXECUÇÃO DE COMANDOS NO BANCO
    ***************************************************************************/

    /**************************************************************
     * Função: getConsultaSQL                                     *
     * Programador: Paulo Silva                                   *
     * Data: 08/06/2015                                           *
     * Parâmetros:                                                *
     *     $sql - Consulta a ser executada                        *
     *     $conDB2 - Instância de conexão com DB2 caso houver,    *
     *               senão será adotada a global $hDbcDB2       *
     * Descrição:                                                 *
     *     Realiza uma consulta SQL no DB2 e retorna o array      *
     *     completo de resultados                                 *
     **************************************************************/
    function getConsultaSQL($sql, $conDB2 = NULL){
        include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_db2_bino.php';

        if ($conDB2 == NULL) $conDB2 = $hDbcDB2;

        $db2Fetch = db2_exec($conDB2, $sql);

        // Armazena o número de colunas da consulta para criá-las no Array posteriormente
        $numFields = db2_num_fields($db2Fetch);

        $i = 0;

        while ($array = db2_fetch_array($db2Fetch)){
            // Armazena o resultado de cada coluna na consulta em sua respectiva posição ($j), para cada linha ($i)
            for ($j = 0; $j < $numFields; $j++){
                $result[$i][$j] = $array[$j];
            }

            $i++;
        }

        return $result;
    }

    /**************************************************************
     * Função: getConsultaSQLSimples                              *
     * Programador: Paulo Silva                                   *
     * Data: 08/06/2015                                           *
     * Parâmetros:                                                *
     *     $sql - Consulta a ser executada                        *
     *     $conDB2 - Instância de conexão com DB2 caso houver,    *
     *               senão será adotada a global $hDbcDB2        *
     * Descrição:                                                 *
     *     Realiza uma consulta SQL no DB2 e retorna a primeira   *
     *     coluna / linha encontrada                              *
     **************************************************************/
    function getConsultaSQLSimples($sql, $conDB2 = NULL){
        $sqlResult = getConsultaSQL($sql, $conDB2);

        // Recupera o valor da primeira coluna na primeira linha da consulta, que se foi feita
        // corretamente deverá ser a única
        foreach ($sqlResult as $value) $valor = $value[0];

        return $valor;
    }

    /**************************************************************
     * Função: getConsultaSQLTopOne                               *
     * Programador: Paulo Silva                                   *
     * Data: 23/06/2015                                           *
     * Parâmetros:                                                *
     *     $sql - Consulta a ser executada                        *
     *     $conDB2 - Instância de conexão com DB2 caso houver,    *
     *               senão será adotada a global $hDbcDB2        *
     * Descrição:                                                 *
     *     Realiza uma consulta SQL no banco e retorna a primeira *
     *     linha apenas (TOP 1)                                   *
     **************************************************************/
    function getConsultaSQLTopOne($sql, $conDB2 = NULL){
        $sqlResult = getConsultaSQL($sql, $conDB2);

        return getLinhaConsulta($sqlResult);
    }

    /**************************************************************
     * Função: getConsultaSQLNumber                               *
     * Programador: Paulo Silva                                   *
     * Data: 08/06/2015                                           *
     * Parâmetros:                                                *
     *     $sql - Consulta que retornará valor NUMBER / DECIMAL   *
     *            a ser executada                                 *
     *     $conDB2 - Instância de conexão com DB2 caso houver,    *
     *               senão será adotada a global $hDbcDB2       *
     *     $escape - Caracter de escape para ser retornado caso o *
     *               resultado seja NULO                          *
     * Descrição:                                                 *
     *     Realiza uma consulta SQL no DB2 cujo resultado deverá  *
     *     ser numérico e retorna o valor já formatado.           *
     * OBS.: Para consultas não numéricas utilize outras funções  *
     * como a getConsultaSQL padrão                               *
     **************************************************************/
    function getConsultaSQLNumber($sql, $conDB2 = NULL, $escape = NULL){
        // Realiza a consulta
        $result = getConsultaSQLSimples($sql, $conDB2);

        // Se o resultado for nulo, retorna o caracter de escape informado ou 0
        // Senão, formata o valor para retorno
        if ($result == NULL)
            return ($escape != NULL) ? $escape : numberFormatDB2(0);
        else
            return numberFormatDB2($result);
    }
?>