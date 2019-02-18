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
        <title>BID - Receita anual por cliente (detalhado)</title>

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
             *   DADOS EMPRESA               *
             * ****************************** */
            $sql_empresa = mssql_query("SELECT TOP 1 * FROM settings WHERE id = 1 ");
            $dados_empresa = mssql_fetch_array($sql_empresa);
            /*             * ****************************** */

            /*             * *******************************
             *   DADOS DO USUARIO LOGADO     *
             * ****************************** */
            $sql_usuario = mssql_query("SELECT TOP 1 * FROM usuario WHERE id_usuario = $_SESSION[idUsuario] ");
            $dados_usuario = mssql_fetch_array($sql_usuario);
            /*             * ****************************** */

            /*             * *******************************
             *   VARIAVEIS                   *
             * ****************************** */
            date_default_timezone_set('America/sao_paulo');
            $wanted_week = date('W');
            $abrev_mes = date('M');
            $nome_mes = date('F');
            $dia = date('d');
            $ano = date('Y');
            $anoAnt = date('Y') - 1;
            $mes_atual = date('m');


            if (isset($_POST['ano'])){
                $ano = $_POST['ano'];
            }

            if (isset($_POST['cliente'])){
                $cliente = $_POST['cliente'];
                $sqlNomeClienteSel = db2_exec($hDbcDB2,
                    "SELECT CAST(RAZAO_SOCIAL AS varchar(30)) FROM CLIENTE WHERE CNPJ_CPF like '$cliente' ORDER BY id_cliente ASC FETCH FIRST 1 ROWS ONLY ");
                $dadosNomeClienteSel = db2_fetch_array($sqlNomeClienteSel);
            }

            /*             * ********************************* */
            $sqlAno = mssql_query("SELECT * FROM ano ORDER BY ano DESC");
            while ($dadosAno = mssql_fetch_array($sqlAno)){
                $listaAno = $listaAno . "<option value='$dadosAno[0]'>$dadosAno[0]</option>";
            }
            /*             * ********************************* */


            /*             * ********************************* */
            /*   LISTA DE CLIENTES      *********
             * ******************************** */
            $sqlCliente = db2_exec($hDbcDB2,
                "SELECT CAST(RAZAO_SOCIAL AS VARCHAR(30)), CNPJ_CPF AS DOC FROM CLIENTE GROUP BY CAST(RAZAO_SOCIAL AS VARCHAR(30)), CNPJ_CPF ORDER BY CAST(RAZAO_SOCIAL AS VARCHAR(30)) ASC");
            while ($dadosCliente = db2_fetch_array($sqlCliente)){
                $listaCliente = $listaCliente . "<option value='$dadosCliente[1]'>$dadosCliente[1] - $dadosCliente[0]</option>";
            }

            /*             * ***************************** */
            /*  CONSULTA MES                 *
              /******************************** */
            $sql_mes = mssql_query("SELECT id_mes, descricao FROM mes");
            while ($dados_mes = mssql_fetch_array($sql_mes)){
                //TITULO DO GRAFICO
                $tituloGraf = $tituloGraf . '<th>' . substr($dados_mes[1], 0, 3) . '</th>';

                $script_faturamentoAnt = "SELECT SUM(FPESO) FROM
    (SELECT SUM(VALTOTFRETE) AS FPESO
    FROM CT
        JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
        JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = CT.IDCTCUSTO)
        JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = CT.IDHCLIENTE)
    WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$anoAnt AND MONTH(CT.DATAEMISSAO)=$dados_mes[0] AND HCLIENTE.CNPJ_CPF like '$cliente'

    UNION
    SELECT SUM(VALFRETE) AS FPESO
    FROM CARRETO
        JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
        JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = FILIAL.IDCTCUSTO)
        JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = CARRETO.ID_CLIENTE)
    WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$anoAnt AND MONTH(CARRETO.DATASAIDA)=$dados_mes[0] AND CLIENTE.CNPJ_CPF like '$cliente'

    UNION
    SELECT SUM(VLR_TOTAL) AS FPESO
    FROM NOTAFAT
        JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTAFAT.ID_FILIAL)
        JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTAFAT.IDCTCUSTO)
        JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = NOTAFAT.ID_CLIENTE)
    WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$anoAnt AND MONTH(NOTAFAT.DATA_EMIS)=$dados_mes[0] AND CLIENTE.CNPJ_CPF like '$cliente'
    AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

    UNION
    SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
    FROM NOTASER
        JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTASER.IDHCLIENTE)
        JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTASER.ID_FILIAL)
        JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
    WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$anoAnt AND MONTH(NOTASER.DATAEMIS)=$dados_mes[0] AND HCLIENTE.CNPJ_CPF like '$cliente'

    UNION
    SELECT SUM(NOTADEB.VALOR) AS FPESO
    FROM NOTADEB
        JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTADEB.ID_FILIAL)
        JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTADEB.IDHCLIENTE)
    WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$anoAnt AND MONTH(NOTADEB.DATAEMISSAO)=$dados_mes[0] AND HCLIENTE.CNPJ_CPF like '$cliente'
    )";
                $db2_faturamentoAnt = db2_exec($hDbcDB2, $script_faturamentoAnt);
                $dados_faturamentoAnt = db2_fetch_array($db2_faturamentoAnt);
                if ($dados_faturamentoAnt[0] == NULL){
                    $dados_faturamentoAnt[0] = 0;
                }
                $realizadoAnt = number_format(str_replace(',', '.', $dados_faturamentoAnt[0]), 0, ',', '');

                $graf_realizadoAnt = $graf_realizadoAnt . '<td>' . $realizadoAnt . '</td>';

                $script_faturamento = "SELECT SUM(FPESO) FROM
    (SELECT SUM(VALTOTFRETE) AS FPESO
    FROM CT
        JOIN FILIAL ON (FILIAL.ID_FILIAL = CT.ID_FILIAL)
        JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = CT.IDCTCUSTO)
        JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = CT.IDHCLIENTE)
    WHERE CT.STATUSCT <> 'C' AND YEAR(CT.DATAEMISSAO)=$ano AND MONTH(CT.DATAEMISSAO)=$dados_mes[0] AND HCLIENTE.CNPJ_CPF like '$cliente'

    UNION
    SELECT SUM(VALFRETE) AS FPESO
    FROM CARRETO
        JOIN FILIAL ON (FILIAL.ID_FILIAL = CARRETO.ID_FILIAL)
        JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = FILIAL.IDCTCUSTO)
        JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = CARRETO.ID_CLIENTE)
    WHERE CARRETO.STATUS <> 'C' AND YEAR(CARRETO.DATASAIDA)=$ano AND MONTH(CARRETO.DATASAIDA)=$dados_mes[0] AND CLIENTE.CNPJ_CPF like '$cliente'

    UNION
    SELECT SUM(VLR_TOTAL) AS FPESO
    FROM NOTAFAT
        JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTAFAT.ID_FILIAL)
        JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTAFAT.IDCTCUSTO)
        JOIN CLIENTE ON (CLIENTE.ID_CLIENTE = NOTAFAT.ID_CLIENTE)
    WHERE NOTAFAT.STATUS <> 'C' AND YEAR(NOTAFAT.DATA_EMIS)=$ano AND MONTH(NOTAFAT.DATA_EMIS)=$dados_mes[0] AND CLIENTE.CNPJ_CPF like '$cliente'
    AND (NOTAFAT.CODIGO_CFOP LIKE '5.551' OR NOTAFAT.CODIGO_CFOP LIKE '6.551' OR NOTAFAT.CODIGO_CFOP LIKE '5.102' OR NOTAFAT.CODIGO_CFOP LIKE '6.102')

    UNION
    SELECT SUM(NOTASER.VALTOTSERV) AS FPESO
    FROM NOTASER
        JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTASER.IDHCLIENTE)
        JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTASER.ID_FILIAL)
        JOIN CTCUSTO ON (CTCUSTO.IDCTCUSTO = NOTASER.IDCTCUSTO)
    WHERE NOTASER.STATUS <> 'C' AND YEAR(NOTASER.DATAEMIS)=$ano AND MONTH(NOTASER.DATAEMIS)=$dados_mes[0] AND HCLIENTE.CNPJ_CPF like '$cliente'

    UNION
    SELECT SUM(NOTADEB.VALOR) AS FPESO
    FROM NOTADEB
        JOIN FILIAL ON (FILIAL.ID_FILIAL = NOTADEB.ID_FILIAL)
        JOIN HCLIENTE ON (HCLIENTE.IDHCLIENTE = NOTADEB.IDHCLIENTE)
    WHERE NOTADEB.STATUS <> 'C' AND YEAR(NOTADEB.DATAEMISSAO)=$ano AND MONTH(NOTADEB.DATAEMISSAO)=$dados_mes[0] AND HCLIENTE.CNPJ_CPF like '$cliente'
    )";
                $db2_faturamento = db2_exec($hDbcDB2, $script_faturamento);
                $dados_faturamento = db2_fetch_array($db2_faturamento);
                if ($dados_faturamento[0] == NULL){
                    $dados_faturamento[0] = 0;
                }
                $realizado = number_format(str_replace(',', '.', $dados_faturamento[0]), 0, ',', '');



                /*                 * **************************** */
                //REALIZADO PARA O GRAFICO
                $graf_realizado = $graf_realizado . '<td>' . $realizado . '</td>';

                //LINHA DA TABELA
                $relizadoLinha = number_format($dados_faturamento[0], 0, ',', '.');
                $realizadoAnt = number_format($dados_faturamentoAnt[0], 0, ',', '.');
                $linhaTabela = $linhaTabela .
                    "<tr class='odd gradeX'>
                <td>" . $dados_mes[1] . "</td>
                <td align='right'>$realizadoAnt</td>
                <td align='right'>$relizadoLinha</td>
            </tr>";

                //VALORES TOTAL
                $cumulativoRealizado = $cumulativoRealizado + $dados_faturamento[0];
                $cumulativoOrcamento = $cumulativoOrcamento + $dados_orcamento[0];
                $cumulativoRealizadoAnt = $cumulativoRealizadoAnt + $dados_faturamentoAnt[0];
            }
