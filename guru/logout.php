<?php
session_start();
require '../config/database.php';

// Clear remember token from database if user is logged in
if (isset($_SESSION['guru_id'])) {
    $stmt = $pdo->prepare("UPDATE guru SET remember_token = NULL, remember_token_expires = NULL WHERE id = ?");
    $stmt->execute([$_SESSION['guru_id']]);
}

// Clear remember me cookie
if (isset($_COOKIE['guru_remember_token'])) {
    setcookie('guru_remember_token', '', time() - 3600, '/');
}

// Unset all of the session variables.
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();

header("Location: index.php");
exit();
