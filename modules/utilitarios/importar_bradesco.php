<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL = new \Library\Scripts\scriptSQL();

    $_SESSION['modulo'] = basename(dirname($_SERVER['PHP_SELF']));
?>
<!doctype html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Importação Bradesco</title>

        <meta charset="utf-8" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="<?php echo $hoUtils->getURLDestino('stylesheets/all.css'); ?>" type="text/css" />

        <script src="<?php echo $hoUtils->getURLDestino("js/all.js"); ?>"></script>
    </head>

    <body>
        <?php
            include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';
            include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_db2_bino.php';
            include $_SERVER['DOCUMENT_ROOT'] . '/old/funcoes.php';

            /*             * *******************************
             *   VARIAVEIS                   *
             * ****************************** */
            date_default_timezone_set('America/sao_paulo');
            $wanted_week = date('W');
            $abrev_mes = date('M');
            $nome_mes = date('F');
            $dia = date('d');
            $ano = date('Y');
            $mes_atual = date('m');
            $mensagemErro = "Selecione o arquivo a ser importado.";
            $registroInserido = 0;
            $erroInsert = 0;
            $registroDuplicado = 0;

            /*             * **************************************
             *   PROCESSO DE IMPORTACAO DO ARQUIVO  *
             * ************************************** */

            function validateDate($date, $format = 'Y-m-d H:i:s'){
                $d = DateTime::createFromFormat($format, $date);
                return $d && $d->format($format) == $date;
            }

            if (isset($_FILES["file"]["name"])){
                //echo "Processar o arquivo";
                $allowedExts = array ("CSV", "csv");
                $temp = explode(".", $_FILES["file"]["name"]);
                $extension = end($temp);

                if (in_array($extension, $allowedExts)){
                    if ($_FILES["file"]["error"] > 0){
                        echo "Error: " . $_FILES["file"]["error"] . "<br>";
                    }
                    else{
                        $filePath = sys_get_temp_dir() . $_FILES["file"]["name"];

                        if (file_exists($filePath)){
                            echo $filePath . " already exists. ";
                        }
                        else{
                            $handle = fopen($_FILES["file"]["tmp_name"], "r");

                            if ($handle){

                                $row = 1;

                                include $_SERVER['DOCUMENT_ROOT'] . '/old/connect_mssql.php';

                                while (($data = fgetcsv($handle, 0, ";")) !== FALSE){
                                    //LINHA DO ARQUIVO.\
                                    //if($data[1] == 'Lan�amentos Futuros'){
                                    //    break;
                                    //}
                                    $data_linha = $data[0];

                                    if (validateDate($data_linha, 'd/m/Y')){

                                        if ($data[3] == ''){
                                            $data[3] = 0;
                                        }
                                        $data[3] = str_replace('.', '', $data[3]);
                                        $data[3] = str_replace(',', '.', $data[3]);

                                        if ($data[4] == ''){
                                            $data[4] = 0;
                                        }
                                        $data[4] = str_replace('.', '', $data[4]);
                                        $debito = str_replace(',', '.', $data[4]) * -1;

                                        if ($data[5] == ''){
                                            $data[5] = 0;
                                        }
                                        $data[5] = str_replace('.', '', $data[5]);
                                        $data[5] = str_replace(',', '.', $data[5]);

                                        $data[2] = (int) $data[2];

                                        $arrTo = array ("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U",
                                            "V", "X", "Z", "Y", "W", " ", "/", ".", "-");
                                        $temp = (int) str_replace($arrTo, '', $data[1]);

                                        $sql_verifica = mssql_query("SELECT * FROM bradesco WHERE data='$data_linha' AND lancamento='$data[1]'
                                                              AND documento='$data[2]' AND credito='$data[3]'
                                                              AND debito='$debito' ");
                                        if (mssql_num_rows($sql_verifica) == 0){
                                            //INSERIR REGISTROS

                                            $sql_inserir = mssql_query("INSERT INTO bradesco (data, lancamento, documento, credito, debito, saldo, num_autorizacao_2)
                                    VALUES ('$data_linha', '$data[1]', '$data[2]', '$data[3]', '$debito', '$data[5]', '$temp') ");
                                            if ($sql_inserir){
                                                $registroInserido++;
                                            }
                                            if (!$sql_inserir){
                                                $erroInsert++;
                                                echo $row;
                                                break;
                                            }
                                        }
                                        if (mssql_num_rows($sql_verifica) != 0){
                                            $registroDuplicado++;
                                        }
                                    }
                                    $row++;
                                }
                                //MENSAGEM DEPOIS DA INCLUSAO
                                $mensagemErro = "<b>TABELA BRADESCO</b><br>
                                         REGISTRO INCLUIDO: $registroInserido <br>
                                         ERRO INCLUSAO: $erroInsert <br>
                                         REGISTRO DUPLICADO: $registroDuplicado <br></b><br>
                                         ";

                                fclose($handle);
                                //unlink( $_SERVER['DOCUMENT_ROOT'] . "/upload/" . $_FILES["file"]["name"]);
                            }
                            else{
                                echo "<script>
                            alert('ERRO! Arquivo nao pode ser Carregado.');
                            history.back();
                          </script>";
                                exit();
                            }
                        }
                    }
                }
                else{
                    $mensagemErro = "Invalid file";
                }
            }

            /*             * **************************************
             *   FIM                                *
             * ************************************** */
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

            <!-- Sidebar -->
            <?php echo $hoUtils->menuUsuario($_SESSION['idUsuario']); ?>

            <div id="content">

                <div id="contentHeader">
                    <h1><?php echo $_SESSION['nomeEmpresa']; ?></h1>
                </div> <!-- #contentHeader -->

                <div class="container">
                    <div class="grid-16">
                        <div class="box plain">

                            <h3>IMPORTACAO EXTRATO BRADESCO</h3>

                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Selecione o arquivo a ser importado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="odd gradeX">
                                        <td><form action="#" method="post" enctype="multipart/form-data">
                                                <div class="field-group inlineField">

                                                    <div class="field">
                                                        <input type="file" name="file" id="file">
                                                        <input type="submit" value="IMPORTAR">
                                                    </div> <!-- .field -->
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div> <!-- .box -->


                        <div class="box plain">

                            <h3>RESULTADO DA IMPORTACAO</h3>

                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>RESULTADO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="odd gradeX">
                                        <td><?php echo $mensagemErro; ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div> <!-- .box -->
                        <div class="box plain">

                            <h3>METODO DE IMPORTACAO</h3>

                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>PASSOS</th>
                                        <th>DESCRICAO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="odd gradeX">
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div> <!-- .box -->

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

            <div id="quickNav">
                <ul>
                    <li class="quickNavMail">
                        <a href="#menuAmpersand" class="menu"><span class="icon-book"></span></a>

                        <span class="alert">3</span>

                        <div id="menuAmpersand" class="menu-container quickNavConfirm">
                            <div class="menu-content cf">

                                <div class="qnc qnc_confirm">

                                    <h3>Confirm</h3>

                                    <div class="qnc_item">
                                        <div class="qnc_content">
                                            <span class="qnc_title">Confirm #1</span>
                                            <span class="qnc_preview">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do.</span>
                                            <span class="qnc_time">3 hours ago</span>
                                        </div> <!-- .qnc_content -->

                                        <div class="qnc_actions">
                                            <button class="btn btn-primary btn-small">Accept</button>
                                            <button class="btn btn-quaternary btn-small">Not Now</button>
                                        </div>
                                    </div>

                                    <div class="qnc_item">
                                        <div class="qnc_content">
                                            <span class="qnc_title">Confirm #2</span>
                                            <span class="qnc_preview">Duis aute irure dolor in henderit in voluptate velit esse cillum dolore.</span>
                                            <span class="qnc_time">3 hours ago</span>
                                        </div> <!-- .qnc_content -->

                                        <div class="qnc_actions">
                                            <button class="btn btn-primary btn-small">Accept</button>
                                            <button class="btn btn-quaternary btn-small">Not Now</button>
                                        </div>
                                    </div>

                                    <div class="qnc_item">
                                        <div class="qnc_content">
                                            <span class="qnc_title">Confirm #3</span>
                                            <span class="qnc_preview">Duis aute irure dolor in henderit in voluptate velit esse cillum dolore.</span>
                                            <span class="qnc_time">3 hours ago</span>
                                        </div> <!-- .qnc_content -->

                                        <div class="qnc_actions">
                                            <button class="btn btn-primary btn-small">Accept</button>
                                            <button class="btn btn-quaternary btn-small">Not Now</button>
                                        </div>
                                    </div>

                                    <a href="javascript:;" class="qnc_more">View all Confirmations</a>

                                </div> <!-- .qnc -->
                            </div>
                        </div>
                    </li>
                    <li class="quickNavNotification">
                        <a href="#menuPie" class="menu"><span class="icon-chat"></span></a>

                        <div id="menuPie" class="menu-container">
                            <div class="menu-content cf">

                                <div class="qnc">

                                    <h3>Notifications</h3>

                                    <a href="javascript:;" class="qnc_item">
                                        <div class="qnc_content">
                                            <span class="qnc_title">Notification #1</span>
                                            <span class="qnc_preview">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do.</span>
                                            <span class="qnc_time">3 hours ago</span>
                                        </div> <!-- .qnc_content -->
                                    </a>

                                    <a href="javascript:;" class="qnc_item">
                                        <div class="qnc_content">
                                            <span class="qnc_title">Notification #2</span>
                                            <span class="qnc_preview">Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu.</span>
                                            <span class="qnc_time">3 hours ago</span>
                                        </div> <!-- .qnc_content -->
                                    </a>

                                    <a href="javascript:;" class="qnc_more">View all Confirmations</a>

                                </div> <!-- .qnc -->
                            </div>
                        </div>
                    </li>
                </ul>
            </div> <!-- .quickNav -->


        </div> <!-- #wrapper -->

        <div id="footer">
            Copyright &copy; 2015, CaseElectronic Ltda.
        </div>



    </body>
</html>