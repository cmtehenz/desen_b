<!doctype html>
    <head>
        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

        <title>BID - Login</title>

        <link rel="stylesheet" href="/library/stylesheets/all.css" type="text/css" />
        <script src="/library/javascripts/all.js"></script>
    </head>
    <body>
        <?php
            include $_SERVER['DOCUMENT_ROOT'] . '/library/scripts/autoloader.php';

            use Library\Classes\connectMSSQL as conSQL;

            session_start();

            $hoUtils = new Library\Classes\Utils();

            $xml = simplexml_load_file($_SERVER['DOCUMENT_ROOT'] . '/.config.xml') or die ("Erro ao carregar configurações, informe o administrador.");

            $_SESSION['idEmpresa'] = $xml->idEmpresa->__toString();
            $_SESSION['dbHost']    = $xml->database->host->__toString();
            $_SESSION['dbName']    = $xml->database->name->__toString();
            $_SESSION['dbUser']    = $xml->database->user->__toString();
            $_SESSION['dbPswd']    = $xml->database->password->__toString();

            $dbcSQL = new conSQL();
            $dbcSQL->connect();

            if(!isset($_POST['usuario'])) $hoUtils->gerarHistoricoLogin($user, $password, "Favor preencher o usuário");

            $user     = $_POST['usuario'];
            $password = $_POST['password'];

            if(strlen($user)     == 0) $hoUtils->gerarHistoricoLogin($user, $password, "Favor preencher o usuário");
            if(strlen($password) == 0) $hoUtils->gerarHistoricoLogin($user, $password, "Favor preencher a senha");

            $usuario = $dbcSQL->selectTopOne("SELECT TOP 1 * FROM usuario WHERE password = '" . sha1(strtolower($user) . $password). "'");

            if ($usuario['id_usuario'] == 0) $hoUtils->gerarHistoricoLogin($user, $password, "Usuário / senha inválido");
            else
            {
                // Dados empresa logada
                $empresa = $dbcSQL->selectTopOne("SELECT TOP 1 * FROM empresa", array( $dbcSQL->whereParam("id", $_SESSION['idEmpresa']) ));

                $_SESSION['idUsuario']    = $usuario['id_usuario'];
                $_SESSION['nomeUsuario']  = $usuario['nome'];
                $_SESSION['nomeEmpresa']  = $empresa['nomeFantasia'];
                $_SESSION['dbERPHost']    = $empresa['dbHost'];
                $_SESSION['dbERPName']    = $empresa['dbName'];
                $_SESSION['dbERPUser']    = $empresa['dbUser'];
                $_SESSION['dbERPPswd']    = $empresa['dbPswd'];
                $_SESSION['anoInicioERP'] = $empresa['anoInicioERP'];
                $_SESSION['version']      = $empresa['version'];

                $_SESSION['smtp']['host'] = $empresa['smtpHost'];
                $_SESSION['smtp']['mail'] = $empresa['smtpMail'];
                $_SESSION['smtp']['pswd'] = $empresa['smtpPswd'];
                $_SESSION['smtp']['name'] = $empresa['smtpName'];

                header("Location: ./dashboard.php");
            }
        ?>
    </body>
</html>