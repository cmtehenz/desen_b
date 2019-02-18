<?php
//$homologacao = TRUE;
$homologacao = FALSE;

if($homologacao){
    $url = "http://homologacao.qct.com.br:9290/webservice/averba/wsdl";
    $token = "75553115000102QRC171231.TESTE";
}
if(!$homologacao){
    $url = "http://brd.qct.com.br:9280/webservice/averba/wsdl";
    $token = "75553115000102BRD180531.MNNCK6N3BTFCQFJZ";
}
$xml = file_get_contents('http://bid.zappellini.com.br/ctesap/sao%20jose%20dos%20pinhais/XML/XML0186471800019915-12-20172475.xml');


$client = new SoapClient($url);
$retono = $client->AverbaXML($token, 'CTE.XML', $xml);
echo "<br>";
echo $retono;
echo "<br><br>";

$retono = simplexml_load_string($retono);
foreach ($retono->Response as $dados){
    echo $dados->Doc->Averbacao;
    echo "<br>";
    echo $dados->Doc->Protocolo;
    echo "<br>";
    echo $dados->Doc->Chave;
    echo "<br>";
    echo $dados->Doc->Tipo;
    echo "<br>";
    echo $dados->Doc["Arquivo"];
}
 
 
/*
$xml = file_get_contents('http://http://bid_d.zappellini.com.br/Ctesap/Sao%20Jose%20dos%20Pinhais/XML/XML2009554500013615-12-20172471.xml');

$client = new SoapClient('http://webserver.averba.com.br/20/index.soap?wsdl');
$function = 'averbaCTe';
$arguments= array('averbaCTe' => array(
                ));
$result = $client->averbaCTe('ws', 'zappe0321', '11376420', $xml );

foreach($result->Averbado->DadosSeguro as $y){
    echo "Imprime for: ";
    echo $y->NumeroAverbacao;
    echo "fim";
    echo "<br><br>";
}

echo $result->Numero;
echo "<br>";
echo $resultDados->NumeroAverbacao;
echo "<br>";
echo $result->Averbado->Protocolo;

echo "<br><br><br>";

echo "<br><br><br><br>";
var_dump($result->Averbado->DadosSeguro);

echo 'Response: ';
print_r($result);
*/

?>


