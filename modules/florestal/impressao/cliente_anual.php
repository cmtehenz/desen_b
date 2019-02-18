<?php
    namespace Modulos\Florestal\Impressao;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();

    use \Library\Classes\impressao as Relatorio;

    $columns = array("Mês", "Viagens", "Peso (T)", "Média T/V", "Faturamento", "Média F/T", "1ª Quinzena", "2ª Quinzena");

    $impressao = new Relatorio("Análise anual por cliente", $columns);

    /** Seta todas as colunas numéricas (2ª em diante) com alinhamento a direita */
    for ($i = 1; $i < count($columns); $i++) $impressao->setAlignRight($i);
?>
<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <title><?php echo $impressao->titulo(); ?></title>

        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <?php echo $impressao->defaultStyles(); ?>
        <?php echo $impressao->defaultJS(); ?>
    </head>
    <body>
        <?php
            $get = filter_input_array(INPUT_GET);

            $ano     = $get['ano'] ?: date('Y');
            $cliente = $get['cliente'];

            $result = $dbcSQL->analiseAnual($ano, $cliente);

            /**
             * Consolida a tabela, montando um novo array onde hajam todos os meses (mesmo os que não possuem valores).
             * Essa rotina lê os 12 meses e atribui os valores corretos de cada um caso a posição zero do $result bata com o mês
             * atual no loop
             */
            $listaMeses = $dbcSQL->select("SELECT id_mes idMes, descricao FROM mes");

            $analise = array();

            foreach ($listaMeses as $dadosMes){
                $posMes = array();

                /** Verifica se a posição 0 corresponde ao mês lido atualmente e então remove-a do array de valores, jogando a mesma no novo array */
                if ($result[0]['mes'] == $dadosMes['idMes']) $posMes = array_shift($result);

                $posMes['nome'] = $dadosMes['descricao'];

                array_push($analise, $posMes);
            }

            /** Com base no novo array consolidado que possui os 12 meses, formata os valores e realiza cálculos para jogar na função do relatório */
            $dataInfo = array();

            foreach ($analise as $mes){
                $posArray = array();

                $viagens     = $mes['viagens'];
                $peso        = $mes['peso'];
                $faturamento = $mes['faturamento'];

                $posArray['mes']         = $mes['nome'];
                $posArray['viagens']     = $hoUtils->numberFormat($viagens, 0, 0);
                $posArray['peso']        = $hoUtils->numberFormat($peso, 0, 0);
                $posArray['mediatv']     = $hoUtils->numberFormat(($peso / $viagens), 0, 0);
                $posArray['faturamento'] = $hoUtils->numberFormat($faturamento, 0, 0);
                $posArray['mediaft']     = $hoUtils->numberFormat(($faturamento / ($peso / 1000)), 0, 0);
                $posArray['quinzena1']   = $hoUtils->numberFormat($mes['quinzena1']);
                $posArray['quinzena2']   = $hoUtils->numberFormat($mes['quinzena2']);

                array_push($dataInfo, $posArray);

                $totViagens += $viagens;
                $totPeso    += $peso;
                $totFat     += $faturamento;
            }
        ?>

        <?php echo $impressao->header(); ?>

        <table>
            <thead><?php echo $impressao->tableColumns(); ?></thead>
            <tbody><?php echo $impressao->tableBody($dataInfo); ?></tbody>
        </table>

        <?php
            /**
             * Alimenta os arrays para gerar a tabela de totalizadores.
             *
             * - 1 array para as colunas
             * - 1 array multi-dimensional com os valores da(s) linha(s), formatando cada valor adequadamente
             */
            $colTot  = array("Ano", "Viagens", "Peso (T)", "Média T/V", "Faturamento", "Média F/T");
            $dataTot =
                array (
                    array (
                        $ano,
                        $hoUtils->numberFormat($totViagens, 0, 0),
                        $hoUtils->numberFormat($totPeso, 0, 0),
                        $hoUtils->numberFormat($totPeso / $totViagens, 0, 0),
                        $hoUtils->numberFormat($totFat),
                        $hoUtils->numberFormat($totFat / ($totPeso / 1000), 0, 0)
                    )
                );
        ?>

        <table class="totalizador">
            <thead><?php echo $impressao->tableColumns($colTot); ?></thead>
            <tbody><?php echo $impressao->tableBody($dataTot); ?></tbody>
        </table>
    </body>
</html>