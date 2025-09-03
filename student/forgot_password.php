<?php
session_start();
require_once '../config/database.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $message = 'Email tidak boleh kosong.';
        $message_type = 'error';
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id, nama_lengkap FROM siswa WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Save token to database
            $stmt = $pdo->prepare("
                INSERT INTO password_resets (email, token, expires_at) 
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                token = VALUES(token), 
                expires_at = VALUES(expires_at),
                created_at = NOW()
            ");
            $stmt->execute([$email, $token, $expires_at]);
            
            // Send email
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/student/reset_password.php?token=" . $token;
            
            $subject = "Reset Password - Anastasya Vocal Arts";
            $message_body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #EE3A6A, #9E0232); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                    .button { display: inline-block; background: linear-gradient(135deg, #EE3A6A, #9E0232); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 20px 0; }
                    .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Reset Password</h1>
                        <h2>Anastasya Vocal Arts</h2>
                    </div>
                    <div class='content'>
                        <p>Halo " . htmlspecialchars($user['nama_lengkap']) . ",</p>
                        <p>Kami menerima permintaan untuk reset password akun Anda. Klik tombol di bawah untuk membuat password baru:</p>
                        
                        <div style='text-align: center;'>
                            <a href='" . $reset_link . "' class='button'>Reset Password</a>
                        </div>
                        
                        <p>Atau copy link berikut ke browser Anda:</p>
                        <p style='word-break: break-all; background: #e9e9e9; padding: 10px; border-radius: 5px;'>" . $reset_link . "</p>
                        
                        <p><strong>Link ini akan expired dalam 1 jam.</strong></p>
                        
                        <p>Jika Anda tidak meminta reset password, abaikan email ini.</p>
                    </div>
                    <div class='footer'>
                        <p>Anastasya Vocal Arts<br>
                        Email: info@anastasyavocalarts.com</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: Anastasya Vocal Arts <noreply@anastasyavocalarts.com>" . "\r\n";
            
            if (mail($email, $subject, $message_body, $headers)) {
                $message = 'Link reset password telah dikirim ke email Anda. Silakan cek inbox atau folder spam.';
                $message_type = 'success';
            } else {
                $message = 'Gagal mengirim email. Silakan coba lagi atau hubungi admin.';
                $message_type = 'error';
            }
        } else {
            $message = 'Email tidak ditemukan dalam sistem.';
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
    <title>Lupa Password - Anastasya Vocal Arts</title>
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
            <h2>Lupa Password</h2>
            <p style="text-align: center; color: #9E0232; margin-bottom: 20px; font-size: 14px;">
                Masukkan email Anda untuk menerima link reset password
            </p>
            
            <?php if ($message): ?>
                <div class="<?php echo $message_type === 'success' ? 'success-message' : 'error-message'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form action="forgot_password.php" method="POST">
                <div class="input-group">
                    <input type="email" id="email" name="email" placeholder=" " required>
                    <label for="email">Email</label>
                </div>
                <button type="submit" class="continue-button">Kirim Link Reset</button>
            </form>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="index.php" class="forgot-password">← Kembali ke Login</a>
            </div>
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