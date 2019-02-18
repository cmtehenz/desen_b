<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));
?>
<!doctype html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Receita por filial</title>

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
            include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptdb2.php';
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
            $imob = 0;

            if (isset($_POST['ano'])){
                $ano = $_POST['ano'];
            }
            if (isset($_POST['mes'])){
                $mes_atual = $_POST['mes'];
            }
            if (isset($_POST['imob'])){
                $imob = $_POST['imob'];
            }

            $sqlAno = mssql_query("SELECT * FROM ano WHERE ano <> $ano ORDER BY ano DESC");
            $listaAno = $listaAno . "<option value='$ano'>$ano</option>";
            while ($dadosAno = mssql_fetch_array($sqlAno)){
                $listaAno = $listaAno . "<option value='$dadosAno[0]'>$dadosAno[0]</option>";
            }

            $sqlMesSelecionado = mssql_query("SELECT * FROM mes WHERE id_mes=$mes_atual");
            $dadosMesSelecionado = mssql_fetch_array($sqlMesSelecionado);

            $sqlMes = mssql_query("SELECT * FROM mes WHERE id_mes <> $mes_atual");
            $listaMes = $listaMes . "<option value='$mes_atual'>$dadosMesSelecionado[1]</option>";
            while ($dadosMes = mssql_fetch_array($sqlMes)){
                $listaMes = $listaMes . "<option value='$dadosMes[0]'>$dadosMes[1]</option>";
            }

            /*             * ********************************* */

            /*             * ***************************** */
            /*  CONSULTA POR FILIAIS        *
              /******************************** */
            $listaFilial = listaFiliaisFaturando($ano, $mes_atual, $imob);
            foreach ($listaFilial as $dados_filiais){

                $nomefilial = htmlentities($dados_filiais['NOMEFILIAL']);
                $receitaFilialGrafico = number_format(str_replace(',', '.', $dados_filiais['FPESO']), 0, '', '');

                //Nomes das filiais para o grafico.
                $titulo = $titulo . "<th>$nomefilial</th>";

                //VALORES REALIZADO GRAFICO
                $graf_realizadoMes = $graf_realizadoMes . "<td>$receitaFilialGrafico</td>";

                //**********************************
                //BUSCA ORCAMENTO DA FILIAL
                //**********************************
                $receitaPrevistoFilial = receitaPrevistoAnoMesIdctcusto($ano, $mes_atual, $dados_filiais['IDCUSTO']);
                //**********************************
                //VALORES ORCAMENTO PARA GRAFICO
                $graf_orcamento = $graf_orcamento . "<td>$receitaPrevistoFilial</td>";

                /*                 * ************************************************
                  //BUSCA RECEITA DA FILIAL E AGREGADO              *
                  /************************************************* */
                //$dados_receitaAgregado[0] = faturamentoAnoMesFilialAFT($ano, $mes_atual, 'A', $imob, $dados_filiais['IDCUSTO']);
                $freteTotalAgregado = freteTotalAnoMesAFTOFilial($ano, $mes_atual, 'A', $imob, $dados_filiais['IDCUSTO']);
                $a_porc = number_format(($freteTotalAgregado * 100) / $dados_filiais['FPESO'], 0, ',', '.');
                //FIM CALCULO FILIAL E AGREGADO

                /*                 * ************************************************
                  //BUSCA RECEITA DA FILIAL E FROTA                 *
                  /************************************************* */
                $freteTotalFrota = freteTotalAnoMesAFTOFilial($ano, $mes_atual, 'F', $imob, $dados_filiais['IDCUSTO']);
                $f_porc = number_format(($freteTotalFrota * 100) / $dados_filiais['FPESO'], 0, ',', '.');
                //$f_porc = number_format(($dados_filiais[2]/$dados_receitaFrota[0]), 0, ',', '.');
                //FIM CALCULO FILIAL E FROTA

                /*                 * ************************************************
                  //BUSCA RECEITA DA FILIAL E TERCEIRO              *
                  /************************************************* */
                $freteTotalTerceiro = freteTotalAnoMesAFTOFilial($ano, $mes_atual, 'T', $imob, $dados_filiais['IDCUSTO']);
                $t_porc = number_format(($freteTotalTerceiro * 100) / $dados_filiais['FPESO'], 0, ',', '.');
                //FIM CALCULO FILIAL E TERCEIRO

                /*                 * ****************************************************
                 *   RECEITA FILIAL OUTROS - SEM PLACA
                 * *************************************************** */
                $freteTotalOutros = faturamentoAnoMesOutrosFilial($ano, $mes_atual, $imob, $dados_filiais['IDCUSTO']);
                $o_porc = number_format(($freteTotalOutros * 100) / $dados_filiais['FPESO'], 0, ',', '.');

                /*                 * ************************************************************* */


                //CALCULO DA PORCENTAGEM PARA META POR FILIAL
                @$porc = number_format(($dados_filiais['FPESO'] * 100 / $receitaPrevistoFilial), 0, ',', '.');


                /*                 * *********************************************** */
                $linhaTabela = $linhaTabela . "<tr class='gradeA'>
                                    <td><a href='sublists/receita_cli_x_fil.php?f=$dados_filiais[IDCUSTO]&mes=$mes_atual&ano=$ano'>$nomefilial</a></td>
                                    <td>$a_porc</td>
                                    <td>$f_porc</td>
                                    <td>$t_porc</td>
                                    <td>$o_porc</td>
                                    <td align='right'>" . number_format($receitaPrevistoFilial, 0, ',', '.') . "</td>
                                    <td align='right'>" . number_format($dados_filiais['FPESO'], 0, ',', '.') . "</td>
                                    <td align='right'>" . number_format(fretePesoAnoMesFilial($ano, $mes_atual, $imob, $dados_filiais['IDCUSTO']), 0, ',', '.') . "</td>
                                    <td align='right'>$porc</td>
                                </tr>";
            }

            /*             * *******************************
             *   CALCULO FATURAMENTO TOTAL   *
             * ****************************** */
            $freteTotalAnoMes = faturamentoAnoMes($ano, $mes_atual, $imob);
            $realizado = number_format(str_replace(',', '.', $freteTotalAnoMes), 0, ',', '.');
            
            $fretePesoAnoMes = faturamentoFretePesoAnoMesSicms($ano, $mes_atual, $imob);
            $realizadoFretePeso = number_format($fretePesoAnoMes, 0, ',', '.');

            /*             * *********************************************************** */
            $orcamentoAnoMes = receitaPrevistoAnoMes($ano, $mes_atual);
            $orcamento_tt = number_format($orcamentoAnoMes, 0, ',', '.');
            /*             * *********************************************************** */

            /*             * ************************************************
              //CALCULO % PREVISTO / REALIZADO                   *
              /************************************************* */
            $porc_total = number_format(($fretePesoAnoMes / $orcamentoAnoMes) * 100, 0, ',', '.');

            /*             * ************************************************
              //BUSCA RECEITA TOTAL AGREGADO                    *
              /************************************************* */
            $freteTotalAgregado = faturamentoAnoMesAFTO($ano, $mes_atual, 'A', $imob);
            $a_porcTotal = number_format(($freteTotalAgregado / $fretePesoAnoMes) * 100, 0, ',', '.');
