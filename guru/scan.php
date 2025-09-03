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

// Check for active session
$active_session = null;
$scanned_student_id = $_SESSION['scanned_student_id'] ?? null;
$scanned_student_name = $_SESSION['scanned_student_name'] ?? null;

if ($scanned_student_id) {
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("
        SELECT sp.*, s.nama_lengkap 
        FROM student_progress sp
        JOIN siswa s ON sp.siswa_id = s.id
        WHERE sp.siswa_id = ? AND sp.session_date = ? AND sp.status = 'in_progress'
        ORDER BY sp.created_at DESC LIMIT 1
    ");
    $stmt->execute([$scanned_student_id, $today]);
    $active_session = $stmt->fetch(PDO::FETCH_ASSOC);
}

include 'partials/header.php';
?>

<script src="https://unpkg.com/@zxing/library@0.20.0/umd/index.min.js"></script>

<main class="">
    <div class="max-w-2xl mx-auto">
        
        <?php if (!$active_session): ?>
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
                <form id="manual-qr-form" class="flex space-x-3">
                    <input type="text" id="manual-qr-input" placeholder="Masukkan kode QR (contoh: AVA-68af...)" 
                           class="flex-1 border border-gray-300 rounded-xl p-3 focus:ring-pink-accent focus:border-pink-accent">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-xl hover:bg-blue-700 transition-all">
                        <i data-lucide="search" class="w-5 h-5"></i>
                    </button>
                </form>
            </div>
        </div>
        
        <?php else: ?>
        <!-- Active Session - Show Student Info and Checkout -->
        <div class="glass-effect p-6 rounded-2xl shadow-lg mb-6">
            <div class="text-center mb-6">
                <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="user-check" class="w-12 h-12 text-green-600"></i>
                </div>
                <h2 class="text-2xl font-bold text-pink-dark">Sesi Aktif</h2>
                <p class="text-pink-dark/70">Dengan: <strong><?php echo htmlspecialchars($active_session['nama_lengkap']); ?></strong></p>
                <p class="text-sm text-pink-dark/60">Check-in: <?php echo date('H:i', strtotime($active_session['checkin_time'])); ?></p>
            </div>

            <!-- Checkout Form -->
            <div class="bg-red-50 border border-red-200 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-red-800 mb-4">Selesaikan Sesi & Check Out</h3>
                
                <form id="checkout-form" class="space-y-4">
                    <input type="hidden" id="checkout-student-id" value="<?php echo $scanned_student_id; ?>">
                    
                    <!-- Progress Score -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nilai Perkembangan (1-100)</label>
                        <input type="number" id="nilai_perkembangan" min="1" max="100" step="1" 
                               class="w-full border border-gray-300 rounded-xl p-3 focus:ring-red-500 focus:border-red-500"
                               placeholder="Masukkan nilai 1-100" required>
                    </div>
                    
                    <!-- Comments -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan & Feedback</label>
                        <textarea id="keterangan" rows="4" 
                                  class="w-full border border-gray-300 rounded-xl p-3 focus:ring-red-500 focus:border-red-500"
                                  placeholder="Tulis catatan tentang progress siswa, area yang perlu diperbaiki, atau pencapaian hari ini..." required></textarea>
                    </div>
                    
                    <div class="flex justify-center space-x-3">
                        <button type="button" id="cancel-session" class="bg-gray-500 text-white px-6 py-3 rounded-xl hover:bg-gray-600 font-medium">
                            <i data-lucide="x" class="w-5 h-5 inline mr-2"></i>Batal
                        </button>
                        <button type="submit" class="bg-red-600 text-white px-8 py-3 rounded-xl hover:bg-red-700 font-medium">
                            <i data-lucide="check" class="w-5 h-5 inline mr-2"></i>Check Out & Selesaikan
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

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
    
    // Handle checkout form
    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', handleCheckout);
    }
    
    // Handle cancel session
    const cancelBtn = document.getElementById('cancel-session');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', cancelSession);
    }
    
    // Handle manual QR form
    const manualForm = document.getElementById('manual-qr-form');
    if (manualForm) {
        manualForm.addEventListener('submit', handleManualQR);
    }
});

function initializeScanner() {
    document.getElementById('start-scanner').addEventListener('click', startScanner);
    document.getElementById('stop-scanner').addEventListener('click', stopScanner);
    
    // Auto-start scanner if no active session
    <?php if (!$active_session): ?>
    startScanner();
    <?php endif; ?>
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
                    processQRCode(qrData);
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

function handleManualQR(e) {
    e.preventDefault();
    const qrData = document.getElementById('manual-qr-input').value.trim();
    if (qrData) {
        processQRCode(qrData);
    }
}

function processQRCode(qrData) {
    fetch('api_mark_attendance.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ qr_code: qrData })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Check-in berhasil untuk ' + data.studentName);
            location.reload();
        } else {
            if (data.active_session) {
                if (confirm(data.message + ' Apakah ingin melanjutkan ke sesi aktif?')) {
                    // Redirect to active session
                    window.location.href = 'scan.php';
                }
            } else {
                alert('Error: ' + data.message);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat memproses QR code');
    });
}

function handleCheckout(e) {
    e.preventDefault();
    
    const studentId = document.getElementById('checkout-student-id').value;
    const nilai = document.getElementById('nilai_perkembangan').value;
    const keterangan = document.getElementById('keterangan').value;
    
    if (!nilai || !keterangan.trim()) {
        alert('Nilai dan keterangan harus diisi');
        return;
    }
    
    if (nilai < 1 || nilai > 100) {
        alert('Nilai harus antara 1-100');
        return;
    }
    
    // Disable form to prevent double submission
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const cancelBtn = document.getElementById('cancel-session');
    submitBtn.disabled = true;
    cancelBtn.disabled = true;
    submitBtn.innerHTML = '<i data-lucide="loader" class="w-5 h-5 inline mr-2 animate-spin"></i>Processing...';
    
    fetch('api_checkout_student.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            student_id: studentId,
            nilai_perkembangan: nilai,
            keterangan: keterangan
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                window.location.href = 'dashboard.php';
            }
        } else {
            alert('Error: ' + data.message);
            // Re-enable form on error
            submitBtn.disabled = false;
            cancelBtn.disabled = false;
            submitBtn.innerHTML = '<i data-lucide="check" class="w-5 h-5 inline mr-2"></i>Check Out & Selesaikan';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat checkout');
        // Re-enable form on error
        submitBtn.disabled = false;
        cancelBtn.disabled = false;
        submitBtn.innerHTML = '<i data-lucide="check" class="w-5 h-5 inline mr-2"></i>Check Out & Selesaikan';
    });
}

function cancelSession() {
    if (confirm('Yakin ingin membatalkan sesi ini?')) {
        // Clear session without saving progress
        fetch('api_cancel_session.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'dashboard.php';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan');
        });
    }
}
</script>

<?php include 'partials/footer.php'; ?>