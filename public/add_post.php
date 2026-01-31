<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require __DIR__ . '/../includes/session.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';

requireLogin();

require __DIR__ . '/../includes/header.php';

$con = dbConnect();

if (empty($_SESSION['csrf_token'])) {
	$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$catStmt = $con->query("SELECT id, name FROM categories");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

$tagStmt = $con->query("SELECT id, name FROM tags");
$tags = $tagStmt->fetchAll(PDO::FETCH_ASSOC);

$userId = $_SESSION['user_id'];
$validCatIds = array_column($categories, 'id');
$validTagIds = array_column($tags, 'id');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
			die ("Invalid CSRF Token");
		}

    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $status = $_POST['status'] === 'draft' ? 'draft' : 'published';

    if (empty($title) || empty($content)) {
        $error = "Title and content are required.";
    } else {
        $postSql = "INSERT INTO posts (user_id, title, content, status, created_at)
                    VALUES (?, ?, ?, ?, NOW())";

        $postStmt = $con->prepare($postSql);
        $postStmt->execute([$userId, $title, $content, $status]);

        $postID = $con->lastInsertId();

        if (!empty($_POST['categories'])) {
            $catInsert = $con->prepare(
                "INSERT INTO post_categories (post_id, category_id) VALUES (?, ?)"
            );
            foreach ($_POST['categories'] as $catID) {
                    if (in_array($catID, $validCatIds, true)) {
                    	$catInsert->execute([$postID, $catID]);
                    }
            }
        }

        if (!empty($_POST['tags'])) {
            $tagInsert = $con->prepare(
                "INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)"
            );
            foreach ($_POST['tags'] as $tagID) {
            	if (in_array($catID, $validTagIds, true)) {
                $tagInsert->execute([$postID, $tagID]);
            }
            }
        }

        header("Location: post.php?id=" . $postID);
        exit;
    }
}

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Add New Post</title>
</head>
<body>

<a href="index.php">Back to Posts</a>

<h1>Add New Blog Post</h1>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?php echo htmlspecialchars($error, ENT_QUOTES); ?></p>
<?php endif; ?>



<form method="POST">

	<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

	<label>Title:</label><br>
	<input type="text" name="title" required><br><br>

	<label>Content:</label><br>
	<textarea name="content" rows="10" required></textarea><br><br>

	<label>Status:</label>
	<select name="status">
    <option value="draft">Draft</option>
    <option value="published">Published</option>
</select>


	<h3>Categories</h3>
	<?php foreach($categories as $cat): ?>
		<label>
			<input type="checkbox" name="categories[]" value="<?php echo $cat['id']; ?>">
			<?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>
		</label><br>
	<?php endforeach; ?>

	<h3>Tags</h3>
	<?php foreach($tags as $tag): ?>
		<label>
			<input type="checkbox" name="tags[]" value="<?php echo $tag['id']; ?>">
			<?php echo htmlspecialchars($tag['name'], ENT_QUOTES, 'UTF-8'); ?>
		</label><br>
	<?php endforeach; ?>

	<br>
	<button type="submit">Publish Post</button>
	
</form>
</body>
</html>