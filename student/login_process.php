<?php
session_start();
require '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        header('Location: index.php?error=Email and password are required.');
        exit();
    }

    $stmt = $pdo->prepare("SELECT id, password FROM siswa WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Password is correct
        $_SESSION['user_id'] = $user['id'];
        header('Location: dashboard.php');
        exit();
    } else {
        // Invalid credentials
        header('Location: index.php?error=Invalid email or password.');
        exit();
    }
} else {
    // Not a POST request
    header('Location: index.php');
    exit();
}
?>
