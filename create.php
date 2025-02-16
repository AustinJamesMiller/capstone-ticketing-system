<?php

session_start();

include 'functions.php';

$pdo = pdo_connect_mysql();

// Output message variable
$msg = '';

if (!isset($_SESSION['loggedin'])) {
	header('Location: login.html');
	exit;
}

// Check if POST data exists (the user submitted the form)
if (isset($_POST['subject'], $_POST['msg'], $_POST['haso'])) {
    // Validation checks... make sure the POST data is not empty
    if (empty($_POST['subject']) || empty($_POST['msg']) || empty($_POST['haso'])) {
        $msg = 'Please complete the form!';
    } else {
        // Insert new record into the tickets table
        $stmt = $pdo->prepare('INSERT INTO tickets (subject, email, msg, haso) VALUES (?, ?, ?, ?)');
        $stmt->execute([ $_POST['subject'], $_SESSION['email'], $_POST['msg'], $_POST['haso'] ]);
        
        //This executes our vectorization program to ensure the new ticket is immediately searchable. "> /dev/null 2>&1 &" redirects and cmd output like errors to "dev/null" where they are discarded, the final "&" tells the script to generate a new process for itself so we don't have to wait for it to complete before the rest of the php can complete (decreasing user load time).
        shell_exec("python3 /var/www/html/vectorization.py 'ticket' > /dev/null 2>&1 &");
        
        // Redirect to the view ticket page. The user will see their created ticket on this page.
        if ($_SESSION['type'] == 'user') {
		header('Location: userview.php?ticket_id=' . $pdo->lastInsertId());
	}
	if ($_SESSION['type'] == 'admin') {
		header('Location: adminview.php?ticket_id=' . $pdo->lastInsertId());
	}
        exit;
    }
}
?>

<?=template_header('Create Ticket')?>

<div class="content create">
	<h2>Create Ticket</h2>
    <form action="create.php" method="post">
        <label for="subject">Subject</label>
        <input type="text" name="subject" placeholder="Subject" id="subject" required>
        <label for="subject">Hardware/Software</label>
        <input type="text" name="haso" placeholder="Dell XPS Laptop" id="haso" required>
        <label for="msg">Message</label>
        <textarea name="msg" placeholder="Enter your message here..." id="msg" required></textarea>
        <input type="submit" value="Create">
    </form>
    <?php if ($msg): ?>
    <p><?=$msg?></p>
    <?php endif; ?>
</div>

<?=template_footer()?>
