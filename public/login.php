<?php

require __DIR__ . '/../includes/session.php';
require __DIR__ . '/../config/db.php';
$con = dbConnect();

if (empty($_SESSION['csrf_token'])) {
	$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

try {
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {

		if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
			die ("Invalid CSRF Token");
		}

		$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
		$password = $_POST['password'] ?? '';

		if(!$email || empty($password)) {
			$error = "Email and password are required.";
		} else {

			$loginSql = "SELECT id, email, password, role FROM users WHERE email = ?";
			$loginStmt = $con->prepare($loginSql);
			$loginStmt->execute([$email]);
			$user = $loginStmt->fetch(PDO::FETCH_ASSOC);

			if($user && password_verify($password, $user['password'])) {
				$_SESSION['user_id'] = $user['id'];
				$_SESSION['email'] = $user['email'];
				$_SESSION['role'] = $user['role'];

				header("Location: index.php");
				exit;
				
			} else {
				$error = "Invalid email or password.";
			}
		}
	}
} catch (Exception $e) {
	$error = "Invalid email or password";
}

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Login</title>
</head>
<body>
	<h1>Login:</h1>

	<?php if (!empty($error)): ?>
		<p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
	<?php endif; ?>

	<form method="POST">
		<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

		<label>Email:</label>
		<input type="email" name="email" required><br><br>

		<label>Password:</label>
		<input type="password" name="password" required><br><br>

		<button type="submit">Login</button>
	</form>
</body>
</html>