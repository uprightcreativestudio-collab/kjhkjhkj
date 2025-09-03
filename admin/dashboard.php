
<?php
$page_title = 'Dashboard';
include 'partials/header.php';

// Fetching stats
$total_siswa = $pdo->query("SELECT COUNT(*) FROM siswa")->fetchColumn();
$total_jadwal = $pdo->query("SELECT COUNT(*) FROM jadwal")->fetchColumn();
$tagihan_belum_lunas = $pdo->query("SELECT COUNT(*) FROM tagihan WHERE status = 'Belum Lunas'")->fetchColumn();
$pendaftar_baru = $pdo->query("SELECT COUNT(*) FROM siswa WHERE tanggal_pendaftaran >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();

// Total pendapatan dari tagihan yang lunas + payments yang success
$pendapatan_tagihan = $pdo->query("SELECT COALESCE(SUM(jumlah), 0) FROM tagihan WHERE status = 'Lunas'")->fetchColumn();
$pendapatan_midtrans = $pdo->query("SELECT COALESCE(SUM(gross_amount), 0) FROM payments WHERE transaction_status = 'paid'")->fetchColumn();
$total_pendapatan = $pendapatan_tagihan + $pendapatan_midtrans;

// Pending payments yang perlu approval
$pending_manual_payments = $pdo->query("SELECT COUNT(*) FROM payments WHERE transaction_status = 'pending' AND payment_type IS NULL")->fetchColumn();

// Recent students
$recent_siswa = $pdo->query("SELECT nama_lengkap, email, tanggal_mulai FROM siswa ORDER BY tanggal_pendaftaran DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Monthly registration stats for chart
$monthly_stats = $pdo->query("
    SELECT 
        MONTH(tanggal_pendaftaran) as bulan,
        MONTHNAME(tanggal_pendaftaran) as nama_bulan,
        COUNT(*) as total
    FROM siswa 
    WHERE YEAR(tanggal_pendaftaran) = YEAR(CURDATE())
    GROUP BY MONTH(tanggal_pendaftaran), MONTHNAME(tanggal_pendaftaran)
    ORDER BY bulan
")->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="flex-1 p-6 space-y-8">
    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Students Card -->
        <div class="card stats-card p-6 bg-gradient-to-br from-blue-500 to-blue-600 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 transform rotate-12 translate-x-6 -translate-y-6">
                <div class="w-full h-full bg-white bg-opacity-20 rounded-2xl"></div>
            </div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-users text-2xl"></i>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold"><?php echo $total_siswa; ?></div>
                        <div class="text-blue-100 text-sm">Total Students</div>
                    </div>
                </div>
                <div class="flex items-center text-blue-100 text-sm">
                    <i class="fas fa-arrow-up mr-1"></i>
                    <span>Active students enrolled</span>
                </div>
            </div>
        </div>

        <!-- Revenue Card -->
        <div class="card stats-card p-6 bg-gradient-to-br from-green-500 to-green-600 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 transform rotate-12 translate-x-6 -translate-y-6">
                <div class="w-full h-full bg-white bg-opacity-20 rounded-2xl"></div>
            </div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-money-bill-wave text-2xl"></i>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold">Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></div>
                        <div class="text-green-100 text-sm">Total Revenue</div>
                    </div>
                </div>
                <div class="flex items-center text-green-100 text-sm">
                    <i class="fas fa-chart-line mr-1"></i>
                    <span>Manual + Midtrans payments</span>
                </div>
            </div>
        </div>

        <!-- Unpaid Bills Card -->
        <div class="card stats-card p-6 bg-gradient-to-br from-red-500 to-red-600 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 transform rotate-12 translate-x-6 -translate-y-6">
                <div class="w-full h-full bg-white bg-opacity-20 rounded-2xl"></div>
            </div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-2xl"></i>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold"><?php echo $tagihan_belum_lunas; ?></div>
                        <div class="text-red-100 text-sm">Unpaid Bills</div>
                    </div>
                </div>
                <div class="flex items-center text-red-100 text-sm">
                    <i class="fas fa-clock mr-1"></i>
                    <span>Need attention</span>
                </div>
            </div>
        </div>

        <!-- New Registrations Card -->
        <div class="card stats-card p-6 bg-gradient-to-br from-purple-500 to-purple-600 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 transform rotate-12 translate-x-6 -translate-y-6">
                <div class="w-full h-full bg-white bg-opacity-20 rounded-2xl"></div>
            </div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-user-plus text-2xl"></i>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold"><?php echo $pendaftar_baru; ?></div>
                        <div class="text-purple-100 text-sm">New This Week</div>
                    </div>
                </div>
                <div class="flex items-center text-purple-100 text-sm">
                    <i class="fas fa-calendar mr-1"></i>
                    <span>Last 7 days</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Students -->
        <div class="lg:col-span-2 card p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-users mr-3 text-indigo-600"></i>
                    Recent Students
                </h3>
                <a href="siswa.php" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium flex items-center">
                    View All <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="space-y-4">
                <?php if (empty($recent_siswa)): ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-users text-4xl mb-4 opacity-50"></i>
                        <p>No students registered yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recent_siswa as $siswa): ?>
                    <div class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                            <?php echo strtoupper(substr($siswa['nama_lengkap'], 0, 1)); ?>
                        </div>
                        <div class="ml-4 flex-1">
                            <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($siswa['nama_lengkap']); ?></h4>
                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($siswa['email']); ?></p>
                        </div>
                        <div class="text-sm text-gray-400">
                            <?php echo $siswa['tanggal_mulai'] ? date('d M Y', strtotime($siswa['tanggal_mulai'])) : 'Not set'; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-bolt mr-3 text-yellow-500"></i>
                Quick Actions
            </h3>
            <div class="space-y-3">
                <a href="siswa_form.php" class="flex items-center p-3 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors">
                    <i class="fas fa-plus w-5 h-5 mr-3"></i>
                    <span class="font-medium">Add New Student</span>
                </a>
                <a href="jadwal_form.php" class="flex items-center p-3 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition-colors">
                    <i class="fas fa-calendar-plus w-5 h-5 mr-3"></i>
                    <span class="font-medium">Create Schedule</span>
                </a>
                <a href="tagihan.php" class="flex items-center p-3 bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100 transition-colors">
                    <i class="fas fa-file-invoice w-5 h-5 mr-3"></i>
                    <span class="font-medium">Manage Billing</span>
                </a>
                <a href="keuangan.php" class="flex items-center p-3 bg-orange-50 text-orange-700 rounded-lg hover:bg-orange-100 transition-colors">
                    <i class="fas fa-chart-pie w-5 h-5 mr-3"></i>
                    <span class="font-medium">Financial Reports</span>
                </a>
                <a href="kirim_notifikasi.php" class="flex items-center p-3 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition-colors">
                    <i class="fas fa-bell w-5 h-5 mr-3"></i>
                    <span class="font-medium">Send Notification</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Pending Manual Payments Alert -->
    <?php if ($pending_manual_payments > 0): ?>
    <div class="card bg-gradient-to-r from-yellow-400 to-orange-500 text-white p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-3xl mr-4"></i>
                <div>
                    <h4 class="text-lg font-semibold">Manual Payments Need Approval</h4>
                    <p>You have <?php echo $pending_manual_payments; ?> manual payment<?php echo $pending_manual_payments > 1 ? 's' : ''; ?> waiting for approval.</p>
                </div>
            </div>
            <a href="keuangan.php" class="bg-white bg-opacity-20 hover:bg-opacity-30 px-6 py-2 rounded-lg font-medium transition-all">
                Review Now
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Pending Bills Alert -->
    <?php if ($tagihan_belum_lunas > 0): ?>
    <div class="card bg-gradient-to-r from-red-400 to-red-500 text-white p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-file-invoice-dollar text-3xl mr-4"></i>
                <div>
                    <h4 class="text-lg font-semibold">Unpaid Invoices</h4>
                    <p>There are <?php echo $tagihan_belum_lunas; ?> unpaid invoice<?php echo $tagihan_belum_lunas > 1 ? 's' : ''; ?> that need attention.</p>
                </div>
            </div>
            <a href="tagihan.php" class="bg-white bg-opacity-20 hover:bg-opacity-30 px-6 py-2 rounded-lg font-medium transition-all">
                Review Bills
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Monthly Registration Chart -->
    <?php if (!empty($monthly_stats)): ?>
    <div class="card p-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
            <i class="fas fa-chart-bar mr-3 text-green-600"></i>
            Monthly Registration Trends (<?php echo date('Y'); ?>)
        </h3>
        <div class="h-64">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>
    <?php endif; ?>
</main>

<?php if (!empty($monthly_stats)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [<?php echo implode(',', array_map(function($item) { return "'" . $item['nama_bulan'] . "'"; }, $monthly_stats)); ?>],
            datasets: [{
                label: 'New Students',
                data: [<?php echo implode(',', array_column($monthly_stats, 'total')); ?>],
                backgroundColor: 'rgba(59, 130, 246, 0.5)',
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>
<?php endif; ?>

<?php include 'partials/footer.php'; ?>
