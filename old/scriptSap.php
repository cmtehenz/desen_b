<?php
function receitaFretePeso($ano=null, $mes=null, $dia=null, $tipoContrato=null, $placa=null, $idFilial=null, $semPlaca=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT SUM(FRETEPESO)
                                FROM VW_FATURAMENTO_DASHBOARD
                                ";
    if ($ano != null) {
        $sql .= 'WHERE YEAR(DATAEMISDOCTO)=' . $ano;
    }
    if ($mes != null) {
        $sql .= ' AND MONTH(DATAEMISDOCTO)=' . $mes;
    }
    if ($dia != null) {
        $sql .= ' AND DAY(DATAEMISDOCTO)=' . $dia;
    }
    //TIPO CONTRATO (AGREGADO, FROTA,TERCEIRO)
    if ($tipoContrato != null) {
        $sql .= ' AND TIPOCONTRATO="' . $tipoContrato . '"';
    }
    //PLACA
    if($placa != null){
        $sql .= ' AND PLACA="' . $placa . '"';
    }
    if($idFilial != null){
        $sql .= ' AND IDFILIAL="' . $idFilial . '"';
    }
    if($semPlaca != null){
        $sql .= ' AND PLACA is NULL ';
    }

    $sql_receitaFretePeso = mssql_query($sql);
    $dados = mssql_fetch_array($sql_receitaFretePeso);
    if ($dados[0] == null) {
        $dados[0] = 0;
    }
    return $dados[0];
}

function listaVeiculosFaturamento($ano=null, $mes=null, 
        $dia=null, $tipoContrato=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    
    $sql = "SELECT PLACA, CONJUNTO,  SUM(FRETEPESO) as VALORPLACA, "
            . "SUM(FRETEPESOCICMS) as VALORPLACACICMS FROM VW_FATURAMENTO_DASHBOARD ";
    
    if ($ano != null) {
        $sql .= 'WHERE YEAR(DATAEMISDOCTO)=' . $ano;
    }
    if ($mes != null) {
        $sql .= ' AND MONTH(DATAEMISDOCTO)=' . $mes;
    }
    if ($dia != null) {
        $sql .= ' AND DAY(DATAEMISDOCTO)=' . $dia;
    }
    //TIPO CONTRATO (AGREGADO, FROTA,TERCEIRO)
    if ($tipoContrato != null) {
        $sql .= ' AND TIPOCONTRATO="' . $tipoContrato . '"';
    }
    
    $sql .= ' GROUP BY PLACA, CONJUNTO ORDER BY VALORPLACA DESC ';    
    
    $SQLeXEC = mssql_query($sql);
    $i=0;
    $dados=null;
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $dados[$i][PLACA]         = $d[PLACA];
        $dados[$i][CONJUNTO]      = $d[CONJUNTO];
        $dados[$i][FRETEPESO]     = $d[VALORPLACA];
        $dados[$i][FRETEPESOCICMS] = $d[VALORPLACACICMS];
    }
    return $dados;
}

function pesoCarregado($ano=null, $mes=null, $dia=null, $tipoContrato=null, 
        $placa=null, $idFilial=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT SUM(PESOKG)
                                FROM VW_FATURAMENTO_DASHBOARD
                                ";
    if ($ano != null) {
        $sql .= 'WHERE YEAR(DATAEMISDOCTO)=' . $ano;
    }
    if ($mes != null) {
        $sql .= ' AND MONTH(DATAEMISDOCTO)=' . $mes;
    }
    if ($dia != null) {
        $sql .= ' AND DAY(DATAEMISDOCTO)=' . $dia;
    }
    //TIPO CONTRATO (AGREGADO, FROTA,TERCEIRO)
    if ($tipoContrato != null) {
        $sql .= ' AND TIPOCONTRATO="' . $tipoContrato . '"';
    }
    //PLACA
    if($placa != null){
        $sql .= ' AND PLACA="' . $placa . '"';
    }
    if($idFilial != null){
        $sql .= ' AND IDFILIAL="' . $idFilial . '"';
    }

    $SQLeXEC = mssql_query($sql);
    $dados = mssql_fetch_array($SQLeXEC);
    if ($dados[0] == null) {
        $dados[0] = 0;
    }
    return $dados[0];
}

function listaFiliais(){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $SQLeXEC = mssql_query("SELECT cast(BPLId as int), cast(BPLName as text) NOME FROM OBPL ");
    $i=0;
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $da[$i][ID] = $d[0];
        $da[$i][NOME] = $d[1];
    }
    return $da;
}

function nomeFilialSAP($idFilial){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $SQLeEXEC = mssql_query("SELECT TOP 1 cast(BPLName as text) NOME FROM OBPL
                    WHERE BPLId=$idFilial");
    $dados = mssql_fetch_array($SQLeEXEC);
    return $dados[NOME];
}

function nomeClienteSAP($troncoCnpj=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $SQLeEXEC = mssql_query("SELECT TOP 1 CardName FROM OCRD
                                WHERE U_SIE_CNPJCPF like '$troncoCnpj%'");
    $dados = mssql_fetch_array($SQLeEXEC);
    return $dados[CardName];  
}

function diretorioPDF($idFilial){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $SQLeEXEC = mssql_query("SELECT top 1 U_PDFPATH FROM [@SIEGO_PFIL]
                    WHERE Code=$idFilial ");
    $dados = mssql_fetch_array($SQLeEXEC);
    return $dados[U_PDFPATH];
}

function diretorioXML($idFilial){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $SQLeEXEC = mssql_query("SELECT top 1 U_XMLPATH FROM [@SIEGO_PFIL]
                    WHERE Code=$idFilial ");
    $dados = mssql_fetch_array($SQLeEXEC);
    return $dados[U_XMLPATH];
}

function diretorioMDFE($idFilial){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $SQLeEXEC = mssql_query("SELECT top 1 U_MDF_PDFPATH FROM [@SIEGO_PFIL]
                    WHERE Code=$idFilial ");
    $dados = mssql_fetch_array($SQLeEXEC);
    return $dados[U_MDF_PDFPATH];
}

function listaCte($idFilial=null, $ano=null, $mes=null, $dia=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT * FROM VW_FATURAMENTO_DASHBOARD";
    if($idFilial != null){
        $sql .= " WHERE IDFILIAL='$idFilial' ";
    }
    if($ano != null){
        $sql .= " AND YEAR(DATAEMISDOCTO) = $ano";
    }
    if($mes != null){
        $sql .= " AND MONTH(DATAEMISDOCTO) = $mes";
    }
    if($dia != null){
        $sql .= " AND DAY(DATAEMISDOCTO) = $dia";
    }
    $SQLeXEC = mssql_query($sql);
    $i=0;
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $da[$i][IDFILIAL]      = $d[IDFILIAL];
        $da[$i][NOMEFILIAL]    = $d[NOMEFILIAL];
        $da[$i][NUMERODOCTO]   = $d[NUMERODOCTO];
        $da[$i][PLACA]         = $d[PLACA];
        $da[$i][DATAEMISDOCTO] = $d[DATAEMISDOCTO];
        $da[$i][TIPOCONTRATO]  = $d[TIPOCONTRATO];
        $da[$i][NOMECLIENTE]   = $d[NOMECLIENTE];
        $da[$i][CNPJDEST]      = $d[CNPJDEST];
        $da[$i][IDDOCTO]       = $d[IDDOCTO];
    }
    return $da;
}

function listaCteAverbacao($dtIni=null, $dtFim=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT DocEntry, U_BPLNAME_OBPL, U_CODECTRC, U_CARDNAME_OCRDCB, U_BPLID_OBPL
            FROM [@SIEGO_CTRC] 
            WHERE U_DTEMISSAO BETWEEN '$dtIni' AND '$dtFim' 
                AND U_STATUSCT = 1
            ORDER BY U_BPLNAME_OBPL, DocEntry
            ";
    $SQLeXEC = mssql_query($sql);
    $i=0;
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $da[$i][ID]      = $d[DocEntry];
        $da[$i][FILIAL]  = $d[U_BPLNAME_OBPL];
        $da[$i][CTE]     = $d[U_CODECTRC];
        $da[$i][CLIENTE] = $d[U_CARDNAME_OCRDCB];
        $da[$i][IDFILIAL]= $d[U_BPLID_OBPL];
    }
    return $da;
}

function listaNfeCliente($idDocto){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT U_NUMNF FROM [@SIEGO_NFTL] WHERE U_ID_CTRC=$idDocto ";
    $SQLeXEC = mssql_query($sql);
    $i=0;
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $da[$i][U_NUMNF]    =$d[U_NUMNF];
    }
    return $da;
}

function listaReceitaPlacasOpeLog($idOpeLog=null, $ano=null, $mes=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT distinct(veic.Code) PL, U_NAME_VMOD MOD, (SELECT SUM(FRETEPESO) FROM VW_FATURAMENTO_DASHBOARD WHERE PLACA=veic.Code AND YEAR(DATAEMISDOCTO)=$ano AND MONTH(DATAEMISDOCTO)=$mes ) as fat,
                    (SELECT TOP 1 NOMEMOTORIS FROM VW_VEICULOS_DASHBOARD WHERE PLACA=veic.Code) as MOTORISTA
                FROM [@SIEGO_VEIC] veic
                WHERE veic.U_CODE_OPER=$idOpeLog
                ORDER BY fat DESC
                ";
    $SQLeXEC = mssql_query($sql);
    $i=0;
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $da[$i][PL]      = $d[PL];
        $da[$i][MOD]     = $d[MOD];
        $da[$i][FAT]     = $d[fat];
        $da[$i][MOT]     = $d[MOTORISTA];
    }
    return $da;
}

function quantidadePlacasOperadorLogistico($idOpeLog=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT distinct(veic.Code)
                FROM [@SIEGO_VEIC] veic
                WHERE veic.U_CODE_OPER=$idOpeLog
                ";
    $SQLeXEC = mssql_query($sql);
    return mssql_num_rows($SQLeXEC);
}

function totalReceitaOpeLog($idOpeLog=null, $ano=null, $mes=null, $tipoContrato=null, $dia=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT SUM(FRETEPESO) VALOR FROM VW_FATURAMENTO_DASHBOARD";
    
    if($ano != null){
        $sql .= " WHERE YEAR(DATAEMISDOCTO)=$ano";
    }
    if($mes != null){
        $sql .= " AND MONTH(DATAEMISDOCTO)=$mes";
    }
    if($dia != null){
        $sql .= " AND DAY(DATAEMISDOCTO)=$dia";
    }
    if($idOpeLog != null){
        $sql .= " AND IDOPERADOR='$idOpeLog'";
    }
    if($tipoContrato != null){
        $sql .= " AND TIPOCONTRATO='$tipoContrato'";
    }
    $SQLeXEC = mssql_query($sql);
    while($d = mssql_fetch_array($SQLeXEC)){
        $da      = $d['VALOR'];
    }
    return $da;
}

function totalReceitaOpeLogSemPlaca($idOpeLog=null, $ano=null, $mes=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT SUM(FRETEPESO) VALOR FROM VW_FATURAMENTO_DASHBOARD 
                WHERE TIPOCONTRATO is null AND IDOPERADOR='$idOpeLog' AND YEAR(DATAEMISDOCTO)=$ano AND MONTH(DATAEMISDOCTO)=$mes
                ";
    $SQLeXEC = mssql_query($sql);
    while($d = mssql_fetch_array($SQLeXEC)){
        $da      = $d[VALOR];
    }
    return $da;
}

function listaOperadorLogistico($OpLog=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT * FROM [@SIEGO_OPER]";
    if($OpLog != null){
        $sql .= " WHERE Code != $OpLog";
    }
    $SQLeXEC = mssql_query($sql);
    $i=0;
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $da[$i][CODE]      = $d[Code];
        $da[$i][NOME]      = $d[Name];
    }
    return $da;
}

