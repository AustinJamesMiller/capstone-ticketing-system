<?php

include 'functions.php';

$pdo = pdo_connect_mysql();

if (!isset($_GET['ticket_id'])) {
    exit('No ID specified!');
}

$stmt = $pdo->prepare('SELECT subject, status, created, msg, claimed, resolution, resolved, agent_id FROM tickets WHERE ticket_id = ?');
$stmt->execute([ $_GET['ticket_id'] ]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    exit('Invalid ticket ID!');
}

if (isset($_GET['status']) && in_array($_GET['status'], array('open', 'closed', 'resolved'))) {
    $stmt = $pdo->prepare('UPDATE tickets SET status = ? WHERE ticket_id = ?');
    $stmt->execute([ $_GET['status'], $_GET['ticket_id'] ]);
    header('Location: userview.php?ticket_id=' . $_GET['ticket_id']);
    exit;
}

if (isset($_POST['msg']) && !empty($_POST['msg'])) {
    // Insert the new comment into the "tickets_comments" table
    $stmt = $pdo->prepare('INSERT INTO tickets_comments (ticket_id, msg) VALUES (?, ?)');
    $stmt->execute([ $_GET['ticket_id'], $_POST['msg'] ]);
    header('Location: userview.php?ticket_id=' . $_GET['ticket_id']);
    exit;
}

$stmt = $pdo->prepare('SELECT created, msg FROM tickets_comments WHERE ticket_id = ? ORDER BY created DESC');
$stmt->execute([ $_GET['ticket_id'] ]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?=template_header('Ticket')?>

<div class="content view">
    <h2><?=htmlspecialchars($ticket['subject'], ENT_QUOTES)?> <span class="<?=$ticket['status']?>">(<?=$ticket['status']?>)</span></h2>
    <p style="font-weight:bold; padding-top:10px">Operator: <?=$agent['full_name']?></p>
    <div class="ticket">
        <p class="created"><?=date('F dS, G:ia', strtotime($ticket['created']))?></p>
        <p class="msg"><?=nl2br(htmlspecialchars($ticket['msg'], ENT_QUOTES))?></p>
    </div>
    <div class="comments">
        <?php foreach($comments as $comment): ?>
            <div class="comment">
                <div>
                    <i class="fas fa-comment fa-2x"></i>
                </div>
                <p>
                    <span><?=date('F dS, G:ia', strtotime($comment['created']))?></span>
                    <?=nl2br(htmlspecialchars($comment['msg'], ENT_QUOTES))?>
                </p>
            </div>
        <?php endforeach; ?>
        <form action="userview.php?ticket_id=<?=$_GET['ticket_id']?>" method="post">
            <textarea name="msg" placeholder="Enter your comment..."></textarea>
            <input type="submit" value="Post Comment">
        </form>
    </div>
</div>

<?=template_footer()?>

