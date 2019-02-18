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
        <title>BID - Documentos não faturados</title>

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
            include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptDB2.php';
            include $_SERVER['DOCUMENT_ROOT'] . '/old/funcoes.php';

            /*             * ******* Variáveis ******** */
            date_default_timezone_set('America/sao_paulo');
            $ano = date('Y');

            if (isset($_GET['ano'])) $ano = $_GET['ano'];
            if (isset($_GET['mes'])) $mes = $_GET['mes'];

            if (isset($_GET['cliente'])) $cliente = $_GET['cliente'];

            if (isset($_GET['status'])) $status = $_GET['status'];

            $tituloPagina = "RELAÇÃO DE DOCUMENTOS " . (($status != 'faturado') ? "NÃO" : "") . " FATURADOS";

            // Totais do cliente
            $filtroAdicional = " AND C.CNPJ_CPF LIKE '$cliente%' ";

            $totalCTe = contasReceberCtFaturado($ano, $mes, $status, false, $filtroAdicional);
            $totalFCR = contasReceberCarretoFaturado($ano, $mes, $status, false, $filtroAdicional);
            $totalNFV = contasReceberNFVendaFaturado($ano, $mes, $status, false, $filtroAdicional);
            $totalNFS = contasReceberNFServFaturado($ano, $mes, $status, false, $filtroAdicional);
            $totalNFD = contasReceberNFDebFaturado($ano, $mes, $status, false, $filtroAdicional);

            $totalCTe = str_replace(',', '.', $totalCTe);
            $totalFCR = str_replace(',', '.', $totalFCR);
            $totalNFV = str_replace(',', '.', $totalNFV);
            $totalNFS = str_replace(',', '.', $totalNFS);
            $totalNFD = str_replace(',', '.', $totalNFD);

            // Relação de documentos
            $listaDocumentos = contasReceberListarDocNaoFaturado($ano, $mes, $status, $filtroAdicional);

            foreach ($listaDocumentos as $key => $documento){
                // Prenche datas de comprovante caso seja CT-e
                $entregaMerc = NULL;
                $entregaComp = NULL;
                $baixaComp = NULL;

                if ($documento[0] == 'CT-e'){
                    // Tipo de conhecimento - Verifica se é Complementar ou Normal
                    if ($documento[5] == 'C'){
                        $sqlBaixado = "SELECT 1
                            FROM NFTRCOMP
                            WHERE ID_CTRCCOMP = $documento[4] AND IDUSUARIOBX IS NULL
                            FETCH FIRST 1 ROWS ONLY";

                        $sqlEntrega = "SELECT DATAENT, DATABX, '-'
                            FROM NFTRCOMP
                            WHERE ID_CTRCCOMP = $documento[4] AND IDUSUARIOBX IS NOT NULL
                            FETCH FIRST 1 ROWS ONLY";
                    }
                    else{
                        $sqlBaixado = "SELECT 1
                            FROM NFTRANSP
                            WHERE ID_CT = $documento[4] AND DATABX IS NULL
                            FETCH FIRST 1 ROWS ONLY";

                        $sqlEntrega = "SELECT DATAENT, DATABX, DATAENTCOMP
                            FROM NFTRANSP
                            WHERE ID_CT = $documento[4] AND DATABX IS NOT NULL
                            ORDER BY DATABX DESC FETCH FIRST 1 ROWS ONLY";
                    }

                    // Verifica se existe alguma nota não baixada. Se sim, não processa as datas
                    $baixado = getConsultaSQLSimples($sqlBaixado);

                    if ($baixado == NULL){
                        foreach (getConsultaSQL($sqlEntrega) as $key => $result){
                            $entregaMerc = $result[0];
                            $entregaComp = $result[1];
                            $baixaComp = $result[2];
                        }
                    }
                }

                $linhaTabela .=
                    "<tr class='gradeA'>
                        <td>$documento[0]</td>
                        <td>$documento[1]</td>
                        <td align='right'>" . numberFormatDB2($documento[2]) . "</td>
                        <td>$documento[3]</td>
                        <td>" . dateDB2($documento[6]) . "</td>
                        <td>" . dateDB2($entregaMerc) . "</td>
                        <td>" . dateDB2($entregaComp) . "</td>
                        <td>" . dateDB2($baixaComp) . "</td>
                    </tr>";

                $razaoSocial = $documento[3];
            }

            $totalGeral += $totalCTe + $totalFCR + $totalNFV + $totalNFS + $totalNFD;
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
                    <div class="grid-24">
                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart"><?php echo $tituloPagina; ?></h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width="8%">TIPO</th>
                                            <th width="8%">DOCUMENTO</th>
                                            <th width="8%">VALOR</th>
                                            <th>CLIENTE</th>
                                            <th width="8%">EMISSÃO</th>
                                            <th width="12%">ENTREGA MERCAD.</th>
                                            <th width="12%">ENTREGA COMPROV.</th>
                                            <th width="12%">BAIXA COMPROV.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php echo $linhaTabela; ?>
                                    </tbody>
                                </table>
                            </div> <!-- .widget-content -->
                        </div> <!-- .widget -->

                        <div class="box plain">
                            <h3>TOTAL DA EMRPESA</h3>
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>CLIENTE</th>
                                        <th width="10%">CT-e</th>
                                        <th width="10%">Carreto</th>
                                        <th width="10%">NF Venda</th>
                                        <th width="10%">NF Serviço</th>
                                        <th width="10%">NF Débito</th>
                                        <th width="10%">TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="odd gradeX">
                                        <td><?php echo $razaoSocial; ?></td>
                                        <td align='right'><?php echo numberFormatDB2($totalCTe); ?></td>
                                        <td align='right'><?php echo numberFormatDB2($totalFCR); ?></td>
                                        <td align='right'><?php echo numberFormatDB2($totalNFV); ?></td>
                                        <td align='right'><?php echo numberFormatDB2($totalNFS); ?></td>
                                        <td align='right'><?php echo numberFormatDB2($totalNFD); ?></td>
                                        <td align='right'><?php echo numberFormatDB2($totalGeral); ?></td>
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
                                        </div> <!-- .qnc_actions -->
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
                                        </div> <!-- .qnc_actions -->
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
                                        </div> <!-- .qnc_actions -->
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
        </div> <!-- #footer -->


    </body>
</html>