<?php

require __DIR__ . '/../config/db.php';
$con = dbConnect();

if (empty($_POST['post_id']) || empty($_POST['author_name']) || empty($_POST['comment_text'])) {
	die("All fields are required.");
}

$postId = (int) $_POST['post_id'];
$name = trim($_POST['author_name']);
$comment = trim($_POST['comment_text']);

$sql = "INSERT INTO comments (post_id, author_name, comment_text) VALUES (?, ?, ?)";
$stmt = $con->prepare($sql);
$stmt->execute([$postId, $name, $comment]);

header("Location: post.php?id=" . $postId);
exit;
?>