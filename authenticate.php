<?php

include 'functions.php';

$pdo = pdo_connect_mysql();

if (!isset($_POST['username'], $_POST['password'])) {
    exit('Please fill both the username and password fields!');
}

$stmt = $pdo->prepare('SELECT user_id, password, email, type FROM accounts WHERE username = ?');
$stmt->execute([ $_POST['username'] ]);
$account = $stmt->fetch(PDO::FETCH_ASSOC);

if ($account) {
    if (password_verify($_POST['password'], $account['password'])) {
        session_regenerate_id();
        $_SESSION['loggedin'] = TRUE;
        $_SESSION['id'] = $account['user_id'];
        $_SESSION['email'] = $account['email'];
        $_SESSION['type'] = $account['type'];
        if ($_SESSION['type'] == 'admin') {
            header('Location: adminticket.php');
        } else {
            header('Location: ticket.php');
        }
    } else {
        // Incorrect password
        echo 'Incorrect username and/or password!';
        header("refresh:5; url=login.html");
    }
} else {
    // Incorrect username
    echo 'Incorrect username and/or password!';
    header("refresh:5; url=login.html");
}

?>
