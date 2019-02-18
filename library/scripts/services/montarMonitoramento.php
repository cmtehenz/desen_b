<?php
    namespace Library\Scripts\Services;

    /** Inicializando manualmente a variável de sessão com ROOT do projeto (para execuções via CLI) */
    $_SERVER['DOCUMENT_ROOT'] = dirname(dirname(dirname(__DIR__)));

    date_default_timezone_set('America/sao_paulo');

    include $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/autoloader.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/startSession.php';

    $listaVeiculos = $dbcDB2->select(
        "SELECT V.PLACA placa FROM HVEICULO V JOIN VEICEMP VE ON (VE.ID_VEICULO = V.ID_VEICULO AND VE.ID_EMPRESA = 1)",
        array (
            $dbcDB2->whereParam("VE.STAFT", $_GET['staft']),
            $dbcDB2->whereParam("VE.BLOQUEADO", "N")
        ),
        "V.PLACA", "V.PLACA");

    foreach ($listaVeiculos as $veiculo){
        $placa = trim($veiculo['PLACA']);

        /** Busca os dados do BIPE para determinar o status e informações da viagem
         * === Resumo da query ===
         * Primeira coluna - Status de BIPE
         *     - DECODE para verificar a data de baixa. Caso nula, significa que o caminhão está em viagem então fazemos um
         *     segundo DECODE pelo tipo de documento para identificar se é Viagem Vazia ('Z') ou não ('V'). Disponível = 'D'
         * Segunda coluna - Rota da viagem
         *     - DECODE para verificar a data de baixa e caso esteja nula (BIPE em aberto = Caminhão em viagem), trazemos a rota dele.
         * Terceira coluna - Data de alteração no status
         *     - DECODE para verificar também se o BIPE está em viagem ou não. Caso sim, buscamos a data de emissão para determinar há quantos dias
         *     está viajando. Caso contrário seleciona a data de baixa para determinar há quantos dias o veículo está disponível
         */
        $bipe = $dbcDB2->selectTopOne(
           "SELECT
                DECODE(B.DATABAIXA, NULL, DECODE(B.TIPODOCTO, 'Z', 'Z', 'V'), 'D') status,
                O.NOME_CIDADE origem, D.NOME_CIDADE destino, O.UF ufOri, D.UF ufDes,
                DECODE(B.DATABAIXA, NULL,
                    (B.DATAEMIS || ' ' || B.HORAEMIS || ':' || B.MINEMIS || ':00'),
                    (B.DATABAIXA || ' ' || B.HORABAIXA || ':' || B.MINBAIXA || ':00')
                ) data,
                VE.IDOPERLOG operLog,
                TRIM(LEFT(M.NOME, 100)) motorista
            FROM CADBIPE B
            JOIN HVEICULO V ON V.ID_HVEICULO = B.ID_HVEICULO
            JOIN VEICEMP VE ON (VE.ID_VEICULO = V.ID_VEICULO AND VE.ID_EMPRESA = 1)
            JOIN ROTA R ON B.ID_ROTA = R.ID_ROTA
            JOIN CIDADE O ON O.ID_CIDADE = R.ID_CIDADEORIG
            JOIN CIDADE D ON D.ID_CIDADE = R.ID_CIDADEDEST
            LEFT JOIN HMOTORIS M ON V.IDHMOTORIS = M.IDHMOTORIS
            WHERE
                V.PLACA = '$placa' AND NOT EXISTS (SELECT 1 FROM CT C WHERE C.IDCADBIPE = B.IDCADBIPE AND C.TIPOCTRC = 'C')
            ORDER BY B.IDCADBIPE DESC FETCH FIRST 1 ROWS ONLY");

        /** Busca os dados de última ordem de serviço em aberto para determinar se o veículo está em manutenção */
        $ordemSer = $dbcDB2->selectTopOne(
           "SELECT
                DECODE(O.DATA_CONCLUI, NULL, 'S', 'N') status,
                DECODE(O.DATA_CONCLUI, NULL,
                    (O.DATA_ABRE || ' ' || O.HORA_ABRE),
                    (O.DATA_CONCLUI || ' ' || O.HORA_CONCLUI || ':00')
                ) data
            FROM ORDEMSER O
            JOIN HVEICULO V ON V.ID_HVEICULO = O.ID_HVEICULO
            WHERE V.PLACA = '$placa' AND O.STATUS <> 'C'
            ORDER BY O.ID_ORDEMSER DESC FETCH FIRST 1 ROWS ONLY");

        /** Atribuição dos parâmetros */
        $dataBipe = $bipe['DATA'];
        $dataOS   = $ordemSer['DATA'];

        $result = $dbcSQL->selectTopOne("SELECT TOP 1 id FROM monitoramento WHERE placa = '$placa'");

        $params = array (
            "placa"      => $placa,
            "statusBipe" => $bipe['STATUS'] ?: "D",
            "statusOS"   => $ordemSer['STATUS'] ?: "N",
            "dataStatus" => (strtotime($dataBipe) > strtotime($dataOS)) ? $dataBipe : $dataOS,
            "operLog"    => $bipe['OPERLOG'],
            "origem"     => $bipe['ORIGEM'],
            "destino"    => $bipe['DESTINO'],
            "ufOri"      => $bipe['UFORI'],
            "ufDes"      => $bipe['UFDES'],
            "motorista"  => utf8_encode($bipe['MOTORISTA'])
        );

        $sql = $result
            ?
                "UPDATE monitoramento SET
                    statusBipe = :statusBipe, statusOS = :statusOS,
                    dataExecucao = CURRENT_TIMESTAMP, operLog = :operLog, dataStatus = :dataStatus,
                    origem = :origem, destino = :destino, ufOri = :ufOri, ufDes = :ufDes, motorista = :motorista
                WHERE placa = :placa"
            :
                "INSERT INTO monitoramento
                    (
                        placa, statusBipe, statusOS, dataExecucao, operLog, dataStatus, origem, destino, ufOri, ufDes, motorista
                    )
                VALUES
                    (
                        :placa, :statusBipe, :statusOS, CURRENT_TIMESTAMP, :operLog, :dataStatus, :origem, :destino, :ufOri, :ufDes, :motorista
                    )";

        $dbcSQL->execute($sql, $params);
    }
?>