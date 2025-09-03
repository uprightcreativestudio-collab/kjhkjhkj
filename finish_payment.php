<?php
session_start();

// Clear all registration session data after successful payment
unset($_SESSION['registration_success']);
unset($_SESSION['student_id']);
unset($_SESSION['order_id']);
unset($_SESSION['student_name']);
unset($_SESSION['gross_amount']);
unset($_SESSION['promo_name']);
unset($_SESSION['harga_kursus']);
unset($_SESSION['biaya_pendaftaran']);
unset($_SESSION['pajak_kursus']);
unset($_SESSION['pajak_pendaftaran']);
unset($_SESSION['harga_kursus_base']);
unset($_SESSION['biaya_pendaftaran_base']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Selesai - Anastasya Vocal Arts</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .animate-bounce-slow {
            animation: bounce 2s infinite;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-2xl p-8 text-center">
        <!-- Success Animation -->
        <div class="mb-8">
            <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4 animate-bounce-slow">
                <i class="fas fa-check text-4xl text-green-500"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Selamat!</h1>
            <p class="text-gray-600 text-lg">Pembayaran Anda telah berhasil</p>
        </div>

        <?php 
        require_once 'config/database.php';

        $order_id = $_GET['order_id'] ?? '';
        if ($order_id) {
            $stmt = $pdo->prepare("
                SELECT p.*, s.nama_lengkap, s.email 
                FROM payments p 
                JOIN siswa s ON p.student_id = s.id 
                WHERE p.order_id = ?
            ");
            $stmt->execute([$order_id]);
            $payment = $stmt->fetch();

            if ($payment) {
        ?>
        <!-- Payment Details -->
        <div class="bg-gray-50 rounded-xl p-6 mb-8">
            <div class="space-y-3 text-left">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 font-medium">Order ID:</span>
                    <span class="font-bold text-sm"><?php echo htmlspecialchars($order_id); ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 font-medium">Nama:</span>
                    <span class="font-bold"><?php echo htmlspecialchars($payment['nama_lengkap']); ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 font-medium">Total:</span>
                    <span class="font-bold text-lg text-green-600">Rp <?php echo number_format($payment['gross_amount'], 0, ',', '.'); ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 font-medium">Status:</span>
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-bold">
                        <i class="fas fa-check-circle mr-1"></i>LUNAS
                    </span>
                </div>
            </div>
        </div>
        <?php 
            }
        }
        ?>

        <!-- Next Steps -->
        <div class="bg-blue-50 rounded-xl p-6 mb-8">
            <h3 class="font-bold text-gray-800 mb-3">Langkah Selanjutnya:</h3>
            <div class="text-left space-y-2 text-sm text-gray-700">
                <div class="flex items-center">
                    <i class="fas fa-circle-check text-blue-500 mr-2"></i>
                    <span>Konfirmasi pembayaran ke admin</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>
                    <span>Tunggu jadwal kelas dari admin</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-music text-blue-500 mr-2"></i>
                    <span>Mulai perjalanan vokal Anda!</span>
                </div>
            </div>
        </div>

        <!-- Chat Admin Button -->
        <div class="space-y-4">
            <a href="https://wa.me/6208112233439?text=Halo%20Admin%2C%20saya%20telah%20menyelesaikan%20pembayaran%20pendaftaran%20dengan%20Order%20ID%3A%20<?php echo urlencode($order_id ?? 'N/A'); ?>%0A%0AMohon%20konfirmasi%20dan%20informasi%20jadwal%20selanjutnya.%20Terima%20kasih%21" 
               target="_blank"
               class="block w-full bg-green-500 hover:bg-green-600 text-white font-bold py-4 px-6 rounded-xl transition duration-300 transform hover:scale-105 shadow-lg">
                <i class="fab fa-whatsapp text-xl mr-2"></i>
                Chat Admin untuk Konfirmasi
            </a>

            <a href="/user/dashboard.php" 
               class="block w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-xl transition duration-300">
                <i class="fas fa-tachometer-alt mr-2"></i>
                Ke Dashboard
            </a>

            <a href="/" 
               class="block w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-3 px-6 rounded-xl transition duration-300">
                <i class="fas fa-home mr-2"></i>
                Kembali ke Beranda
            </a>
        </div>

        <!-- Contact Info -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <p class="text-xs text-gray-500 mb-2">
                <i class="fas fa-info-circle mr-1"></i>
                Bukti pembayaran telah dikirim ke email Anda
            </p>
            <p class="text-xs text-gray-400">
                Jika ada kendala, hubungi admin di nomor: 08112233439
            </p>
        </div>
    </div>

    <script>
        // Auto refresh payment status setiap 30 detik
        let refreshCount = 0;
        const maxRefresh = 10; // Maximum 5 menit

        function checkPaymentStatus() {
            if (refreshCount >= maxRefresh) return;

            const urlParams = new URLSearchParams(window.location.search);
            const orderId = urlParams.get('order_id');

            if (!orderId) return;

            fetch('check_payment_status.php?order_id=' + orderId)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'paid') {
                        // Payment confirmed, stop checking
                        clearInterval(statusInterval);
                    } else if (data.status === 'failed' || data.status === 'expired') {
                        // Payment failed, redirect
                        window.location.href = 'error_payment.php?order_id=' + orderId;
                    }
                })
                .catch(error => {
                    console.log('Status check error:', error);
                });

            refreshCount++;
        }

        // Check status every 30 seconds
        const statusInterval = setInterval(checkPaymentStatus, 30000);

        // Initial check
        checkPaymentStatus();
    </script>
</body>
</html>