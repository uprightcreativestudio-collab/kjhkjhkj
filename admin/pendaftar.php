
<?php
$page_title = 'Pendaftar Baru';
include 'partials/header.php';

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $siswa_id = $_POST['siswa_id'];
    $action = $_POST['action'];
    
    if ($action === 'approve') {
        // Update status pembayaran
        $stmt = $pdo->prepare("UPDATE siswa SET status_pembayaran = 'paid' WHERE id = ?");
        $stmt->execute([$siswa_id]);
        
        // Update payment status
        $stmt = $pdo->prepare("UPDATE payments SET transaction_status = 'settlement' WHERE student_id = ?");
        $stmt->execute([$siswa_id]);
        
        $_SESSION['flash_message'] = 'Pendaftar berhasil disetujui.';
    } elseif ($action === 'reject') {
        // Update status pembayaran
        $stmt = $pdo->prepare("UPDATE siswa SET status_pembayaran = 'failed' WHERE id = ?");
        $stmt->execute([$siswa_id]);
        
        // Update payment status
        $stmt = $pdo->prepare("UPDATE payments SET transaction_status = 'failed' WHERE student_id = ?");
        $stmt->execute([$siswa_id]);
        
        $_SESSION['flash_message'] = 'Pendaftar berhasil ditolak.';
    }
    
    header('Location: pendaftar.php');
    exit;
}

// Get new registrations
$stmt = $pdo->query("
    SELECT s.*, p.order_id, p.gross_amount, p.transaction_status, p.snap_token, p.created_at as payment_date
    FROM siswa s 
    LEFT JOIN payments p ON s.id = p.student_id 
    WHERE s.tanggal_pendaftaran >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ORDER BY s.tanggal_pendaftaran DESC
");
$pendaftar_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<header class="bg-white shadow-sm p-4">
    <h1 class="text-2xl font-semibold text-gray-800"><?php echo $page_title; ?></h1>
    <p class="text-gray-600">Kelola pendaftar baru dalam 30 hari terakhir</p>
</header>

<main class="flex-1 p-6 bg-gray-50">
    <?php if (empty($pendaftar_list)): ?>
        <div class="text-center py-10">
            <div class="bg-white rounded-lg shadow p-8">
                <i class="fas fa-user-plus text-gray-400 text-6xl mb-4"></i>
                <p class="text-gray-500 text-lg">Belum ada pendaftar baru dalam 30 hari terakhir.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Daftar Pendaftar Baru</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Pendaftar
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kontak
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Pembayaran
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tanggal
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($pendaftar_list as $pendaftar): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <img class="h-10 w-10 rounded-full object-cover" 
                                         src="<?php echo htmlspecialchars($pendaftar['foto_profil'] ?? '../uploads/profil/default.png'); ?>" 
                                         alt="Foto">
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($pendaftar['nama_lengkap']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($pendaftar['nama_panggilan'] ?? '-'); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($pendaftar['email']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($pendaftar['telepon']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?php if ($pendaftar['order_id']): ?>
                                        Order: <?php echo htmlspecialchars($pendaftar['order_id']); ?><br>
                                        Total: Rp <?php echo number_format($pendaftar['gross_amount'], 0, ',', '.'); ?>
                                    <?php else: ?>
                                        <span class="text-gray-500">Belum ada pembayaran</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $status = $pendaftar['transaction_status'] ?? $pendaftar['status_pembayaran'];
                                $status_class = '';
                                $status_text = '';
                                
                                switch ($status) {
                                    case 'settlement':
                                    case 'paid':
                                    case 'Lunas':
                                        $status_class = 'bg-green-100 text-green-800';
                                        $status_text = 'Lunas';
                                        break;
                                    case 'pending':
                                        $status_class = 'bg-yellow-100 text-yellow-800';
                                        $status_text = 'Pending';
                                        break;
                                    case 'failed':
                                    case 'expired':
                                        $status_class = 'bg-red-100 text-red-800';
                                        $status_text = 'Gagal';
                                        break;
                                    default:
                                        $status_class = 'bg-gray-100 text-gray-800';
                                        $status_text = 'Belum Bayar';
                                }
                                ?>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo $status_class; ?>">
                                    <?php echo $status_text; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('d/m/Y H:i', strtotime($pendaftar['tanggal_pendaftaran'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <a href="siswa_form.php?id=<?php echo $pendaftar['id']; ?>" 
                                   class="text-indigo-600 hover:text-indigo-900" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <?php if ($status === 'pending' || $status === 'Belum Lunas'): ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="siswa_id" value="<?php echo $pendaftar['id']; ?>">
                                    <button type="submit" name="action" value="approve" 
                                            class="text-green-600 hover:text-green-900" 
                                            title="Setujui" 
                                            onclick="return confirm('Setujui pendaftar ini?')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                
                                <form method="POST" class="inline">
                                    <input type="hidden" name="siswa_id" value="<?php echo $pendaftar['id']; ?>">
                                    <button type="submit" name="action" value="reject" 
                                            class="text-red-600 hover:text-red-900" 
                                            title="Tolak" 
                                            onclick="return confirm('Tolak pendaftar ini?')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                
                                <?php if ($pendaftar['snap_token']): ?>
                                <button onclick="showPaymentDetails('<?php echo $pendaftar['order_id']; ?>', '<?php echo $pendaftar['snap_token']; ?>')" 
                                        class="text-blue-600 hover:text-blue-900" title="Info Pembayaran">
                                    <i class="fas fa-credit-card"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</main>

<!-- Payment Details Modal -->
<div id="paymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Detail Pembayaran</h3>
        </div>
        <div class="px-6 py-4">
            <div id="paymentContent">
                <!-- Payment content will be loaded here -->
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
            <button onclick="closePaymentModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
function showPaymentDetails(orderId, snapToken) {
    document.getElementById('paymentContent').innerHTML = `
        <div class="space-y-3">
            <div>
                <label class="text-sm font-medium text-gray-700">Order ID:</label>
                <p class="text-sm text-gray-900">${orderId}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Snap Token:</label>
                <p class="text-sm text-gray-900 break-all">${snapToken}</p>
            </div>
        </div>
    `;
    document.getElementById('paymentModal').classList.remove('hidden');
    document.getElementById('paymentModal').classList.add('flex');
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.add('hidden');
    document.getElementById('paymentModal').classList.remove('flex');
}
</script>

<?php include 'partials/footer.php'; ?>
