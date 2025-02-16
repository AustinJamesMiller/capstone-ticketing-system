<?php
//Start the session so we can use session variables
session_start();

// If the user is not logged in redirect to the login page...
if (!isset($_SESSION['loggedin'])) {
	header('Location: login.html');
	exit;
}

// this lets us get functions from the functions file
include 'functions.php';

// Connect to MySQL using the below function
$pdo = pdo_connect_mysql();


// If they set a search term and an email to search for...
if (!empty($_POST['search']) and !empty($_POST['email'])) {
	// variables should be self explanatory
	$email = $_POST['email'];
	$separator = "tickets";
	$search = $_POST['search'];
	$status = $_POST['status'];
	$result = shell_exec("python3 /var/www/html/search.py $search $separator $email");
	$result = explode(",",$result);
	$chars = array("{","}","[","]");
	$result = str_replace($chars,"",$result);
	$in = str_repeat('?,', count($result) - 1) . '?';
	if (empty($status)) {
		$result[] = $_POST['email'];
		$stmt = $pdo->prepare("SELECT * FROM tickets WHERE ticket_id IN ($in) and email = ?");
	} else {
		$result[] = $_POST['status'];
		$result[] = $_POST['email'];
		$stmt = $pdo->prepare("SELECT * FROM tickets WHERE ticket_id IN ($in) and status = ? and email = ?");
	}
	$stmt->execute($result);
	$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$plain = False;
} elseif (!empty($_POST['search']) and empty($_POST['email'])) {
	$email = "empty";
	$separator = "tickets";
	$search = $_POST['search'];
	$status = $_POST['status'];
	$result = shell_exec("python3 /var/www/html/search.py $search $separator $email");
	$result = explode(",",$result);
	$chars = array("{","}","[","]");
	$result = str_replace($chars,"",$result);
	$in = str_repeat('?,', count($result) - 1) . '?';
	if (empty($status)) {
		$stmt = $pdo->prepare("SELECT * FROM tickets WHERE ticket_id IN ($in)");
	} else {
		$result[] = $_POST['status'];
		$stmt = $pdo->prepare("SELECT * FROM tickets WHERE ticket_id IN ($in) and status = ?");
	}
	$stmt->execute($result);
	$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$plain = False;
} elseif (empty($_POST['search']) and !empty($_POST['email'])) {
	$email = $_POST['email'];
	$status = $_POST['status'];
	if (empty($status)) {
		$result = [$email];
		$stmt = $pdo->prepare("SELECT * FROM tickets WHERE email = ?");
	} else {
		$result = [$email,$status];
		$stmt = $pdo->prepare("SELECT * FROM tickets WHERE email = ? and status = ?");
	}
	$stmt->execute($result);
	$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$plain = False;
} elseif (empty($_POST['search']) and empty($_POST['email']) and !empty($_POST['status'])) {
	$status = $_POST['status'];
	if (empty($status)) {
		$stmt = $pdo->prepare("SELECT * FROM tickets");
		$stmt->execute();
	} else {
		$result = [$status];
		$stmt = $pdo->prepare("SELECT * FROM tickets where status = ?");
		$stmt->execute($result);
	}
	$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$plain = False;
} else {
	$stmta = $pdo->prepare('SELECT * FROM tickets WHERE status = "open" ORDER BY created DESC LIMIT 10');
	$stmtb = $pdo->prepare('SELECT * FROM tickets WHERE status = "closed" ORDER BY created DESC LIMIT 10');
	$stmtc = $pdo->prepare('SELECT * FROM tickets WHERE status = "resolved" ORDER BY created DESC LIMIT 10');
	$stmta->execute();
	$stmtb->execute();
	$stmtc->execute();
	$open = $stmta->fetchAll(PDO::FETCH_ASSOC);
	$closed = $stmtb->fetchAll(PDO::FETCH_ASSOC);
	$resolved = $stmtc->fetchAll(PDO::FETCH_ASSOC);
	$plain = True;
}
var_dump($result);
$stmtmine = $pdo->prepare("SELECT * FROM tickets WHERE agent_id = ? ORDER BY created DESC");
$stmtmine->execute([$_SESSION['id']]);
$mine = $stmtmine->fetchAll(PDO::FETCH_ASSOC);
?>

