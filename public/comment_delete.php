<?php
require __DIR__ . '/../includes/session.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';

requireLogin();

$con = dbConnect();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid comment ID.");
}

$commentID = (int) $_GET['id'];

$stmt = $con->prepare("SELECT user_id, post_id FROM comments WHERE id = ?");
$stmt->execute([$commentID]);
$comment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$comment) {
    die("Comment not found.");
}

requireOwnerOrAdmin($comment['user_id']);

$deleteStmt = $con->prepare("DELETE FROM comments WHERE id = ?");
$deleteStmt->execute([$commentID]);

header("Location: post.php?id=" . $comment['post_id']);
exit;
?>