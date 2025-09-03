<?php
error_reporting(0);
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');

require '../config/database.php';

// Check if a teacher is logged in
if (!isset($_SESSION['guru_id'])) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Silakan login terlebih dahulu.']);
    exit();
}

// Get the QR code from the POST request body
$data = json_decode(file_get_contents('php://input'), true);
$qr_code = $data['qr_code'] ?? null;

if (!$qr_code) {
    echo json_encode(['success' => false, 'message' => 'QR code tidak valid atau tidak ada.']);
    exit();
}

try {
    // Check if there's already an active session for this teacher
    $stmt = $pdo->prepare("
        SELECT sp.*, s.nama_lengkap 
        FROM student_progress sp
        JOIN siswa s ON sp.siswa_id = s.id
        WHERE sp.status = 'in_progress' 
        AND DATE(sp.session_date) = CURDATE()
        LIMIT 1
    ");
    $stmt->execute();
    $active_session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($active_session) {
        echo json_encode([
            'success' => false, 
            'message' => 'Masih ada sesi aktif dengan ' . $active_session['nama_lengkap'] . '. Selesaikan sesi tersebut terlebih dahulu.',
            'active_session' => true,
            'active_student_id' => $active_session['siswa_id'],
            'active_student_name' => $active_session['nama_lengkap']
        ]);
        exit();
    }

    // Find the student associated with the scanned QR code
    $stmt = $pdo->prepare("SELECT id, nama_lengkap FROM siswa WHERE qr_code_identifier = ?");
    $stmt->execute([$qr_code]);
    $siswa = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$siswa) {
        echo json_encode(['success' => false, 'message' => 'Siswa dengan QR code ini tidak ditemukan.']);
        exit();
    }

    $siswa_id = $siswa['id'];
    $siswa_nama = $siswa['nama_lengkap'];

    // Check if student already has a session today
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("
        SELECT * FROM student_progress 
        WHERE siswa_id = ? AND session_date = ?
        ORDER BY created_at DESC LIMIT 1
    ");
    $stmt->execute([$siswa_id, $today]);
    $existing_session = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_session && $existing_session['status'] == 'completed') {
        echo json_encode(['success' => false, 'message' => 'Siswa ' . $siswa_nama . ' sudah menyelesaikan sesi hari ini.']);
        exit();
    }

    // Create new session
    $stmt = $pdo->prepare("
        INSERT INTO student_progress (siswa_id, session_date, session_time, checkin_time, status) 
        VALUES (?, ?, ?, NOW(), 'in_progress')
    ");
    $stmt->execute([$siswa_id, $today, date('H:i:s')]);

    // Also update absensi table
    $stmt = $pdo->prepare("
        INSERT INTO absensi (siswa_id, tanggal, waktu, keterangan) 
        VALUES (?, ?, ?, 'Hadir')
        ON DUPLICATE KEY UPDATE waktu = VALUES(waktu), keterangan = VALUES(keterangan)
    ");
    $stmt->execute([$siswa_id, $today, date('H:i:s')]);

    // Store the scanned student's details in the teacher's session
    $_SESSION['scanned_student_id'] = $siswa_id;
    $_SESSION['scanned_student_name'] = $siswa_nama;

    echo json_encode([
        'success' => true, 
        'studentName' => $siswa_nama,
        'studentId' => $siswa_id,
        'message' => 'Check-in berhasil untuk ' . $siswa_nama
    ]);

} catch (PDOException $e) {
    error_log('Database Error in api_mark_attendance.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan pada database. Silakan coba lagi.']);
}
?>