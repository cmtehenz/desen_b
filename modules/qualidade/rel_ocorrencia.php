<?php
    namespace Modulos\Qualidade;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    use Library\Classes\KeyDictionary as KeyDictionary;

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();
    $dbcDB2  = new \Library\Scripts\scriptDB2();

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));

    date_default_timezone_set('America/sao_paulo');
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Relatório de ocorrência</title>

        <meta charset="utf-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
    </head>
    <body>
        <?php
            $sql = "SELECT
                        o.idOcorrencia, dbo.DateTimeFormat103(o.dtOcorrencia) data, o.cpf, o.idVeiculo, t.descricao tipo, t.pontos, o.classificacao, o.bipe,
                        CASE o.acionouMonitoramento WHEN 1 THEN 'Sim' ELSE 'Não' END acMonit
                    FROM ocorrencia o
                    JOIN tipoOcorrencia t ON o.idTipoOcorrencia = t.id";

            $ocorrencias = $dbcSQL->select($sql, null, "dtOcorrencia");

            foreach ($ocorrencias as $ocorrencia){
                $placa = $dbcDB2->placaVeiculo($ocorrencia['idVeiculo']);

                $linhaTabela .=
                    "<tr>
                        <td><a href='ocorrencia.php?id=$ocorrencia[idOcorrencia]' title='Detalhes sobre a ocorrência'>$ocorrencia[data]</a></td>
                        <td>" . $hoUtils->cnpjCpfFormat($ocorrencia['cpf']) . "</td>
                        <td>$placa</td>
                        <td>$ocorrencia[tipo]</td>
                        <td class='text-right'>$ocorrencia[pontos]</td>
                        <td>$ocorrencia[acMonit]</td>
                        <td>" . KeyDictionary::valueClassificacao($ocorrencia['classificacao']) . "</td>
                        <td class='text-right'>$ocorrencia[bipe]</td>
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
                        <div class="agr grid-24 box notify-info" style="margin: -10px 0px 15px 0px; padding: 10px; width: 98%;">
                            <b>&#9679; Para ver detalhes da ocorrência clique no link sobre a data</b>
                        </div>

                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-list"></span>
                                <h3 class="icon chart">Relação de ocorrências</h3>
                            </div>

                            <div class="widget-content">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th width="14%">Data</th>
                                            <th width="10%">CPF motorista</th>
                                            <th width="7%">Placa</th>
                                            <th width="20%">Tipo de ocorrência</th>
                                            <th width="5%">Pontos</th>
                                            <th width="10%">Acionou monit.</th>
                                            <th width="10%">Classificação</th>
                                            <th width="5%">BIPE</th>
                                        </tr>
                                    </thead>
                                    <tbody><?php echo $linhaTabela; ?></tbody>
                                </table>
                            </div> <!-- .widget-content -->
                        </div> <!-- .widget -->
                    </div>
                </div>
            </div>

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