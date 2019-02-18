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

        <title>BID - Carregamentos</title>

        <meta charset="utf-8" />
        <meta name="description" content="" />
        <meta name="author" content="" />
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

            //LISTAR DOCUMETNOS DE CARREGAMENTO
            if (!isset($_GET['motorista']) && !isset($_GET['destino']) && !isset($_GET['origem']) && !isset($_GET['placa']) && isset($_GET['mes']) && isset($_GET['ano']) && isset($_GET['dia'])){
                $data = $_GET['ano'] . '-' . $_GET['mes'] . '-' . $_GET['dia'];
                $sqlDoc = mssql_query("SELECT carregamento.data, origem.descricao, destino.descricao,
                                  carregamento.placa, carregamento.nota, carregamento.peso,
                                  carregamento.observacao, carregamento.valor,
                                  carregamento.valor_sugerido
                                    FROM carregamento
                                    JOIN origem origem ON (origem.id = carregamento.origem)
                                    JOIN cliente destino ON (destino.id = carregamento.destino)
                                    AND data='$data' ");

                while ($dados = mssql_fetch_array($sqlDoc)){
                    $dataTemp = explode(" ", $dados[0]);
                    $MostraData = $dataTemp[1] . '/' . $dataTemp[0] . '/' . $dataTemp[2];

                    $placa = $dados[3];
                    $sql = "SELECT VEICULO.ID_VEICULO, VEICULO.PLACA FROM VEICULO WHERE ID_VEICULO=$placa ORDER BY PLACA";
                    $db2_veic = db2_exec($hDbcDB2, $sql);
                    while ($dadosPlaca = db2_fetch_array($db2_veic)){
                        $mostraPlaca = $dadosPlaca[1];
                    }

                    $linha = $linha . "<tr>
                            <td>$MostraData</td>
                            <td>$dados[1]</td>
                            <td>$dados[2]</td>
                            <td>$mostraPlaca </td>
                            <td>$dados[4]</td>
                            <td>$dados[5]</td>
                            <td>$dados[6]</td>
                            <td>" . number_format($dados[7], 2, ',', '.') . "</td>
                            <td>" . number_format($dados[8], 2, ',', '.') . "</td>
                            <td>" . number_format(($dados[5] / 1000) * $dados[7], 2, ',', '.') . "</td>
                         </tr>";
                }

                //CALCULO DO TOTAL PESO e VIAGENS
                $soma = mssql_query("SELECT SUM(peso), COUNT(*), SUM((peso/1000)*valor) FROM carregamento
                                    WHERE data='$data' ");
                $dados_soma = mssql_fetch_array($soma);
                $totalPeso = $dados_soma[0];
                $totalViagens = $dados_soma[1];
                $totalReais = $dados_soma[2];
            }

            if (isset($_GET['destino']) && isset($_GET['mes']) && isset($_GET['ano']) && isset($_GET['dia'])){
                $destino = $_GET['destino'];
                $data = $_GET['ano'] . '-' . $_GET['mes'] . '-' . $_GET['dia'];
                $sqlDoc = mssql_query("SELECT carregamento.data, origem.descricao, destino.descricao,
                                  carregamento.placa, carregamento.nota, carregamento.peso,
                                  carregamento.observacao, carregamento.valor,
                                  carregamento.valor_sugerido
                                  FROM carregamento
                                    JOIN origem origem ON (origem.id = carregamento.origem)
                                    JOIN cliente destino ON (destino.id = carregamento.destino)
                                    WHERE destino=$destino AND data='$data' ");




                while ($dados = mssql_fetch_array($sqlDoc)){
                    $dataTemp = explode(" ", $dados[0]);
                    $MostraData = $dataTemp[1] . '/' . $dataTemp[0] . '/' . $dataTemp[2];

                    $placa = $dados[3];
                    $sql = "SELECT VEICULO.ID_VEICULO, VEICULO.PLACA FROM VEICULO WHERE ID_VEICULO=$placa ORDER BY PLACA";
                    $db2_veic = db2_exec($hDbcDB2, $sql);
                    while ($dadosPlaca = db2_fetch_array($db2_veic)){
                        $mostraPlaca = $dadosPlaca[1];
                    }

                    $linha = $linha . "<tr>
                            <td>$MostraData</td>
                            <td>$dados[1]</td>
                            <td>$dados[2]</td>
                            <td>$mostraPlaca </td>
                            <td>$dados[4]</td>
                            <td>$dados[5]</td>
                            <td>$dados[6]</td>
                            <td>" . number_format($dados[7], 2, ',', '.') . "</td>
                            <td>" . number_format($dados[8], 2, ',', '.') . "</td>
                            <td>" . number_format(($dados[5] / 1000) * $dados[7], 2, ',', '.') . "</td>
                         </tr>";
                }

                //CALCULO DO TOTAL PESO e VIAGENS
                $soma = mssql_query("SELECT SUM(peso), COUNT(*), SUM((peso/1000)*valor) FROM carregamento
                                    WHERE destino=$destino AND data='$data' ");
                $dados_soma = mssql_fetch_array($soma);
                $totalPeso = $dados_soma[0];
                $totalViagens = $dados_soma[1];
                $totalReais = $dados_soma[2];
            }

            if (isset($_GET['origem']) && isset($_GET['mes']) && isset($_GET['ano']) && isset($_GET['dia'])){
                $origem = $_GET['origem'];
                $data = $_GET['ano'] . '-' . $_GET['mes'] . '-' . $_GET['dia'];
                $sqlDoc = mssql_query("SELECT carregamento.data, origem.descricao, destino.descricao,
                                  carregamento.placa, carregamento.nota, carregamento.peso,
                                  carregamento.observacao, carregamento.valor,
                                  carregamento.valor_sugerido
                                  FROM carregamento
                                    JOIN origem origem ON (origem.id = carregamento.origem)
                                    JOIN cliente destino ON (destino.id = carregamento.destino)
                                    WHERE origem=$origem AND data='$data' ");
                while ($dados = mssql_fetch_array($sqlDoc)){
                    $dataTemp = explode(" ", $dados[0]);
                    $MostraData = $dataTemp[1] . '/' . $dataTemp[0] . '/' . $dataTemp[2];

                    $placa = $dados[3];
                    $sql = "SELECT VEICULO.ID_VEICULO, VEICULO.PLACA FROM VEICULO WHERE ID_VEICULO=$placa ORDER BY PLACA";
                    $db2_veic = db2_exec($hDbcDB2, $sql);
                    while ($dadosPlaca = db2_fetch_array($db2_veic)){
                        $mostraPlaca = $dadosPlaca[1];
                    }


                    $linha = $linha . "<tr>
                            <td>$MostraData</td>
                            <td>$dados[1]</td>
                            <td>$dados[2]</td>
                            <td>$mostraPlaca</td>
                            <td>$dados[4]</td>
                            <td>$dados[5]</td>
                            <td>$dados[6]</td>
                            <td>" . number_format($dados[7], 2, ',', '.') . "</td>
                            <td>" . number_format($dados[8], 2, ',', '.') . "</td>
                            <td>" . number_format(($dados[5] / 1000) * $dados[7], 2, ',', '.') . "</td>
                         </tr>";
                }

                //CALCULO DO TOTAL PESO e VIAGENS
                $sqlDoc = mssql_query("SELECT SUM(peso), COUNT(*), SUM((peso/1000)*valor) FROM carregamento
                                    WHERE origem=$origem AND data='$data' ");
                $dados_soma = mssql_fetch_array($sqlDoc);
                $totalPeso = $dados_soma[0];
                $totalViagens = $dados_soma[1];
                $totalReais = $dados_soma[2];
            }
            if (isset($_GET['placa']) && isset($_GET['mes']) && isset($_GET['ano']) && isset($_GET['dia'])){
                $placa = $_GET['placa'];
                $data = $_GET['ano'] . '-' . $_GET['mes'] . '-' . $_GET['dia'];
                $sqlDoc = mssql_query("SELECT carregamento.data, origem.descricao, destino.descricao,
                                  carregamento.placa, carregamento.nota, carregamento.peso,
                                  carregamento.observacao, carregamento.valor,
                                  carregamento.valor_sugerido
                                  FROM carregamento
                                    JOIN origem origem ON (origem.id = carregamento.origem)
                                    JOIN cliente destino ON (destino.id = carregamento.destino)
                                    WHERE placa=$placa AND data='$data' ");
                while ($dados = mssql_fetch_array($sqlDoc)){
                    $dataTemp = explode(" ", $dados[0]);
                    $MostraData = $dataTemp[1] . '/' . $dataTemp[0] . '/' . $dataTemp[2];

                    $placa = $dados[3];
                    $sql = "SELECT VEICULO.ID_VEICULO, VEICULO.PLACA FROM VEICULO WHERE ID_VEICULO=$placa ORDER BY PLACA";
                    $db2_veic = db2_exec($hDbcDB2, $sql);
                    while ($dadosPlaca = db2_fetch_array($db2_veic)){
                        $mostraPlaca = $dadosPlaca[1];
                    }

                    $linha = $linha . "<tr>
                            <td>$MostraData</td>
                            <td>$dados[1]</td>
                            <td>$dados[2]</td>
                            <td>$mostraPlaca</td>
                            <td>$dados[4]</td>
                            <td>$dados[5]</td>
                            <td>$dados[6]</td>
                            <td>" . number_format($dados[7], 2, ',', '.') . "</td>
                            <td>" . number_format($dados[8], 2, ',', '.') . "</td>
                            <td>" . number_format(($dados[5] / 1000) * $dados[7], 2, ',', '.') . "</td>
                         </tr>";
                }

                //CALCULO DO TOTAL PESO e VIAGENS
                $sqlDoc = mssql_query("SELECT SUM(peso), COUNT(*), SUM((peso/1000)*valor) FROM carregamento
                                    WHERE placa=$placa AND data='$data' ");
                $dados_soma = mssql_fetch_array($sqlDoc);
                $totalPeso = $dados_soma[0];
                $totalViagens = $dados_soma[1];
                $totalReais = $dados_soma[2];
            }

            if (isset($_GET['motorista']) && isset($_GET['mes']) && isset($_GET['ano']) && isset($_GET['dia'])){
                $motorista = $_GET['motorista'];
                $data = $_GET['ano'] . '-' . $_GET['mes'] . '-' . $_GET['dia'];
                $sqlDoc = mssql_query("SELECT carregamento.data, origem.descricao, destino.descricao,
                                  carregamento.placa, carregamento.nota, carregamento.peso,
                                  carregamento.observacao, carregamento.valor,
                                  carregamento.valor_sugerido
                                  FROM carregamento
                                    JOIN origem origem ON (origem.id = carregamento.origem)
                                    JOIN cliente destino ON (destino.id = carregamento.destino)
                                    WHERE motorista=$motorista AND data='$data' ");
                while ($dados = mssql_fetch_array($sqlDoc)){
                    $dataTemp = explode(" ", $dados[0]);
                    $MostraData = $dataTemp[1] . '/' . $dataTemp[0] . '/' . $dataTemp[2];

                    $placa = $dados[3];
                    $sql = "SELECT VEICULO.ID_VEICULO, VEICULO.PLACA FROM VEICULO WHERE ID_VEICULO=$placa ORDER BY PLACA";
                    $db2_veic = db2_exec($hDbcDB2, $sql);
                    while ($dadosPlaca = db2_fetch_array($db2_veic)){
                        $mostraPlaca = $dadosPlaca[1];
                    }

                    $linha = $linha . "<tr>
                            <td>$MostraData</td>
                            <td>$dados[1]</td>
                            <td>$dados[2]</td>
                            <td>$mostraPlaca</td>
                            <td>$dados[4]</td>
                            <td>$dados[5]</td>
                            <td>$dados[6]</td>
                            <td>" . number_format($dados[7], 2, ',', '.') . "</td>
                            <td>" . number_format($dados[8], 2, ',', '.') . "</td>
                            <td>" . number_format(($dados[5] / 1000) * $dados[7], 2, ',', '.') . "</td>
                         </tr>";
                }

                //CALCULO DO TOTAL PESO e VIAGENS
                $sqlDoc = mssql_query("SELECT SUM(peso), COUNT(*), SUM((peso/1000)*valor) FROM carregamento
                                    WHERE placa=$placa AND data='$data' ");
                $dados_soma = mssql_fetch_array($sqlDoc);
                $totalPeso = $dados_soma[0];
                $totalViagens = $dados_soma[1];
                $totalReais = $dados_soma[2];
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

                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Relat&oacute;rio Carregamento</h3>
                            </div>

                            <div class="widget-content">

                                <table class="table table-bordered table-striped ">
                                    <thead>
                                        <tr>
                                            <th>Data</th>
                                            <th>Origem</th>
                                            <th>Destino</th>
                                            <th>Placa</th>
                                            <th>Nota Fiscal</th>
                                            <th>Peso Kg</th>
                                            <th>Observa&ccedil;&abreve;o</th>
                                            <th>Valor</th>
                                            <th>Tabela</th>
                                            <th>Faturamento</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            echo $linha;
                                        ?>
                                    </tbody>
                                </table>
                            </div> <!-- .widget-content -->

                        </div> <!-- .widget -->

                        <div class="box plain">

                            <h3>TOTAL CARREGAMENTO</h3>

                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>EMPRESA</th>
                                        <th>TOTAL VIAGENS</th>
                                        <th>TOTAL PESO</th>
                                        <th>TOTAL R$</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="odd gradeX">
                                        <td><?php echo $dados_empresa[2]; ?></td>
                                        <td align='right'><?php echo $totalViagens; ?></td>
                                        <td align='right'><?php echo number_format($totalPeso / 1000, 0, ',', '.'); ?> T</td>
                                        <td align='right'><?php echo number_format($totalReais, 2, ',', '.'); ?></td>
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
                                    <li><a href="javascript:;">Edit Profile</a></li>
                                    <li><a href="javascript:;">Suspend Account</a></li>
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