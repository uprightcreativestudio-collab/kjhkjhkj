<?php
// Ensure clean JSON output
error_reporting(0);
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');

require '../config/database.php';

// Only logged-in teachers can update stream status
if (!isset($_SESSION['guru_id'])) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? null;
$student_id = $data['student_id'] ?? null;
$stream_id = $data['stream_id'] ?? null; // Only needed for 'start' action

if (!$action || !$student_id) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
    exit();
}

try {
    if ($action === 'start') {
        if (!$stream_id) {
            echo json_encode(['success' => false, 'message' => 'Stream ID dibutuhkan untuk memulai.']);
            exit();
        }
        // Set the active stream ID for the specific student
        $stmt = $pdo->prepare("UPDATE siswa SET active_stream_id = ? WHERE id = ?");
        $stmt->execute([$stream_id, $student_id]);
    } elseif ($action === 'end') {
        // Clear the active stream ID for the student
        $stmt = $pdo->prepare("UPDATE siswa SET active_stream_id = NULL WHERE id = ?");
        $stmt->execute([$student_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);
        exit();
    }

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    // error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}
