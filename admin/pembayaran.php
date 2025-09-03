<?php
$page_title = 'Manajemen Pembayaran';
include 'partials/header.php';

// Fetch all payments
$payments = $pdo->query("
    SELECT p.*, s.nama_lengkap, pc.nama_promo
    FROM payments p
    JOIN siswa s ON p.student_id = s.id
    LEFT JOIN promo_codes pc ON p.kode_promo = pc.kode_promo
    ORDER BY p.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<header class="bg-white shadow-sm p-4">
    <h1 class="text-2xl font-semibold text-gray-800">Manajemen Pembayaran Pendaftaran</h1>
</header>

<main class="flex-1 p-6">
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <h3 class="text-lg font-semibold mb-4">Daftar Pembayaran Pendaftaran</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-2 px-4 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                        <th class="py-2 px-4 text-left text-xs font-medium text-gray-500 uppercase">Nama Siswa</th>
                        <th class="py-2 px-4 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                        <th class="py-2 px-4 text-left text-xs font-medium text-gray-500 uppercase">Pajak</th>
                        <th class="py-2 px-4 text-left text-xs font-medium text-gray-500 uppercase">Promo</th>
                        <th class="py-2 px-4 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="py-2 px-4 text-left text-xs font-medium text-gray-500 uppercase">Bukti Manual</th>
                        <th class="py-2 px-4 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="py-2 px-4 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="8" class="py-3 px-4 text-center text-gray-500">Belum ada pembayaran.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td class="py-3 px-4 whitespace-nowrap font-mono text-sm"><?php echo htmlspecialchars($payment['order_id']); ?></td>
                            <td class="py-3 px-4 whitespace-nowrap"><?php echo htmlspecialchars($payment['nama_lengkap']); ?></td>
                            <td class="py-3 px-4 whitespace-nowrap">Rp <?php echo number_format($payment['gross_amount'], 0, ',', '.'); ?></td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="text-xs text-gray-600">
                                    Rp <?php echo number_format(($payment['pajak_kursus'] ?? 0) + ($payment['pajak_pendaftaran'] ?? 0), 0, ',', '.'); ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <?php if ($payment['nama_promo']): ?>
                                    <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full"><?php echo htmlspecialchars($payment['nama_promo']); ?></span>
                                <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php 
                                    switch($payment['transaction_status']) {
                                        case 'settlement':
                                        case 'capture':
                                        case 'paid':
                                            echo 'bg-green-100 text-green-800';
                                            break;
                                        case 'pending':
                                            echo 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'failed':
                                        case 'expired':
                                            echo 'bg-red-100 text-red-800';
                                            break;
                                        default:
                                            echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?php echo ucfirst($payment['transaction_status']); ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <?php if ($payment['status_manual_upload'] == 'approved'): ?>
                                    <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Disetujui</span>
                                <?php elseif ($payment['status_manual_upload'] == 'pending' && $payment['bukti_manual']): ?>
                                    <div class="flex space-x-1">
                                        <a href="../<?php echo $payment['bukti_manual']; ?>" target="_blank" class="px-2 py-1 text-xs bg-orange-100 text-orange-800 rounded-full hover:bg-orange-200">
                                            Lihat Bukti
                                        </a>
                                        <button onclick="approvePayment('<?php echo $payment['order_id']; ?>')" class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full hover:bg-green-200">
                                            Setujui
                                        </button>
                                        <button onclick="rejectPayment('<?php echo $payment['order_id']; ?>')" class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full hover:bg-red-200">
                                            Tolak
                                        </button>
                                    </div>
                                <?php elseif ($payment['status_manual_upload'] == 'rejected'): ?>
                                    <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">Ditolak</span>
                                <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap"><?php echo date('d M Y H:i', strtotime($payment['created_at'])); ?></td>
                            <td class="py-3 px-4 whitespace-nowrap text-sm font-medium">
                                <a href="invoice.php?type=payment&id=<?php echo $payment['id']; ?>" class="text-red-600 hover:text-red-900" title="Download Invoice" target="_blank">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section for Manual Payment Upload -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold mb-4">Upload Bukti Pembayaran Manual</h3>
        <form action="upload_manual_payment.php" method="POST" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="order_id_manual" class="block text-sm font-medium text-gray-700">Order ID</label>
                    <input type="text" name="order_id" id="order_id_manual" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>
                <div>
                    <label for="amount_manual" class="block text-sm font-medium text-gray-700">Jumlah Pembayaran</label>
                    <input type="number" name="amount" id="amount_manual" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>
            </div>
            <div class="mb-4">
                <label for=" bukti_pembayaran" class="block text-sm font-medium text-gray-700">Bukti Pembayaran</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4h-8m0 0v8m0-8v8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <div class="flex text-sm text-gray-600">
                            <label for="bukti_pembayaran" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                <span>Upload File</span>
                                <input id="bukti_pembayaran" name="bukti_pembayaran" type="file" class="sr-only" required>
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500">PNG, JPG, PDF up to 10MB</p>
                    </div>
                </div>
            </div>
             <div class="mb-4">
                <label for="whatsapp_number" class="block text-sm font-medium text-gray-700">Nomor WhatsApp Admin</label>
                <input type="text" name="whatsapp_number" id="whatsapp_number" value="+6281234567890" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" readonly>
            </div>
            <div class="text-right">
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Kirim Bukti
                </button>
            </div>
        </form>
    </div>
</main>

<script>
function approvePayment(orderId) {
    if(confirm('Apakah Anda yakin ingin menyetujui pembayaran manual ini?')) {
        fetch('api_approve_payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'approve',
                order_id: orderId
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert('Pembayaran berhasil disetujui!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat memproses permintaan');
        });
    }
}

function rejectPayment(orderId) {
    if(confirm('Apakah Anda yakin ingin menolak pembayaran manual ini?')) {
        fetch('api_approve_payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'reject',
                order_id: orderId
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert('Pembayaran berhasil ditolak!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat memproses permintaan');
        });
    }
}
</script>

<?php include 'partials/footer.php'; ?>