<?php
    /**
     * Localiza e envia o XML do CT-e para o WebService de averbação
     *
     * @author Paulo Silva
     * @date 02/06/2016
     */
    namespace Library\Ajax;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptSap.php';

    $get = filter_input_array(INPUT_GET);

    $cte = $get['cte'];

    $explode = explode("-", $cte, 3);

    $sigla = $explode[0];
    $numero = $explode[1];
    $DocEntry = $explode[2];

    /** Inicializa o client SOAP do WS da TraceLog e chama o método responsável por devolver a lista de veículos com suas posições atuais */
    $wsAverbacaoCTe = new \Library\Classes\wsAverbacaoCTe();

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
    $file = averbacaoFile($sigla, $numero);
    
    //$fullFileName = $path . $folder . $file;
    $fullFileName = $file;
    $xmlData = file_get_contents($fullFileName);

    $response = $wsAverbacaoCTe->UploadFile("faturamento@zappellini.com.br", $xmlData);

    $dbcSQL = new \Library\Classes\connectMSSQL();
    $dbcSQL->connect();

    $params = array('sigla' => $sigla, 'numero' => $numero, 'retorno' => $response, 'idUsuario' => $_SESSION['idUsuario']);

    $result = $dbcSQL->execute("INSERT INTO logaverbacao (sigla, numero, retorno, idUsuario) VALUES (:sigla, :numero, :retorno, :idUsuario)", $params);

    $dbcSQL->disconnect();
    
    protocoloAverbacaoSAP($DocEntry, $response);
        
    

    return printf(json_encode($response));
?>