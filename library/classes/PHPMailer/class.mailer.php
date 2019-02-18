<?php
    /**
     * Classe customizada do PHPMailer para facilitar o uso em produtos da Case-E
     *
     * @author Paulo Silva
     * @date 22/04/2016
     * @version 1.2 - Versão do PHPMailer: 5.2.13
     */

    /**
     * Mailer - Custom da biblioteca PHPMailer
     * @package PHPMailer
     * @author Paulo Silva
     */
    final class Mailer extends PHPMailer {
        /**
         * CSS aplicado ao corpo do e-mail, por padrão utilizará o definido nesta classe (fonte Verdana, 13px, com parágrafo e rodapé padrão)
         * @var string Código da tag <style> responsável pelo estilo do corpo do e-mail
         */
        public $css;

        /**
         * Faz a configuração inicial do Mailer
         *
         * @author Paulo Silva
         * @param bool $configSMTP Indica se a configuração SMTP padrão deve ser feita. Deafult = True
         */
        public function setConfig($configSMTP = true){
            $this->setLanguage('pt');

            if ($configSMTP) $this->configSMTP();

            $this->css =
                "<style='text/css'>
                    p { text-indent: 50px; }
                    table { 
                        margin-left: 50px; border: black 1px solid; border-collapse: collapse; 
                        font-family: Verdana,Geneva,sans-serif; font-size: 13px; text-align: center;
                    } 
                    table td, table th { border: 1px solid black; padding: 5px 10px 5px 10px; }
                    .body { font-family: Verdana,Geneva,sans-serif; font-size: 13px; text-align: justify; }
                    .lista { margin-left: 50px; }
                    .red { color: red; } .green { color: green; } .yellow { color: #F5980C; }
                    .red-bolder { color: red; font-weight: bold; } .green-bolder { color: green; font-weight: bold; } .yellow-bolder { color: #F5980C; font-weight: bold; }
                    .bolder { font-weight: bold; }
                </style>";
        }

        /**
         * Faz a configuração dos parâmetros SMTP no mailer, usando os endereços configurados na empresa como autenticador padrão
         *
         * @author Paulo Silva
         */
        public function configSMTP(){
            /** Configuração dos dados para login no servidor SMTP */
            $this->isSMTP();
            $this->SMTPAuth = true;
            $this->Port = 587;

            $this->Host     = $_SESSION['smtp']['host'];
            $this->Username = $_SESSION['smtp']['mail'];
            $this->Password = $_SESSION['smtp']['pswd'];

            $this->setFrom($_SESSION['smtp']['mail'], $_SESSION['smtp']['name']);
        }

        /**
         * Realiza o envio do e-mail previamewnte configurado
         *
         * @author Paulo Silva
         * @date 02/05/2016
         * @param string $subject Assunto da mensagem
         * @param string $body Corpo da mensagem sem incluir o CSS
         * @return string Mensagem de sucesso ou informações sobre o erro gerado pelo Mailer
         */
        public function send($subject, $body){
            $this->Subject = $subject;
            $this->msgHTML($this->css . $body);

            /** Realiza o envio e verifica por erros */
            if (!parent::send())
                return "Erro no envio do e-mail, mensagem retornada pelo Mailer: " . $this->ErrorInfo;
            else
                return "E-mail enviado com sucesso!";
        }
    }
?>