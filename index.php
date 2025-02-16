<?php
session_start();

include 'functions.php';
$pdo = pdo_connect_mysql();
// MySQL query that retrieves all the tickets from the database
$stmt = $pdo->prepare('SELECT * FROM articles ORDER BY created ASC limit 3');
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?=template_header('Create Ticket')?>

<div class="contented" style="text-align:center">
	<h1>Welcome to the SDC Ticketing Site</h1>
	<img src="/uploads/image.png"></img><img src="/uploads/image.png"></img><img src="/uploads/image.png">
	<figcaption>Image credit to Morgan Peddle</figcaption>
</div>
<div class="rows">
	<div id="Column2" class="col-md-6">
		<div class="contents create">
			<div class="test">
				<table class="table table-bordered" style="width: 100%;">
					<tbody>
						<tr>
							<td style="text-align: center">
							<h1 style="padding-top: 16px">Hours of Operation</h1>

							<h3><span style="font-size: 14px; padding: 0; margin: 0"></span></h3>

							<h3>Monday - Friday</h3>
							<h3>8:00am - 4:00pm<h3>
							&nbsp;
							<p style="font-size:25px">Available by self service ticket, or live chat</p>
							<p style="font-size:25px">By telephone 902-491-HELP (4357)</p>

							<p style="font-size:25px">Toll free: 1-866-898-4357</p>
							<p style="font-size:25px">8:00am - 4:00 pm</p>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<div id="Column3" class="col-md-6">
		<div class="contents create">
			<div class="test">
				<?php if (!$_SESSION['loggedin']): ?>
				<table class="table table-bordered" style="width: 100%;">
					<tbody>
						<tr>
							<td style="text-align: center">
							<p style="padding-top: 16px; font-size: 2rem;">Login or Create an Account to Submit a Ticket</p>
							<button type="button" onclick="location.href='login.html'">Login</button>
							<button type="button" onclick="location.href='reg.php'">Create Account</button>
							</td>
						</tr>
					</tbody>
				</table>
				<?php endif; ?>
				<table class="table table-bordered" style="width: 100%;">
					<tbody>
						<tr>
							<td style="text-align: center">
							<p class="mycustom">Popular Articles</p>
							<div class="contents home">
								<div class="tickets-list">
									<?php foreach ($articles as $article): ?>
									<a href="articleview.php?id=<?=$article['id']?>" class="ticket" style="justify-content: center">
										<span class="con">
											<span class="title"><?=htmlspecialchars($article['title_clean'], ENT_QUOTES)?></span><br>
											<span class="custom"><?=htmlspecialchars($article['content_clean'], ENT_QUOTES)?></span>
										</span>
									</a>
									<?php endforeach; ?>
								</div>
							</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<?=template_footer()?>
