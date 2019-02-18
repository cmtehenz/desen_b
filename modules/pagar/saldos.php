<?php
    namespace Modulos\SemParar;

    require $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();
    $dbcDB2  = new \Library\Scripts\scriptDB2();

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Saldos a pagar</title>

        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/modernizr.js"); ?>"></script>
        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>

        <script>
            Modernizr.load({
                test: Modernizr.inputtypes.date,
                nope: ['http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.7/jquery-ui.min.js', 'jquery-ui.css'],
                complete: function () {
                    $('input[type=date]').datepicker({ dateFormat: 'yy-mm-dd' });
                }
            });
        </script>
    </head>
    <body>
        <?php
            date_default_timezone_set('America/sao_paulo');

            $post = filter_input_array(INPUT_POST);

            $dtIni = $post['dtIni'] ?: date('Y-m-01');
            $dtFin = $post['dtFin'] ?: date('Y-m-d');

            $params = array(
                $dbcSQL->whereParam("C.DATAEMIS", $dtIni, ">="), $dbcSQL->whereParam("C.DATAEMIS", $dtFin, "<="),
                $dbcSQL->whereParam("G.OPERACAO", "Saldo"), $dbcSQL->whereParam("G.SITUACAO", "EFE", "<>"),
                $dbcSQL->whereParam("G.VALOR", "0.01", ">"), $dbcSQL->whereParam("PE.STAFT", "A")
            );

            $sql = "SELECT
                        P.CNPJ_CPF cgc, P.RAZAO_SOCIAL nome, MAX(VE.ID_FILIAL) filial, SUM(G.VALOR) saldo
                    FROM PROGVIAG  G
                    JOIN CADBIPE   C  ON G.IDCADBIPE = C.IDCADBIPE
                    JOIN HVEICULO  V  ON C.ID_HVEICULO = V.ID_HVEICULO
                    JOIN VEICEMP   VE ON (V.ID_VEICULO = VE.ID_VEICULO AND VE.ID_EMPRESA = 1)
                    JOIN HPROPRIET P  ON V.IDHPROPRIET = P.IDHPROPRIET
                    JOIN HPROPEMP  PE ON (P.IDHPROPRIET = PE.IDHPROPRIET AND PE.ID_EMPRESA = 1)
                    WHERE
                        NOT EXISTS (
                            SELECT 1 FROM NFTRANSP NF
                            WHERE NF.ID_CT IN (
                                SELECT CT.ID_CT FROM CT WHERE CT.IDCADBIPE = C.IDCADBIPE
                            )
                            AND NF.DATABX IS NULL
                        )";

            $saldos = $dbcDB2->select($sql, $params, "MAX(VE.ID_FILIAL), P.RAZAO_SOCIAL", "P.CNPJ_CPF, P.RAZAO_SOCIAL", false);

            $groupFilial = array(); $filial = array();

            $totViagens = 0; $totGeral = 0; $totFilial = 0;

            foreach ($saldos as $registro){
                $linkSublist  = "sublists/saldos.php?dtIni=$dtIni&dtFin=$dtFin&cgc=" . $registro['CGC'];

                $contratado = $registro['CGC'] . ' - ' . $registro['NOME'];

                $linhaTabela .=
                    "<tr class='gradeA'>
                        <td><a href='$linkSublist' target='_blank'>$contratado</a></td>
                        <td align='right'>" . $hoUtils->numberFormat($registro['SALDO']) . "</td>
                    </tr>";

                $totViagens++;
                $totGeral  += $hoUtils->numberFormat($registro['SALDO'], 0, 2, '.', '');

                $dadosFilial = $dbcDB2->dadosFilial($registro['FILIAL']);

                if ($filial['sigla'] != $dadosFilial['SIGLA']){
                    if ($filial) {
                        array_push($groupFilial, $filial);
                        
                        /** Consolida o totalizador da filial de acordo com a posição atual do array */
                        $groupFilial[count($groupFilial) - 1]['total'] = $totFilial;
                        
                        $totFilial = 0;
                    }

                    $filial = array("sigla" => $dadosFilial['SIGLA'], "nome" => $dadosFilial['NOME']);
                }

                $filial['linhas'] .= $linhaTabela; $linhaTabela = "";
                
                $totFilial += $hoUtils->numberFormat($registro['SALDO'], 0, 2, '.', '');
            }

            if ($filial) {
                array_push($groupFilial, $filial);

                /** Consolida o totalizador da filial de acordo com a posição atual do array */
                $groupFilial[count($groupFilial) - 1]['total'] = $totFilial;
            }

            $thead = "<tr>
                        <th>Contratado</th>
                        <th width='15%'>Saldo (R$)</th>
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

            <!-- Sidebar -->
            <?php echo $hoUtils->menuUsuario($_SESSION['idUsuario']); ?>

            <div id="content">
                <div id="contentHeader">
                    <h1><?php echo $_SESSION['nomeEmpresa']; ?></h1>
                </div> <!-- #contentHeader -->

                <div class="container">
                    <div class="grid-24">
                        <form action="#" method="post" id="frmSaldoAgr" name="frmSaldoAgr" class="form uniformForm">
                            <div class="field">
                                <label>Selecione o período de emissão das viagens:&nbsp;</label>
                                <input type="date" id="dtIni" name="dtIni" style="width: 130px" maxlength="10" value="<?php echo $dtIni; ?>" />
                                <input type="date" id="dtFin" name="dtFin" style="width: 130px" maxlength="10" value="<?php echo $dtFin; ?>" />
                                &nbsp;
                                <button class="btn btn-primary"><span class="icon-magnifying-glass"></span>Buscar</button>
                            </div>
                        </form>

                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-minus"></span>
                                <h3 class="icon chart">Resumo de saldos pendentes a pagar para agregados</h3>
                            </div>
                        </div> <!-- .widget -->

                        <div class="grid-24 box notify-info" style="margin: -10px 0px 15px 0px; padding: 10px; width: 98%;">
                            <b>&#9679; Período de busca:</b> Corresponde à data em que a viagem foi emitida no sistema e não às datas de vencimento dos saldos. <br />
                            <b>&#9679;</b> O valor indicado na coluna <b>Saldo</b> corresponde a todos os pagamentos pendentes cuja baixa de comprovante do CT-e já foi efetuada. <br />
                            <b>&#9679;</b> Agrupamento de acordo com a filial do veículo, usada para determinar a data de vencimento dos saldos - 
                            <b>HR = Ortigueira (5 e 20), MC = Kimberly (10 e 20) e LE = Geral (5 e 25).</b><br />
                            <b>&#9679;</b> Em casos onde novas filiais apareçam, deve-se verificar com o setor Operacional o cadastro do veículo em questão. <br />
                            <b>&#9679;</b> Ordenado alfabeticamente pela razão social do contratado. <br />
                            <b>&#9679;</b> Clique no nome do proprietário para abrir os detalhes sobre suas viagens individualmente. <br />
                        </div>

                        <?php
                            foreach ($groupFilial as $filial){
                                echo
                                    "<div class='widget widget-table'>
                                        <div class='widget-header'>
                                        <h3 class='icon chart'>" . $filial['sigla'] . " => " . $filial['nome'] . "</h3>
                                    </div>";

                                echo
                                    "<table class='table table-bordered'>
                                        <thead>$thead</thead>
                                        <tbody>
                                            $filial[linhas]
                                            <tr style='font-weight: bold; border-top: 2px solid black;'>
                                                <td class='text-right'>TOTAL POR FILIAL</td>
                                                <td class='text-right'>" . $hoUtils->numberFormat($filial['total'])  . "</td>
                                            </tr>
                                        </tbody>
                                    </table>";

                                echo "</div>";
                            }
                        ?>

                        <hr style="background: #000; height: 2px; margin-bottom: 10px;">

                        <div class="box plain">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th width="50%">Qtd. de contratados</th>
                                        <th width="50%">Total geral (R$)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="odd gradeX">
                                        <td align='right'><?php echo $totViagens; ?></td>
                                        <td align='right'><?php echo $hoUtils->numberFormat($totGeral); ?></td>
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
        </div> <!-- #wrapper -->

        <div id="footer">
            <div style="float: left;">Versão <?php echo $_SESSION['version']; ?></div> Copyright &copy; <?php echo date('Y'); ?>, Case Electronic Ltda.
        </div> <!-- #footer -->
    </body>
</html>