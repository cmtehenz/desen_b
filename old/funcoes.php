<?php
    /**************************************************************
     * Função: diffDate                                           *
     * Programador: Unknown                                       *
     * Data: Unknown                                              *
     * Descrição:                                                 *
     *     Retorna o número de dias existentes entre 2 datas      *
     **************************************************************/
    function diffDate($data_inicial, $data_final){
        // Calcula a diferença em segundos entre as datas
        $diferenca = strtotime($data_final) - strtotime($data_inicial);

        //Calcula a diferença em dias
        $dias = floor($diferenca / (60 * 60 * 24));

        return $dias;
    }

    /**************************************************************
     * Função: dateFormat                                         *
     * Programador: Paulo Silva                                   *
     * Data: 10/06/2015                                           *
     * Descrição:                                                 *
     *     Converte formato de datas usando a classe DateTime. É  *
     *     útil para conversões como d/m/Y para Y-m-d que não são *
     *     facilmente feitas com a função 'date($format, $input)' *
     *     sem algumas conversões manuais feitas pelo programador *
     * OBS.: Por padrão adota a conversão de formato BR para EUA  *
     *       (d/m/Y -> Y-m-d), mas pode ser alterado informando   *
     *       os parâmetros $from e $to.                           *
     **************************************************************/
    function dateFormat($date, $from = 'd/m/Y', $to = 'Y-m-d'){
        return DateTime::createFromFormat($from, $date)->format($to);
    }

    /**************************************************************
     * Função: menuUsuario                                        *
     * Programador: Paulo Silva                                   *
     * Data: 12/06/2015                                           *
     * Descrição:                                                 *
     *     Busca, monta e retorna em HTML o menu do usuário que   *
     *     for passado por parâmetro, destacando o menu do módulo *
     *     ativo (informar categoria do módulo no $active)        *
     **************************************************************/

    /**
     * @deprecated since version 3.20
     */
    function menuUsuario($idUsuario, $active = NULL){
        //include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';

        // Busca registros e monta linhas dos menus
        $sqlMenu = mssql_query(
                "SELECT
                    m.categoria, m.url, m.nome
                 FROM menu_usuario u
                 JOIN menu m ON (m.id_menu = u.id_menu)
                 WHERE
                    u.id_usuario = $idUsuario
                 ORDER BY
                    m.categoria, m.ordenacao");

        // Inicializar todos os menus senão caso o usuário não possua um deles, nem todos serão escritos e assim
        // as opções que ele tinha para o final ficarão fora de ordem. No futuro implementar uma tabela Categoria
        // no banco para que já venha certinho apenas os que o usuário possui
        $menu[0] = "";
        $menu[1] = "";
        $menu[2] = "";
        $menu[3] = "";
        $menu[4] = "";
        $menu[5] = "";
        $menu[6] = "";
        $menu[7] = "";

        while($dadosMenu = mssql_fetch_array($sqlMenu)){
            if($dadosMenu[0] == 1) $menu[0] .= "<li><a href='$dadosMenu[1]'>$dadosMenu[2]</a></li>";
            if($dadosMenu[0] == 2) $menu[1] .= "<li><a href='$dadosMenu[1]'>$dadosMenu[2]</a></li>";
            if($dadosMenu[0] == 3) $menu[2] .= "<li><a href='$dadosMenu[1]'>$dadosMenu[2]</a></li>";
            if($dadosMenu[0] == 4) $menu[3] .= "<li><a href='$dadosMenu[1]'>$dadosMenu[2]</a></li>";
            if($dadosMenu[0] == 5) $menu[4] .= "<li><a href='$dadosMenu[1]'>$dadosMenu[2]</a></li>";
            if($dadosMenu[0] == 6) $menu[5] .= "<li><a href='$dadosMenu[1]'>$dadosMenu[2]</a></li>";
            if($dadosMenu[0] == 7) $menu[6] .= "<li><a href='$dadosMenu[1]'>$dadosMenu[2]</a></li>";
            if($dadosMenu[0] == 8) $menu[7] .= "<li><a href='$dadosMenu[1]'>$dadosMenu[2]</a></li>";
        }

        // Monta a class do li de cada menu, colocando a 'nav active' no que foi parametrizado ($active == x)
        for ($i = 0; $i < count($menu); $i++)
            $navClass[$i] = 'nav' . (($active == $i + 1) ? ' active' : '');

        // Prepara arrays com os ícones e nome de cada menu
        $icon = array(
            "icon-document-alt-stroke",
            "icon-article",
            "icon-info",
            "icon-layers",
            "icon-equalizer",
            "icon-cloud-upload",
            "icon-list",
            "icon-list"
        );

        $nome = array(
            "Comercial",
            "Contas a Receber",
            "Contas a Pagar",
            "Operacional",
            "Custos",
            "Florestal",
            "Qualidade",
            "Administração"
        );

        // Monta o código HTML e retorna
        $echoMenu .= "<div id='sidebar'><ul id='mainNav'>";
        $echoMenu .= "<li class='nav'>
                          <span class='icon-home'></span>
                          <a href='./index_menu.php'>Dashboard</a>
                      </li>";

        for ($i = 0; $i < count($menu); $i++){
            $echoMenu .=
                "<li class='$navClass[$i]'>
                    <span class='$icon[$i]'></span>
                    <a href=''>$nome[$i]</a>
                    <ul class='subNav'>
                        $menu[$i]
                    </ul>
                </li>";
        }

        $echoMenu .= "</ul></div>";

        return $echoMenu;
    }

    /**************************************************************
     * Função: alertScript                                        *
     * Programador: Paulo Silva                                   *
     * Data: 17/06/2015                                           *
     * Descrição:                                                 *
     *     Função simples para retornar um alerta em JavaScript   *
     **************************************************************/
    function alertScript($msg){
        return "<script>alert('" . $msg . "'); history.back();</script>";
    }

    /**************************************************************
     * Função: geraHistoricoLogin                                 *
     * Programador: Paulo Silva                                   *
     * Data: 23/06/2015                                           *
     * Descrição:                                                 *
     *     Função para gerar log de erro nas tentativas de login  *
     **************************************************************/
    function geraHistoricoLogin($login, $senha, $msg){
        include_once './connect_mssql.php';

        $ip = getenv('REMOTE_ADDR');

        mssql_query("INSERT INTO hlogin VALUES ('$login', '$senha', '" . utf8_decode($msg) . "', '$ip', GETDATE())");

        die (alertScript($msg));
    }
    
    /**************************************************************
     * Função: buscarXml                                          *
     * Programador: Gabriel Luis                                  *
     * Data: 18/04/2017                                           *
     * Descrição:                                                 *
     *     Função para buscar arquivo xml atraves                 *
     *      nome do arquivo                                       *
     **************************************************************/
    function xmlDuplicado($arquivo){
        $sql = mssql_query("SELECT * FROM xml WHERE arquivo = '$arquivo'");
        if(mssql_num_rows($sql) <> 0 ){
            return true; 
        } else {
            return false; 
        }
    }
    
    /**************************************************************
     * Função: inserirXml                                         *
     * Programador: Gabriel Luis                                  *
     * Data: 18/04/2017                                           *
     * Descrição:                                                 *
     *     Função para inserir registros de XML                   *
     **************************************************************/
    function inserirXml($arquivo, $tipo, $chave, $versao, $serie, $nNF, $dhEmi, $emitCNPJ, $emitNome, $destCNPJ,$destNome){
        $sql = mssql_query("INSERT INTO xml (arquivo, tipo, chaveNota, versao, serie, nNF, dhEmi, emitCNPJ, emitNome, destCNPJ, destNome)
                   VALUES ('$arquivo', '$tipo', '$chave', '$versao', '$serie', '$nNF', '$dhEmi', '$emitCNPJ', '$emitNome', '$destCNPJ', '$destNome' )");
        if($sql){ return true; } else {return false; }
    }
?>