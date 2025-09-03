
<?php
session_start();
include 'config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['subscription']) || !isset($input['user_type'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required data']);
    exit();
}

$subscription = $input['subscription'];
$userType = $input['user_type'];

// Get user ID based on session and user type
$userId = null;
if ($userType === 'student' && isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
} elseif ($userType === 'guru' && isset($_SESSION['guru_id'])) {
    $userId = $_SESSION['guru_id'];
} elseif ($userType === 'admin' && isset($_SESSION['admin_id'])) {
    $userId = $_SESSION['admin_id'];
}

if (!$userId) {
    http_response_code(401);
    echo json_encode(['error' => 'User not authenticated']);
    exit();
}

try {
    // Extract subscription data
    $endpoint = $subscription['endpoint'];
    $keys = $subscription['keys'];
    $p256dhKey = $keys['p256dh'];
    $authKey = $keys['auth'];

    // Save or update subscription
    $stmt = $pdo->prepare("
        INSERT INTO push_subscriptions (user_id, user_type, endpoint, p256dh_key, auth_key) 
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
        endpoint = VALUES(endpoint), 
        p256dh_key = VALUES(p256dh_key), 
        auth_key = VALUES(auth_key),
        updated_at = CURRENT_TIMESTAMP
    ");
    
    $stmt->execute([$userId, $userType, $endpoint, $p256dhKey, $authKey]);
    
    echo json_encode(['success' => true, 'message' => 'Subscription saved successfully']);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
