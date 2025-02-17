<?php
session_start();
function pdo_connect_mysql() {
    // Update the details below with your MySQL details
    $DATABASE_HOST = 'localhost';
    $DATABASE_USER = 'root';
    $DATABASE_PASS = 'Passw0rd';
    $DATABASE_NAME = 'ticketing_system';
    try {
        return new PDO('mysql:host=' . $DATABASE_HOST . ';dbname=' . $DATABASE_NAME . ';charset=utf8', $DATABASE_USER, $DATABASE_PASS);
    } catch (PDOException $exception) {
        exit('Failed to connect to database!');
    }
}

if (!$_SESSION['loggedin']) {
    // Template header, feel free to customize this
    function template_header($title) {
    echo <<<EOT
    <!DOCTYPE html>
    <html>
        <head>
            <meta charset="utf-8">
            <title>$title</title>
            <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
            <link href="style.css" rel="stylesheet" type="text/css">
        </head>
        <body>
        <nav class="navtop">
            <div>
                <h1><a href="index.php">Ticketing System</a></h1>
                <a href="kba.php"><i class="fas fa-book"></i>Knowledge Base</a>
                <a href="create.php"><i class="fas fa-ticket-alt"></i>Create Ticket</a>
                <a href="login.html"><i class="fas fa-key"></i>Login</a>
            </div>
        </nav>
    EOT;
    }
} elseif ($_SESSION['loggedin'] and $_SESSION['type'] == "admin") {
    // Template header, feel free to customize this
    function template_header($title) {
    echo <<<EOT
    <!DOCTYPE html>
    <html>
        <head>
            <meta charset="utf-8">
            <title>$title</title>
            <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
            <link href="style.css" rel="stylesheet" type="text/css">
        </head>
        <body>
        <nav class="navtop">
            <div>
                <h1><a href="index.php">Ticketing System</a></h1>
                <a href="create.php"><i class="fas fa-ticket-alt"></i>Create Ticket</a>
                <a href="adminticket.php"><i class="fas fa-envelope"></i>All Tickets</a>
                <a href="kba.php"><i class="fas fa-book"></i>Knowledge Base</a>
                <a href="createarticle.php"><i class="fas fa-file"></i>Create Article</a>
                <a href="logout.php"><i class="fas fa-key"></i>logout</a>
            </div>
        </nav>
    EOT;
    }
} else {
    // Template header, feel free to customize this
    function template_header($title) {
    echo <<<EOT
    <!DOCTYPE html>
    <html>
        <head>
            <meta charset="utf-8">
            <title>$title</title>
            <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
            <link href="style.css" rel="stylesheet" type="text/css">
            <link href="/live-chat/public/css/style.css" rel="stylesheet" type="text/css">
        </head>
        <body>
        <nav class="navtop">
            <div>
                <h1><a href="index.php">Ticketing System</a></h1>
                <a href="ticket.php"><i class="fas fa-envelope"></i>My Tickets</a>
                <a href="create.php"><i class="fas fa-ticket-alt"></i>Create a Ticket</a>
                <a href="kba.php"><i class="fas fa-book"></i>Knowledge Base</a>
                <a href="logout.php"><i class="fas fa-key"></i>logout</a>
            </div>
        </nav>
    EOT;
    }
}

// Template footer
if ($_SESSION['loggedin'] && $_SESSION['type'] == "user") {
    function template_footer() {
    echo <<<EOT
        <div class="msg_box" style="right: 0px">
        <div class="msg_head">Live Chat</div>
        <div class="contentArea" style="display: none">
            <div class="formArea">
                <div class="titled">Please fill out this form</div>
                <form class="inputFields" onsubmit="return false">
                    <div class="inputContainer">
                        <input class="nameInput" type="text" maxlength="20" pattern="^[a-zA-Z ]{3,}" placeholder=" * Name" required />
                    </div>
                    <div class="inputContainer">
                        <input class="emailInput" type="email" placeholder=" * Email" required />
                    </div>
                    <div class="inputContainer">
                        <input class="phoneInput" type="text" pattern="[0-9\(\)\-\+ ]{8,15}" title="Enter a valid Phone Number" placeholder=" * Phone Number" required />
                    </div>
                    <input type="submit" class="submitBtn">
                </form>
            </div>
            <div class="chatArea" style="display: none">
                <div class="messages">
                    <div class="msg_push_old"></div>
                    <div class="msg_push_new"></div>
                </div>
                <div class='typing'></div>
                <input class="inputMessage" rows="1" placeholder="Type here..."></input>
            </div>
        </div>
        </div>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
        <script src="http://localhost:8080/socket.io/socket.io.js"></script>
        <script src="http://localhost:8080/js/client.js"></script>
        </body>
    </html>
    EOT;
    }
}

function categories() {
    $servername = "localhost";
    $username = "root";
    $password = "Passw0rd";
    $dbname = "ticketing_system";
    $a = "";
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT DISTINCT category FROM articles";
    $result = $conn->query($sql);
    
    echo "<select id='category' name='category' style='width: 224px'>";
    echo "<option value='" . $a . "'>" . $a . "</option>";
    while ($row = $result->fetch_assoc()) {
      echo "<option value='" . $row['category'] . "'>" . $row['category'] . "</option>";
    }
    echo "</select>";

    $conn->close();
}

function status() {
    $servername = "localhost";
    $username = "root";
    $password = "Passw0rd";
    $dbname = "ticketing_system";
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    $a = "";
    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT DISTINCT status FROM tickets";
    $result = $conn->query($sql);
    
    echo "<select id='status' name='status' style='width: 224px'>";
    echo "<option value='" . $a . "'>" . $a . "</option>";
    while ($row = $result->fetch_assoc()) {
      echo "<option value='" . $row['status'] . "'>" . $row['status'] . "</option>";
    }
    echo "</select>";

    $conn->close();
}

?>


