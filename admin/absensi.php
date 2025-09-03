<?php
$page_title = 'Absensi Siswa';
include 'partials/header.php';

$message = '';
$error = '';

// Handle form submission for marking attendance
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['qr_identifier'])) {
    $identifier = $_POST['qr_identifier'];
    
    $stmt = $pdo->prepare("SELECT id, nama_lengkap FROM siswa WHERE qr_code_identifier = ?");
    $stmt->execute([$identifier]);
    $siswa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($siswa) {
        // Check if already attended today
        $stmt_check = $pdo->prepare("SELECT id FROM absensi WHERE siswa_id = ? AND DATE(waktu_absensi) = CURDATE()");
        $stmt_check->execute([$siswa['id']]);
        if ($stmt_check->fetch()) {
            $error = "Siswa <strong>" . htmlspecialchars($siswa['nama_lengkap']) . "</strong> sudah absen hari ini.";
        } else {
            $stmt_insert = $pdo->prepare("INSERT INTO absensi (siswa_id) VALUES (?)");
            $stmt_insert->execute([$siswa['id']]);
            $message = "Absensi untuk <strong>" . htmlspecialchars($siswa['nama_lengkap']) . "</strong> berhasil dicatat.";
        }
    } else {
        $error = "Kode QR tidak valid atau siswa tidak ditemukan.";
    }
}

// Fetch attendance history
$siswa_id_filter = $_GET['siswa_id'] ?? null;
$where_clause = '';
$params = [];
if ($siswa_id_filter) {
    $where_clause = 'WHERE s.id = ?';
    $params[] = $siswa_id_filter;
}

$stmt_history = $pdo->prepare("
    SELECT a.id, s.nama_lengkap, s.foto_profil, a.waktu_absensi 
    FROM absensi a
    JOIN siswa s ON a.siswa_id = s.id
    $where_clause
    ORDER BY a.waktu_absensi DESC
    LIMIT 20
");
$stmt_history->execute($params);
$absensi_list = $stmt_history->fetchAll(PDO::FETCH_ASSOC);

$siswa_info = null;
if ($siswa_id_filter) {
    $stmt_siswa = $pdo->prepare("SELECT nama_lengkap FROM siswa WHERE id = ?");
    $stmt_siswa->execute([$siswa_id_filter]);
    $siswa_info = $stmt_siswa->fetch(PDO::FETCH_ASSOC);
}
?>

<header class="bg-white shadow-sm p-4">
    <h1 class="text-2xl font-semibold text-gray-800">
        <?php echo $siswa_info ? 'Riwayat Absensi: ' . htmlspecialchars($siswa_info['nama_lengkap']) : 'Absensi Siswa'; ?>
    </h1>
</header>

<main class="flex-1 p-6 bg-gray-50">
    <!-- Scan Form -->
    <?php if (!$siswa_id_filter): ?>
    <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md mb-8">
        <h3 class="text-lg font-semibold mb-4 text-center">Scan QR Code Siswa</h3>
        <form method="POST" class="flex">
            <input type="text" name="qr_identifier" placeholder="Masukkan kode dari QR..." class="flex-grow p-2 border border-gray-300 rounded-l-md focus:ring-indigo-500 focus:border-indigo-500" required autofocus>
            <button type="submit" class="bg-indigo-600 text-white py-2 px-4 rounded-r-md hover:bg-indigo-700">
                <i class="fas fa-check mr-2"></i>Catat
            </button>
        </form>
        <?php if ($message): ?>
            <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded" role="alert"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert"><?php echo $error; ?></div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Attendance History -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold mb-4"><?php echo $siswa_info ? 'Detail Kehadiran' : '20 Absensi Terakhir'; ?></h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-2 px-4 text-left text-xs font-medium text-gray-500 uppercase">Siswa</th>
                        <th class="py-2 px-4 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="py-2 px-4 text-left text-xs font-medium text-gray-500 uppercase">Jam</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($absensi_list)): ?>
                        <tr>
                            <td colspan="3" class="py-3 px-4 text-center text-gray-500">Belum ada data absensi.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($absensi_list as $absensi): ?>
                        <tr>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <img src="<?php echo htmlspecialchars($absensi['foto_profil'] ?? '../uploads/profil/default.png'); ?>" class="w-8 h-8 rounded-full object-cover mr-3">
                                    <span><?php echo htmlspecialchars($absensi['nama_lengkap']); ?></span>
                                </div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap"><?php echo date('d M Y', strtotime($absensi['waktu_absensi'])); ?></td>
                            <td class="py-3 px-4 whitespace-nowrap"><?php echo date('H:i:s', strtotime($absensi['waktu_absensi'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include 'partials/footer.php'; ?>
