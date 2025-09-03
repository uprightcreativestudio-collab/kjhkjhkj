<?php
session_start();
require_once 'config/database.php';

// Get order_id from session or URL parameter
$order_id = $_SESSION['order_id'] ?? $_GET['order_id'] ?? '';

if (empty($order_id)) {
    // If no order_id at all, redirect to home
    header('Location: index.php?error=' . urlencode('Order ID tidak ditemukan. Silakan daftar ulang.'));
    exit;
}

// Always get fresh payment data from database
$stmt = $pdo->prepare("
    SELECT p.*, s.nama_lengkap, s.id as student_id, s.kode_promo, pr.nama_promo 
    FROM payments p 
    JOIN siswa s ON p.student_id = s.id 
    LEFT JOIN promo_codes pr ON s.kode_promo = pr.kode_promo
    WHERE p.order_id = ?
");
$stmt->execute([$order_id]);
$payment_data = $stmt->fetch();

if (!$payment_data) {
    header('Location: index.php?error=' . urlencode('Data pembayaran tidak ditemukan. Silakan daftar ulang.'));
    exit;
}

// Check if payment is already completed
if ($payment_data['transaction_status'] === 'paid') {
    header('Location: finish_payment.php?order_id=' . urlencode($order_id) . '&transaction_status=settlement');
    exit;
}

// Set/update session data with fresh data
$_SESSION['order_id'] = $order_id;
$_SESSION['student_id'] = $payment_data['student_id'];
$_SESSION['student_name'] = $payment_data['nama_lengkap'];
$_SESSION['gross_amount'] = $payment_data['gross_amount'];
$_SESSION['promo_name'] = $payment_data['nama_promo'] ?? 'Harga Reguler';

// Use the fresh payment data we already retrieved
$payment = $payment_data;
$student_name = $payment['nama_lengkap'];
$gross_amount = $payment['gross_amount'];
$promo_name = $payment['nama_promo'] ?? 'Harga Reguler';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - Anastasya Vocal Arts</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script type="text/javascript" 
            src="<?php 
                $stmt = $pdo->query("SELECT is_production FROM midtrans_settings WHERE id = 1");
                $is_production = (bool)$stmt->fetchColumn();
                echo $is_production ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js';
            ?>" 
            data-client-key="<?php 
                $stmt = $pdo->query("SELECT client_key FROM midtrans_settings WHERE id = 1");
                $client_key = $stmt->fetchColumn();
                echo $client_key ?: 'SB-Mid-client-Q-sik9HB-Mtgrfff'; 
            ?>"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">Pembayaran Pendaftaran</h2>
                <p class="mt-2 text-sm text-gray-600">Selesaikan pembayaran untuk mengaktifkan akun Anda</p>

                <?php if (isset($_GET['error'])): ?>
                <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="space-y-4">
                    <div class="border-b pb-4">
                        <h3 class="font-semibold text-lg text-gray-800">Detail Pendaftaran</h3>
                        <p class="text-gray-600">Nama: <?php echo htmlspecialchars($student_name); ?></p>
                        <p class="text-gray-600">Paket: <?php echo htmlspecialchars($promo_name); ?></p>
                        <?php if ($payment['kode_promo']): ?>
                            <p class="text-green-600 font-medium">✓ Kode Promo: <?php echo htmlspecialchars($payment['kode_promo']); ?></p>
                        <?php endif; ?>
                        <p class="text-gray-600">Order ID: <?php echo htmlspecialchars($order_id); ?></p>
                    </div>

                    <div class="border-b pb-4">
                        <h4 class="font-medium text-gray-800">Rincian Biaya</h4>

                        <div class="space-y-1 mb-3">
                            <!-- Course Fee -->
                            <div class="flex justify-between text-sm">
                                <span class="<?php echo $payment['kode_promo'] ? 'text-green-600 font-medium' : 'text-gray-600'; ?>">
                                    Biaya Kursus <?php echo $payment['kode_promo'] ? '(Promo: ' . htmlspecialchars($payment['kode_promo']) . ')' : '(Reguler)'; ?>
                                </span>
                                <span>Rp <?php echo number_format($payment['harga_kursus'], 0, ',', '.'); ?></span>
                            </div>

                            <!-- Registration Fee -->
                            <div class="flex justify-between text-sm">
                                <span class="<?php echo $payment['kode_promo'] ? 'text-green-600 font-medium' : 'text-gray-600'; ?>">
                                    Biaya Pendaftaran <?php echo $payment['kode_promo'] ? '(Promo: ' . htmlspecialchars($payment['kode_promo']) . ')' : '(Reguler)'; ?>
                                </span>
                                <span>Rp <?php echo number_format($payment['biaya_pendaftaran'], 0, ',', '.'); ?></span>
                            </div>

                            <div class="border-t pt-1 mt-2">
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>Subtotal</span>
                                    <span>Rp <?php echo number_format($payment['harga_kursus'] + $payment['biaya_pendaftaran'], 0, ',', '.'); ?></span>
                                </div>
                                
                                

                                <div class="flex justify-between text-sm text-gray-500">
                                    <span>Biaya Admin</span>
                                    <span>Rp <?php echo number_format($payment['midtrans_fee'] ?? 5000, 0, ',', '.'); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between font-semibold text-lg border-t pt-2 mt-2">
                            <span>Total Pembayaran</span>
                            <span>Rp <?php echo number_format($payment['gross_amount'], 0, ',', '.'); ?></span>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h4 class="font-medium text-gray-800 text-center">Pilih Metode Pembayaran</h4>
                        <h5 class="text-gray-800 text-center">BNI BRI MANDIRI Permatabank CIMB Niaga</h5>
                        <!-- Online Payment -->
                        <?php if ($payment['snap_token']): ?>
                        <button id="pay-button" class="w-full bg-indigo-600 text-white py-3 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-150 ease-in-out flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                            Bayar Online (Virtual Account)
                        </button>
                        <?php endif; ?>

                        <!-- Manual Transfer -->
                        <div class="border-t pt-4">
                            <button id="manual-transfer-btn" class="w-full bg-green-600 text-white py-3 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition duration-150 ease-in-out flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Transfer Manual Bank
                            </button>
                        </div>

                        <!-- Contact Admin -->
                        <div class="border-t pt-4">
                            <a href="https://wa.me/628112233439?text=Halo%20admin,%20saya%20ingin%20bertanya%20tentang%20pembayaran%20untuk%20Order%20ID:%20<?php echo urlencode($order_id); ?>" 
                               target="_blank" 
                               class="w-full bg-orange-500 text-black py-3 px-4 rounded-md hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition duration-150 ease-in-out flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                                </svg>
                                Hubungi Admin WhatsApp
                            </a>
                        </div>

                        <?php if (!$payment['snap_token']): ?>
                        <div class="text-center text-red-600 bg-red-50 p-3 rounded-lg">
                            <p>Sistem pembayaran online sedang tidak tersedia. Silakan gunakan transfer manual atau hubungi admin.</p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="text-center text-sm text-gray-500 mt-4">
                        <p>Setelah pembayaran berhasil, Anda akan dapat login ke sistem.</p>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <a href="index.php" class="text-indigo-600 hover:text-indigo-500">
                    ← Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>

    <!-- Manual Transfer Modal -->
    <div id="manualTransferModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 max-h-screen overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Transfer Manual Bank</h3>
                <button onclick="closeManualTransferModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="px-6 py-4">
                <div class="space-y-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-blue-900 mb-2">Informasi Rekening</h4>
                        <div class="space-y-2 text-sm text-blue-800">
                            <p><strong>Bank BNI</strong></p>
                            <p>No. Rekening: <span class="font-mono">2110101023</span></p>
                            <p>Atas Nama: <strong>Anastasya Karya Harmoni</strong></p>
                            <p class="text-lg font-bold text-blue-900 mt-2">
                                Total: Rp <?php echo number_format($gross_amount, 0, ',', '.'); ?>
                            </p>
                        </div>
                    </div>

                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-yellow-900 mb-2">Petunjuk Transfer</h4>
                        <ol class="list-decimal list-inside text-sm text-yellow-800 space-y-1">
                            <li>Transfer sesuai nominal yang tertera</li>
                            <li>Sertakan Order ID: <strong><?php echo htmlspecialchars($order_id); ?></strong></li>
                            <li>Upload bukti transfer di bawah</li>
                            <li>Tunggu konfirmasi dari admin</li>
                        </ol>
                    </div>

                    <form id="manualPaymentForm" enctype="multipart/form-data">
                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_id); ?>">

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Upload Bukti Transfer</label>
                                <input type="file" name="payment_proof" id="payment_proof" accept="image/*,.pdf" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, PDF (Max 5MB)</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                                <textarea name="notes" rows="3" placeholder="Tambahkan catatan jika diperlukan..."
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                            </div>

                            <button type="submit" class="w-full bg-green-600 text-white py-3 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition duration-150 ease-in-out">
                                Submit Bukti Pembayaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        // Online payment
        <?php if ($payment['snap_token']): ?>
        const payButton = document.getElementById('pay-button');
        if (payButton) {
            payButton.addEventListener('click', function () {
                snap.pay('<?php echo $payment['snap_token']; ?>', {
                    onSuccess: function(result) {
                        console.log('Payment success:', result);
                        // Redirect to finish page
                        window.location.href = 'finish_payment.php?order_id=' + encodeURIComponent('<?php echo $order_id; ?>') + 
                                             '&transaction_status=' + encodeURIComponent(result.transaction_status || 'success');
                    },
                    onPending: function(result) {
                        alert('Pembayaran pending. Silakan selesaikan pembayaran Anda.');
                    },
                    onError: function(result) {
                        alert('Pembayaran gagal. Silakan coba lagi.');
                    },
                    onClose: function() {
                        alert('Anda menutup popup tanpa menyelesaikan pembayaran');
                    }
                });
            });
        }
        <?php endif; ?>

        // Manual transfer modal
        const manualTransferBtn = document.getElementById('manual-transfer-btn');
        const manualTransferModal = document.getElementById('manualTransferModal');

        manualTransferBtn.addEventListener('click', function() {
            manualTransferModal.classList.remove('hidden');
            manualTransferModal.classList.add('flex');
        });

        function closeManualTransferModal() {
            manualTransferModal.classList.add('hidden');
            manualTransferModal.classList.remove('flex');
        }

        // Handle manual payment form submission
        document.getElementById('manualPaymentForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitBtn = e.target.querySelector('button[type="submit"]');

            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.textContent = 'Uploading...';

            fetch('upload_payment_proof.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Bukti pembayaran berhasil diupload! Admin akan memverifikasi dalam 1x24 jam.');
                    closeManualTransferModal();
                    // Optionally redirect or refresh
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Gagal upload bukti pembayaran'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat upload. Silakan coba lagi.');
            })
            .finally(() => {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Bukti Pembayaran';
            });
        });

        // Close modal when clicking outside
        manualTransferModal.addEventListener('click', function(e) {
            if (e.target === manualTransferModal) {
                closeManualTransferModal();
            }
        });
    </script>
</body>
</html>

<?php
// Don't clear session data until payment is completed
// This will be cleared in finish_payment.php or error_payment.php
?>