<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));
    
    //ini_set("display_errors",1);
    //ini_set("display_startup_erros",1);
    //error_reporting(E_ALL);
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
            include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptMSSQL.php';            
            include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptSap.php';

            /*             * *******************************
             *   VARIAVEIS                   *
             * ****************************** */
            date_default_timezone_set('America/sao_paulo');
            $wanted_week = date('W');
            $abrev_mes = date('M');
            $nome_mes = date('F');
            $dia = date('d');
            $ano = date('Y');
            $mes = date('m');
            $imob = 0;

            if (isset($_POST['ano'])){
                $ano = $_POST['ano'];
            }
            if (isset($_POST['mes'])){
                $mes = $_POST['mes'];
            }
            if (isset($_POST['imob'])){
                $imob = $_POST['imob'];
            }

            foreach (listaAno() as $dadosAno){
                $selected = null;
                if($ano == $dadosAno[ANO]){
                    $selected = "selected";
                }
                $listaAno = $listaAno . "<option value='$dadosAno[ANO]' $selected>$dadosAno[ANO]</option>";
            }

            foreach (listaMes() as $dadosMes){
                $selected = null;
                if($mes == $dadosMes[ID_MES]){
                    $selected = "selected";
                }
                $listaMes = $listaMes . "<option value='$dadosMes[ID_MES]' $selected>$dadosMes[MES]</option>";
            }
            
            $receitaRealizado = receitaFretePeso($ano, $mes );
            
            

            /************************* 
             * CONSULTA POR FILIAIS
             * ***********************        
             */
            
            foreach(receitaFilialSAP($ano, $mes) as $filial ){
                $linhaTabela .= "<tr class='gradeA'>
                                    <td><a href='sublists/receita_cli_x_fil.php?f=$filial[NOME]&mes=$mes&ano=$ano'>$filial[NOME]</a></td>
                                    <td align='right'>".number_format($filial[FRETEAGREGADO], 2, ',', '.')."</td>
                                    <td align='right'>".number_format($filial[FRETEFROTA], 2, ',', '.')."</td>
                                    <td align='right'>".number_format($filial[FRETETERCEIRO], 2, ',', '.')."</td>
                                    <td align='right'>".number_format($filial[FRETEPESO], 2, ',', '.')."</td>
                                    <td align='right'>".number_format($filial[FRETEPESO]*100/$receitaRealizado, 0 )."</td>
                                </tr>";
                
            }          
            
            /*
             * 
             * SELECT FILIAL.BPLId, FILIAL.BPLName, (SELECT SUM(VW.FRETEPESO) FROM VW_FATURAMENTO_DASHBOARD VW WHERE IDFILIAL=FILIAL.BPLId AND YEAR(VW.DATAEMISDOCTO)=2018) FROM OBPL FILIAL



                SELECT SUM(VW.FRETEPESO) 
                FROM VW_FATURAMENTO_DASHBOARD VW 
                WHERE IDFILIAL=7 AND YEAR(VW.DATAEMISDOCTO)=2018 AND MONTH(DATAEMISDOCTO)=11
             * 
             * 
            foreach (listaFiliais() as $dados_filiais){
                $receitaFilialMes = receitaFretePeso($ano, $mes, null, null, null, $dados_filiais['ID']);
                $linhaTabela = $linhaTabela . "<tr class='gradeA'>
                                    <td><a href='sublists/receita_cli_x_fil.php?f=$dados_filiais[NOME]&mes=$mes&ano=$ano'>$dados_filiais[NOME]</a></td>
                                    <td align='right'>".number_format(receitaFretePeso($ano, $mes, null, 'A', null, $dados_filiais['ID']), 2, ',', '.')."</td>
                                    <td align='right'>".number_format(receitaFretePeso($ano, $mes, null, 'F', null, $dados_filiais['ID']), 2, ',', '.')."</td>
                                    <td align='right'>".number_format(receitaFretePeso($ano, $mes, null, 'T', null, $dados_filiais['ID']), 2, ',', '.')."</td>
                                    <td align='right'>".number_format($receitaFilialMes, 2, ',', '.')."</td>
                                    <td align='right'>".number_format((($receitaFilialMes/$receitaRealizado)*100),0)."</td>
                                </tr>";
                
                //VALORES ORCAMENTO PARA GRAFICO
                $graf_orcamento = $graf_orcamento . "<td>0</td>";
                //NOME FILIAL PARA O GRAFICO
                $nomefilial = htmlentities($dados_filiais['NOME']);
                $titulo = $titulo . "<th>$nomefilial</th>";
                //VALOR FRETE PESO PARA O GRAFICO
                $graf_realizadoMes .= "<td>$receitaFilialMes</td>";
            }
            */

            //DIAS PARA O GRAFICO
            /*$dias_mes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);
            for ($i = 1; $i <= $dias_mes; $i++){
                $tituloGraf .= '<th>' . $i . '</th>';
                //$dados_faturamentoDiario[0] = receitaFretePeso($ano, $mes, $dia, null, null, null);
                $realizadoDiario = number_format(str_replace(',', '.', receitaFretePeso($ano, $mes, $i, null, null, null)), 0, ',', '');

                //REALIZADO PARA O GRAFICO
                $graf_realizado .= '<td>' . $realizadoDiario . '</td>';
                //$graf_realizado = $graf_realizado . '<td>0</td>';
            }
            */
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
                            </div>
                        </form>
                        <br>
                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Faturamento Filiais</h3>
                            </div>

                            <div class="widget-content">

                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>FILIAL</th>
                                            <th width="12%">AGREGADO(R$)</th>
                                            <th width="12%">FROTA(R$)</th>
                                            <th width="12%">TERCEIRO(R$)</th>
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
                                        <th>FRETE PESO(R$)</th>
                                        <th width="7%">PORC %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="odd gradeX">
                                        <td><?php echo $_SESSION['nomeEmpresa']; ?></td>
                                        <td align='right'><?php echo number_format(receitaFretePeso($ano, $mes, null, 'A', null, null), 2, ',', '.'); ?></td>
                                        <td align='right'><?php echo number_format(receitaFretePeso($ano, $mes, null, 'F', null, null), 2, ',', '.'); ?></td>
                                        <td align='right'><?php echo number_format(receitaFretePeso($ano, $mes, null, 'T', null, null), 2, ',', '.'); ?></td>
                                        <td align='right'><?php echo number_format($receitaRealizado, 2, ',', '.'); ?></td>
                                        <td align='right'><?php echo 0; ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div> <!-- .box -->


                        <!--<div class="widget widget-tabs">

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
                            </div>

                        </div> -->
                        
                        <!-- <div class="widget">

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
                            </div> 

                        </div> -->

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