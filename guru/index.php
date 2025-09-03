<?php
session_start();
require '../config/database.php';

// Check for remember me token
if (!isset($_SESSION['guru_id']) && isset($_COOKIE['guru_remember_token'])) {
    $remember_token = $_COOKIE['guru_remember_token'];
    
    $stmt = $pdo->prepare("
        SELECT id, nama_lengkap 
        FROM guru 
        WHERE remember_token = ? 
        AND remember_token_expires > NOW()
    ");
    $stmt->execute([$remember_token]);
    $guru = $stmt->fetch();
    
    if ($guru) {
        $_SESSION['guru_id'] = $guru['id'];
        $_SESSION['guru_nama'] = $guru['nama_lengkap'];
        header('Location: dashboard.php');
        exit();
    } else {
        // Invalid or expired token, clear cookie
        setcookie('guru_remember_token', '', time() - 3600, '/');
    }
}

if (isset($_SESSION['guru_id'])) {
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
  <title>Guru Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-pink-100 via-pink-200 to-[#800020]">
  <div class="w-full max-w-md p-8 space-y-6 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-pink-100">
    
    <!-- Logo -->
    <div class="flex justify-center">
      <img src="../avaaset/logo-ava.png" alt="Logo AVA" class="h-16 w-auto drop-shadow-md">
    </div>

    <!-- Title -->
    <div class="text-center">
      <h1 class="text-3xl font-extrabold text-[#800020]">Portal Guru</h1>
      <p class="mt-2 text-sm text-gray-600">Silakan masuk untuk melanjutkan</p>
    </div>

    <!-- Error message -->
    <?php if ($error): ?>
      <div class="bg-pink-100 border border-pink-300 text-[#800020] px-4 py-3 rounded-lg text-sm shadow-sm" role="alert">
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <!-- Form -->
    <form class="space-y-6" action="login_process.php" method="POST">
      <div>
        <label for="username" class="text-sm font-medium text-gray-700">Username</label>
        <input id="username" name="username" type="text" required
          class="mt-1 block w-full px-3 py-2 bg-pink-50 border border-pink-200 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-[#800020] focus:border-[#800020] sm:text-sm">
      </div>

      <div>
        <label for="password" class="text-sm font-medium text-gray-700">Password</label>
        <input id="password" name="password" type="password" required
          class="mt-1 block w-full px-3 py-2 bg-pink-50 border border-pink-200 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-[#800020] focus:border-[#800020] sm:text-sm">
      </div>

      <div>
        <button type="submit"
          class="w-full flex justify-center py-2 px-4 rounded-md shadow-md text-sm font-semibold text-white bg-[#800020] hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 transition">
          Login
        </button>
      </div>

      <div class="flex items-center justify-between mt-4">
        <div class="flex items-center">
          <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-[#800020] focus:ring-[#800020] border-pink-300 rounded">
          <label for="remember" class="ml-2 block text-sm text-gray-700">
            Remember me
          </label>
        </div>
      </div>
    </form>
  </div>
</body>
</html>
