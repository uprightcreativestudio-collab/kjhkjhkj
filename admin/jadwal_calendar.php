
<?php
$page_title = 'Kalender Jadwal';
include 'partials/header.php';

// Get all schedules
$jadwal_list = $pdo->query("
    SELECT j.*, s.nama_lengkap as siswa_nama 
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
?>

<header class="bg-white shadow-sm p-4 flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-800">Kalender Jadwal</h1>
    <div class="flex space-x-2">
        <button onclick="openAddScheduleModal()" class="bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700">
            <i class="fas fa-plus mr-2"></i>Tambah Jadwal
        </button>
        <a href="jadwal.php" class="bg-gray-600 text-white py-2 px-4 rounded-md hover:bg-gray-700">
            <i class="fas fa-list mr-2"></i>List View
        </a>
    </div>
</header>

<main class="flex-1 p-6">
    <!-- Legend -->
    <div class="bg-white p-4 rounded-lg shadow-md mb-6">
        <h3 class="text-lg font-semibold mb-3">Legend</h3>
        <div class="flex flex-wrap gap-4">
            <div class="flex items-center">
                <div class="w-4 h-4 bg-gray-100 border-2 border-dashed border-gray-300 mr-2"></div>
                <span class="text-sm">Available</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-green-100 border border-green-300 mr-2"></div>
                <span class="text-sm">Scheduled (No Student)</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-blue-100 border border-blue-300 mr-2"></div>
                <span class="text-sm">Booked (With Student)</span>
            </div>
        </div>
    </div>

    <!-- Calendar Grid -->
    <div class="bg-white p-6 rounded-lg shadow-md overflow-x-auto">
        <div class="min-w-full">
            <table class="w-full border-collapse">
                <thead>
                    <tr>
                        <th class="border border-gray-300 p-2 bg-gray-50 text-left sticky left-0 z-10">Time</th>
                        <?php foreach ($days as $day): ?>
                            <th class="border border-gray-300 p-2 bg-gray-50 text-center min-w-32"><?php echo $day; ?></th>
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
                                $cell_class = 'border border-gray-300 p-1 h-16 relative cursor-pointer hover:bg-gray-50';
                                $content = '';
                                
                                if ($schedule) {
                                    if ($schedule['siswa_nama']) {
                                        // Booked with student
                                        $cell_class .= ' bg-blue-100 border-blue-300';
                                        $content = '<div class="text-xs font-medium text-blue-800 truncate">' . htmlspecialchars($schedule['siswa_nama']) . '</div>';
                                        $content .= '<div class="text-xs text-blue-600">' . date('H:i', strtotime($schedule['jam_mulai'])) . '-' . date('H:i', strtotime($schedule['jam_selesai'])) . '</div>';
                                    } else {
                                        // Scheduled but no student
                                        $cell_class .= ' bg-green-100 border-green-300';
                                        $content = '<div class="text-xs font-medium text-green-800">Available</div>';
                                        $content .= '<div class="text-xs text-green-600">' . date('H:i', strtotime($schedule['jam_mulai'])) . '-' . date('H:i', strtotime($schedule['jam_selesai'])) . '</div>';
                                    }
                                } else {
                                    // No schedule
                                    $cell_class .= ' bg-gray-100 border-dashed border-gray-300';
                                    $content = '<div class="text-xs text-gray-400 text-center">+</div>';
                                }
                                ?>
                                <td class="<?php echo $cell_class; ?>" 
                                    data-day="<?php echo $day; ?>" 
                                    data-time="<?php echo $time_slot; ?>"
                                    data-schedule-id="<?php echo $schedule ? $schedule['id'] : ''; ?>"
                                    onclick="handleCellClick(this)">
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

<!-- Add Schedule Modal -->
<div id="addScheduleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Add New Schedule</h3>
        </div>
        <form id="addScheduleForm" action="jadwal_form.php" method="POST">
            <div class="px-6 py-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Day</label>
                    <select name="hari" id="modal_hari" class="w-full border border-gray-300 rounded-md p-2" required>
                        <option value="">Select Day</option>
                        <?php foreach ($days as $day): ?>
                            <option value="<?php echo $day; ?>"><?php echo $day; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Start Time</label>
                    <input type="time" name="jam_mulai" id="modal_jam_mulai" class="w-full border border-gray-300 rounded-md p-2" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">End Time</label>
                    <input type="time" name="jam_selesai" id="modal_jam_selesai" class="w-full border border-gray-300 rounded-md p-2" required>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-2">
                <button type="button" onclick="closeAddScheduleModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Cancel
                </button>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                    Add Schedule
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Schedule Modal -->
<div id="editScheduleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Edit Schedule</h3>
        </div>
        <div class="px-6 py-4">
            <div id="editScheduleContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-2">
            <button type="button" onclick="closeEditScheduleModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                Close
            </button>
            <button type="button" onclick="deleteSchedule()" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                Delete
            </button>
        </div>
    </div>
</div>

<script>
let currentScheduleId = null;

function openAddScheduleModal(day = '', time = '') {
    document.getElementById('addScheduleModal').classList.remove('hidden');
    document.getElementById('addScheduleModal').classList.add('flex');
    
    if (day) {
        document.getElementById('modal_hari').value = day;
    }
    if (time) {
        document.getElementById('modal_jam_mulai').value = time;
        // Set end time to 1 hour later
        const startTime = new Date('2000-01-01 ' + time);
        startTime.setHours(startTime.getHours() + 1);
        document.getElementById('modal_jam_selesai').value = startTime.toTimeString().slice(0, 5);
    }
}

function closeAddScheduleModal() {
    document.getElementById('addScheduleModal').classList.add('hidden');
    document.getElementById('addScheduleModal').classList.remove('flex');
}

function openEditScheduleModal(scheduleId) {
    currentScheduleId = scheduleId;
    document.getElementById('editScheduleModal').classList.remove('hidden');
    document.getElementById('editScheduleModal').classList.add('flex');
    
    // Load schedule details
    fetch(`api_get_schedule.php?id=${scheduleId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('editScheduleContent').innerHTML = `
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Day</label>
                        <p class="text-lg">${data.hari}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Time</label>
                        <p class="text-lg">${data.jam_mulai} - ${data.jam_selesai}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <p class="text-lg">${data.siswa_nama ? 'Booked by: ' + data.siswa_nama : 'Available'}</p>
                    </div>
                </div>
            `;
        })
        .catch(err => {
            console.error('Error loading schedule:', err);
        });
}

function closeEditScheduleModal() {
    document.getElementById('editScheduleModal').classList.add('hidden');
    document.getElementById('editScheduleModal').classList.remove('flex');
    currentScheduleId = null;
}

function handleCellClick(cell) {
    const scheduleId = cell.dataset.scheduleId;
    const day = cell.dataset.day;
    const time = cell.dataset.time;
    
    if (scheduleId) {
        // Existing schedule - open edit modal
        openEditScheduleModal(scheduleId);
    } else {
        // Empty slot - open add modal
        openAddScheduleModal(day, time);
    }
}

function deleteSchedule() {
    if (!currentScheduleId) return;
    
    if (confirm('Are you sure you want to delete this schedule?')) {
        window.location.href = `jadwal.php?action=delete&id=${currentScheduleId}`;
    }
}

// Handle form submission
document.getElementById('addScheduleForm').addEventListener('submit', function(e) {
    const day = document.getElementById('modal_hari').value;
    const startTime = document.getElementById('modal_jam_mulai').value;
    const endTime = document.getElementById('modal_jam_selesai').value;
    
    // Check for time conflicts
    const existingSchedules = <?php echo json_encode($jadwal_list); ?>;
    const hasConflict = existingSchedules.some(schedule => {
        return schedule.hari === day && 
               ((startTime >= schedule.jam_mulai && startTime < schedule.jam_selesai) ||
                (endTime > schedule.jam_mulai && endTime <= schedule.jam_selesai) ||
                (startTime <= schedule.jam_mulai && endTime >= schedule.jam_selesai));
    });
    
    if (hasConflict) {
        e.preventDefault();
        alert('Time conflict detected! Please choose a different time slot.');
    }
});
</script>

<?php include 'partials/footer.php'; ?>
