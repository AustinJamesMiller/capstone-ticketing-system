<?php

session_start();

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

if (!empty($ticket['agent_id'])) {
    $stmt = $pdo->prepare('SELECT full_name FROM accounts WHERE user_id = ?');
    $stmt->execute([ $ticket['agent_id'] ]);
    $agent = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (isset($_GET['claimed'])) {
    if ($_GET['claimed'] == 1) {
        $stmt = $pdo->prepare('UPDATE tickets SET agent_id = ?, claimed = ? WHERE ticket_id = ?');
        $stmt->execute([ $_SESSION['id'], $_GET['claimed'], $_GET['ticket_id'] ]);
        header('Location: adminview.php?ticket_id=' . $_GET['ticket_id']);
    }
    if ($_GET['claimed'] == 0) {
        $stmt = $pdo->prepare('UPDATE tickets SET agent_id = ?, claimed = ? WHERE ticket_id = ?');
        $stmt->execute([ NULL, $_GET['claimed'], $_GET['ticket_id'] ]);
        header('Location: adminview.php?ticket_id=' . $_GET['ticket_id']);
    }
}

if (isset($_GET['status']) && in_array($_GET['status'], array('open', 'closed'))) {
    $stmt = $pdo->prepare('UPDATE tickets SET status = ? WHERE ticket_id = ?');
    $stmt->execute([ $_GET['status'], $_GET['ticket_id'] ]);
    header('Location: adminview.php?ticket_id=' . $_GET['ticket_id']);
}

if ($_GET['status'] == "resolved") {
    $stmt = $pdo->prepare('UPDATE tickets SET status = ?, problem = ?, cause = ?, resolution = ?, full_resolution = ? WHERE ticket_id = ?');
    $stmt->execute([ $_GET['status'], $_POST['problem'], $_POST['cause'], $_POST['resolution'], $_POST['full_resolution'], $_GET['ticket_id'] ]);
    header('Location: adminview.php?ticket_id=' . $_GET['ticket_id']);
}

if (isset($_POST['msg']) && !empty($_POST['msg'])) {
    // Insert the new comment into the "tickets_comments" table
    $stmt = $pdo->prepare('INSERT INTO tickets_comments (ticket_id, msg) VALUES (?, ?)');
    $stmt->execute([ $_GET['ticket_id'], $_POST['msg'] ]);
    header('Location: adminview.php?ticket_id=' . $_GET['ticket_id']);
}

if (isset($_POST['txt']) && !empty($_POST['txt'])) {
    $stmt = $pdo->prepare('INSERT INTO notes (ticket_id, txt) VALUES (?, ?)');
    $stmt->execute([ $_GET['ticket_id'], $_POST['txt'] ]);
    header('Location: adminview.php?ticket_id=' . $_GET['ticket_id']);
}

$stmt = $pdo->prepare('SELECT created, msg FROM tickets_comments WHERE ticket_id = ? ORDER BY created DESC');
$stmt->execute([ $_GET['ticket_id'] ]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare('SELECT created, txt FROM notes WHERE ticket_id = ? ORDER BY created DESC');
$stmt->execute([ $_GET['ticket_id'] ]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<?=template_header('Ticket')?>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

<div class="content view">
    <div id="Column2" class="col-md-6">
        <h2><?=htmlspecialchars($ticket['subject'], ENT_QUOTES)?><span class="<?=$ticket['status']?>">(<?=$ticket['status']?>)</span></h2>
        <p style="font-weight:bold; padding-top:10px">Claimed by: <?=$agent['full_name']?></p>
        <div class="ticket">
            <p class="created"><?=date('F dS, G:ia', strtotime($ticket['created']))?></p>
            <p class="msg"><?=nl2br(htmlspecialchars($ticket['msg'], ENT_QUOTES))?></p>
        </div>

        <div class="btns">
            <?php if (!$ticket['claimed']): ?>
                <form>
                    <a href="adminview.php?ticket_id=<?=$_GET['ticket_id']?>&status=open&claimed=1" class="btn blue">Claim</a>
                </form>
            <?php elseif ($ticket['claimed'] && $ticket['agent_id'] == $_SESSION['id']): ?>
                <form>
                    <?php if ($ticket['status'] == "open"): ?>
                        <a href="adminview.php?ticket_id=<?=$_GET['ticket_id']?>&status=open&claimed=0" class="btn blue">Unclaim</a>
                    <?php endif;?>
                    <?php if ($ticket['status'] == "closed"): ?>
                        <a href="adminview.php?ticket_id=<?=$_GET['ticket_id']?>&status=open" class="btn red">Re-open</a>
                    <?php else: ?>
                        <a href="adminview.php?ticket_id=<?=$_GET['ticket_id']?>&status=closed" class="btn red">Close</a>
                    <?php endif; ?>
                    <a class="btn" data-toggle="modal" data-target="#myModal">Resolve</a>
                </form>
            <?php endif ?>
            <div class="modal fade" id="myModal" role="dialog">
                <div class="modal-dialog">
    
                    <!-- Popup form -->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Resolution Report</h4>
                        </div>
                        <div class="modal-body">
                    
		            <form action="adminview.php?ticket_id=<?=$_GET['ticket_id']?>&status=resolved" method="post">
		                <label for="problem">Describe the real problem the customer was having</label>
		                <input type="text" name="problem" placeholder="Front USB ports weren't working" id="problem" required>
		                <label for="cause">Describe the cause of the problem</label>
		                <input type="text" name="cause" placeholder="USB headers were unplugged" id="cause" required>
		                <label for="resolution">Describe how the problem was resolved in a few concise sentences</label>
		                <input type="text" name="resolution" placeholder="Plugged in USB headers" id="resolution" required>
		                <label for="full_resolution">Long Resolution Form</label>
		                <textarea name="full_resolution" placeholder="I opened up the PC and located the USB headers on the bottom right of the motherboard. Then ..." id="full_resolution" required></textarea>
		                <input type="submit" value="Submit">
		             </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="comments">
            <p style="font-size:25px">Comments</h2>
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
            <form action="" method="post">
            <textarea name="msg" placeholder="Enter your comment..."></textarea>
                <input type="submit" value="Post Comment">
            </form>
        </div>
    </div>
    <div id="Column2" class="col-md-6">
        <?php if (!empty($ticket['resolution'])): ?>
            <div class="ticket">
                <p style="font-size:24px; border-bottom: 1px solid #ebebeb; padding-bottom: 20px">Resolution</h2>
                <div class="ticket">
                    <p class="created"><?=date('F dS, G:ia', strtotime($ticket['resolved']))?></p>
                    <p class="msg"><?=nl2br(htmlspecialchars($ticket['resolution'], ENT_QUOTES))?></p>
                </div>
            </div>
        <?php endif;?>
        <div class="comments">
            <p style="font-size:25px">Private Notes</p>
            <?php foreach($notes as $note): ?>
                <div class="comment">
                    <div>
                        <i class="fas fa-comment fa-2x"></i>
                    </div>
                    <p>
                        <span><?=date('F dS, G:ia', strtotime($note['created']))?></span>
                        <?=nl2br(htmlspecialchars($note['txt'], ENT_QUOTES))?>
                    </p>
                </div>
            <?php endforeach; ?>
            <form action="" method="post">
                <textarea name="txt" placeholder="Enter your private note..."></textarea>
                <input type="submit" value="Post Note">
            </form>
        </div>
    </div>
</div>

<?=template_footer()?>
