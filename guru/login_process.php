<?php
session_start();
require '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? true : false;

    if (empty($username) || empty($password)) {
        header('Location: index.php?error=Username and password are required.');
        exit();
    }

    $stmt = $pdo->prepare("SELECT id, nama_lengkap, password FROM guru WHERE username = ?");
    $stmt->execute([$username]);
    $guru = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($guru && password_verify($password, $guru['password'])) {
        // Password is correct
        $_SESSION['guru_id'] = $guru['id'];
        $_SESSION['guru_nama'] = $guru['nama_lengkap'];
        
        // Handle remember me functionality
        if ($remember) {
            // Generate remember token
            $remember_token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
            
            // Save token to database
            $stmt = $pdo->prepare("UPDATE guru SET remember_token = ?, remember_token_expires = ? WHERE id = ?");
            $stmt->execute([$remember_token, $expires, $guru['id']]);
            
            // Set cookie
            setcookie('guru_remember_token', $remember_token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
        }
        
        // Unset any previous student scan data
        unset($_SESSION['scanned_student_id']);
        unset($_SESSION['scanned_student_name']);
        header('Location: dashboard.php');
        exit();
    } else {
        // Invalid credentials
        header('Location: index.php?error=Invalid username or password.');
        exit();
    }
} else {
    // Not a POST request
    header('Location: index.php');
    exit();
}
