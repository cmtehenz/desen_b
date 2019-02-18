<?php
    namespace Modulos\Utilitarios\Post;

    require $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    header('Content-Type: text/html; charset=utf-8');

    $hoUtils = new \Library\Classes\Utils();
    $dbcDB2  = new \Library\Scripts\scriptDB2();

    /** Necessário para o alertScript personalizado */
    echo "<link rel='stylesheet' href=" . $hoUtils->getURLDestino('stylesheets/all.css') . " type='text/css' />";
    echo "<script src=" . $hoUtils->getURLDestino("js/all.js") . "></script>";

    /** Validações e retornos */
    $fileName = "exporta.txt"; //$_FILES["file"]["tmp_name"][0];

    session_start();

    $_SESSION['retLog'] = array();

    $post = filter_input_array(INPUT_POST);

    $dtIni = $post['dtIni'];
    $dtFin = $post['dtFin'];


    /** Busca os valores para a folha ponto em duas tabelas diferentes e faz um merge dos resultados em um único array para exportação */
    $dbcDB2->connect();

    $sqlAcerto = "SELECT year(c.DATAEMIS) as ano, month(c.DATAEMIS) as mes, m.NOME, m.CPF, m.NUMRG, m.DATANASC, sum(c.QTDETARIFATRANS) as QTDETARIFATRANS, sum(c.VLR_TARIFATRANSF) as VLR_TARIFATRANSF, 
						sum(c.QTDETARIFASAQ) as QTDETARIFASAQ, sum(c.VLR_TARIFASAQ) as VLR_TARIFASAQ, sum(c.BC_PIS) as BC_PIS, sum(c.VALFRETEPAGOTOT) as VALFRETEPAGOTOT,
						sum(c.VALTOTLIQPAGO) as VALTOTLIQPAGO, sum(c.VALOBCINSSSEST) as VALOBCINSSSEST, sum(c.VALINSS) as VALINSS,  
						sum((c.VLR_TARIFASAQ * c.QTDETARIFASAQ)) as Tarifa_SAQUE, sum((c.VLR_TARIFATRANSF * c.QTDETARIFATRANS)) as Tarifa_TRANSF
					FROM CADBIPE c 
						Join HVEICULO v on (v.ID_HVEICULO = c.ID_HVEICULO)						
						Join HMOTORIS m on (m.IDHMOTORIS = v.IDHMOTORIS)
						JOIN HVEICULO h ON (c.ID_HVEICULO = h.ID_HVEICULO)
						JOIN HPROPRIET p ON (h.IDHPROPRIET = p.IDHPROPRIET)
					Where c.dataemis BETWEEN '$dtIni' AND '$dtFin'  
						and c.staft in ('T', 'A')
						and p.TIPO_PESSOA='F'
					Group By year(c.DATAEMIS), month(c.DATAEMIS),m.CPF, m.NUMRG, m.NOME, m.DATANASC
					ORDER BY 1,2,3";
					

					/*
    $sqlAcerto = "SELECT year(c.DATAEMIS) as ano, month(c.DATAEMIS) as mes, m.NOME, m.CPF, m.NUMRG, m.DATANASC, sum(c.QTDETARIFATRANS) as QTDETARIFATRANS, sum(c.VLR_TARIFATRANSF) as VLR_TARIFATRANSF, 
						sum(c.QTDETARIFASAQ) as QTDETARIFASAQ, sum(c.VLR_TARIFASAQ) as VLR_TARIFASAQ, sum(c.BC_PIS) as BC_PIS, sum(c.VALFRETEPAGOTOT) as VALFRETEPAGOTOT,
						sum(c.VALTOTLIQPAGO) as VALTOTLIQPAGO, sum(c.VALOBCINSSSEST) as VALOBCINSSSEST, sum(c.VALINSS) as VALINSS,  
						sum((c.VLR_TARIFASAQ * c.QTDETARIFASAQ)) as Tarifa_SAQUE, sum((c.VLR_TARIFATRANSF * c.QTDETARIFATRANS)) as Tarifa_TRANSF,
						p.NUMINSS
					FROM CADBIPE c 
						Join HVEICULO v on (v.ID_HVEICULO = c.ID_HVEICULO)						
						Join HMOTORIS m on (m.IDHMOTORIS = v.IDHMOTORIS)
						Join HPROPRIET p on (p.CNPJ_CPF = m.CPF and p.NUMINSS is not null and p.NUMINSS<> ' ')
					Where c.dataemis BETWEEN '$dtIni' AND '$dtFin'  
						and c.staft in ('T', 'A')
					Group By year(c.DATAEMIS), month(c.DATAEMIS), m.CPF, p.NUMINSS, m.NUMRG, m.NOME, m.DATANASC
					ORDER BY 1,2,3";
					*/
	/* sem agrupar por motorista
        "SELECT c.NUMBIPE, c.DATAEMIS, m.NOME, m.CPF, m.NUMRG, m.DATANASC, v.PLACA, c.QTDETARIFATRANS, c.VLR_TARIFATRANSF, c.QTDETARIFASAQ, 
				c.VLR_TARIFASAQ, c.BC_PIS, c.VALFRETEPAGOTOT, c.VALTOTLIQPAGO, 
				c.VALOBCINSSSEST, c.VALINSS, (c.VLR_TARIFASAQ * c.QTDETARIFASAQ) as Tarifa_SAQUE, (c.VLR_TARIFATRANSF * c.QTDETARIFATRANS) as Tarifa_TRANSF,
				p.NUMINSS, p.CNPJ_CPF, p.NUMRG
			FROM CADBIPE c 
				Join HVEICULO v on (v.ID_HVEICULO = c.ID_HVEICULO)
				Join HPROPRIET p on (p.IDHPROPRIET = v.IDHPROPRIET and p.NUMINSS is not null and p.NUMINSS<> ' ')
				Join HMOTORIS m on (m.IDHMOTORIS = v.IDHMOTORIS)
			Where c.dataemis BETWEEN '$dtIni' AND '$dtFin' 
				and c.staft in ('T', 'A')
				and m.idhmotoris = 48126
			ORDER BY 1,2";
*/
    $result = array_merge($dbcDB2->select($sqlAcerto));

    /** Processa o retorno do banco e monta uma matriz com cada código de evento possível para cada valor encontrado */
    $arLines = array();

	$caminho = "C:/inetpub/wwwroot/bid/producao/modules/utilitarios/post/";
	//echo $caminho;
	$arquivo = fopen("C:/inetpub/wwwroot/bid/producao/modules/utilitarios/post/bipe.txt", "w");
	
    foreach ($result as $motorista){
        $posArray = array();

        //$numbipe = trim($motorista['NUMBIPE']);
		$mes = $motorista['MES'];
		if($motorista['MES'] < 10){
			$mes = "0" . $motorista['MES'];			
		}
        $mesano = ($mes . $motorista['ANO']);		
        $qtdTarifa = $motorista['QTDETARIFATRANS'];		
        $vlrTransf = $motorista['VLR_TARIFATRANSF'];		
        $qtdTransf = $motorista['QTDETARIFASAQ'];		
        $vlrTransfSaq = $motorista['VLR_TARIFASAQ'];
		//$TarifaSaque = (str_replace(",", ".", $qtdTransf)) * (str_replace(",", ".", $vlrTransfSaq)); 		
       //$TarifaTrans = (str_replace(",", ".", $vlrTransf)) * (str_replace(",", ".", $qtdTarifa));
        $TarifaSaque = $motorista['Tarifa_SAQUE'];		
        $TarifaTrans = $motorista['Tarifa_TRANSF'];		
        $bcPis = $motorista['BC_PIS'];		
        $valFretePagoTot = $motorista['VALFRETEPAGOTOT'];	
	
        $valTotLiqPago = ($motorista['VALTOTLIQPAGO'] - $TarifaSaque - $TarifaTrans);		
        $valTotLiqPago = $motorista['VALTOTLIQPAGO'];		
        $valTotLiqPago = (str_replace(".", ",", $valTotLiqPago));		
        $TarifaSaque = (str_replace(".", ",", $TarifaSaque));		
        $vlrTransfSaq = (str_replace(".", ",", $vlrTransfSaq));		
        $valBcINSS = $motorista['VALOBCINSSSEST'];		
        $valINSS = $motorista['VALINSS'];		
        $datEmis = trim($motorista['DATAEMIS']);				
       // $numInss = trim($motorista['NUMINSS']);		
        $numCPF = trim($motorista['CPF']);		
        $numRG = trim($motorista['NUMRG']);		
        $nome = trim($motorista['NOME']);		
        $datanasc = trim($motorista['DATANASC']);		
        $placa = trim($motorista['PLACA']);	

		//Busca o INSS do motorista através do CPF
		$sqlInss = "SELECT NUMINSS FROM PROPRIET WHERE cnpj_cpf='$numCPF' and NUMINSS is not null and NUMINSS<> ' '";
		
		

		$result1 = array_merge($dbcDB2->select($sqlInss));
		foreach ($result1 as $inss){			
			$numInss = trim($inss['NUMINSS']);
			
			
			$linha = $mesano .";". $nome .";". $numCPF .";". $numRG. ";". $numInss .";". $datanasc .";XXX9999;". $valFretePagoTot.";".
				 $valTotLiqPago .";". $valBcINSS .";". $valINSS; 
			$linha .= PHP_EOL;
			fwrite($arquivo, $linha); 
		}
		
			
		/*sem agrupar por mes e ano
		$linha = $numbipe .";". $datEmis .";". $nome .";". $numCPF .";". $numRG. ";". $numInss .";". $datanasc .";". $placa.";". $valFretePagoTot.";".
				 $valTotLiqPago .";". $valBcINSS .";". $valINSS;
				 */
		
		
		
    }
	fclose($arquivo);
    $dbcDB2->disconnect();

	

    /**
     * Recupera o caminho do arquivo que chamou este POST e retorna para ele alertando a mensagem de retorno.
     * O explode serve para remover qualquer parâmetro adjacente ao caminho do arquivo (i.e. file.php?id=1 se torna file.php)
     */
    $location = explode(".php", $_SERVER['HTTP_REFERER']);

    if (count($_SESSION['retLog']) <= 0) $hoUtils->pushLog("Nenhum arquivo informado para importação", "msg");

    return printf($hoUtils->alertScript("Importação finalizada, verifique o log de mensagens para maiores detalhes", "Pronto", "window.location = '$location[0].php'"));
?>