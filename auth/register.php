<?php session_start(); $_SESSION['validation'] = 0; ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Register</title>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="/style.css?<?=time()?>">
    </head>
    <body>
        <?php
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            abstract class Validation 
            {
                const forename = 1;
                const surname = 2;
                const username_taken = 4;
                const username_len = 8;
                const username_sym = 16;
                const email_format = 32;
                const email_taken = 64;
                const password_len = 128;
                const password_upper = 256;
                const password_lower = 512;
                const password_number = 1024;
            };
           /* class Validation extends SplEnum {
                const forename = 1;
                const surname = 2;
                const username_taken = 4;
                const username_len = 8;
                const username_sym = 16;
                const email = 32;
                const password_len = 64;
                const password_upper = 128;
                const password_lower = 256;
                const password_number = 512;
            }*/

            if(isset($_POST['forename']) && isset($_POST['surname']) && isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password']))
            {
                /* Stage 0: Connect to database */
                require_once($_SERVER['DOCUMENT_ROOT'] . '/auth/scripts/db.php');

                /* Stage 0.5: Save form fields in session */
                $_SESSION['register']['forename'] = $_POST['forename'];
                $_SESSION['register']['surname'] = $_POST['surname'];
                $_SESSION['register']['username'] = $_POST['username'];
                $_SESSION['register']['email'] = $_POST['email'];
                $_SESSION['register']['password'] = $_POST['password'];

                /* Stage 1: Perform validation on registration fields */

                /* >>Username */
                $query = 'SELECT username FROM users';
                $sth = $db->prepare($query);
                $sth->execute();
                $rows = $sth->fetchAll(PDO::FETCH_ASSOC);
                foreach($rows as $row)
                {
                    if($row['username'] == $_POST['username'])
                    {
                        $_SESSION['validation'] |= Validation::username_taken; 
                        break;
                    }
                }
                if(strlen($_POST['username']) > 16 || strlen($_POST['username']) < 3)
                    $_SESSION['validation'] |= Validation::username_len;
                if(!ctype_alnum($_POST['username']))
                    $_SESSION['validation'] |= Validation::username_sym;

                /* >>Forename */
                if(!ctype_alpha($_POST['forename']))
                    $_SESSION['validation'] |= Validation::forename;

                /* >>Surname */
                if(!ctype_alpha($_POST['surname']))
                    $_SESSION['validation'] |= Validation::surname;

                /* >>Email */
                $query = 'SELECT email FROM users';
                $sth = $db->prepare($query);
                $sth->execute();
                $rows = $sth->fetchAll(PDO::FETCH_ASSOC);
                foreach($rows as $row)
                {
                    if($row['email'] == $_POST['email'])
                    {
                        $_SESSION['validation'] |= Validation::email_taken; 
                        break;
                    }
                }
                if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
                    $_SESSION['validation'] |= Validation::email_format;

                /* >>Password */
                if(strlen($_POST["password"]) < 8)
                    $_SESSION['validation'] |= Validation::password_len;
                if(!preg_match("#[0-9]+#", $_POST['password']))
                    $_SESSION['validation'] |= Validation::password_number;
                if(!preg_match("#[A-Z]+#", $_POST['password']))
                    $_SESSION['validation'] |= Validation::password_upper;
                if(!preg_match("#[a-z]+#", $_POST['password']))
                    $_SESSION['validation'] |= Validation::password_lower;

                if(!($_SESSION['validation']))
                {
                    /* Stage 2: Update database with valid registration details */
                    $verification_token = hash('sha256', uniqid($_POST['username'], true));
                    $query = 'INSERT INTO users (forename, surname, username, email, password, verification_token) VALUES (:forename, :surname, :username, :email, :password, :verification_token)';
                    $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $sth = $db->prepare($query);
                    $sth->bindParam(':forename', $_POST['forename']);
                    $sth->bindParam(':surname', $_POST['surname']);
                    $sth->bindParam(':username', $_POST['username']);
                    $sth->bindParam(':email', $_POST['email']);
                    $sth->bindParam(':password', $hash);
                    $sth->bindParam(':verification_token', $verification_token);
                    $sth->execute();

                    /* Stage 3: Initiate email verification process */
                    echo 'Send this URL to client: http://localhost/auth/verify.php?username='.$_POST['username'].'&verify='.$verification_token;
                    header('Location: /auth/verify.php');
                }
            }
        ?>
        <div class="container">
            <div class="row-1-col-1">
            </div>
            <div class="row-1-col-2">
            </div>
            <div class="row-1-col-3">
            </div>
            <div class="row-2">
                <form method="post" action="register.php">
                    <label>Forename</label>
                    <input type="text" id="register-forename" name="forename" <?php if(isset($_SESSION['register']['forename'])) echo ' value="' . $_SESSION['register']['forename'] . '" '; ?> required>
                    <?php 
                        if($_SESSION['validation'] && $_SESSION['validation'] & Validation::forename)
                            echo "<pre><em>Must contain alphabetic characters only.</em></pre>";
                    ?>
                    <label>Surname</label>
                    <input type="text" id="register-surname" name="surname" <?php if(isset($_SESSION['register']['surname'])) echo ' value="' . $_SESSION['register']['surname'] . '" '; ?>required>
                    <?php 
                        if($_SESSION['validation'] && $_SESSION['validation'] & Validation::surname)
                            echo "<pre><em>Must contain alphabetic characters only.</em></pre>";
                    ?>
                    <label>Username</label>
                    <input type="text" id="register-username" name="username" <?php if(isset($_SESSION['register']['username'])) echo ' value="' . $_SESSION['register']['username'] . '" '; ?>required>
                    <?php 
                        if($_SESSION['validation'] && $_SESSION['validation'] & Validation::username_taken)
                            echo "<pre><em>This username is already taken.</em></pre>";
                        else if($_SESSION['validation'] && $_SESSION['validation'] & Validation::username_len)
                            echo "<pre><em>Must be between 3-16 characters.</em></pre>";
                        else if($_SESSION['validation'] && $_SESSION['validation'] & Validation::username_sym)
                            echo "<pre><em>Must contain alphanumeric characters only.</em></pre>";
                    ?>
                    <label>Email</label>
                    <input type="text" id="register-email" name="email" <?php if(isset($_SESSION['register']['email'])) echo ' value="' . $_SESSION['register']['email'] . '" '; ?>required>
                    <?php
                        if($_SESSION['validation'] && $_SESSION['validation'] & Validation::email_taken)
                            echo "<pre><em>This email address is already associated with an account.</em></pre>";
                        else if($_SESSION['validation'] && $_SESSION['validation'] & Validation::email_format)
                            echo "<pre><em>This is not a valid email address.</em></pre>";
                    ?>
                    <label>Password</label>
                    <input type="password" id="login-pass" name="password" <?php if(isset($_SESSION['register']['password'])) echo ' value="' . $_SESSION['register']['password'] . '" '; ?>required>
                    <?php
                        if($_SESSION['validation'] && $_SESSION['validation'] & Validation::password_len)
                            echo "<pre><em>Must be 8 or more characters.</em></pre>";
                        if($_SESSION['validation'] && $_SESSION['validation'] & Validation::password_number)
                            echo "<pre><em>Must contain at least one numeric character.</em></pre>";
                        if($_SESSION['validation'] && $_SESSION['validation'] & Validation::password_upper)
                            echo "<pre><em>Must contain at least one uppercase character.</em></pre>";
                        if($_SESSION['validation'] && $_SESSION['validation'] & Validation::password_lower)
                            echo "<pre><em>Must contain at least one lowercase character.</em></pre>";
                    ?>
                    <input type="submit" id="register-button" value="Register">
                    <p>Already have a login and password? <a href="/auth/login.php">Sign in</a></p>
                </form>
            </div>
            <div class="row-3">
            </div>
        </div>
    </body>
</html>

