<?php
require '../config/database.php';
include 'partials/header.php';

$message = '';
$message_type = '';

// Handle leave request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_ijin'])) {
    $tanggal_ijin = $_POST['tanggal_ijin'];
    $alasan = $_POST['alasan'];
    $siswa_id = $user['id'];

    // Check for existing request on the same date
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM ijin WHERE siswa_id = ? AND tanggal_ijin = ?");
    $stmt_check->execute([$siswa_id, $tanggal_ijin]);
    if ($stmt_check->fetchColumn() > 0) {
        $message = 'Anda sudah mengajukan ijin untuk tanggal ini.';
        $message_type = 'error';
    } else {
        $stmt = $pdo->prepare("INSERT INTO ijin (siswa_id, tanggal_ijin, alasan) VALUES (?, ?, ?)");
        if ($stmt->execute([$siswa_id, $tanggal_ijin, $alasan])) {
            $message = 'Permintaan ijin berhasil diajukan.';
            $message_type = 'success';
        } else {
            $message = 'Gagal mengajukan ijin. Silakan coba lagi.';
            $message_type = 'error';
        }
    }
}


$qr_identifier = $user['qr_code_identifier'];
$qr_code_url = '';
if ($qr_identifier) {
    $qr_code_url = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($qr_identifier);
}

// Fetch user's leave history
$stmt_ijin = $pdo->prepare("SELECT * FROM ijin WHERE siswa_id = ? ORDER BY tanggal_pengajuan DESC");
$stmt_ijin->execute([$user['id']]);
$ijin_history = $stmt_ijin->fetchAll(PDO::FETCH_ASSOC);
?>
<title>QR Attendance & Ijin</title>
</head>

<body class="bg-gray-100">
    <header class="relative bg-gradient-to-br from-pink-accent via-pink-dark to-pink-light rounded-b-[35px] shadow-2xl p-6 text-cream z-10 mb-5 animate-slide-in">
        <div class="absolute inset-0 bg-gradient-to-br from-pink-accent/90 to-pink-dark/90 rounded-b-[35px] backdrop-blur-sm"></div>
        <div class="absolute top-0 right-0 w-32 h-32 bg-yellow-bright/20 rounded-full -translate-y-16 translate-x-16 animate-pulse-soft"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-blue-soft/20 rounded-full translate-y-12 -translate-x-12 animate-float"></div>

        <div class="relative flex items-center justify-between">
            <div class="flex items-center">
            <a href="profile.php" class="group">
                    <div class="relative">
                    <img class="w-16 h-16 rounded-full border-2 border-white-400 object-cover" src="<?php echo htmlspecialchars($user['foto_profil'] ? '../' . $user['foto_profil'] : '../avaaset/logo-ava.png'); ?>" alt="Profile Picture">      
                    </div>
                </a>
                <div class="ml-4">
                    <h1 class="font-bold text-xl text-cream drop-shadow-sm"><?php echo htmlspecialchars($user['nama_lengkap']); ?></h1>
                    <p class="text-sm text-cream/80 font-medium"> <?php echo htmlspecialchars($user['qr_code_identifier']); ?></p>
                </div>
            </div>
            <a href="notifikasi.php" class="relative p-3 rounded-2xl hover:bg-cream/10 transition-all duration-300 group">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-cream group-hover:scale-110 transition-transform duration-300">
                    <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                    <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
                </svg>
                <div class="absolute top-2 right-2 w-3 h-3 bg-yellow-bright rounded-full animate-bounce-soft"></div>
            </a>
        </div>
    </header>
    <div class="container mx-auto p-4 pb-24">
        <div class="max-w-md mx-auto">
            <!-- QR Code Section -->
            <div class="w-full text-center">
            
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <?php if ($qr_code_url): ?>
                        <img src="<?php echo $qr_code_url; ?>" alt="Your QR Code" class="w-full h-auto rounded-md max-w-xs mx-auto">
                        <p class="mt-4 font-mono text-sm text-gray-600 break-all"><?php echo htmlspecialchars($qr_identifier); ?></p>
                    <?php else: ?>
                        <div class="text-center text-gray-500 py-10">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield-alert mx-auto h-12 w-12 text-red-400 mb-4">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                <path d="M12 8v4" />
                                <path d="M12 16h.01" />
                            </svg>
                            <p>Your QR Code is not available. Please contact the administrator.</p>
                        </div>
                    <?php endif; ?>
                </div>
                <p class="mt-4 text-xs text-gray-400">This QR code is unique to your account.</p>
            </div>

            <!-- My Schedule -->
            <div class="w-full mt-6">
                <div class="bg-white rounded-lg shadow-md p-4">
                    <h3 class="text-md font-bold text-gray-700 mb-2 text-left">My Schedule</h3>
                    <?php if ($user['hari']): ?>
                        <div class="bg-gray-50 p-3 rounded-lg text-left">
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($user['hari']); ?></p>
                            <p class="text-sm text-gray-600"><?php echo date('H:i', strtotime($user['jam_mulai'])); ?> - <?php echo date('H:i', strtotime($user['jam_selesai'])); ?></p>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-gray-500 text-center py-4">Your schedule is not set yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Leave Request Form -->
            <div class="w-full mt-6">
                <div class="bg-white rounded-lg shadow-md p-4">
                    <h3 class="text-md font-bold text-gray-700 mb-3">Ajukan Ijin Tidak Masuk</h3>
                    <?php if ($message): ?>
                        <div class="p-3 mb-4 text-sm rounded-lg <?php echo $message_type == 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST" action="qr_attendance.php">
                        <div class="mb-3">
                            <label for="tanggal_ijin" class="block text-sm font-medium text-gray-600">Tanggal Ijin</label>
                            <input type="date" name="tanggal_ijin" id="tanggal_ijin" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md" min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="alasan" class="block text-sm font-medium text-gray-600">Alasan</label>
                            <textarea name="alasan" id="alasan" rows="3" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md"></textarea>
                        </div>
                        <button type="submit" name="submit_ijin" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700">
                            Kirim Permintaan Ijin
                        </button>
                    </form>
                </div>
            </div>

            <!-- Leave History -->
            <div class="w-full mt-6">
                <div class="bg-white rounded-lg shadow-md p-4">
                    <h3 class="text-md font-bold text-gray-700 mb-3">Riwayat Ijin Anda</h3>
                    <div class="space-y-3">
                        <?php if (empty($ijin_history)): ?>
                            <p class="text-sm text-gray-500 text-center py-4">Anda belum pernah mengajukan ijin.</p>
                        <?php else: ?>
                            <?php foreach ($ijin_history as $ijin): ?>
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-semibold text-gray-800"><?php echo date('d F Y', strtotime($ijin['tanggal_ijin'])); ?></p>
                                            <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($ijin['alasan']); ?></p>
                                        </div>
                                        <?php
                                        $status_color = 'bg-yellow-200 text-yellow-800';
                                        if ($ijin['status'] == 'disetujui') $status_color = 'bg-green-200 text-green-800';
                                        if ($ijin['status'] == 'ditolak') $status_color = 'bg-red-200 text-red-800';
                                        ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $status_color; ?>">
                                            <?php echo ucfirst($ijin['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'partials/footer.php'; ?>
</body>

</html>