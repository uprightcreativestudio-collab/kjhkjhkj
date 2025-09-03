<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['guru_id'])) {
    header('Location: index.php');
    exit();
}
require '../config/database.php';

$page_title = 'QR Scanner - Absensi';

// Handle QR scan result
$qr_data = $_POST['qr_data'] ?? $_GET['qr_data'] ?? null;
$student_id = null;
$schedule_id = null;
$student = null;

if ($qr_data) {
    // Parse QR data to get student info
    if (strpos($qr_data, 'AVA-') === 0) {
        $stmt = $pdo->prepare("SELECT * FROM siswa WHERE qr_code_identifier = ?");
        $stmt->execute([$qr_data]);
        $student = $stmt->fetch();
        
        if ($student) {
            $student_id = $student['id'];
            $schedule_id = $student['jadwal_id'];
            
            // Store in session for streaming functionality
            $_SESSION['scanned_student_id'] = $student_id;
            $_SESSION['scanned_student_name'] = $student['nama_lengkap'];
        }
    }
}

// If we have student data, get schedule info
if ($student && $schedule_id) {
    $stmt = $pdo->prepare("SELECT j.hari, j.jam_mulai, j.jam_selesai FROM jadwal j WHERE j.id = ?");
    $stmt->execute([$schedule_id]);
    $schedule = $stmt->fetch();
    
    if ($schedule) {
        $student = array_merge($student, $schedule);
    }
}

