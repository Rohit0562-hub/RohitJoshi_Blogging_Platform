<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
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
	<header style="margin-bottom: 20px;">
		<nav>
			<a href="index.php">Home</a>

			<?php if(!empty($_SESSION['user_id'])): ?>
				| <a href="add_post.php">Add a post</a>
				| <a href="logout.php" onclick="return confirm('Are you sure you want to logout?');">
					Logout
				</a>
			<?php else: ?>
				| <a href="login.php">Login</a>
				| <a href="register.php">Register</a>
			<?php endif; ?>
		</nav>
	</header>

<hr>