function operadorLogisticoSelecionado($OpLog){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT TOP 1 * FROM [@SIEGO_OPER] WHERE Code = $OpLog";
    $SQLeXEC = mssql_query($sql);
    while($d = mssql_fetch_array($SQLeXEC)){
        $da[NOME]      = $d[Name];
    }
    return $da[NOME];
}

function listaDocumentos($placa=null, $ano=null, $mes=null, $cnpjCliente=null, $documentoNFtaurado=null, $filial=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT * FROM VW_FATURAMENTO_DASHBOARD";
    if($ano != null){
        $sql .= " WHERE YEAR(DATAEMISDOCTO)=$ano";
    }
    if($mes != null){
        $sql .= " AND MONTH(DATAEMISDOCTO)=$mes";
    }
    if($filial != null){
        $sql .= " AND NOMEFILIAL='$filial'";
    }
    if($placa != null){
        $sql .= " AND PLACA='$placa'";
    }
    if($cnpjCliente != null){
        $sql .= " AND CNPJCLIENTE like '$cnpjCliente%' ";
    }
    if($documentoNFtaurado != null){
        $sql .= " AND FATURA is null ";
    }
    
    //DOCUMENTOS NAO FATURADOS
    // FATURA is null AND Pago = 0
    //ORDER
    $sql .= " ORDER BY NUMERODOCTO";
    $SQLeXEC = mssql_query($sql);
    $i=0;
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $da[$i][TIPODOCTO]      = $d[TIPODOCTO];
        $da[$i][NOMEFILIAL]     = $d[NOMEFILIAL];
        $da[$i][NUMERODOCTO]    = $d[NUMERODOCTO];
        $da[$i][DATAEMISDOCTO]  = $d[DATAEMISDOCTO];
        $da[$i][FRETEPESO]      = $d[FRETEPESO];
        $da[$i][FRETEPESOCICMS] = $d[FRETEPESOCICMS];
        $da[$i][NOMECLIENTE]    = $d[NOMECLIENTE];
        $da[$i][VALORCOMIMPOSTO]= $d[VALORCOMIMPOSTO];
    }
    return $da;
}

function quantidadeDocumentos($placa=null, $ano=null, $mes=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT * FROM VW_FATURAMENTO_DASHBOARD";
    if($ano != null){
        $sql .= " WHERE YEAR(DATAEMISDOCTO)=$ano";
    }
    if($mes != null){
        $sql .= " AND MONTH(DATAEMISDOCTO)=$mes";
    }
    if($placa != null){
        $sql .= " AND PLACA='$placa'";
    }
    $SQLeXEC = mssql_query($sql);
    
    return mssql_num_rows($SQLeXEC);
}

function kmviagemVazia($placa=null, $idOperLog=null, $ano=null, $mes=null, $dia=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT SUM(rt.U_DSTANCIA) as DISTANCIA
                FROM [@SIEGO_BIPE] bp
                JOIN [@SIEGO_ROTA] rt ON (rt.Code = bp.U_CODE_ROTA)
                JOIN [@SIEGO_VEIC] vc ON (vc.Code = bp.U_CODE_VEIC)
              WHERE bp.u_tpdoc = 1 AND bp.U_STATUS<>2
                ";
    if($dia != null){
        $sql .= " AND DAY(bp.U_DTEMISSAO)=$dia";
    }
    if($mes != null){
        $sql .= " AND MONTH(bp.U_DTEMISSAO)=$mes";
    }
    if($ano != null){
        $sql .= " AND YEAR(bp.U_DTEMISSAO)=$ano";
    }
    
    if($placa != null){
        $sql .= " AND bp.U_CODE_VEIC ='$placa'";
        $sql .= " GROUP BY bp.U_CODE_VEIC ";
    }
    if($idOperLog != null){
        $sql .= " AND vc.U_CODE_OPER='$idOperLog'";
        $sql .= " GROUP BY vc.U_CODE_OPER ";
    }
    //GROUP BY
    
    $SQLeXEC = mssql_query($sql);
    while($d = mssql_fetch_array($SQLeXEC)){
        $da      = $d[DISTANCIA];
    }
    return $da; 
}

function listaBipesViagemVazia($placa=null, $ano=null, $mes=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT FL.BPLNAME NOMEFILIAL, BP.DocEntry NUMBIPE, BP.U_DTEMISSAO DTEMISSAO, 
                   BP.U_CODE_ROTA, RT.Name ROTANOME, RT.U_DSTANCIA KM
                FROM [@SIEGO_BIPE] BP 
                JOIN OBPL FL ON (FL.BPLId = BP.U_BPLID_OBPL)
                JOIN [@SIEGO_ROTA] RT ON (RT.Code = BP.U_CODE_ROTA)
                WHERE BP.u_tpdoc = 1 AND bp.U_STATUS<>2
            ";
    if($ano != null){
        $sql .= " AND YEAR(U_DTEMISSAO)=$ano";
    }
    
    if($mes != null){
        $sql .= " AND MONTH(U_DTEMISSAO)=$mes";
    }
    if($placa != null){
        $sql .= " AND U_CODE_VEIC='$placa'";
    }
    
    //ORDER
    $sql .= " ORDER BY BP.DocEntry";
    $SQLeXEC = mssql_query($sql);
    $i=0;
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $da[$i][IDFILIAL]     = $d[NOMEFILIAL];
        $da[$i][NUMBIPE]      = $d[NUMBIPE];
        $da[$i][DTEMISSAO]    = $d[DTEMISSAO];
        $da[$i][ROTA]         = $d[ROTANOME];
        $da[$i][KM]           = $d[KM];
    }
    return $da;
}

function quantidadeBipeViagemVazia($placa=null, $idOperLog=null, $ano=null, $mes=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT COUNT(*) QT
                FROM [@SIEGO_BIPE] BP 
                JOIN [@SIEGO_VEIC] VC ON (VC.Code = BP.U_CODE_VEIC)
                WHERE BP.u_tpdoc = 1 AND bp.U_STATUS<>2 ";
    if($mes != null){
        $sql .= " AND MONTH(BP.U_DTEMISSAO)=$mes";
    }
    if($ano != null){
        $sql .= " AND YEAR(BP.U_DTEMISSAO)=$ano";
    }
    
    if($placa != null){
        $sql .= " AND U_CODE_VEIC='$placa'";
    }
    
    if($idOperLog != null){
        $sql .= " AND VC.U_CODE_OPER='$idOperLog'";
    }
    
    $SQLeXEC = mssql_query($sql);
    while($d = mssql_fetch_array($SQLeXEC)){
        $da = $d[QT];
    }
    return $da;
}

function totalDocumentosNFaturados($ano=null, $mes=null, $tipoDocto=null, $cnpjCliente=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT SUM(VALORCOMIMPOSTO) VALOR FROM VW_FATURAMENTO_DASHBOARD
            WHERE FATURA is null
            ";
    
    if($ano != null){
        $sql .= " AND YEAR(DATAEMISDOCTO)=$ano";
    }
    if($mes != null){
        $sql .= " AND MONTH(DATAEMISDOCTO)=$mes";
    }
    if($tipoDocto != null){
        $sql .= " AND TIPODOCTO='$tipoDocto' "; 
    }
    if($cnpjCliente != null){
        $sql .= " AND CNPJCLIENTE like '$cnpjCliente%' ";
    }
    
    $SQLeXEC = mssql_query($sql);
    while($d = mssql_fetch_array($SQLeXEC)){
        $da      = $d[VALOR];
    }
    return $da; 
}

function listaClientesDocumentosNFaturados($ano=null, $mes=null, $tipoDocto=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT CONVERT(VARCHAR(8), VW1.CNPJCLIENTE) as CNPJGROUP, SUM(VALORCOMIMPOSTO) VALOR
                FROM VW_FATURAMENTO_DASHBOARD VW1
                WHERE VW1.FATURA is null
            ";
    
    if($ano != null){
        $sql .= " AND YEAR(DATAEMISDOCTO)=$ano";
    }
    if($mes != null){
        $sql .= " AND MONTH(DATAEMISDOCTO)=$mes";
    }
    if($tipoDocto != null){
        $sql .= " AND TIPODOCTO='$tipoDocto' "; 
    }
    $sql .= " GROUP BY CONVERT(VARCHAR(8), VW1.CNPJCLIENTE)
                ORDER BY SUM(VW1.VALORCOMIMPOSTO) DESC";
    
    $i=0;
    $SQLeXEC = mssql_query($sql);
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $da[$i][NOMECLIENTE]      = $d[NOMECLIENTE];
        $da[$i][CNPJCLIENTE]      = $d[CNPJGROUP];
        $da[$i][VALOR]            = $d[VALOR];
    }
    return $da; 
}


