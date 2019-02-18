<?php     
function buscaDadosWsAverbacao($idSeguradoraERP){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
    $sql = "SELECT top 1 * FROM wsSeguros where idSeguradoraERP=$idSeguradoraERP ";
    $SQLeXEC = mssql_query($sql);
    while($dados = mssql_fetch_array($SQLeXEC)){
        $da[wsHomologacao]      = $dados['wsHomologacao'];
        $da[wsProducao]         = $dados['wsProducao'];
        $da[tokenHomologacao]   = $dados['tokenHomologacao'];         
        $da[tokenProducao]      = $dados['tokenProducao'];
        $da[integradora]        = $dados['integradora'];
        $da[usuarioHomologacao] = $dados['usuarioHomologacao'];
        $da[usarioProducao]     = $dados['usuarioProducao'];
        $da[senhaHomologacao]   = $dados['senhaHomologacao'];
        $da[senhaProducao]      = $dados['senhaProducao'];
    }
    return $da;
}
    
    
function averbar($xmlData, $codigoSeguradora, $producao=TRUE){     
    if($codigoSeguradora){        
        //verifica se e chub seguros, para enviar a averbacao via ATM
        if($codigoSeguradora == 2){
            $dados = buscaDadosWsAverbacao($codigoSeguradora);
            if(!$producao){
                $ambiente = "HOMOLOGACAO";
                $url = $dados[wsHomologacao];
                $token = $dados[tokenHomologacao];
                $integradora = $dados[integradora];
                $usuario = $dados[usuarioHomologacao];
                $senha = $dados[senhaHomologacao];
                
                $client = new SoapClient($url);
                $result = $client->averbaCTe($usuario, $senha, $token, $xmlData );
                
                $da[response] = $result->Averbado->DadosSeguro->NumeroAverbacao;            
                $da[averbacao] = $result->Averbado->DadosSeguro->NumeroAverbacao;
                $da[protocolo] = $result->Averbado->Protocolo;
                $da[tipo] = $result->TpDoc;
                $da[ambiente]  = $ambiente;
                $da[integradora] = $integradora;
            }
            if($producao){
                $ambiente = "PRODUCAO";
                $url = $dados[wsProducao];
                $token = $dados[tokenProducao];
                $integradora = $dados[integradora];
                $usuario = $dados[usuarioProducao];
                $senha = $dados[senhaProducao];
                
                $client = new SoapClient($url);
                $result = $client->averbaCTe('ws', 'zappe0321', '11376420', $xmlData );
                
                foreach($result->Averbado->DadosSeguro as $y){
                    $da[response] = $y->NumeroAverbacao;
                    $da[averbacao] = $y->NumeroAverbacao;
                    break;
                }
                
                $da[protocolo] = $result->Averbado->Protocolo;
                $da[tipo] = $result->TpDoc;
                $da[ambiente]  = $ambiente;
                $da[integradora] = $integradora;
            }
            
        }
        //qualquer outra seguradora diferente de de 2 - chub KLABIN
        if($codigoSeguradora != 2){
            //codigoSeguradora = 3 - bradesco seguros.
            //Por padrao quando nao tem cadastro de apolices manda para QUORUM/BRADESCOSEGUROS
            $dados = buscaDadosWsAverbacao(3);
            if(!$producao){
                $ambiente = "HOMOLOGACAO";
                $url = $dados[wsHomologacao];
                $token = $dados[tokenHomologacao];
                $integradora = $dados[integradora];
            }
            if($producao){
                $ambiente = "PRODUCAO";
                $url = $dados[wsProducao];
                $token = $dados[tokenProducao];
                $integradora = $dados[integradora];
            } 
            //AVERBACAO COM INFORMACAO DA QUORUM
            $client = new SoapClient($url);
            $retono = $client->AverbaXML($token, 'CTE.XML', $xmlData);
            $retono = simplexml_load_string($retono);
            foreach ($retono->Response as $dados){     
                $da[response]  = $dados->Doc->Averbacao;
                $da[averbacao] = $dados->Doc->Averbacao;
                $da[protocolo] = $dados->Doc->Protocolo;
                $da[chave]     = $dados->Doc->Chave;
                $da[tipo]      = $dados->Doc->Tipo; 
                $da[ambiente]  = $ambiente;
                $da[integradora] = $integradora;
            }
        }
        
    }
    if($codigoSeguradora == 0){
        //codigoSeguradora = 3 - bradesco seguros.
        //Por padrao quando nao tem cadastro de apolices manda para QUORUM/BRADESCOSEGUROS
        $dados = buscaDadosWsAverbacao(3);
        if(!$producao){
            $ambiente = "HOMOLOGACAO";
            $url = $dados[wsHomologacao];
            $token = $dados[tokenHomologacao];
            $integradora = $dados[integradora];
        }
        if($producao){
            $ambiente = "PRODUCAO";
            $url = $dados[wsProducao];
            $token = $dados[tokenProducao];
            $integradora = $dados[integradora];
        } 
        //AVERBACAO COM INFORMACAO DA QUORUM
        $client = new SoapClient($url);
        $retono = $client->AverbaXML($token, 'CTE.XML', $xmlData);
        $retono = simplexml_load_string($retono);
        foreach ($retono->Response as $dados){     
            $da[response]  = $dados->Doc->Averbacao;
            $da[averbacao] = $dados->Doc->Averbacao;
            $da[protocolo] = $dados->Doc->Protocolo;
            $da[chave]     = $dados->Doc->Chave;
            $da[tipo]      = $dados->Doc->Tipo; 
            $da[ambiente]  = $ambiente;
            $da[integradora] = $integradora;
        }
    }
        
    return $da;
}

