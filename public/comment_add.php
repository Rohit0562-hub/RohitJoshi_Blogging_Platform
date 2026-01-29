<?php
ini_set('display_errors', 1); 
error_reporting(E_ALL);
require __DIR__ . '/../includes/session.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';

requireLogin();

$con = dbConnect();

$postId = (int) $_POST['post_id'];
$comment = trim($_POST['comment_text']);

if (empty($comment)) {
    die("Comment cannot be empty.");
}

$sql = "INSERT INTO comments (post_id, user_id, comment_text, created_at) VALUES (?, ?, ?, NOW())";
$stmt = $con->prepare($sql);
$stmt->execute([$postId, $_SESSION['user_id'], $comment]);

header("Location: post.php?id=" . $postId);
exit;
?>