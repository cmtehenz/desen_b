<?php
    namespace Modulos\Multas\Cadastros\Post;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    header('Content-Type: text/html; charset=utf-8');

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Classes\connectMSSQL();

    /** Necessário para o alertScript personalizado */
    echo "<link rel='stylesheet' href=" . $hoUtils->getURLDestino('stylesheets/all.css') . " type='text/css' />";
    echo "<script src=" . $hoUtils->getURLDestino("js/all.js") . "></script>";

    $params = filter_input_array(INPUT_POST);

    $dbcSQL->connect();

    /******************************** Validações e transação da notificação ********************************/
    $numAuto     = $params['numAuto'];
    $placa       = $params['placa'];
    $data        = $params['dtInfracao'];
    $codInfracao = $params['codInfracao'];
    $digInfracao = $params['digInfracao'];

    if ((!isset($numAuto)) || (strlen($numAuto) == 0)) return printf($hoUtils->alertScript("Informe Nº de auto da infração!"));

    if ((!isset($placa)) || (strlen($placa) == 0)) return printf($hoUtils->alertScript("Informe a placa do veículo!"));

    if (!isset($data) || strlen($data) <= 0) return printf($hoUtils->alertScript("Informe a data da infração!"));

    if (!isset($codInfracao) || strlen($codInfracao) <= 0 || !isset($digInfracao) || strlen($digInfracao) <= 0)
        return printf($hoUtils->alertScript("Informe o código da infração, incluindo o dígito!"));

    /** Valida se o código infração está cadastrado */
    $paramInf = array($dbcSQL->whereParam("codigo", $codInfracao), $dbcSQL->whereParam("digito", $digInfracao));

    $idInfracao = $dbcSQL->simpleSelect("mlt.infracao", "idInfracao", $paramInf);

    if (!isset($idInfracao) || $idInfracao == 0) return printf($hoUtils->alertScript("Código da infração não encontrado, por favor realize o cadastro no módulo de Multas!"));

    /** Caso já exista o registro informado, buscamos o ID para atualização */
    $paramsNot = array('numAuto' => $numAuto, 'placa' => $placa, 'dtInfracao' => $data, 'idInfracao' => $idInfracao);

    // Opcionais
    $paramsNot['dtRecurso'] = $params['dtRecurso'] ?: null;
    $paramsNot['orgao']     = $params['orgao'] ?: null;
    $paramsNot['obs']       = trim($params['observacao']) ?: null;

    $filter = array($dbcSQL->whereParam("numAuto", $params['numAuto']));

    $paramsNot['id'] = $params['idNotificacao'] ?: $dbcSQL->simpleSelect("mlt.notificacao", "idNotificacao", $filter);

    if ($paramsNot['id'] != null){
        $sqlNot =
            "UPDATE mlt.notificacao SET
                idInfracao = :idInfracao, numAuto = :numAuto, placa = :placa, dtInfracao = :dtInfracao, dtRecurso = :dtRecurso, idOrgao = :orgao, observacao = :obs
            WHERE idNotificacao = :id";

        $msgNot = "Notificação alterada com sucesso!";
    }
    else
    {
        $sqlNot = "INSERT INTO mlt.notificacao VALUES (:idInfracao, :numAuto, :placa, :dtInfracao, :dtRecurso, :orgao, :obs)";

        $msgNot = "Notificação inserida com sucesso!";
    }

    $result = $dbcSQL->execute($sqlNot, $paramsNot);

    if ($result) $msgNot = "Erro na transação: $result";

    /******************************** Validações e transação da multa ********************************/
    $vlrOriginal = $params['vlrOriginal'];
    $vlrVencido  = $params['vlrVencido'];
    $data        = $params['dtVencimento'] ?: null;

    /**
     * Só valida se pelo menos uma das informações sobre a multa estiver preenchida, pois caso contrário
     * ela pode nem estar sendo cadastrada ainda
     */
    if ($params['idMulta'] || $vlrOriginal != 0 || $vlrVencido != 0 || $data){
        if ((!isset($vlrOriginal)) || ($vlrOriginal <= 0) || (!is_numeric($vlrOriginal)))
            return printf($hoUtils->alertScript("Valor até o vencimento inválido!"));

        if ((!isset($vlrVencido)) || ($vlrVencido <= 0) || (!is_numeric($vlrVencido)))
            return printf($hoUtils->alertScript("Valor após o vencimento inválido!"));

        if (!isset($data)) return printf($hoUtils->alertScript("Data de vencimento inválida!"));

        $paramsMlt = array('idNotificacao' => $params['idNotificacao'], 'vlrOriginal' => $vlrOriginal, 'vlrVencido' => $vlrVencido, 'dtVencimento' => $data);

        $filter = array($dbcSQL->whereParam("idNotificacao", $params['idNotificacao']));

        $paramsMlt['id'] = $params['idMulta'] ?: $dbcSQL->simpleSelect("mlt.multa", "idMulta", $filter);

        if ($paramsMlt['id'] != null){
            $sqlMlt =
                "UPDATE mlt.multa SET
                    vlrOriginal = :vlrOriginal, vlrVencido = :vlrVencido, dtVencimento = :dtVencimento
                WHERE idMulta = :id OR idNotificacao = :idNotificacao";

            $msgMlt = "Multa alterada com sucesso!";
        }
        else
        {
            $sqlMlt = "INSERT INTO mlt.multa VALUES (:idNotificacao, :vlrOriginal, :vlrVencido, :dtVencimento)";

            $msgMlt = "Multa inserida com sucesso!";
        }

        /**
         * Prepara os valores da inserção / atualização e seus filtros (bindParam do PDO) e exeuta as transações
         */
        $result = $dbcSQL->execute($sqlMlt, $paramsMlt);

        if ($result) $msgMlt = "Erro na transação: $result";
    }


    $dbcSQL->disconnect();

    $msg = $msgNot . "<br />" . $msgMlt;

    /**
     * Recupera o caminho do arquivo que chamou este POST e retorna para ele alertando a mensagem de retorno.
     * O explode serve para remover qualquer parâmetro adjacente ao caminho do arquivo (i.e. file.php?id=1 se torna file.php)
     */
    $location = explode(".php", $_SERVER['HTTP_REFERER']);

    return printf($hoUtils->alertScript($msg, "Pronto", "window.location = '$location[0].php'"));
?>