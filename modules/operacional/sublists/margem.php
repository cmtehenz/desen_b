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
        <title>BID - Documentos x margem de frete</title>

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
            $totalFrete = 0;
            $totalRecibo = 0;
            $totalSugerido = 0;

            if (isset($_POST['ano'])){
                $ano = $_POST['ano'];
            }
            if (isset($_GET['ano'])){
                $ano = $_GET['ano'];
            }
            if (isset($_POST['mes'])){
                $mes_atual = $_POST['mes'];
            }
            if (isset($_GET['mes'])){
                $mes_atual = $_GET['mes'];
            }

            $sqlMes = mssql_query("SELECT * FROM mes");
            while ($dadosMes = mssql_fetch_array($sqlMes)){
                $listaMes = $listaMes . "<option value='$dadosMes[0]'>$dadosMes[1]</option>";
            }

            $sqlAno = mssql_query("SELECT * FROM ano ORDER BY ano DESC");
            while ($dadosAno = mssql_fetch_array($sqlAno)){
                $listaAno = $listaAno . "<option value='$dadosAno[0]'>$dadosAno[0]</option>";
            }

//NOME DO MES SELECIONADO
            $sqlMesSelecionado = mssql_query("SELECT * FROM mes WHERE id_mes=$mes_atual");
            $dadosMesSelecionado = mssql_fetch_array($sqlMesSelecionado);

            /*             * ********************************* */
            $sql_relacao = "SELECT CADBIPE.DATAEMIS, CADBIPE.NUMBIPE, HVEICULO.PLACA,
                       FILIAL.SIGLA_FILIAL, CADBIPE.STAFT, CADBIPE.FRETETOTALSUG,
                       CADBIPE.VALFRETEPAGOTOT, CADBIPE.VALFPESOPAGO, HPROPRIET.TIPO_PESSOA,
                       CASE CADBIPE.STAFT
                            WHEN 'A' THEN
                                CASE HPROPRIET.TIPO_PESSOA
                                    WHEN 'F' THEN VEICEMP.PERCFRETEAGRFIS
                                    WHEN 'J' THEN VEICEMP.PERCFRETEAGRJUR
                                END
                            WHEN 'T' THEN
                                CASE HPROPRIET.TIPO_PESSOA
                                    WHEN 'F' THEN VEICEMP.PERCFRETETERFIS
                                    WHEN 'J' THEN VEICEMP.PERCFRETETERJUR
                                END
                        END,
                        (SELECT USUARIO FROM USUARIO WHERE ID_USUARIO=(SELECT IDUSUARIOLIB FROM LIBMARGEM WHERE ID_LIBMARGEM=CADBIPE.ID_LIBMARGEM)),
                        VEICEMP.ID_VEICULO, FILIAL.ID_FILIAL
                    FROM CADBIPE
                    JOIN HVEICULO ON (HVEICULO.ID_HVEICULO = CADBIPE.ID_HVEICULO)
                    JOIN VEICEMP ON (VEICEMP.ID_VEICULO = HVEICULO.ID_VEICULO)
                    JOIN HPROPRIET ON (HPROPRIET.IDHPROPRIET = HVEICULO.IDHPROPRIET)
                    JOIN FILIAL ON (FILIAL.ID_FILIAL = CADBIPE.ID_FILIAL)
                    WHERE YEAR(CADBIPE.DATAEMIS)=$ano AND MONTH(CADBIPE.DATAEMIS)=$mes_atual
                        AND CADBIPE.STAFT != 'F'
                    ORDER BY CADBIPE.DATAEMIS DESC";
            $db2_relacao = db2_exec($hDbcDB2, $sql_relacao);
            while ($dados_documento = db2_fetch_array($db2_relacao)){

                //VERIFICAR MARGEM POR TABELA DE FILIAL
                $sql_margemFilial = "SELECT * FROM MARFIL WHERE ID_VEICULO=$dados_documento[11] AND ID_FILIAL=$dados_documento[12]";
                $db2_margemFilial = db2_exec($hDbcDB2, $sql_margemFilial);
                $dados_margemFilial = db2_fetch_array($db2_margemFilial);
                if (!empty($dados_margemFilial[2])){
                    $rentabilidade_sugerida = number_format($dados_margemFilial['2'], 0, ',', '.');
                    ;
                }
                elseif (empty($dados_margemFilial[2])){
                    $rentabilidade_sugerida = number_format($dados_documento[9], 0, ',', '.');
                    if ($dados_documento[9] == 0){
                        if ($dados_documento[8] == 'J'){
                            $sql_cadFilial = "SELECT PERCFRETEJUR FROM FILIAL WHERE ID_FILIAL=$dados_documento[12]";
                        }
                        if ($dados_documento[8] == 'F'){
                            $sql_cadFilial = "SELECT PERCFRETEFIS FROM FILIAL WHERE ID_FILIAL=$dados_documento[12]";
                        }

                        $db2_cadFilial = db2_exec($hDbcDB2, $sql_cadFilial);
                        $dados_cadFilial = db2_fetch_array($db2_cadFilial);
                        $rentabilidade_sugerida = number_format($dados_cadFilial[0], 0, ',', '.');
                    }
                }



                $rentabilidade = number_format(($dados_documento[6] / $dados_documento[7]) * 100, 0, ',', '.');


                $linhaTabela = $linhaTabela . "<tr class='gradeA' bgcolor='F3F781'>
                                    <td>$dados_documento[3]-$dados_documento[1]</td>
                                    <td>$dados_documento[0]</td>
                                    <td>$dados_documento[2]</td>
                                    <td>$dados_documento[8]</td>
                                    <td>$dados_documento[4]</td>
                                    <td>$dados_documento[7]</td>
                                    <td>$dados_documento[5]</td>
                                    <td>$dados_documento[6]</td>
                                    <td>$rentabilidade_sugerida</td>
                                    <td>$rentabilidade</td>
                                    <td>$dados_documento[10]</td>
                                </tr>";

                $totalFrete += $dados_documento[7];
                $totalRecibo += $dados_documento[6];
                $totalSugerido += $dados_documento[5];
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

                                <input type="submit" value="IR">
                                Periodo Selecionado: <?php echo $dadosMesSelecionado[1] . '/ ' . $ano; ?>.
                            </div>
                        </form>
                        <br>
                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">RELACAO DOCUMENTOS - MARGEM FRETE</h3>
                            </div>

                            <div class="widget-content">

                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>NUMERO</th>
                                            <th>EMISSAO</th>
                                            <th>PLACA</th>
                                            <th>TP</th>
                                            <th>AFT</th>
                                            <th>TOTAL FRETE</th>
                                            <th>FRETE SUGERIDO</th>
                                            <th>TOTAL RECIBO</th>
                                            <th>MARGEM CADASTRO</th>
                                            <th>RENTABILIDADE(%)</th>
                                            <th>LIBERADO</th>
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

                            <h3>TOTAL DA EMRPESA</h3>

                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th width="45%">EMPRESA</th>
                                        <th>TOTAL FRETE</th>
                                        <th>TOTAL SUGERIDO</th>
                                        <th>TOTAL RECIBO</th>
                                        <th>MARGEM (%)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="odd gradeX">
                                        <td><?php echo $_SESSION['nomeEmpresa']; ?></td>
                                        <td><?php echo number_format($totalFrete, 2, ',', '.'); ?></td>
                                        <td><?php echo number_format($totalSugerido, 2, ',', '.'); ?></td>
                                        <td><?php echo number_format($totalRecibo, 2, ',', '.'); ?></td>
                                        <td><?php echo number_format(($totalRecibo / $totalFrete) * 100, 2, ',', '.'); ?></td>
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
            <div style="float: left;">Versão <?php echo $_SESSION['version']; ?></div> Copyright &copy; <?php echo date('Y'); ?>, Case Electronic Ltda.
        </div>



    </body>
</html>