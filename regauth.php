<?php
session_start();

// Change this to your connection info.
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = 'Passw0rd';
$DATABASE_NAME = 'ticketing_system';
// Try and connect using the info above.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) {
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

// Password checking. Ensure 8+ chars, 1 digit, 1 lowercase, 1 uppercase, 1 special character
$pass_pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/';
$email_pattern = '/^.*@nscc.ca$/';
$user_pattern = '/[\w\d]*/';
$fails = ["user","email","pass"];

foreach($fails as $fail) {
	$_POST["$fail"] = iconv("ucs2", "utf8", iconv("utf8", "ucs2//IGNORE", $_POST["$fail"]));
	if (!preg_match(${$fail . "_pattern"}, $_POST["$fail"])) {
		$_SESSION["$fail"] = "failed";
	} else {
		unset($_SESSION["$fail"]);
	}
}

if ($_POST['pass'] != $_POST['confirm']) {
	$_SESSION['confirm_fail'] = "failed";
} else {
	unset($_SESSION['confirm_fail']);
}

if (isset($_SESSION["user"]) || isset($_SESSION["email"]) || isset($_SESSION["pass"]) || isset($_SESSION['confirm'])) {
	header("Location: reg.php");
}

// Now we check if the data was submitted, isset() function will check if the data exists.
if (!isset($_POST['user'], $_POST['pass'], $_POST['email'])) {
	// Could not get the data that should have been sent.
	exit('Please complete the registration form!');
}
// Make sure the submitted registration values are not empty.
if (empty($_POST['user']) || empty($_POST['pass']) || empty($_POST['email'])) {
	// One or more values are empty.
	exit('Please complete the registration form');
}

// We need to check if the account with that username exists.
if ($stmt = $con->prepare('SELECT user_id, password, email FROM accounts WHERE username = ?')) {
	// Bind parameters (s = string, i = int, b = blob, etc), hash the password using the PHP password_hash function.
	$stmt->bind_param('s', $_POST['user']);
	$stmt->execute();
	$stmt->store_result();
	// Store the result so we can check if the account exists in the database.
	if ($stmt->num_rows > 0) {
		// Username already exists
		echo 'Username exists, please choose another!';
		header("refresh:5; url=reg.php");
	} else {
		if ($stmt = $con->prepare('SELECT user_id, password, email FROM accounts WHERE email = ?')) {
			// Bind parameters (s = string, i = int, b = blob, etc), hash the password using the PHP password_hash function.
			$stmt->bind_param('s', $_POST['email']);
			$stmt->execute();
			$stmt->store_result();
			if ($stmt->num_rows > 0) {
				// Username already exists
				echo 'Email already exists, please choose another!';
				header("refresh:5; url=reg.php");
			} else {
				// Username doesn't exists, insert new account
				if ($stmt = $con->prepare('INSERT INTO accounts (username, password, email, full_name) VALUES (?, ?, ?, ?)')) {
					// We do not want to expose passwords in our database, so hash the password and use password_verify when a user logs in.
					$password = password_hash($_POST['pass'], PASSWORD_DEFAULT);
					$stmt->bind_param('ssss', $_POST['user'], $password, $_POST['email'], $_POST['full_name']);
					$stmt->execute();
					echo "Account created successfully! Redirecting to login page...";
					header("refresh:5; url=login.html");
				} else {
					// Something is wrong with the SQL statement, so you must check to make sure your accounts table exists with all three fields.
					echo 'Could not prepare statement!';
				}
			}
		}
	}
	$stmt->close();
} else {
	// Something is wrong with the SQL statement, so you must check to make sure your accounts table exists with all 3 fields.
	echo 'Could not prepare statement!';
}
$con->close();
?>
