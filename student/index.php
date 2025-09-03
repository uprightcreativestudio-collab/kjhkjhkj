<?php
session_start();
require '../config/database.php';

// Check for remember me token
if (!isset($_SESSION['user_id']) && isset($_COOKIE['student_remember_token'])) {
    $remember_token = $_COOKIE['student_remember_token'];
    
    $stmt = $pdo->prepare("
        SELECT id, nama_lengkap 
        FROM siswa 
        WHERE remember_token = ? 
        AND remember_token_expires > NOW()
    ");
    $stmt->execute([$remember_token]);
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        header('Location: dashboard.php');
        exit();
    } else {
        // Invalid or expired token, clear cookie
        setcookie('student_remember_token', '', time() - 3600, '/');
    }
}

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="header-bg">
            <div class="circle"></div>
        </div>
        <img src="../avaaset/AVA-Logo-Master 1-White@16x.png" alt="Logo" class="logo">

        <div class="login-card">
            <h2>Login </h2>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="login_process.php" method="POST">
                <div class="input-group">
                    <input type="email" id="email" name="email" placeholder=" " required>
                    <label for="email">Email</label>
                </div>
                <div class="input-group">
                    <input type="password" id="password" name="password" placeholder=" " required>
                    <label for="password">Password</label>
                </div>
                <button type="submit" class="continue-button">Continue</button>
            </form>
            <div class="flex items-center justify-between mt-4">
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-pink-accent focus:ring-pink-accent border-pink-300 rounded">
                    <label for="remember" class="ml-2 block text-sm text-pink-dark">
                        Remember me
                    </label>
                </div>
            </div>
            <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
        </div>
    </div>
</body>
</html>
