<?php
    /**
     * Localiza e envia o XML do CT-e para o WebService de averbação
     *
     */
    namespace Library\Ajax;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/averbacaoCte.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptSap.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptMSSQL.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';

    $get = filter_input_array(INPUT_GET);

    $cte = $get['cte'];

    $explode = explode("-", $cte, 3);

    $sigla = $explode[0];
    $numero = $explode[1];
    $DocEntry = $explode[2];

    
    //buscar qual a seguradora para envio do arquivo.
    $sql = "SELECT S.U_CODESEGR
                FROM [@SIEGO_CTRC] C
                LEFT JOIN [@SIEGO_SEGL] S ON (S.Code = C.U_CARDCODE_OCRDCB)
                WHERE U_BPLID_OBPL=$sigla AND U_CODECTRC= '$numero' ";
    $SQLeXEC = mssql_query($sql);
    while($dados = mssql_fetch_array($SQLeXEC)){ 
        if($dados[U_CODESEGR] == 'NULL'){
            $codigoSeguradora = 0;
        }
            $codigoSeguradora = $dados[U_CODESEGR];
    }

    /**
     * O caminho do arquivo compõe-se de:
     *
     * - Diretório = O caminho da pasta ArquivosCTeNFe do GetOne
     * - Pasta = O ID da empresa (sempre 1) apendado na sigla da filial
     * - Arquivo = A sigla e número do CT-e, separados por hífen sem espaço, e a extensão .xml
     */
    //$path   = "\\\ZAP03\\ArquivosCTeNFe\\XML\\";
    //$folder = "1_" . $sigla . "\\";
    //$file   = $cte . ".xml";
    //BUSCA a localizacao completa do arquivo XML
    $fullFileName = averbacaoFile($sigla, $numero);    
    $xmlData = file_get_contents($fullFileName);

    
    //if para verificar se ja existe este CTe no LogAverbcao.
    if(buscaCteLogAverbacao($sigla, $numero) == 0){
        $dados = averbar($xmlData, $codigoSeguradora);
        $response  = $dados[response];
        $averbacao = $dados[averbacao];
        $protocolo = $dados[protocolo];
        $chave     = $dados[chave];
        $tipo      = $dados[tipo];
        $ambinete  = $dados[ambiente];
        $integradora = $dados[integradora];

        insertLogAverbacao($sigla, $numero, $response, $_SESSION[idUsuario], $averbacao, $protocolo, $chave, $tipo, $ambinete, $integradora);

        protocoloAverbacaoSAP($DocEntry, $response);

        return printf(json_encode($response));
    }
    
    
?>