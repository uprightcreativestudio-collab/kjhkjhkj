<?php
require_once '../config/database.php';
include 'partials/header.php';

// Handle verification actions
if ($_POST && isset($_POST['action'])) {
    $order_id = $_POST['order_id'];
    $action = $_POST['action'];
    $admin_notes = $_POST['admin_notes'] ?? '';
    
    try {
        $pdo->beginTransaction();
        
        if ($action === 'approve') {
            // Update manual verification record
            $stmt = $pdo->prepare("
                UPDATE manual_payment_verification 
                SET status = 'approved', admin_notes = ?, verified_by = ?, verified_at = NOW() 
                WHERE order_id = ?
            ");
            $stmt->execute([$admin_notes, $_SESSION['admin_id'], $order_id]);
            
            // Update payment status
            $stmt = $pdo->prepare("
                UPDATE payments 
                SET transaction_status = 'paid', payment_type = 'manual_transfer', settlement_time = NOW() 
                WHERE order_id = ?
            ");
            $stmt->execute([$order_id]);
            
            // Update student status
            $stmt = $pdo->prepare("
                UPDATE siswa s 
                JOIN payments p ON s.id = p.student_id 
                SET s.status_pembayaran = 'paid' 
                WHERE p.order_id = ?
            ");
            $stmt->execute([$order_id]);
            
            $success_message = "Pembayaran berhasil disetujui!";
            
        } elseif ($action === 'reject') {
            // Update manual verification record
            $stmt = $pdo->prepare("
                UPDATE manual_payment_verification 
                SET status = 'rejected', admin_notes = ?, verified_by = ?, verified_at = NOW() 
                WHERE order_id = ?
            ");
            $stmt->execute([$admin_notes, $_SESSION['admin_id'], $order_id]);
            
            // Update payment status
            $stmt = $pdo->prepare("
                UPDATE payments 
                SET transaction_status = 'failed' 
                WHERE order_id = ?
            ");
            $stmt->execute([$order_id]);
            
            $success_message = "Pembayaran berhasil ditolak!";
        }
        
        $pdo->commit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = "Error: " . $e->getMessage();
    }
}

// Fetch pending manual payments
$pending_payments = $pdo->query("
    SELECT mpv.*, p.gross_amount, s.nama_lengkap, s.email, s.telepon, p.created_at as payment_created
    FROM manual_payment_verification mpv
    JOIN payments p ON mpv.order_id = p.order_id
    JOIN siswa s ON p.student_id = s.id
    WHERE mpv.status = 'pending'
    ORDER BY mpv.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch verified payments (recent 50)
$verified_payments = $pdo->query("
    SELECT mpv.*, p.gross_amount, s.nama_lengkap, s.email, a.username as verified_by_name
    FROM manual_payment_verification mpv
    JOIN payments p ON mpv.order_id = p.order_id
    JOIN siswa s ON p.student_id = s.id
    LEFT JOIN admin a ON mpv.verified_by = a.id
    WHERE mpv.status IN ('approved', 'rejected')
    ORDER BY mpv.verified_at DESC
    LIMIT 50
")->fetchAll(PDO::FETCH_ASSOC);
?>

<title>Verifikasi Pembayaran Manual</title>
</head>
<body class="bg-gray-100">
    <div class="flex">
        <?php include 'partials/sidebar.php'; ?>
        
        <main class="flex-1 ml-64 p-8">
            <div class="max-w-7xl mx-auto">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Verifikasi Pembayaran Manual</h1>
                    <p class="text-gray-600">Kelola verifikasi bukti transfer manual dari siswa</p>
                </div>

                <?php if (isset($success_message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <?php echo $success_message; ?>
                </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?php echo $error_message; ?>
                </div>
                <?php endif; ?>

                <!-- Pending Payments -->
                <div class="bg-white rounded-lg shadow-md mb-8">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">
                            Pembayaran Menunggu Verifikasi 
                            <span class="bg-red-100 text-red-800 text-sm font-medium px-2.5 py-0.5 rounded-full ml-2">
                                <?php echo count($pending_payments); ?>
                            </span>
                        </h2>
                    </div>
                    
                    <?php if (empty($pending_payments)): ?>
                    <div class="p-6 text-center text-gray-500">
                        <p>Tidak ada pembayaran yang menunggu verifikasi</p>
                    </div>
                    <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Siswa</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bukti</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catatan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Upload</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($pending_payments as $payment): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div>
                                            <div class="font-medium text-gray-900"><?php echo htmlspecialchars($payment['nama_lengkap']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($payment['email']); ?></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-mono text-gray-900">
                                        <?php echo htmlspecialchars($payment['order_id']); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                        Rp <?php echo number_format($payment['gross_amount'], 0, ',', '.'); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="../<?php echo htmlspecialchars($payment['payment_proof_path']); ?>" 
                                           target="_blank" 
                                           class="text-blue-600 hover:text-blue-800 underline">
                                            Lihat Bukti
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 max-w-xs">
                                        <?php echo htmlspecialchars($payment['notes'] ?: '-'); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?php echo date('d/m/Y H:i', strtotime($payment['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex space-x-2">
                                            <button onclick="showVerificationModal('<?php echo $payment['order_id']; ?>', 'approve')"
                                                    class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600">
                                                Setujui
                                            </button>
                                            <button onclick="showVerificationModal('<?php echo $payment['order_id']; ?>', 'reject')"
                                                    class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600">
                                                Tolak
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Verified Payments History -->
                <div class="bg-white rounded-lg shadow-md">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">Riwayat Verifikasi</h2>
                    </div>
                    
                    <?php if (empty($verified_payments)): ?>
                    <div class="p-6 text-center text-gray-500">
                        <p>Belum ada riwayat verifikasi</p>
                    </div>
                    <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Siswa</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Verifikator</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($verified_payments as $payment): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($payment['nama_lengkap']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($payment['email']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-mono text-gray-900">
                                        <?php echo htmlspecialchars($payment['order_id']); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                        Rp <?php echo number_format($payment['gross_amount'], 0, ',', '.'); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($payment['status'] === 'approved'): ?>
                                            <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Disetujui</span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Ditolak</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?php echo htmlspecialchars($payment['verified_by_name']); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?php echo date('d/m/Y H:i', strtotime($payment['verified_at'])); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Verification Modal -->
    <div id="verificationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Verifikasi Pembayaran</h3>
            </div>
            <form method="POST">
                <div class="px-6 py-4">
                    <input type="hidden" name="order_id" id="modalOrderId">
                    <input type="hidden" name="action" id="modalAction">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Admin</label>
                        <textarea name="admin_notes" rows="3" placeholder="Tambahkan catatan untuk siswa..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-2">
                    <button type="button" onclick="closeVerificationModal()" 
                            class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Batal
                    </button>
                    <button type="submit" id="modalSubmitBtn"
                            class="px-4 py-2 rounded text-white">
                        Konfirmasi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showVerificationModal(orderId, action) {
            document.getElementById('modalOrderId').value = orderId;
            document.getElementById('modalAction').value = action;
            
            const modal = document.getElementById('verificationModal');
            const title = document.getElementById('modalTitle');
            const submitBtn = document.getElementById('modalSubmitBtn');
            
            if (action === 'approve') {
                title.textContent = 'Setujui Pembayaran';
                submitBtn.textContent = 'Setujui';
                submitBtn.className = 'px-4 py-2 rounded text-white bg-green-600 hover:bg-green-700';
            } else {
                title.textContent = 'Tolak Pembayaran';
                submitBtn.textContent = 'Tolak';
                submitBtn.className = 'px-4 py-2 rounded text-white bg-red-600 hover:bg-red-700';
            }
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeVerificationModal() {
            document.getElementById('verificationModal').classList.add('hidden');
            document.getElementById('verificationModal').classList.remove('flex');
        }
    </script>

    <?php include 'partials/footer.php'; ?>
</body>
</html>
