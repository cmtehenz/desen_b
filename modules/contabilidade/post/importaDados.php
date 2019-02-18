<?php
    namespace Modulos\Contabilidade\Post;

    require $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    use PDO;

    header('Content-Type: text/html; charset=utf-8');

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Classes\connectMSSQL();
    
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/old/funcoes.php';

    /** Necessário para o alertScript personalizado */
    echo "<link rel='stylesheet' href=" . $hoUtils->getURLDestino('stylesheets/all.css') . " type='text/css' />";
    echo "<script src=" . $hoUtils->getURLDestino("js/all.js") . "></script>";
    
    /** Validações e retornos */
    $fileNameCon = $_FILES["file"]["tmp_name"];
    $fileName = $_FILES["file"]["name"];

    /** Parametros para gravar o aquivo. */
    //$uploaddir  = '/modules/contabilidade/xml/';
    //$uploadfile = '../xml/'.basename($_FILES["file"]["name"]);
        
    session_start();
    
    //echo $uploaddir;
    //echo $uploadfile;   
    
    $xml = simplexml_load_file($fileNameCon);
    if (isset($xml->NFe)){
        echo "Nota Fiscal";
        $tipo = 'NFE';
        $_SESSION['log'] = "Nota Fiscal";
        $chave = $xml->protNFe->infProt->chNFe;
        $versao = $xml->protNFe['versao'];
        $serie = $xml->NFe->infNFe->ide->serie;
        $nNF = $xml->NFe->infNFe->ide->nNF;
        $dhEmi = explode('-', str_replace('T', " ", $xml->NFe->infNFe->ide->dhEmi)) ;
        $dhEmi = $dhEmi[0].'-'.$dhEmi[1].'-'.$dhEmi[2];
        $emitCNPJ = $xml->NFe->infNFe->emit->CNPJ;
        $emitNome = $xml->NFe->infNFe->emit->xNome;
        $destCNPJ = $xml->NFe->infNFe->dest->CNPJ;
        $destNome = $xml->NFe->infNFe->dest->xNome;
        $_SESSION['log'] .= $destNome;
    }
    if (isset($xml->Nfse)){
        echo "Nota Servico";
        $tipo = 'NFSE';
        $_SESSION['log'] = "Nota Servico";
        $chave = $xml->Nfse->InfNfse->CodigoVerificacao;
        $versao = NULL;
        $serie  = NULL;
        $nNF = $xml->Nfse->InfNfse->Numero;
        $dhEmi = explode('-', str_replace('T', " ", $xml->Nfse->InfNfse->DataEmissao));
        $dhEmi = $dhEmi[0].'-'.$dhEmi[1].'-'.$dhEmi[2];
        $emitCNPJ = $xml->Nfse->InfNfse->PrestadorServico->IdentificacaoPrestador->Cnpj;
        $emitNome = $xml->Nfse->InfNfse->PrestadorServico->RazaoSocial;
        if(isset($xml->Nfse->InfNfse->TomadorServico->IdentificacaoTomador->CpfCnpj->Cnpj)){
            $destCNPJ = $xml->Nfse->InfNfse->TomadorServico->IdentificacaoTomador->CpfCnpj->Cnpj;
        }
        if(isset($xml->Nfse->InfNfse->TomadorServico->IdentificacaoTomador->CpfCnpj->Cpf)){
            $destCNPJ = $xml->Nfse->InfNfse->TomadorServico->IdentificacaoTomador->CpfCnpj->Cpf;
        }
        $destNome = $xml->Nfse->InfNfse->TomadorServico->RazaoSocial;
        $_SESSION['log'] .= $destNome;
    }
    if (isset($xml->ListaNfse)){
        echo "Nota Servico 2";
        $tipo = 'NFSE';
        $_SESSION['log'] = "Nota Servico";
        $chave = $xml->ListaNfse->ComplNfse->Nfse->InfNfse->CodigoVerificacao;
        $versao = NULL;
        $serie  = NULL;
        $nNF = $xml->ListaNfse->ComplNfse->Nfse->InfNfse->Numero;
        $dhEmi = explode('-', str_replace('T', " ", $xml->ListaNfse->ComplNfse->Nfse->InfNfse->DataEmissao));
        $dhEmi = $dhEmi[0].'-'.$dhEmi[1].'-'.$dhEmi[2];
        $emitCNPJ = $xml->ListaNfse->ComplNfse->Nfse->InfNfse->PrestadorServico->IdentificacaoPrestador->Cnpj;
        $emitNome = $xml->ListaNfse->ComplNfse->Nfse->InfNfse->PrestadorServico->RazaoSocial;
        if(isset($xml->ListaNfse->ComplNfse->Nfse->InfNfse->TomadorServico->IdentificacaoTomador->CpfCnpj->Cnpj)){
            $destCNPJ = $xml->ListaNfse->ComplNfse->Nfse->InfNfse->TomadorServico->IdentificacaoTomador->CpfCnpj->Cnpj;
        }
        if(isset($xml->ListaNfse->ComplNfse->Nfse->InfNfse->TomadorServico->IdentificacaoTomador->CpfCnpj->Cpf)){
            $destCNPJ = $xml->ListaNfse->ComplNfse->Nfse->InfNfse->TomadorServico->IdentificacaoTomador->CpfCnpj->Cpf;
        }
        $destNome = $xml->ListaNfse->ComplNfse->Nfse->InfNfse->TomadorServico->RazaoSocial;
        $_SESSION['log'] .= $destNome;
    }
    
    //NOME DO ARQUIVO XML
    $fileNameDb = $tipo.$chave.'.xml';
    $fileNamePross = '../xml/'.$tipo.$chave.'.xml';
    if(xmlDuplicado($fileNameDb)){
        $_SESSION['log'] = $_SESSION['log']."<br>DUPLICADO! Arquivo ja inserido;"; 
    }else{
        //MOVER ARQUIVO
        if (move_uploaded_file($fileNameCon, $fileNamePross)) {
            $_SESSION['log'] .= "<br>Arquivo válido e enviado com sucesso....";
        } else {
            $_SESSION['log'] .= "Problemas para salvar o arquivo no servidor, FAVOR ENTRAR EM CONTATO COM O ADMINISTRADOR DA REDE!";
        }
        //SALVAR REGISTRO BANCO DE DADOS
        if(inserirXml($fileNameDb, $tipo, $chave, $versao, $serie, $nNF, $dhEmi, $emitCNPJ, $emitNome, $destCNPJ, $destNome)){
            $_SESSION['log'] .= "<br>Resgitro Salvo com Sucesso no Banco de Dados;";
        } else {
            $_SESSION['log'] .= "<br>ERRO SQL, Resgitro não Salvo;";
        }
    }
        
    /**
     * Recupera o caminho do arquivo que chamou este POST e retorna para ele alertando a mensagem de retorno.
     * O explode serve para remover qualquer parâmetro adjacente ao caminho do arquivo (i.e. file.php?id=1 se torna file.php)
     */
    $location = explode(".php", $_SERVER['HTTP_REFERER']);

    if (isset($_SESSION['log'])){
        return printf($hoUtils->alertScript("Importação finalizada, verifique o log de mensagens para maiores detalhes", "Pronto", "window.location = '$location[0].php'"));
    }else{
        return printf($hoUtils->alertScript("Importação não realizada, verifique o log de mensagens para maiores detalhes", "Pronto", "window.location = '$location[0].php'"));
    }

?>