function listaFolha($dtIni=null, $dtFin=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    
    $sql2 = "SELECT COALESCE(EMP.ExtEmpNo, 0) AS MATRICULA,
			(CASE
					WHEN COALESCE(EMP.MiddleName, '') <> '' THEN (COALESCE(EMP.firstName,'') + ' ' + COALESCE(EMP.MiddleName, '') + ' ' + COALESCE(EMP.LastName,''))
					ELSE (COALESCE(EMP.firstName,'') + ' ' + COALESCE(EMP.LastName,''))
				END) AS NOME,
			SUM (ACER.U_VALVIAGENS) AS VIAGENS,
				SUM (ACER.U_VALDEBITOS) AS DEBITOS,
					SUM (ACER.U_VALCREDITOS) AS CREDITOS,
						(SUM (ACER.U_VALCREDITOS) - SUM (ACER.U_VALDEBITOS)) AS DIFCREDEB,
						(SUM (ACER.U_VALCOMISSAO)) AS COMISSAO,
                                                EMP.CPF
		FROM [@SIEGO_ACER] ACER
		JOIN OHEM EMP ON EMP.empID = ACER.U_EMPID_OHEM
		WHERE ACER.U_TIPOCONT = 1
		AND ACER.U_DATAINI BETWEEN '$dtIni' AND '$dtFin'
		AND ACER.U_STATUS = 1
		GROUP BY EMP.ExtEmpNo,
				EMP.firstName,
				EMP.middleName,
				EMP.lastName,
                                EMP.CPF
            ";
   
    $sql = "SELECT COALESCE(EMP.ExtEmpNo, 0) AS MATRICULA,
		(CASE
			WHEN COALESCE(EMP.MiddleName, '') <> '' THEN (COALESCE(EMP.firstName,'') + ' ' + COALESCE(EMP.MiddleName, '') + ' ' + COALESCE(EMP.LastName,''))
			ELSE (COALESCE(EMP.firstName,'') + ' ' + COALESCE(EMP.LastName,''))
		END) AS NOME,
		PONTO.U_TOTHRCINQP AS EXTRA50,
		PONTO.U_TOTHRCEMP AS EXTRA100,
		PONTO.U_TOTHRADNOT AS ADNOTURNO,
		PONTO.U_TOTHRESPEC AS HORAESPERA,
		PONTO.U_TOTDIARIA AS DIARIAS,
                EMP.CPF,
		(SELECT COUNT (ITPONTO.DocEntry) * 7.33333
		FROM [@SIEGO_FPT1] ITPONTO
		WHERE ITPONTO.DocEntry = PONTO.DocEntry
			AND LOWER(ITPONTO.U_MOTIVO) LIKE 'falt%') HORAFALTA,
			PONTO.U_TOTDSRT DSRT
		FROM [@SIEGO_FPTO] PONTO
		JOIN OHEM EMP ON EMP.empID = PONTO.U_EMPID_OHEM
		WHERE PONTO.U_PERIODOINICIO >= '$dtIni'
		AND PONTO.U_PERIODOINICIO <= '$dtFin'
            ";
    
    $i=0;
    
    $SQLeXEC2 = mssql_query($sql2);
    while($d = mssql_fetch_array($SQLeXEC2)){
        $i++;
        $da[$i][MATRICULA] = $d[MATRICULA];
        /*
        if($d[MATRICULA]==""){                    
            $da[$i][MATRICULA] = $d[CPF];  
        }else{
            $da[$i][MATRICULA] = $d[MATRICULA];
        }
        */
        $da[$i][NOME]= $d[NOME];      
        $da[$i][VIAGENS]= $d[VIAGENS];
        $da[$i][EXTRA50]= $d[EXTRA50];
        $da[$i][EXTRA100]= $d[EXTRA100];
        $da[$i][ADNOTURNO]= $d[ADNOTURNO];
        $da[$i][HORAESPERA]= $d[HORAESPERA];
        $da[$i][DIARIAS]= $d[DIARIAS];
        $da[$i][HORAFALTA]= $d[HORAFALTA];
        $da[$i][DSRT]= $d[DSRT];      
        //13/10 de acordo com Jerri eventos 106 e 191: subtrair um pelo outro, se der negativo, gera no evento 106 se der positivo, gera no evento 191
        // por isso gera diretamente a diferença entre crédito e débito
        if($d[DIFCREDEB] < 0){
			$valAux = $d[DIFCREDEB] * (-1);
			//$valAux = str_replace('.', ',', $valAux);
                        //verifica se o valor é inteiro, se for, adiciona ,00
                      //  if(preg_match('/^[1-9][0-9]*$/', $valAux)){
                       //     $valAux = $valAux . ",00";
                      //  }
            $da[$i][DEBITOS]= $valAux;
        }else{
			$valAux = $d[DIFCREDEB];
			//$valAux = str_replace('.', ',', $valAux);
                        //verifica se o valor é inteiro, se for, adiciona ,00
                       // if(preg_match('/^[1-9][0-9]*$/', $valAux)){
                       //     $valAux = $valAux . ",00";
                       // }
            $da[$i][CREDITOS]= $valAux;
        }
         
        $da[$i][COMISSAO]= $d[COMISSAO];
        $da[$i][DIFCREDEB]= $d[DIFCREDEB];
        //echo ">>>>>>>>>>>>>>>> " . $da[$i][DIFCREDEB] . "<br>";
    }
    $SQLeXEC = mssql_query($sql);
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $da[$i][MATRICULA] = $d[MATRICULA];
        /*
        if($d[MATRICULA]==""){                    
            $da[$i][MATRICULA] = $d[CPF];  
        }else{
            $da[$i][MATRICULA] = $d[MATRICULA];
        }
        */
        $da[$i][NOME]= $d[NOME];      
        $da[$i][CPF]= $d[CPF];        
        $da[$i][VIAGENS]= $d[VIAGENS];
        $da[$i][EXTRA50]= $d[EXTRA50];
        $da[$i][EXTRA100]= $d[EXTRA100];
        $da[$i][ADNOTURNO]= $d[ADNOTURNO];
        $da[$i][HORAESPERA]= $d[HORAESPERA];
        $da[$i][DIARIAS]= $d[DIARIAS];
        $da[$i][HORAFALTA]= $d[HORAFALTA];
        $da[$i][DSRT]= $d[DSRT];
        $da[$i][CREDITOS]= $d[CREDITOS]; 
        $da[$i][DEBITOS]= $d[DEBITOS];
        $da[$i][COMISSAO]= $d[COMISSAO];
        $da[$i][DIFCREDEB]= $d[DIFCREDEB];
        //echo ">>>>>>>>>>>>>>>> " . $da[$i][CREDITOS] . "<br>";
        
//echo "XXXXX". $d[MATRICULA];  
    }    
    return $da; 
}

function listaFolha2(){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT COALESCE(EMP.ExtEmpNo, 0) AS MATRICULA,
					(CASE
						WHEN COALESCE(EMP.MiddleName, '') <> '' THEN (COALESCE(EMP.firstName,'') + ' ' + COALESCE(EMP.MiddleName, '') + ' ' + COALESCE(EMP.LastName,''))
						ELSE (COALESCE(EMP.firstName,'') + ' ' + COALESCE(EMP.LastName,''))
					END) AS NOME,
					PONTO.U_TOTHRCINQP AS extra50,
					PONTO.U_TOTHRCEMP AS extra100,
					PONTO.U_TOTHRADNOT AS adNoturno,
					PONTO.U_TOTHRESPEC AS horaEspera,
					PONTO.U_TOTDIARIA AS diarias,
					(SELECT COUNT (ITPONTO.DocEntry) * 7.33333
					FROM [@SIEGO_FPT1] ITPONTO
					WHERE ITPONTO.DocEntry = PONTO.DocEntry
						AND LOWER(ITPONTO.U_MOTIVO) LIKE 'falt%') horaFalta,
						PONTO.U_TOTDSRT dsrt
					FROM [@SIEGO_FPTO] PONTO
					JOIN OHEM EMP ON EMP.empID = PONTO.U_EMPID_OHEM
            ";
    
    $i=0;
    $SQLeXEC = mssql_query($sql);
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $da[$i][MATRICULA] = $d[MATRICULA];
        $da[$i][NOME]= $d[NOME];        
        $da[$i][VIAGENS]= $d[VIAGENS];
        $da[$i][EXTRA50]= $d[EXTRA50];
        $da[$i][EXTRA100]= $d[EXTRA100];
        $da[$i][ADNOTURNO]= $d[ADNOTURNO];
        $da[$i][HORAESPERA]= $d[HORAESPERA];
        $da[$i][DIARIAS]= $d[DIARIAS];
        $da[$i][HORAFALTA]= $d[HORAFALTA];
        $da[$i][DSRT]= $d[DSRT];
        $da[$i][CREDITOS]= $d[CREDITOS]; 
        $da[$i][DEBITOS]= $d[DEBITOS];
    }
    return $da; 
}

// lista folha frotas
function listaFolhaFrota($dtIni=null, $dtFin=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    
    $sql2 = "SELECT COALESCE(EMP.ExtEmpNo, 0) AS MATRICULA,
			(CASE
					WHEN COALESCE(EMP.MiddleName, '') <> '' THEN (COALESCE(EMP.firstName,'') + ' ' + COALESCE(EMP.MiddleName, '') + ' ' + COALESCE(EMP.LastName,''))
					ELSE (COALESCE(EMP.firstName,'') + ' ' + COALESCE(EMP.LastName,''))
				END) AS NOME,
			SUM (ACER.U_VALVIAGENS) AS VIAGENS,
				SUM (ACER.U_VALDEBITOS) AS DEBITOS,
					SUM (ACER.U_VALCREDITOS) AS CREDITOS,
						(SUM (ACER.U_VALCREDITOS) - SUM (ACER.U_VALDEBITOS)) AS DIFCREDEB,
						(SUM (ACER.U_VALCOMISSAO)) AS COMISSAO,
                                                EMP.CPF
		FROM [@SIEGO_ACER] ACER
		JOIN OHEM EMP ON EMP.empID = ACER.U_EMPID_OHEM
		WHERE ACER.U_TIPOCONT = 1
		AND ACER.U_DATAINI BETWEEN '$dtIni' AND '$dtFin'
		AND ACER.U_STATUS = 1
                AND EMP.U_code_mdac = 1
		GROUP BY EMP.ExtEmpNo,
				EMP.firstName,
				EMP.middleName,
				EMP.lastName,
                                EMP.CPF
            ";
   
    $sql = "SELECT COALESCE(EMP.ExtEmpNo, 0) AS MATRICULA,
		(CASE
			WHEN COALESCE(EMP.MiddleName, '') <> '' THEN (COALESCE(EMP.firstName,'') + ' ' + COALESCE(EMP.MiddleName, '') + ' ' + COALESCE(EMP.LastName,''))
			ELSE (COALESCE(EMP.firstName,'') + ' ' + COALESCE(EMP.LastName,''))
		END) AS NOME,
		PONTO.U_TOTHRCINQP AS EXTRA50,
		PONTO.U_TOTHRCEMP AS EXTRA100,
		PONTO.U_TOTHRADNOT AS ADNOTURNO,
		PONTO.U_TOTHRESPEC AS HORAESPERA,
		PONTO.U_TOTDIARIA AS DIARIAS,
                EMP.CPF,
		(SELECT COUNT (ITPONTO.DocEntry) * 7.33333
		FROM [@SIEGO_FPT1] ITPONTO
		WHERE ITPONTO.DocEntry = PONTO.DocEntry
			AND LOWER(ITPONTO.U_MOTIVO) LIKE 'falt%') HORAFALTA,
			PONTO.U_TOTDSRT DSRT
		FROM [@SIEGO_FPTO] PONTO
		JOIN OHEM EMP ON EMP.empID = PONTO.U_EMPID_OHEM
		WHERE PONTO.U_PERIODOINICIO >= '$dtIni'
		AND PONTO.U_PERIODOINICIO <= '$dtFin'
                AND EMP.U_code_mdac = 1
            ";
    
    $i=0;
    
    $SQLeXEC2 = mssql_query($sql2);
    while($d = mssql_fetch_array($SQLeXEC2)){
        $i++;
        $da[$i][MATRICULA] = $d[MATRICULA];
        /*
        if($d[MATRICULA]==""){                    
            $da[$i][MATRICULA] = $d[CPF];  
        }else{
            $da[$i][MATRICULA] = $d[MATRICULA];
        }
        */
        $da[$i][NOME]= $d[NOME];      
        $da[$i][VIAGENS]= $d[VIAGENS];
        $da[$i][EXTRA50]= $d[EXTRA50];
        $da[$i][EXTRA100]= $d[EXTRA100];
        $da[$i][ADNOTURNO]= $d[ADNOTURNO];
        $da[$i][HORAESPERA]= $d[HORAESPERA];
        $da[$i][DIARIAS]= $d[DIARIAS];
        $da[$i][HORAFALTA]= $d[HORAFALTA];
        $da[$i][DSRT]= $d[DSRT];      
        //13/10 de acordo com Jerri eventos 106 e 191: subtrair um pelo outro, se der negativo, gera no evento 106 se der positivo, gera no evento 191
        // por isso gera diretamente a diferença entre crédito e débito
        if($d[DIFCREDEB] < 0){
			$valAux = $d[DIFCREDEB] * (-1);
			//$valAux = str_replace('.', ',', $valAux);
                        //verifica se o valor é inteiro, se for, adiciona ,00
                      //  if(preg_match('/^[1-9][0-9]*$/', $valAux)){
                       //     $valAux = $valAux . ",00";
                      //  }
            $da[$i][DEBITOS]= $valAux;
        }else{
			$valAux = $d[DIFCREDEB];
			//$valAux = str_replace('.', ',', $valAux);
                        //verifica se o valor é inteiro, se for, adiciona ,00
                       // if(preg_match('/^[1-9][0-9]*$/', $valAux)){
                       //     $valAux = $valAux . ",00";
                       // }
            $da[$i][CREDITOS]= $valAux;
        }
         
        $da[$i][COMISSAO]= $d[COMISSAO];
        $da[$i][DIFCREDEB]= $d[DIFCREDEB];
        //echo ">>>>>>>>>>>>>>>> " . $da[$i][DIFCREDEB] . "<br>";
    }
    $SQLeXEC = mssql_query($sql);
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $da[$i][MATRICULA] = $d[MATRICULA];
        /*
        if($d[MATRICULA]==""){                    
            $da[$i][MATRICULA] = $d[CPF];  
        }else{
            $da[$i][MATRICULA] = $d[MATRICULA];
        }
        */
        $da[$i][NOME]= $d[NOME];      
        $da[$i][CPF]= $d[CPF];        
        $da[$i][VIAGENS]= $d[VIAGENS];
        $da[$i][EXTRA50]= $d[EXTRA50];
        $da[$i][EXTRA100]= $d[EXTRA100];
        $da[$i][ADNOTURNO]= $d[ADNOTURNO];
        $da[$i][HORAESPERA]= $d[HORAESPERA];
        $da[$i][DIARIAS]= $d[DIARIAS];
        $da[$i][HORAFALTA]= $d[HORAFALTA];
        $da[$i][DSRT]= $d[DSRT];
        $da[$i][CREDITOS]= $d[CREDITOS]; 
        $da[$i][DEBITOS]= $d[DEBITOS];
        $da[$i][COMISSAO]= $d[COMISSAO];
        $da[$i][DIFCREDEB]= $d[DIFCREDEB];
        //echo ">>>>>>>>>>>>>>>> " . $da[$i][CREDITOS] . "<br>";
        
//echo "XXXXX". $d[MATRICULA];  
    }    
    return $da; 
}


