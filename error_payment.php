
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
$stmt = $pdo->prepare("SELECT p.*, s.nama_lengkap, s.email, s.telepon FROM payments p JOIN siswa s ON p.student_id = s.id WHERE p.order_id = ?");
$stmt->execute([$order_id]);
$payment = $stmt->fetch();

if (!$payment) {
    header('Location: /');
    exit;
}

// Handle manual payment proof upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bukti_pembayaran'])) {
    $upload_dir = 'uploads/bukti_pembayaran/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file = $_FILES['bukti_pembayaran'];
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
    
    if (in_array(strtolower($file_extension), $allowed_extensions) && $file['size'] < 5000000) {
        $filename = $order_id . '_' . time() . '.' . $file_extension;
        $filepath = $upload_dir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Save to database
            $stmt = $pdo->prepare("UPDATE payments SET bukti_manual = ?, status_manual_upload = 'pending' WHERE order_id = ?");
            $stmt->execute([$filepath, $order_id]);
            
            // Send WhatsApp notification to admin
            require_once 'email_helper.php';
            $whatsapp_message = "📋 BUKTI PEMBAYARAN MANUAL DITERIMA\n\n";
            $whatsapp_message .= "Order ID: " . $order_id . "\n";
            $whatsapp_message .= "Nama: " . $payment['nama_lengkap'] . "\n";
            $whatsapp_message .= "Email: " . $payment['email'] . "\n";
            $whatsapp_message .= "Telepon: " . $payment['telepon'] . "\n";
            $whatsapp_message .= "Jumlah: Rp " . number_format($payment['gross_amount'], 0, ',', '.') . "\n";
            $whatsapp_message .= "Waktu Upload: " . date('d F Y, H:i') . "\n\n";
            $whatsapp_message .= "Silakan cek bukti pembayaran di admin panel: https://" . $_SERVER['HTTP_HOST'] . "/admin/pembayaran.php";
            
            sendWhatsAppNotification($pdo, $whatsapp_message);
            
            // Also send direct WhatsApp link to admin
            $stmt = $pdo->prepare("SELECT whatsapp_number FROM admin_settings WHERE id = 1");
            $stmt->execute();
            $admin_settings = $stmt->fetch();
            if ($admin_settings && $admin_settings['whatsapp_number']) {
                $admin_whatsapp_url = "https://wa.me/" . $admin_settings['whatsapp_number'] . "?text=" . urlencode($whatsapp_message);
                echo "<script>
                    if(confirm('Bukti pembayaran berhasil diupload! Apakah Anda ingin langsung mengirim notifikasi ke admin via WhatsApp?')) {
                        window.open('" . $admin_whatsapp_url . "', '_blank');
                    }
                </script>";
            }
            
            $success_message = "Bukti pembayaran berhasil diupload dan dikirim ke admin. Pembayaran akan diverifikasi dalam 1x24 jam.";
        } else {
            $error_message = "Gagal mengupload file. Silakan coba lagi.";
        }
    } else {
        $error_message = "File tidak valid. Gunakan format JPG, PNG, atau PDF dengan ukuran maksimal 5MB.";
    }
}

$page_title = 'Pembayaran Gagal';
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
<body class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-8">
        <div class="text-center mb-6">
            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-times text-3xl text-red-500"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Pembayaran Gagal</h1>
            <p class="text-gray-600">Terjadi kesalahan dalam proses pembayaran</p>
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
                    <span class="font-medium text-red-600">Gagal</span>
                </div>
            </div>
        </div>

        <?php if (isset($success_message)): ?>
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded mb-4">
            <i class="fas fa-check-circle mr-2"></i><?php echo $success_message; ?>
        </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded mb-4">
            <i class="fas fa-exclamation-triangle mr-2"></i><?php echo $error_message; ?>
        </div>
        <?php endif; ?>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-blue-800 mb-2">
                <i class="fas fa-upload mr-2"></i>Upload Bukti Pembayaran Manual
            </h3>
            <p class="text-blue-700 text-sm mb-4">
                Jika Anda sudah melakukan pembayaran melalui transfer bank atau metode lain, silakan upload bukti pembayaran di bawah ini.
            </p>
            
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label for="bukti_pembayaran" class="block text-sm font-medium text-gray-700 mb-2">
                        Pilih File Bukti Pembayaran
                    </label>
                    <input type="file" name="bukti_pembayaran" id="bukti_pembayaran" 
                           accept=".jpg,.jpeg,.png,.pdf" required
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-xs text-gray-500 mt-1">
                        Format: JPG, PNG, PDF. Maksimal 5MB
                    </p>
                </div>
                
                <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-cloud-upload-alt mr-2"></i>Upload Bukti Pembayaran
                </button>
            </form>
        </div>

        <div class="space-y-3">
            <a href="/user/select_payment.php?retry=<?php echo urlencode($order_id); ?>" 
               class="block w-full bg-green-600 text-white py-3 px-4 rounded-lg hover:bg-green-700 transition text-center">
                <i class="fas fa-redo mr-2"></i>Coba Pembayaran Lagi
            </a>
            <a href="/user/dashboard.php" class="block w-full bg-gray-200 text-gray-800 py-3 px-4 rounded-lg hover:bg-gray-300 transition text-center">
                <i class="fas fa-home mr-2"></i>Kembali ke Dashboard
            </a>
        </div>

        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
            <h4 class="font-semibold text-gray-800 mb-2">Butuh Bantuan?</h4>
            <p class="text-sm text-gray-600 mb-3">Hubungi admin untuk bantuan pembayaran:</p>
            
            <?php
            // Get admin WhatsApp number
            $stmt = $pdo->prepare("SELECT whatsapp_number FROM admin_settings WHERE id = 1");
            $stmt->execute();
            $admin_settings = $stmt->fetch();
            $whatsapp_number = $admin_settings['whatsapp_number'] ?? '6281234567890';
            
            // WhatsApp message
            $whatsapp_message = "Halo Admin, saya mengalami kendala pembayaran:\n\n";
            $whatsapp_message .= "Order ID: " . $order_id . "\n";
            $whatsapp_message .= "Nama: " . $payment['nama_lengkap'] . "\n";
            $whatsapp_message .= "Jumlah: Rp " . number_format($payment['gross_amount'], 0, ',', '.') . "\n";
            $whatsapp_message .= "Status: Pembayaran Gagal\n\n";
            $whatsapp_message .= "Mohon bantuan untuk menyelesaikan pembayaran ini. Terima kasih.";
            
            $whatsapp_url = "https://wa.me/" . $whatsapp_number . "?text=" . urlencode($whatsapp_message);
            ?>
            
            <div class="space-y-3">
                <a href="<?php echo $whatsapp_url; ?>" 
                   target="_blank"
                   class="flex items-center justify-center w-full bg-green-500 text-white py-3 px-4 rounded-lg hover:bg-green-600 transition">
                    <i class="fab fa-whatsapp mr-2 text-lg"></i>
                    Chat Admin via WhatsApp
                </a>
                
                <div class="text-sm text-center text-gray-600">
                    <p><i class="fas fa-envelope text-blue-600 mr-2"></i>Email: admin@avaprogram.com</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
