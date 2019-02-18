<?php
    namespace Modulos\Utilitarios\Post;

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
    $fileNameBrd = $_FILES["file"]["tmp_name"][1];

    /** Verifica erros com os arquivos. 0 = Sucesso; 4 = Nenhum arquivo enviado (não bloqueia pois os arquivos são opcionais) */
    $errors = $_FILES["file"]["error"];

    if ($errors[0] != 0 && $errors[0] != 4) return printf($hoUtils->alertScript("Erro com arquivo de conciliação: " . $errors[0]));
    if ($errors[1] != 0 && $errors[1] != 4) return printf($hoUtils->alertScript("Erro com arquivo do Bradesco: "    . $errors[0]));

    session_start();

    $_SESSION['retLog'] = array();

    /** Conexão PDO com o banco */
    $dbcSQL->connect();
    $dbcSQL->getPDO()->setAttribute(PDO::SQLSRV_ATTR_ENCODING, PDO::SQLSRV_ENCODING_SYSTEM);

    /** Arquivo de conciliação */
    if ($fileNameCon){
        $dbcSQL->beginTransaction();

        /** Usamos o Try Catch para prever erros com o PDO ou de programação */
        try {
            $contrato = 0;
            $pagamento = 0;
            $adiantamento = 0;
            $commit = true;

            /** Prepara as queries que serão usadas nesta transação */
            $sqlCont = "INSERT INTO pcd.contrato VALUES (:nomeArq, :idViagem, :cte, :filial, :bipe, :placa, :cpf, :nome, :numAut, :numBrd, :tpParc, null)";
            $sqlPWeb = "INSERT INTO pcd.pagamento VALUES (:nomeArq, :idViagem, :cte, :filial, :placa, :cpf, :nome, :numAut, :numBrd)";
            $sqlAdto = "INSERT INTO pcd.adiantamento VALUES (:nomeArq, :idViagem, :cpf, :nome, :numAut, :numBrd)";

            /** Realiza a leitura do arquivo */
            $hFile = fopen($fileNameCon, "r");

            $nomeArq = substr($_FILES["file"]["name"][0], 0, 30);

            while (!feof($hFile)){
                $line = fgets($hFile);

                unset($values);

                /** Registros a serem ignorados
                 *  0 - Cabeçalho do arquivo
                 *  5 - Abastecimento
                 *  6 - Vale=pedágio
                 *  9 - Rodapé
                 *  Branco - Última linha do arquivo
                 */
                if (strpos('0569', $line[0]) !== false || !$line[0]) continue;

                $values['nomeArq']  = $nomeArq;
                $values['idViagem'] = substr($line, 31, 10);
                $values['cpf']      = trim(substr($line, 473, 11));
                $values['nome']     = trim(substr($line, 484, 60));
                $values['numAut']   = trim(substr($line, 413, 10));
                $values['numBrd']   = trim(substr($line, 695, 10));

                /** Se possuir valor para o BIPE, significa que é um contrato de frete, senão é um adiantamento feito em cartão ou pagamentos via web */
                $values['bipe'] = trim(substr($line, 76, 25));

                /** Campos para os contratos de frete */
                if ($values['bipe']){
                    $values['filial'] = trim(substr($line, 72, 2));
                    $values['cte']    = substr($line, 42, 30);
                    $values['placa']  = substr($line, 121, 7);
                    $values['tpParc'] = ((int) substr($line, 569, 2)) ?: 4;

                    $sql = $sqlCont;

                    /** Para a mensagem de log caso ocorram erros */
                    $documento = $values['filial'] . " - " . $values['bipe'];
                }
                else
                {
                    /** Se não for um contrato de frete, verifica pelo tipo de documento se foi um pagamento no portal web ou adiantamento de frota */
                    $tpDoc = (int) substr($line, 40, 2);

                    /** Instruções em comum entre os dois tipos */
                    unset($values['bipe']);

                    /** Particularidades (5 = Pagamentos via web; Else = Adiantamentos de frota) */
                    if ($tpDoc == 5){
                        $values['filial'] = substr($line, 42, 2);
                        $values['cte']    = substr($line, 44, 28);
                        $values['placa']  = substr($line, 121, 7);

                        $sql = $sqlPWeb;

                        $documento = $values['filial'] . " - " . $values['cte'];
                    }
                    else { $sql = $sqlAdto; $documento = $values['cgc'] . " - " . $values['nome']; }
                }

                /** Insere o registro no banco e incrementa o contador em caso de sucesso. Caso contrário, reporta o erro e cancela o commit da transação */
                $result = $dbcSQL->execute($sql, $values);

                if (!$result) {
                    /** Incrementa o contador de acordo com a tabela inserida */
                    if (strpos($sql, "contrato"))     $contrato++;
                    if (strpos($sql, "pagamento"))    $pagamento++;
                    if (strpos($sql, "adiantamento")) $adiantamento++;
                }
                else {
                    /** Tratamento dos possíveis erros */

                    /** Violação da Unique Key UK_numBradesco */
                    $result = strpos($result, 'numBradesco') ? "Registro duplicado" : $result;

                    $hoUtils->pushLog(utf8_encode($result) . " - Documento: $documento - Aut. Bradesco: $values[numBrd]", "erro");
                }
            }

            fclose($hFile);

            $dbcSQL->endTransaction($commit);

            if ($commit){
                $hoUtils->pushLog("$contrato registros de integração inseridos com sucesso");
                $hoUtils->pushLog("$pagamento pagamentos do portal web inseridos com sucesso");
                $hoUtils->pushLog("$adiantamento adiantamentos de frota inseridos com sucesso");
            }
        } catch (\PDOException $p) {
            $dbcSQL->endTransaction(false);

            $hoUtils->pushLog("Atenção! Erro com transação PDO encontrada, informe o administrador do sistema (Conc. - $p->getMessage())");
        } catch (\Exception $e) {
            $dbcSQL->endTransaction(false);

            $hoUtils->pushLog("Atenção! Ocorreu um erro, informe o administrador do sistema (Conc. - $e->getMessage())");
        }
    }

    /** Arquivo de extrato do Bradesco */
    if ($fileNameBrd){
        $dbcSQL->beginTransaction();

        /** Usamos o Try Catch para prever erros com o PDO ou de programação */
        try {
            $count = 0;
            $commit = true;

            /** Prepara as queries que serão usadas nesta transação */
            $sql = "INSERT INTO pcd.extratobrd VALUES (:nomeArq, :lancamento, :data, :documento, :numBrd, :saldo, :credito, :debito)";

            /** Realiza a leitura do arquivo */
            $hFile = fopen($fileNameBrd, "r");

            $nomeArq = substr($_FILES["file"]["name"][1], 0, 30);

            while (($line = fgetcsv($hFile, 0, ";")) !== false){
                unset($values);

                $values['nomeArq'] = $nomeArq;

                /** Ignora os registros que não possuem data na primeira célula (pois não são lançamentos) */
                $values['data'] = $hoUtils->dateFormat($line[0]);

                if (!$hoUtils->isValidDate($values['data'], 'Y-m-d')) continue;

                $values['lancamento'] = trim($line[1]);
                $values['documento']  = $line[2] ?: null;

                /** Formata os valores numéricos, que vem como T.HHH,DD (ponto de milhar e vírgula decimal), para o formato THHH.DD (ponto decimal) */
                $values['credito'] = $hoUtils->numberFormat(str_replace(".", "", $line[3]), 0, 2, '.', '');
                $values['debito']  = $hoUtils->numberFormat(str_replace(".", "", $line[4]), 0, 2, '.', '');
                $values['saldo']   = $hoUtils->numberFormat(str_replace(".", "", $line[5]), 0, 2, '.', '');

                if ($values['debito'] < 0) $values['debito'] = $values['debito'] * -1;

                /** O número da autorização vem nos últimos 8 caracteres da descrição do lançamento */
                $values['numBrd'] = ((int) substr($line[1], -8)) ?: null;

                /** Insere o registro no banco e incrementa o contador em caso de sucesso. Caso contrário, reporta o erro e cancela o commit da transação */
                $result = $dbcSQL->execute($sql, $values);

                if (!$result) $count++;
                else {
                    /** Tratamento dos possíveis erros */

                    /** Violação da Unique Key UK_lancamento */
                    $result = strpos($result, 'UK_extb_lancamento') ? "Registro duplicado" : $result;

                    $hoUtils->pushLog(utf8_encode($result) . " - Data: $values[data]; Lançamento: $values[lancamento]; Documento: $values[documento]", "erro");
                }
            }

            fclose($hFile);

            $dbcSQL->endTransaction($commit);

            if ($commit) $hoUtils->pushLog("$count registros do extrato inseridos com sucesso");
        } catch (\PDOException $p) {
            $dbcSQL->endTransaction(false);

            $hoUtils->pushLog("Atenção! Erro com transação PDO encontrada, informe o administrador do sistema (Extr. - $p->getMessage())");
        } catch (\Exception $e) {
            $dbcSQL->endTransaction(false);

            $hoUtils->pushLog("Atenção! Ocorreu um erro, informe o administrador do sistema (Extr. - $e->getMessage())");
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