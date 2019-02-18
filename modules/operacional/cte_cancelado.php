<?php
    namespace Modulos\Operacional;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();
    $dbcDB2  = new \Library\Scripts\scriptDB2();

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));

    date_default_timezone_set('America/sao_paulo');

    $post = filter_input_array(INPUT_POST);
    $get  = filter_input_array(INPUT_GET);

    $dtIni = $post['dtIni'] ?: $get['dtIni'] ?: date('Y-m-d');
    $dtFin = $post['dtFin'] ?: $get['dtFin'] ?: date('Y-m-d');
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - CT-es cancelados</title>

        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
    </head>
    <body>
        <?php
            $sql = "SELECT
                        (TRIM(F.SIGLA_FILIAL) || ' - ' || C.NUMERO) cte, L.RAZAO_SOCIAL cliente, U2.USUARIO usuCanc, U.USUARIO usuCad,
                        (C.DATAEMISSAO || ' ' || RIGHT(('00' || C.HORAEMI), 2) || ':' || RIGHT(('00' || C.MINEMI), 2)) emissao
                    FROM CT C
                    JOIN FILIAL   F ON C.ID_FILIAL = F.ID_FILIAL
                    JOIN HCLIENTE L ON C.IDHCLIENTE = L.IDHCLIENTE
                    JOIN USUARIO U ON C.IDUSUARIOCAD = U.ID_USUARIO
                    JOIN USUARIO U2 ON C.IDUSUARIOCANC = U2.ID_USUARIO";

            $params = array(
                $dbcDB2->whereParam("C.DATAEMISSAO", $dtIni, ">="),
                $dbcDB2->whereParam("C.DATAEMISSAO", $dtFin, "<="),
                $dbcDB2->whereParam("C.STATUSCT", "C")
            );

            $ctes = $dbcDB2->select($sql, $params, "C.DATAEMISSAO, C.HORAEMI, C.MINEMI, F.SIGLA_FILIAL, C.NUMERO");

            foreach ($ctes as $cte){
                $numero = $cte['FILIAL'] . '-' . $cte['CTE'];

                $linhaTabela .=
                    "<tr class='gradeA'>
                        <td>$cte[CTE]</td>
                        <td>$cte[CLIENTE]</td>
                        <td>$cte[USUCANC]</td>
                        <td>" .$hoUtils->dateFormat($cte['EMISSAO'], 'Y-m-d H:i', 'd/m/Y H:i')  . "</td>
                        <td>$cte[USUCAD]</td>
                    </tr>";
            }
            
            $totCte = count($ctes);
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
                        <form action="#" method="post" id="frmAverbaCte">
                            <div class="field">
                                <label>Selecione o período:&nbsp;</label>
                                <input type="date" id="dtIni" name="dtIni" style="width: 130px" maxlength="10" value="<?php echo $dtIni; ?>" />
                                <input type="date" id="dtFin" name="dtFin" style="width: 130px" maxlength="10" value="<?php echo $dtFin; ?>" />
                                &nbsp;
                                <button class="btn btn-primary"><span class="icon-magnifying-glass"></span>Buscar</button>
                            </div>
                        </form>
                        <br>
                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-document-alt-stroke"></span>
                                <h3 class="icon chart">CT-es cancelados no período</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width="10%">CT-e</th>
                                            <th>Cliente</th>
                                            <th>Usuário cancelamento</th>
                                            <th>Emissão</th>
                                            <th>Usuario expedidor</th>
                                        </tr>
                                    </thead>
                                    <tbody><?php echo $linhaTabela; ?></tbody>
                                </table>
                            </div> <!-- .widget-content -->
                        </div> <!-- .widget -->
                        <br>
                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-document-alt-stroke"></span>
                                <h3 class="icon chart">CT-es cancelados no período</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width="10%">Quantidade</th>
                                            <th><?php echo $totCte; ?></th>
                                        </tr>
                                    </thead>
                                </table>
                            </div> <!-- .widget-content -->
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
        </div> <!-- #wrapper -->

        <div id="footer"><div style="float: left;">Versão <?php echo $_SESSION['version']; ?></div> Copyright &copy; <?php echo date('Y'); ?>, Case Electronic Ltda.</div>
    </body>
</html>