<?php
    namespace Modulos\Utilitarios\Post;

    require $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    header('Content-Type: text/html; charset=utf-8');

    $hoUtils = new \Library\Classes\Utils();
    $dbcDB2  = new \Library\Scripts\scriptDB2();

    use \Library\Classes\KeyDictionary as DD;

    /** Necessário para o alertScript personalizado */
    echo "<link rel='stylesheet' href=" . $hoUtils->getURLDestino('stylesheets/all.css') . " type='text/css' />";
    echo "<script src=" . $hoUtils->getURLDestino("js/all.js") . "></script>";

    /** Validações e retornos */
    $fileName = "exporta.txt"; //$_FILES["file"]["tmp_name"][0];

    session_start();

    $_SESSION['retLog'] = array();

    $post = filter_input_array(INPUT_POST);

    $dtIni = $post['dtIni'];
    $dtFin = $post['dtFin'];

    function generatePosLine($matricula, $evento, $referencia = null, $valor = null){
        if ($referencia > 0 || $valor > 0)
            return array(
                'matricula' => $matricula,
                'evento' => $evento,
                'referencia' => str_replace(array(":", ','), "", $referencia),
                'valor' => str_replace(array(":", ','), "", $valor)
            );
    }

    /** Busca os valores para a folha ponto em duas tabelas diferentes e faz um merge dos resultados em um único array para exportação */
    $dbcDB2->connect();

    $sqlAcerto =
        "SELECT
            E.MATRICULA, M.NOME, SUM(A.VIAGENS) VIAGENS, SUM(A.DEBITOS) DEBITOS, SUM(A.CREDITOS) CREDITOS, SUM(A.COMISSAOCOMPL) COMPLEMENTO
        FROM ACERTO  A
        JOIN MOTORIS M ON A.IDMOTORIS = M.IDMOTORIS
        JOIN MOTEMP  E ON (M.IDMOTORIS = E.IDMOTORIS AND E.ID_EMPRESA = 1)
        WHERE
            A.ID_EMPRESA = 1 AND A.DATAFIM BETWEEN '$dtIni' AND '$dtFin' AND A.FECHAACERTO = 'S' AND A.STAF = 'F'
        GROUP BY E.MATRICULA, M.NOME
        ORDER BY M.NOME";

    $sqlPonto =
        "SELECT
            E.MATRICULA, M.NOME, P.TOT_EXTRA50 extra50, P.TOT_EXTRA100 extra100, P.TOT_ADICNOT adNoturno, P.TOT_HORAESPERA horaEspera, P.TOT_DIARIA diarias,
            (SELECT COUNT(I.ID_PONTO) * 7.33333 FROM ITPONTO I WHERE LOWER(I.MOTIVO) LIKE 'falt%' AND I.ID_PONTO = P.ID_PONTO) horaFalta, P.TOT_DSRT dsrt
        FROM PONTO   P
        JOIN MOTORIS M ON P.IDMOTORIS = M.IDMOTORIS
        JOIN MOTEMP  E ON (M.IDMOTORIS = E.IDMOTORIS AND E.ID_EMPRESA = 1)
        WHERE
            P.DATAINI = '$dtIni' AND P.DATAFIM = '$dtFin' ";

    $result = array_merge($dbcDB2->select($sqlAcerto), $dbcDB2->select($sqlPonto));

    /** Processa o retorno do banco e monta uma matriz com cada código de evento possível para cada valor encontrado */
    $arLines = array();

    foreach ($result as $motorista){
        $posArray = array();

        $matricula = $motorista['MATRICULA'];

        $arLines[] = generatePosLine($matricula, DD::EV_QUESTOR_COMISSAO, null, $motorista['VIAGENS']);
        $arLines[] = generatePosLine($matricula, DD::EV_QUESTOR_EXTRA50, $motorista['EXTRA50']);
        $arLines[] = generatePosLine($matricula, DD::EV_QUESTOR_EXTRA100, $motorista['EXTRA100']);
        $arLines[] = generatePosLine($matricula, DD::EV_QUESTOR_ADNOTURNO, $motorista['ADNOTURNO']);
        $arLines[] = generatePosLine($matricula, DD::EV_QUESTOR_ESPERA, $motorista['HORAESPERA']);
        $arLines[] = generatePosLine($matricula, DD::EV_QUESTOR_DIARIA, $motorista['DIARIAS']);
        $arLines[] = generatePosLine($matricula, DD::EV_QUESTOR_FALTAS, $hoUtils->numberFormat($motorista['HORAFALTA']));
        $arLines[] = generatePosLine($matricula, DD::EV_QUESTOR_DSRT, $motorista['DSRT']);
    }

    if ($fileName && $result){
        /** Usamos o Try Catch para prever erros com o PDO ou de programação */
        try {
            /** Abre o arquivo para escrita dos dados */
            $hFile = fopen($fileName, "w");

            foreach ($arLines as $posLine){
                if ($posLine != null){
                    $line = "";

                    $line .= "002;"; // Código da empresa no Questor
                    $line .= str_pad($posLine['matricula'],  6, "0", STR_PAD_LEFT) . ";"; // Código do funcionário
                    $line .= str_pad($posLine['evento'],     3, "0", STR_PAD_LEFT) . ";"; // Código do evento na Questor
                    $line .= str_pad($posLine['referencia'], 6, "0", STR_PAD_LEFT) . ";"; // Valor da referência
                    $line .= str_pad($posLine['valor'],      9, "0", STR_PAD_LEFT) . ";"; // Valor do evento
                    $line .= PHP_EOL;

                    fwrite($hFile, $line);
                }
            }

            fclose($hFile);
        } catch (\PDOException $p) {
            $hoUtils->pushLog("Atenção! Erro com transação PDO encontrada, informe o administrador do sistema ($p->getMessage())");
        } catch (\Exception $e) {
            $hoUtils->pushLog("Atenção! Ocorreu um erro, informe o administrador do sistema ($e->getMessage())");
        }
    }

    $dbcDB2->disconnect();

    /**
     * Recupera o caminho do arquivo que chamou este POST e retorna para ele alertando a mensagem de retorno.
     * O explode serve para remover qualquer parâmetro adjacente ao caminho do arquivo (i.e. file.php?id=1 se torna file.php)
     */
    $location = explode(".php", $_SERVER['HTTP_REFERER']);

    if (count($_SESSION['retLog']) <= 0) $hoUtils->pushLog("Nenhum arquivo informado para importação", "msg");

    return printf($hoUtils->alertScript("Importação finalizada, verifique o log de mensagens para maiores detalhes", "Pronto", "window.location = '$location[0].php'"));
?>