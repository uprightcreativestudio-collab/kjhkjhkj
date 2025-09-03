
<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Schedule ID is required']);
    exit();
}

$schedule_id = $_GET['id'];

try {
    $stmt = $pdo->prepare("
        SELECT j.*, s.nama_lengkap as siswa_nama 
        FROM jadwal j 
        LEFT JOIN siswa s ON j.id = s.jadwal_id 
        WHERE j.id = ?
    ");
    $stmt->execute([$schedule_id]);
    $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$schedule) {
        http_response_code(404);
        echo json_encode(['error' => 'Schedule not found']);
        exit();
    }
    
    echo json_encode($schedule);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
