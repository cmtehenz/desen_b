<?php
    namespace Modulos\Utilitarios\Post;

    require $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    header('Content-Type: text/html; charset=utf-8');

    $hoUtils = new \Library\Classes\Utils();
    $dbcDB2  = new \Library\Scripts\scriptDB2();

    /** Necessário para o alertScript personalizado */
    echo "<link rel='stylesheet' href=" . $hoUtils->getURLDestino('stylesheets/all.css') . " type='text/css' />";
    echo "<script src=" . $hoUtils->getURLDestino("js/all.js") . "></script>";

    /** Validações e retornos */
    $fileName = "exportaBipe.txt"; //$_FILES["file"]["tmp_name"][0];

    session_start();

    $_SESSION['retLog'] = array();

    $post = filter_input_array(INPUT_POST);

    $dtIni = $post['dtIni'];
    $dtFin = $post['dtFin'];
    $caminho = "C:/inetpub/wwwroot/bid/producao/modules/utilitarios/post/";
    //echo $caminho;
    $arquivo = fopen("C:/inetpub/wwwroot/bid/desenv/modules/utilitarios/post/bipe.txt", "w");

    
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptSap.php';
    if($cone_mssqlZap){
            echo "ok, conectado";
    }

/** Processa o retorno do banco e monta uma matriz com cada código de evento possível para cada valor encontrado */
    $arLines = array();
//var_dump(listaBipe());
    foreach (listaBipe($dtIni, $dtFin) as $motorista){
        $posArray = array();

        //$matricula = $motorista['MATRICULA'];
        //$mesano[] = "08/2017";
        $arLines[] = $motorista['NOME'];
        $arLines[] = $motorista['VIAGENS'];
        $arLines[] = $motorista['MES'];
        $arLines[] = $motorista['ANO'];        
        $arLines[] = $motorista['CPF'];       
        $arLines[] = $motorista['NUMRG'];      
        $arLines[] = $motorista['DATANASC'];      
        $arLines[] = $motorista['VALFRETEPAGOTOT'];
        $arLines[] = $motorista['VALTOTLIQPAGO'];
        $arLines[] = $motorista['VALOBCINSSSEST'];
        $arLines[] = $motorista['VALINSS'];
        $arLines[] = $motorista['NUMINSS'];
        
        /*
        $linha = $motorista['NOME']. ";" .$mesano .";". $nome .";". $numCPF .";". $numRG. ";". $numInss .";". $datanasc .";XXX9999;". $valFretePagoTot.";".
                 $valTotLiqPago .";". $valBcINSS .";". $valINSS; 
         */
        if($motorista['MES'] < 10){
            $mes = "0" . $motorista['MES'];			
        }else{
			$mes = $motorista['MES'];
		}
        
        $vlrfretepagotot = str_replace('.', ',', $motorista['VALFRETEPAGOTOT']);
        $valtotliqpago = str_replace('.', ',', $motorista['VALTOTLIQPAGO']);
        $valbcinsssest = str_replace('.', ',', $motorista['VALOBCINSSSEST']);
        $valinss = str_replace('.', ',', $motorista['VALINSS']);
        $valbcinsssestCalc = number_format($motorista['VALOBCINSSSEST'] * 0.025, 2, ',', '');
        
        //verifica se o valor é inteiro, se for, adiciona ,00
        if(preg_match('/^[1-9][0-9]*$/', $valbcinsssest)){
            $valbcinsssest = $valbcinsssest . ",00";
        }
        if(preg_match('/^[1-9][0-9]*$/', $valinss)){
            $valinss = $valinss . ",00";
        }
        if(preg_match('/^[1-9][0-9]*$/', $valtotliqpago)){
            $valtotliqpago = $valtotliqpago . ",00";
        }
        if(preg_match('/^[1-9][0-9]*$/', $vlrfretepagotot)){
            $vlrfretepagotot = $vlrfretepagotot . ",00";
        }
        
        
        
        $linha = $mes. $motorista['ANO'] .";". $motorista['NOME'] .";". $motorista['CPF'] .
                 ";". $motorista['NUMRG']. ";". $motorista['NUMINSS'] .";". $motorista['DATANASC'] .";XXX9999;". 
                 $valtotliqpago .";". $vlrfretepagotot .";". $valbcinsssest .";". $valinss .";". $valbcinsssestCalc; 
        $linha .= PHP_EOL;
        fwrite($arquivo, $linha); 
        
    }
    fclose($arquivo);

	

    /**
     * Recupera o caminho do arquivo que chamou este POST e retorna para ele alertando a mensagem de retorno.
     * O explode serve para remover qualquer parâmetro adjacente ao caminho do arquivo (i.e. file.php?id=1 se torna file.php)
     */
    $location = explode(".php", $_SERVER['HTTP_REFERER']);

    if (count($_SESSION['retLog']) <= 0) $hoUtils->pushLog("Nenhum arquivo informado para importação", "msg");

    return printf($hoUtils->alertScript("Importação finalizada, verifique o log de mensagens para maiores detalhes", "Pronto", "window.location = '$location[0].php'"));
?>