function averbarATM($xmlData, $codigoSeguradora, $producao=TRUE){             
        if($codigoSeguradora == 2){
            $dados = buscaDadosWsAverbacao(2);
            if(!$producao){
                $ambiente = "HOMOLOGACAO";
                $url = $dados[wsHomologacao];
                $token = $dados[tokenHomologacao];
                $integradora = $dados[integradora];
                $usuario = $dados[usuarioHomologacao];
                $senha = $dados[senhaHomologacao];
                
                $client = new SoapClient($url);
                $result = $client->averbaCTe($usuario, $senha, $token, $xmlData );
                
                $da[response] = $result->Averbado->DadosSeguro->NumeroAverbacao;            
                $da[averbacao] = $result->Averbado->DadosSeguro->NumeroAverbacao;
                $da[protocolo] = $result->Averbado->Protocolo;
                $da[tipo] = $result->TpDoc;
                $da[ambiente]  = $ambiente;
                $da[integradora] = $integradora;
            }
            if($producao){
                $ambiente = "PRODUCAO";
                $url = $dados[wsProducao];
                $token = $dados[tokenProducao];
                $integradora = $dados[integradora];
                $usuario = $dados[usuarioProducao];
                $senha = $dados[senhaProducao];
                
                $client = new SoapClient($url);
                $result = $client->averbaCTe('ws', 'zappe0321', '11376420', $xmlData );
                
                foreach($result->Averbado->DadosSeguro as $y){
                    $da[response] = $y->NumeroAverbacao;
                    $da[averbacao] = $y->NumeroAverbacao;
                    break;
                }
                
                $da[protocolo] = $result->Averbado->Protocolo;
                $da[tipo] = $result->TpDoc;
                $da[ambiente]  = $ambiente;
                $da[integradora] = $integradora;
            }
        }
        
        if($codigoSeguradora != 2){
            $dados = buscaDadosWsAverbacao(2);
            if(!$producao){
                $ambiente = "HOMOLOGACAO";
                $url = $dados[wsHomologacao];
                $token = $dados[tokenHomologacao];
                $integradora = $dados[integradora];
                $usuario = $dados[usuarioHomologacao];
                $senha = $dados[senhaHomologacao];
                
                $client = new SoapClient($url);
                $result = $client->averbaCTe($usuario, $senha, $token, $xmlData );
                
                $da[response] = $result->Averbado->DadosSeguro->NumeroAverbacao;            
                $da[averbacao] = $result->Averbado->DadosSeguro->NumeroAverbacao;
                $da[protocolo] = $result->Averbado->Protocolo;
                $da[tipo] = $result->TpDoc;
                $da[ambiente]  = $ambiente;
                $da[integradora] = $integradora;
            }
            if($producao){
                $ambiente = "PRODUCAO";
                $url = $dados[wsProducao];
                $token = $dados[tokenProducao];
                $integradora = $dados[integradora];
                $usuario = $dados[usuarioProducao];
                $senha = $dados[senhaProducao];
                
                $client = new SoapClient($url);
                $result = $client->averbaCTe('ws', 'zappe0321', '11376420', $xmlData );
                                
                $da[response] = $result->Averbado->DadosSeguro->NumeroAverbacao;
                $da[averbacao] = $result->Averbado->DadosSeguro->NumeroAverbacao;
                $da[protocolo] = $result->Averbado->Protocolo;
                $da[tipo] = $result->TpDoc;
                $da[ambiente]  = $ambiente;
                $da[integradora] = $integradora;
            }
        }
        
    return $da;
}
    
?>