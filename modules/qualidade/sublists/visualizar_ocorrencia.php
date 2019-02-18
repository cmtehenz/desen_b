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

            if (isset($_POST['ano'])){
                $ano = $_POST['ano'];
            }
            if (isset($_POST['mes'])){
                $mes_atual = $_POST['mes'];
            }


            $sqlAno = mssql_query("SELECT * FROM ano ORDER BY ano DESC");
            while ($dadosAno = mssql_fetch_array($sqlAno)){
                $listaAno = $listaAno . "<option value='$dadosAno[0]'>$dadosAno[0]</option>";
            }

            $sqlMes = mssql_query("SELECT * FROM mes");
            while ($dadosMes = mssql_fetch_array($sqlMes)){
                $listaMes = $listaMes . "<option value='$dadosMes[0]'>$dadosMes[1]</option>";
            }

//NOME DO MES SELECIONADO
            $sqlMesSelecionado = mssql_query("SELECT * FROM mes WHERE id_mes=$mes_atual");
            $dadosMesSelecionado = mssql_fetch_array($sqlMesSelecionado);
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

<?php
    $sql_empresa = mssql_query("SELECT * FROM SETTINGS WHERE id=1");
    $dados_empresa = mssql_fetch_array($sql_empresa);

    if (isset($_GET['id'])){
        $sql_usuario = "SELECT * FROM ocorrencia_inclusao WHERE id = " . $_GET['id'];
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
            $data = $linha['data_hora'];
            $d = explode(" ", $data);
            $dataMostra = $d[1] . '/' . $d[0] . '/' . $d[2];
            $hora = $d[3];
            $h = explode(":", $hora);
            $horaMostra = $h[0] . ':' . $h[1] . ':' . $h[2];
            $cpf = $linha['cpf'];
            $n_p = $placa;
            $data = $linha['data_hora'];
            $obs = $linha['observacao'];
            $a_m = $acionou;
            $t_o = $ocorrencia;
            $bipe = $linha['bipe'];
        }
    }
?>


            <div id="content">

                <div id="contentHeader">
                    <h1><?php echo $_SESSION['nomeEmpresa']; ?></h1>
                </div> <!-- #contentHeader -->

                <div class="container">
                    <div class="grid-24">
                        <table>
                            <tr>
                                <td width="100%" align="left" style="text-align:center;">
                                    <a href="listar_ocorrencia.php" class="link1">
                                        <h4><- Voltar Rela&ccedil;&abreve;o de Inclus&abreve;o</h4>
                                    </a>
                                </td>
                            </tr>
                        </table>
                        <div class="widget widget-table">
                            <form method="POST" action="#">
                                <div class="widget-header">
                                    <span class="icon-list"></span>
                                    <h3 class="icon chart">Ocorr&ecirc;ncia - Detalhado</h3>
                                </div>
                                <div class="widget-content">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th><br>CPF Motorista:<br></th>
                                                <td width="49%"><br><p>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $cpf; ?></p></td>
                                                <td width="24%">&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <th><br>Placa:<br></th>
                                                <td width="49%"><br><p>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $placa; ?></p></td>
                                                <td width="24%">&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <th><br>Ocorr&ecirc;ncia:<br></th>
                                                <td width="49%"><br><p>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $t_o; ?></p></td>
                                                <td width="24%">&nbsp;</td>
                                            <tr>
                                                <th><br>Data da Ocorr&ecirc;ncia:<br></th>
                                                <td width="49%"><br><p>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $dataMostra; ?></p></td>
                                                <td width="24%">&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <th><br>Hora da Ocorr&ecirc;ncia:<br></th>
                                                <td width="49%"><br><p>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $horaMostra; ?></p></td>
                                                <td width="24%">&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <th><br>Observacao:<br></th>
                                                <td width="49%"><br><p>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $obs ?></p></td>
                                                <td width="24%">&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <th><br>Acionou Monitoramento:<br></th>
                                                <td width="49%"><br><p>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $a_m; ?></p></td>
                                                <td width="24%">&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <th><br>Tipo da Ocorr&ecirc;ncia:<br></th>
                                                <td width="49%"><br><p>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $t_o; ?></p></td>
                                                <td width="24%">&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <th><br>Bipe:<br></th>
                                                <td width="49%"><br><p>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $bipe; ?></p></td>
                                                <td width="24%">&nbsp;</td>
                                            </tr>
                                    </table>
                                </div> <!-- .widget-content -->

                        </div> <!-- .widget -->
                        <table width="100%" border="0">

                        </table>

                    </div> <!-- .widget-content -->

                </div> <!-- .widget -->
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