// lista folha fixos
function listaFolhaFixos($dtIni=null, $dtFin=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    
    $sql2 = "SELECT COALESCE(EMP.ExtEmpNo, 0) AS MATRICULA,
			(CASE
					WHEN COALESCE(EMP.MiddleName, '') <> '' THEN (COALESCE(EMP.firstName,'') + ' ' + COALESCE(EMP.MiddleName, '') + ' ' + COALESCE(EMP.LastName,''))
					ELSE (COALESCE(EMP.firstName,'') + ' ' + COALESCE(EMP.LastName,''))
				END) AS NOME,
			SUM (ACER.U_VALVIAGENS) AS VIAGENS,
				SUM (ACER.U_VALDEBITOS) AS DEBITOS,
					SUM (ACER.U_VALCREDITOS) AS CREDITOS,
						(SUM (ACER.U_VALCREDITOS) - SUM (ACER.U_VALDEBITOS)) AS DIFCREDEB,
						(SUM (ACER.U_VALCOMISSAO)) AS COMISSAO,
                                                EMP.CPF
		FROM [@SIEGO_ACER] ACER
		JOIN OHEM EMP ON EMP.empID = ACER.U_EMPID_OHEM
		WHERE ACER.U_TIPOCONT = 1
		AND ACER.U_DATAINI BETWEEN '$dtIni' AND '$dtFin'
		AND ACER.U_STATUS = 1
                AND EMP.U_code_mdac = 2 
		GROUP BY EMP.ExtEmpNo,
				EMP.firstName,
				EMP.middleName,
				EMP.lastName,
                                EMP.CPF
            ";
   
    $sql = "SELECT COALESCE(EMP.ExtEmpNo, 0) AS MATRICULA,
		(CASE
			WHEN COALESCE(EMP.MiddleName, '') <> '' THEN (COALESCE(EMP.firstName,'') + ' ' + COALESCE(EMP.MiddleName, '') + ' ' + COALESCE(EMP.LastName,''))
			ELSE (COALESCE(EMP.firstName,'') + ' ' + COALESCE(EMP.LastName,''))
		END) AS NOME,
		PONTO.U_TOTHRCINQP AS EXTRA50,
		PONTO.U_TOTHRCEMP AS EXTRA100,
		PONTO.U_TOTHRADNOT AS ADNOTURNO,
		PONTO.U_TOTHRESPEC AS HORAESPERA,
		PONTO.U_TOTDIARIA AS DIARIAS,
                EMP.CPF,
		(SELECT COUNT (ITPONTO.DocEntry) * 7.33333
		FROM [@SIEGO_FPT1] ITPONTO
		WHERE ITPONTO.DocEntry = PONTO.DocEntry
			AND LOWER(ITPONTO.U_MOTIVO) LIKE 'falt%') HORAFALTA,
			PONTO.U_TOTDSRT DSRT
		FROM [@SIEGO_FPTO] PONTO
		JOIN OHEM EMP ON EMP.empID = PONTO.U_EMPID_OHEM
		WHERE PONTO.U_PERIODOINICIO >= '$dtIni'
		AND PONTO.U_PERIODOINICIO <= '$dtFin'
                AND EMP.U_code_mdac = 2
            ";
    
    $i=0;
    
    $SQLeXEC2 = mssql_query($sql2);
    while($d = mssql_fetch_array($SQLeXEC2)){
        $i++;
        $da[$i][MATRICULA] = $d[MATRICULA];
        /*
        if($d[MATRICULA]==""){                    
            $da[$i][MATRICULA] = $d[CPF];  
        }else{
            $da[$i][MATRICULA] = $d[MATRICULA];
        }
        */
        $da[$i][NOME]= $d[NOME];      
        $da[$i][VIAGENS]= $d[VIAGENS];
        $da[$i][EXTRA50]= $d[EXTRA50];
        $da[$i][EXTRA100]= $d[EXTRA100];
        $da[$i][ADNOTURNO]= $d[ADNOTURNO];
        $da[$i][HORAESPERA]= $d[HORAESPERA];
        $da[$i][DIARIAS]= $d[DIARIAS];
        $da[$i][HORAFALTA]= $d[HORAFALTA];
        $da[$i][DSRT]= $d[DSRT];      
        //13/10 de acordo com Jerri eventos 106 e 191: subtrair um pelo outro, se der negativo, gera no evento 106 se der positivo, gera no evento 191
        // por isso gera diretamente a diferença entre crédito e débito
        if($d[DIFCREDEB] < 0){
			$valAux = $d[DIFCREDEB] * (-1);
			//$valAux = str_replace('.', ',', $valAux);
                        //verifica se o valor é inteiro, se for, adiciona ,00
                      //  if(preg_match('/^[1-9][0-9]*$/', $valAux)){
                       //     $valAux = $valAux . ",00";
                      //  }
            $da[$i][DEBITOS]= $valAux;
        }else{
			$valAux = $d[DIFCREDEB];
			//$valAux = str_replace('.', ',', $valAux);
                        //verifica se o valor é inteiro, se for, adiciona ,00
                       // if(preg_match('/^[1-9][0-9]*$/', $valAux)){
                       //     $valAux = $valAux . ",00";
                       // }
            $da[$i][CREDITOS]= $valAux;
        }
         
        $da[$i][COMISSAO]= $d[COMISSAO];
        $da[$i][DIFCREDEB]= $d[DIFCREDEB];
        //echo ">>>>>>>>>>>>>>>> " . $da[$i][DIFCREDEB] . "<br>";
    }
    $SQLeXEC = mssql_query($sql);
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $da[$i][MATRICULA] = $d[MATRICULA];
        /*
        if($d[MATRICULA]==""){                    
            $da[$i][MATRICULA] = $d[CPF];  
        }else{
            $da[$i][MATRICULA] = $d[MATRICULA];
        }
        */
        $da[$i][NOME]= $d[NOME];      
        $da[$i][CPF]= $d[CPF];        
        $da[$i][VIAGENS]= $d[VIAGENS];
        $da[$i][EXTRA50]= $d[EXTRA50];
        $da[$i][EXTRA100]= $d[EXTRA100];
        $da[$i][ADNOTURNO]= $d[ADNOTURNO];
        $da[$i][HORAESPERA]= $d[HORAESPERA];
        $da[$i][DIARIAS]= $d[DIARIAS];
        $da[$i][HORAFALTA]= $d[HORAFALTA];
        $da[$i][DSRT]= $d[DSRT];
        $da[$i][CREDITOS]= $d[CREDITOS]; 
        $da[$i][DEBITOS]= $d[DEBITOS];
        $da[$i][COMISSAO]= $d[COMISSAO];
        $da[$i][DIFCREDEB]= $d[DIFCREDEB];
        //echo ">>>>>>>>>>>>>>>> " . $da[$i][CREDITOS] . "<br>";
        
//echo "XXXXX". $d[MATRICULA];  
    }    
    return $da; 
}


