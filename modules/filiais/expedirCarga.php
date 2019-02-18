<?php
    namespace Modulos\Filiais\Cargas;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
            //include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptMSSQL.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptSap.php';

    use Library\Classes\KeyDictionary as DD;

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();

    $_SESSION['modulo'] = basename(dirname(dirname($_SERVER['PHP_SELF'])));
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Cadastro de Cargas </title>

        <meta http-equiv="Content-type" content="text/html" charset="UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <style type="text/css">
            #codigo { width: 60px; text-align: center; }
            #digito { width: 22px; text-align: center; }

            textarea { overflow: hidden; word-wrap: break-word; resize: none; height: 80px; max-height: 80px; }
        </style>

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
        <script src="<?php echo $hoUtils->getURLDestino("library/javascripts/jquery.maskMoney.js"); ?>"></script>
        <script type="text/javascript">
            $(document).ready(function(){
                $('.valor')
                    .maskMoney( {thousands: ""} )
                    .on("click", function (){
                        $(this).select();
                    });
            });
        </script>
    </head>
    <body>
        <?php   
        
        if($_POST['filial'] != NULL and $_POST['filial'] != NULL){
            
            $up = "UPDATE cargas SET idFilialBIPE='$_POST[filial]', numBIPE='$_POST[bipe]' "
                    . "WHERE id=$_POST[idCarga]";
            
            //echo $up;
            $altera = mssql_query($up) or die (mssql_error());
            echo "<script>
                        alert('Registro Alterado com SUCESSO.');
                  </script>";
            echo "<meta HTTP-EQUIV='refresh' CONTENT='0;URL=listaCarga.php'>";
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
                        <form method="post" action="#" enctype="multipart/form-data">
                            <input type="hidden" name="idCarga" value="<?php echo $_GET['idCarga']; ?>" >
                            <div class="widget widget-table">
                                <div class="widget-header">
                                    <span class="icon-document-alt-stroke"></span>
                                    <h3 class="icon chart">Cadastro de cargas </h3>
                                </div>

                                <div class="widget-content">
                                    <table class="table table-bordered table-striped cadastroBID">
                                        <thead>
                                            <tr>
                                                <th>Filial:</th>
                                                    <td>
                                                        <select name="filial" required="required" <?php echo $naoEditavel; ?> >
                                                            <option></option>
                                                            <?php 
                                                                foreach (listaFiliais() as $dados){
                                                                    if($dadosCarga['IDFILIAL'] == $dados['ID']){
                                                                        $selected = "selected";
                                                                    }else{
                                                                        $selected = null;
                                                                    }
                                                                        
                                                                    echo "<option value='$dados[ID]' ".$selected.">$dados[NOME]</option>";
                                                                }
                                                            ?>
                                                        </select>
                                                    </td>
                                            </tr>
                                                                                        
                                            <tr>
                                                <th>BIPE:</th>
                                                <td>
                                                    <input class="text" name="bipe" id="bipe">
                                                </td>                                            
                                            </tr>
                                            <tr>
                                                <th colspan="8">
                                                    <div align="center">
                                                        <?php echo $campoId; ?>
                                                        <button class="btn btn-success"><span class="icon-check"></span>Salvar</button>
                                                    </div>
                                                </th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div> <!-- .widget-content -->
                            </div> <!-- .widget -->
                        </form>
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
            <div style="float: left;">VersÃ£o <?php echo $_SESSION['version']; ?></div> Copyright &copy; <?php echo date('Y'); ?>, Case Electronic Ltda.
        </div> <!-- #footer -->
    </body>
</html>