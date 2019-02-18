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
        <title>BID - A receber semanal</title>

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
            include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptDB2.php';
            include $_SERVER['DOCUMENT_ROOT'] . '/old/funcoes.php';

            /*             * ******* Variáveis ******** */
            date_default_timezone_set('America/sao_paulo');
            $wantedWeek = date('W');
            $ano = date('Y');

            // Verifica se foi selecionada alguma semana e alimenta as variáveis de datas
            if (isset($_POST['selecao_semana']) && strlen($_POST['selecao_semana']) != 0){
                $selecao = explode(",", $_POST['selecao_semana']);
                $ano = $selecao[1];
                $semana = $selecao[0];

                $wantedWeek = $semana;
            }

            // Diferença entre a semana selecionada e a atual
            $weekDiff = $wantedWeek - date('W');
            $tsWeek = strtotime("$weekDiff week");
            $dayOfWeek = date('w', $tsWeek);

            // Alimenta array de datas com os valores (dias) da semana
            for ($i = 0; $i < 7; $i++){
                // TimeStamp contendo os dias da semana de domingo a sabado
                $ts = strtotime(($i - $dayOfWeek) . " days", $tsWeek);

                $diasSemana[$i] = date('d/m/' . trim($ano), $ts);

                // Totais dos dias usando a função contasReceberRecebimentosPeriodo
                $totalDia[$i] = contasReceberRecebimentosPeriodo($diasSemana[$i], NULL, 'receber');
            }

            $totalSemana = arraySumDB2($totalDia);

            if ($totalSemana == 0) $totalSemana = '-';

            // Prepara query para buscar Razão Social do cliente dentro do looping
            $sqlRazaoSocial = "SELECT
                    CAST(RAZAO_SOCIAL AS VARCHAR(45)), ID_CLIENTE
                FROM HCLIENTE WHERE CNPJ_CPF LIKE '%s%%' FETCH FIRST 1 ROWS ONLY";

            $sqlClientes = "SELECT
                    CAST(C.CNPJ_CPF AS VARCHAR(8)) AS CNPJ, SUM(P.VLR_PARCELA + P.VLR_JUROS - P.VLR_DESC)
                FROM PARCDUP P
                JOIN DUPLIC D ON D.IDDUPLIC = P.IDDUPLIC
                JOIN HCLIENTE C ON C.IDHCLIENTE = D.IDHCLIENTE
                WHERE
                    P.DATA_VENCTO BETWEEN '$diasSemana[0]' AND '$diasSemana[6]'
                    AND D.STATUS <> 'C'
                    AND P.DATA_PAGTO IS NULL
                    AND P.VLR_PARCELA + P.VLR_JUROS - P.VLR_DESC > 0
                GROUP BY
                    CAST(C.CNPJ_CPF AS VARCHAR(8))
                ORDER BY
                    SUM(P.VLR_PARCELA + P.VLR_JUROS - P.VLR_DESC) DESC";

            $listaClientes = getConsultaSQL($sqlClientes);

            foreach ($listaClientes as $key => $cliente){
                // Busca Razão Social
                $dadosCliente = getConsultaSQL(sprintf($sqlRazaoSocial, $cliente[0]));

                foreach ($dadosCliente as $key => $value){
                    $razaoSocial = htmlentities($value[0]);
                    $idCliente = $value[1];
                }

                $totalCliente = numberFormatDB2($cliente[1]);

                // Totais por cliente usando a função contasReceberRecebimentosPeriodo
                for ($i = 0; $i < 7; $i++) $dia[$i] = contasReceberRecebimentosPeriodo($diasSemana[$i], NULL, 'receber', $cliente[0]);

                // Monta o link para a listagem de faturas
                $auxHref = "bidonline_fatura.php?cliente=$idCliente"
                    . "&dtIni=" . dateFormat($diasSemana[0])
                    . "&dtFin=" . dateFormat($diasSemana[6]);

                $linhaTabela .= "<tr class='gradeA'>";
                $linhaTabela .=
                    "<td>

                                $razaoSocial

                        </td>";

                // Cria as células para cada dia da semana
                for ($i = 0; $i < 7; $i++) $linhaTabela .= "<td align='right'>$dia[$i]</td>";

                $linhaTabela .= "<td align='right'>$totalCliente</td>";
                $linhaTabela .= "</tr>";
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

            <!-- Sidebar -->
            <?php echo $hoUtils->menuUsuario($_SESSION['idUsuario']); ?>

            <div id="content">
                <div id="contentHeader">
                    <h1><?php echo $_SESSION['nomeEmpresa']; ?></h1>
                </div> <!-- #contentHeader -->

                <div class="container">
                    <div class="grid-24">
                        <form action="#" method="post" name="formReceberSemanal">
                            <div class="field">
                                <label>Selecione a semana:</label>
                                <select name="selecao_semana" id="selecao_semana" onchange="document.formReceberSemanal.submit()">
                                    <option value="0">Selecione</option>
                                    <?php
                                        $semanaAtual = 52;

                                        $sqlAno = mssql_query("SELECT * FROM ANO ORDER BY ANO DESC");

                                        while ($dados = mssql_fetch_array($sqlAno))
                                                for ($i = $semanaAtual; $i >= 1; $i--){
                                                $selected = ($i == $semana && $dados[0] == $ano) ? "selected" : "";

                                                echo "<option value='$i, $dados[0]' $selected>$i - $dados[0]<?option>";
                                            }
                                    ?>
                                </select>
                            </div>
                        </form>
                        <br />
                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">A RECEBER SEMANAL</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>CLIENTE</th>

                                            <?php
                                                // Escreve os dias da semana
                                                for ($i = 0; $i < 7; $i++) echo "<th>$diasSemana[$i]</th>"
                                                    ?>

                                            <th>TOTAL SEMANA</th>
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
                                        <th>EMPRESA</th>

                                        <?php
                                            // Escreve os dias da semana
                                            for ($i = 0; $i < 7; $i++) echo "<th>$diasSemana[$i]</th>"
                                        ?>

                                        <th>TOTAL SEMANA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="odd gradeX">
                                        <td width="20%"><?php echo $_SESSION['nomeEmpresa']; ?></td>
                                        <?php
                                            // Escreve os dias da semana
                                            for ($i = 0; $i < 7; $i++) echo "<td align='right'>$totalDia[$i]</td>"
                                        ?>
                                        <td align='right'><?php echo $totalSemana; ?></td>
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