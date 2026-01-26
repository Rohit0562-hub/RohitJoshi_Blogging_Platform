<?php

require __DIR__ . '/../config/db.php';
$con = dbConnect();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
	die("Invalid post ID.");
}

$postID = (int) $_GET['id'];

$sql = "SELECT title, content, author_name FROM posts WHERE id = ?";
$stmt = $con->prepare($sql);
$stmt->execute([$postID]);
$stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
	die("Post not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$title = trim($_POST['title']);
	$content = trim($_POST['content']);

	if(empty($title) || empty($content)) {
		$error = "All fields are required.";
	} else {
		$updateSql = "UPDATE posts SET title = ?, content = ?, WHERE id = ?";
		$updateStmt = $con->prepare($updateSql);
		$updateStmt->execute([$title, $content, $postID]);

		header("Location: add.php?id=" .  $postID);
		exit;
	}
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Edit Post: <?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?></title>
	<link rel="stylesheet" type="text/css" href="../assets/style.css">
</head>
<body>

<a href="add.php?id=<?php echo $postID; ?>">Back to Post</a>

<h1>Edit Post</h1>
<?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

<form method="POST">
	<label>Title:</label><br>
	<input type="text" name="title" value="<?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?>" required><br><br>

	<label>Content:</label><br>
	<textarea name="content" rows="10" required><?php echo htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8'); ?></textarea><br><br>

	<button type="submit">Update Post</button>
	
</form>
</body>
</html>

