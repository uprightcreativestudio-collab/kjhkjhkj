
<?php
require_once 'config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$email = $_POST['email'] ?? '';

if (empty($email)) {
    echo json_encode(['has_pending' => false]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT p.order_id, p.transaction_status, s.nama_lengkap 
        FROM payments p 
        JOIN siswa s ON p.student_id = s.id 
        WHERE s.email = ? AND p.transaction_status IN ('pending', 'challenge')
        ORDER BY p.created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $pending_payment = $stmt->fetch();
    
    if ($pending_payment) {
        echo json_encode([
            'has_pending' => true,
            'order_id' => $pending_payment['order_id'],
            'nama_lengkap' => $pending_payment['nama_lengkap']
        ]);
    } else {
        echo json_encode(['has_pending' => false]);
    }
    
} catch (Exception $e) {
    error_log("Check pending payment error: " . $e->getMessage());
    echo json_encode(['has_pending' => false]);
}
?>