//FIM CALCULO TOTAL AGREGADO

            /*             * ************************************************
              //BUSCA RECEITA TOTAL FROTA                       *
              /************************************************* */
            $freteTotalFrota = faturamentoAnoMesAFTO($ano, $mes_atual, 'F', $imob);
            $f_porcTotal = number_format(($freteTotalFrota / $fretePesoAnoMes) * 100, 0, ',', '.');

//FIM CALCULO TOTAL FROTA

            /****************************************************
              //BUSCA RECEITA TOTAL TERCEIRO                    *
              /************************************************* */
            $freteTotalTerceiro = faturamentoAnoMesAFTO($ano, $mes_atual, 'T', $imob);
            $t_porcTotal = number_format(($freteTotalTerceiro / $fretePesoAnoMes) * 100, 0, ',', '.');
//FIM CALCULO TOTAL TERCEIRO

            /** ****************************************************
             *   RECEITA TOTAL OUTROS - SEM PLACA
             * *************************************************** */
            $freteTotalOutros = faturamentoAnoMesOutros($ano, $mes_atual, $imob);
            $o_porcTotal = number_format(($freteTotalOutros / $fretePesoAnoMes) * 100, 0, ',', '.');
            /*             * ************************************************************* */


//DIAS PARA O GRAFICO
            $dias_mes = cal_days_in_month(CAL_GREGORIAN, $mes_atual, $ano);
            for ($i = 1; $i <= $dias_mes; $i++){
                $tituloGraf = $tituloGraf . '<th>' . $i . '</th>';
                $dados_faturamentoDiario[0] = faturamentoAnoMesDia($ano, $mes_atual, $i, $imob);
                $realizadoDiario = number_format(str_replace(',', '.', $dados_faturamentoDiario[0]), 0, ',', '');
                /*                 * **************************** */
                //REALIZADO PARA O GRAFICO
                $graf_realizado = $graf_realizado . '<td>' . $realizadoDiario . '</td>';
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

                        <form action="#" method="post" name="form1">
                            <div class="field">
                                <label>Selecione o MES:</label>
                                <select id="mes" name="mes" onchange="document.form1.submit()" >
                                    <?php echo $listaMes; ?>
                                </select>

                                <label>Selecione o ANO:</label>
                                <select id="ano" name="ano" onchange="document.form1.submit()">
                                    <?php echo $listaAno; ?>
                                </select>

                                Calcular venda de imobilizado.
                                <input type="checkbox" name="imob" value="1" <?php if ($imob) echo "checked"; ?> onchange="document.form1.submit()">
                            </div>
                        </form>
                        <br>
                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Faturamento Filiais</h3>
                            </div>

                            <div class="widget-content">

                                <table class="table table-bordered table-striped data-table">
                                    <thead>
                                        <tr>
                                            <th>FILIAL</th>
                                            <th width="12%">AGREGADO(R$)</th>
                                            <th width="12%">FROTA %</th>
                                            <th width="12%">TERCEIRO %</th>
                                            <th width="12%">OUTROS %</th>
                                            <th>PREVISTO(R$)</th>
                                            <th>FRETE TOTAL(R$)</th>
                                            <th>FRETE PESO(R$)</th>
                                            <th width="9%">PORC %</th>
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
                                        <th width="10%">AGREGADO %</th>
                                        <th width="10%">FROTA %</th>
                                        <th width="10%">TERCEIRO %</th>
                                        <th width="10%">OUTROS %</th>
                                        <th>PREVISTO(R$)</th>
                                        <th>FRETE TOTAL(R$)</th>
                                        <th>FRETE PESO(R$)</th>
                                        <th width="7%">PORC %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="odd gradeX">
                                        <td><?php echo $_SESSION['nomeEmpresa']; ?></td>
                                        <td><?php echo $a_porcTotal; ?></td>
                                        <td><?php echo $f_porcTotal; ?></td>
                                        <td><?php echo $t_porcTotal; ?></td>
                                        <td><?php echo $o_porcTotal; ?></td>
                                        <td align='right'><?php echo $orcamento_tt; ?></td>
                                        <td align='right'><?php echo $realizado; ?></td>
                                        <td align='right'><?php echo $realizadoFretePeso; ?></td>
                                        <td align='right'><?php echo $porc_total; ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div> <!-- .box -->


                        <div class="widget widget-tabs">

                            <div class="widget-header">
                                <span class="icon-chart"></span>
                                <h3 class="">Grafico Receita X Previsto Filial</h3>

                                <ul class="tabs right">
                                    <li class="active"><a href="#">Mensal</a></li>
                                </ul>
                            </div>

                            <div id="yearly" class="widget-content">
                                <table class="stats" data-chart-type="bar" data-chart-colors="">
                                    <caption><?php echo $ano; ?> Receita Bruta (Milhoes)</caption>
                                    <thead>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <?php
                                                echo $titulo;
                                            ?>
                                        </tr>

                                    </thead>

                                    <tbody>

                                        <tr>
                                            <th>Previsto</th>
                                            <?php
                                                echo $graf_orcamento;
                                            ?>
                                        </tr>

                                        <tr>
                                            <th>Realizado</th>
                                            <?php
                                                echo $graf_realizadoMes;
                                            ?>
                                        </tr>
                                    </tbody>
                                </table>
                            </div> <!-- .widget-content -->

                        </div> <!-- .widget -->

                        <div class="widget">

                            <div class="widget-header">
                                <span class="icon-chart"></span>
                                <h3 class="icon chart">Receita Diaria Empresa Mensal</h3>
                            </div>

                            <div class="widget-content">
                                <table class="stats" data-chart-type="line" data-chart-colors="">
                                    <caption><?php echo $ano; ?> Receita Diaria Empresa Mensal</caption>
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