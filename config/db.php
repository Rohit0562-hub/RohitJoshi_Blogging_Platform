<?php

function dbConnect() {
	$server = "mysql:host=localhost;dbname=blog_cms;charset=utf8mb4";
	$user = "root";
	$password = "";

	try {
		$con = new PDO($server, $user, $password);

		return $con;
	} catch (PDOException $e) {
		die("Database Connection Error " . $e->getMessage());
	}
}
?>