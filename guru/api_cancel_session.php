<?php
error_reporting(0);
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');

require '../config/database.php';

// Check if a teacher is logged in
if (!isset($_SESSION['guru_id'])) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit();
}

try {
    // Find and delete any in-progress sessions for today
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("
        DELETE FROM student_progress 
        WHERE status = 'in_progress' AND session_date = ?
    ");
    $stmt->execute([$today]);

    // Clear session data
    unset($_SESSION['scanned_student_id']);
    unset($_SESSION['scanned_student_name']);

    echo json_encode(['success' => true, 'message' => 'Sesi dibatalkan.']);

} catch (PDOException $e) {
    error_log('Database Error in api_cancel_session.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan pada database.']);
}
?>