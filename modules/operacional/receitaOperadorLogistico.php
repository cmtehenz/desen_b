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
            include $_SERVER['DOCUMENT_ROOT'] . '/old/funcoes.php';
            include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptSap.php';
            include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptMSSQL.php';

            /********************************
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
            if (isset($_POST['OpLog'])){
                $operLog = $_POST['OpLog'];
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
            
            $totalFRETEPESOAGREGADO=0;
            foreach (receitaOperadoresLogistico($ano, $mes) as $dados){
                $previsto = valorPrevistoOperLog($dados['CODE'], $ano, $mes);
                $adicEntrega = totalAdicEntregaOperadorLog($dados['CODE'], $ano, $mes);
                $previstoTotal += $previsto;                        
                $linhaTabela = $linhaTabela . "<tr class='gradeA'>
                                    <td>$dados[NOME]</td>
                                    <td align='right'>".number_format($dados['FRETEPESOAGREGADO'], 2, ',', '.')."</td>
                                    <td align='right'>".number_format($dados['FRETEPESOFROTA'], 2, ',', '.')."</td>
                                    <td align='right'>".number_format($dados['FRETEPESOTERCEIRO'], 2, ',', '.')."</td>
                                    <td align='right'>".number_format($adicEntrega, 2, ',', '.')."</td>    
                                    <td align='right'>".number_format($dados['FRETEPESO'], 2, '.', '.')."</td>
                                    <td align='right'>".number_format(($dados['FRETEPESOAGREGADO']+$dados['FRETEPESOFROTA']+$dados['FRETEPESOTERCEIRO']+$adicEntrega), 2, ',', '.')."</td>    
                                    <td align='right'>".number_format($previsto, 2, ',', '.')."</td>
                                    <td align='right'>".number_format(((($dados['FRETEPESOAGREGADO']+$dados['FRETEPESOFROTA']+$dados['FRETEPESOTERCEIRO']+$adicEntrega)/$previsto)*100))."</td>
                                </tr>";
                
                $totalFRETEPESOAGREGADO += $dados['FRETEPESOAGREGADO'];
                $totalFRETEPESOFROTA    += $dados['FRETEPESOFROTA'];
                $totalFRETEPESOTERCEIRO += $dados['FRETEPESOTERCEIRO'];
                $totalADICIONALENTREGA  += $adicEntrega;
                $totalFRETEPESO += $dados['FRETEPESOAGREGADO']+$dados['FRETEPESOFROTA']+$dados['FRETEPESOTERCEIRO'];
                $total          += $dados['FRETEPESOAGREGADO']+$dados['FRETEPESOFROTA']+$dados['FRETEPESOTERCEIRO']+$adicEntrega;
                $totalBaseSemTerceiro += $dados['FRETEPESOAGREGADO']+$dados['FRETEPESOFROTA'];
                
            }
            //CONTRATAÇÃO DE TERCEIROS
            $totalCONTRATACAOTERCEIROS = receitaFretePeso($ano, $mes, null, 'T', null);
            
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
                                <h3 class="icon chart">Faturamento Operador Logistico</h3>
                            </div>

                            <div class="widget-content">

                                <table class="table table-bordered table-striped data-table">
                                    <thead>
                                        <tr>
                                            <th>BASE</th>
                                            <th>AGREGADO(R$)</th>
                                            <th>FROTA(R$)</th>
                                            <th>TERCEIRO(R$)</th>   
                                            <th>ADIC ENT(R$)</th>
                                            <th>FRETE PESO(R$)</th>
                                            <th>TOTAL(R$)</th>
                                            <th>PREVISTO(R$)</th>
                                            <th>PORC(%)</th>
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

                            <h3>TOTAL DAS BASES</h3>

                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>EMPRESA</th>
                                        <th>AGREGADO(R$)</th>
                                        <th>FROTA(R$)</th>
                                        <th>TERCEIRO(R$)</th>
                                        <th>ADIC ENT(R$)</th>
                                        <th>FRETE PESO(R$)</th>
                                        <th>TOTAL</th>
                                        <th>PREVISTO(R$)</th>
                                        <th>PORC %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="odd gradeX">
                                        <td><?php echo $_SESSION['nomeEmpresa']; ?></td>
                                        <td align='right'><?php echo number_format($totalFRETEPESOAGREGADO, 2, ',', '.'); ?></td>
                                        <td align='right'><?php echo number_format($totalFRETEPESOFROTA, 2, ',', '.'); ?></td>
                                        <td align='right'><?php echo number_format($totalFRETEPESOTERCEIRO, 2, ',', '.'); ?></td>
                                        <td align='right'><?php echo number_format($totalADICIONALENTREGA, 2, ',', '.'); ?></td>
                                        <td align='right'><?php echo number_format($totalFRETEPESO, 2, ',', '.'); ?></td>
                                        <td align='right'><?php echo number_format($total, 2, ',', '.'); ?></td>
                                        <td align='right'><?php echo number_format($previstoTotal, 2, ',', '.'); ?></td>
                                        <td align='right'><?php echo number_format((($receitaTotal/$previstoTotal)*100)); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div> <!-- .box -->
                        <div class="box plain">

                            <h3>TOTAL DAS BASES COM CONTRATAÇÃO TERCEIROS</h3>

                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>EMPRESA</th>
                                        <th>AGREGADO(R$)</th>
                                        <th>FROTA(R$)</th>
                                        <th>TERCEIRO(R$)</th>
                                        <th>ADIC ENT(R$)</th>
                                        <th>FRETE PESO(R$)</th>
                                        <th>CONTR TERCEIRO</th>
                                        <th>TOTAL</th>
                                        <th>PREVISTO(R$)</th>
                                        <th>PORC %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="odd gradeX">
                                        <td><?php echo $_SESSION['nomeEmpresa']; ?></td>
                                        <td align='right'><?php echo number_format($totalFRETEPESOAGREGADO, 2, ',', '.'); ?></td>
                                        <td align='right'><?php echo number_format($totalFRETEPESOFROTA, 2, ',', '.'); ?></td>
                                        <td align='right'><?php echo number_format($totalFRETEPESOTERCEIRO, 2, ',', '.'); ?></td>
                                        <td align='right'><?php echo number_format($totalADICIONALENTREGA, 2, ',', '.'); ?></td>
                                        <td align='right'><?php echo number_format($totalFRETEPESO, 2, ',', '.'); ?></td>
                                        <td align='right'><?php echo number_format($totalCONTRATACAOTERCEIROS, 2, ',', '.'); ?></td>
                                        <td align='right'><?php echo number_format($totalBaseSemTerceiro+$totalCONTRATACAOTERCEIROS, 2, ',', '.'); ?></td>
                                        <td align='right'><?php echo number_format($previstoTotal, 2, ',', '.'); ?></td>
                                        <td align='right'><?php echo number_format((($receitaTotal/$previstoTotal)*100)); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
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