//LINHA CUMULATIVO
            $cumulativoRealizado = number_format($cumulativoRealizado, 0, ',', '.');
            $cumulativoRealizadoAnt = number_format($cumulativoRealizadoAnt, 0, ',', '.');

            $linhaCumulativo = "<tr class='odd gradeX'>
                <td>" . $ano . "</td>
                <td align='right'>$cumulativoRealizadoAnt</td>
                <td align='right'>$cumulativoRealizado</td>
            </tr>";
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
                                <label>Selecione o CLIENTE:</label>
                                <select id="cliente" name="cliente">
<?php echo $listaCliente; ?>
                                </select>

                                <label>Selecione o ANO:</label>
                                <select id="ano" name="ano">
<?php echo $listaAno; ?>
                                </select>

                                <input type="submit" value="IR">

                                <br>
                                Cliente Selecionado: <?php echo $dadosNomeClienteSel[0]; ?>.
                                <br>
                                Ano : <?php echo $ano; ?>
                            </div>
                        </form>
                        <br>
                        <div class="widget">

                            <div class="widget-header">
                                <span class="icon-chart"></span>
                                <h3 class="icon chart">Receita Bruta Anual</h3>
                            </div>

                            <div class="widget-content">
                                <table class="stats" data-chart-type="line" data-chart-colors="">
                                    <caption><?php echo $ano; ?> Receita Bruta Anual (Milhoes)</caption>
                                    <thead>
                                        <tr>
                                            <td>&nbsp;</td>
