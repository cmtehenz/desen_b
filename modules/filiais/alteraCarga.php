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

		<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

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


			if(($_POST[tipoCte] <> "")){
				$up = "UPDATE cargas SET tipoCte='$_POST[tipoCte]', idFilial='$_POST[filial]' WHERE id=$_POST[idCarga]";
				$altera = mssql_query($up) or die (mssql_error());
				
				echo "<script>
                            alert('Resgitro alterado com sucesso.');
                      </script>";
					  
				echo "<meta HTTP-EQUIV='refresh' CONTENT='0;URL=listaCarga.php'>";
			}
				/*
				if(insertCarga($_POST[tipoCte], $_POST[filial], $_POST[modalidade], $_POST[remetente],
							   $_POST[destinatario], $_POST[cobranca], $_POST[redespacho], $_POST[expedidor],
							   $_POST[modIcms], $_POST[pesoCalculo], $_POST[contrato], $_POST[placa],
							   $_POST[reboque], $_POST[semiReboque], $_POST[fretePeso],
							   $_POST[escolta], $_POST[advaloren], $_POST[seccat],
							   $_POST[carga],
							   $_POST[despacho], $_POST[descarga], $_POST[gris],
							   $_POST[enlonamento], $_POST[adicionalEntrega],
							   $_POST[freteTotal], $_POST[pedagio],
							   $_POST[freteBrutoDesejado], $_POST[responsavelSeguro],
							   $_POST[observacao])){
					
					echo "<script>
								alert('Resgitro cadastrado com sucesso.');
						  </script>";
				}
				*/
				
            /******************************************/
            /******************************************/
           // $sql = mssql_query("SELECT * FROM usuarioFilial WHERE idUsuario=$idUsuario");
            $sql = mssql_query("SELECT * FROM cargas WHERE id = $_GET[idCarga]");
            while($listaFilial = mssql_fetch_array($sql)){
                $nomeFilial = nomeFilialSAP($listaFilial[idFilial]);
                $tipoCte = $listaFilial[tipoCte];
                $remetente = $listaFilial[remetente];
                $destinatario = $listaFilial[destinatario];
                $cobranca = $listaFilial[cobranca];
                $redespacho = $listaFilial[redespacho];
                $expedidor = $listaFilial[expedidor];
                $placa = $listaFilial[placa];
                $idCarga = $listaFilial[id];               
                
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

            <?php 
			echo $hoUtils->menuUsuario($_SESSION['idUsuario']); 
			$listaClientes = listaCliente();
			?>
            <div id="content">

                <div id="contentHeader">
                    <h1><?php echo $_SESSION['nomeEmpresa']; ?></h1>
                </div> <!-- #contentHeader -->

                <div class="container">
                    <div class="grid-24">
                        <form method="post" action="#" enctype="multipart/form-data">
							<input name="idCarga" id="idCarga" hidden value="<?php echo $_GET['idCarga']; ?>">
                            <div class="widget widget-table">
                                <div class="widget-header">
                                    <span class="icon-document-alt-stroke"></span>
                                    <h3 class="icon chart">Cadastro de cargas </h3>
									
                                </div>

                                <div class="widget-content">
                                    <table class="table table-bordered table-striped cadastroBID">
                                        <thead>
                                            <tr>
                                                <th>Tipo Conhecimento:</th>
                                                <td>
                                                    <select name="tipoCte">
                                                        <option value="normal"<?php echo $tipoCte=='normal'?'selected':'';?> >Normal</option>
                                                        <option value="substituto"<?php echo $tipoCte=='substituto'?'selected':'';?> >Subtituto</option>
                                                        <option value="complementar"<?php echo $tipoCte=='complementar'?'selected':'';?> >Complementar</option>
                                                        <option value="anulacao"<?php echo $tipoCte=='anulacao'?'selected':'';?> >Anulação</option>
                                                    </select>
                                                    </td>
                                                <th>Mod do Frete:</th>
                                                <td>
                                                    <select name="modalidade">
                                                        <option value="CIF"<?php echo $tipoCte=='CIF'?'selected':'';?> >CIF</option>
                                                        <option value="FOB"<?php echo $tipoCte=='FOB'?'selected':'';?> >FOB</option>
                                                    </select>
                                                </td>
                                                <th>Filial:</th>
                                                    <td>
                                                        <select name="filial">
                                                            <?php 
                                                                foreach (listaFiliais() as $dados){
																	if($nomeFilial == $dados[NOME]){
																		echo "<option value='$dados[ID]' selected='selected'>$dados[NOME]</option>";
																	}else{
																		echo "<option value='$dados[ID]'>$dados[NOME]</option>";
																	}
                                                                }
                                                            ?>
                                                        </select>
                                                    </td>
                                            </tr>
                                            <tr>
                                                <th>Remetente:</th>
                                                <td colspan="6">
                                                    <select name="remetente">
                                                        <option value="null"></option>
                                                        <?php
                                                            foreach($listaClientes as $dadosC){
                                                                if($remetente == $dadosC[U_SIE_CNPJCPF]){
																	echo "<option value='$dadosC[U_SIE_CNPJCPF]' selected = 'selected'>$dadosC[U_SIE_CNPJCPF] - $dadosC[CardName]</option>";
																}else{
																	echo "<option value='$dadosC[U_SIE_CNPJCPF]'>$dadosC[U_SIE_CNPJCPF] - $dadosC[CardName]</option>";
																}
                                                            }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Destinatário:</th>
                                                <td colspan="6">
                                                    <select name="destinatario">
                                                        <option value="null"></option>
                                                        <?php
                                                            foreach($listaClientes as $dadosC){
                                                                if($destinatario == $dadosC[U_SIE_CNPJCPF]){
																	echo "<option value='$dadosC[U_SIE_CNPJCPF]' selected = 'selected'>$dadosC[U_SIE_CNPJCPF] - $dadosC[CardName]</option>";
																}else{
																	echo "<option value='$dadosC[U_SIE_CNPJCPF]'>$dadosC[U_SIE_CNPJCPF] - $dadosC[CardName]</option>";
																}
                                                            }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Cobrança:</th>
                                                <td colspan="6">
                                                    <select name="cobranca">
                                                        <option value="null"></option>
                                                        <?php
                                                            foreach($listaClientes as $dadosC){
                                                                if($cobranca == $dadosC[U_SIE_CNPJCPF]){
																	echo "<option value='$dadosC[U_SIE_CNPJCPF]' selected = 'selected'>$dadosC[U_SIE_CNPJCPF] - $dadosC[CardName]</option>";
																}else{
																	echo "<option value='$dadosC[U_SIE_CNPJCPF]'>$dadosC[U_SIE_CNPJCPF] - $dadosC[CardName]</option>";
																}
                                                            }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Redespacho:</th>
                                                <td colspan="6">
                                                    <select name="redespacho">
                                                        <option value="null"></option>
                                                        <?php
                                                            foreach($listaClientes as $dadosC){
                                                                if($redespacho == $dadosC[U_SIE_CNPJCPF]){
																	echo "<option value='$dadosC[U_SIE_CNPJCPF]' selected = 'selected'>$dadosC[U_SIE_CNPJCPF] - $dadosC[CardName]</option>";
																}else{
																	echo "<option value='$dadosC[U_SIE_CNPJCPF]'>$dadosC[U_SIE_CNPJCPF] - $dadosC[CardName]</option>";
																}
                                                            }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Expedidor:</th>
                                                <td colspan="6">
                                                    <select name="expedidor">
                                                        <option value="null"></option>
                                                        <?php
                                                            foreach($listaClientes as $dadosC){
                                                                if($expedidor == $dadosC[U_SIE_CNPJCPF]){
																	echo "<option value='$dadosC[U_SIE_CNPJCPF]' selected = 'selected'>$dadosC[U_SIE_CNPJCPF] - $dadosC[CardName]</option>";
																}else{
																	echo "<option value='$dadosC[U_SIE_CNPJCPF]'>$dadosC[U_SIE_CNPJCPF] - $dadosC[CardName]</option>";
																}
                                                            }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th colspan="8">
                                                    <div align="center">
                                                        <input name="id" id="id" hidden value="<?php echo $_GET['idCarga']; ?>">
                                                        <button class="btn btn-success"><span class="icon-check"></span>Salvar</button>
                                                    </div>
                                                </th>
                                            </tr>
										</thead>	
									</table>
								</div>
							</div>
						</form>
					</div>
				</div>
					
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