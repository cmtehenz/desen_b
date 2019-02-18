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
    $fileName = "exportaSap.txt"; //$_FILES["file"]["tmp_name"][0];

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
                'nome' => $nome,
                'referencia' => str_replace(array(":", ','), "", $referencia)
            );
    }

    /** Busca os valores para a folha ponto em duas tabelas diferentes e faz um merge dos resultados em um único array para exportação */
    //$dbcDB2->connect();
	
	include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
        include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptSap.php';
	if($cone_mssqlZap){
		echo "ok, conectado";
	}

       
    /** Processa o retorno do banco e monta uma matriz com cada código de evento possível para cada valor encontrado */
    $arLines = array();
 //   var_dump(listaFolha());
    $enc = 0;
    foreach (listaFolha() as $motorista){
        $posArray = array();
//echo "88888888".$motorista[0];
        $matricula = $motorista['MATRICULA'];
        $arLines[] = generatePosLine($matricula, DD::EV_QUESTOR_COMISSAO, null, $motorista['MATRICULA']);
        //$arLines[] = $motorista['NOME'];
        $arLines[] = generatePosLine($matricula, DD::EV_QUESTOR_COMISSAO, null, $motorista['VIAGENS']);
        $arLines[] = generatePosLine($matricula, DD::EV_QUESTOR_EXTRA50, $motorista['EXTRA50']);
        $arLines[] = generatePosLine($matricula, DD::EV_QUESTOR_EXTRA100, $motorista['EXTRA100']);
        $arLines[] = generatePosLine($matricula, DD::EV_QUESTOR_ADNOTURNO, $motorista['ADNOTURNO']);
        $arLines[] = generatePosLine($matricula, DD::EV_QUESTOR_ESPERA, $motorista['HORAESPERA']);
        $arLines[] = generatePosLine($matricula, DD::EV_QUESTOR_DIARIA, $motorista['DIARIAS']);
        $arLines[] = generatePosLine($matricula, DD::EV_QUESTOR_FALTAS, $hoUtils->numberFormat($motorista['HORAFALTA']));
        $arLines[] = generatePosLine($matricula, DD::EV_QUESTOR_DSRT, $motorista['DSRT']);
        $arLines[] = generatePosLine($matricula, DD::EV_QUESTOR_CREDITO, $motorista['CREDITOS']); //FFB
        $arLines[] = generatePosLine($matricula, DD::EV_QUESTOR_ADIANTAMENTO, $motorista['DEBITOS']); //FFB  EV_QUESTOR_ADIANTAMENTO EV_QUESTOR_CREDITO
       // $arLines[] = generatePosLine($matricula, DD::EV_QUESTOR_DIFCREDEB, $motorista['DIFCREDEB']); //FFB DIFERENÇA ENTRE CRÉDITOS E DÉBITOS
    }

    if ($fileName){
       
        /** Usamos o Try Catch para prever erros com o PDO ou de programação */
        try {
            /** Abre o arquivo para escrita dos dados */
            //$hFile = fopen($fileName, "w");
            $hFile = fopen("C:/inetpub/wwwroot/bid/producao/modules/utilitarios/post/exportaSap.txt", "w");

            foreach ($arLines as $posLine){
                if ($posLine != null){
                    $line = "";
                    if(($posLine['evento'] == "106") or ($posLine['evento'] == "191") or ($posLine['evento'] == "108") or ($posLine['evento'] == "071")){
                            if(($posLine['evento'] == "071")){
                                    $line .= "002;"; // Código da empresa no Questor
                                    $line .= str_pad($posLine['matricula'],  6, "0", STR_PAD_LEFT) . ";"; // Código do funcionário
                                    $line .= str_pad($posLine['evento'],     3, "0", STR_PAD_LEFT) . ";"; // Código do evento na Questor
                                    $line .= str_pad($posLine['valor'], 6, "0", STR_PAD_LEFT) . ";"; // Valor da referência
                                    $line .= str_pad($posLine['referencia'],      9, "0", STR_PAD_LEFT) . ";"; // Valor do evento
                                    $line .= PHP_EOL;

                            }else{
                                    $line .= "002;"; // Código da empresa no Questor
                                    $line .= str_pad($posLine['matricula'],  6, "0", STR_PAD_LEFT) . ";"; // Código do funcionário
                                    $line .= str_pad($posLine['evento'],     3, "0", STR_PAD_LEFT) . ";"; // Código do evento na Questor
                                    $line .= str_pad($posLine['valor'], 6, "0", STR_PAD_LEFT) . ";"; // Valor da referência
                                    $line .= str_pad($posLine['referencia'],      9, "0", STR_PAD_LEFT) . ";"; // Valor do evento
                                    $line .= PHP_EOL;
                            }
                    }else{
                            $line .= "002;"; // Código da empresa no Questor
                            $line .= str_pad($posLine['matricula'],  6, "0", STR_PAD_LEFT) . ";"; // Código do funcionário
                            $line .= str_pad($posLine['evento'],     3, "0", STR_PAD_LEFT) . ";"; // Código do evento na Questor
                            $line .= str_pad($posLine['referencia'], 6, "0", STR_PAD_LEFT) . ";"; // Valor da referência
                            $line .= str_pad($posLine['valor'],      9, "0", STR_PAD_LEFT) . ";"; // Valor do evento
                            $line .= PHP_EOL;
                    }
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

    //$dbcDB2->disconnect();

    /**
     * Recupera o caminho do arquivo que chamou este POST e retorna para ele alertando a mensagem de retorno.
     * O explode serve para remover qualquer parâmetro adjacente ao caminho do arquivo (i.e. file.php?id=1 se torna file.php)
     */
    $location = explode(".php", $_SERVER['HTTP_REFERER']);

    if (count($_SESSION['retLog']) <= 0) $hoUtils->pushLog("Nenhum arquivo informado para importação", "msg");

    return printf($hoUtils->alertScript("Importação finalizada, verifique o log de mensagens para maiores detalhes", "Pronto", "window.location = '$location[0].php'"));
?>