function listaBipe($dtI=null, $dtF=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "WITH IMPOSTOS AS
		(SELECT IMPOSTOS.DocEntry,
				COALESCE(SUM(IMPOSTOS.BaseSum),0) AS BC_PIS
		FROM PCH4 IMPOSTOS
		JOIN OPCH NOTA ON NOTA.DocEntry = IMPOSTOS.DocEntry
		JOIN OSTT ON IMPOSTOS.staType = OSTT.AbsId
		AND OSTT.Name = 'PIS'
		GROUP BY IMPOSTOS.DocEntry )
		
		SELECT YEAR(BIPE.U_DTCADASTRO) AS ANO,
			MONTH(BIPE.U_DTCADASTRO) AS MES,
			(CASE
					WHEN COALESCE(MOT.MiddleName, '') <> '' THEN (COALESCE(MOT.firstName,'') + ' ' + COALESCE(MOT.MiddleName, '') + ' ' + COALESCE(MOT.LastName,''))
					ELSE (COALESCE(MOT.firstName,'') + ' ' + COALESCE(MOT.LastName,''))
				END) AS NOME,
                        PROP.CARDNAME AS MOTORISTA,
                        PROP.U_SIE_CNPJCPF AS CPF,
			PROP.U_RG AS NUMRG,			
			PROP.U_SKILLNIT AS NUMINSS,			
			CAST(PROP.U_DATANASC AS DATE) AS DATANASC,
			COALESCE((SUM (BIPE.U_QTDTRA)),0) AS QTDETARIFATRANS,
			COALESCE(sum(CASE BIPE.U_QTDTRA
								WHEN 0 THEN 0
								ELSE BIPE.U_VALTRA / BIPE.U_QTDTRA
							END),0) AS VLR_TARIFATRANSF,
			COALESCE((SUM (BIPE.U_QTDSAQ)),0) AS QTDETARIFASAQ,
			COALESCE(sum(CASE BIPE.U_QTDSAQ
								WHEN 0 THEN 0
								ELSE BIPE.U_VALSAQ / BIPE.U_QTDSAQ
							END),0) AS VLR_TARIFASAQ,
			COALESCE((SUM (IMPOSTOS.BC_PIS)),0) AS BC_PIS,
			COALESCE((SUM (BIPE.U_FREBRUTOPAGO)),0) AS VALFRETEPAGOTOT,
			COALESCE((SUM (BIPE.U_FRELIQPAGO)),0) AS VALTOTLIQPAGO,
			COALESCE((SUM (BIPE.U_BASEINSS)),0) AS VALOBCINSSSEST,
			COALESCE((SUM (BIPE.U_VALINSS)),0) AS VALINSS,
			COALESCE((SUM (BIPE.U_VALSAQ)),0) AS Tarifa_SAQUE,
			COALESCE((SUM (BIPE.U_VALTRA)),0) AS Tarifa_TRANSF
		FROM [@SIEGO_BIPE] BIPE
		JOIN [@SIEGO_HVEI] HVEI ON HVEI.Code = BIPE.U_CODE_HVEI
		JOIN OHEM MOT ON MOT.empID = HVEI.U_EMPID_OHEM
		JOIN OCRD PROP ON PROP.CardCode = HVEI.U_CARDCODE_OCRD
		LEFT JOIN OPCH NOTA ON NOTA.U_DOCENTRY_BIPE = BIPE.DocEntry
		LEFT JOIN IMPOSTOS ON IMPOSTOS.DocEntry = NOTA.DocEntry
                WHERE BIPE.U_DTCADASTRO BETWEEN '$dtI' AND '$dtF'
		AND   MOT.U_TIPOCONT IN (2,3)
		AND PROP.U_TIPOPESSOA = 1
		AND BIPE.CANCELED <> 'Y'
		GROUP BY YEAR(BIPE.U_DTCADASTRO),
				MONTH(BIPE.U_DTCADASTRO),
				MOT.firstName,
				MOT.MiddleName,
				MOT.LastName,
                                PROP.CARDNAME,
				PROP.U_SIE_CNPJCPF,
				PROP.U_RG,
				PROP.U_DATANASC,
				PROP.U_SKILLNIT
		ORDER BY 1,
				2,
				3
                                

            ";
// AND PROP.U_SIE_NIT = '10407997641'   
    $i=0;
    $SQLeXEC = mssql_query($sql);
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $da[$i][MATRICULA] = $d[MATRICULA];
        $da[$i][NOME]= $d[MOTORISTA];        
        $da[$i][VIAGENS]= $d[VIAGENS];
        $da[$i][EXTRA50]= $d[EXTRA50];
        $da[$i][EXTRA100]= $d[EXTRA100];
        $da[$i][ADNOTURNO]= $d[ADNOTURNO];
        $da[$i][HORAESPERA]= $d[HORAESPERA];
        $da[$i][DIARIAS]= $d[DIARIAS];
        $da[$i][HORAFALTA]= $d[HORAFALTA];
        $da[$i][DSRT]= $d[DSRT];
        $da[$i][CREDITOS]= $d[CREDITOS]; 
        $da[$i][DEBITOS]= $d[DEBITOS];
        $da[$i][NUMINSS]= $d[NUMINSS];
        $da[$i][MES]= $d[MES];        
        $da[$i][ANO]= $d[ANO];      
        $da[$i][CPF]= $d[CPF];    
        $da[$i][NUMRG]= $d[NUMRG];    
        $da[$i][DATANASC]= $d[DATANASC];    
        $da[$i][VALTOTLIQPAGO]= $d[VALTOTLIQPAGO];
        $da[$i][VALFRETEPAGOTOT]= $d[VALFRETEPAGOTOT];
        $da[$i][VALINSS]= $d[VALINSS];
        $da[$i][VALOBCINSSSEST]= $d[VALOBCINSSSEST];
    }
    return $da; 
}



function listarAbastecimento($ano=null, $mes=null, $placa=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT IDABAST, DATA_ABAST, LITROS, VALOR_ABAST,
                (ODOMETRO - ODOMETRO_ANTERIOR) AS KMRODADO
                FROM [dbo].[VW_ABASTECIMENTOS_DASHBOARD]
                WHERE  ID_COMBUSTIVEL=741 AND PLACA='$placa' AND YEAR(DATA_ABAST)=$ano AND MONTH(DATA_ABAST)=$mes

        ";
    $SQLeXEC = mssql_query($sql);
    $i=0;
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $da[$i][IDABAST]        = $d[IDABAST];
        $da[$i][DATA_ABAST]     = $d[DATA_ABAST];
        $da[$i][LITROS]         = $d[LITROS];
        $da[$i][VALOR_ABAST]    = $d[VALOR_ABAST];
        $da[$i][KMRODADO]       = $d[KMRODADO];
    }        
    return $da;         
}

function listaCteBipe($bipe){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT U_CODECTRC, U_BPLNAME_OBPL, U_DTEMISSAO, U_CARDNAME_OCRDCB,
                   U_DATAGENDACLI, U_HORAAGENDACLI
            FROM [@SIEGO_CTRC]
            WHERE U_DOCENTRY_BIPE = '$bipe'
        ";
    $SQLeXEC = mssql_query($sql);
    $i=0;
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $da[$i][FILIAL]     = $d[U_BPLNAME_OBPL];
        $da[$i][DocNum]     = $d[U_CODECTRC];
        $da[$i][DTEMISSAO]  = $d[U_DTEMISSAO];
        $da[$i][CLIENTE]    = $d[U_CARDNAME_OCRDCB];
        $da[$i][DTPREVCHEG] = $d[U_DATAGENDACLI];
        $da[$i][HPREVCHEG]  = $d[U_HORAAGENDACLI];
    }        
    return $da;
}

function ultimoBipe($placa=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT TOP 1 BP.DocNum, FL.BPLName FILIAL, U_DTEMISSAO DATAEMISSAO,
		(CASE 
			WHEN VEICULO.U_TIPOCONT = 1 THEN 'FROTA' 
			WHEN VEICULO.U_TIPOCONT = 2 THEN 'AGREGADO'
			WHEN VEICULO.U_TIPOCONT = 3 THEN 'TERCEIRO' 
		ELSE NULL END) TIPOCONTRATO,
		RT.U_NMOROCNT CIDADEORIGEM, RT.U_NMDEOCNT CIDADEDESTINO, 
		RT.U_ORIGOCST UFORIGEM, RT.U_DESTOCST UFDESTINO
            FROM [@SIEGO_BIPE] BP
            JOIN OBPL FL ON (FL.BPLId = BP.U_BPLID_OBPL)
            JOIN [@SIEGO_VEIC] VEICULO ON (VEICULO.Code = BP.U_CODE_VEIC)
            JOIN [@SIEGO_ROTA] RT ON (RT.Code = BP.U_CODE_ROTA)
          WHERE BP.Canceled='N' AND BP.U_TPDOC=0 AND BP.U_CODE_VEIC='$placa'
          ORDER BY BP.DocEntry DESC
            ";
    $SQLeXEC = mssql_query($sql);
    $i=0;
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $da[$i][DocNum]       = $d[DocNum];
        $da[$i][FILIAL]       = $d[FILIAL];
        $da[$i][DATAEMISSAO]  = $d[DATAEMISSAO]; 
        $da[$i][TIPOCONTRATO] = $d[TIPOCONTRATO];
        $da[$i][ORIGEM]       = $d[CIDADEORIGEM];
        $da[$i][UFORIGEM]     = $d[UFORIGEM];
        $da[$i][DESTINO]      = $d[CIDADEDESTINO];
        $da[$i][UFDESTINO]    = $d[UFDESTINO];
    }        
    return $da; 
}

function averbacaoFile($sigla=null, $numero=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT TOP 1 * FROM VW_FATURAMENTO_DASHBOARD
            WHERE IDFILIAL='$sigla' AND NUMERODOCTO='$numero' ";
    
    $diretorio = diretorioXML($sigla);
    $limpar = "\\zapsapnew\Cte$";
    $dir = str_replace($limpar, "", $diretorio);
    $dir = str_replace('\\', '/', $dir);
    $dir = str_replace(' ', '%20', $dir);
    
    $SQLeXEC = mssql_query($sql);
    while($d = mssql_fetch_array($SQLeXEC)){
        $da[IDFILIAL]      = $d[IDFILIAL];
        $da[NOMEFILIAL]    = $d[NOMEFILIAL];
        $da[NUMERODOCTO]   = $d[NUMERODOCTO];
        $da[PLACA]         = $d[PLACA];
        $da[DATAEMISDOCTO] = $d[DATAEMISDOCTO];
        $da[TIPOCONTRATO]  = $d[TIPOCONTRATO];
        $da[NOMECLIENTE]   = $d[NOMECLIENTE];
        $da[CNPJDEST]      = $d[CNPJDEST];
        $da[IDDOCTO]       = $d[IDDOCTO];
    }
    
    $da[CNPJDEST] = str_replace('.', '', $da[CNPJDEST]);
    $da[CNPJDEST] = str_replace('/', '', $da[CNPJDEST]);
    $da[CNPJDEST] = str_replace('-', '', $da[CNPJDEST]);
            
    $dataDia = date('d-m-Y', strtotime($da[DATAEMISDOCTO]));        
    $file = 'http://bid_d.zappellini.com.br/ctesap'.$dir.'/XML'.$da[CNPJDEST].$dataDia.$da[NUMERODOCTO].'.XML';
                        
    return $file;
}

function protocoloAverbacaoSAP($DocEntry, $protocolo){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "UPDATE [@SIEGO_CTRA]
                SET U_NUMAVEB='$protocolo'
                WHERE DocEntry=$DocEntry ";
    $SQLeXEC = mssql_query($sql);
       
    return true;
}

function faturadoValorTotal($ano=null, $mes=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT SUM(U_VALOR_TOTAL) VALORTOTAL FROM [@SIEGO_FTRA]
                WHERE YEAR(CreateDate)=$ano AND MONTH(CreateDate)=$mes AND Canceled ='N'
        ";
    $SQLeXEC = mssql_query($sql);
    while($d = mssql_fetch_array($SQLeXEC)){
        $da = $d[VALORTOTAL];
    }        
    return $da;
}

function buscaMotoristaMulta($placa=null, $data=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT TOP 1 createdate, U_EMPID_OHEM, (ohem.firstname + ' ' + ohem.lastName ) as NOME, CPF
                FROM [@SIEGO_HVEI] 
                left join ohem on ohem.empID = U_EMPID_OHEM 
                where U_CODE_VEIC = '$placa' AND CreateDate <= '$data'
                order by DocEntry DESC
        ";
    $SQLeXEC = mssql_query($sql);
    if(mssql_num_rows($SQLeXEC) == 0){
        $sql = "SELECT TOP 1 V.CreateDate, V.U_EMPID_OHEM, (M.firstName + ' ' + M.lastName) as NOME, M.CPF as CPF
                    FROM [@SIEGO_HVEI] V
                    JOIN [@SIEGO_HVRQ] R ON (R.U_CODEVEIC = V.U_CODE_VEIC)
                    JOIN [OHEM] M ON (M.empID = V.U_EMPID_OHEM)
                    WHERE R.U_CODEREBQ = '$placa' AND V.CreateDate <= '$data'
                ";
    }
    $SQLeXEC = mssql_query($sql);
    while($d = mssql_fetch_array($SQLeXEC)){
        $da = $d[CPF].' - '.$d[NOME];
    } 
    return $da;
}

