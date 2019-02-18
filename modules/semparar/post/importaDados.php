<?php
    namespace Modulos\SemParar\Post;

    require $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    use PDO;

    header('Content-Type: text/html; charset=utf-8');

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Classes\connectMSSQL();

    /** Necessário para o alertScript personalizado */
    echo "<link rel='stylesheet' href=" . $hoUtils->getURLDestino('stylesheets/all.css') . " type='text/css' />";
    echo "<script src=" . $hoUtils->getURLDestino("js/all.js") . "></script>";

    /** Validações e retornos */
    $fileNameCon = $_FILES["file"]["tmp_name"][0];
    $fileNamePrc = $_FILES["file"]["tmp_name"][1];
    $fileNamePsg = $_FILES["file"]["tmp_name"][2];
    $fileNameCre = $_FILES["file"]["tmp_name"][3];

    /** Verifica erros com os arquivos. 0 = Sucesso; 4 = Nenhum arquivo enviado (não bloqueia pois os arquivos são opcionais) */
    $errors = $_FILES["file"]["error"];

    if ($errors[0] != 0 && $errors[0] != 4) return printf($hoUtils->alertScript("Erro com arquivo de concessionárias: "   . $errors[0]));
    if ($errors[1] != 0 && $errors[1] != 4) return printf($hoUtils->alertScript("Erro com arquivo de preças de pedágio: " . $errors[1]));
    if ($errors[2] != 0 && $errors[2] != 4) return printf($hoUtils->alertScript("Erro com arquivo de passagens: "         . $errors[2]));
    if ($errors[3] != 0 && $errors[3] != 4) return printf($hoUtils->alertScript("Erro com arquivo de créditos: "          . $errors[3]));

    session_start();

    $_SESSION['retLog'] = array();

    /** Conexão PDO com o banco e configuração do Encode para o padrão do OS, pois estavam ocorrendo erros de INSERT com as praças de pedágio */
    $dbcSQL->connect();
    $dbcSQL->getPDO()->setAttribute(PDO::SQLSRV_ATTR_ENCODING, PDO::SQLSRV_ENCODING_SYSTEM);

    /** Atualização das concessionárias */
    if ($fileNameCon && count($_SESSION['retLog']) < 50){
        $dbcSQL->beginTransaction();

        /** Usamos o Try Catch para prever erros com o PDO ou de programação */
        try {
            $count = 0;
            $commit = true;

            /** Não podemos utilizar o TRUNCATE devido a chave estrangeira na tabela ParcPed, portanto desabilitamos a mesma e executamos um DELETE com RESEED */
            $dbcSQL->execute("ALTER TABLE sp.pracped NOCHECK CONSTRAINT ALL; DELETE FROM sp.concessionaria; ALTER TABLE sp.pracped CHECK CONSTRAINT ALL;");
            $dbcSQL->execute("DBCC CHECKIDENT ('sp.concessionaria', RESEED, 0)");

            /** Prepara a query que será usada nesta transação */
            $sql = "INSERT INTO sp.concessionaria VALUES (:codigo, :nome)";

            /**
             * Realiza a leitura do arquivo
             */
            $hFile = fopen($fileNameCon, "r");

            while (!feof($hFile)){
                $line = fgets($hFile);

                if ($line[1] == 0) continue; // Cabeçalho do arquivo - registro "00"
                if ($line[1] == 9) break;    // Rodapé do arquivo - registro "99"

                /** Limita os erros no log a 50, para não gerar flood de informação */
                if (count($_SESSION['retLog']) >= 50) break;

                $values['codigo'] = substr($line, 2, 5);
                $values['nome']   = utf8_decode(trim(substr($line, 7, 40)));

                /** Insere o registro no banco e incrementa o contador em caso de sucesso. Caso contrário, reporta o erro e cancela o commit da transação */
                $result = $dbcSQL->execute($sql, $values);

                if (!$result) $count++;
                else { $hoUtils->pushLog(utf8_encode($result) . " - Conces.: $values[codigo]", "erro"); $commit = false; }
            }

            fclose($hFile);

            $dbcSQL->endTransaction($commit);

            if ($commit) $hoUtils->pushLog("$count concessionárias atualizadas / inseridas com sucesso");
        } catch (\PDOException $p) {
            $dbcSQL->endTransaction(false);

            $hoUtils->pushLog("Atenção! Erro com transação PDO encontrada, informe o administrador do sistema (Conc. - $p->getMessage())");
        } catch (\Exception $e) {
            $dbcSQL->endTransaction(false);

            $hoUtils->pushLog("Atenção! Ocorreu um erro, informe o administrador do sistema (Conc. - $e->getMessage())");
        }
    }

    /** Atualização das praças de pedágio */
    if ($fileNamePrc && count($_SESSION['retLog']) < 50){
        $dbcSQL->beginTransaction();

        try {
            $count = 0;
            $commit = true;

            /** Não podemos utilizar o TRUNCATE devido a chave estrangeira na tabela ParcPed, portanto desabilitamos a mesma e executamos um DELETE com RESEED */
            $dbcSQL->execute("ALTER TABLE sp.passagens NOCHECK CONSTRAINT ALL; DELETE FROM sp.pracped; ALTER TABLE sp.passagens CHECK CONSTRAINT ALL;");
            $dbcSQL->execute("DBCC CHECKIDENT ('sp.pracped', RESEED, 0)");

            /** Prepara a query que será usada nesta transação */
            $sql = "INSERT INTO sp.pracped VALUES (:codC, :codigo, :nome)";

            /**
             * Realiza a leitura do arquivo
             */
            $hFile = fopen($fileNamePrc, "r");

            while (!feof($hFile)){
                $line = fgets($hFile);

                if ($line[1] == 0) continue; // Cabeçalho do arquivo - registro "00"
                if ($line[1] == 9) break;    // Rodapé do arquivo - registro "99"

                if (count($_SESSION['retLog']) >= 50) break; $erro = "";

                $values['codC']   = substr($line, 2, 5);
                $values['codigo'] = substr($line, 7, 5);
                $values['nome']   = utf8_decode(trim(substr($line, 12, 40)));

                /** Validações - Caso haja algum erro, reporta no log, cancela o commit da transação e interrompe o laço atual **/

                /** Verifica se a concessionária dona desta praça de pedágio está cadastrada */
                $params = array( $dbcSQL->whereParam("codigo", $values['codC']) );

                if (!$dbcSQL->simpleSelect("sp.concessionaria", null, $params))
                    $erro = "A concessionária ($values[codC]) desta praça de pedágio ($values[nome]) não está cadastrada";

                if ($erro){ $hoUtils->pushLog($erro, "erro"); $commit = false; continue; }

                $result = $dbcSQL->execute($sql, $values);

                if (!$result) $count++;
                else { $hoUtils->pushLog(utf8_encode($result) . " - Praça: $values[codigo]", "erro"); $commit = false; }
            }

            fclose($hFile);

            $dbcSQL->endTransaction($commit);

            if ($commit) $hoUtils->pushLog("$count praças de pedágio atualizadas / inseridas com sucesso");
        } catch (\PDOException $p) {
            $dbcSQL->endTransaction(false);

            $hoUtils->pushLog("Atenção! Erro com transação PDO encontrada, informe o administrador do sistema (PracPed. - $p->getMessage())");
        } catch (\Exception $e) {
            $dbcSQL->endTransaction(false);

            $hoUtils->pushLog("Atenção! Ocorreu um erro, informe o administrador do sistema (PracPed. - $e->getMessage())");
        }
    }

    /** Inserção das passagens */
    if ($fileNamePsg && count($_SESSION['retLog']) < 50){
        $dbcSQL->beginTransaction();

        try {
            $count = 0;
            $commit = true;

            /** Armazena o nome do arquivo para propósito de log nos registros */
            $fileName = substr($_FILES["file"]["name"][2], 0, 35);

            /** Prepara as queries que serão usadas nesta transação */
            $sqlFat = "INSERT INTO sp.fatura VALUES (:numero, :valor, :dtEmis, :dtVenc, :tpCli, :cgcCli, :codCli)";
            $sqlVei = "INSERT INTO sp.veiculo VALUES (:placa, :categoria)";
            $sqlPsg = "INSERT INTO sp.passagens VALUES (:idFatura, :idVeiculo, :codC, :codP, :data, :categoria, :valor, :tag, :file)";

            /**
             * Realiza a leitura do arquivo
             */
            $hFile = fopen($fileNamePsg, "r");

            while (!feof($hFile)){
                $line = fgets($hFile);

                if ($line[1] == 0) continue; // Cabeçalho do arquivo - registro "00"
                if ($line[1] == 9) break;    // Rodapé do arquivo - registro "99"

                if (count($_SESSION['retLog']) >= 50) break; $erro = "";

                /** Reseta o array de valores para o caso de haver mudança no tipo de registro */
                $values = array();

                /**
                 * Tipo de registro: 01 - Cabeçalho da fatura; 02 - Cabeçalho dos veículos; 03 - Passagens
                 */
                $registro = substr($line, 0, 2);

                switch ($registro){
                    /** Leitura do cabeçalho da fatura - Registro 01 - Que contém informações sobre a mesma e o cliente */
                    case "01":
                        $values['tpCli']  = substr($line, 2, 2);
                        $values['cgcCli'] = substr($line, 4, 14);
                        $values['codCli'] = substr($line, 18, 8);
                        $values['numero'] = substr($line, 26, 9);
                        $values['dtEmis'] = date('Y-m-d', strtotime(substr($line, 35, 8)));
                        $values['dtVenc'] = date('Y-m-d', strtotime(substr($line, 43, 8)));
                        $values['valor']  = substr($line, 51, 11) . "." . substr($line, 62, 2);

                        $numFatura = $values['numero'];

                        /** Valida se a fatura em questão já existe */
                        $idFatura = $dbcSQL->simpleSelect("sp.fatura", "idFatura", array($dbcSQL->whereParam("numero", $values['numero'])));

                        /** Realiza inserção do nova fatura e recupera o ID inserido */
                        if (!$idFatura){
                            $result = $dbcSQL->execute($sqlFat, $values);

                            if (!$result) $idFatura = $dbcSQL->getPDO()->lastInsertId();
                            else { $hoUtils->pushLog(utf8_encode($result) . " - Fatura: $values[numero]", "erro"); $commit = false; }
                        }
                        /** Reporta que a fatura já existe e interrompe o processo de importação das passagens, cancelando o commit da transação */
                        else {
                            $hoUtils->pushLog("A fatura número $values[numero] já existe no sistema", "erro"); $commit = false;

                            break;
                        }

                        continue;

                    /** Leitura do cabeçalho das passagens - Registros 02 - Que contém informações sobre o veículo */
                    case "02":
                        $tag = substr($line, 19, 10);

                        $values['placa']     = substr($line, 2, 7);
                        $values['categoria'] = substr($line, 29, 2);

                        /** Busca o ID do veículo na base e caso não exista realiza a inserção do mesmo e recupera o novo ID atribuído */
                        $idVeiculo = $dbcSQL->simpleSelect("sp.veiculo", "idVeiculo", array($dbcSQL->whereParam("placa", $values['placa'])));

                        if (!$idVeiculo){
                            $result = $dbcSQL->execute($sqlVei, $values);

                            if (!$result) $idVeiculo = $dbcSQL->getPDO()->lastInsertId();
                            else { $hoUtils->pushLog(utf8_encode($result) . " - Veículo: $values[placa]", "erro"); $commit = false; }
                        }

                        continue;

                    /** Leitura das passagens - Registros 03 */
                    case "03":
                        $values['file']      = $fileName;
                        $values['idFatura']  = $idFatura;
                        $values['idVeiculo'] = $idVeiculo;
                        $values['tag']       = $tag;
                        $values['data']      = date('Y-m-d H:i:s', strtotime(substr($line, 2, 14)));
                        $values['codC']      = substr($line, 16, 5);
                        $values['codP']      = substr($line, 21, 5);
                        $values['categoria'] = substr($line, 26, 2);
                        $values['valor']     = substr($line, 28, 11) . "." . substr($line, 39, 2);

                        /** Validações - Caso haja algum erro, reporta no log, cancela o commit da transação e interrompe o laço atual **/

                        /** Verifica se a praça de pedágio a qual esta passagem refere-se está cadastrada */
                        $params = array( $dbcSQL->whereParam("codConcessionaria", $values['codC']), $dbcSQL->whereParam("codigo", $values['codP']) );

                        if (!$dbcSQL->simpleSelect("sp.pracped", null, $params))
                            $erro = "A praça de pedágio ($values[codC] - $values[codP]) desta passagem ($values[data]) não está cadastrada";

                        if ($erro){ $hoUtils->pushLog($erro, "erro"); $commit = false; continue; }

                        $result = $dbcSQL->execute($sqlPsg, $values);

                        if (!$result) $count++;
                        else { $hoUtils->pushLog(utf8_encode($result) . " - Passagem: $placa - $values[data]", "erro"); $commit = false; }

                        continue;

                    default: continue;
                }
            }

            fclose($hFile);

            $dbcSQL->endTransaction($commit);

            if ($commit) $hoUtils->pushLog("Fatura $numFatura inserida com sucesso ($count passagens processadas)");
        } catch (\PDOException $p) {
            $dbcSQL->endTransaction(false);

            $hoUtils->pushLog("Atenção! Erro com transação PDO encontrada, informe o administrador do sistema (Pass.. - $p->getMessage())");
        } catch (\Exception $e) {
            $dbcSQL->endTransaction(false);

            $hoUtils->pushLog("Atenção! Ocorreu um erro, informe o administrador do sistema (Pass. - $e->getMessage())");
        }
    }

    /** Inserção de créditos */
    if ($fileNameCre && count($_SESSION['retLog']) < 50){
        $dbcSQL->beginTransaction();

        try {
            $count = 0;
            $commit = true;

            /** Prepara as queries que serão usadas nesta transação */
            $sql = "INSERT INTO sp.credito VALUES (:idV, :tag, :data, :valor, CURRENT_TIMESTAMP)";
            $sqlVei = "INSERT INTO sp.veiculo (placa) VALUES (:placa)";

            /**
             * Realiza a leitura do arquivo
             */
            $hFile = fopen($fileNameCre, "r");

            while (!feof($hFile)){
                $line = fgets($hFile);

                if (!$line) continue;

                if (count($_SESSION['retLog']) >= 50) break; $erro = "";

                $placa = substr($line, 8, 7);
                $desc  = trim(substr($line, 25, 35));

                $values['data']  = $hoUtils->dateFormat(substr($line, 0, 8), 'dmY', 'Y-m-d');
                $values['tag']   = substr($line, 15, 10);
                $values['valor'] = substr($line, 60, 13) . "." . substr($line, 73, 2);
                $values['idV']   = $dbcSQL->simpleSelect("sp.veiculo", "idVeiculo", array($dbcSQL->whereParam("placa", $placa)));

                /** Busca o ID do veículo na base e caso não exista realiza a inserção do mesmo e recupera o novo ID atribuído */
                if (!$values['idV']){
                    $result = $dbcSQL->execute($sqlVei, array("placa" => $placa));

                    if (!$result) $values['idV'] = $dbcSQL->getPDO()->lastInsertId();
                    else { $hoUtils->pushLog(utf8_encode($result) . " - Veículo: $placa", "erro"); $commit = false; }
                }

                /** Importar apenas os registros referentes a reenvio de passagem */
                if (strpos($desc, 'REENVIO') || strpos($desc, 'indevida')){
                    $result = $dbcSQL->execute($sql, $values);

                    if (!$result) $count++;
                    else { $hoUtils->pushLog(utf8_encode($result) . " Créd.: $values[data] - $placa", "erro"); $commit = false; }
                }
            }

            fclose($hFile);

            $dbcSQL->endTransaction($commit);

            if ($commit) $hoUtils->pushLog("$count créditos inseridos com sucesso");
        } catch (\PDOException $p) {
            $dbcSQL->endTransaction(false);

            $hoUtils->pushLog("Atenção! Erro com transação PDO encontrada, informe o administrador do sistema (Conc. - $p->getMessage())");
        } catch (\Exception $e) {
            $dbcSQL->endTransaction(false);

            $hoUtils->pushLog("Atenção! Ocorreu um erro, informe o administrador do sistema (Conc. - $e->getMessage())");
        }
    }

    $dbcSQL->disconnect();

    /**
     * Recupera o caminho do arquivo que chamou este POST e retorna para ele alertando a mensagem de retorno.
     * O explode serve para remover qualquer parâmetro adjacente ao caminho do arquivo (i.e. file.php?id=1 se torna file.php)
     */
    $location = explode(".php", $_SERVER['HTTP_REFERER']);

    if (count($_SESSION['retLog']) <= 0) $hoUtils->pushLog("Nenhum arquivo informado para importação", "msg");

    return printf($hoUtils->alertScript("Importação finalizada, verifique o log de mensagens para maiores detalhes", "Pronto", "window.location = '$location[0].php'"));
?>