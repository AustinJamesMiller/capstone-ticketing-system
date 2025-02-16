<?php

$cat = $_POST['category'];

$servername = "localhost";
$username = "root";
$password = "Passw0rd";
$db = "ticketing_system";

$conn = new mysqli($servername, $username, $password, $db);

if ($conn->connect_error){
	die("Connection failed: ". $conn->connect_error);
}

$sql = "select * from articles where category = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $cat);
$stmt->execute();
$row = $result->fetch_assoc()

if ($result->num_rows > 0){
while($row = $result->fetch_assoc() ){
	echo $row["html_title"]."<br><br>".$row["content"]."<br>";
}
} else {
	echo "0 records";
}

$conn->close();

?>
