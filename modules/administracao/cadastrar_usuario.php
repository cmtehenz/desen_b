<?php
    namespace Modulos\Administracao;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Classes\connectMSSQL();

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));
    
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptSap.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/old/scriptMSSQL.php';
    
    //VARIAVEIS
    $id = $_GET['id'];
    //lista de filiais
    
    foreach (listaFiliais() as $array){
        $checkboxFilial = NULL;
        if(isset($_GET['username'])){
            
            if(buscaUsuarioFilial($id, $array[ID])){
                $checkboxFilial = "checked";
            }
        }
        $listaFiliais .= $array[NOME]."<input name='filial[]' value='$array[ID]' type='checkbox'".$checkboxFilial."/><br>";
    }
    
    
?>
<!doctype html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Cadastro de usuários</title>

        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
        <script type="text/javascript">
            $(document).ready(function(){
                var getConsulta = "<?php echo $_GET['username']; ?>";

                if (getConsulta) consultarUsuario(getConsulta);
            });

            function carregarUsuario(dadosUsuario){
                document.getElementById("txtnome") .value = dadosUsuario.nome;
                document.getElementById("txtlogin").value = dadosUsuario.login;
                document.getElementById("txtsenha").value = dadosUsuario.password;
                document.getElementById("txtmail") .value = dadosUsuario.email;

                dadosUsuario.menus.forEach(function(idMenu){
                    if (document.getElementById(idMenu) != null) document.getElementById(idMenu).checked = true;
                });

                document.getElementById("txtnome").focus();
            };

            // Função para consultar as informações de um usuário e caso exista carregar seus dados na página, evitando duplicação de registros
            function consultarUsuario(username){
                if (username != ""){
                    $.ajax({
                        url: '../../library/ajax/consultarUsuario.ajax.php?username=' + username,
                        method: 'get',
                        success: function(json){
                            var dadosUsuario = JSON.parse(json);

                            if (dadosUsuario.idUsuario != ""){
                                var getConsulta = "<?php echo $_GET['username']; ?>";

                                if (!getConsulta)
                                    var carregarDados = confirm("Já existe um usuário com o login informado, deseja carregar suas informações?");

                                if (carregarDados == true || getConsulta) carregarUsuario(dadosUsuario);
                                else
                                {
                                    document.getElementById("txtlogin").value = "";
                                    document.getElementById("txtlogin").focus();
                                }
                            }
                        }
                    });
                }
            };
        </script>
    </head>
    <body>
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
                        <a href="listar_usuario.php">Listar usuários</a><br /><br />

                        <form method="post" action="post/gravaUsuario.php"  enctype="multipart/form-data">
                            <div class="widget widget-table">
                                <div class="widget-header">
                                    <span class="icon-list"></span>
                                    <h3 class="icon chart">Cadastro de usuários</h3>
                                </div>

                                <div class="widget-content">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Nome:</th>
                                                <th><input name="txtnome" type="text" id="txtnome" size="100" height="20px"maxlength="250"></th>
                                            </tr>
                                            <tr>
                                                <th>Login:</th>
                                                <th><input name="txtlogin" id="txtlogin" maxlength="200" onblur="consultarUsuario(this.value)"></th>
                                            </tr>
                                            <tr>
                                                <th>Senha:</th>
                                                <th>
                                                    <input name="txtsenha" type="password" id="txtsenha">&nbsp;
                                                    <input type="checkbox" name="alteraSenha" id="alteraSenha" value="S"><font style="font-weight: normal">Alterar</font>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th>E-mail:</th>
                                                <th><input name="txtmail" type="text" id="txtmail" size="100" maxlength="250"></th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div> <!-- .widget-content -->
                            </div> <!-- .widget -->
                            
                            <div class="widget widget-table">
                                <div class="widget-header">
                                    <span class="icon-list"></span>
                                    <h3 class="icon chart">Filiais para Acesso</h3>
                                </div>

                                <div class="widget-content">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Selecione as filiais:</th>
                                                <th><?php echo $listaFiliais; ?></th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div> <!-- .widget-content -->
                            </div> <!-- .widget -->

                            <div class="box plain">
                                <h3><u>Permissões de acesso por módulo</u></h3>
                            </div> <!-- .box -->

                            <?php
                                $dbcSQL->connect();

                                $listaModulos =
                                    $dbcSQL->select(
                                        "SELECT o.idModulo, o.nome
                                         FROM modulo o
                                         WHERE o.produto = 'B'
                                         ORDER BY o.ordenacao");

                                // Percorre os módulos de menu e lista seus sub-menus em ordem, criando dinamicamente um check para cada
                                foreach ($listaModulos as $modulo){
                                    $listaMenus = $dbcSQL->select("SELECT m.id_menu idMenu, m.nome FROM menu m WHERE m.idModulo = $modulo[idModulo] ORDER BY ordenacao");

                                    $listaCheckbox = "";

                                    foreach ($listaMenus as $menu){
                                        $listaCheckbox .=
                                            "<tr>
                                                <td>
                                                    <input type='checkbox' name='menu[]' id='$menu[idMenu]' value='$menu[idMenu]'> $menu[nome]
                                                </td>
                                            </tr>";
                                    }

                                    echo "<div class='box plain'>
                                            <h3>$modulo[nome]</h3>
                                            <table class='table table-striped table-bordered'>
                                                <thead>
                                                    $listaCheckbox
                                                </thead>
                                            </table>
                                        </div>";

                                }

                                $dbcSQL->disconnect();
                            ?>

                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th><div align="center"><button class="btn btn-success">Salvar</button></div></th>
                                    </tr>
                                </thead>
                            </table>
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
            <div style="float: left;">Versão <?php echo $_SESSION['version']; ?></div> Copyright &copy; <?php echo date('Y'); ?>, Case Electronic Ltda.
        </div> <!-- #footer -->
    </body>
</html>