
<?php
$page_title = 'Kalender Jadwal';
include 'partials/header.php';

// Get all schedules with student info
$jadwal_list = $pdo->query("
    SELECT j.*, s.nama_lengkap as siswa_nama, s.id as siswa_id
    FROM jadwal j 
    LEFT JOIN siswa s ON j.id = s.jadwal_id 
    ORDER BY j.hari, j.jam_mulai
")->fetchAll(PDO::FETCH_ASSOC);

// Days of the week
$days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

// Time slots (9 AM to 9 PM)
$time_slots = [];
for ($hour = 9; $hour <= 21; $hour++) {
    $time_slots[] = sprintf('%02d:00:00', $hour);
}

// Organize schedules by day and time
$schedule_grid = [];
foreach ($days as $day) {
    $schedule_grid[$day] = [];
    foreach ($time_slots as $time) {
        $schedule_grid[$day][$time] = null;
    }
}

foreach ($jadwal_list as $jadwal) {
    $day = $jadwal['hari'];
    $time = $jadwal['jam_mulai'];
    $schedule_grid[$day][$time] = $jadwal;
}

// Get today's day name in Indonesian
$today = date('N'); // 1 = Monday, 7 = Sunday
$day_names = ['', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
$today_name = $day_names[$today];
?>

<header class="bg-white shadow-sm p-4">
    <h1 class="text-2xl font-semibold text-gray-800">Kalender Jadwal</h1>
    <p class="text-gray-600">Hari ini: <?php echo $today_name; ?>, <?php echo date('d F Y'); ?></p>
</header>

<main class="flex-1 p-6">
    <!-- Today's Classes -->
    <div class="bg-white p-4 rounded-lg shadow-md mb-6">
        <h3 class="text-lg font-semibold mb-3">Kelas Hari Ini</h3>
        <?php
        $today_classes = array_filter($jadwal_list, function($jadwal) use ($today_name) {
            return $jadwal['hari'] === $today_name && !empty($jadwal['siswa_nama']);
        });
        ?>
        
        <?php if (empty($today_classes)): ?>
            <p class="text-gray-500">Tidak ada kelas hari ini.</p>
        <?php else: ?>
            <div class="grid gap-3">
                <?php foreach ($today_classes as $class): ?>
                    <div class="flex items-center justify-between bg-blue-50 p-3 rounded-lg border border-blue-200">
                        <div>
                            <h4 class="font-medium text-blue-900"><?php echo htmlspecialchars($class['siswa_nama']); ?></h4>
                            <p class="text-sm text-blue-600"><?php echo date('H:i', strtotime($class['jam_mulai'])); ?> - <?php echo date('H:i', strtotime($class['jam_selesai'])); ?></p>
                        </div>
                        <div class="space-x-2">
                            <a href="scan.php?student_id=<?php echo $class['siswa_id']; ?>&schedule_id=<?php echo $class['id']; ?>" 
                               class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                                Scan QR
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Legend -->
    <div class="bg-white p-4 rounded-lg shadow-md mb-6">
        <h3 class="text-lg font-semibold mb-3">Legend</h3>
        <div class="flex flex-wrap gap-4">
            <div class="flex items-center">
                <div class="w-4 h-4 bg-gray-100 border-2 border-dashed border-gray-300 mr-2"></div>
                <span class="text-sm">Tidak Ada Jadwal</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-green-100 border border-green-300 mr-2"></div>
                <span class="text-sm">Jadwal Kosong</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-blue-100 border border-blue-300 mr-2"></div>
                <span class="text-sm">Ada Siswa</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-yellow-100 border border-yellow-300 mr-2"></div>
                <span class="text-sm">Hari Ini</span>
            </div>
        </div>
    </div>

    <!-- Calendar Grid -->
    <div class="bg-white p-6 rounded-lg shadow-md overflow-x-auto">
        <div class="min-w-full">
            <table class="w-full border-collapse">
                <thead>
                    <tr>
                        <th class="border border-gray-300 p-2 bg-gray-50 text-left sticky left-0 z-10">Waktu</th>
                        <?php foreach ($days as $day): ?>
                            <th class="border border-gray-300 p-2 bg-gray-50 text-center min-w-32 <?php echo $day === $today_name ? 'bg-yellow-100' : ''; ?>">
                                <?php echo $day; ?>
                                <?php if ($day === $today_name): ?>
                                    <div class="text-xs font-normal text-yellow-700">Hari Ini</div>
                                <?php endif; ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($time_slots as $time_slot): ?>
                        <tr>
                            <td class="border border-gray-300 p-2 bg-gray-50 font-medium sticky left-0 z-10">
                                <?php echo date('H:i', strtotime($time_slot)); ?>
                            </td>
                            <?php foreach ($days as $day): ?>
                                <?php 
                                $schedule = $schedule_grid[$day][$time_slot];
                                $cell_class = 'border border-gray-300 p-1 h-16 relative';
                                $content = '';
                                
                                // Add today highlight
                                if ($day === $today_name) {
                                    $cell_class .= ' bg-yellow-50 border-yellow-200';
                                }
                                
                                if ($schedule) {
                                    if ($schedule['siswa_nama']) {
                                        // Has student
                                        $cell_class .= ' bg-blue-100 border-blue-300';
                                        $content = '<div class="text-xs font-medium text-blue-800 truncate">' . htmlspecialchars($schedule['siswa_nama']) . '</div>';
                                        $content .= '<div class="text-xs text-blue-600">' . date('H:i', strtotime($schedule['jam_mulai'])) . '-' . date('H:i', strtotime($schedule['jam_selesai'])) . '</div>';
                                    } else {
                                        // Schedule exists but no student
                                        $cell_class .= ' bg-green-100 border-green-300';
                                        $content = '<div class="text-xs font-medium text-green-800">Tersedia</div>';
                                        $content .= '<div class="text-xs text-green-600">' . date('H:i', strtotime($schedule['jam_mulai'])) . '-' . date('H:i', strtotime($schedule['jam_selesai'])) . '</div>';
                                    }
                                } else {
                                    // No schedule
                                    $cell_class .= ' bg-gray-100 border-dashed border-gray-300';
                                    $content = '<div class="text-xs text-gray-400 text-center">-</div>';
                                }
                                ?>
                                <td class="<?php echo $cell_class; ?>">
                                    <?php echo $content; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include 'partials/footer.php'; ?>
