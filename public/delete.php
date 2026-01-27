<?php
require __DIR__ . '/../config/db.php';

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
	die("Invalid post ID.");
}

$postID = (int) $_GET['id'];

$sql = "DELETE FROM posts WHERE id = ?";
$stmt = $con->prepare($sql);
$stmt->execute([$postID]);

header("Location: index.php");
exit;
?>