function cteSemBipeFilial(){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT U_BPLID_OBPL IDFILIAL, (SELECT TOP 1 BPLName FROM [OBPL] WHERE BPLId=U_BPLID_OBPL) FILIAL, COUNT(*) QUANTIDADE
                FROM [@SIEGO_CTRC]
                WHERE U_STATUSCT = 1 AND U_DOCENTRY_BIPE =0
                GROUP BY U_BPLID_OBPL
                ORDER BY QUANTIDADE DESC
        ";
    $SQLeXEC = mssql_query($sql);
    $i=0;
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $da[$i][IDFILIAL]   = $d[IDFILIAL];
        $da[$i][FILIAL]     = $d[FILIAL];
        $da[$i][QUANTIDADE] = $d[QUANTIDADE];
    } 
    return $da;
}

function cteSemBipeDiario(){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT U_BPLNAME_OBPL, U_CODECTRC, U_CODE_VEIC, CreateDate, CreateTime, Creator FROM [@SIEGO_CTRC]
                WHERE U_STATUSCT = 1 AND U_DOCENTRY_BIPE =0
                      AND day(CreateDate)=day(GETDATE()) AND MONTH(CreateDate)=MONTH(GETDATE()) AND YEAR(CreateDate) = YEAR(GETDATE())
            ";
    
    $SQLeXEC = mssql_query($sql);
    $i=0;
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $da[$i][FILIAL]     = $d[U_BPLNAME_OBPL];
        $da[$i][NUMERO]     = $d[U_CODECTRC];
        $da[$i][PLACA]      = $d[U_CODE_VEIC];
        $da[$i][DATA]       = $d[CreateDate];
        $da[$i][HORA]       = $d[CreateTime];
        $da[$i][USUARIO]    = $d[Creator];
    } 
    return $da;
}

function cteSemBipeMesAtual(){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT COUNT(*) as qtda
                FROM [@SIEGO_CTRC]
                WHERE U_STATUSCT = 1 AND U_DOCENTRY_BIPE =0
                      AND MONTH(CreateDate)=MONTH(GETDATE()) AND YEAR(CreateDate) = YEAR(GETDATE())
            ";
    
    $SQLeXEC = mssql_query($sql);
    while($d = mssql_fetch_array($SQLeXEC)){
        $da =  $d[qtda];        
    } 
    return $da;
}

function listaCteSemBipe($idFilial){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT U_CODECTRC, Creator, U_DTEMISSAO, U_CODE_VEIC, U_CARDNAME_OCRDCB
                FROM [@SIEGO_CTRC]
                WHERE U_STATUSCT = 1 AND U_DOCENTRY_BIPE =0 AND U_BPLID_OBPL=$idFilial
        ";
    $SQLeXEC = mssql_query($sql);
    $i=0;
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $da[$i][U_CODECTRC]         = $d[U_CODECTRC];
        $da[$i][Creator]            = $d[Creator];
        $da[$i][U_DTEMISSAO]        = $d[U_DTEMISSAO];
        $da[$i][U_CODE_VEIC]        = $d[U_CODE_VEIC];
        $da[$i][U_CARDNAME_OCRDCB]  = $d[U_CARDNAME_OCRDCB];
    } 
    return $da;
}

function buscaMdfe($idFIilial, $numDocto){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT CTRC.U_DOCENTRY_BIPE, MDFE.U_CODE_MDFE MDFE, CTRC.U_BPLID_OBPL IDFILIAL, 
                    DAY(MDFE.U_DATAEMISSAO) DIA, MONTH(MDFE.U_DATAEMISSAO) MES, YEAR(MDFE.U_DATAEMISSAO) ANO 
                FROM [@SIEGO_CTRC] CTRC
                JOIN [@SIEGO_MDFE] MDFE ON (MDFE.U_DOCENTRY_BIPE = CTRC.U_DOCENTRY_BIPE AND MDFE.U_BPLID_OBPL=CTRC.U_BPLID_OBPL)
                WHERE CTRC.U_CODECTRC = $numDocto AND CTRC.U_BPLID_OBPL=$idFIilial
        ";
    $SQLeXEC = mssql_query($sql);
    $i=0;
    while($d = mssql_fetch_array($SQLeXEC)){
        if($d[DIA] < 10){
            $d[DIA] = '0'.$d[DIA];
        }
        if($d[MES] < 10){
            $d[MES] = '0'.$d[MES];
        }
        $i++;
        $da[$i][MDFE]          = $d[MDFE];
        $da[$i][DIA]           = $d[DIA];
        $da[$i][MES]           = $d[MES];
        $da[$i][ANO]           = $d[ANO];
    } 
    return $da;
}

function receitaMotorista($ano=null, $mes=null){
     include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT CPFMOTORIS CPF, NOMEMOTORIS NOME, SUM(FRETEPESO) FRETEPESO, SUM(VALORCOMIMPOSTO) FRETETOTAL FROM VW_FATURAMENTO_DASHBOARD
                WHERE MONTH(DATAEMISDOCTO)=$mes AND YEAR(DATAEMISDOCTO)=$ano
                GROUP BY CPFMOTORIS, NOMEMOTORIS
                ORDER BY SUM(VALORCOMIMPOSTO) DESC
        ";
    $SQLeXEC = mssql_query($sql);
    $i=0;
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $da[$i][CPF]          = $d[CPF];
        $da[$i][NOME]           = $d[NOME];
        $da[$i][FRETEPESO]           = $d[FRETEPESO];
        $da[$i][FRETETOTAL]           = $d[FRETETOTAL];
    } 
    return $da;
}

function buscaSeguradora($code){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT * FROM [@SIEGO_SEGL]
                WHERE Code = '$code'
        ";
    $SQLeXEC = mssql_query($sql);
    while($d = mssql_fetch_array($SQLeXEC)){
        if($d[U_CODESEGR] == null){
            $da = false;
        }else{
            $da = $d[U_CODESEGR];
        }
    } 
    return $da;
}

function buscaNomeSeguradoraCtrc($sigla, $numero){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT S.U_NOMESEGR
                FROM [@SIEGO_CTRC] C
                LEFT JOIN [@SIEGO_SEGL] S ON (S.Code = C.U_CARDCODE_OCRDCB)
                WHERE C.U_BPLID_OBPL='$sigla' AND C.U_CODECTRC= '$numero' 
        ";
    $SQLeXEC = mssql_query($sql);
    while($d = mssql_fetch_array($SQLeXEC)){
        $da = $d[U_NOMESEGR];
    }
    return $da;
}

function listaCliente(){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT CardName, U_SIE_CNPJCPF FROM OCRD
        ";
    $SQLeXEC = mssql_query($sql);
    $i=0;
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $da[$i][CardName] = $d[CardName];
        $da[$i][U_SIE_CNPJCPF] = $d[U_SIE_CNPJCPF];
    }
    return $da;
}

function listaFormaPagamentoBipe(){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT GroupNum id, PymntGroup descricao FROM OCTG 
        ";
    $SQLeXEC = mssql_query($sql);
    $i=0;
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $da[$i][ID] = $d[id];
        $da[$i][DESCRICAO] = $d[descricao];
    }
    return $da;
}

function buscaNomeFormaPagamentoBipe($id){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT TOP 1 PymntGroup FROM OCTG WHERE GroupNum=$id 
        ";
    $SQLeXEC = mssql_query($sql);
    while($d = mssql_fetch_array($SQLeXEC)){
        $da = $d[PymntGroup];
    }
    return $da;
}

function totalAdicEntregaOperadorLog($OperadorLogistico=null, $ano=null, $mes=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT SUM(CTRC.U_VALADENT) as TOTAL
        FROM [@SIEGO_CTRC] CTRC
        JOIN [@SIEGO_VEIC] VEICULO ON (VEICULO.Code = CTRC.U_CODE_VEIC)
        LEFT JOIN [@SIEGO_OPER] OPERADOR ON (OPERADOR.Code = VEICULO.U_CODE_OPER)
        WHERE CTRC.U_STATUSCT = 1 AND CTRC.U_DOCENTRY_BIPE <> 0 
    ";
    if($OperadorLogistico != null){
        $sql .= " AND OPERADOR.Code='$OperadorLogistico'";
    }else{
        $sql .= " AND OPERADOR.Code is not null";
    }
    if($ano != null){
        $sql .= " AND YEAR(CTRC.CreateDate)=$ano";
    }
    if($mes != null){
        $sql .= " AND MONTH(CTRC.CreateDate) = $mes";
    }
    
    $SQLeXEC = mssql_query($sql);
    while($d = mssql_fetch_array($SQLeXEC)){
        $da = $d[TOTAL];
    }
    return $da;
}

function quantidadeCargasPendente(){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT COUNT(*) TOTAL FROM [@SIEGO_PRCA]
                WHERE U_STATUS='0' AND U_CODE_VEIC = ''
    ";
    $SQLeXEC = mssql_query($sql);
    while($d = mssql_fetch_array($SQLeXEC)){
        $da = $d[TOTAL];
    }
    return $da;
}

function quantidadeCargasProgramada(){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT COUNT(*) TOTAL FROM [@SIEGO_PRCA]
                WHERE U_STATUS='0' AND U_CODE_VEIC != ''
    ";
    $SQLeXEC = mssql_query($sql);
    while($d = mssql_fetch_array($SQLeXEC)){
        $da = $d[TOTAL];
    }
    return $da;
}

function quantidadeCargasEmAndamento(){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT COUNT(*) TOTAL FROM [@SIEGO_PRCA] PROG
                JOIN [@SIEGO_CTRC] CTRC ON (CTRC.DocEntry = PROG.U_DOCENTRY_CTRC)
                JOIN [@SIEGO_BIPE] BIPE ON (BIPE.DocEntry = CTRC.U_DOCENTRY_BIPE)
                WHERE PROG.U_STATUS=1 AND BIPE.U_STATUS=0
    ";
    $SQLeXEC = mssql_query($sql);
    while($d = mssql_fetch_array($SQLeXEC)){
        $da = $d[TOTAL];
    }
    return $da;
}

function listaFilialCargasPendente(){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT U_BPLID_OBPL, U_BPLNAME_OBPL FROM [@SIEGO_PRCA]
                WHERE U_STATUS='0' AND U_CODE_VEIC = ''
		GROUP BY  U_BPLID_OBPL, U_BPLNAME_OBPL 
        ";
    $SQLeXEC = mssql_query($sql);
    $i=0;
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $da[$i][ID] = $d[U_BPLID_OBPL];
        $da[$i][FILIAL] = $d[U_BPLNAME_OBPL];
    }
    return $da;
}

