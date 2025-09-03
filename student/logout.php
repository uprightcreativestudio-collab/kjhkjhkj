<?php
session_start();
require '../config/database.php';

// Clear remember token from database if user is logged in
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("UPDATE siswa SET remember_token = NULL, remember_token_expires = NULL WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
}

// Clear remember me cookie
if (isset($_COOKIE['student_remember_token'])) {
    setcookie('student_remember_token', '', time() - 3600, '/');
}

session_unset();
session_destroy();
header('Location: index.php');
exit();
?>
