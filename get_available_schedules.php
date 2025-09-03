<?php
require_once 'config/database.php';

header('Content-Type: application/json');

try {
    // Get available schedules - using original database structure
    $stmt = $pdo->prepare("
        SELECT id, hari, jam_mulai, jam_selesai 
        FROM jadwal 
        WHERE is_booked = 0 
        ORDER BY 
            CASE hari 
                WHEN 'Senin' THEN 1
                WHEN 'Selasa' THEN 2 
                WHEN 'Rabu' THEN 3
                WHEN 'Kamis' THEN 4
                WHEN 'Jumat' THEN 5
                WHEN 'Sabtu' THEN 6
                WHEN 'Minggu' THEN 7
            END,
            jam_mulai
    ");
    
    $stmt->execute();
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($schedules);
    
} catch (Exception $e) {
    error_log("Error getting schedules: " . $e->getMessage());
    echo json_encode([]);
}
?>
