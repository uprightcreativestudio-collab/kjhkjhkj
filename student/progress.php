
<?php
$page_title = 'Progress Belajar';
include 'partials/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user data
$stmt = $pdo->prepare("SELECT * FROM siswa WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get progress data
$stmt = $pdo->prepare("
    SELECT * FROM student_progress 
    WHERE siswa_id = ? AND status = 'completed' 
    ORDER BY session_date DESC
");
$stmt->execute([$user_id]);
$progress_data = $stmt->fetchAll();

// Calculate averages
$averages = [
    'teknik' => 0,
    'pitch' => 0,
    'rhythm' => 0,
    'expression' => 0
];

if (!empty($progress_data)) {
    $totals = ['teknik' => 0, 'pitch' => 0, 'rhythm' => 0, 'expression' => 0];
    $counts = ['teknik' => 0, 'pitch' => 0, 'rhythm' => 0, 'expression' => 0];
    
    foreach ($progress_data as $session) {
        if ($session['nilai_teknik']) { $totals['teknik'] += $session['nilai_teknik']; $counts['teknik']++; }
        if ($session['nilai_pitch']) { $totals['pitch'] += $session['nilai_pitch']; $counts['pitch']++; }
        if ($session['nilai_rhythm']) { $totals['rhythm'] += $session['nilai_rhythm']; $counts['rhythm']++; }
        if ($session['nilai_expression']) { $totals['expression'] += $session['nilai_expression']; $counts['expression']++; }
    }
    
    foreach ($averages as $key => &$avg) {
        if ($counts[$key] > 0) {
            $avg = round($totals[$key] / $counts[$key], 1);
        }
    }
}

// Get last 10 sessions for chart
$chart_data = array_slice(array_reverse($progress_data), -10);
?>
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
<main class="min-h-screen bg-gradient-to-br from-cream via-pink-light/30 to-blue-soft/20 px-4 py-8">
    
    <div class="container mx-auto max-w-6xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-pink-dark mb-4">Progress Belajar</h1>
            <p class="text-pink-dark/70 text-lg">Lihat perkembangan kemampuan vokal Anda</p>
        </div>

        <!-- Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="glass-effect p-6 rounded-2xl text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-soft to-pink-light rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-microphone text-pink-dark text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-pink-dark mb-2">Teknik Vokal</h3>
                <div class="text-3xl font-bold text-pink-accent"><?php echo $averages['teknik']; ?>/10</div>
            </div>
            
            <div class="glass-effect p-6 rounded-2xl text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-pink-accent to-yellow-bright rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-music text-pink-dark text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-pink-dark mb-2">Pitch Control</h3>
                <div class="text-3xl font-bold text-pink-accent"><?php echo $averages['pitch']; ?>/10</div>
            </div>
            
            <div class="glass-effect p-6 rounded-2xl text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-yellow-bright to-blue-soft rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-drum text-pink-dark text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-pink-dark mb-2">Rhythm</h3>
                <div class="text-3xl font-bold text-pink-accent"><?php echo $averages['rhythm']; ?>/10</div>
            </div>
            
            <div class="glass-effect p-6 rounded-2xl text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-pink-light to-pink-accent rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-heart text-pink-dark text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-pink-dark mb-2">Expression</h3>
                <div class="text-3xl font-bold text-pink-accent"><?php echo $averages['expression']; ?>/10</div>
            </div>
        </div>

        <!-- Progress Chart -->
        <div class="glass-effect p-6 rounded-2xl mb-8">
            <h3 class="text-xl font-semibold text-pink-dark mb-6">Progress Chart</h3>
            <?php if (!empty($chart_data)): ?>
                <div class="h-96">
                    <canvas id="progressChart"></canvas>
                </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <div class="w-24 h-24 bg-pink-light/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-chart-line text-pink-dark/50 text-2xl"></i>
                    </div>
                    <p class="text-pink-dark/60">Belum ada data progress. Mulai kelas untuk melihat perkembangan!</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Session History -->
        <div class="glass-effect p-6 rounded-2xl">
            <h3 class="text-xl font-semibold text-pink-dark mb-6">Riwayat Sesi</h3>
            <?php if (!empty($progress_data)): ?>
                <div class="space-y-4">
                    <?php foreach ($progress_data as $session): ?>
                        <div class="border border-pink-light/30 rounded-xl p-4 hover:bg-pink-light/10 transition-colors">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h4 class="font-semibold text-pink-dark"><?php echo date('d F Y', strtotime($session['session_date'])); ?></h4>
                                    <p class="text-sm text-pink-dark/70"><?php echo date('H:i', strtotime($session['checkin_time'])); ?> - <?php echo date('H:i', strtotime($session['checkout_time'])); ?></p>
                                </div>
                                <div class="flex space-x-2">
                                    <?php if ($session['nilai_teknik']): ?>
                                        <span class="bg-blue-soft/20 text-blue-soft px-2 py-1 rounded-full text-xs">T: <?php echo $session['nilai_teknik']; ?></span>
                                    <?php endif; ?>
                                    <?php if ($session['nilai_pitch']): ?>
                                        <span class="bg-pink-accent/20 text-pink-accent px-2 py-1 rounded-full text-xs">P: <?php echo $session['nilai_pitch']; ?></span>
                                    <?php endif; ?>
                                    <?php if ($session['nilai_rhythm']): ?>
                                        <span class="bg-yellow-bright/20 text-yellow-600 px-2 py-1 rounded-full text-xs">R: <?php echo $session['nilai_rhythm']; ?></span>
                                    <?php endif; ?>
                                    <?php if ($session['nilai_expression']): ?>
                                        <span class="bg-pink-light/20 text-pink-dark px-2 py-1 rounded-full text-xs">E: <?php echo $session['nilai_expression']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($session['keterangan']): ?>
                                <p class="text-sm text-pink-dark/80 bg-cream/50 p-3 rounded-lg"><?php echo htmlspecialchars($session['keterangan']); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <div class="w-24 h-24 bg-pink-light/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-history text-pink-dark/50 text-2xl"></i>
                    </div>
                    <p class="text-pink-dark/60">Belum ada riwayat sesi. Yuk mulai kelas pertama!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php if (!empty($chart_data)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('progressChart').getContext('2d');
const chart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_map(function($session) { 
            return date('d/m', strtotime($session['session_date'])); 
        }, $chart_data)); ?>,
        datasets: [
            {
                label: 'Teknik Vokal',
                data: <?php echo json_encode(array_map(function($session) { 
                    return $session['nilai_teknik'] ?? 0; 
                }, $chart_data)); ?>,
                borderColor: '#60A5FA',
                backgroundColor: 'rgba(96, 165, 250, 0.1)',
                tension: 0.4
            },
            {
                label: 'Pitch Control',
                data: <?php echo json_encode(array_map(function($session) { 
                    return $session['nilai_pitch'] ?? 0; 
                }, $chart_data)); ?>,
                borderColor: '#F472B6',
                backgroundColor: 'rgba(244, 114, 182, 0.1)',
                tension: 0.4
            },
            {
                label: 'Rhythm',
                data: <?php echo json_encode(array_map(function($session) { 
                    return $session['nilai_rhythm'] ?? 0; 
                }, $chart_data)); ?>,
                borderColor: '#FBBF24',
                backgroundColor: 'rgba(251, 191, 36, 0.1)',
                tension: 0.4
            },
            {
                label: 'Expression',
                data: <?php echo json_encode(array_map(function($session) { 
                    return $session['nilai_expression'] ?? 0; 
                }, $chart_data)); ?>,
                borderColor: '#EC4899',
                backgroundColor: 'rgba(236, 72, 153, 0.1)',
                tension: 0.4
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                max: 10,
                grid: {
                    color: 'rgba(0, 0, 0, 0.1)'
                }
            },
            x: {
                grid: {
                    color: 'rgba(0, 0, 0, 0.1)'
                }
            }
        },
        plugins: {
            legend: {
                position: 'top'
            }
        }
    }
});
</script>
<?php endif; ?>

<?php include 'partials/footer.php'; ?>