<?=template_header('Tickets')?>

<div class="content home">
	<h1 style="text-align: left">Admin Ticket Area</h1>
	<table class="table table-bordered" style="width: 100%;">
		<tbody>
			<tr>
				<td style="text-align: center">
					<div class="rows">
					<p class="mycustom">Search Tickets</p>
						<form action="adminticket.php" method="post">
							<div id="Column20" class="col-md-6">
								<div class="form-group">
									<label for="search" class="col-md-3 labelled">Search:</label>
									<div class="col-sm-9 inputted">
										<input type="text" name="search" placeholder="Search text" id="search">
									</div>
								</div>
								<div class="form-group";>
									<label for="status" class="col-md-3 labelled">Status:</label>
									<div class="col-sm-9 inputted">
										<?php echo status() ?>
									</div>
								</div>
							
							</div>
							<div id="Column6" class="col-md-6">
								<div class="form-group">
									<label for="email" class="col-md-3 labelled">Email:</label>
									<div class="col-sm-9 inputted">
										<input type="text" name="email" placeholder="w01234@nscc.ca" id="device">
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
	<div class="tickets-list">
		<ul class="nav nav-tabs" id="myTab" role="tablist">
		<li class="nav-item" role="presentation">
			<button class="nav-items nav-link active" id="open-tab" data-bs-toggle="tab" data-bs-target="#open" type="button" role="tab"><?php if (empty($_POST['status'])): ?>Open<?php else: ?><?=ucfirst($_POST['status'])?><?php endif; ?></button>
		</li>
		<li class="nav-item" role="presentation">
			<button class="nav-items nav-link" id="closed-tab" data-bs-toggle="tab" data-bs-target="#closed" type="button" role="tab" <?php if (!empty($_POST['status'])): ?>hidden<?php endif; ?>><?php if (empty($_POST['status'])): ?>Closed<?php elseif(!empty($_POST['status'])): ?><?php endif; ?></button>
		</li>
		<li class="nav-item" role="presentation">
			<button class="nav-items nav-link" id="resolved-tab" data-bs-toggle="tab" data-bs-target="#resolved" type="button" role="tab" <?php if (!empty($_POST['status'])): ?>hidden<?php endif; ?>><?php if (empty($_POST['status'])): ?>Resolved<?php elseif(!empty($_POST['status'])): ?><?php endif; ?></button>
		</li>
		<li class="nav-item" role="presentation">
			<button class="nav-items nav-link" id="mine-tab" data-bs-toggle="tab" data-bs-target="#mine" type="button" role="tab">Mine</button>
		</li>
		</ul>
		<?php if($plain): ?>
			<div class="tab-content" id="myTabContent">
				<div class="tab-pane show active" id="open" role="tabpanel">
					<?php foreach ($open as $o): ?>
						<?php if ($o['status'] == 'open'): ?>
							<a href="<?=$_SESSION['type']?>view.php?ticket_id=<?=$o['ticket_id']?>" class="ticket">
								<span class="con">
								<i class="far fa-clock fa-2x"></i>
								</span>
								<span class="con">
									<span class="subject"><?=htmlspecialchars($o['subject'], ENT_QUOTES)?></span>
									<span class="msg"><?=htmlspecialchars($o['msg'], ENT_QUOTES)?></span>
								</span>
								<span class="con created"><?=date('F dS, G:ia', strtotime($o['created']))?></span>
							</a>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
				<div class="tab-pane" id="closed" role="tabpanel">
					<?php foreach ($closed as $c): ?>
						<?php if ($c['status'] == 'closed'): ?>
							<a href="<?=$_SESSION['type']?>view.php?ticket_id=<?=$c['ticket_id']?>" class="ticket">
								<span class="con">
								<i class="fas fa-times fa-2x"></i>
								</span>
								<span class="con">
									<span class="subject"><?=htmlspecialchars($c['subject'], ENT_QUOTES)?></span>
									<span class="msg"><?=htmlspecialchars($c['msg'], ENT_QUOTES)?></span>
								</span>
								<span class="con created"><?=date('F dS, G:ia', strtotime($c['created']))?></span>
							</a>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
				<div class="tab-pane" id="resolved" role="tabpanel">
					<?php foreach ($resolved as $r): ?>
						<?php if ($r['status'] == 'resolved'): ?>
							<a href="<?=$_SESSION['type']?>view.php?ticket_id=<?=$r['ticket_id']?>" class="ticket">
								<span class="con">
								<i class="fas fa-check fa-2x"></i>
								</span>
								<span class="con">
									<span class="subject"><?=htmlspecialchars($r['subject'], ENT_QUOTES)?></span>
									<span class="msg"><?=htmlspecialchars($r['msg'], ENT_QUOTES)?></span>
								</span>
								<span class="con created"><?=date('F dS, G:ia', strtotime($r['created']))?></span>
							</a>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</div>
		<?php elseif(empty($_POST['status'])): ?>
			<div class="tab-content" id="myTabContent">
				<div class="tab-pane show active" id="open" role="tabpanel">
					<?php foreach ($articles as $a): ?>
						<?php if ($a['status'] == 'open'): ?>
							<a href="<?=$_SESSION['type']?>view.php?ticket_id=<?=$a['ticket_id']?>" class="ticket">
								<span class="con">
								<i class="far fa-clock fa-2x"></i>
								</span>
								<span class="con">
									<span class="subject"><?=htmlspecialchars($a['subject'], ENT_QUOTES)?></span>
									<span class="msg"><?=htmlspecialchars($a['msg'], ENT_QUOTES)?></span>
								</span>
								<span class="con created"><?=date('F dS, G:ia', strtotime($a['created']))?></span>
							</a>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
				<div class="tab-pane" id="closed" role="tabpanel">
					<?php foreach ($articles as $a): ?>
						<?php if ($a['status'] == 'closed'): ?>
							<a href="<?=$_SESSION['type']?>view.php?ticket_id=<?=$a['ticket_id']?>" class="ticket">
								<span class="con">
								<i class="fas fa-times fa-2x"></i>
								</span>
								<span class="con">
									<span class="subject"><?=htmlspecialchars($a['subject'], ENT_QUOTES)?></span>
									<span class="msg"><?=htmlspecialchars($a['msg'], ENT_QUOTES)?></span>
								</span>
								<span class="con created"><?=date('F dS, G:ia', strtotime($a['created']))?></span>
							</a>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
				<div class="tab-pane" id="resolved" role="tabpanel">
					<?php foreach ($articles as $a): ?>
						<?php if ($a['status'] == 'resolved'): ?>
							<a href="<?=$_SESSION['type']?>view.php?ticket_id=<?=$a['ticket_id']?>" class="ticket">
								<span class="con">
								<i class="fas fa-check fa-2x"></i>
								</span>
								<span class="con">
									<span class="subject"><?=htmlspecialchars($a['subject'], ENT_QUOTES)?></span>
									<span class="msg"><?=htmlspecialchars($a['msg'], ENT_QUOTES)?></span>
								</span>
								<span class="con created"><?=date('F dS, G:ia', strtotime($a['created']))?></span>
							</a>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</div>
		<?php else: ?>
			<div class="tab-content" id="myTabContent">
				<div class="tab-pane show active" id="<?=$_POST['status']?>" role="tabpanel">
					<?php foreach ($articles as $a): ?>
						<a href="<?=$_SESSION['type']?>view.php?ticket_id=<?=$a['ticket_id']?>" class="ticket">
							<?php if ($a['status'] == 'open'): ?>
							<i class="far fa-clock fa-2x"></i>
							<?php elseif ($a['status'] == 'resolved'): ?>
							<i class="fas fa-check fa-2x"></i>
							<?php elseif ($a['status'] == 'closed'): ?>
							<i class="fas fa-times fa-2x"></i>
							<?php endif; ?>
							<span class="con">
								<span class="subject"><?=htmlspecialchars($a['subject'], ENT_QUOTES)?></span>
								<span class="msg"><?=htmlspecialchars($a['msg'], ENT_QUOTES)?></span>
							</span>
							<span class="con created"><?=date('F dS, G:ia', strtotime($a['created']))?></span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>

<?=template_footer()?>
