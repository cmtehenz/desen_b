<?php
    namespace Modulos\Florestal\Impressao;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();

    use \Library\Classes\impressao as Relatorio;

    date_default_timezone_set('America/sao_paulo');
    setlocale(LC_ALL, "ptb");

    $impressao = new Relatorio("Resumo geral de carregamentos");
?>
<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <title><?php echo $impressao->titulo(); ?></title>

        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <style type="text/css">
            body {
                background: rgb(255,255,255);
                font-family: Verdana, Geneva, sans-serif;
                font-size: 9px;
                font-style: normal;
            }

            @page {
                margin-top: 1cm;
                margin-right: 1cm;
                margin-bottom:2cm;
                margin-left: 2cm;
                size: portrait;
                -webkit-transform: rotate(-90deg); -moz-transform: rotate(-90deg);
                filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=3);
            }

            @media print {
                body, page[size="A4"] {
                    margin: 1cm;
                    box-shadow: 0;
                }

                table { page-break-after: auto; }
                tr    { page-break-inside: avoid; }
                td    { page-break-inside: auto; }

                thead { display: table-header-group; }
                tbody { display: table-row-group; }
                tfoot { display: table-footer-group; }
            }

            @media screen {
                thead { display: block; }
                tfoot { display: block; }
            }

            .header { border: 3px solid #000; min-height: 30px; margin-bottom: 10px; text-align: center; vertical-align: middle; padding: 10px 0px 10px 0px; }
            .title { font-size: 25px; font-weight: bold; }

            table { width: 100%; page-break-inside: auto; border: solid 1px #000; border-collapse: collapse; }
            table thead { display: table-header-group; }
            table tbody { display: table-footer-group; }
            table tr {  page-break-inside: avoid; page-break-after: auto; }
            table th { padding: 5px; background-color: #cccccc; border: solid 1px #000; }
            table td { padding: 5px; border: solid 1px #000; }
        </style>
        <script type="text/javascript">
            window.onload = function () {
                window.print();
                setTimeout(function(){ window.close(); }, 1);
            };
        </script>

        <?php
            $get = filter_input_array(INPUT_GET);

            $ano = $get['ano'] ?: date('Y');
            $mes = $get['mes'];

            $params = array();

            if ($mes) array_push($params, $dbcSQL->whereParam("MONTH(c.data)", $mes));

            /** Busca os dados sobre os clientes de destino dos carregamentos */
            $analiseClientes = $dbcSQL->analiseClientes($ano, $params);

            /** Escreve a tabela com base no novo array consolidado que possui os 12 meses */
            foreach ($analiseClientes as $cliente){
                $viagens     = $cliente['viagens'];
                $peso        = $cliente['peso'];
                $faturamento = $cliente['faturamento'];
                $quinzena1   = $cliente['quinzena1'];
                $quinzena2   = $cliente['quinzena2'];

                $totViagens += $viagens;
                $totPeso    += $peso;
                $totFat     += $faturamento;

                $linhaClientes .=
                    "<tr>
                        <td>" . substr($cliente['nome'], 0, 35) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($viagens, 0, 0) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($peso   , 0, 0) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat(($peso / $viagens), 0, 0) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($faturamento) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat(($faturamento / ($peso / 1000)), 0, 0) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($quinzena1) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($quinzena2) . "</td>
                    </tr>";
            }

            /** Busca os dados sobre as fazendas de origem dos carregamentos */
            $analiseFazendas = $dbcSQL->analiseFazendas($ano, $params);

            /** Escreve a tabela com base no novo array consolidado que possui os 12 meses */
            foreach ($analiseFazendas as $fazenda){
                $viagens = $fazenda['viagens'];
                $peso    = $fazenda['peso'];

                $linhaFazendas .=
                    "<tr>
                        <td>$fazenda[nome]</td>
                        <td align='right'>" . $hoUtils->numberFormat($viagens, 0, 0) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($peso   , 0, 0) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat(($peso / $viagens), 0, 0) . "</td>
                    </tr>";
            }
            
            /** Busca os dados sobre os itens nos carregamentos */
            $analiseItens = $dbcSQL->analiseItens($ano, $params);
            foreach ($analiseItens as $item){
                $linhaItens .=
                    "<tr class='odd gradeX'>
                        <td>$item[nome]</td>
                        <td align='right'>" . $hoUtils->numberFormat($item['viagens'], 0, 0) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($item['faturamento'], 0, 0) . "</td>
                        <td align='right'>" . $hoUtils->numberFormat($item['vlrMedio']) . "</td>
                    </tr>";
            }
        ?>
    </head>
    <body>
        <div class="header">
            <span class="title">RESUMO GERAL DE CARREGAMENTOS</span>
        </div>
        <!-- Clientes -->
        <table>
            <thead>
                <tr>
                    <th width="40%">Cliente</th>
                    <th>Viagens</th>
                    <th>Peso (T)</th>
                    <th>Média T/V</th>
                    <th>Faturamento</th>
                    <th>Média F/T</th>
                    <th>1ª Quinzena</th>
                    <th>2ª Quinzena</th>
                </tr>
            </thead>
            <tbody><?php echo $linhaClientes; ?></tbody>
        </table>
        <br />

        <!-- Fazendas -->
        <table>
            <thead>
                <tr>
                    <th width="60%">Fazenda</th>
                    <th>Viagens</th>
                    <th>Peso (T)</th>
                    <th>Média T/V</th>
                </tr>
            </thead>
            <tbody><?php echo $linhaFazendas; ?></tbody>
        </table>
        <br />
        
        <!-- Itens -->
        <table>
            <thead>
                <tr>
                    <th width="60%">Item</th>
                    <th>Viagens</th>
                    <th>Faturamento</th>
                    <th>Valor Médio</th>
                </tr>
            </thead>
            <tbody><?php echo $linhaItens; ?></tbody>
        </table>
        <br />

        <!-- Totais -->
        <table>
            <thead>
                <tr>
                    <th width="20%">Total no período</th>
                    <th>Viagens</th>
                    <th>Peso (T)</th>
                    <th>Tonelada / viagem</th>
                    <th>Faturamento</th>
                    <th>Faturamento / tonelada</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo ($mes ? ($hoUtils->monthName($mes) . " / ") : "") . $ano; ?></td>
                    <td align="right"><?php echo $hoUtils->numberFormat($totViagens, 0, 0); ?></td>
                    <td align="right"><?php echo $hoUtils->numberFormat($totPeso, 0, 0); ?></td>
                    <td align="right"><?php echo $hoUtils->numberFormat($totPeso / $totViagens, 0, 0); ?></td>
                    <td align="right"><?php echo $hoUtils->numberFormat($totFat); ?></td>
                    <td align="right"><?php echo $hoUtils->numberFormat($totFat / ($totPeso / 1000), 0, 0); ?></td>
                </tr>
            </tbody>
        </table>
    </body>
</html>