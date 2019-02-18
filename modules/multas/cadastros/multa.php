<?php
    namespace Modulos\Multas\Cadastros;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Scripts\scriptSQL();

    $_SESSION['modulo'] = basename(dirname(dirname($_SERVER['PHP_SELF'])));
?>
<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Lanc. de notificação</title>

        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <style type="text/css">
            div.form-line { text-align: center; }
            div.first-field { float: left; }
            div.last-field  { float: right; }

            div.inline-field { display: inline-block; text-align: left; }

            input.numeric-value { width: 90%; text-align: right; }

            #numAuto     { width: 85%; }
            #placa       { width: 85%; }
            #codInfracao { width: 40%; text-align: center; }
            #digInfracao { width: 15%; text-align: center; }

            textarea { overflow: hidden; word-wrap: break-word; resize: none; height: 80px; max-height: 80px; width: 100%; }
        </style>

        <script src="<?php echo $hoUtils->getURLDestino("js/modernizr.js"); ?>"></script>
        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
        <script type="text/javascript">
            Modernizr.load({
                test: Modernizr.inputtypes.date,
                nope: ['http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.7/jquery-ui.min.js', 'jquery-ui.css'],
                complete: function () {
                    $('input[type=date]').datepicker({ dateFormat: 'yy-mm-dd' });
                }
            });

            /*
             * AJAX para buscar o nome do motorista no GetOne de acordo com histórico do veículo na data de infração
             */
            function getNomeMotorista(){
                var strNome = "Informe uma placa válida e a data da infração para encontrar o motorista";

                var placa      = $("#placa").val();
                var dtInfracao = $("#dtInfracao").val();

                if (placa.trim() != "" && placa.length == 7 && dtInfracao.trim() != ""){
                    var url = "../../library/ajax/motoristaVeiculo.ajax.php?";

                    var json = { placa: placa, data: dtInfracao, ajax: 'true' };

                    $.getJSON(url, json, function(retorno){ $("#nomeMotorista").html(retorno); });
                }
                else $("#nomeMotorista").html(strNome);
            }

            /*
             * AJAX para buscar a descrição da infração de acordo com código e dígito informados
             */
            function getDescInfracao(){
                var strDesc = "Infração não encontrada";

                var codigo = $("#codInfracao").val();
                var digito = $("#digInfracao").val();

                if (codigo.trim() != "" && codigo.length == 3 && digito.trim() != "" && digito.length == 2){
                    var url = "../../library/ajax/descricaoInfracaoCTB.ajax.php?";

                    var json = { codigo: codigo, digito: digito, ajax: 'true' };

                    $.getJSON(url, json, function(retorno){ $("#infracao").html(retorno); });
                }
                else $("#infracao").html(strDesc);
            }

            $(document).ready(function(){
                getNomeMotorista();
                getDescInfracao();

                $("#placa")     .on("input", function(){ getNomeMotorista(); });
                $("#dtInfracao").on("input", function(){ getNomeMotorista(); });

                $("#codInfracao").on("input", function(){ getDescInfracao(); });
                $("#digInfracao").on("input", function(){ getDescInfracao(); });

                $("#btnBuscar").click(function(e){
                    e.preventDefault();

                    var numAuto = $("#numAuto").val();

                    if (!numAuto) return;

                    window.location = location.protocol + '//' + location.host + location.pathname + "?numAuto=" + numAuto;
                });
            });
        </script>
    </head>
    <body>
        <?php
            $params = filter_input_array(INPUT_GET);

            /** Prepara os parâmetros para a busca com PDO do registro caso os mesmos sejam passados por GET */
            if ($params){
                $sqlParams = array();

                if ($params['idNotificacao'])
                    array_push($sqlParams, $dbcSQL->whereParam("idNotificacao", $params['idNotificacao']));

                if ($params['numAuto'])
                    array_push($sqlParams, $dbcSQL->whereParam("numAuto", $params['numAuto']));

                if ($sqlParams){
                    $notificacao = $dbcSQL->selectTopOne("SELECT * FROM mlt.notificacao", $sqlParams);

                    /** Procura a multa baseado no ID da notificação */
                    $multa = $dbcSQL->selectTopOne("SELECT * FROM mlt.multa", array($dbcSQL->whereParam("idNotificacao", $notificacao['idNotificacao'])));

                    $infracao = $dbcSQL->selectTopOne("SELECT codigo, digito FROM mlt.infracao", array($dbcSQL->whereParam("idInfracao", $notificacao['idInfracao'])));
                }
            }

            /** <select> de órgãos autuadores */
            $listaOrgaos = $dbcSQL->select("SELECT o.idOrgao '0', o.razaoSocial '1' FROM mlt.orgao o", null, "o.razaoSocial")
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
                        <div class="widget">
                            <div class="widget-header">
                                <span class="icon-denied"></span>
                                <h3>Lançamento de infrações a legislação de trânsito</h3>
                            </div>
                            <div class="widget-content">
                                &#9679; Tela para o registro em etapas (notificação do auto, multa, recurso, definição) de uma infração de trânsito.<br />
                                &#9679; Preencha o número do auto e clique em <b style="color: #0099FF;">buscar</b> para localizar uma notificação já cadastrada.<br />
                                &#9679; Utilize ponto decimal para os valores numéricos, e não vírgula.
                            </div>
                        </div>

                        <form method="post" action="post/gravaMulta.php" enctype="multipart/form-data" class="form">
                            <div class="widget">
                                <div class="widget-header">
                                    <span class="icon-document-alt-stroke"></span>
                                    <h3>Dados da notificação</h3>
                                </div>

                                <div class="widget-content">
                                    <div class="form-line field-group">
                                        <!-- Nº do Auto -->
                                        <div class="inline-field" style="width: 150px;">
                                            <label>Nº auto da infração</label>
                                            <div class="field">
                                                <input name="numAuto" id="numAuto" maxlength="14" value="<?php echo $notificacao['numAuto']; ?>" />
                                            </div>
                                        </div>

                                        <!-- CNPJ órgão autuador -->
                                        <div class="inline-field">
                                            <label>Órgão autuador</label>
                                            <div class="field">
                                                <select name="orgao" style="width: 200px;"><?php echo $hoUtils->getOptionsSelect($listaOrgaos, $notificacao['idOrgao'], 'Nenhum') ?></select>
                                            </div>
                                        </div>

                                        <!-- Data da infração -->
                                        <div class="inline-field">
                                            <label>Data da infração</label>
                                            <div class="field">
                                                <input type="date" name="dtInfracao" id="dtInfracao" value="<?php echo substr($notificacao['dtInfracao'], 0, 10); ?>" />
                                            </div>
                                        </div>

                                        <!-- Data para abertura de recurso -->
                                        <div class="inline-field">
                                            <label>Data p/ recurso</label>
                                            <div class="field">
                                                <input type="date" name="dtRecurso" id="dtRecurso" value="<?php echo substr($notificacao['dtRecurso'], 0, 10); ?>" />
                                            </div>
                                        </div>

                                        <!-- Código da infração -->
                                        <div class="inline-field" style="width: 150px;">
                                            <label>Cód. infração</label>
                                            <div class="field">
                                                <input name="codInfracao" id="codInfracao" maxlength="3" value="<?php echo $infracao['codigo']; ?>" />
                                                -
                                                <input name="digInfracao" id="digInfracao" maxlength="2" value="<?php echo $infracao['digito']; ?>" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-line field-group">
                                        <!-- Placa -->
                                        <div class="inline-field" style="width: 100px;">
                                            <label>Placa</label>
                                            <div class="field">
                                                <input name="placa" id="placa" maxlength="7" value="<?php echo $notificacao['placa']; ?>" />
                                            </div>
                                        </div>

                                        <!-- Nome do meliante -->
                                        <div class="inline-field">
                                            <label>Motorista</label>
                                            <div class="field"><span id="nomeMotorista"></span></div>
                                        </div>
                                    </div>

                                    <div class="form-line field-group">
                                        <!-- Observação -->
                                        <div class="inline-field" style="width: 80%; padding: 10px 10px 0px 10px;">
                                            <label>Observação</label>
                                            <div class="field" style="width: 100%;">
                                                <textarea name="observacao" id="observacao" maxlength="255"><?php echo $notificacao['observacao']; ?></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-line field-group">
                                        <!-- Descrição da infração -->
                                        <div class="inline-field" style="width: 80%; padding: 10px 10px 0px 10px; margin-bottom: 20px; border: 1px #000 dashed;">
                                            <label>Descrição da infração</label>
                                            <div class="field" style="text-align: justify;"><span id="infracao">Nenhum código informado</span></div>
                                        </div>
                                    </div>

                                    <div class="form-line">
                                        <div class="inline-field">
                                            <input name="idNotificacao" id="idNotificacao" hidden value="<?php echo $notificacao['idNotificacao']; ?>">
                                            <button class="btn btn-primary" id="btnBuscar"><span class="icon-magnifying-glass"></span>Buscar</button>
                                        </div>
                                    </div>
                                </div> <!-- .widget-content -->
                            </div> <!-- .widget -->

                            <div class="widget">
                                <div class="widget-header">
                                    <span class="icon-document-alt-fill"></span>
                                    <h3>Dados da multa</h3>
                                </div>

                                <div class="widget-content">
                                    <div class="form-line field-group">
                                        <!-- Valor antes do vencimento -->
                                        <div class="inline-field" style="width: 120px;">
                                            <label>Valor até o venc.</label>
                                            <div class="field">
                                                <input class="numeric-value" name="vlrOriginal" id="vlrOriginal" value="<?php echo $hoUtils->numberFormat($multa['vlrOriginal'], 0, 2, ".", ""); ?>" />
                                            </div>
                                        </div>

                                        <!-- Valor depois de vencido -->
                                        <div class="inline-field" style="width: 120px;">
                                            <label>Valor após o venc.</label>
                                            <div class="field">
                                                <input class="numeric-value" name="vlrVencido" id="vlrVencido" value="<?php echo $hoUtils->numberFormat($multa['vlrVencido'], 0, 2, ".", ""); ?>" />
                                            </div>
                                        </div>

                                        <!-- Data do vencimento -->
                                        <div class="inline-field">
                                            <label>Data de vencimento</label>
                                            <div class="field">
                                                <input type="date" name="dtVencimento" id="dtVencimento" value="<?php echo substr($multa['dtVencimento'], 0, 10); ?>" />
                                            </div>
                                        </div>
                                    </div>

                                    <input name="idMulta" id="idMulta" hidden value="<?php echo $multa['idMulta']; ?>">
                                </div> <!-- .widget-content -->
                            </div> <!-- .widget -->

                            <div class="text-center"><button class="btn btn-success"><span class="icon-check"></span>Salvar todas as informações</button></div>
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