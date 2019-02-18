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
        <title>BID - Custo analítico</title>

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
            if (isset($_POST['datastart']) && isset($_POST['datastop'])){
                $dataStart = $_POST['datastart'];
                $dataStop = $_POST['datastop'];
            }

            $listaContaTotal = "SELECT SUM(LANCTO.VLR_LANCTO)
                FROM LANCTO
                JOIN CTCUSTO ON (CTCUSTO.COD_CTCUSTO = LANCTO.CCUSTO)
                JOIN CTCONTAB ON (CTCONTAB.ID_CTCONTABIL = LANCTO.ID_CTCONTABIL)
                WHERE LANCTO.DAT_LANCTO BETWEEN '$dataStart' AND '$dataStop'
                ";
            $db2ContaTotal = db2_exec($hDbcDB2, $listaContaTotal);
            $dadosContaTotal = db2_fetch_array($db2ContaTotal);

            $listaConta = "SELECT LANCTO.DAT_LANCTO, LANCTO.CCUSTO, CTCUSTO.DESCRICAO, LANCTO.NOM_MASPLA, CTCONTAB.DESCRICAO, LANCTO.VLR_LANCTO, LANCTO.NOM_HISTO, LANCTO.NOM_HISTO1, LANCTO.NOM_HISTO2
                FROM LANCTO
                JOIN CTCUSTO ON (CTCUSTO.COD_CTCUSTO = LANCTO.CCUSTO)
                JOIN CTCONTAB ON (CTCONTAB.ID_CTCONTABIL = LANCTO.ID_CTCONTABIL)
                WHERE LANCTO.DAT_LANCTO BETWEEN '$dataStart' AND '$dataStop'
                ORDER BY LANCTO.DAT_LANCTO";
            $db2Conta = db2_exec($hDbcDB2, $listaConta);

            while ($dadosConta = db2_fetch_array($db2Conta)){
                $dadosConta[2] = htmlentities($dadosConta[2]);
                $dadosConta[4] = htmlentities($dadosConta[4]);
                $dadosConta[6] = htmlentities($dadosConta[6]);
                $dadosConta[7] = htmlentities($dadosConta[7]);
                $dadosConta[8] = htmlentities($dadosConta[8]);
                $linhaTabela = $linhaTabela . "<tr class='gradeA'>
                                    <td>$dadosConta[0]</td>
                                    <td>$dadosConta[1]</td>
                                    <td align='right'>$dadosConta[2]</td>
                                    <td>$dadosConta[3]</td>
                                    <td>$dadosConta[4]</td>
                                    <td>$dadosConta[5]</td>
                                    <td>$dadosConta[6] $dadosConta[7] $dadosConta[8]</td>
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
                        <form action="#" method="POST">
                            <div class="field-group inlineField">
                                <label for="datepicker">Selecione o Periodo:</label>

                                <div class="field">
                                    <input type="date" name="datastart" />
                                    <input type="date" name="datastop" />
                                    <input type="submit" value="IR">
                                </div> <!-- .field -->
                            </div>
                        </form><br>
                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">CUSTO ANALITICO</h3>
                            </div>

                            <div class="widget-content">

                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>DATA</th>
                                            <th>CCUSTO</th>
                                            <th>DESCRICAO</th>
                                            <th>CONTA</th>
                                            <th>DESCRICAO</th>
                                            <th>VALOR</th>
                                            <th>HISTORICO</th>
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
                                        <th>EMPRESA</th>
                                        <th>TOTAL SEMANA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="odd gradeX">
                                        <td width="20%"><?php echo $_SESSION['nomeEmpresa']; ?></td>
                                        <td align='right'><?php echo number_format($dadosContaTotal[0], 2, ',', '.'); ?></td>
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


        <script>
            $(function () {
                $("#datepicker").datepicker();
                $("#datepicker_inline").datepicker();
                $('#colorpickerHolder').ColorPicker({flat: true});
                $('#timepicker').timepicker({
                    showPeriod: true
                    , showNowButton: true
                    , showCloseButton: true
                });

                $('#timepicker_inline_div').timepicker({
                    defaultTime: '9:20'
                });

                $('#colorSelector').ColorPicker({
                    color: '#FF9900',
                    onShow: function (colpkr) {
                        $(colpkr).fadeIn(500);
                        return false;
                    },
                    onHide: function (colpkr) {
                        $(colpkr).fadeOut(500);
                        return false;
                    },
                    onSubmit: function (hsb, hex, rgb, el) {
                        $(el).ColorPickerHide();
                    },
                    onChange: function (hsb, hex, rgb) {
                        $('#colorSelector div').css({'background-color': '#' + hex});
                        $('#colorpickerField1').val('#' + hex);
                    }
                });

                $('#colorpickerField1').live('keyup', function (e) {
                    var val = $(this).val();
                    val = val.replace('#', '');
                    $('#colorSelector div').css({'background-color': '#' + val});
                    $('#colorSelector').ColorPickerSetColor(val);
                });

            });

        </script>

    </body>
</html>