<?php

require __DIR__ . '/../includes/session.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';

requireLogin();

$con = dbConnect();

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid Request.");
}

if (!isset($_POST['post_id'], $_POST['comment_text'])) {
    die("Required fields missing.");
}

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