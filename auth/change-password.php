<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Login</title>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="/style.css?<?=time()?>">
    </head>
    <body>
        <div class="container">
            <div class="row-1-col-1">
            </div>
            <div class="row-1-col-2">
            </div>
            <div class="row-1-col-3">
            </div>
            <div class="row-2">
                <form method="post" action="change-password.php">
                    <label>New password</label>
                    <input type="password" id="change-password" name="password" required>
                    <input type="submit" id="change-button" name="submit" value="Reset password">
                    <?php
                        if(isset($_POST['password']) && isset($_GET['token']))
                        {
                            require_once($_SERVER['DOCUMENT_ROOT'] . '/scripts/auth/db.php');
                            $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                            $query = 'UPDATE users SET password=:password WHERE change_password_token=:token';
                            $sth = $db->prepare($query);
                            $sth->bindParam(':password', $hash);
                            $sth->bindParam(':token', $_POST['token']);
                            $sth->execute();
                            echo '<pre><em>Your password has been updated. <a href="/auth/login.php">Sign in.</a></em></pre>';
                        }
                    ?>
                </form>
            </div>
            <div class="row-3">
            </div>
        </div>
    </body>
</html>

