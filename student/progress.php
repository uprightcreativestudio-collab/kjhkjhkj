
<?php
$page_title = 'Progress Belajar';
include 'partials/header.php';

// Get progress data
$stmt = $pdo->prepare("
    SELECT * FROM student_progress 
    WHERE siswa_id = ? 
    ORDER BY session_date DESC
");
$stmt->execute([$user['id']]);
$progress_data = $stmt->fetchAll();

// Calculate average from nilai_perkembangan
$stmt = $pdo->prepare("
    SELECT AVG(nilai_perkembangan) as avg_progress, COUNT(*) as total_sessions
    FROM student_progress 
    WHERE siswa_id = ? AND status = 'completed'
");
$stmt->execute([$user['id']]);
$progress_summary = $stmt->fetch();

$avg_progress = $progress_summary['avg_progress'] ? round($progress_summary['avg_progress'], 1) : 0;
$total_sessions = $progress_summary['total_sessions'] ?? 0;

// Get last 10 sessions for chart
$completed_sessions = array_filter($progress_data, function($session) {
    return $session['status'] === 'completed';
});
$chart_data = array_slice(array_reverse($completed_sessions), -10);
?>

<header class="relative bg-gradient-to-br from-pink-accent via-pink-dark to-pink-light rounded-b-[35px] shadow-2xl p-6 text-cream z-10 mb-5 animate-slide-in">
    <div class="absolute inset-0 bg-gradient-to-br from-pink-accent/90 to-pink-dark/90 rounded-b-[35px] backdrop-blur-sm"></div>
    <div class="absolute top-0 right-0 w-32 h-32 bg-yellow-bright/20 rounded-full -translate-y-16 translate-x-16 animate-pulse-soft"></div>
    <div class="absolute bottom-0 left-0 w-24 h-24 bg-blue-soft/20 rounded-full translate-y-12 -translate-x-12 animate-float"></div>

    <div class="relative flex items-center justify-between">
        <div class="flex items-center">
            <a href="dashboard.php" class="group mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-cream group-hover:scale-110 transition-transform duration-300">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
            </a>
            <div class="flex items-center">
                <img class="w-16 h-16 rounded-full border-2 border-white-400 object-cover" src="<?php echo htmlspecialchars($user['foto_profil'] ? '../' . $user['foto_profil'] : '../avaaset/logo-ava.png'); ?>" alt="Profile Picture">      
                <div class="ml-4">
                    <h1 class="font-bold text-xl text-cream drop-shadow-sm">Progress Belajar</h1>
                    <p class="text-sm text-cream/80 font-medium"><?php echo htmlspecialchars($user['nama_lengkap']); ?></p>
                </div>
            </div>
        </div>
    </header>

<main class="flex-grow overflow-y-auto p-4 space-y-6 pb-20">
    <div class="container mx-auto max-w-4xl">
        <!-- Overall Progress Card -->
        <div class="glass-effect p-6 rounded-2xl shadow-lg card-hover animate-fade-in">
            <div class="text-center">
                <h2 class="text-2xl font-bold text-pink-dark mb-4">Overall Progress</h2>
                <div class="text-6xl font-bold text-pink-accent mb-2"><?php echo $avg_progress; ?></div>
                <div class="text-lg text-pink-dark/70 mb-4">Average Score</div>
                <div class="w-full bg-gray-200 rounded-full h-4 mb-4">
                    <div class="bg-gradient-to-r from-pink-accent to-pink-dark h-4 rounded-full transition-all duration-1000" style="width: <?php echo $avg_progress; ?>%"></div>
                </div>
                <p class="text-pink-dark/70"><?php echo $total_sessions; ?> completed sessions</p>
            </div>
        </div>

        <!-- Progress Chart -->
        <div class="glass-effect p-6 rounded-2xl shadow-lg card-hover animate-fade-in" style="animation-delay: 0.2s;">
            <h3 class="text-xl font-semibold text-pink-dark mb-6">Progress Trend</h3>
            <?php if (!empty($chart_data)): ?>
                <div class="h-64">
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
        <div class="glass-effect p-6 rounded-2xl shadow-lg card-hover animate-fade-in" style="animation-delay: 0.4s;">
            <h3 class="text-xl font-semibold text-pink-dark mb-6">Riwayat Sesi</h3>
            <?php if (!empty($progress_data)): ?>
                <div class="space-y-4">
                    <?php foreach ($progress_data as $session): ?>
                        <div class="border border-pink-light/30 rounded-xl p-4 hover:bg-pink-light/10 transition-colors">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h4 class="font-semibold text-pink-dark"><?php echo date('d F Y', strtotime($session['session_date'])); ?></h4>
                                    <p class="text-sm text-pink-dark/70">
                                        <?php echo date('H:i', strtotime($session['checkin_time'])); ?> - 
                                        <?php echo $session['checkout_time'] ? date('H:i', strtotime($session['checkout_time'])) : 'Ongoing'; ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <?php if ($session['nilai_perkembangan']): ?>
                                        <div class="text-2xl font-bold text-pink-accent"><?php echo $session['nilai_perkembangan']; ?></div>
                                        <div class="text-xs text-pink-dark/70">Score</div>
                                    <?php else: ?>
                                        <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs">In Progress</span>
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
                label: 'Progress Score',
                data: <?php echo json_encode(array_map(function($session) { 
                    return $session['nilai_perkembangan'] ?? 0; 
                }, $chart_data)); ?>,
                borderColor: '#EC4899',
                backgroundColor: 'rgba(236, 72, 153, 0.1)',
                tension: 0.4,
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                max: 100,
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
