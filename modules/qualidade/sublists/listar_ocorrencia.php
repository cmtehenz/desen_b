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
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
    <head>

        <title>BID - Qualidade</title>

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
            /*             * ********************************* */

            $sql_usuario = "SELECT * FROM ocorrencia_inclusao ORDER BY data_hora ASC";
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

            <div id="empLogo">
                <form>

                </form>
            </div>

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
                                <h3 class="icon chart">Relação de Ocorrencias</h3>
                            </div>

                            <div class="widget-content">

                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>DETALHES</th>
                                            <th>DATA E HORA</th>
                                            <th>CPF MOTORISTA</th>
                                            <th>PLACA</th>
                                            <th>NOME DA OCORRENCIA</th>
                                            <th><p align="right">PONTOS</p></th>
                                    <th>ACIONOU MONITORAMENTO</th>
                                    <th>TIPO DA OCORRENCIA</th>
                                    <th>BIPE</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            $script_usuario = mssql_query($sql_usuario);
                                            while ($linha = mssql_fetch_array($script_usuario)){
                                                $id_tipoOcorrencia = $linha['tipo_ocorrencia'];
                                                $sql = mssql_query("SELECT descricao FROM ocorrencia_tipo WHERE id='$id_tipoOcorrencia'");
                                                while ($dados = mssql_fetch_array($sql)){
                                                    $tipo_ocorrencia = $dados[0];
                                                }
                                                $id_ocorrencia = $linha['ocorrencia'];
                                                $sql = mssql_query("SELECT descricao, pontos FROM ocorrencia WHERE id='$id_ocorrencia'");
                                                while ($dados = mssql_fetch_array($sql)){
                                                    $ocorrencia = $dados[0];
                                                    $pontos = $dados[1];
                                                }
                                                $id_placa = $linha['placa'];
                                                $sql = "SELECT VEICULO.PLACA FROM VEICULO WHERE ID_VEICULO='$id_placa'";
                                                $db2_veic = db2_exec($hDbcDB2, $sql);
                                                while ($dados = db2_fetch_array($db2_veic)){
                                                    $placa = $dados[0];
                                                }
                                                if ($linha['acionou_monitoramento'] == 0){
                                                    $acionou = 'Sim';
                                                }
                                                if ($linha['acionou_monitoramento'] == 1){
                                                    $acionou = 'Nao';
                                                }

                                                echo"<tr class='odd gradeX'>
            <td align='left'><p><a class='link1' href='visualizar_ocorrencia.php?id=" . $linha['id'] . "'> Visualizar </a></p></td>
            <td align='left'>" . $linha['data_hora'] . "</p></td>
            <td align='left'>" . $linha['cpf'] . "</p></td>
            <td align='left'>" . $placa . "</p></td>
            <td align='left'>" . $ocorrencia . "</p></td>
            <td align='right'>" . $pontos . "</p></td>
            <td align='left'>" . $acionou . "</p></td>
            <td align='left'>" . $tipo_ocorrencia . "</p></td>
            <td align='right'>" . $linha['bipe'] . "</p></td>
            </tr>";
                                            }
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
            Copyright &copy; <?php echo date('Y'); ?>, Case Electronic Ltda.
        </div>



    </body>
</html>