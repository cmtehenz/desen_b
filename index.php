<!doctype html>
<html class="no-js" lang="pt-br">
    <head>
        <title>BID - Login</title>

        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta name="description" content="" />
        <meta name="author" content="Case Electronic" />
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="stylesheet" href="stylesheets/reset.css" type="text/css" media="screen" title="no title" />
        <link rel="stylesheet" href="stylesheets/text.css" type="text/css" media="screen" title="no title" />
        <link rel="stylesheet" href="stylesheets/buttons.css" type="text/css" media="screen" title="no title" />
        <link rel="stylesheet" href="stylesheets/theme-default.css" type="text/css" media="screen" title="no title" />
        <link rel="stylesheet" href="stylesheets/login.css" type="text/css" media="screen" title="no title" />

        <script src="js/all.js"></script>
    </head>
    <body>
        <div id="login">
            <h1>Dashboard</h1>
            <div id="login_panel">
                <form action="login.php" method="post" accept-charset="utf-8">
                    <div class="login_fields">
                        <div class="field">
                            <label for="email">Usuário</label>
                            <input type="text" name="usuario" value="" id="usuario" tabindex="1" placeholder="Insira seu nome de usuário" />
                        </div>
                        <div class="field">
                            <label for="password">Senha</label>
                            <input type="password" name="password" value="" id="password" tabindex="2" placeholder="Insira sua senha" />
                        </div>
                    </div> <!-- .login_fields -->
                    <div class="login_actions" style="text-align: center;">
                        <button type="submit" class="btn btn-primary" tabindex="3"><span class="icon-unlock-stroke"></span> Login</button>
                    </div>
                </form>
            </div> <!-- #login_panel -->
        </div> <!-- #login -->
    </body>
</html>