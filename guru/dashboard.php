<?php
include 'partials/header.php';

$scanned_student_id = $_SESSION['scanned_student_id'] ?? null;
$scanned_student_name = $_SESSION['scanned_student_name'] ?? null;
$scan_success_message = $_SESSION['scan_success_message'] ?? null;

// Clear the message after displaying it
unset($_SESSION['scan_success_message']);

// Get some stats for the dashboard
$stmt = $pdo->prepare("SELECT COUNT(*) FROM siswa WHERE status_pembayaran = 'paid'");
$stmt->execute();
$total_siswa_aktif = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM ebooks");
$stmt->execute();
$total_ebooks = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM jadwal WHERE is_booked = 1");
$stmt->execute();
$jadwal_terisi = $stmt->fetchColumn();
?>
<title>Dashboard Guru</title>

<?php if ($scan_success_message): ?>
    <div class="mx-4 glass-effect border-l-4 border-green-500 text-green-700 p-4 rounded-2xl mb-6 card-hover animate-fade-in" role="alert">
        <div class="flex">
            <div class="py-1">
                <div class="w-8 h-8 bg-green-500/20 rounded-xl flex items-center justify-center mr-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                </div>
            </div>
            <div>
                <p class="font-bold text-green-800">Sukses!</p>
                <p class="text-sm text-green-700"><?php echo htmlspecialchars($scan_success_message); ?></p>
            </div>
        </div>
    </div>
<?php endif; ?>



<!-- Main Action Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 px-4">
    <!-- QR Scan Card -->
    <div class="glass-effect p-6 rounded-2xl shadow-lg card-hover animate-fade-in" style="animation-delay: 0.3s;">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-pink-dark">Absensi Siswa</h2>
            <div class="w-12 h-12 bg-gradient-to-br from-pink-accent to-pink-dark rounded-2xl flex items-center justify-center shadow-lg">
                <i data-lucide="qr-code" class="w-6 h-6 text-cream"></i>
            </div>
        </div>
        <p class="text-pink-dark/70 mb-6 text-sm">Gunakan kamera untuk memindai QR code siswa dan mencatat kehadiran.</p>
        <a href="scan.php" class="block w-full text-center bg-gradient-to-r from-pink-accent to-pink-dark text-cream font-semibold py-3 px-4 rounded-xl hover:shadow-lg transition-all duration-300 transform hover:scale-105">
            <div class="flex items-center justify-center space-x-2">
                <i data-lucide="camera" class="w-5 h-5"></i>
                <span>Mulai Pindai QR</span>
            </div>
        </a>
    </div>

    <!-- Streaming Card -->
    <div class="glass-effect p-6 rounded-2xl shadow-lg card-hover animate-fade-in" style="animation-delay: 0.4s;">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-pink-dark">Upload Gallery</h2>
            <div class="w-12 h-12 bg-gradient-to-br <?php echo $scanned_student_id ? 'from-blue-soft to-blue-500' : 'from-gray-300 to-gray-400'; ?> rounded-2xl flex items-center justify-center shadow-lg">
                <i data-lucide="upload" class="w-6 h-6 text-cream"></i>
            </div>
        </div>
        
        <?php if ($scanned_student_id): ?>
            <div class="bg-gradient-to-r from-green-50 to-blue-50 p-4 rounded-xl border border-green-200 mb-4">
                <p class="text-green-700 font-semibold text-sm mb-1">✓ Ready to Upload</p>
                <p class="text-green-600 text-xs">Siap upload materi untuk <?php echo htmlspecialchars($scanned_student_name); ?>.</p>
            </div>
            <a href="stream.php" class="block w-full text-center bg-gradient-to-r from-blue-soft to-blue-500 text-cream font-semibold py-3 px-4 rounded-xl hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                <div class="flex items-center justify-center space-x-2">
                    <i data-lucide="upload" class="w-5 h-5"></i>
                    <span>Upload Gallery Sekarang</span>
                </div>
            </a>
        <?php else: ?>
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 mb-4">
                <p class="text-gray-600 text-sm">Pindai QR siswa terlebih dahulu untuk mengaktifkan upload gallery.</p>
            </div>
            <button disabled class="block w-full text-center bg-gray-300 text-gray-500 font-semibold py-3 px-4 rounded-xl cursor-not-allowed">
                <div class="flex items-center justify-center space-x-2">
                    <i data-lucide="upload-x" class="w-5 h-5"></i>
                    <span>Upload Gallery</span>
                </div>
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Today's Schedule -->
<div class="mt-4 mx-4 glass-effect p-6 rounded-2xl shadow-lg card-hover animate-fade-in" style="animation-delay: 0.5s;">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-pink-dark">Jadwal Hari Ini</h2>
        <span class="text-sm font-medium text-pink-dark/70 bg-pink-light/20 px-3 py-1 rounded-xl"><?php echo date('l, d M Y'); ?></span>
    </div>
    
    <?php
    $hari_ini = date('l'); // Get current day in English
    // Convert to Indonesian
    $hari_indonesia = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin', 
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    ];
    $hari_indo = $hari_indonesia[$hari_ini];
    
    $stmt = $pdo->prepare("
        SELECT j.*, s.nama_lengkap as nama_siswa, s.status_pembayaran
        FROM jadwal j 
        INNER JOIN siswa s ON j.id = s.jadwal_id 
        WHERE j.hari = ? AND s.status_pembayaran = 'paid'
        ORDER BY j.jam_mulai
    ");
    $stmt->execute([$hari_indo]);
    $jadwal_hari_ini = $stmt->fetchAll();
    ?>
    
    <?php if ($jadwal_hari_ini): ?>
        <div class="space-y-3">
            <?php foreach ($jadwal_hari_ini as $jadwal): ?>
                <div class="flex items-center bg-gradient-to-r from-pink-accent/5 to-pink-light/5 p-4 rounded-2xl border border-pink-light/30">
                    <div class="w-16 h-16 bg-gradient-to-br from-pink-accent to-pink-dark rounded-2xl flex items-center justify-center mr-4 shadow-lg">
                        <i data-lucide="clock" class="w-6 h-6 text-cream"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-bold text-pink-dark"><?php echo date('H:i', strtotime($jadwal['jam_mulai'])); ?> - <?php echo date('H:i', strtotime($jadwal['jam_selesai'])); ?></p>
                        <?php if ($jadwal['nama_siswa']): ?>
                            <p class="text-pink-dark/70 text-sm">Dengan: <?php echo htmlspecialchars($jadwal['nama_siswa']); ?></p>
                        <?php else: ?>
                            <p class="text-pink-dark/50 text-sm">Slot tersedia</p>
                        <?php endif; ?>
                    </div>
                    <div class="w-3 h-3 rounded-full <?php echo $jadwal['nama_siswa'] ? 'bg-blue-soft' : 'bg-gray-300'; ?>"></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-12">
            <div class="w-20 h-20 bg-gradient-to-br from-pink-light to-blue-soft rounded-full flex items-center justify-center mx-auto mb-4 opacity-50">
                <i data-lucide="coffee" class="w-10 h-10 text-pink-dark"></i>
            </div>
            <p class="text-pink-dark/60 font-medium">Tidak ada jadwal hari ini</p>
            <p class="text-pink-dark/40 text-sm">Selamat beristirahat!</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'partials/footer.php'; ?>