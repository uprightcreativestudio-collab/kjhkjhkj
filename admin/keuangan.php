
<?php
$page_title = 'Laporan Keuangan';
include 'partials/header.php';

// Fetch financial data from both tagihan and payments
$total_pendapatan_tagihan = $pdo->query("SELECT COALESCE(SUM(jumlah), 0) FROM tagihan WHERE status = 'Lunas'")->fetchColumn();
$total_pendapatan_midtrans = $pdo->query("SELECT COALESCE(SUM(gross_amount), 0) FROM payments WHERE transaction_status IN ('settlement', 'capture', 'paid')")->fetchColumn();
$total_pendapatan = $total_pendapatan_tagihan + $total_pendapatan_midtrans;

$total_tunggakan = $pdo->query("SELECT COALESCE(SUM(jumlah), 0) FROM tagihan WHERE status IN ('Belum Lunas', 'Terlambat')")->fetchColumn();
$total_pending_midtrans = $pdo->query("SELECT COALESCE(SUM(gross_amount), 0) FROM payments WHERE transaction_status = 'pending'")->fetchColumn();
$total_tunggakan_all = $total_tunggakan + $total_pending_midtrans;

$total_transaksi_lunas = $pdo->query("SELECT COUNT(*) FROM tagihan WHERE status = 'Lunas'")->fetchColumn();
$total_transaksi_midtrans = $pdo->query("SELECT COUNT(*) FROM payments WHERE transaction_status IN ('settlement', 'capture', 'paid')")->fetchColumn();
$total_transaksi = $total_transaksi_lunas + $total_transaksi_midtrans;

