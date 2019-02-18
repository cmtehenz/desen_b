<?php
    header('Cache-Control: no-cache');

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils;

    $get = filter_input_array(INPUT_GET);

    /** Busca o status do conhecimento e BIPE no DB2 */
    $dbcDB2 = new \Library\Classes\connectDB2();

    $dbcDB2->connect();

    $params = array( $dbcDB2->whereParam("F.SIGLA_FILIAL", $get['sigla']), $dbcDB2->whereParam("C.NUMERO", $get['cte']) );

    $result = $dbcDB2->select(
        "SELECT
            M.CPF cpfMot, M.NOME nomeMot,
            DECODE(C.STATUSCT, 'A', 'Emitido', 'C', 'Cancelado', 'Indefinido') stCTe,
            DECODE(B.DATABAIXA, NULL, 'Aberto', 'Baixado') stBipe
        FROM CT C
        JOIN FILIAL   F ON C.ID_FILIAL = F.ID_FILIAL
        JOIN CADBIPE  B ON C.IDCADBIPE = B.IDCADBIPE
        JOIN HVEICULO V ON C.ID_HVEICULO = V.ID_HVEICULO
        JOIN HMOTORIS M ON V.IDHMOTORIS = M.IDHMOTORIS", $params);

    $dadosDB2 = $dbcDB2->getResultRow();

    $dbcDB2->disconnect();

    /** Busca os dados do motorista e veículo no MSSQL */
    $dbcSQL = new \Library\Classes\connectMSSQL();

    $dbcSQL->connect();

    $params = array( $dbcSQL->whereParam("c.filial", $get['sigla']), $dbcSQL->whereParam("c.cte", $get['cte']) );

    $dadosSQL = $dbcSQL->selectTopOne("SELECT c.placa, c.cpf cpfFav, c.nome nomeFav FROM pcd.contrato c", $params);

    $dbcSQL->disconnect();

    $dados = array_merge($dadosDB2, $dadosSQL);

    $dados['motorista']  = $hoUtils->cnpjCpfFormat($dados['CPFMOT']) . ' - ' . trim($dados['NOMEMOT']);
    $dados['favorecido'] = $hoUtils->cnpjCpfFormat($dados['cpfFav']) . ' - ' . trim($dados['nomeFav']);

    echo json_encode($dados);
?>