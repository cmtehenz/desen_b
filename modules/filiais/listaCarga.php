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
        <title>BID - Lista de Cargas Pendentes</title>

        <meta charset="utf-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="refresh" content='60;url=#'>        


        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
    </head>

    <body>
        <?php
            include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptSap.php';
            include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
            //include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptMSSQL.php';

            /**********************************
             *   VARIAVEIS                    *
             * ********************************/
            date_default_timezone_set('America/sao_paulo');
            $wanted_week = date('W');
            $abrev_mes = date('M');
            $nome_mes = date('F');
            $dia = date('d');
            $ano = date('Y');
            $mes_atual = date('m');
            $mes = date('m');
            $imob = 0;
            $idUsuario = $_SESSION[idUsuario];

            if (isset($_POST['ano'])){
                $ano = $_POST['ano'];
            }
            if (isset($_POST['mes'])){
                $mes_atual = $_POST['mes'];
                $mes = $_POST['mes'];
            }
            if (isset($_POST['imob'])){
                $imob = $_POST['imob'];
            }

            /******************************************/
            /******************************************/
           // $sql = mssql_query("SELECT * FROM usuarioFilial WHERE idUsuario=$idUsuario");
            
            if(isset($_POST['semPlaca'])){
                $cond = " 1=1 ";  
            }else{
                $cond = " (placa = ' ' or placa = '') ";
            }
            if($_POST['filialBusca']){
                $cond = $cond . " and idFilial = " . $_POST['filialBusca'];       
            }
            $sql = mssql_query("SELECT * FROM cargas WHERE  $cond order by 1");
            
            while($listaFilial = mssql_fetch_array($sql)){
                $nomeFilial = nomeFilialSAP($listaFilial[idFilial]);
                $tipoCte = $listaFilial[tipoCte];
                $remetente = $listaFilial[remetente];
                $destinatario = $listaFilial[destinatario];
                $cobranca = $listaFilial[cobranca];
                $redespacho = $listaFilial[redespacho];
                $expedidor = $listaFilial[expedidor];
                $idCarga = $listaFilial[id];
                
                $linhaTabela = $linhaTabela . "<tr class='gradeA'>
                                                <td>".$listaFilial[id]."</td>
                                                <td>$listaFilial[numPedido]</td>
                                                <td>".$nomeFilial."</td>
                                                <td>".$tipoCte."</td>
                                                <td>".$remetente."</td>
                                                <td>".$destinatario."</td>
                                                <td>".$cobranca."</td>
                                                <td>".$redespacho."</td>
                                                <td>".$expedidor."</td>
                                                <td>
                                                    <a href=Carga.php?idCarga=".$idCarga.">Programar</a>
                                                    <a href=imprime.php?idCarga=".$idCarga." target='_blank'>Visualizar</a>
                                                </td>
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

                        <div class="widget widget-table">

                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Relatorio Cargas Pendentes</h3>
                            </div>

                            
                        <form method="post" action="#" enctype="multipart/form-data">
                            <div class="widget widget-table">

                                <div class="widget-content">
                                    <table class="table table-bordered table-striped cadastroBID">
                                        <thead>
                                            <tr>
                                                <td><b>Filial</b>
                                                    <select name="filialBusca">
                                                        <option value "" selected >Todos </option>
                                                        <?php 
                                                            foreach (listaFiliais() as $dados){
                                                                echo "<option value='$dados[ID]'>$dados[NOME]</option>";
                                                                
                                                            }
                                                        ?>
                                                    </select>
                                                    <input type="checkbox" name="semPlaca" value="S">Todas as Cargas
                                                    <button class="btn btn-success"><span class="icon-check"></span>Filtrar</button>
                                                </td>
                                            </th>
                                        </thead>
                                    </table>
                                </div>
                        </form>
                            
                        <div class="widget-content">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID CARGA</th>
                                        <th>NUM PEDIDO</th>                                           
                                        <th>FILIAL</th>
                                        <th>TIPO </th>
                                        <th>REMETENTE</th>
                                        <th>DESTINATÁRIO</th>
                                        <th>COBRANÇA</th>
                                        <th>REDESPACHO</th>
                                        <th>EXPEDITOR</th>
                                        <th>AÇÕES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php echo $linhaTabela; ?>
                                </tbody>
                            </table>
                            <form method="post" action="imprimeComp.php" enctype="multipart/form-data">
                                
                                <input name="filialBusca" id="filialBusca" hidden value="<?php echo $_POST['filialBusca']; ?>">
                              
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th align="center"><button class="btn btn-success"><span class="icon-check"></span>Imprimir</button></th>
                                        </tr>
                                    </thead>
                                </table>
                            </form>
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

        <div id="footer">
            <div style="float: left;">Versão <?php echo $_SESSION['version']; ?></div> Copyright &copy; <?php echo date('Y'); ?>, Case Electronic Ltda.
        </div>
    </body>
</html>