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

        <title>BID - Qualidade</title>

        <meta charset="utf-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>

        <script src="http://cdn.jsdelivr.net/webshim/1.12.4/extras/modernizr-custom.js"></script>
        <!-- polyfiller file to detect and load polyfills -->
        <script src="http://cdn.jsdelivr.net/webshim/1.12.4/polyfiller.js"></script>
        <script>
            webshims.setOptions('waitReady', false);
            webshims.setOptions('forms-ext', {types: 'date'});
            webshims.polyfill('forms forms-ext');
        </script>
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


            if (isset($_POST['cpf'])){

                $tipo = $_POST['cpf'];

                $script_usuario = mssql_query("SELECT *
  FROM ocorrencia_inclusao
  WHERE '$tipo'=cpf  ORDER BY data_hora asc
                                ");
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
                        <form action="#" method="POST">
                            <div class="field-group inlineField">

                                <label for="datepicker">Entre com o CPF:</label>

                                <div class="field">
                                    <input type="text" name="cpf" id="cpf" maxlength="12"/>
                                    <input type="submit" value="IR">
                                    <br><br>
                                    <?php
                                        echo'<h3><b>CPF:</b> ' . $tipo . '</h3>';
                                    ?>
                                </div> <!-- .field -->
                                <br>
                            </div>
                        </form>
                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Relat&oacute;rio de Ocorrencia - Ocorrencia por Motorista</h3>
                            </div>

                            <div class="widget-content">

                                <table class="table table-bordered table-striped ">

                                    <thead>
                                        <tr>            <th>ID</th>
                                            <th>DATA E HORA</th>
                                            <th>PLACA</th>
                                            <th>NOME DA OCORRENCIA</th>
                                            <th><p align="right">PONTOS</p></th>
                                    <th>DESCRI&Ccedil;&Abreve;O</p></th>
                                    <th>ACIONOU MONITORAMENTO</th>
                                    <th>BIPE</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                        <?php
                                            while ($linha = mssql_fetch_array($script_usuario)){
                                                $data = $linha['data_hora'];
                                                $d = explode(" ", $data);
                                                $dataMostra = $d[1] . '/' . $d[0] . '/' . $d[2];


                                                $id_tipoOcorrencia = $linha['tipo_ocorrencia'];
                                                $sql = mssql_query("SELECT descricao FROM ocorrencia_tipo WHERE id='$id_tipoOcorrencia'");
                                                while ($dados = mssql_fetch_array($sql)){
                                                    $tipo_ocorrencia = $dados[0];
                                                }
                                                $id_ocorrencia = $linha['ocorrencia'];
                                                $sql = mssql_query("SELECT descricao, pontos FROM ocorrencia WHERE id='$id_ocorrencia'");
                                                while ($dados = mssql_fetch_array($sql)){
                                                    $ocorrencia_id = $dados[0];
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

                                                echo"<tr>
                                    <td width='10%'><p class='texto'>" . $linha['id'] . "</p></td>
                                    <td width='20%'><p class='texto'>" . $dataMostra . "</p></td>
                                    <td width='20%'><p class='texto'>" . $placa . "</p></td>
                                    <td width='30%'><p class='texto'>" . $ocorrencia_id . "</p></td>
                                    <td width='10%'><p class='texto'>" . $pontos . "</p></td>
                                    <td width='30%'><p class='texto'>" . $tipo_ocorrencia . "</p></td>
                                    <td width='30%'><p class='texto'>" . $acionou . "</p></td>
                                    <td width='30%'><p class='texto'>" . $linha['bipe'] . "</p></td>
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
            <div style="float: left;">Versão <?php echo $_SESSION['version']; ?></div> Copyright &copy; <?php echo date('Y'); ?>, Case Electronic Ltda.
        </div>



    </body>
</html>