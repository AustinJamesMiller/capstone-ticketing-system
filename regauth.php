<?php
session_start();

include 'functions.php';

$pdo = pdo_connect_mysql();

// Password checking. Ensure 8+ chars, 1 digit, 1 lowercase, 1 uppercase, 1 special character
$password_pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/';
$email_pattern = '/^.*@nscc.ca$/';
$user_pattern = '/[\w\d]*/';
$fails = ["user","email","password"];

foreach($fails as $fail) {
    $_POST["$fail"] = iconv("ucs2", "utf8", iconv("utf8", "ucs2//IGNORE", $_POST["$fail"]));
    if (!preg_match(${$fail . "_pattern"}, $_POST["$fail"])) {
        $_SESSION["$fail"] = "failed";
    } else {
        unset($_SESSION["$fail"]);
    }
}

if ($_POST['password'] != $_POST['confirm']) {
    $_SESSION['confirm_fail'] = "failed";
} else {
    unset($_SESSION['confirm_fail']);
}

if (isset($_SESSION["user"]) || isset($_SESSION["email"]) || isset($_SESSION["password"]) || isset($_SESSION['confirm'])) {
    header("Location: reg.php");
}

if (!isset($_POST['user'], $_POST['password'], $_POST['email'])) {
    exit('Please complete the registration form!');
}

if (empty($_POST['user']) || empty($_POST['password']) || empty($_POST['email'])) {
    exit('Please complete the registration form');
}

$stmt = $pdo->prepare('SELECT user_id, email FROM accounts WHERE username = ? OR email = ?');
$stmt->execute([ $_POST['user'], $_POST['email'] ]);
$account = $stmt->fetch(PDO::FETCH_ASSOC);

if ($account) {
    // Username or email already exists
    echo 'Username or Email already exists';
    header("refresh:5; url=reg.php");
} else {
    $stmt = $pdo->prepare('INSERT INTO accounts (username, password, email, full_name) VALUES (?, ?, ?, ?)');
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt->execute([ $_POST['user'], $password, $_POST['email'], $_POST['full_name'] ]);
    echo "Account created successfully! Redirecting to login page...";
    header("refresh:5; url=login.html");
}
?>
