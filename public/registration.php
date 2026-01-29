<?php
require __DIR__ . '/../config/db.php';
$con = dbConnect();

$message = '';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        $username = trim($_POST['username']);

        if (!$email) {
            $message = "Please enter a valid email.";
        }
        elseif (empty($password)) {
            $message = "Password cannot be empty.";
        }
        elseif (strlen($password) < 8) {
            $message = "Password must be at least 8 characters long.";
        }
        elseif (empty($username)) {
    		$message = "Username cannot be empty.";
		}

        else {
            $checkSql = "SELECT id FROM users WHERE email = ?";
            $checkStmt = $con->prepare($checkSql);
            $checkStmt->execute([$email]);

            if ($checkStmt->fetch()) {
                $message = "Email already registered.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $insertSql = "INSERT INTO users (username, email, password, role)
                              VALUES (?, ?, ?, 'user')";
                $insertStmt = $con->prepare($insertSql);
                $insertStmt->execute([$username, $email, $hashedPassword]);

                header("Location: login.php");
                exit;
            }
        }
    }
} catch (PDOException $e) {
    $message = $e->getMessage();
}
?>


<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Registration Page</title>
</head>
<body>
	<h2>Register</h2>

	<?php if ($message): ?>
    <p><?php echo $message; ?></p>
	<?php endif; ?>

	<form method="POST">
    <label>Email:</label><br>
    <input type="text" name="email"><br><br>

    <label>Username:</label><br>
	<input type="text" name="username"><br><br>

    <label>Password:</label><br>
    <input type="password" name="password"><br><br>

    <button type="submit">Register</button>
	</form>

	<br>
	<a href="login.php">Go to Login</a>
</body>
</html>