<?php
    namespace Modulos\Utilitarios;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Pagamentos Pamcard</title>

        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <style type="text/css">
            table.details-modal { border-collapse: collapse; margin-bottom: 5px; width: 100%; }
            table.details-modal th { text-align: right; font-weight: bold; width: 25%; }
            table.details-modal td, table.details-modal th { border: 1px solid black; padding: 5px; }
            table.details-modal tr:first-child td, table.details-modal tr:first-child th { border-top: 0; }
            table.details-modal tr:last-child  td, table.details-modal tr:last-child  th { border-bottom: 0; }
            table.details-modal tr td:first-child, table.details-modal tr th:first-child { border-left: 0; }
            table.details-modal tr td:last-child,  table.details-modal tr th:last-child  { border-right: 0; }
        </style>

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>

        <script type="text/javascript">
            $(document).ready(function(){
                $("#justificados").change(function(){ $("#frmPagPamcard").submit(); });

                // AJAX responsável pelo modal de detalhes do conhecimento
                $(".details-modal").click(function(e){
                    e.preventDefault();

                    var sigla = $(this).attr('filial');
                    var cte = $(this).attr('cte');

                    var json = { sigla: sigla, cte: cte };

                    $.getJSON('../../library/ajax/dadosCte.ajax.php', json, function (retorno) {
                        if (retorno){
                            var msg =
                                '<hr style="background: #000; height: 3px; margin-bottom: 10px; margin-top: -10px;">\n\
                                <table class="details-modal">\n\
                                    <tr><th>Status</th><td>' + retorno.STCTE + '</td></tr>\n\
                                    <tr><th>BIPE</th><td>' + retorno.STBIPE + '</td></tr>\n\
                                    <tr><th>Veículo</th><td>' + retorno.placa + '</td></tr>\n\
                                    <tr><th>Favorecido</th><td>' + retorno.favorecido + '</td></tr>\n\
                                    <tr><th>Motorista</th><td>' + retorno.motorista + '</td></tr>\
                                </table>';

                            $.alert ({
                                type: 'modal',
                                title: 'CT-e Nº ' + sigla + ' - ' + cte,
                                text: msg
                            });
                        }
                        else alert("Erro ao consultar detalhes, contate o admnistrador do sistema");
                    });
                });

                // AJAX responsável por editar a justificativa do registro
                $("a.editJustf").click(function (e) {
                    e.preventDefault();

                    var idContrato = $(this).attr('id');

                    var justificativa = prompt("Insira a justificativa");

                    if (justificativa == null) return;

                    updateJustf(idContrato, justificativa);
                });

                function updateJustf(idContrato, justificativa) {
                    var json = { id: idContrato, texto: justificativa };

                    $.getJSON('../../library/ajax/justfPagPamcard.ajax.php', json, function (retorno) {
                        if (!retorno)
                            $("#justf_" + idContrato).text(justificativa);
                        else
                            alert("Erro ao atualizar justificativa, contate o admnistrador do sistema - " + retorno);
                    });
                }
            });
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
                        <div class="widget widget-table">
                            <div class="widget-header">
                                <span class="icon-info"></span>
                                <h3>Relação de pagamentos duplicados nos arquivos de conciliação da Pamcard</h3>
                            </div>
                            <div style="padding: 10px">
                                &#9679; Abaixo constarão todos os pagamentos referente a BIPEs feitos pelo sistema e integrados com a Pamcard via WebService <br />
                                &#9679; Os registros estarão ordenados e agrupados em tabelas pelo número do CT-e <br />
                                &#9679; Cada tabela listará as transações encontradas que possuírem mesmo tipo de parcela mas Nº do Bradesco diferentes <br />
                                <hr style="background: #000; height: 1px; margin-bottom: 5px; margin-top: 5px;">
                                <form method="post" action="#" enctype="multipart/form-data" id="frmPagPamcard">
                                    <input type="checkbox" value="S" id="justificados" name="justificados" <?php if ($_POST['justificados']) echo "checked"; ?>>
                                    Exibir registros justificados - <b style="color: #0099FF;">Marque esta opção para buscar registros duplicados que já foram resolvidos / estornados junto ao banco</b>
                                </form>
                            </div>
                        </div>

                        <?php
                            $filtro = $_POST['justificados'] != "S";

                            /**
                             * Busca todos os registros de contrato de frete (feitos via integração do WebService) que possuam mesmo número de CT-e (Filial - Nº)
                             * e tipo de parcela, e não possuam uma justificativa de resolução do conflito.
                             * Por exemplo se houverem 2 saldos pagos para o CT-e 666, ele será exibido aqui. Ou se houverem 2 BIPEs feitos para
                             * um mesmo CT-e, ambos serão mosrados para o usuário.
                             */
                            $contratos = $dbcSQL->select(
                                "SELECT
                                    c.idContrato, c.idViagem, c.cte, c.filial, c.bipe, c.tipoParcela,
                                    e.numBradesco, e.debito valor, e.lancamento descricao, dbo.DateFormat103(e.data) data, c.justificativa
                                FROM pcd.contrato c
                                LEFT JOIN pcd.extratobrd e ON c.numBradesco = e.numBradesco
                                WHERE (
                                    SELECT COUNT(t.idContrato) FROM pcd.contrato t
                                    WHERE t.cte = c.cte AND t.filial = c.filial AND t.tipoParcela = c.tipoParcela
                                      " . ($filtro ? " AND t.justificativa IS NULL " : "") . "
                                ) > 1 " . ($filtro ? " AND c.justificativa IS NULL " : "") . "
                                ORDER BY e.data, c.cte, c.bipe");

                            foreach ($contratos as $contrato){
                                /** Verifica se o registro corrente é do mesmo CT-e que o anterior e caso não seja, cria uma nova tabela para ele */
                                $echoTable = ($cte != $contrato['cte'] || $filial != $contrato['filial']);

                                if ($echoTable){
                                    /** Se não for o primeiro CT-e, fecha a tabela do anterior */
                                    if ($cte) echo "</tbody></table></div>";

                                    $filial = $contrato['filial'];
                                    $cte    = $contrato['cte'];

                                    $thead =
                                        "<tr>
                                            <th>CT-e número $filial - $cte</th>
                                            <th colspan='7'><a class='details-modal' filial='$filial' cte='$cte'>Ver detalhes</a></th>
                                        </tr>
                                        <tr>
                                            <th width='20%'>Descrição</th>
                                            <th width='8%'>BIPE</th>
                                            <th width='10%'>Parcela</th>
                                            <th width='8%'>ID viagem</th>
                                            <th width='8%'>Nº bradesco</th>
                                            <th width='8%'>Valor</th>
                                            <th width='8%'>Data</th>
                                            <th width='30%'>Justificativa</th>
                                        </tr>";

                                    echo "<div class='widget widget-table'>";
                                    echo "<table class='table table-striped table-bordered'>";
                                    echo "<thead>$thead</thead>";
                                    echo "<tbody>";
                                }

                                /** Remover o número da autorização na descrição do lançamento */
                                $descricao = preg_replace('/[0-9]+/', '', $contrato['descricao']);

                                echo
                                    "<tr>
                                        <td>$descricao</td>
                                        <td>" . $contrato['filial'] . ' - ' . $contrato['bipe'] . "</td>
                                        <td>" . $hoUtils->tipoParcelaPamcard($contrato['tipoParcela']) . "</td>
                                        <td class='text-right'>$contrato[idViagem]</td>
                                        <td class='text-right'>$contrato[numBradesco]</td>
                                        <td class='text-right'>" . $hoUtils->numberFormat($contrato['valor']) . "</td>
                                        <td>$contrato[data]</td>
                                        <td>
                                            <a href='' class='editJustf tooltip' id='$contrato[idContrato]' title='Editar'><i class='icon-pen-alt-fill'></i></a>&nbsp
                                            <span id='justf_$contrato[idContrato]'>$contrato[justificativa]</span>
                                        </td>
                                    </tr>";
                            }

                            if ($contratos) echo "</tbody></table></div>";
                        ?>
                        <!-- Se não encontrou nenhum registro duplicado exibimos um box com mensagem apenas para não deixar a tela vazia -->
                        <div class="notify notify-error" style="<?php if ($contratos) echo "display: none;" ?>">
                            <b>&#9679; Nenhum registro duplicado e não justificado no momento</b>
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
                                    <li><a href="javascript:;">Edit Profile</a></li>
                                    <li><a href="javascript:;">Suspend Account</a></li>
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