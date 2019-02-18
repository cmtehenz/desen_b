<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

$hoUtils = new \Library\Classes\Utils();
$dbcSQL = new \Library\Scripts\scriptSQL();

$_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));

?>

<!doctype html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Receita Bonus Motorista</title>

        <meta charset="utf-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
        <script src="<?php echo $hoUtils->getURLDestino("js/sorttable.js"); ?>"></script>
    </head>

    <body>

        <?php
        include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
        include $_SERVER['DOCUMENT_ROOT'] . '/old/funcoes.php';
        include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptSap.php';
        include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptMSSQL.php';

        /*         * ******************************
         *   VARIAVEIS                   *
         * ****************************** */
        date_default_timezone_set('America/sao_paulo');
        $wanted_week = date('W');
        $abrev_mes = date('M');
        $nome_mes = date('F');
        $dia = date('d');
        $ano = date('Y');
        $mes = date('m');
        $imob = 0;

        if (isset($_POST['ano'])) {
            $ano = $_POST['ano'];
        }
        if (isset($_POST['mes'])) {
            $mes = $_POST['mes'];
        }
        if (isset($_POST['conjunto'])) {
            $conjunto = $_POST['conjunto'];
        } else {
            $conjunto = 'todos';
        }

        foreach (listaAno() as $dadosAno) {
            $selected = null;
            if ($ano == $dadosAno[ANO]) {
                $selected = "selected";
            }
            $listaAno = $listaAno . "<option value='$dadosAno[ANO]' $selected>$dadosAno[ANO]</option>";
        }

        foreach (listaMes() as $dadosMes) {
            $selected = null;
            if ($mes == $dadosMes[ID_MES]) {
                $selected = "selected";
            }
            $listaMes = $listaMes . "<option value='$dadosMes[ID_MES]' $selected>$dadosMes[MES]</option>";
        }

        //Todos os conjuntos
        $conjuntos = array(
            "todos"         => "Todos",
            "1-VANDERLEIA"  => "Vanderleia",
            "2-TRUCK"       => "Truck",
            "3-CARRETA"     => "Carreta",
            "4-BI-TREM"     => "Bi-Trem",
            "5-RODOTREM"    => "RodoTrem",
        );

        foreach ($conjuntos as $dadosConjunto => $valor) {
            $selected = null;
            if ($conjunto == $dadosConjunto) {
                $selected = "selected";
            }
            $listaConjuntos = $listaConjuntos . "<option value='$dadosConjunto' $selected>$valor</option>";
        }

        $ultimo_dia = date("t", mktime(0,0,0,$mes,'01',$ano));
        $dtInicio = "$ano-$mes-01";
        $dtFinal = "$ano-$mes-$ultimo_dia";
        $veiculos = 0;
        
        //listaBonusFrota('1/10/2018', '2/10/2018', $conjunto);
        //die();
        foreach (listaBonusFrota($dtInicio, $dtFinal, $conjunto) as $dados) {
            $totalBonus = $dados[BFATURAMENTO] + $dados[BMEDIA] + $dados[BCOMPORTAMENTO];
            $receitaTotal += $dados[FRETEPESO];
            $totalBonusValor += $dados[TOTALBONUS];
            $veiculos ++;
            $linhaTabela = $linhaTabela . "<tr class='gradeA'>
                                    <td>$dados[PLACA]</td>
                                    <td>$dados[CONJUNTO]</td>
                                    <td>$dados[MATRICULA]</td>
                                    <td>$dados[MOTORISTA]</td>
                                    <td>" . number_format($dados[FRETEPESO], 2, ',', '.') . "</td>
                                    <td>" .number_format($dados[MEDIA], 2, ',', '.'). "</td>
                                    <td>$dados[BFATURAMENTO]</td>
                                    <td>$dados[BMEDIA]</td>
                                    <td>$dados[BCOMPORTAMENTO]</td>
                                    <td>$totalBonus</td>
                                    <td>" . number_format($dados[TOTALBONUS], 2, ',', '.') . "</td>
                                </tr>";
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

<?php echo $hoUtils->menuUsuario($_SESSION['idUsuario']); ?>

            <div id="content">

                <div id="contentHeader">
                    <h1><?php echo $_SESSION['nomeEmpresa']; ?></h1>
                </div> <!-- #contentHeader -->

                <div class="container">
                    <div class="grid-24">

                        <form action="#" method="post">
                            <div class="field">
                                <label>Selecione o MES:</label>
                                <select id="mes" name="mes">
<?php echo $listaMes; ?>
                                </select>

                                <label>Selecione o ANO:</label>
                                <select id="ano" name="ano">
                                    <?php echo $listaAno; ?>
                                </select>

                                <label>Conjunto</label>
                                <select id="conjunto" name="conjunto">
                                    <?php echo $listaConjuntos; ?>
                                </select>

                                <input type="submit" value="IR">
                            </div>
                        </form>
                        <br>
                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Totalizador</h3>
                            </div>

                            <div class="widget-content">

                                <table class="table table-bordered table-striped ">
                                    <thead>
                                        <tr>
                                            <th>VEICULOS</th>
                                            <th>TOTAL FRETE PESO R$</th>
                                            <th>TOTAL BONUS R$</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class='gradeA'>
                                            <td><?php echo $veiculos; ?></td>
                                            <td><?php echo number_format($receitaTotal, 2, ',', '.'); ?></td>
                                            <td align="right"><?php echo number_format($totalBonusValor, 2, ',', '.'); ?></td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div> <!-- .widget-content -->

                        </div> <!-- .widget -->
                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Relatório Bonus</h3>
                            </div>

                            <div class="widget-content">

                                <table class="sortable table table-bordered table-striped ">
                                    <thead>
                                        <tr>
                                            <th>PLACA</th>
                                            <th>CONJUNTO</th>
                                            <th>MATRICULA</th>
                                            <th>MOTORISTA</th>
                                            <th>FRETE PESO R$</th>
                                            <th>M.EFETIVA</th>
                                            <th>B Faturamento</th>
                                            <th>B média</th>
                                            <th>B comportamento</th>
                                            <th>B Total</th>
                                            <th>Valor Bonus</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                            <?php
                                    echo $linhaTabela;
                                ?>

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

            <div id="quickNav">
                <ul>
                    <li class="quickNavMail">
                        <a href="#menuAmpersand" class="menu"><span class="icon-book"></span></a>

                        <span class="alert">3</span>

                        <div id="menuAmpersand" class="menu-container quickNavConfirm">
                            <div class="menu-content cf">

                                <div class="qnc qnc_confirm">

                                    <h3>Confirm</h3>

                                    <div class="qnc_item">
                                        <div class="qnc_content">
                                            <span class="qnc_title">Confirm #1</span>
                                            <span class="qnc_preview">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do.</span>
                                            <span class="qnc_time">3 hours ago</span>
                                        </div> <!-- .qnc_content -->

                                        <div class="qnc_actions">
                                            <button class="btn btn-primary btn-small">Accept</button>
                                            <button class="btn btn-quaternary btn-small">Not Now</button>
                                        </div>
                                    </div>

                                    <div class="qnc_item">
                                        <div class="qnc_content">
                                            <span class="qnc_title">Confirm #2</span>
                                            <span class="qnc_preview">Duis aute irure dolor in henderit in voluptate velit esse cillum dolore.</span>
                                            <span class="qnc_time">3 hours ago</span>
                                        </div> <!-- .qnc_content -->

                                        <div class="qnc_actions">
                                            <button class="btn btn-primary btn-small">Accept</button>
                                            <button class="btn btn-quaternary btn-small">Not Now</button>
                                        </div>
                                    </div>

                                    <div class="qnc_item">
                                        <div class="qnc_content">
                                            <span class="qnc_title">Confirm #3</span>
                                            <span class="qnc_preview">Duis aute irure dolor in henderit in voluptate velit esse cillum dolore.</span>
                                            <span class="qnc_time">3 hours ago</span>
                                        </div> <!-- .qnc_content -->

                                        <div class="qnc_actions">
                                            <button class="btn btn-primary btn-small">Accept</button>
                                            <button class="btn btn-quaternary btn-small">Not Now</button>
                                        </div>
                                    </div>

                                    <a href="javascript:;" class="qnc_more">View all Confirmations</a>

                                </div> <!-- .qnc -->
                            </div>
                        </div>
                    </li>
                    <li class="quickNavNotification">
                        <a href="#menuPie" class="menu"><span class="icon-chat"></span></a>

                        <div id="menuPie" class="menu-container">
                            <div class="menu-content cf">

                                <div class="qnc">

                                    <h3>Notifications</h3>

                                    <a href="javascript:;" class="qnc_item">
                                        <div class="qnc_content">
                                            <span class="qnc_title">Notification #1</span>
                                            <span class="qnc_preview">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do.</span>
                                            <span class="qnc_time">3 hours ago</span>
                                        </div> <!-- .qnc_content -->
                                    </a>

                                    <a href="javascript:;" class="qnc_item">
                                        <div class="qnc_content">
                                            <span class="qnc_title">Notification #2</span>
                                            <span class="qnc_preview">Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu.</span>
                                            <span class="qnc_time">3 hours ago</span>
                                        </div> <!-- .qnc_content -->
                                    </a>

                                    <a href="javascript:;" class="qnc_more">View all Confirmations</a>

                                </div> <!-- .qnc -->
                            </div>
                        </div>
                    </li>
                </ul>
            </div> <!-- .quickNav -->


        </div> <!-- #wrapper -->

        <div id="footer">
            <div style="float: left;">Versão <?php echo $_SESSION['version']; ?></div> Copyright &copy; <?php echo date('Y'); ?>, Case Electronic Ltda.
        </div>
    </body>
</html>