<?php echo $tituloGraf; ?>
                                        </tr>

                                    </thead>

                                    <tbody>

                                        <tr>
                                            <th>Realizado</th>
                                            <?php echo $graf_realizado; ?>
                                        </tr>
                                        <tr>
                                            <th>Ano Anterior</th>
<?php echo $graf_realizadoAnt; ?>
                                        </tr>

                                    </tbody>
                                </table>
                            </div> <!-- .widget-content -->

                        </div> <!-- .widget -->

                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Receita Bruta Anual</h3>
                            </div>

                            <div class="widget-content">

                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width='20%'>MES</th>
                                            <th>REALIZADO ANO ANTERIOR</th>
                                            <th>REALIZADO ANO ATUAL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
<?php echo $linhaTabela; ?>
                                    </tbody>
                                </table>


                            </div> <!-- .widget-content -->

                        </div> <!-- .widget -->

                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Cumulativo</h3>
                            </div>

                            <div class="widget-content">

                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width='20%'>ANO</th>
                                            <th>ANO ANTERIOR</th>
                                            <th>REALIZADO</th>
                                        </tr>
                                    </thead>
                                    <tbody>
<?php echo $linhaCumulativo; ?>
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
            Copyright &copy; 2015, CaseElectronic Ltda.
        </div>



    </body>
</html>