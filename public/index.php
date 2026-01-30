<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../includes/session.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';

requireLogin();

$con = dbConnect();

$limit = 5;
$offset = 0;

// Single search input
$searchInput = isset($_GET['search']) ? trim($_GET['search']) : '';

$posts = [];
$params = [];
$where = "WHERE status = 'published'";

if ($searchInput !== '') {
    $userStmt = $con->prepare("SELECT id FROM users WHERE username LIKE ?");
    $userStmt->execute(['%' . $searchInput . '%']);
    $userIds = $userStmt->fetchAll(PDO::FETCH_COLUMN);

    $conditions = [];

    if (!empty($userIds)) {
        $in = implode(',', array_fill(0, count($userIds), '?'));
        $conditions[] = "user_id IN ($in)";
        $params = array_merge($params, $userIds);
    }

    $conditions[] = "title LIKE ?";
    $params[] = '%' . $searchInput . '%';

    $where .= " AND (" . implode(' OR ', $conditions) . ")";
}

$params[] = $limit;
$params[] = $offset;

$sql = "SELECT id, user_id, title, created_at FROM posts $where ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $con->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($posts as &$post) {
    $uStmt = $con->prepare("SELECT username FROM users WHERE id = ?");
    $uStmt->execute([$post['user_id']]);
    $user = $uStmt->fetch(PDO::FETCH_ASSOC);
    $post['username'] = $user ? $user['username'] : 'Unknown';
}
unset($post);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Simple Blog CMS</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<h1>Blog Posts</h1>

<form method="GET" action="index.php">
    <input type="text" name="search" placeholder="Search by author or keyword"
        value="<?php echo htmlspecialchars($searchInput, ENT_QUOTES); ?>">
    <button type="submit">Search</button>
</form>

<hr>

<div id="posts-container">
<?php if (empty($posts)): ?>
    <p>No posts found.</p>
<?php else: ?>
    <?php foreach ($posts as $post): ?>
        <article>
            <h2>
                <a href="post.php?id=<?php echo $post['id']; ?>">
                    <?php echo htmlspecialchars($post['title'], ENT_QUOTES); ?>
                </a>
            </h2>
            <p>
                By <?php echo htmlspecialchars($post['username'], ENT_QUOTES); ?>
                | <?php echo date("F j, Y", strtotime($post['created_at'])); ?>
            </p>
        </article>
        <hr>
    <?php endforeach; ?>
<?php endif; ?>
</div>

<button id="loadMore" <?php echo empty($posts) ? 'style="display:none"' : ''; ?>>
    Load More
</button>

<script>
let offset = <?php echo count($posts); ?>;
const limit = <?php echo $limit; ?>;
const search = "<?php echo htmlspecialchars($searchInput, ENT_QUOTES); ?>";

document.getElementById('loadMore').addEventListener('click', () => {
    let url = `load_more.php?offset=${offset}&limit=${limit}`;
    if (search) url += `&search=${encodeURIComponent(search)}`;

    fetch(url)
        .then(res => res.text())
        .then(html => {
            if (html.trim() === '') {
                document.getElementById('loadMore').style.display = 'none';
            } else {
                document.getElementById('posts-container')
                    .insertAdjacentHTML('beforeend', html);
                offset += limit;
            }
        })
        .catch(err => console.error(err));
});
</script>

</body>
</html>
