<?php

    namespace Modulos\Utilitarios\Post;
    
    //foi necessário aumentar o tempo de execução para aguardar a reposta do banco de dados
    set_time_limit(600);
    
    require $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';
        
    include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptSap.php';
    
    $hoUtils = new \Library\Classes\Utils();
    
    $urlArquivo = $_SERVER['DOCUMENT_ROOT'] . '/modules/utilitarios/post/bonusMotorista.txt';
    
    $post = filter_input_array(INPUT_POST);
    $dtIni = $post['dtIni'];
    $dtFim = $post['dtFin'];
    
    $erroData = false;
    $msg = "";
    $notify = "";
    
    //verifica se foi indicado uma data;   
    if($dtIni === "" || $dtFim === ""){
        $erroData = true;
        $msg = "Erro não foi encontrado a data para a busca.";
        $notify = "error";
    }
    
    //verifica se a dataInicial não é maior que a data final
    if(strtotime($dtIni) > strtotime($dtFim)){
        $erroData = true;
        $msg = "A data inicial não pode ser maior que a data final.";
        $notify = "error";
    }
    
    if(!$erroData){
        
        $linha = '';
        $fp = fopen($urlArquivo, "w");
        
        
        foreach (listaBonusMatricula($dtIni, $dtFim) as $bonus){
            
            
            $valor = number_format($bonus['TOTALBONUS'], 2 , '', ''); // retira ponto e virgula dos valor;        
            $linha .= "002;";
            $linha .= str_pad($bonus['MATRICULA'], 6, "0", STR_PAD_LEFT ).";";
            $linha .= "413;";
            $linha .= "00000;";
            $linha .= str_pad($valor, 9, "0", STR_PAD_LEFT);
            $linha .= PHP_EOL;
                       
        
        }
        //echo $linha;
        
    
        $teste = fwrite($fp, $linha);
    
        if($teste > 1){
            $msg = "Arquivo txt gravado com sucesso";
            $notify = "success";
        }else{
            $msg = "Erro ao tentar gravar";
            $notify = "error";
        }
        
        fclose($fp);
        
    }
    
    $_SESSION['mensagem']   = $msg;
    $_SESSION['notify']     = $notify;
    
    
    echo "<body onload='window.history.back();'>";
    
    
  

    

