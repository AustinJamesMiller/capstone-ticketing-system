<?php

session_start();

include 'functions.php';

$pdo = pdo_connect_mysql();

$separator = "kba";
$query = 'SELECT device, content_clean, title_clean, id FROM articles';

if (!empty($_POST['device'])) {
    $device = escapeshellarg($_POST['device']);
}

if (!empty($_POST['search'])) {
    $search = escapeshellarg($_POST['search']);
}

if ($device || $search) {
    $result = shell_exec("python3 /var/www/html/search.py $device $separator $search");
}

if ($result) {
    $result = explode(",",$result);
    $chars = array("{","}","[","]");
    $result = str_replace($chars,"",$result);
    $in = str_repeat('?,', count($result) - 1) . '?';
    $pieces[" id IN ($in)"] = $result;
}

$pieces[' category = ?'] = $_POST['category'];

foreach ($pieces as $x => $y) {
    if (empty($y)) {
        $count += 1;
    }
}

if (count($pieces) != $count) {
    $query .= ' WHERE ';
    foreach ($pieces as $x => $y) {
        if (!empty($y)) {
            if (!is_array($y)) {
                $subs[] = $y;
                $query = $query . $x . " and";
            } else {
                $subs = $result;
                $query = $query . $x . " and";
            }
        }
    }
    $query = substr($query, 0, -4);
    $query .= ' order by created ' . $_POST['date'];
    $stmt = $pdo->prepare($query);
    $stmt->execute($subs);
} else {
    $query .= ' order by created ' . $_POST['date'];
    $stmt = $pdo->prepare($query);
    $stmt->execute();
}

$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<?=template_header('Tickets')?>

<div class="content home">
    <h2>Knowledge Base</h2>
    <table class="table table-bordered" style="width: 100%;">
        <tbody>
            <tr>
                <td style="text-align: center">
                    <div class="rows">
                        <p class="mycustom">Search Articles</p>
                        <form action="kba.php" method="post">
                            <div id="Column5" class="col-md-6">
                                <div class="form-group">
                                    <label for="search" class="col-md-3 labelled">Search:</label>
                                    <div class="col-sm-9 inputted" >
                                        <input type="text" name="search" placeholder="Search text" id="search">
                                    </div>
                                </div>
                                <div class="form-group";>
                                    <label for="category" class="col-md-3 labelled">Category:</label>
                                    <div class="col-sm-9 inputted">
                                        <?php echo categories() ?>
                                    </div>
                                </div>
                            
                            </div>
                            <div id="Column6" class="col-md-6">
                                <div class="form-group">
                                    <label for="device" class="col-md-3 labelled">Device:</label>
                                    <div class="col-sm-9 inputted" >
                                        <input type="text" name="device" placeholder="dell xps laptop" id="device">
                                    </div>
                                </div>
                                <div class="form-group";>
                                    <label for="order" class="col-md-3 labelled">Date:</label>
                                    <div class="col-sm-9 inputted" >
                                        <select name='date' style='width: 224px' id='date'>
                                        <option value='DESC'>Newest-Oldest</option>
                                        <option value='ASC'>Oldest-Newest</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div id="Column6" class="col-md-12" style="align-content: center">
                                <input type="submit" value="Search">
                            </div>
                        </form>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    <p style="font-size: 1.5em">Articles</p>

    <div class="tickets-list">
        <?php foreach ($articles as $article): ?>
            <a href="articleview.php?id=<?=$article['id']?>" class="ticket">
                <span class="con">
                    <span class="title"><?=htmlspecialchars($article['title_clean'], ENT_QUOTES)?></span>
                    <span class="custom"><?=htmlspecialchars($article['content_clean'], ENT_QUOTES)?></span>
                </span>
                <span class="con created"><?=$article['device']?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?=template_footer()?>
