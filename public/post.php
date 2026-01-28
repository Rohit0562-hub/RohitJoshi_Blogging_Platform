<?php

require __DIR__ . '/../config/db.php';

$con = dbConnect();

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
	die("Invalid post ID.");
}

$postID = (int) $_GET['id'];

$postSql = "SELECT title, content, author_name, created_at FROM posts WHERE id = ? AND status = 'published'";

$postStmt = $con->prepare($postSql);
$postStmt->execute([$postID]);
$post = $postStmt->fetch(PDO::FETCH_ASSOC);

if(!$post) {
	die("Post not found.");
}

$categoryIdsStmt = $con->prepare("SELECT category_id FROM post_categories WHERE post_id = ?");
$categoryIdsStmt->execute([$postID]);
$categoryIds = $categoryIdsStmt->fetchAll(PDO::FETCH_COLUMN);

$categories = [];
if(!empty($categoryIds)) {
	$in = implode(',', array_fill(0, count($categoryIds), '?'));
	$categorystmt = $con->prepare("SELECT name FROM categories WHERE id IN ($in)");
	$categorystmt->execute($categoryIds);
	$categories = $categorystmt->fetchAll(PDO::FETCH_COLUMN);
}

$tagIdsStmt = $con->prepare("SELECT tag_id FROM post_tags WHERE post_id = ?");
$tagIdsStmt->execute([$postID]);
$tagIds = $tagIdsStmt->fetchAll(PDO::FETCH_COLUMN);

$tags = [];
if(!empty($tagIds)){
	$in = implode(',', array_fill(0, count($tagIds), '?'));
	$tagStmt = $con->prepare("SELECT name FROM tags WHERE id IN ($in)");
	$tagStmt->execute($tagIds);
	$tags = $tagStmt->fetchAll(PDO::FETCH_COLUMN);
}

$commentSql = "SELECT author_name, comment_text, created_at FROM comments WHERE post_id = ? ORDER BY created_at DESC";
$commentStmt = $con->prepare($commentSql);
$commentStmt->execute([$postID]);
$comments = $commentStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?></title>
	<link rel="stylesheet" type="text/css" href="../assets/style.css">
</head>
<body>
	<a href="index.php">Back to Posts</a>
<article>
	<h1><?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
	<a href="edit.php?id=<?php echo $postID; ?>">Edit Post</a>
	<a href="delete.php?id=<?php echo $postID; ?>" onclick="return confirm('Are you sure you want to delete this post?');">Delete Post</a>



	<p>
		By <?php echo htmlspecialchars($post['author_name'], ENT_QUOTES, 'UTF-8'); ?>
		| <?php echo date("F j, Y", strtotime($post['created_at'])); ?>
	</p>

	<?php if(!empty($categories)): ?>
		<p>
			<strong>Categories:</strong>
			<?php echo htmlspecialchars(implode(', ', $categories), ENT_QUOTES, 'UTF-8'); ?>
		</p>
	<?php endif; ?>

	<?php if(!empty($tags)): ?>
		<p>
			<strong>Tags:</strong>
			<?php echo htmlspecialchars(implode(', ', $tags), ENT_QUOTES, 'UTF-8'); ?>
		</p>
	<?php endif; ?>

	<div>
		<?php echo nl2br(htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8')); ?>
	</div>

	<h2>Comments</h2>

	<?php if(count($comments) === 0): ?>
		<p>No comments yet.</p>

	<?php else: ?>

		<?php foreach($comments as $comment): ?>
			<div style="margin-bottom: 15px;">
				<strong>
					<?php echo htmlspecialchars($comment['author_name'], ENT_QUOTES, 'UTF-8'); ?>
				</strong>
				<br>
				<small>
					<?php echo date("F j, Y, g:i a", strtotime($comment['created_at'])); ?>
				</small>
				<p>
					<?php echo nl2br(htmlspecialchars($comment['comment_text'], ENT_QUOTES, 'UTF-8')); ?>
				</p>
			</div>
			<hr>
		<?php endforeach; ?>
	<?php endif; ?>

	<h3>Add a Comment</h3>

	<form method="POST" action="comment_add.php">
		<input type="hidden" name="post_id" value="<?php echo $postID; ?>">

		<label>Name:</label><br>
		<input type="text" name="author_name" required><br><br>

		<label>Comment:</label><br>
		<textarea name="comment_text" rows="4" required></textarea><br><br>

		<button type="submit">Submit Comment</button>
		
	</form>
</article>

</body>
</html>