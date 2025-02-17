<?php
session_start();
?>

<html>
    <head>
        <meta charset="utf-8">
        <title>Register</title>
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
        <link href="style.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <div class="login">
            <h1>Register</h1>
            <form action="regauth.php" method="post" autocomplete="off">
            <label for="user">
                <i class="fas fa-user"></i>
            </label>
            <input type="text" name="user" placeholder="Username" id="user" required>
            <label for="full_name">
                <i class="fas fa-user"></i>
            </label>
            <input type="text" name="full_name" placeholder="John Doe" id="full_name" required>
            <?php if (isset($_SESSION['user'])):?>
      	        <p style="padding-left:50px; padding-right:50px; font-size:14px; color:red; align-text:center;">Username should be in the format "John_Doe"</p>
            <?php endif;?>
            <label for="email">
                <i class="fas fa-envelope"></i>
            </label>
            <input type="text" name="email" placeholder="Email" id="email" required>
            <?php if (isset($_SESSION['email'])):?>
      	        <p style="padding-left:20px; padding-right:20px; font-size:14px; color:red;">Email must match the format of w00000@nscc.ca</p>
            <?php endif;?>
            <label for="pass">
                <i class="fas fa-lock"></i>
            </label>
            <input type="password" name="password" placeholder="Password" id="password" required>
            <?php if (isset($_SESSION['password'])):?>
                <p style="padding-left:20px; padding-right:20px; font-size:14px; color:red;">Requirements: minimum length 8, one uppercase, one lowercase, one digit and one special character</p>
            <?php endif;?>
            <label for="confirm">
                <i class="fas fa-lock"></i>
            </label>
            <input type="password" name="confirm" placeholder="Re-enter Password" id="confirm" required>
            <?php if (isset($_SESSION['confirm'])):?>
                <p style="padding-left:20px; padding-right:20px; font-size:14px; color:red;">Passwords must match</p>
            <?php endif;?>
            <input type="submit" value="Register">
            </form>
        </div>
    </body>
</html>
