<?php

session_start();

if (!isset($_SESSION['loggedin'])) {
	header('Location: login.html');
	exit;
}

include 'functions.php';
// Connect to MySQL using the below function
$pdo = pdo_connect_mysql();
// Check if the ID param in the URL exists
if (!isset($_GET['id'])) {
    exit('No ID specified!');
}

$_SESSION['id'] = $_GET['id'];

// MySQL query that selects the ticket by the ID column, using the ID GET request variable
$stmt = $pdo->prepare('SELECT * FROM articles WHERE id = ?');
$stmt->execute([ $_GET['id'] ]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);
// Check if ticket exists
if (!$article) {
    exit('Invalid article ID!');
}
?>

<?=template_header('Tickets')?>

<html>
	<head>
		<meta charset="utf-8">
		<title>Article</title>
		<script src="//cdn.ckeditor.com/4.5.9/full/ckeditor.js"></script>
		<script src="https://cdn.tiny.cloud/1/gl8u9arquurub2qhwbrpcy5u8o44zbb4cmt33hx6093ch95b/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
		<script>
			tinymce.init({
				selector: "#myeditor",
				plugins: "powerpaste casechange searchreplace autolink directionality advcode visualblocks visualchars image link media mediaembed codesample table charmap pagebreak nonbreaking anchor tableofcontents insertdatetime advlist lists checklist wordcount tinymcespellchecker editimage help formatpainter permanentpen charmap linkchecker emoticons advtable export autosave save",
				toolbar: "save undo redo spellcheckdialog | blocks fontfamily fontsize | bold italic underline forecolor backcolor | link image addcomment showcomments  | alignleft aligncenter alignright alignjustify lineheight | checklist bullist numlist indent outdent ",
				height: '700px',
				/* enable title field in the Image dialog*/
				image_title: true,
				/* enable automatic uploads of images represented by blob or data URIs*/
				automatic_uploads: true,
				/*
				URL of our upload handler (for more details check: https://www.tiny.cloud/docs/configure/file-image-upload/#images_upload_url)
				images_upload_url: 'postAcceptor.php',
				here we add custom filepicker only to Image dialog
				*/
				file_picker_types: 'image',
				/* and here's our custom image picker*/
				file_picker_callback: (cb, value, meta) => {
					const input = document.createElement('input');
					input.setAttribute('type', 'file');
					input.setAttribute('accept', 'image/*');

					input.addEventListener('change', (e) => {
						const file = e.target.files[0];

						const reader = new FileReader();
						reader.addEventListener('load', () => {
							/*
							Note: Now we need to register the blob in TinyMCEs image blob
							registry. In the next release this part hopefully won't be
							necessary, as we are looking to handle it internally.
							*/
							const id = 'blobid' + (new Date()).getTime();
							const blobCache =  tinymce.activeEditor.editorUpload.blobCache;
							const base64 = reader.result.split(',')[1];
							const blobInfo = blobCache.create(id, file, base64);
							blobCache.add(blobInfo);

							/* call the callback and populate the Title field with the file name */
							cb(blobInfo.blobUri(), { title: file.name });
						});
						reader.readAsDataURL(file);
					});

					input.click();
				},
				content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }'
		       });
		</script>
	</head>
	<body>
		<form method="POST" action="save.php">
			<div id="Column10" class="col-md-6 wide">
				<div class="form-group">
				<label for="title">Title:</label>
				<input type="title" name="title" placeholder="KBA Title" id="title" value="<?=$article['title_clean']?>"\>
				<label for="authors">Authors:</label>
				<input type="authors" name="authors" placeholder="Austin Miller, Cole Bishop, Jordan Hunt, Dylan Gong" id="authors" value="<?=$article['authors']?>">
				<label for="device">Device:</label>
				<input type="text" name="device" placeholder="Dell XPS Laptop" id="device" style="margin-bottom:15px" value="<?=$article['device']?>">
				<label for="category">Category:</label>
				<input type="text" name="category" placeholder="Software" id="category" value="<?=$article['category']?>">
			<textarea name="myeditor" id="myeditor"><?=$article['content']?></textarea>
		</form>
	</body>
</html>
