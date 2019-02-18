<?php
    namespace Modulos\SemParar;

    require $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();

    $_SESSION['modulo'] = basename(str_replace("sublists", "", dirname($_SERVER['PHP_SELF'])));
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Fatura</title>

        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>

        <script>
            $(document).ready(function(){
                $(".toggle").click(function(e){
                    e.preventDefault();

                    var trClass = "tr.veiculos-" + $(this).attr('id');
                    var iconObj = this;
                    var icon = $(trClass).is(":visible") ? "icon-12-plus" : "icon-12-minus";

                    $(trClass).toggle("normal");

                    $(iconObj).fadeOut('normal', function() { $(iconObj).html("<i class='" + icon + "'></i>").fadeIn('normal'); });
                });
            });
        </script>
    </head>
    <body>
        <?php
            /********* Variáveis *********/
            date_default_timezone_set('America/sao_paulo');
            $numFatura = $_GET['numero'];

            $dadosFatura = $dbcSQL->faturas($numFatura);

            $listaConsulta = $dbcSQL->passagensSemParar(array( $dbcSQL->whereParam("g.idFatura", $dadosFatura['idFatura']) ));

            // Reorganiza a consulta realizada de forma que os resultados fiquem agrupados por placa e ordenados por data
            foreach ($listaConsulta as $linha){
                $key = $linha['placa'];

                // Inicia o array caso não exista, senão o push não funcionará
                if (!array_key_exists($key, $listaVeiculos)) { $listaVeiculos[$key] = array(); $listaVeiculos[$key]['passagens'] = array(); }

                $listaVeiculos[$key]['placa']     = $linha['placa'];
                $listaVeiculos[$key]['totValor'] += $linha['valor'];

                array_push($listaVeiculos[$key]['passagens'],
                    array(
                        'concessionaria' => $linha['concessionaria'], 'praca' => $linha['praca'], 'data' => $linha['data'],
                        'valor' => $linha['valor'], 'tag' => $linha['tag']
                    ));
            }

            $totalQtd = 0;
            $totalVei = 0;

            // Monta a tabela com dropdown nas placas
            foreach ($listaVeiculos as $veiculo){
                $placa = $veiculo['placa'];

                $totalVei++;

                // Monta a div com elemento para hide and show das passagens
                $iconToggle = "<a href='#' class='toggle' id='$placa'><i class='icon-12-plus'></i></a>";

                $linhaTabela .=
                    "<tr class='gradeA'>
                        <td>$iconToggle&nbsp;&nbsp;$placa</td>
                        <td colspan='2' align='right'>R$ " . $hoUtils->numberFormat($veiculo['totValor']) . "</td>
                        <td colspan ='3'>&nbsp;</td>
                    </tr>";

                $count = 0;

                foreach ($veiculo['passagens'] as $passagem){
                    $linhaTabela .=
                        "<tr class='gradeA veiculos-$placa' style='display: none;'>
                            <td>" . ++$count . "</td>
                            <td>$passagem[data]</td>
                            <td align='right'>" . $hoUtils->numberFormat($passagem['valor']) . "</td>
                            <td>$passagem[concessionaria]</td>
                            <td>$passagem[praca]</td>
                            <td>$passagem[tag]</td>
                        </tr>";

                    $totalQtd++;
                    $totalVlr += $passagem['valor'];
                }
            }
        ?>

        <div id="wrapper">
            <div id="header">
                <h1><a href="<?php echo $hoUtils->getURLDestino("dashboard.php"); ?>">BID</a></h1>

                <a href="javascript:;" id="reveal-nav">
                    <span class="reveal-bar"></span>
                    <span class="reveal-bar"></span>
                    <span class="reveal-bar"></span>
                </a>
            </div> <!-- #header -->
            <div id="empLogo"></div>

            <!-- Sidebar -->
            <?php echo $hoUtils->menuUsuario($_SESSION['idUsuario']); ?>

            <div id="content">
                <div id="contentHeader">
                    <h1><?php echo $_SESSION['nomeEmpresa']; ?></h1>
                </div> <!-- #contentHeader -->

                <div class="container">
                    <div class="grid-24 box" style="width: 94.5%;">
                        <h2 style="text-align: center;">Fatura Nº <?php echo $numFatura; ?></h2><br />

                        <div class="grid-4" style="text-align: center;"><h3>Emissão</h3>     <?php echo $dadosFatura['emissao']; ?></div>
                        <div class="grid-5" style="text-align: center;"><h3>Vencimento</h3>  <?php echo $dadosFatura['vencimento']; ?></div>
                        <div class="grid-6" style="text-align: center;"><h2>Valor</h2><h3>R$ <?php echo $hoUtils->numberFormat($dadosFatura['valorTotal']); ?></h3></div>
                        <div class="grid-5" style="text-align: center;"><h3>Passagens</h3>   <?php echo $totalQtd; ?></div>
                        <div class="grid-4" style="text-align: center;"><h3>Veículos</h3>    <?php echo $totalVei; ?></div>
                    </div>

                    <div class="grid-24">
                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Passagens por veículo</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width="8%">Placa</th>
                                            <th width="15%">Data</th>
                                            <th width="12%">Valor</th>
                                            <th width="30%">Concessionária</th>
                                            <th width="30%">Praça</th>
                                            <th width="5%">TAG</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php echo $linhaTabela; ?>
                                    </tbody>
                                </table>
                            </div> <!-- .widget-content -->
                        </div> <!-- .widget -->
                    </div> <!-- .grid -->
                </div> <!-- .container -->
            </div> <!-- #content -->

            <div id="topNav">
                <ul>
                    <li>
                        <a href="#menuProfile" class="menu"><?php echo $_SESSION['nomeUsuario']; ?></a>
                        <div id="menuProfile" class="menu-container menu-dropdown">
                            <div class="menu-content">
                                <ul class="">
                                    <li><a href="javascript:;">Editar perfil</a></li>
                                    <li><a href="javascript:;">Suspender conta</a></li>
                                </ul>
                            </div>
                        </div>
                    </li>
                    <li><a href="<?php echo $hoUtils->getURLDestino("logout.php"); ?>">Sair</a></li>
                </ul>
            </div> <!-- #topNav -->
        </div> <!-- #wrapper -->

        <div id="footer">
            <div style="float: left;">Versão <?php echo $_SESSION['version']; ?></div> Copyright &copy; <?php echo date('Y'); ?>, Case Electronic Ltda.
        </div> <!-- #footer -->
    </body>
</html>