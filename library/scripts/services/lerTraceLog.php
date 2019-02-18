<?php
    namespace Library\Scripts\Services;

    /** Inicializando manualmente a variável de sessão com ROOT do projeto (para execuções via CLI) */
    $_SERVER['DOCUMENT_ROOT'] = dirname(dirname(dirname(__DIR__)));

    date_default_timezone_set('America/sao_paulo');

    include $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/autoloader.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/startSession.php';

    /** Inicializa o client SOAP do WS da TraceLog e chama o método responsável por devolver a lista de veículos com suas posições atuais */
    $wsTraceLog = new Library\Classes\wsTraceLog("monitoramento", "mo116");

    $response = $wsTraceLog->RecebePosicaoAtual();

    foreach ($response as $veiculo){
        $placa = str_replace("-", "", $veiculo->PLACA);

        $params = array (
            "placa"      => $placa,
            "ponto"      => (str_replace("'", "", $veiculo->POS)),
            "ignicao"    => $veiculo->IG == "L" ? 1 : 0,
            "latitude"   => $veiculo->LA ?: 0,
            "longitude"  => $veiculo->LO ?: 0
        );

        /** Busca o ID da placa no banco do BID, pois caso não exista (no monitoramento do BID), nós não inserimos informação daqui */
        $result = $dbcSQL->selectTopOne("SELECT TOP 1 id FROM monitoramento WHERE placa = '$placa'");

        if ($result){
            $sql =
                "UPDATE monitoramento SET
                    ponto = :ponto, ignicao = :ignicao, latitude = :latitude, longitude = :longitude
                WHERE placa = :placa";

            $dbcSQL->execute($sql, $params);
        }
    }
?>