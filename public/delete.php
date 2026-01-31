<?php

require __DIR__ . '/../includes/session.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';

requireLogin();

$con = dbConnect();

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
	die("Invalid post ID.");
}

if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
	die("Invalid CSRF token");
}


$postID = (int) $_GET['id'];

$postStmt = $con->prepare("SELECT user_id FROM posts WHERE id = ?");
$postStmt->execute([$postID]);
$post = $postStmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
	die("Post not found");
}

requireOwnerOrAdmin($post['user_id']);

$deleteStmt = $con->prepare("DELETE FROM posts WHERE id = ?");
$deleteStmt->execute([$postID]);

header("Location: index.php");
exit;
?>