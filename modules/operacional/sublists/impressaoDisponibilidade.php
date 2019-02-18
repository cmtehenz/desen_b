<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();
?>
<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <title>BID - Impressão da disponibilidade de frota</title>

        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <style type="text/css">
            body {
                background: rgb(255,255,255);
                font-family: Verdana, Geneva, sans-serif;
                font-size: 10px;
                font-style: normal;
            }

            @page {
                margin-top: 1cm;
                margin-right: 1cm;
                margin-bottom:2cm;
                margin-left: 2cm;
                size: landscape;
                -webkit-transform: rotate(-90deg); -moz-transform: rotate(-90deg);
                filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=3);
            }

            table { width: 100%; page-break-inside: auto; border: solid 1px #000; border-collapse: collapse; }
            table thead { display: table-header-group; }
            table tbody { display: table-footer-group; }
            table tr {  page-break-inside: avoid; page-break-after: auto; }
            table th { padding: 5px; background-color: #cccccc; border: solid 1px #000; }
            table td { padding: 5px; border: solid 1px #000; }

            table.estado  { margin-bottom: 10px; }
            th.nomeEstado { font-size: 15px; }

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
        </style>
        <script type="text/javascript">
            window.onload = function () {
                window.print();
                setTimeout(function(){ window.close(); }, 1);
            };
        </script>

        <?php
            date_default_timezone_set('America/sao_paulo');
            $dataAtual = new \DateTime(date('Y-m-d H:i:00'));

            $get = filter_input_array(INPUT_GET);

            $groupEstados = array();

            $params = array($dbcSQL->whereParam("m.operLog", $get['idOperLog']));

            $orderBy = ($get['rdGroup'] == "E" ? "m.ufDes, " : "") . ($get['rdOrder'] == "P" ? "m.placa" : "m.dataStatus DESC");

            $listaVeiculos = $dbcSQL->select(
                "SELECT
                    m.placa, m.statusBipe, m.statusOS, m.obs, m.dataStatus,
                    CASE m.statusBipe WHEN 'D' THEN '' ELSE m.ufOri + ' - ' + m.origem END origem, m.ufDes + ' - ' + m.destino destino,
                    m.ufDes, m.motorista, m.ponto
                 FROM monitoramento m", $params, $orderBy);

            foreach ($listaVeiculos as $veiculo){
                $placa      = $veiculo['placa'];
                $statusBipe = $veiculo['statusBipe'];
                $statusOS   = $veiculo['statusOS'];

                $status = $hoUtils->getStatusMonitoramento($statusBipe, $statusOS);

                $ultMov = date_diff($dataAtual, new \DateTime($veiculo['dataStatus']))->format('%ad %Hhrs %im');
                $dtMov  = date('d/m/Y', strtotime($veiculo['dataStatus']));

                $linhaTabela .=
                    "<tr class='gradeA'>
                        <td>$placa</td>
                        <td>$status</td>
                        <td>$veiculo[origem]</td>
                        <td>$veiculo[destino]</td>
                        <td>$dtMov - $ultMov</td>
                        <td>$veiculo[motorista]</td>
                        <td>$veiculo[ponto]</td>
                        <td>" . utf8_encode($veiculo['obs']) . "</td>
                    </tr>";

                /**
                 * Tratativas para o layout agrupado por estado de entrega
                 *
                 * Caso necessite agrupar, jogamoas a variável $linhaTabela para dentro do array de estados e zeramos ela
                 */
                if ($get['rdGroup'] == "E"){
                    // Se encontrou um novo estado na listagem, adicionamos o atual ao array e reinicializamos a variável
                    if ($estado['uf'] != $veiculo['ufDes']){
                        if ($estado) array_push($groupEstados, $estado);

                        $estado = array("uf" => $veiculo['ufDes']);
                    }

                    $estado['linhas'] .= $linhaTabela; $linhaTabela = "";
                }

                $totalVeiculos++;
            }

            if ($estado) array_push($groupEstados, $estado);
        ?>
    </head>
    <body>
        <?php
            if ($get['rdGroup'] != "E"){
                echo
                    "<table>
                        <thead>
                            <tr>
                                <th width='6%'>Placa</th>
                                <th width='9%'>Status</th>
                                <th width='15%'>Origem</th>
                                <th width='15%'>Último destino</th>
                                <th width='10%'>Última mov.</th>
                                <th width='15%'>Motorista</th>
                                <th width='15%'>Ponto</th>
                                <th width='15%'>Observação</th>
                            </tr>
                        </thead>
                        <tbody>$linhaTabela</tbody>
                    </table>";
            }
            else
            {
                /**
                 * Layout diferenciado para o agrupamento por estado de entrega
                 */
                foreach ($groupEstados as $estado){
                    echo
                        "<table class='estado'>
                            <thead>
                                <tr><th class='nomeEstado' colspan='7'>" . $estado['uf'] . " - " . $hoUtils->nomeUf($estado['uf']) . "</th></tr>
                                <tr>
                                    <th width='6%'>Placa</th>
                                    <th width='9%'>Status</th>
                                    <th width='15%'>Origem</th>
                                    <th width='15%'>Último destino</th>
                                    <th width='10%'>Última mov.</th>
                                    <th width='15%'>Motorista</th>
                                    <th width='15%'>Ponto</th>
                                    <th width='15%'>Observação</th>
                                </tr>
                            </thead>
                            <tbody>$estado[linhas]</tbody>
                        </table>";
                }
            }
        ?>
    </body>
</html>