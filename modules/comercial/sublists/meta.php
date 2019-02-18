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
        <title>BID - Meta x conjunto</title>

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
            if (isset($_GET['conj'])){
                $conj = $_GET['conj'];
                $sql_meta = mssql_query("SELECT TOP 1 * FROM META WHERE descricao='$conj'");
                if (mssql_num_rows($sql_meta) == 0){
                    $valor_meta = 0;
                }
                if (mssql_num_rows($sql_meta) != 0){
                    $dados_meta = mssql_fetch_array($sql_meta);
                    $valor_meta = $dados_meta[1];
                }
            }
            /*             * ********************************* */
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
                        <br>
                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">META POR CONJUNTO</h3>
                            </div>

                            <div class="widget-content">
                                <form action="gravaMeta.php" method="post" enctype="multipart/form-data" name="form1" id="form1">
                                    <input type="hidden" name="conjunto" id="conjunto" value="<?php echo $conj; ?>"></td>
                                    <table width="100%">
                                        <tr>
                                            <td align="center">
                                                <div class="form-group">
                                                    <label class="col-md-2">Valor da Meta para:<br> <?php echo $conj; ?></label>

                                                    <div class="col-md-2">
                                                        <input type="text" class="form-control" placeholder="10000" value="<?php echo $valor_meta; ?>" name="meta" required>
                                                    </div>
                                                    <br>
                                                    <div class="col-md-7 col-md-push-3">
                                                        <button type="submit" class="btn btn-primary">Salvar</button>
                                                        &nbsp;
                                                        <button type="reset" class="btn btn-default">Cancel</button>
                                                    </div>
                                            </td>
                                        </tr>
                                    </table>
                                </form>
                            </div>


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