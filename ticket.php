<?php

session_start();

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.html');
    exit;
}

include 'functions.php';

$pdo = pdo_connect_mysql();

$stmt = $pdo->prepare('SELECT ticket_id, status, subject, msg, created FROM tickets WHERE email = ?');
$stmt->execute([$_SESSION['email']]);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<?=template_header('Tickets')?>

<div class="content home">
    <h2>Tickets</h2>
    <p>Current Tickets</p>
    <div class="tickets-list">
        <?php foreach ($tickets as $ticket): ?>
            <a href="<?=$_SESSION['type']?>view.php?ticket_id=<?=$ticket['ticket_id']?>" class="ticket">
            <span class="con">
                <?php if ($ticket['status'] == 'open'): ?>
                    <i class="far fa-clock fa-2x"></i>
                <?php elseif ($ticket['status'] == 'resolved'): ?>
                    <i class="fas fa-check fa-2x"></i>
                <?php elseif ($ticket['status'] == 'closed'): ?>
                    <i class="fas fa-times fa-2x"></i>
                <?php endif; ?>
            </span>
            <span class="con">
                <span class="subject"><?=htmlspecialchars($ticket['subject'], ENT_QUOTES)?></span>
                <span class="msg"><?=htmlspecialchars($ticket['msg'], ENT_QUOTES)?></span>
            </span>
            <span class="con created"><?=date('F dS, G:ia', strtotime($ticket['created']))?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?=template_footer()?>
