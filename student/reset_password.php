<?php
session_start();
require_once '../config/database.php';

$token = $_GET['token'] ?? '';
$message = '';
$message_type = '';
$valid_token = false;

// Verify token
if (!empty($token)) {
    $stmt = $pdo->prepare("
        SELECT email FROM password_resets 
        WHERE token = ? AND expires_at > NOW()
    ");
    $stmt->execute([$token]);
    $reset_data = $stmt->fetch();
    
    if ($reset_data) {
        $valid_token = true;
        $email = $reset_data['email'];
    } else {
        $message = 'Link reset password tidak valid atau sudah expired.';
        $message_type = 'error';
    }
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($new_password) || empty($confirm_password)) {
        $message = 'Semua field harus diisi.';
        $message_type = 'error';
    } elseif (strlen($new_password) < 6) {
        $message = 'Password minimal 6 karakter.';
        $message_type = 'error';
    } elseif ($new_password !== $confirm_password) {
        $message = 'Password dan konfirmasi password tidak cocok.';
        $message_type = 'error';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE siswa SET password = ? WHERE email = ?");
            $stmt->execute([$hashed_password, $email]);
            
            // Delete used token
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
            $stmt->execute([$token]);
            
            $pdo->commit();
            
            $message = 'Password berhasil diubah! Silakan login dengan password baru.';
            $message_type = 'success';
            $valid_token = false; // Hide form
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = 'Terjadi kesalahan. Silakan coba lagi.';
            $message_type = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Anastasya Vocal Arts</title>
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
            <h2>Reset Password</h2>
            
            <?php if ($message): ?>
                <div class="<?php echo $message_type === 'success' ? 'success-message' : 'error-message'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if ($valid_token): ?>
                <form action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>" method="POST">
                    <div class="input-group">
                        <input type="password" id="password" name="password" placeholder=" " required minlength="6">
                        <label for="password">Password Baru</label>
                    </div>
                    <div class="input-group">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder=" " required minlength="6">
                        <label for="confirm_password">Konfirmasi Password</label>
                    </div>
                    <button type="submit" class="continue-button">Ubah Password</button>
                </form>
            <?php else: ?>
                <div style="text-align: center; margin-top: 20px;">
                    <a href="index.php" class="continue-button" style="display: inline-block; text-decoration: none;">
                        Kembali ke Login
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <style>
        .success-message {
            padding: 15px 20px;
            margin-bottom: 20px;
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(22, 163, 74, 0.1) 100%);
            color: #166534;
            border: 2px solid rgba(34, 197, 94, 0.3);
            border-radius: 15px;
            text-align: center;
            font-weight: 500;
            animation: fadeIn 0.5s ease-in-out;
        }
    </style>
</body>
</html>