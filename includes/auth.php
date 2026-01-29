<?php

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        die("Access Denied");
    }
}

function requireOwnerOrAdmin($ownerId) {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
    if (!isAdmin() && $_SESSION['user_id'] !== $ownerId) {
        die("Access Denied");
    }
}
?>
