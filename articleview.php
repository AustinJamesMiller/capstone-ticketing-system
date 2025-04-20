<?php
session_start();
include 'functions.php';

$pdo = pdo_connect_mysql();

if (!isset($_GET['id'])) {
    exit('No ID specified!');
}

$stmt = $pdo->prepare('SELECT title_clean, device, content, authors FROM articles WHERE id = ?');
$stmt->execute([ $_GET['id'] ]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (empty($article)) {
    exit('Invalid ticket ID!');
}

?>

<?=template_header('Ticket')?>

<div class="content view">
    <h2><?=htmlspecialchars($article['title_clean'], ENT_QUOTES)?></h2>
    <p style="border-bottom: 1px solid #ebebeb; padding-bottom: 11px; padding-top: 11px;">Device: <?=nl2br($article['device'], ENT_QUOTES)?></p>
    <?php if ($_SESSION['loggedin'] and $_SESSION['type'] == "admin"): ?>
        <div class="btns" style="border-bottom: 1px solid #ebebeb; padding-bottom: 11px">
        <a href="editarticle.php?id=<?=$_GET['id']?>" class="btn red">EDIT</a>
        </div>
    <?php endif; ?>
    <div class="ticket">
        <?=nl2br($article['content'], ENT_QUOTES)?>
        <p style="border-top: 1px solid #000000; padding-top: 11px"><?=$article['authors']?></p>
    </div>
</div>

<?=template_footer()?>
