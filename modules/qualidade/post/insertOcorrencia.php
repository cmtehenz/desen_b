<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Portal BID</title>
        <style type="text/css">
            <!--
            body {
                margin-left: 0px;
                margin-top: 0px;
                margin-right: 0px;
                margin-bottom: 0px;
            }
            .style3 {
                font-size: 20px;
                font-weight: bold;
                color: #000000;
                font-family: Tahoma, Georgia;
                text-align: center;
            }
            .style4 {	font-size: 14px;
                      font-weight: bold;
                      color: #000000;
                      font-family:Arial, Helvetica, sans-serif;
            }
            .style5 {	font-size: 10px;
                      font-weight: bold;
                      color: #000000;
                      font-family:Arial, Helvetica, sans-serif;
            }
            .style6 {	font-size: 11px;
                      font-weight: bold;
                      color: #000000;
                      font-family:Arial, Helvetica, sans-serif;
            }
            .style7 {font-size: 16px;
                     color: #000000;
                     font-family:Arial, Helvetica, sans-serif;
            }
            .titulo{
                font-family: Arial, Helvetica, sans-serif;
                font-size: 15px;
                font-weight: bold;
            }

            a.link1{
                font-family: Arial, Helvetica, sans-serif;
                font-size: 12px;
                color: #000000;
                text-decoration: none;
            }
            a.link1:hover {
                font-family: Arial, Helvetica, sans-serif;
                font-size: 12px;
                color: #006600;
                font-weight: bold;
                text-decoration: none;
            }
            -->
        </style></head>

    <body>
        <?php
            include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
            $executesql = true;

            if (strlen($_POST['descricao']) == 0){
                echo "<script>
                alert('Favor digitar a descricao.');
                history.back();
              </script>";
                $executesql = false;
            }

            $sql = mssql_query("SELECT * FROM ocorrencia");
            while ($dados = mssql_fetch_array($sql)){
                $descricao = $dados['descricao'];
            }
            if ($_POST['descricao'] == $descricao){
                echo "<script>
                alert('Descricao ja cadastrada.');
               history.go(-1);
              </script>";
                $executesql = false;
            }

            if ($executesql){
                $mssql_settings = mssql_query("INSERT INTO ocorrencia (
                                            descricao,
                                            pontos)
                                          VALUES
                                            ('$_POST[descricao]',
                                             '$_POST[pontos]') ");
            }

            if ($mssql_settings){
                echo " <script>
                    alert('Descrição cadastrada.')
              </script>";
            }
            else{
                echo "<script>
                    alert('Erro na Inserção!')
              </script>";
            }
        ?>
        <script language="JavaScript">
            window.location = "../cadastro_ocorrencia.php";
        </script>
    </body>
</html>
