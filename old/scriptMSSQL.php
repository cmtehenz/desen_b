<?php
    // Nome do mês selecionado
    function mesSelecionado($x){
        include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
        $sql = "SELECT TOP 1 * FROM mes WHERE id_mes=$x";
        $SQLeXEC = mssql_query($sql);
        while($d = mssql_fetch_array($SQLeXEC)){
                $da[NOME]      = $d[descricao];
        }

        return $da[NOME];
    }    
    
    function buscaUsuarioFilial($idUsuario, $idFilial){
        include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
        $sqlUsuarioFilial = "SELECT *
            FROM usuarioFilial WHERE idUsuario='$idUsuario' AND idFilial='$idFilial' ";
        $sqlEXEC = mssql_query($sqlUsuarioFilial);
        return mssql_num_rows($sqlEXEC);
    }
    
    function listaAno($ano=null){
        include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
        $sql = "SELECT * FROM ano";
        if($ano != null){
            $sql .= " WHERE ano <> $ano ";
        }
        //ORDER
        $sql .= " ORDER BY ano DESC";
        $SQLeXEC = mssql_query($sql);
        $i=0;
        while($d = mssql_fetch_array($SQLeXEC)){
                $i++;
                $da[$i][ANO]      = $d[ano];
        }
        return $da;
    }
    
    function listaMes($mes=null){
        include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
        $sql = "SELECT * FROM mes";
        if($mes != null){
            $sql .= " WHERE id_mes <> $mes ";
        }
        //ORDER
        
        $SQLeXEC = mssql_query($sql);
        $i=0;
        while($d = mssql_fetch_array($SQLeXEC)){
                $i++;
                $da[$i][ID_MES]   = $d[id_mes];
                $da[$i][MES]      = $d[descricao];
        }
        return $da;
    }
    
    function buscaPerfilUsuario($idUsuario){
        include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
        $sql = "SELECT TOP 1 * FROM usuario WHERE id_usuario=$idUsuario";
        $SQLeXEC = mssql_query($sql);
        while($dados = mssql_fetch_array($SQLeXEC)){
            $da = $dados['perfil'];
        }
        return $da;
    }
    
    function valorPrevistoOperLog($idOperLog=null, $ano=null, $mes=null){
        include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
        $sql = "SELECT TOP 1 valor FROM previstoOperLog ";
        if($idOperLog != null){
            $sql .= "WHERE descricao like '$idOperLog' ";
        }
        if($ano != null){
            $sql .= " AND ano = $ano";
        }
        if($mes != null){
            $sql .= " AND mes = $mes";
        }
        $SQLeXEC = mssql_query($sql);
        while($dados = mssql_fetch_array($SQLeXEC)){
            $da = $dados[valor];
        }
        return $da;
    }
    
    function kmPrevistoOperLog($idOperLog=null, $ano=null, $mes=null){
        include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
        $sql = "SELECT TOP 1 km FROM previstoOperLog ";
        if($idOperLog != null){
            $sql .= "WHERE descricao like '$idOperLog' ";
        }
        if($ano != null){
            $sql .= " AND ano = $ano";
        }
        if($mes != null){
            $sql .= " AND mes = $mes";
        }
        $SQLeXEC = mssql_query($sql);
        while($dados = mssql_fetch_array($SQLeXEC)){
            $da = $dados[km];
        }
        return $da;
    }
    
    function buscaTempoTela($idUsuario){
        include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
        $sql = "SELECT TOP 1 * FROM usuario WHERE id_usuario=$idUsuario";
        $SQLeXEC = mssql_query($sql);
        while($dados = mssql_fetch_array($SQLeXEC)){
            $da = $dados['tempoTela'];
        }
        return $da;
    }
    
    function buscaCteLogAverbacao($idFilial, $numCte){
        include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
        $sql = "SELECT * FROM logAverbacao WHERE sigla='$idFilial' AND numero=$numCte ";
        $SQLeXEC = mssql_query($sql);
        $i = mssql_num_rows($SQLeXEC);
        return $i;
    }
    
    function insertLogAverbacao($sigla, $numero, $retorno, $idUsuario, $averbacao, $protocolo, $chave, $tipo, $ambiente, $integradora, $responseWs){
        include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
        $sql = "INSERT INTO logAverbacao (sigla, numero, retorno, idUsuario, dataProcessamento,
                                        averbacao, protocolo, chave, tipo, ambiente, integradora)
                VALUES ($sigla, $numero, '$retorno', $idUsuario, getdate(), 
                         '$averbacao', '$protocolo', '$chave', '$tipo', '$ambiente', '$integradora' ) ";
        $SQLeXEC = mssql_query($sql);
        
        return $SQLeXEC;
    }
    
    
   function insertCarga($tipoCte, $idFilial, $modalidade, $remetente, $destinatario, $cobranca, $redespacho, $expedidor, $modIcms, $pesoCalculo, $contrato, $placa, $reboque, $semiReboque,
                        $fretePeso, $escolta, $advaloren, $seccat, $carga, $despacho, $descarga, $gris, $enlonamento, $adicionalEntrega, $freteTotal, $pedagio, $freteBrutoDesejado, $responsavelSeguro,
                        $observacao, $dataAgendamentoCliente, $horaAgendamentoCliente, $idFormaPagamento, $descricaoFormaPagamentoBipe, $numPedido){
       if($pesoCalculo == ''){ $pesoCalculo=0; }
       if($escolta == ''){ $escolta=0; }
       if($fretePeso == ''){ $fretePeso=0; }
       if($advaloren == ''){ $advaloren=0; }
       if($seccat == ''){ $seccat=0; }
       if($carga == ''){ $carga=0; }
       if($despacho == ''){ $despacho=0; }
       if($descarga == ''){ $descarga=0; }
       if($gris == ''){ $gris=0; }
       if($enlonamento == ''){ $enlonamento=0; }
       if($adicionalEntrega == ''){ $adicionalEntrega=0; }
       if($freteTotal == ''){ $freteTotal=0; }
       if($pedagio == ''){ $pedagio=0; }
       if($freteBrutoDesejado == ''){ $freteBrutoDesejado=0; }
       if($numPedido == ''){ $numPedido=0; }
       
       //PREPARA DATA PARA BANCO
       $timestamp = $dataAgendamentoCliente.' '.$horaAgendamentoCliente;
       $datetimeAgendamentoCliente = date('Y/m/d H:i:s', strtotime($timestamp)); 
       
       include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
        $sql = "INSERT INTO cargas (tipoCte, idFilial, modalidadeFrete, remetente,
                                    destinatario, cobranca, redespacho, expedidor,
                                    modIcms, pesoCalculo, contrato, placa,
                                    reboque, semiReboque, fretePeso, escolta,
                                    advaloren, seccat, carga, despacho,
                                    descarga, gris, enlonamento, adicionalEntrega,
                                    freteTotal, pedagio, freteBrutoDesejado,
                                    responsavelSeguro, observacao, agendamentoCliente,
                                    dataCadastro, idSAPOCTG, descricaoSAPOCTG, numPedido)
                            VALUES
                                    ('$tipoCte', $idFilial, '$modalidade', '$remetente', 
                                    '$destinatario', '$cobranca', '$redespacho', '$expedidor',
                                    '$modIcms', '$pesoCalculo', '$contrato', '$placa',
                                    '$reboque', '$semiReboque', '$fretePeso', '$escolta',
                                    '$advaloren', '$seccat', '$carga', '$despacho', 
                                    '$descarga', '$gris', '$enlonamento', '$adicionalEntrega',
                                    '$freteTotal', '$pedagio', '$freteBrutoDesejado',
                                    '$responsavelSeguro', '$observacao', '$datetimeAgendamentoCliente',
                                    GETDATE(), '$idFormaPagamento', '$descricaoFormaPagamentoBipe', '$numPedido' )";
        $SQLeXEC = mssql_query($sql);
        
        return $SQLeXEC;
   }
   
   function receitaPrevistoOperadorLogistico($ano=null, $mes=null){
       include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
        $sql = "SELECT SUM(valor) VALOR FROM previstoOperLog";
        if($ano != null){
            $sql .= " WHERE ano = $ano";
        }
        if($mes != null){
            $sql .= " AND mes = $mes";
        }
        $SQLeXEC = mssql_query($sql);
        while($dados = mssql_fetch_array($SQLeXEC)){
            $da = $dados['VALOR'];
        }
        return $da;
   }
   
   function buscaCarga($idCarga){
        include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
        $sql = "SELECT TOP 1 *, day(agendamentoCliente) diaAgenCli, month(agendamentoCliente) mesAgenCli, year(agendamentoCliente) anoAgenCli,
                                datepart(hour,agendamentoCliente) horaCli, datepart(minute,agendamentoCliente) minutoCli
                FROM cargas WHERE id=$idCarga";
        $SQLeXEC = mssql_query($sql);
        while($dados = mssql_fetch_array($SQLeXEC)){
            if($dados['diaAgenCli'] < 10){
                $dados['diaAgenCli'] = '0'.$dados['diaAgenCli'];
            }
            if($dados['mesAgenCli'] < 10){
                $dados['mesAgenCli'] = '0'.$dados['mesAgenCli'];
            }
            if($dados['horaCli'] < 10){
                $dados['horaCli'] = '0'.$dados['horaCli'];
            }
            if($dados['minutoCli'] < 10){
                $dados['minutoCli'] = '0'.$dados['minutoCli'];
            }
            $da[TIPOCTE]     = $dados['tipoCte'];
            $da[MODALIDADE]  = $dados['modalidadeFrete'];
            $da[IDFILIAL]    = $dados['idFilial'];
            $da[REMETENTE]   = $dados['remetente'];
            $da[DESTINATARIO]= $dados['destinatario'];
            $da[COBRANCA]    = $dados['cobranca'];
            $da[REDESPACHO]  = $dados['redespacho'];
            $da[MODICMS]     = $dados['modIcms'];
            $da[PESOCALCULO] = $dados['pesoCalculo'];
            $da[FRETEPESO]   = $dados['fretePeso'];
            $da[ESCOLTA]     = $dados['escolta'];
            $da[ADVALOREN]   = $dados['advaloren'];
            $da[SECCAT]      = $dados['seccat'];
            $da[CARGA]       = $dados['carga'];
            $da[DESPACHO]    = $dados['despacho'];
            $da[DESCARGA]    = $dados['descarga'];
            $da[GRIS]        = $dados['gris'];
            $da[ENLONAMENTO] = $dados['enlonamento'];
            $da[ADICIONALENTREGA]   = $dados['adicionalEntrega'];
            $da[FRETETOTAL]         = $dados['freteTotal'];
            $da[PEDAGIO]            = $dados['pedagio'];
            $da[FRETEBRUTODESEJADO] = $dados['freteBrutoDesejado'];
            $da[RESPONSAVELSEGURO]  = $dados['responsavelSeguro'];
            $da[AGENDAMENTOCLIENTE] = $dados['agendamentoCliente'];
            $da[AGENCLI] = $dados['anoAgenCli'].'-'.$dados['mesAgenCli'].'-'.$dados['diaAgenCli'];
            $da[HORACLI] = $dados['horaCli'].':'.$dados['minutoCli'];
            $da[OBSERVACAO]         = $dados['observacao'];
            $da[IDFORMAPAGAMENTO]   = $dados['idSAPOCTG'];
            $da[NUMPEDIDO]          = $dados['numPedido'];
        }
        return $da;
    }
    
    function alteraCarga($idCarga, $contrato, $placa){
        include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
        $sql = "UPDATE cargas SET dataProgramacao= GETDATE(), contrato='$contrato', placa='$placa'
                WHERE id=$idCarga";
        $SQLeXEC = mssql_query($sql);
        
        return $SQLeXEC;
    }
    
    //FLORESTAL
    function florestalTotalCargas($ano=null, $mes=null, $idPlaca=null){
        include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
        $sql = "SELECT COUNT(*) FROM flr.carregamento";
        if ($ano != null) {
            $sql .= " WHERE YEAR(data)=$ano";
        }
        if ($mes != null) {
            $sql .= " AND MONTH(data)=$mes";
        }
        if ($idPlaca != null) {
            $sql .= " AND placa='$idPlaca' ";
        }
        $SQLeXEC = mssql_query($sql);
        while($dados = mssql_fetch_array($SQLeXEC)){
            $da = $dados[0];
        }
        return $da;
    }
    
    //FLORESTAL
    function florestalTotalPeso($ano=null, $mes=null, $idPlaca=null){
        include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
        $sql = "SELECT sum(peso) FROM flr.carregamento";
        if ($ano != null) {
            $sql .= " WHERE YEAR(data)=$ano";
        }
        if ($mes != null) {
            $sql .= " AND MONTH(data)=$mes";
        }
        if ($idPlaca != null) {
            $sql .= " AND placa='$idPlaca' ";
        }
        $SQLeXEC = mssql_query($sql);
        while($dados = mssql_fetch_array($SQLeXEC)){
            $da = $dados[0];
        }
        return $da;
    }
    
    //FLORESTAL
    function florestalTotalFaturamento($ano=null, $mes=null, $idPlaca=null){
        include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
        $sql = "SELECT SUM(valor * (peso / 1000)) FROM flr.carregamento";
        if ($ano != null) {
            $sql .= " WHERE YEAR(data)=$ano";
        }
        if ($mes != null) {
            $sql .= " AND MONTH(data)=$mes";
        }
        if ($idPlaca != null) {
            $sql .= " AND placa='$idPlaca' ";
        }
        $SQLeXEC = mssql_query($sql);
        while($dados = mssql_fetch_array($SQLeXEC)){
            $da = $dados[0];
        }
        return $da;
    }
    
    //FLORESTAL
    function florestalListaPlacas($ano=null, $mes=null){
        include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
        $sql = "SELECT placa, SUM(valor * (peso / 1000)) faturamento
                    FROM flr.carregamento
                    ";
        if ($ano != null) {
            $sql .= " WHERE YEAR(data)=$ano";
        }
        if ($mes != null) {
            $sql .= " AND MONTH(data)=$mes";
        }
        $sql .= " GROUP BY placa
                    ORDER BY faturamento DESC";
        $SQLeXEC = mssql_query($sql);
        $i=0;
        while($d = mssql_fetch_array($SQLeXEC)){
                $i++;
                $da[$i][IDPLACA]   = $d[placa];
                $da[$i][FATURAMENTO]      = $d[faturamento];
        }
        return $da;
    }
    
    //FLORESTAl
    function florestalListaFazendaPlaca($ano=null, $mes=null, $idPlaca=null){
        include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
        $sql= "SELECT idFazenda, COUNT(idCarregamento) viagem, SUM(peso) peso, SUM(valor * (peso / 1000)) faturamento
                            FROM flr.carregamento 
                          ";
        if ($ano != null) {
            $sql .= " WHERE YEAR(data)=$ano";
        }
        if ($mes != null) {
            $sql .= " AND MONTH(data)=$mes";
        }
        if ($idPlaca != null) {
            $sql .= " AND placa='$idPlaca' ";
        }
        
        $sql .= " GROUP BY idFazenda
                 ORDER BY faturamento DESC";
        
        $SQLeXEC = mssql_query($sql);
        $i=0;
        while($d = mssql_fetch_array($SQLeXEC)){
                $i++;
                $da[$i][IDFAZENDA]   = $d[idFazenda];
                $da[$i][VIAGEM]      = $d[viagem];
                $da[$i][PESO]        = $d[peso];
                $da[$i][FATURAMENTO] = $d[faturamento];
        }
        return $da;
    }
        
    //FLORESTAL
    function florestalNomeFazenda($idFazenda=null){
        include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
        $sql = "SELECT top 1 descricao FROM flr.fazenda where idFazenda=$idFazenda";

        $SQLeXEC = mssql_query($sql);
        while($dados = mssql_fetch_array($SQLeXEC)){
            $da = $dados[0];
        }
        return $da;
    }
?>