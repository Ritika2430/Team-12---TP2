<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Login</title>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="/auth-style.css?<?=time()?>">
    </head>
    <body class="auth-body">
        <?php
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
            if(isset($_POST['id']) && isset($_POST['password']))
            {
                require_once($_SERVER['DOCUMENT_ROOT'] . '/auth/scripts/db.php');

                $id_type= str_contains($_POST['id'], '@');
                $query = 'SELECT * FROM users WHERE ' . ($id_type ? 'email' : 'username') . '=:id';

                $sth = $db->prepare($query);
                $sth->bindParam(':id', $_POST['id']);
                $sth->execute();
                $row = $sth->fetch(PDO::FETCH_ASSOC);

                if($_POST['id'] == ($id_type ? $row['email'] : $row['username']) && password_verify($_POST['password'], $row['password']))
                {
                    if($row['verified'] == FALSE)
                        header('Location: /auth/login.php?failed=1&reason=1'); # Email has not been verified
                    else
                    {
                        $_SESSION['auth'] = [
                            'forename' => $row['forename'],
                            'surname' => $row['surname'],
                            'username' => $row['username'],
                            'email' => $row['email'],
                            'password' => $row['password'],
                            'privileges' => $row['privileges'],
                        ];

                        if(isset($_POST['remember-me']))
                            $query = 'UPDATE users SET remember_me=TRUE WHERE ' . ($id_type ? 'email' : 'username') . ':=id';
                            /***
                                Code here.
                            ***/
                        header('Location: /index.php');
                    }
                }
                else
                    header('Location: /auth/login.php?failed=1&reason=0'); # Username or password is incorrect
            }
        ?>
        <div class="container">
            <div class="row-1-col-1">
            </div>
            <div class="row-1-col-2">
            </div>
            <div class="row-1-col-3">
            </div>
            <div class="row-2" class="auth-div">
                <form method="post" action="login.php" class="auth-form>
                    <label>Username or email</label>
                    <input type="text" id="login-id" name="id" required>
                    <label>Password</label>
                    <input type="password" id="login-pass" name="password" required>
                    <input type="checkbox" id="login-rm-token" name="remember-me">
                    <label>Remember me</label>
                    <a id="fr" href="forgot-password.php">Forgot your password?</a>
                    <input type="submit" id="login-button" value="Sign in">
                    <p>Don't have an account yet? <a href="/auth/register.php">Register now</a></p>
                    <?php
                        if(isset($_GET['failed']))
                            if(isset($_GET['reason']))
                                if($_GET['reason'] == 0)
                                    echo "<pre style=\"color: red;\"\"><em>(Your login credentials are incorrect).</em></pre>";
                                else if($_GET['reason'] == 1)
                                    echo "<pre style=\"color: red;\"\"><em>(You have to verify your email address to activate your account).</em></pre>";
                    ?>
                </form>
            </div>
            <div class="row-3">
            </div>
        </div>
    </body>
</html>


