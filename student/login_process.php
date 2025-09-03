<?php
session_start();
require '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? true : false;

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
        
        // Handle remember me functionality
        if ($remember) {
            // Generate remember token
            $remember_token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
            
            // Save token to database
            $stmt = $pdo->prepare("UPDATE siswa SET remember_token = ?, remember_token_expires = ? WHERE id = ?");
            $stmt->execute([$remember_token, $expires, $user['id']]);
            
            // Set cookie
            setcookie('student_remember_token', $remember_token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
        }
        
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
