<?php
require __DIR__ . '/../includes/session.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/header.php';

requireLogin();

$con = dbConnect();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid comment ID.");
}

$commentID = (int) $_GET['id'];

$sql = "SELECT id, post_id, user_id, comment_text
        FROM comments
        WHERE id = ?";
$stmt = $con->prepare($sql);
$stmt->execute([$commentID]);
$comment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$comment) {
    die("Comment not found.");
}

requireOwnerOrAdmin($comment['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = trim($_POST['comment_text']);

    if (empty($text)) {
        $error = "Comment cannot be empty.";
    } else {
        $updateSql = "UPDATE comments SET comment_text = ? WHERE id = ?";
        $updateStmt = $con->prepare($updateSql);
        $updateStmt->execute([$text, $commentID]);

        header("Location: post.php?id=" . $comment['post_id']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Edit Comment</title>
</head>
<body>

<a href="post.php?id=<?php echo $comment['post_id']; ?>">Back to Post</a>

<h1>Edit Comment</h1>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<form method="POST">
    <textarea name="comment_text" rows="5" required><?php
        echo htmlspecialchars($comment['comment_text'], ENT_QUOTES, 'UTF-8');
    ?></textarea>
    <br><br>

    <button type="submit">Update Comment</button>
</form>

</body>
</html>