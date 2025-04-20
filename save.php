<?php
session_start();

include 'functions.php';

$pdo = pdo_connect_mysql();

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
$row = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($row) {
    $stmt= $pdo->prepare("UPDATE articles SET title = ?, title_clean = ?, authors = ?, content = ?, content_clean = ?, device = ?, category = ? WHERE id = ?");
    $stmt->execute([$title, $title_clean, $authors, $content, $content_clean, $device, $category, $id]);
    header('Location: articleview.php?id=' . $id);
} else {
    $stmt= $pdo->prepare("INSERT IGNORE INTO articles (title, title_clean, authors, content, content_clean, device, category) VALUES(?,?,?,?,?,?,?)");
    $stmt->execute([$title, $title_clean, $authors, $content, $content_clean, $device, $category]);
    header('Location: articleview.php?id=' . $pdo->lastInsertId());
}

// We execute the vectorization.py file so the article is immediately able to be searched
shell_exec("python3 /var/www/html/vectorization.py 'article' > /dev/null 2>&1 &");
?>
