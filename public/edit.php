<?php
ini_set('display_errors', 1); 
error_reporting(E_ALL);

require __DIR__ . '/../includes/session.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/header.php';

requireLogin();

$con = dbConnect();

$error = '';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid post ID.");
}

$postID = (int) $_GET['id'];

$sql = "SELECT title, content, user_id FROM posts WHERE id = ?";
$stmt = $con->prepare($sql);
$stmt->execute([$postID]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    die("Post not found.");
}

requireOwnerOrAdmin($post['user_id']);

$allCategoriesStmt = $con->query("SELECT id, name FROM categories");
$allCategories = $allCategoriesStmt->fetchAll(PDO::FETCH_ASSOC);

$assignedCategoriesStmt = $con->prepare("SELECT category_id FROM post_categories WHERE post_id = ?");
$assignedCategoriesStmt->execute([$postID]);
$assignedCategories = $assignedCategoriesStmt->fetchAll(PDO::FETCH_COLUMN);

$allTagsStmt = $con->query("SELECT id, name FROM tags");
$allTags = $allTagsStmt->fetchAll(PDO::FETCH_ASSOC);

$assignedTagsStmt = $con->prepare("SELECT tag_id FROM post_tags WHERE post_id = ?");
$assignedTagsStmt->execute([$postID]);
$assignedTags = $assignedTagsStmt->fetchAll(PDO::FETCH_COLUMN);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die ("Invalid CSRF Token");
    }

    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if (empty($title) || empty($content)) {
        $error = "All fields are required.";
    } else {
        
        // Update post
        $updateSql = "UPDATE posts SET title = ?, content = ? WHERE id = ?";
        $updateStmt = $con->prepare($updateSql);
        $updateStmt->execute([$title, $content, $postID]);

        // Update categories
        $deleteCategoryStmt = $con->prepare("DELETE FROM post_categories WHERE post_id = ?");
        $deleteCategoryStmt->execute([$postID]);

        if(!empty($_POST['categories'])) {
            $insertCategoryStmt = $con->prepare("INSERT INTO post_categories (post_id, category_id) VALUES (?, ?)");
            foreach($_POST['categories'] as $catID) {
                $insertCategoryStmt->execute([$postID, $catID]);
            }
        }

        // Update tags
        $deleteTagStmt = $con->prepare("DELETE FROM post_tags WHERE post_id = ?");
        $deleteTagStmt->execute([$postID]);

        if(!empty($_POST['tags'])) {
            $insertTagStmt = $con->prepare("INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)");
            foreach($_POST['tags'] as $tagID) {
                $insertTagStmt->execute([$postID, $tagID]);
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
    <title>Edit Post</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<a href="post.php?id=<?php echo $postID; ?>">Back to Post</a>

<h1>Edit Post</h1>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?php echo $error; ?></p>
<?php endif; ?>

<form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

    <label>Title:</label><br>
    <input type="text" name="title" value="<?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?>" required><br><br>

    <label>Content:</label><br>
    <textarea name="content" rows="10" required><?php echo htmlspecialchars($post['content'],ENT_QUOTES, 'UTF-8'); ?></textarea><br><br>

    <h3>Categories:</h3>
    <?php foreach($allCategories as $category): ?>
        <label>
            <input type="checkbox" name="categories[]" value="<?php echo $category['id']; ?>"
            <?php echo in_array($category['id'], $assignedCategories) ? 'checked' : ''; ?>>
            <?php echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?>
        </label><br>
    <?php endforeach; ?>

    <h3>Tags:</h3>
    <?php foreach($allTags as $tag): ?>
        <label>
            <input type="checkbox" name="tags[]" value="<?php echo $tag['id']; ?>"
            <?php echo in_array($tag['id'], $assignedTags) ? 'checked' : ''; ?>>
            <?php echo htmlspecialchars($tag['name'], ENT_QUOTES, 'UTF-8'); ?>
        </label><br>
    <?php endforeach; ?>

    <br>
    <button type="submit">Update Post</button>
</form>

</body>
</html>
