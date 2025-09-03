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

// Get the data from POST request
$data = json_decode(file_get_contents('php://input'), true);
$student_id = $data['student_id'] ?? null;
$nilai_perkembangan = $data['nilai_perkembangan'] ?? null;
$keterangan = $data['keterangan'] ?? '';

if (!$student_id || !$nilai_perkembangan) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
    exit();
}

// Validate nilai_perkembangan range
if ($nilai_perkembangan < 1 || $nilai_perkembangan > 100) {
    echo json_encode(['success' => false, 'message' => 'Nilai harus antara 1-100.']);
    exit();
}

try {
    // Find active session for this student today
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("
        SELECT * FROM student_progress 
        WHERE siswa_id = ? AND session_date = ? AND status = 'in_progress'
        ORDER BY created_at DESC LIMIT 1
    ");
    $stmt->execute([$student_id, $today]);
    $active_session = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$active_session) {
        echo json_encode(['success' => false, 'message' => 'Tidak ada sesi aktif untuk siswa ini.']);
        exit();
    }

    // Update session with checkout data
    $stmt = $pdo->prepare("
        UPDATE student_progress 
        SET checkout_time = NOW(), 
            nilai_perkembangan = ?, 
            keterangan = ?, 
            status = 'completed' 
        WHERE id = ?
    ");
    $result = $stmt->execute([$nilai_perkembangan, $keterangan, $active_session['id']]);
    
    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan data checkout.']);
        exit();
    }

    // Clear session data
    unset($_SESSION['scanned_student_id']);
    unset($_SESSION['scanned_student_name']);

    echo json_encode([
        'success' => true, 
        'message' => 'Check-out berhasil. Sesi telah diselesaikan.'
    ]);

} catch (PDOException $e) {
    error_log('Database Error in api_checkout_student.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan pada database. Silakan coba lagi.']);
}
?>