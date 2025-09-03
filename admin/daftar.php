<?php
session_start();
require_once '../config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if (empty($username) || empty($password) || empty($confirm)) {
        $error = 'Semua field harus diisi.';
    } elseif ($password !== $confirm) {
        $error = 'Password dan konfirmasi tidak cocok.';
    } else {
        // Cek apakah username sudah ada
        $stmt = $pdo->prepare("SELECT id FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = 'Username sudah digunakan.';
        } else {
            // Hash password dan simpan
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO admin (username, password, nama_lengkap, email) VALUES (?, ?, 'Admin', 'admin@example.com')");
            if ($stmt->execute([$username, $hashed])) {
                $success = 'Admin berhasil didaftarkan. Silakan login.';
            } else {
                $error = 'Gagal menyimpan data admin.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Admin - Kursus Online</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Daftar Admin</h2>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 mb-4 rounded">
                <?php echo $error; ?>
            </div>
        <?php elseif ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 mb-4 rounded">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm mb-2">Username</label>
                <input type="text" name="username" required class="w-full px-4 py-2 border rounded focus:ring-indigo-500">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm mb-2">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-2 border rounded focus:ring-indigo-500">
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm mb-2">Konfirmasi Password</label>
                <input type="password" name="confirm" required class="w-full px-4 py-2 border rounded focus:ring-indigo-500">
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700">
                Daftar
            </button>
        </form>
        <p class="text-center text-sm text-gray-600 mt-4">
            Sudah punya akun? <a href="login.php" class="text-indigo-600 hover:underline">Login</a>
        </p>
    </div>
</body>
</html>
