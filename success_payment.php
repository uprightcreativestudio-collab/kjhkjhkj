
<?php
session_start();
require_once 'config/database.php';

$order_id = $_GET['order_id'] ?? '';
$transaction_status = $_GET['transaction_status'] ?? '';

if (empty($order_id)) {
    header('Location: /');
    exit;
}

// Get payment details
$stmt = $pdo->prepare("SELECT p.*, s.nama_lengkap FROM payments p JOIN siswa s ON p.student_id = s.id WHERE p.order_id = ?");
$stmt->execute([$order_id]);
$payment = $stmt->fetch();

if (!$payment) {
    header('Location: /');
    exit;
}

$page_title = 'Pembayaran Berhasil';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-8 text-center">
        <div class="mb-6">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-check text-3xl text-green-500"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Pembayaran Berhasil!</h1>
            <p class="text-gray-600">Terima kasih atas pembayaran Anda</p>
        </div>

        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <div class="text-left space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">Order ID:</span>
                    <span class="font-medium"><?php echo htmlspecialchars($order_id); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Nama:</span>
                    <span class="font-medium"><?php echo htmlspecialchars($payment['nama_lengkap']); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Jumlah:</span>
                    <span class="font-medium">Rp <?php echo number_format($payment['gross_amount'], 0, ',', '.'); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Status:</span>
                    <span class="font-medium text-green-600">Lunas</span>
                </div>
            </div>
        </div>

        <div class="space-y-3">
            <a href="/user/dashboard.php" class="block w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-home mr-2"></i>Kembali ke Dashboard
            </a>
            <a href="/user/payment_history.php" class="block w-full bg-gray-200 text-gray-800 py-3 px-4 rounded-lg hover:bg-gray-300 transition">
                <i class="fas fa-history mr-2"></i>Riwayat Pembayaran
            </a>
        </div>

        <p class="text-xs text-gray-500 mt-6">
            Bukti pembayaran telah dikirim ke email Anda
        </p>
    </div>
</body>
</html>