// Check if there's an active session today
$current_session = null;
if ($student_id) {
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("
        SELECT * FROM student_progress 
        WHERE siswa_id = ? AND session_date = ? 
        ORDER BY created_at DESC LIMIT 1
    ");
    $stmt->execute([$student_id, $today]);
    $current_session = $stmt->fetch();
}

// Handle check-in
if (isset($_POST['action']) && $_POST['action'] === 'checkin' && $student_id && !$current_session) {
    $stmt = $pdo->prepare("
        INSERT INTO student_progress (siswa_id, session_date, session_time, checkin_time, status) 
        VALUES (?, ?, ?, NOW(), 'in_progress')
    ");
    $stmt->execute([$student_id, $today, date('H:i:s')]);
    
    // Also update absensi table
    $stmt = $pdo->prepare("
        INSERT INTO absensi (siswa_id, tanggal, waktu, keterangan) 
        VALUES (?, ?, ?, 'Hadir')
        ON DUPLICATE KEY UPDATE waktu = VALUES(waktu), keterangan = VALUES(keterangan)
    ");
    $stmt->execute([$student_id, $today, date('H:i:s')]);
    
    $_SESSION['success'] = 'Student checked in successfully!';
    header('Location: scan.php?qr_data=' . urlencode($qr_data));
    exit;
}

// Handle check-out with evaluation  
if (isset($_POST['action']) && $_POST['action'] === 'checkout' && $student_id && $current_session) {
    $nilai_perkembangan = $_POST['nilai_perkembangan'] ?? null;
    $keterangan = $_POST['keterangan'] ?? '';
    
    $stmt = $pdo->prepare("
        UPDATE student_progress 
        SET checkout_time = NOW(), 
            nilai_perkembangan = ?, 
            keterangan = ?, 
            status = 'completed' 
        WHERE id = ?
    ");
    $stmt->execute([$nilai_perkembangan, $keterangan, $current_session['id']]);
    
    // Clear streaming session
    $stmt = $pdo->prepare("UPDATE siswa SET active_stream_id = NULL WHERE id = ?");
    $stmt->execute([$student_id]);
    
    // Clear session data
    unset($_SESSION['scanned_student_id']);
    unset($_SESSION['scanned_student_name']);
    
    $_SESSION['success'] = 'Session completed successfully!';
    header('Location: dashboard.php');
    exit;
}

include 'partials/header.php';
?>

<script src="https://unpkg.com/@zxing/library@0.20.0/umd/index.min.js"></script>

<main class="">
    <div class="max-w-2xl mx-auto">
        
        <?php if (!$student): ?>
        <!-- QR Scanner Interface -->
        <div class="glass-effect p-6 rounded-2xl shadow-lg mb-6">
            <h2 class="text-2xl font-bold text-pink-dark mb-4 text-center">QR Code Scanner</h2>
            <p class="text-pink-dark/70 mb-6 text-center">Arahkan kamera ke QR code siswa untuk memulai sesi</p>
            
            <!-- Scanner Container -->
            <div class="relative bg-black rounded-xl overflow-hidden mb-4" style="aspect-ratio: 4/3;">
                <video id="scanner-video" class="w-full h-full object-cover" autoplay muted playsinline></video>
                <div id="scanner-overlay" class="absolute inset-0 border-4 border-pink-accent rounded-xl opacity-50"></div>
                <div id="scanner-line" class="absolute left-1/2 top-0 w-1 h-full bg-pink-accent animate-pulse transform -translate-x-1/2"></div>
            </div>
            
            <!-- Scanner Controls -->
            <div class="flex justify-center space-x-4">
                <button id="start-scanner" class="bg-gradient-to-r from-pink-accent to-pink-dark text-cream px-6 py-3 rounded-xl font-medium hover:shadow-lg transition-all">
                    <i data-lucide="camera" class="w-5 h-5 inline mr-2"></i>Mulai Scanner
                </button>
                <button id="stop-scanner" class="bg-gray-500 text-white px-6 py-3 rounded-xl font-medium hover:bg-gray-600 transition-all" style="display: none;">
                    <i data-lucide="camera-off" class="w-5 h-5 inline mr-2"></i>Stop Scanner
                </button>
            </div>
            
            <!-- Manual Input (fallback) -->
            <div class="mt-6 pt-6 border-t border-pink-light/30">
                <h3 class="text-lg font-semibold text-pink-dark mb-3">Input Manual QR Code</h3>
                <form method="POST" class="flex space-x-3">
                    <input type="text" name="qr_data" placeholder="Masukkan kode QR (contoh: AVA-68af...)" 
                           class="flex-1 border border-gray-300 rounded-xl p-3 focus:ring-pink-accent focus:border-pink-accent">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-xl hover:bg-blue-700 transition-all">
                        <i data-lucide="search" class="w-5 h-5"></i>
                    </button>
                </form>
            </div>
        </div>
        <?php else: ?>
        <!-- Student Info Card -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center space-x-4">
                <?php if ($student['foto_profil']): ?>
                    <img src="../<?php echo htmlspecialchars($student['foto_profil']); ?>" 
                         alt="Profile" class="w-16 h-16 rounded-full object-cover">
                <?php else: ?>
                    <div class="w-16 h-16 rounded-full bg-gray-300 flex items-center justify-center">
                        <i class="fas fa-user text-gray-600 text-2xl"></i>
                    </div>
                <?php endif; ?>
                <div>
                    <h3 class="text-xl font-semibold text-gray-800"><?php echo htmlspecialchars($student['nama_lengkap']); ?></h3>
                    <p class="text-gray-600"><?php echo htmlspecialchars($student['hari']); ?> | <?php echo date('H:i', strtotime($student['jam_mulai'])); ?> - <?php echo date('H:i', strtotime($student['jam_selesai'])); ?></p>
                    <p class="text-sm text-gray-500">Today: <?php echo date('d F Y'); ?></p>
                </div>
            </div>
        </div>

        <!-- Session Status -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Status Sesi</h3>
            
            <?php if (!$current_session): ?>
                <!-- Check-in Form -->
                <div class="text-center">
                    <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-qrcode text-green-600 text-3xl"></i>
                    </div>
                    <h4 class="text-lg font-medium text-gray-800 mb-2">Mulai Sesi Belajar</h4>
                    <p class="text-gray-600 mb-4">Klik tombol di bawah untuk check-in siswa</p>
                    
                    <form method="POST" class="inline">
                        <input type="hidden" name="action" value="checkin">
                        <button type="submit" class="bg-green-600 text-white px-8 py-4 rounded-lg hover:bg-green-700 font-medium text-lg">
                            <i class="fas fa-sign-in-alt mr-2"></i>Check In & Mulai Streaming
                        </button>
                    </form>
                </div>
                
            <?php elseif ($current_session['status'] === 'in_progress'): ?>
                <!-- Active Session - Show Checkout Option -->
                <div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-blue-600 rounded-full animate-pulse mr-3"></div>
                            <div>
                                <p class="font-medium text-blue-800">Sesi Sedang Berlangsung</p>
                                <p class="text-sm text-blue-600">Check-in: <?php echo date('H:i', strtotime($current_session['checkin_time'])); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Streaming Controls -->
                    <div class="bg-gradient-to-r from-purple-50 to-blue-50 p-4 rounded-lg border border-purple-200 mb-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-purple-800">Live Streaming</p>
                                <p class="text-sm text-purple-600">Siswa dapat bergabung ke streaming</p>
                            </div>
                            <a href="stream.php" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-all">
                                <i class="fas fa-video mr-2"></i>Buka Streaming
                            </a>
                        </div>
                    </div>
                    
                    <!-- Checkout Form -->
                    <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                        <h4 class="text-lg font-semibold text-red-800 mb-4">Selesaikan Sesi & Check Out</h4>
                        
                        <form method="POST" class="space-y-4">
                            <input type="hidden" name="action" value="checkout">
                            
                            <!-- Progress Score -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nilai Perkembangan (10-100)</label>
                                <input type="number" name="nilai_perkembangan" min="10" max="100" step="1" 
                                       class="w-full border border-gray-300 rounded-xl p-3 focus:ring-red-500 focus:border-red-500"
                                       placeholder="Masukkan nilai 10-100" required>
                            </div>
                            
                            <!-- Comments -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan & Feedback</label>
                                <textarea name="keterangan" rows="4" 
                                          class="w-full border border-gray-300 rounded-xl p-3 focus:ring-red-500 focus:border-red-500"
                                          placeholder="Tulis catatan tentang progress siswa, area yang perlu diperbaiki, atau pencapaian hari ini..." required></textarea>
                            </div>
                            
                            <div class="flex justify-center">
                                <button type="submit" class="bg-red-600 text-white px-8 py-3 rounded-lg hover:bg-red-700 font-medium">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Check Out & Selesaikan Sesi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
            <?php else: ?>
                <!-- Session Completed -->
                <div class="text-center">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check-circle text-gray-600 text-3xl"></i>
                    </div>
                    <h4 class="text-lg font-medium text-gray-800 mb-2">Sesi Telah Selesai</h4>
                    <p class="text-gray-600 mb-4">
                        Check-in: <?php echo date('H:i', strtotime($current_session['checkin_time'])); ?> | 
                        Check-out: <?php echo date('H:i', strtotime($current_session['checkout_time'])); ?>
                    </p>
                    <div class="bg-green-50 p-4 rounded-lg border border-green-200 mb-4">
                        <p class="text-green-800 font-medium">Nilai: <?php echo $current_session['nilai_perkembangan']; ?>/100</p>
                        <p class="text-green-700 text-sm mt-1"><?php echo htmlspecialchars($current_session['keterangan']); ?></p>
                    </div>
                    <a href="dashboard.php" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700">
                        Kembali ke Dashboard
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Quick Actions -->
        <div class="glass-effect p-6 rounded-2xl shadow-lg">
            <h3 class="text-lg font-semibold text-pink-dark mb-4">Quick Actions</h3>
            <div class="grid grid-cols-2 gap-4">
                <a href="dashboard.php" class="text-center p-4 border border-pink-light/30 rounded-xl hover:bg-pink-light/10">
                    <i data-lucide="home" class="text-pink-dark text-2xl mb-2 block w-6 h-6 mx-auto"></i>
                    <span class="text-sm text-pink-dark">Dashboard</span>
                </a>
                <a href="jadwal_calendar.php" class="text-center p-4 border border-pink-light/30 rounded-xl hover:bg-pink-light/10">
                    <i data-lucide="calendar" class="text-pink-dark text-2xl mb-2 block w-6 h-6 mx-auto"></i>
                    <span class="text-sm text-pink-dark">Jadwal</span>
                </a>
            </div>
        </div>
    </div>
        <?php endif; ?>
    </div>
</main>

<script>
let codeReader;
let isScanning = false;

document.addEventListener('DOMContentLoaded', function() {
    if (typeof ZXing !== 'undefined') {
        initializeScanner();
    } else {
        setTimeout(() => {
            if (typeof ZXing !== 'undefined') {
                initializeScanner();
            } else {
                console.error('ZXing library not loaded');
                document.getElementById('start-scanner').disabled = true;
                document.getElementById('start-scanner').innerHTML = '<i data-lucide="camera-off" class="w-5 h-5 inline mr-2"></i>Library Error';
            }
        }, 1000);
    }
    
    lucide.createIcons();
});

function initializeScanner() {
    document.getElementById('start-scanner').addEventListener('click', startScanner);
    document.getElementById('stop-scanner').addEventListener('click', stopScanner);
    
    // Auto-start scanner
    startScanner();
}

async function startScanner() {
    try {
        codeReader = new ZXing.BrowserQRCodeReader();
        
        const devices = await navigator.mediaDevices.enumerateDevices();
        const videoDevices = devices.filter(device => device.kind === 'videoinput');
        
        if (videoDevices.length === 0) {
            alert('Tidak ada kamera yang terdeteksi');
            return;
        }
        
        let selectedDeviceId = videoDevices[0].deviceId;
        
        for (const device of videoDevices) {
            if (device.label.toLowerCase().includes('back') || device.label.toLowerCase().includes('rear')) {
                selectedDeviceId = device.deviceId;
                break;
            }
        }
        
        isScanning = true;
        document.getElementById('start-scanner').style.display = 'none';
        document.getElementById('stop-scanner').style.display = 'inline-block';
        
        codeReader.decodeFromVideoDevice(selectedDeviceId, 'scanner-video', (result, err) => {
            if (result) {
                const qrData = result.text;
                console.log('QR Code detected:', qrData);
                
                if (qrData.startsWith('AVA-')) {
                    stopScanner();
                    
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `<input type="hidden" name="qr_data" value="${qrData}">`;
                    document.body.appendChild(form);
                    form.submit();
                } else {
                    alert('QR Code tidak valid. Pastikan menggunakan QR Code siswa AVA.');
                }
            }
            
            if (err && !(err instanceof ZXing.NotFoundException)) {
                console.error('QR Scanner error:', err);
            }
        });
        
    } catch (err) {
        console.error('Error starting scanner:', err);
        alert('Error memulai scanner: ' + err.message);
        
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: true });
            const video = document.getElementById('scanner-video');
            video.srcObject = stream;
            
            document.getElementById('start-scanner').style.display = 'none';
            document.getElementById('stop-scanner').style.display = 'inline-block';
            
            codeReader = new ZXing.BrowserQRCodeReader();
            codeReader.decodeFromStream(stream, 'scanner-video', (result, err) => {
                if (result) {
                    const qrData = result.text;
                    console.log('QR Code detected:', qrData);
                    
                    if (qrData.startsWith('AVA-')) {
                        stopScanner();
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.innerHTML = `<input type="hidden" name="qr_data" value="${qrData}">`;
                        document.body.appendChild(form);
                        form.submit();
                    } else {
                        alert('QR Code tidak valid. Pastikan menggunakan QR Code siswa AVA.');
                    }
                }
                
                if (err && !(err instanceof ZXing.NotFoundException)) {
                    console.error('QR Scanner error:', err);
                }
            });
        } catch (fallbackErr) {
            console.error('Fallback camera access failed:', fallbackErr);
            alert('Tidak dapat mengakses kamera. Silakan gunakan input manual di bawah.');
        }
    }
}

function stopScanner() {
    if (codeReader) {
        codeReader.reset();
    }
    
    const video = document.getElementById('scanner-video');
    if (video.srcObject) {
        const tracks = video.srcObject.getTracks();
        tracks.forEach(track => track.stop());
        video.srcObject = null;
    }
    
    isScanning = false;
    document.getElementById('start-scanner').style.display = 'inline-block';
    document.getElementById('stop-scanner').style.display = 'none';
}
</script>

<?php include 'partials/footer.php'; ?>