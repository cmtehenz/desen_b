<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL = new \Library\Scripts\scriptSQL();

    $_SESSION['modulo'] = basename(str_replace("sublists", "", dirname($_SERVER['PHP_SELF'])));
?>
<!doctype html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Receita frota</title>

        <meta charset="utf-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
    </head>

    <body>
        <?php
            include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
            include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_db2_bino.php';
            include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptdb2.php';
            include $_SERVER['DOCUMENT_ROOT'] . '/old/funcoes.php';

            /*             * *******************************
             *   VARIAVEIS                   *
             * ****************************** */
            date_default_timezone_set('America/sao_paulo');
            $wanted_week = date('W');
            $abrev_mes = date('M');
            $nome_mes = date('F');
            $dia = date('d');
            $ano = date('Y');
            $mes_atual = date('m');
            $contrato = "F";
            $qtdePlacas = 0;
            $imob = 0;

            if (isset($_GET['contrato'])){
                $contrato = $_GET['contrato'];
            }

            if (isset($_GET['mes'])){
                $mes_atual = $_GET['mes'];
            }

            if (isset($_GET['ano'])){
                $ano = $_GET['ano'];
            }

            if (isset($_POST['imob'])){
                $imob = $_POST['imob'];
            }

//NOME DO MES SELECIONADO
            $sqlMesSelecionado = mssql_query("SELECT * FROM mes WHERE id_mes=$mes_atual");
            $dadosMesSelecionado = mssql_fetch_array($sqlMesSelecionado);
            /*             * ********************************* */

            /*             * ************************************************
              //BUSCA RECEITA TOTAL FROTA                       *
              /************************************************* */
            $receitaFrotaTotal = faturamentoAnoMesAFTO($ano, $mes_atual, 'F', $imob);
            $fretePesoTotal = fretePesoAnoMesAFTO($ano, $mes_atual, 'F', $imob);
//FIM CALCULO TOTAL FROTA

            /*             * ************************************************
              //LISTA PLACAS FATURANDO NO MES                   *
              /************************************************* */
            $listaPlacas = listaPlacasFaturando($ano, $mes_atual, 'F', $imob);
// FIM LISTA PLACAS

            foreach ($listaPlacas as $valor){
                $kml = 0;
                //QUANTIDADE DE PLACAS
                $qtdePlacas++;
                //SELECAO DO CONJUNTO CADASTRADO NO MOMENTO.
                $sqlConjunto = "SELECT CONJUNTO.NAME
                        FROM HVEICULO
                        JOIN CONJUNTO ON (CONJUNTO.CODECONJ = HVEICULO.CODECONJ)
                        WHERE PLACA = '$valor[PL]' ORDER BY ID_HVEICULO DESC FETCH FIRST 1 ROWS ONLY ";
                $db2Conjunto = db2_exec($hDbcDB2, $sqlConjunto);
                $dadosConjunto = db2_fetch_array($db2Conjunto);
                $sql_meta = mssql_query("SELECT *
                                FROM meta
                                WHERE meta_descricao='$dadosConjunto[0]'  ");

                $dados_meta = mssql_fetch_array($sql_meta);

                $porc_meta = number_format(($valor[FRETEP] / $dados_meta[1]) * 100, 0, ',', '.');

                $dados_meta[1] = number_format($dados_meta[1], 2, ',', '.');
                if (mssql_num_rows($sql_meta) == 0){
                    $dadosmeta[1] = '-';
                }

                $linhaTabela = $linhaTabela . "<tr class='gradeA'>
                                    <td><a href='analise_placa.php?placa=$valor[PL]&id=$valor[ID]'>$valor[PL]</a></td>
                                    <td>$valor[MODE]</td>
                                    <td>$dadosConjunto[0]</td>
                                    <td align='right'><a href='meta.php?conj=$dados_meta[0]'>$dados_meta[1]</a></td>
                                    <td align='right'><a href='documentos.php?placa=$valor[PL]&mes=$mes_atual&ano=$ano'>" . number_format($valor[FPESO],
                        2, ',', '.') . "</a></td>
                                    <td align='right'><a href='documentos.php?placa=$valor[PL]&mes=$mes_atual&ano=$ano'>" . number_format($valor[FRETEP],
                        2, ',', '.') . "</a></td>
                                    <td align='right'>$porc_meta%</td>
                                    <td>" . kmRodadoVeiculo($ano, $mes_atual, $valor[ID]) . "</td>
                                    <td><a href='abastecimento.php?placa=$valor[PL]&mes=$mes_atual&ano=$ano'>" . number_format(mediaVeiculo($ano,
                            $mes_atual, $valor[ID]), 2, ',', '.') . "</a></td>
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
                        <form action="#" method="post" name="form1">
                            <div class="field">
                                Calcular venda de imobilizado.
                                <input type="checkbox" name="imob" value="1" <?php if ($imob) echo "checked"; ?> onchange="document.form1.submit()">
                            </div>
                        </form>
                        <br>
                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Faturamento Placa Frota</h3>
                            </div>

                            <div class="widget-content">

                                <table class="table table-bordered table-striped ">
                                    <thead>
                                        <tr>
                                            <th>PLACA</th>
                                            <th>MODELO</th>
                                            <th>CONJUNTO</th>
                                            <th>META</th>
                                            <th>F TOTAL</th>
                                            <th>F PESO</th>
                                            <th>% META</th>
                                            <th>KM RODADO </th>
                                            <th>KM/L </th>
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

                        <div class="box plain">

                            <h3>TOTAL FROTA</h3>

                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>CONTRATO</th>
                                        <th>QTDE PLACAS</th>
                                        <th>FRETE TOTAL(R$)</th>
                                        <th>FRETE PESO(R$)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="odd gradeX">
                                        <td>Frota</td>
                                        <td align='right'><?php echo $qtdePlacas; ?></td>
                                        <td align='right'><?php echo number_format($receitaFrotaTotal, 2, ',', '.'); ?></td>
                                        <td align='right'><?php echo number_format($fretePesoTotal, 2, ',', '.'); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div> <!-- .box -->

                        <div class="box plain">

                            <h3>Observacao:</h3>

                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Observacao</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="odd gradeX">
                                        <td>CALCULO DA META</td>
                                        <td align='left'>META(%) = FretePeso / Meta</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div> <!-- .box -->

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
            Copyright &copy; 2015, CaseElectronic Ltda.
        </div>



    </body>
</html>