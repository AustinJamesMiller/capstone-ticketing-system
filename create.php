<?php

session_start();

include 'functions.php';

$pdo = pdo_connect_mysql();

$msg = '';

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.html');
    exit;
}

// Check if POST data exists (the user submitted the form)
if (isset($_POST['subject'], $_POST['msg'], $_POST['haso'])) {
    if (empty($_POST['subject']) || empty($_POST['msg']) || empty($_POST['haso'])) {
        $msg = 'Please complete the form!';
    } else {
        $stmt = $pdo->prepare('INSERT INTO tickets (subject, email, msg, haso) VALUES (?, ?, ?, ?)');
        $stmt->execute([ $_POST['subject'], $_SESSION['email'], $_POST['msg'], $_POST['haso'] ]);
        
        // We execute the vectorization script so that our new article is immediately able to be searched for
        shell_exec("python3 /var/www/html/vectorization.py 'ticket' > /dev/null 2>&1 &");

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
