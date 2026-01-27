<?php

require __DIR__ . '/../config/db.php';
$con = dbConnect();

$sql = "SELECT id, title, author_name, created_at FROM posts WHERE status = 'published' ORDER BY created_at DESC";

$stmt = $con->prepare($sql);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$catStmt = $con->query("SELECT id, name FROM categories ORDER BY name");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Simple Blog CMS</title>
	<link rel="stylesheet" type="text/css" href="../assets/style.css">
</head>
<body>

<h1>Blog Posts</h1>

<?php if (empty($posts)): ?>
	<p>No Posts Available.</p>
<?php else: ?>
	<?php foreach ($posts as $post): ?> 
		<article>
			<h2>
				<a href="add.php?id=<?php echo $post['id']; ?>">
					<?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?>
				</a>
			</h2>
			<p>
				By <?php echo htmlspecialchars($post['author_name'], ENT_QUOTES, 'UTF-8'); ?>
				| <?php echo date("F j, Y", strtotime($post['created_at'])); ?>
			</p>
		</article>
		<hr>
	<?php endforeach; ?>
<?php endif; ?>

<label>Category:</label>
<select name="category_id" required>
	<option value="">Select Category</option>
	<?php foreach($categories as $cat): ?>
	<option value="<?php echo $cat['id']; ?>">
		<?php echo htmlspecialchars($cat['name']); ?>
	</option>
	<?php endforeach; ?>	
</select><br><br>
<label>Tags (comma separated):</label><br>
<input type="text" name="tags" placeholder="php, mysql, cms"><br><br>

</body>
</html>