function listaCargasPendenteFilial($idFilial=NULL){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT PROG.DocNum, PROG.U_PESO, CONVERT(varchar, PROG.U_DTEMBARQUE, 103) U_DTEMBARQUE, PROG.U_CARDNAME_OCRDRM,
            REME.Name REMTENTECITY, DEST.Name DESTINATARIOCITY, PROG.U_CARDNAME_OCRDRM
            FROM [@SIEGO_PRCA] PROG
            JOIN OCNT REME ON (REME.AbsId = PROG.U_CODE_OCNTOR)
            JOIN OCNT DEST ON (DEST.AbsId = PROG.U_CODE_OCNTDT)
                WHERE U_STATUS='0' AND U_CODE_VEIC = '' AND U_BPLID_OBPL=$idFilial
        ";
    $SQLeXEC = mssql_query($sql);
    $i=0;
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $da[$i][DOCNUM]             = $d[DocNum];
        $da[$i][PESO]               = $d[U_PESO];
        $da[$i][COLETA]             = $d[U_DTEMBARQUE];
        $da[$i][REMETENTECIDADE]    = $d[REMTENTECITY];
        $da[$i][DESTINATARIOCIDADE] = $d[DESTINATARIOCITY];
        $da[$i][CLIENTE]            = $d[U_CARDNAME_OCRDRM];
    }
    return $da;
}

function listaFilialCargasProgramada(){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT U_BPLID_OBPL, U_BPLNAME_OBPL FROM [@SIEGO_PRCA]
                WHERE U_STATUS='0' AND U_CODE_VEIC != ''
		GROUP BY U_BPLID_OBPL, U_BPLNAME_OBPL 
        ";
    $SQLeXEC = mssql_query($sql);
    $i=0;
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $da[$i][ID] = $d[U_BPLID_OBPL];
        $da[$i][FILIAL] = $d[U_BPLNAME_OBPL];
    }
    return $da;
}

function listaCargasProgramadaFilial($idFilial=NULL){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT PROG.DocNum, PROG.U_PESO, CONVERT(varchar, PROG.U_DTEMBARQUE, 103) U_DTEMBARQUE, PROG.U_CARDNAME_OCRDRM,
            REME.Name REMTENTECITY, DEST.Name DESTINATARIOCITY, PROG.U_CARDNAME_OCRDRM, PROG.U_CODE_VEIC PLACA
            FROM [@SIEGO_PRCA] PROG
            JOIN OCNT REME ON (REME.AbsId = PROG.U_CODE_OCNTOR)
            JOIN OCNT DEST ON (DEST.AbsId = PROG.U_CODE_OCNTDT)
                WHERE U_STATUS='0' AND U_CODE_VEIC != '' AND U_BPLID_OBPL=$idFilial
        ";
    $SQLeXEC = mssql_query($sql);
    $i=0;
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $da[$i][DOCNUM]             = $d[DocNum];
        $da[$i][PESO]               = $d[U_PESO];
        $da[$i][COLETA]             = $d[U_DTEMBARQUE];
        $da[$i][REMETENTECIDADE]    = $d[REMTENTECITY];
        $da[$i][DESTINATARIOCIDADE] = $d[DESTINATARIOCITY];
        $da[$i][CLIENTE]            = $d[U_CARDNAME_OCRDRM];
        $da[$i][PLACA]              = $d[PLACA];
    }
    return $da;
}

function receitaFretePesoCIcms($ano=null, $mes=null, $dia=null, $tipoContrato=null, $placa=null, $idFilial=null, $semPlaca=null, $vendaImobilizado=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT SUM(FRETEPESOCICMS)
                                FROM VW_FATURAMENTO_DASHBOARD
                                ";
    if ($ano != null) {
        $sql .= 'WHERE YEAR(DATAEMISDOCTO)=' . $ano;
    }
    if ($mes != null) {
        $sql .= ' AND MONTH(DATAEMISDOCTO)=' . $mes;
    }
    if ($dia != null) {
        $sql .= ' AND DAY(DATAEMISDOCTO)=' . $dia;
    }
    //TIPO CONTRATO (AGREGADO, FROTA,TERCEIRO)
    if ($tipoContrato != null) {
        $sql .= ' AND TIPOCONTRATO="' . $tipoContrato . '"';
    }
    //PLACA
    if($placa != null){
        $sql .= ' AND PLACA="' . $placa . '"';
    }
    if($idFilial != null){
        $sql .= ' AND IDFILIAL="' . $idFilial . '"';
    }
    if($semPlaca != null){
        $sql .= ' AND PLACA is NULL ';
    }
    if($vendaImobilizado == null){
        $sql .= " AND TIPODOCTO <> 'NFVENDA'";
    }

    $sql_receitaFretePeso = mssql_query($sql);
    $dados = mssql_fetch_array($sql_receitaFretePeso);
    if ($dados[0] == null) {
        $dados[0] = 0;
    }
    return $dados[0];
}

function listaBonusFrota($dtInicial, $dtFinal, $conjunto){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    
    $sql = "EXEC SIE_BONUSFROTA '".$dtInicial."', '".$dtFinal."'";
    
    $SQLeXEC = mssql_query($sql);
    $i=0;
    
   
    while($d = mssql_fetch_array($SQLeXEC)){
        
        if($conjunto == $d[0]){
            $i++;
            $da[$i][CONJUNTO]        = $d[0];
            $da[$i][MOTORISTA]       = $d[1];
            $da[$i][MATRICULA]       = $d[2];
            $da[$i][PLACA]           = $d[5];           
            $da[$i][FRETEPESO]       = $d[6];
            $da[$i][MEDIA]           = $d[7];
            $da[$i][BCOMPORTAMENTO]  = $d[8];
            $da[$i][BFATURAMENTO]    = $d[9];           
            $da[$i][BMEDIA]          = $d[10];
            $da[$i][TOTALBONUS]      = $d[11];
            
        }if($conjunto =='todos'){
            $i++;
            $da[$i][CONJUNTO]        = $d[0];
            $da[$i][MOTORISTA]       = $d[1];
            $da[$i][MATRICULA]       = $d[2];
            $da[$i][PLACA]           = $d[5];           
            $da[$i][FRETEPESO]       = $d[6];
            $da[$i][MEDIA]           = $d[7];
            $da[$i][BCOMPORTAMENTO]  = $d[8];
            $da[$i][BFATURAMENTO]    = $d[9];           
            $da[$i][BMEDIA]          = $d[10];
            $da[$i][TOTALBONUS]      = $d[11];        
        }        
    }   
    
    return $da;    
}

function listaBonusMatricula($dtInicial, $dtFinal){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    
    $sql = "EXEC SIE_BONUSFROTA '".$dtInicial."', '".$dtFinal."'";
    
    $SQLeXEC = mssql_query($sql);
    $i=0;
    
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $da[$i][TOTALBONUS]      = $d[11];
        $da[$i][MATRICULA]       = $d[2];
        //var_dump($d);
    }
    
    return $da;
}

function listaClientesFaturamento($ano=null, $mes=null, $dia=null, $tipoContrato=null, $vendaImobilizado=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
           
    $sql = "SELECT NOMECLIENTE, SUM(FRETEPESOCICMS) as TOTAL, SUM(FRETEPESO) as FRETEPESO,
                CAST(CNPJCLIENTE as VARCHAR(8)) AS CNPJ,
                SUM(CASE WHEN TIPOCONTRATO = 'F' THEN FRETEPESOCICMS ELSE 0 END) AS FRETEPESOFROTA,
                SUM(CASE WHEN TIPOCONTRATO = 'A' THEN FRETEPESOCICMS ELSE 0 END) AS FRETEPESOAGREGAO,
                SUM(CASE WHEN TIPOCONTRATO = 'T' THEN FRETEPESOCICMS ELSE 0 END) AS FRETEPESOTERCEIRO                    
             FROM VW_FATURAMENTO_DASHBOARD ";
    
    if ($ano != null) {
        $sql .= 'WHERE YEAR(DATAEMISDOCTO)=' . $ano;
    }
    if ($mes != null) {
        $sql .= ' AND MONTH(DATAEMISDOCTO)=' . $mes;
    }
    if ($dia != null) {
        $sql .= ' AND DAY(DATAEMISDOCTO)=' . $dia;
    }
    //TIPO CONTRATO (AGREGADO, FROTA,TERCEIRO)
    if ($tipoContrato != null) {
        $sql .= ' AND TIPOCONTRATO="' . $tipoContrato . '"';
    }    
    if($vendaImobilizado == null){
        $sql .= ' AND TIPODOCTO <> "NFVENDA"';
    }
    $sql .= ' GROUP BY NOMECLIENTE, CAST(CNPJCLIENTE as VARCHAR(8)) ORDER BY TOTAL DESC ;';    
        
    $SQLeXEC = mssql_query($sql);
    $i=0;
    $dados=null;
    while($d = mssql_fetch_array($SQLeXEC)){
        if($d[TOTAL] > 1){
            $i++;
            $dados[$i][FRETEPESO]         = $d[FRETEPESO];
            $dados[$i][CNPJ]              = $d[CNPJ];
            $dados[$i][NOMECLIENTE]       = $d[NOMECLIENTE];
            $dados[$i][TOTAL]             = $d[TOTAL];
            $dados[$i][FRETEPESOFROTA]    = $d[FRETEPESOFROTA];
            $dados[$i][FRETEPESOAGREGAO]  = $d[FRETEPESOAGREGAO];
            $dados[$i][FRETEPESOTERCEIRO] = $d[FRETEPESOTERCEIRO];
        }        
    }
    return $dados;
}

function listaReceitaClienteFilial($ano=null, $mes=null, $cliente=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    
    $sql = "SELECT NOMECLIENTE, NOMEFILIAL , SUM(FRETEPESO) AS TOTAL, 
        SUM(FRETEPESOCICMS) AS TOTALICMS,
        SUM(CASE WHEN TIPOCONTRATO = 'F' THEN FRETEPESO ELSE 0 END) AS FRETEPESOFROTA,
	SUM(CASE WHEN TIPOCONTRATO = 'A' THEN FRETEPESO ELSE 0 END) AS FRETEPESOAGREGAO,
	SUM(CASE WHEN TIPOCONTRATO = 'T' THEN FRETEPESO ELSE 0 END) AS FRETEPESOTERCEIRO
	FROM VW_FATURAMENTO_DASHBOARD
        WHERE CNPJCLIENTE LIKE '$cliente%'
    ";
    
    if ($ano != null) {
        $sql .= ' AND YEAR(DATAEMISDOCTO)=' . $ano;
    }
    if ($mes != null) {
        $sql .= ' AND MONTH(DATAEMISDOCTO)=' . $mes;
    }
    $sql .= ' GROUP BY NOMEFILIAL, NOMECLIENTE ORDER BY TOTAL DESC ;';
    
    
    $SQLeXEC = mssql_query($sql);
    $i=0;
    $dados=null;
    while($d = mssql_fetch_array($SQLeXEC)){
        if($d[TOTAL] > 1){
            $i++;
            $dados[$i][NOMECLIENTE]       = $d[NOMECLIENTE];
            $dados[$i][NOMEFILIAL]        = $d[NOMEFILIAL];
            $dados[$i][FRETEPESO]         = $d[TOTAL];
            $dados[$i][FRETEPESOCICMS]    = $d[TOTALICMS];
            $dados[$i][FRETEPESOFROTA]    = $d[FRETEPESOFROTA];
            $dados[$i][FRETEPESOAGREGAO]  = $d[FRETEPESOAGREGAO];
            $dados[$i][FRETEPESOTERCEIRO] = $d[FRETEPESOTERCEIRO];
        }        
    }
    return $dados;
}

