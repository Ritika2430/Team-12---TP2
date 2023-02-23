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
                <form method="post" action="forgot-password.php">
                    <label>Email address</label>
                    <input type="text" id="forgot-email" name="email" required>
                    <input type="submit" id="forgot-button" name="submit" value="Send link to email">
                    <?php
                        if(isset($_POST['submit']))
                        {
                            if(isset($_POST['email']))
                            {
                                require_once($_SERVER['DOCUMENT_ROOT'] . '/auth/scripts/db.php');
                                $query = 'SELECT email FROM users';
                                $sth = $db->prepare($query);
                                $sth->execute();
                                $rows = $sth->fetchAll(PDO::FETCH_ASSOC);
                                foreach($rows as $row)
                                {
                                    if($row['email'] == $_POST['email'])
                                    {
                                        /* Send email like {domain}/auth/changepassword.php?token=XXXX */
                                        break;
                                    }
                                }
                            }
                            echo "<pre><em>If this email address is found to be associated with an account, you will receive an email shortly.<em></pre>";
                        }
                    ?>
                </form>
            </div>
            <div class="row-3">
            </div>
        </div>
    </body>
</html>

