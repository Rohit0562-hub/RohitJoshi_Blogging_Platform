<?php
require __DIR__ . '/../includes/session.php';
require __DIR__ . '/../config/db.php';

$con = dbConnect();

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

$searchInput = isset($_GET['search']) ? trim($_GET['search']) : '';

$posts = [];
$params = [];
$where  = "status = 'published'";

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

$sql = "SELECT id, user_id, title, created_at FROM posts WHERE $where ORDER BY created_at DESC LIMIT ? OFFSET ? ";
$postStmt = $con->prepare($sql);
$postStmt->execute($params);
$posts = $postStmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($posts as &$post) {
    $userStmt = $con->prepare("SELECT username FROM users WHERE id = ?");
    $userStmt->execute([$post['user_id']]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    $post['username'] = $user ? $user['username'] : 'Unknown';
}
unset($post);

foreach ($post as $posts) {

    echo '<article>';
    echo '<h2><a href="post.php?id='.$post['id'].'">'.htmlspecialchars($post['title'], ENT_QUOTES).'</a></h2>';
    echo '<p>By '.htmlspecialchars($post['username'], ENT_QUOTES).' | '.date("F j, Y", strtotime($post['created_at'])).'</p>';
    echo '</article><hr>';
}
?>