<?php
session_start();
require_once '../config/database.php';

// Clear remember token from database if admin is logged in
if (isset($_SESSION['admin_id'])) {
    try {
        $stmt = $pdo->prepare("UPDATE admin SET remember_token = NULL, remember_token_expires = NULL WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
    } catch (Exception $e) {
        // Ignore errors for admin table if columns don't exist
    }
}

// Clear remember me cookie
if (isset($_COOKIE['admin_remember_token'])) {
    setcookie('admin_remember_token', '', time() - 3600, '/');
}

session_unset();
session_destroy();
header("Location: .");
exit();
?>
