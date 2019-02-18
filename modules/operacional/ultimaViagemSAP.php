<?php
    namespace Modulos\Operacional;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();
    $dbcDB2  = new \Library\Scripts\scriptDB2();

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Última viagem do veículo</title>

        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
    </head>
    <body>
        <?php
            include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
            include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptSap.php';
            
            /********* Variáveis *********/
            date_default_timezone_set('America/sao_paulo');
            $placa =null;
            if(isset($_POST['placa'])){
                $placa = $_POST['placa'];
            }
            if(isset($_GET['placa'])){
                $placa = $_GET['placa'];
            }

            /** Para evitar a busca e listagem de todas as OS ao abrir a tela pelo menu */
            if ($placa != null){
                //$os = $dbcDB2->osAberto($placa);
                
                foreach(ultimoBipe($placa)as $bipe){
                    $numBipe            = $bipe[DocNum];
                    $dataEmissaoBipe    = $bipe[DATAEMISSAO];
                    $tipoContrato       = $bipe[TIPOCONTRATO];
                    $origem             = $bipe[ORIGEM];
                    $ufOrigem           = $bipe[UFORIGEM];
                    $destino            = $bipe[DESTINO];
                    $ufDestino          = $bipe[UFDESTINO];
                } 
                
                foreach (listaCteBipe($numBipe) as $cte){                
                    $listaNfe = null;
                    foreach(listaNfeCliente($cte[DocNum]) as $lista){
                        $listaNfe .= $lista[U_NUMNF].'-';
                    }
                    $listaNfe = substr($listaNfe, 0, -1);
                    
                    $agendamento = date('d/m/Y', strtotime($cte[DTPREVCHEG]));
                    $agendamento .= ' '.date('h:m', strtotime($cte[HPREVCHEG]));
                    
                    $linhaCte .=
                    "<tr class='gradeA'>
                        <td>$cte[FILIAL]</td>
                        <td>$cte[DocNum]</td>
                        <td>".date('d/m/Y', strtotime($cte[DTEMISSAO]))."</td>
                        <td>$listaNfe</td>
                        <td>$agendamento</td>
                        <td>0</td>
                        <td>" . utf8_encode($cte['CLIENTE']) . "</td>
                    </tr>";
                }
                
                foreach ($os as $ordemSer){
                    $linhaOS .=
                    "<tr class='gradeA'>
                        <td>$ordemSer[NUMERO]</td>
                        <td>$ordemSer[STATUS]</td>
                        <td>$ordemSer[TIPO]</td>
                        <td>" . $hoUtils->dateFormat($ordemSer['ABERTURA'], 'Y-m-d H:i', 'd/m/Y H:i') . "</td>
                        <td>" . $hoUtils->dateFormat($ordemSer['PREVISAO'], 'Y-m-d H:i', 'd/m/Y H:i') . "</td>
                        <td>" . utf8_encode($ordemSer['OBS']) . "</td>
                    </tr>";
                }
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
                        <form action="#" method="post" id="frmUltViagem">
                            <div class="field">
                                <label>Placa:&nbsp;</label>
                                <input type="text" id="placa" name="placa" value="<?php echo $placa; ?>">
                                &nbsp;
                                <button class="btn btn-primary"><span class="icon-magnifying-glass"></span>Buscar</button>
                            </div>
                        </form>
                        <br />

                        <div class="grid-24 box" style="width: 97.5%; margin-left: 0px;">
                            <h2 style="text-align: center;">BIPE <?php echo $numBipe; ?></h2><br />

                            <div class="grid-6" style="text-align: center;"><h3>Emissão</h3>  <?php echo date('d/m/Y', strtotime($dataEmissaoBipe)); ?></div>
                            <div class="grid-6" style="text-align: center;"><h3>Contrato</h3> <?php echo $tipoContrato; ?></div>
                            <div class="grid-6" style="text-align: center;"><h3>Origem</h3>   <?php echo $origem."-".$ufOrigem; ?></div>
                            <div class="grid-6" style="text-align: center;"><h3>Destino</h3>  <?php echo $destino."-".$ufDestino; ?></div>
                        </div>

                        <div class="widget widget-table" id="divCTe">
                            <div class="widget-header">
                                <span class="icon-document-alt-stroke"></span>
                                <h3 class="icon chart">CT-es vinculados ao BIPE</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Filial</th>
                                            <th>Número</th>
                                            <th>Emissão</th>
                                            <th>Notas</th>
                                            <th>Agendamento</th>
                                            <th>Cidade</th>
                                            <th>Cliente</th>
                                        </tr>
                                    </thead>
                                    <tbody><?php echo $linhaCte; ?></tbody>
                                </table>
                            </div> <!-- .widget-content -->
                        </div> <!-- .widget -->

                        <div class="widget widget-table" id="divOS" style="<?php if (!$os) echo "display: none;" ?>">
                            <div class="widget-header">
                                <span class="icon-wrench"></span>
                                <h3 class="icon chart">Ordens de Serviço em aberto para o veículo</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Número</th>
                                            <th>Status</th>
                                            <th>Tipo</th>
                                            <th>Abertura</th>
                                            <th>Previsão</th>
                                            <th>Observação</th>
                                        </tr>
                                    </thead>
                                    <tbody><?php echo $linhaOS; ?></tbody>
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
        </div> <!-- #wrapper -->

        <div id="footer"><div style="float: left;">Versão <?php echo $_SESSION['version']; ?></div> Copyright &copy; <?php echo date('Y'); ?>, Case Electronic Ltda.</div>
    </body>
</html>