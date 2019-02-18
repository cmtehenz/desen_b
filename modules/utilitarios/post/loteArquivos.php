<?php
    /**
     * Script desenvolvido com propósito de ler qualquer diretório e processar os arquivos contidos nele.
     *
     * @version 1.0
     *
     * Nesta primeira versão foi desenvolvida a leitura de lotes da Conciliação Pamcard x Bradesco. Quando surgirem novas integrações
     * este arquivo pode ser resetado e alterado para atender a nova demanda, pois suas versões anteriores constarão para sempre no Git
     * caso haja necessidade de utilizar uma integração passada
     */

    namespace Modulos\Utilitarios\Post;

    require $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/includes.php';

    use PDO;

    header('Content-Type: text/html; charset=utf-8');

    $hoUtils = new \Library\Classes\Utils();
    $dbcSQL  = new \Library\Classes\connectMSSQL();

    error_reporting(E_ERROR);
    ini_set("display_errors", 1);

    /** Conexão PDO com o banco */
    $dbcSQL->connect();
    $dbcSQL->getPDO()->setAttribute(PDO::SQLSRV_ATTR_ENCODING, PDO::SQLSRV_ENCODING_SYSTEM);

    /**
     * Faça abaixo a leitura e manipulação de cada tipo de arquivo desejado
     */

    /** Arquivos de conciliação */
    echo "<b style='font-size: 20px; color: red;'>Log dos arquivos de conciliação</b><br /><br />";

    $dir = "C:\\inetpub\\wwwroot\\loteArquivos\\";

    $files = glob($dir . "*.[tT][xX][tT]");

    foreach ($files as $file) {
        /** Usamos o Try Catch para prever erros com o PDO ou de programação */
        try {
            $dbcSQL->beginTransaction();

            $nomeArq = substr($file, strrpos($file, "\\") + 1, 30);

            echo "<b style='font-size: 20px;'>Arquivo: $nomeArq</b><br /><br />";

            $contrato = 0;
            $pagamento = 0;
            $adiantamento = 0;
            $commit = true;

            /** Prepara as queries que serão usadas nesta transação */
            $sqlCont = "INSERT INTO pcd.contrato VALUES (:nomeArq, :idViagem, :cte, :filial, :bipe, :placa, :cpf, :nome, :numAut, :numBrd, :tpParc, null)";
            $sqlPWeb = "INSERT INTO pcd.pagamento VALUES (:nomeArq, :idViagem, :cte, :filial, :placa, :cpf, :nome, :numAut, :numBrd)";
            $sqlAdto = "INSERT INTO pcd.adiantamento VALUES (:nomeArq, :idViagem, :cpf, :nome, :numAut, :numBrd)";

            /** Realiza a leitura do arquivo */
            $hFile = fopen($file, "r");

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

                    echo (utf8_encode($result) . " - Documento: $documento - Aut. Bradesco: $values[numBrd]<br />");
                }
            }

            fclose($hFile);

            $dbcSQL->endTransaction($commit);

            if ($commit){
                if ($contrato > 0)     echo "$contrato registros de integração inseridos com sucesso<br />";
                if ($pagamento > 0)    echo "$pagamento pagamentos do portal web inseridos com sucesso<br />";
                if ($adiantamento > 0) echo "$adiantamento adiantamentos de frota inseridos com sucesso<br />";
            }
        } catch (\PDOException $p) {
            $dbcSQL->endTransaction(false);

            echo ("Atenção! Erro com transação PDO encontrada, informe o administrador do sistema (Conc. - $p->getMessage())<br />");
        } catch (\Exception $e) {
            $dbcSQL->endTransaction(false);

            echo ("Atenção! Ocorreu um erro, informe o administrador do sistema (Conc. - $e->getMessage())<br />");
        }

        echo "<br /><hr style='height: 5px; background-color: #000;'>";
    }

    /** Arquivos de extrato do Bradesco */
    echo "<b style='font-size: 20px; color: red;'>Log dos extratos do Bradesco</b><br /><br />";

    $dir = "C:\\inetpub\\wwwroot\\loteArquivos\\";

    $files = glob($dir . "*.[cC][sS][vV]");

    foreach ($files as $file) {
        /** Usamos o Try Catch para prever erros com o PDO ou de programação */
        try {
            $dbcSQL->beginTransaction();

            $nomeArq = substr($file, strrpos($file, "\\") + 1, 30);

            echo "<b style='font-size: 20px;'>Arquivo: $nomeArq</b><br /><br />";

            $count = 0;
            $commit = true;

            /** Prepara as queries que serão usadas nesta transação */
            $sql = "INSERT INTO pcd.extratobrd VALUES (:nomeArq, :lancamento, :data, :documento, :numBrd, :saldo, :credito, :debito)";

            /** Realiza a leitura do arquivo */
            $hFile = fopen($file, "r");

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

                    echo (utf8_encode($result) . " - Data: $values[data]; Lançamento: $values[lancamento]; Documento: $values[documento]<br />");
                }
            }

            fclose($hFile);

            $dbcSQL->endTransaction($commit);

            if ($count) echo "$count registros do extrato inseridos com sucesso<br />";
        } catch (\PDOException $p) {
            $dbcSQL->endTransaction(false);

            echo ("Atenção! Erro com transação PDO encontrada, informe o administrador do sistema (Extr. - $p->getMessage())<br />");
        } catch (\Exception $e) {
            $dbcSQL->endTransaction(false);

            echo ("Atenção! Ocorreu um erro, informe o administrador do sistema (Extr. - $e->getMessage())<br />");
        }

        echo "<br /><hr style='height: 5px; background-color: #000;'>";
    }

    $dbcSQL->disconnect();
?>