function fretePesoCliente($ano=null, $mes=null, $dia=null, $cliente=null, $tipoContrato=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    
    $sql = "SELECT SUM(FRETEPESO) AS TOTAL, SUM(FRETEPESOCICMS) AS TOTALICMS,
                SUM(CASE WHEN TIPOCONTRATO = 'F' THEN FRETEPESO ELSE 0 END) AS FRETEPESOFROTA,
                SUM(CASE WHEN TIPOCONTRATO = 'A' THEN FRETEPESO ELSE 0 END) AS FRETEPESOAGREGAO,
                SUM(CASE WHEN TIPOCONTRATO = 'T' THEN FRETEPESO ELSE 0 END) AS FRETEPESOTERCEIRO
                FROM VW_FATURAMENTO_DASHBOARD";
    if ($ano != null) {
        $sql .= ' WHERE YEAR(DATAEMISDOCTO)=' . $ano;
    }
    if ($mes != null) {
        $sql .= ' AND MONTH(DATAEMISDOCTO)=' . $mes;
    }
    if ($dia != null) {
        $sql .= ' AND DAY(DATAEMISDOCTO)=' . $dia;
    }if ($cliente != null) {
        $sql .= " AND CNPJCLIENTE LIKE '$cliente%'";
    }
    if ($tipoContrato != null) {
        $sql .= ' AND TIPOCONTRATO="' . $tipoContrato . '"';
    }
    
    $SQLeXEC = mssql_query($sql);
    $i=0;
    $dados=null;
    while($d = mssql_fetch_array($SQLeXEC)){
        if($d[TOTAL] > 1){
            $i++;
            $dados[$i][FRETEPESO]         = $d[TOTAL];
            $dados[$i][FRETEPESOCICMS]    = $d[TOTALICMS];
            $dados[$i][FRETEPESOFROTA]    = $d[FRETEPESOFROTA];
            $dados[$i][FRETEPESOAGREGAO]  = $d[FRETEPESOAGREGAO];
            $dados[$i][FRETEPESOTERCEIRO] = $d[FRETEPESOTERCEIRO];
        }        
    }
    return $dados;
}

function listaEquipamentosClientes($ano=null, $mes=null, $cliente=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    
    $sql = "SELECT CONJUNTO, COUNT(CONJUNTO) AS TOTAL
	FROM VW_FATURAMENTO_DASHBOARD";
    
    if ($ano != null) {
        $sql .= ' WHERE YEAR(DATAEMISDOCTO)=' . $ano;
    }
    if ($mes != null) {
        $sql .= ' AND MONTH(DATAEMISDOCTO)=' . $mes;
    }
    if ($cliente != null) {
        $sql .= " AND CNPJCLIENTE LIKE '$cliente%'";
    }
        $sql .= " GROUP BY CONJUNTO ORDER BY TOTAL DESC";
    
    
    $SQLeXEC = mssql_query($sql);
    $i=0;
    $dados=null;
    while($d = mssql_fetch_array($SQLeXEC)){
        if($d[TOTAL] > 1){
            $i++;
            $dados[$i][CONJUNTO]    = $d[CONJUNTO];
            $dados[$i][TOTAL]       = $d[TOTAL];            
        }        
    }
    return $dados;    
}

function receitaFilialSAP($ano=null, $mes=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    
    $sql .= "SELECT FILIAL.BPLId, FILIAL.BPLName,
	(SELECT SUM(FRETEPESO)	
                FROM VW_FATURAMENTO_DASHBOARD VW 
                WHERE IDFILIAL= FILIAL.BPLId AND YEAR(VW.DATAEMISDOCTO)=$ano AND MONTH(DATAEMISDOCTO)=$mes) AS FRETEPESO, 
	(SELECT SUM(CASE WHEN TIPOCONTRATO = 'F' THEN FRETEPESO ELSE 0 END)	
                FROM VW_FATURAMENTO_DASHBOARD VW 
                WHERE IDFILIAL= FILIAL.BPLId AND YEAR(VW.DATAEMISDOCTO)=$ano AND MONTH(DATAEMISDOCTO)=$mes) AS FRETEFROTA,
	(SELECT SUM(CASE WHEN TIPOCONTRATO = 'A' THEN FRETEPESO ELSE 0 END)	
                FROM VW_FATURAMENTO_DASHBOARD VW 
                WHERE IDFILIAL= FILIAL.BPLId AND YEAR(VW.DATAEMISDOCTO)=$ano AND MONTH(DATAEMISDOCTO)=$mes) AS FRETEAGREGADO,
	(SELECT SUM(CASE WHEN TIPOCONTRATO = 'T' THEN FRETEPESO ELSE 0 END)	
                FROM VW_FATURAMENTO_DASHBOARD VW 
                WHERE IDFILIAL= FILIAL.BPLId AND YEAR(VW.DATAEMISDOCTO)=$ano AND MONTH(DATAEMISDOCTO)=$mes) AS FRETETERCEIRO 
	FROM OBPL FILIAL ORDER BY FRETEPESO DESC";
    
    $SQLeXEC = mssql_query($sql);
    $i=0;
    $dados=null;
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $dados[$i][NOME]            = $d[BPLName];
        $dados[$i][FRETEPESO]       = $d[FRETEPESO];
        $dados[$i][FRETEFROTA]      = $d[FRETEFROTA];
        $dados[$i][FRETEAGREGADO]   = $d[FRETEAGREGADO];
        $dados[$i][FRETETERCEIRO]   = $d[FRETETERCEIRO]; 
               
    }
    return $dados; 
    
    
}

function receitaOperadoresLogistico($ano=null, $mes=null){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT Code, Name, 
                (SELECT SUM(FRETEPESO) VALOR FROM VW_FATURAMENTO_DASHBOARD WHERE IDOPERADOR=OPER.Code AND TIPOCONTRATO='A' AND MONTH(DATAEMISDOCTO)=$mes AND YEAR(DATAEMISDOCTO)=$ano) as FRETEPESOAGREGADO,
                (SELECT SUM(FRETEPESO) VALOR FROM VW_FATURAMENTO_DASHBOARD WHERE IDOPERADOR=OPER.Code AND TIPOCONTRATO='F' AND MONTH(DATAEMISDOCTO)=$mes AND YEAR(DATAEMISDOCTO)=$ano) as FRETEPESOFROTA,
                (SELECT SUM(FRETEPESO) VALOR FROM VW_FATURAMENTO_DASHBOARD WHERE IDOPERADOR=OPER.Code AND TIPOCONTRATO='T' AND MONTH(DATAEMISDOCTO)=$mes AND YEAR(DATAEMISDOCTO)=$ano) as FRETEPESOTERCEIRO,
                (SELECT SUM(FRETEPESO) VALOR FROM VW_FATURAMENTO_DASHBOARD WHERE IDOPERADOR=OPER.Code AND MONTH(DATAEMISDOCTO)=$mes AND YEAR(DATAEMISDOCTO)=$ano) as FRETEPESO
                FROM [@SIEGO_OPER] OPER";
    
    $SQLeXEC = mssql_query($sql);
    $i=0;
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $da[$i][CODE]              = $d[Code];
        $da[$i][NOME]              = $d[Name];
        $da[$i][FRETEPESOAGREGADO] = $d[FRETEPESOAGREGADO];
        $da[$i][FRETEPESOFROTA]    = $d[FRETEPESOFROTA];
        $da[$i][FRETEPESOTERCEIRO] = $d[FRETEPESOTERCEIRO];
        $da[$i][FRETEPESO]         = $d[FRETEPESO];
    }
    return $da;
}

function dashboardFretePeso($ano=null, $mes=null, $dia=NULL){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SELECT 
                    SUM(CASE WHEN YEAR(DATAEMISDOCTO)=$ano THEN FRETEPESO ELSE 0 END) AS FRETEPESOANO,
                    SUM(CASE WHEN YEAR(DATAEMISDOCTO)=$ano AND MONTH(DATAEMISDOCTO)=$mes THEN FRETEPESO ELSE 0 END) AS FRETEPESOMES,
                    SUM(CASE WHEN YEAR(DATAEMISDOCTO)=$ano AND MONTH(DATAEMISDOCTO)=$mes AND DAY(DATAEMISDOCTO)=$dia THEN FRETEPESO ELSE 0 END) AS FRETEPESODIA
            FROM VW_FATURAMENTO_DASHBOARD
        ";
    
    $i=0;
    $SQLeXEC = mssql_query($sql);
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $da[$i][FRETEPESOMES] = $d[FRETEPESOMES];
        $da[$i][FRETEPESODIA] = $d[FRETEPESODIA];
        $da[$i][FRETEPESOANO] = $d[FRETEPESOANO];
    }
    return $da;
}

function dashboardGraf($ano){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    $sql = "SET LANGUAGE 'Brazilian'
                ;WITH Meses AS
                (
                  SELECT 1 AS IdMes, DATENAME(MONTH, 0) AS NomeMes
                  UNION ALL
                  SELECT IdMes + 1, DATENAME(MONTH, DATEADD(MONTH, IdMes, 0))
                  FROM Meses
                  WHERE IdMes < 12
                )
                SELECT *, (SELECT SUM(FRETEPESO)/100000
                                            FROM VW_FATURAMENTO_DASHBOARD
                                    WHERE YEAR(DATAEMISDOCTO)=$ano
                                    AND MONTH(DATAEMISDOCTO)=Meses.IdMes) as FRETEPESOMES
                FROM Meses
                ORDER BY IdMes
        ";
    
    $i=0;
    $SQLeXEC = mssql_query($sql);
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $da[$i][IdMes] = $d[IdMes];
        $da[$i][NomeMes] = $d[NomeMes];
        $da[$i][FRETEPESOMES] = $d[FRETEPESOMES];
    }
    return $da;
    
}

function dashboardMixFaturamento($ano, $mes, $dia){
    include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_sap.php';
    
    $sql = "SELECT SUM(FRETEPESO) AS TOTAL ,  
            SUM(CASE WHEN TIPOCONTRATO = 'F' THEN FRETEPESOCICMS ELSE 0 END) AS FRETEPESOFROTA, 
            SUM(CASE WHEN TIPOCONTRATO = 'A' THEN FRETEPESOCICMS ELSE 0 END) AS FRETEPESOAGREGADO, 
            SUM(CASE WHEN TIPOCONTRATO = 'T' THEN FRETEPESOCICMS ELSE 0 END) AS FRETEPESOTERCEIRO 
	FROM VW_FATURAMENTO_DASHBOARD " ;
    
    if($ano != null){
        $sql .= "WHERE YEAR(DATAEMISDOCTO)= " .$ano;        
    }if($mes != null){
        $sql .= "AND MONTH(DATAEMISDOCTO)= " .$mes;
    }if($dia != null){
        $sql .= "AND DAY(DATAEMISDOCTO)= " .$dia;
    }
    
    $SQLeXEC = mssql_query($sql);
    $i=0;
    $dados=null;
    while($d = mssql_fetch_array($SQLeXEC)){
        $i++;
        $dados[$i][TOTAL]               = $d[TOTAL];
        $dados[$i][FRETEPESOFROTA]      = $d[FRETEPESOFROTA];
        $dados[$i][FRETEPESOAGREGADO]   = $d[FRETEPESOAGREGADO];
        $dados[$i][FRETEPESOTERCEIRO]   = $d[FRETEPESOTERCEIRO];
    }
    return $dados;
    
}

?>