<?php
// Ensure clean JSON output by suppressing any potential PHP warnings/errors.
error_reporting(0);
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');

require '../config/database.php';

// Check if a teacher is logged in.
if (!isset($_SESSION['guru_id'])) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Silakan login terlebih dahulu.']);
    exit();
}

// Get the QR code from the POST request body.
$data = json_decode(file_get_contents('php://input'), true);
$qr_code = $data['qr_code'] ?? null;

if (!$qr_code) {
    echo json_encode(['success' => false, 'message' => 'QR code tidak valid atau tidak ada.']);
    exit();
}

try {
    // Find the student associated with the scanned QR code.
    $stmt = $pdo->prepare("SELECT id, nama_lengkap FROM siswa WHERE qr_code_identifier = ?");
    $stmt->execute([$qr_code]);
    $siswa = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$siswa) {
        echo json_encode(['success' => false, 'message' => 'Siswa dengan QR code ini tidak ditemukan.']);
        exit();
    }

    $siswa_id = $siswa['id'];
    $siswa_nama = $siswa['nama_lengkap'];

    // ** THE FIX IS HERE: This query now matches the corrected database schema. **
    // It inserts the attendance for the day, and if the student scans again, it just updates the time.
    $stmt = $pdo->prepare(
        "INSERT INTO absensi (siswa_id, tanggal, waktu) VALUES (?, CURDATE(), CURTIME()) 
         ON DUPLICATE KEY UPDATE waktu = CURTIME()"
    );
    $stmt->execute([$siswa_id]);

    // Store the scanned student's details in the teacher's session. This is crucial for the next step (streaming).
    $_SESSION['scanned_student_id'] = $siswa_id;
    $_SESSION['scanned_student_name'] = $siswa_nama;

    // Send a clean success response back to the browser.
    echo json_encode(['success' => true, 'studentName' => $siswa_nama]);

} catch (PDOException $e) {
    // Log the actual error to a file for debugging, but don't show it to the user.
    error_log('Database Error in api_mark_attendance.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan pada database. Silakan coba lagi.']);
}
