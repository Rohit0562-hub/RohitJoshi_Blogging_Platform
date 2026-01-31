<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../includes/session.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';

requireLogin();
require __DIR__ . '/../includes/header.php';

$con = dbConnect();

$limit = 5;
$offset = 0;

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

<h1>Blog Posts</h1>

<form method="GET" action="index.php">
    <input type="text" id="searchInput" name="search" placeholder="Search by author or keyword"
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
let offset = 0;
const limit = <?php echo $limit; ?>;
let searchValue = '';
let typingTimer;
const debounceDelay = 300;

const postsContainer = document.getElementById('posts-container');
const loadMoreBtn = document.getElementById('loadMore');
const searchInput = document.getElementById('searchInput');

function fetchPosts(reset = false) {
    let url = `load_more.php?offset=${offset}&limit=${limit}`;

    if (searchValue) {
        url += `&search=${encodeURIComponent(searchValue)}`;
    }

    fetch(url)
        .then(res => res.text())
        .then(html => {
            if (reset) {
                postsContainer.innerHTML = '';
                offset = 0;
            }

            if (html.trim() === '') {
                loadMoreBtn.style.display = 'none';
            } else {
                postsContainer.insertAdjacentHTML('beforeend', html);
                loadMoreBtn.style.display = 'block';
                offset += limit;
            }
        })
        .catch(err => console.error(err));
}

searchInput.addEventListener('input', () => {
    clearTimeout(typingTimer);

    typingTimer = setTimeout(() => {
        searchValue = searchInput.value.trim();
        offset = 0;
        fetchPosts(true);
    }, debounceDelay);
});

loadMoreBtn.addEventListener('click', () => {
    fetchPosts(false);
});
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
