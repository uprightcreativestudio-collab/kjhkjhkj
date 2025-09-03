<?php
// Ensure clean JSON output
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require '../config/database.php';
header('Content-Type: application/json');

// Only logged-in students can check for a stream
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$siswa_id = $_SESSION['user_id'];

try {
    // Check for an active stream ID in the student's own database record
    $stmt = $pdo->prepare("SELECT active_stream_id FROM siswa WHERE id = ?");
    $stmt->execute([$siswa_id]);
    $siswa = $stmt->fetch(PDO::FETCH_ASSOC);

    $stream_id = $siswa ? $siswa['active_stream_id'] : null;

    if ($stream_id) {
        // If a stream ID is found, send it to the frontend
        echo json_encode(['success' => true, 'stream_id' => $stream_id]);
    } else {
        // If no stream is active, report that clearly
        echo json_encode(['success' => false, 'message' => 'No active stream']);
    }
} catch (PDOException $e) {
    // error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}
