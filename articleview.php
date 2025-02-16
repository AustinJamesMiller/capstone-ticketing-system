<?php

include 'functions.php';

// Connect to MySQL using the below function
$pdo = pdo_connect_mysql();
// Check if the ID param in the URL exists
if (!isset($_GET['id'])) {
    exit('No ID specified!');
}
// MySQL query that selects the ticket by the ID column, using the ID GET request variable
$stmt = $pdo->prepare('SELECT * FROM articles WHERE id = ?');
$stmt->execute([ $_GET['id'] ]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);
// Check if ticket exists
if (!$article) {
    exit('Invalid ticket ID!');
}

?>

<?=template_header('Ticket')?>

<div class="content view">

	<h2><?=htmlspecialchars($article['title_clean'], ENT_QUOTES)?></h2>
	<p style="border-bottom: 1px solid #ebebeb; padding-bottom: 11px; padding-top: 11px;">Device: <?=nl2br($article['device'], ENT_QUOTES)?></p>
		<div class="btns" style="border-bottom: 1px solid #ebebeb; padding-bottom: 11px">
			<a href="editarticle.php?id=<?=$_GET['id']?>" class="btn red">EDIT</a>
		</div>

	<div class="ticket">
		<?=nl2br($article['content'], ENT_QUOTES)?>
		<p style="border-top: 1px solid #000000; padding-top: 11px"><?=$article['authors']?></p>
	</div>

</div>
<?=template_footer()?>

