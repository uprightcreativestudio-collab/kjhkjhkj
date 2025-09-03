<?php 
require '../config/database.php';
include 'partials/header.php'; 

// Fetch stats
// 2. Unpaid bills
$stmt_tagihan = $pdo->prepare("SELECT COUNT(*) FROM tagihan WHERE siswa_id = ? AND status = 'Belum Lunas'");
$stmt_tagihan->execute([$user['id']]);
$tagihan_belum_lunas = $stmt_tagihan->fetchColumn();

// 3. New Ebooks this month
$stmt_ebooks = $pdo->prepare("SELECT COUNT(*) FROM ebooks WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
$stmt_ebooks->execute();
$ebooks_baru = $stmt_ebooks->fetchColumn();

// 4. Today's Schedule - Fix the logic
$hari_mapping = [
    'Sunday' => 'Minggu',
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa', 
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu'
];

$hari_ini_english = date('l');
$hari_ini = $hari_mapping[$hari_ini_english];
$jadwal_hari_ini = null;

if ($user['jadwal_id']) {
    $stmt_jadwal = $pdo->prepare("SELECT * FROM jadwal WHERE id = ? AND hari = ?");
    $stmt_jadwal->execute([$user['jadwal_id'], $hari_ini]);
    $jadwal_hari_ini = $stmt_jadwal->fetch();
}

// Get progress data for overview
$stmt_progress = $pdo->prepare("
    SELECT AVG(nilai_perkembangan) as avg_progress, COUNT(*) as total_sessions
    FROM student_progress 
    WHERE siswa_id = ? AND status = 'completed'
");
$stmt_progress->execute([$user['id']]);
$progress_summary = $stmt_progress->fetch();

$avg_progress = $progress_summary['avg_progress'] ? round($progress_summary['avg_progress'], 1) : 0;
$total_sessions = $progress_summary['total_sessions'] ?? 0;

?>
<title>Dashboard - Mobile App</title>
</head>
<body class="bg-gray-100">
  <!-- Install PWA Prompt -->
  <div id="install-container" class="fixed top-4 left-4 right-4 z-50 p-4 glass-effect rounded-2xl shadow-lg border border-pink-light/30 animate-slide-in" style="display: none;">
      <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
              <div class="w-12 h-12 bg-gradient-to-br from-pink-accent to-pink-dark rounded-xl flex items-center justify-center">
                  <svg class="w-6 h-6 text-cream" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 011 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                  </svg>
              </div>
              <div>
                  <h4 class="font-semibold text-pink-dark">Install AVA App</h4>
                  <p class="text-sm text-pink-dark/70">Akses mudah di HP Anda</p>
              </div>
          </div>
          <div class="flex space-x-2">
              <button id="install-button" class="px-4 py-2 bg-gradient-to-r from-pink-accent to-pink-dark text-cream rounded-xl font-medium hover:shadow-lg transition-all duration-300">
                  Install
              </button>
              <button id="dismiss-install" class="px-3 py-2 bg-gray-300 text-gray-700 rounded-xl hover:bg-gray-400 transition-all duration-300">
                  ×
              </button>
          </div>
      </div>
  </div>

  <div class="flex flex-col h-screen">
    <!-- Modern Header -->
    <header class="relative bg-gradient-to-br from-pink-accent via-pink-dark to-pink-light rounded-b-[35px] shadow-2xl p-6 text-cream z-10 mb-5 animate-slide-in">
        <div class="absolute inset-0 bg-gradient-to-br from-pink-accent/90 to-pink-dark/90 rounded-b-[35px] backdrop-blur-sm"></div>
        <div class="absolute top-0 right-0 w-32 h-32 bg-yellow-bright/20 rounded-full -translate-y-16 translate-x-16 animate-pulse-soft"></div>
      
        
        <div class="relative flex items-center justify-between">
            <div class="flex items-center">
                <a href="profile.php" class="group">
                    <div class="relative">
                    <img class="w-16 h-16 rounded-full border-2 border-white-400 object-cover" src="<?php echo htmlspecialchars($user['foto_profil'] ? '../' . $user['foto_profil'] : '../avaaset/logo-ava.png'); ?>" alt="Profile Picture">      
                    </div>
                </a>
                <div class="ml-4">
                    <h1 class="font-bold text-xl text-cream drop-shadow-sm"><?php echo htmlspecialchars($user['nama_lengkap']); ?></h1>
                    <p class="text-sm text-cream/80 font-medium">ID: <?php echo htmlspecialchars($user['id']); ?></p>
                </div>
            </div>
            <a href="notifikasi.php" class="relative p-3 rounded-2xl hover:bg-cream/10 transition-all duration-300 group">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-cream group-hover:scale-110 transition-transform duration-300">
                    <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
                    <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
                </svg>
                <div class="absolute top-2 right-2 w-3 h-3 bg-yellow-bright rounded-full animate-bounce-soft"></div>
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow overflow-y-auto p-4 space-y-6 pb-20">
        <!-- Modern Quick Stats -->
        <div class="grid grid-cols-2 gap-4 px-4">
            <div class="glass-effect p-4 rounded-2xl shadow-lg border-l-4 border-yellow-bright card-hover animate-fade-in" style="animation-delay: 0.1s;">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-8 h-8 bg-yellow-bright/20 rounded-xl flex items-center justify-center">
                        <svg class="w-4 h-4 text-yellow-bright" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"/>
                        </svg>
                    </div>
                </div>
                <h2 class="text-xs font-semibold text-pink-dark/70 mb-1">Unpaid Bills</h2>
                <p class="text-2xl font-bold text-pink-dark"><?php echo $tagihan_belum_lunas; ?></p>
                <p class="text-xs text-pink-dark/60">bills pending</p>
            </div>
            
            <div class="glass-effect p-4 rounded-2xl shadow-lg border-l-4 border-blue-soft card-hover animate-fade-in" style="animation-delay: 0.2s;">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-8 h-8 bg-blue-soft/20 rounded-xl flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-soft" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <h2 class="text-xs font-semibold text-pink-dark/70 mb-1">Progress</h2>
                <p class="text-2xl font-bold text-pink-dark"><?php echo $avg_progress; ?></p>
                <p class="text-xs text-pink-dark/60">average score</p>
            </div>
            
            <div class="glass-effect p-4 rounded-2xl shadow-lg border-l-4 border-pink-accent card-hover animate-fade-in" style="animation-delay: 0.2s;">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-8 h-8 bg-pink-accent/20 rounded-xl flex items-center justify-center">
                        <svg class="w-4 h-4 text-pink-accent" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"/>
                        </svg>
                    </div>
                </div>
                <h2 class="text-xs font-semibold text-pink-dark/70 mb-1">Course End</h2>
                <p class="text-lg font-bold text-pink-dark"><?php echo date('d M Y', strtotime($user['tanggal_mulai'] . ' + ' . $user['durasi_bulan'] . ' months')); ?></p>
                <p class="text-xs text-pink-dark/60">estimated date</p>
            </div>
        </div>

        <!-- Modern Today's Schedule -->
        <div class="mx-4 glass-effect p-6 rounded-2xl shadow-lg card-hover animate-fade-in" style="animation-delay: 0.5s;">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-pink-dark">Today's Schedule</h2>
                <span class="text-sm font-medium text-pink-dark/70 bg-pink-light/20 px-3 py-1 rounded-xl"><?php echo $hari_ini; ?></span>
            </div>
            <?php if ($jadwal_hari_ini): ?>
                <div class="flex items-center bg-gradient-to-r from-pink-accent/10 to-pink-light/10 p-4 rounded-2xl border border-pink-light/30">
                    <div class="w-16 h-16 bg-gradient-to-br from-pink-accent to-pink-dark rounded-2xl flex items-center justify-center mr-4 shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-cream">
                            <path d="M21 7.5V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h3.5"/>
                            <path d="M16 2v4"/>
                            <path d="M8 2v4"/>
                            <path d="M3 10h18"/>
                            <path d="M18 22a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/>
                            <path d="M18 16.5V18l.5.5"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold text-pink-dark text-lg">You have a class today!</p>
                        <p class="text-pink-dark/70 font-medium">Time: <?php echo date('H:i', strtotime($jadwal_hari_ini['jam_mulai'])); ?> - <?php echo date('H:i', strtotime($jadwal_hari_ini['jam_selesai'])); ?></p>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <div class="w-20 h-20 bg-gradient-to-br from-pink-light to-blue-soft rounded-full flex items-center justify-center mx-auto mb-4 opacity-50">
                        <svg class="w-10 h-10 text-cream" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2L3 7v11a2 2 0 002 2h10a2 2 0 002-2V7l-7-5zM10 18a3 3 0 100-6 3 3 0 000 6z"/>
                        </svg>
                    </div>
                    <p class="text-pink-dark/60 font-medium">No schedule for today</p>
                    <p class="text-pink-dark/40 text-sm">Enjoy your day off!</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Progress Overview -->
        <div class="mx-4 glass-effect p-6 rounded-2xl shadow-lg card-hover animate-fade-in" style="animation-delay: 0.7s;">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-pink-dark">Progress Overview</h2>
                <a href="progress.php" class="text-sm font-medium text-pink-accent hover:text-pink-dark">View All</a>
            </div>
            
            <?php if ($total_sessions > 0): ?>
                <div class="text-center">
                    <div class="text-4xl font-bold text-pink-accent mb-2"><?php echo $avg_progress; ?>/100</div>
                    <div class="text-sm text-pink-dark/70 mb-4">Average Score</div>
                    <div class="w-full bg-gray-200 rounded-full h-3 mb-4">
                        <div class="bg-gradient-to-r from-pink-accent to-pink-dark h-3 rounded-full transition-all duration-500" style="width: <?php echo $avg_progress; ?>%"></div>
                    </div>
                    <p class="text-sm text-pink-dark/70"><?php echo $total_sessions; ?> completed sessions</p>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gradient-to-br from-pink-light to-blue-soft rounded-full flex items-center justify-center mx-auto mb-4 opacity-50">
                        <svg class="w-8 h-8 text-pink-dark" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-pink-dark/60 font-medium">No progress data yet</p>
                    <p class="text-pink-dark/40 text-sm">Start your first class!</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Modern Floating Action Button -->
        <a href="ebook.php" class="fixed bottom-28 right-6 w-16 h-16 bg-gradient-to-br from-yellow-bright to-pink-accent text-pink-dark rounded-2xl flex flex-col items-center justify-center shadow-2xl hover:shadow-yellow-bright/50 transition-all duration-300 transform hover:scale-110 animate-bounce-soft z-30">
             <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                 <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                 <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
             </svg>
            <span class="text-xs font-bold mt-1">Ebook</span>
        </a>
    </main>
    
    <script src="main.js"></script>
</div>
<?php include 'partials/footer.php'; ?>