// Fetch recent transactions from both sources
$recent_transactions = $pdo->query("
    (SELECT 
        'tagihan' as source,
        t.jumlah as amount, 
        t.tanggal_bayar as date, 
        s.nama_lengkap as name,
        t.invoice_kode as reference,
        'Manual' as payment_method,
        t.status
    FROM tagihan t
    JOIN siswa s ON t.siswa_id = s.id
    WHERE t.status = 'Lunas' AND t.tanggal_bayar IS NOT NULL
    )
    UNION ALL
    (SELECT 
        'payments' as source,
        p.gross_amount as amount,
        COALESCE(p.settlement_time, p.transaction_time, p.created_at) as date,
        s.nama_lengkap as name,
        p.order_id as reference,
        COALESCE(p.payment_type, 'Midtrans') as payment_method,
        p.transaction_status as status
    FROM payments p
    JOIN siswa s ON p.student_id = s.id
    WHERE p.transaction_status IN ('settlement', 'capture', 'paid')
    )
    ORDER BY date DESC
    LIMIT 20
")->fetchAll(PDO::FETCH_ASSOC);

// Get payment summary by method
$payment_summary = $pdo->query("
    SELECT 
        COALESCE(payment_type, 'Midtrans') as method,
        COUNT(*) as count,
        SUM(gross_amount) as total
    FROM payments 
    WHERE transaction_status IN ('settlement', 'capture', 'paid')
    GROUP BY payment_type
    
    UNION ALL
    
    SELECT 
        'Manual' as method,
        COUNT(*) as count,
        SUM(jumlah) as total
    FROM tagihan 
    WHERE status = 'Lunas'
")->fetchAll(PDO::FETCH_ASSOC);

// Monthly revenue chart data
$monthly_revenue = $pdo->query("
    SELECT 
        MONTH(tanggal_bayar) as bulan,
        MONTHNAME(tanggal_bayar) as nama_bulan,
        SUM(jumlah) as total
    FROM tagihan 
    WHERE status = 'Lunas' 
        AND YEAR(tanggal_bayar) = YEAR(CURDATE())
        AND tanggal_bayar IS NOT NULL
    GROUP BY MONTH(tanggal_bayar), MONTHNAME(tanggal_bayar)
    
    UNION ALL
    
    SELECT 
        MONTH(COALESCE(settlement_time, transaction_time)) as bulan,
        MONTHNAME(COALESCE(settlement_time, transaction_time)) as nama_bulan,
        SUM(gross_amount) as total
    FROM payments 
    WHERE transaction_status IN ('settlement', 'capture', 'paid')
        AND YEAR(COALESCE(settlement_time, transaction_time)) = YEAR(CURDATE())
    GROUP BY MONTH(COALESCE(settlement_time, transaction_time)), MONTHNAME(COALESCE(settlement_time, transaction_time))
    ORDER BY bulan
")->fetchAll(PDO::FETCH_ASSOC);
?>

<header class="bg-white shadow-sm p-4">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-800">Laporan Keuangan</h1>
        <div class="flex space-x-3">
            <button onclick="exportToCSV()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                <i class="fas fa-download mr-2"></i>Export CSV
            </button>
            <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-print mr-2"></i>Print
            </button>
        </div>
    </div>
</header>

<main class="flex-1 p-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-gradient-to-br from-green-500 to-green-600 text-white p-6 rounded-lg shadow-md">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-xl"></i>
                </div>
            </div>
            <p class="text-green-100 text-sm mb-1">Total Pendapatan</p>
            <p class="text-2xl font-bold">Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></p>
            <p class="text-xs text-green-200 mt-1">Manual + Midtrans</p>
        </div>
        
        <div class="bg-gradient-to-br from-red-500 to-red-600 text-white p-6 rounded-lg shadow-md">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-xl"></i>
                </div>
            </div>
            <p class="text-red-100 text-sm mb-1">Total Tunggakan</p>
            <p class="text-2xl font-bold">Rp <?php echo number_format($total_tunggakan_all, 0, ',', '.'); ?></p>
            <p class="text-xs text-red-200 mt-1">Belum Lunas + Pending</p>
        </div>
        
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-6 rounded-lg shadow-md">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
            </div>
            <p class="text-blue-100 text-sm mb-1">Total Transaksi Sukses</p>
            <p class="text-2xl font-bold"><?php echo number_format($total_transaksi, 0, ',', '.'); ?></p>
            <p class="text-xs text-blue-200 mt-1">Manual + Otomatis</p>
        </div>
        
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white p-6 rounded-lg shadow-md">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-credit-card text-xl"></i>
                </div>
            </div>
            <p class="text-purple-100 text-sm mb-1">Pendapatan Midtrans</p>
            <p class="text-2xl font-bold">Rp <?php echo number_format($total_pendapatan_midtrans, 0, ',', '.'); ?></p>
            <p class="text-xs text-purple-200 mt-1">Gateway Otomatis</p>
        </div>
    </div>

    <!-- Chart Section -->
    <?php if (!empty($monthly_revenue)): ?>
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <h3 class="text-lg font-semibold mb-4 flex items-center">
            <i class="fas fa-chart-area mr-3 text-indigo-600"></i>
            Grafik Pendapatan Bulanan (<?php echo date('Y'); ?>)
        </h3>
        <div class="h-64">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <!-- Payment Methods Summary -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <h3 class="text-lg font-semibold mb-4 flex items-center">
            <i class="fas fa-chart-pie mr-3 text-green-600"></i>
            Ringkasan per Metode Pembayaran
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php 
            $method_totals = [];
            foreach ($payment_summary as $method) {
                $key = $method['method'] ?? 'Unknown';
                if (!isset($method_totals[$key])) {
                    $method_totals[$key] = ['count' => 0, 'total' => 0];
                }
                $method_totals[$key]['count'] += $method['count'];
                $method_totals[$key]['total'] += $method['total'];
            }
            
            foreach ($method_totals as $method => $data): 
                $bg_color = $method == 'Manual' ? 'bg-blue-50 border-blue-200' : 'bg-green-50 border-green-200';
                $text_color = $method == 'Manual' ? 'text-blue-700' : 'text-green-700';
            ?>
            <div class="<?php echo $bg_color; ?> p-4 rounded-lg border">
                <div class="flex items-center justify-between mb-2">
                    <p class="font-semibold <?php echo $text_color; ?>"><?php echo ucfirst($method); ?></p>
                    <i class="fas fa-<?php echo $method == 'Manual' ? 'hand-holding-usd' : 'credit-card'; ?> <?php echo $text_color; ?>"></i>
                </div>
                <p class="text-xl font-bold text-gray-800"><?php echo $data['count']; ?></p>
                <p class="text-sm text-gray-600">Rp <?php echo number_format($data['total'], 0, ',', '.'); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <h3 class="text-lg font-semibold mb-4 flex items-center">
            <i class="fas fa-history mr-3 text-orange-600"></i>
            20 Transaksi Terbaru
        </h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Nama Siswa</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Referensi</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Metode</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($recent_transactions)): ?>
                        <tr>
                            <td colspan="6" class="py-4 px-4 text-center text-gray-500">Belum ada transaksi.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_transactions as $transaksi): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-4 whitespace-nowrap font-medium"><?php echo htmlspecialchars($transaksi['name']); ?></td>
                            <td class="py-3 px-4 whitespace-nowrap font-mono text-sm"><?php echo htmlspecialchars($transaksi['reference']); ?></td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                    <?php echo $transaksi['payment_method'] == 'Manual' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'; ?>">
                                    <?php echo htmlspecialchars($transaksi['payment_method']); ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap font-semibold">Rp <?php echo number_format($transaksi['amount'], 0, ',', '.'); ?></td>
                            <td class="py-3 px-4 whitespace-nowrap text-sm"><?php echo $transaksi['date'] ? date('d M Y H:i', strtotime($transaksi['date'])) : '-'; ?></td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                    <?php echo $transaksi['source'] == 'tagihan' ? 'bg-yellow-100 text-yellow-800' : 'bg-purple-100 text-purple-800'; ?>">
                                    <?php echo $transaksi['source'] == 'tagihan' ? 'Manual' : 'Midtrans'; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Manual Payment Approval Section -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold mb-4 flex items-center">
            <i class="fas fa-user-check mr-3 text-red-600"></i>
            Pembayaran Manual yang Perlu Persetujuan
        </h3>
        <?php
        $pending_manual = $pdo->query("
            SELECT p.order_id, p.gross_amount, s.nama_lengkap, p.created_at
            FROM payments p
            JOIN siswa s ON p.student_id = s.id
            WHERE p.transaction_status = 'pending' AND p.payment_type IS NULL
            ORDER BY p.created_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        
        <?php if (empty($pending_manual)): ?>
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-check-circle text-4xl mb-4 text-green-500"></i>
                <p>Tidak ada pembayaran manual yang perlu persetujuan.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Nama Siswa</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($pending_manual as $payment): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-4 whitespace-nowrap font-mono text-sm"><?php echo htmlspecialchars($payment['order_id']); ?></td>
                            <td class="py-3 px-4 whitespace-nowrap font-medium"><?php echo htmlspecialchars($payment['nama_lengkap']); ?></td>
                            <td class="py-3 px-4 whitespace-nowrap font-semibold">Rp <?php echo number_format($payment['gross_amount'], 0, ',', '.'); ?></td>
                            <td class="py-3 px-4 whitespace-nowrap text-sm"><?php echo date('d M Y H:i', strtotime($payment['created_at'])); ?></td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <button onclick="approvePayment('<?php echo $payment['order_id']; ?>')" 
                                        class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600 transition-colors mr-2">
                                    <i class="fas fa-check mr-1"></i>Setujui
                                </button>
                                <button onclick="rejectPayment('<?php echo $payment['order_id']; ?>')" 
                                        class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600 transition-colors">
                                    <i class="fas fa-times mr-1"></i>Tolak
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php if (!empty($monthly_revenue)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    // Group data by month
    const monthlyData = {};
    <?php foreach($monthly_revenue as $item): ?>
        const month = <?php echo $item['bulan']; ?>;
        const name = '<?php echo $item['nama_bulan']; ?>';
        const amount = <?php echo $item['total']; ?>;
        
        if (!monthlyData[month]) {
            monthlyData[month] = {name: name, total: 0};
        }
        monthlyData[month].total += amount;
    <?php endforeach; ?>
    
    const sortedData = Object.keys(monthlyData).sort().map(key => monthlyData[key]);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: sortedData.map(item => item.name),
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: sortedData.map(item => item.total),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Pendapatan: Rp ' + context.raw.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
});
</script>
<?php endif; ?>

<script>
function approvePayment(orderId) {
    if (confirm('Yakin ingin menyetujui pembayaran ini?')) {
        fetch('api_approve_payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                order_id: orderId,
                action: 'approve'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Pembayaran berhasil disetujui');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    }
}

function rejectPayment(orderId) {
    if (confirm('Yakin ingin menolak pembayaran ini?')) {
        fetch('api_approve_payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                order_id: orderId,
                action: 'reject'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Pembayaran berhasil ditolak');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    }
}

function exportToCSV() {
    const table = document.querySelector('table');
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = [], cols = rows[i].querySelectorAll('td, th');
        for (let j = 0; j < cols.length - 1; j++) { // Exclude action column
            row.push(cols[j].innerText);
        }
        csv.push(row.join(','));
    }
    
    const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
    const downloadLink = document.createElement('a');
    downloadLink.download = 'laporan_keuangan_' + new Date().toISOString().split('T')[0] + '.csv';
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}
</script>

<?php include 'partials/footer.php'; ?>
