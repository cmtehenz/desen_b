<?php
$str = new stdClass();

$str = "stdClass Object
(
    [Numero] => 66453
    [Serie] => 10
    [Filial] => 0007
    [CNPJCli] => 75553115000706
    [TpDoc] => 2
    [Averbado] => stdClass Object
        (
            [dhAverbacao] => 2017-11-30T17:00:02
            [Protocolo] => P0MW5E528Q4TMV851857229T85YVRQVYSVRQWR
            [DadosSeguro] => Array
                (
                    [0] => stdClass Object
                        (
                            [NumeroAverbacao] => 0651304197555311500070657010000066453166
                            [CNPJSeguradora] => 03502099000118
                            [NomeSeguradora] => ACE
                            [NumApolice] => EMISSAO
                            [TpMov] => 1
                            [ValorAverbado] => 163824.78
                            [RamoAverbado] => 54
                        )

                    [1] => stdClass Object
                        (
                            [NumeroAverbacao] => 0651304197555311500070657010000066453166
                            [CNPJSeguradora] => 03502099000118
                            [NomeSeguradora] => ACE
                            [ValorAverbado] => 163824.78
                            [RamoAverbado] => 21
                        )

                )

        )

)";

$xml = json_decode(json_encode($str), true);

var_dump($xml);

print_r($xml);
?>