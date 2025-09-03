<?php
session_start();
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
    </form>
  </div>
</body>
</html>
