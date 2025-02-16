<?php
session_start();
include 'config.php';

//trim removes whitespace before and after the title, strip_tags removes html formatting
$title = trim("<h1>".$_POST["title"]."</h1>");
$title_clean = strip_tags($_POST["title"]);

$authors = $_POST["authors"];

$content = $_REQUEST["myeditor"];
$content_clean = strip_tags($_REQUEST["myeditor"]);

$device = $_REQUEST["device"];
$category = $_REQUEST["category"];
$id = $_SESSION['id'];

$stmt = $pdo->prepare("SELECT id FROM articles WHERE title_clean = ?");
$stmt->execute([$title_clean]);
$row = $stmt->fetch();

if ( $row ) {
	$sql = "UPDATE articles SET title = ?, title_clean = ?, authors = ?, content = ?, content_clean = ?, device = ?, category = ? WHERE id = ?";
	$stmt= $pdo->prepare($sql);
	$stmt->execute([$title, $title_clean, $authors, $content, $content_clean, $device, $category, $id]);
	header('Location: articleview.php?id=' . $id);
} else {
	$sql = "INSERT IGNORE INTO articles (title, title_clean, authors, content, content_clean, device, category) VALUES(?,?,?,?,?,?,?)";
	$stmt= $pdo->prepare($sql);
	$stmt->execute([$title, $title_clean, $authors, $content, $content_clean, $content_clean, $device, $category]);
	header('Location: articleview.php?id=' . $pdo->lastInsertId());
}

//This executes our vectorization program to ensure the new article is immediately searchable. "> /dev/null 2>&1 &" redirects any cmd output like errors to "dev/null" where they are discarded, the final "&" tells the script to generate a new process for itself so we don't have to wait for it to complete before the rest of the php can complete (decreasing user load time).
shell_exec("python3 /var/www/html/vectorization.py 'article' > /dev/null 2>&1 &");
?>
