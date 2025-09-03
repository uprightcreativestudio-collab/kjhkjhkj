
<?php
include 'config/database.php';
include 'push_notification_helper.php';

header('Content-Type: application/json');

try {
    $pushHelper = new PushNotificationHelper($pdo);
    $publicKey = $pushHelper->getVapidPublicKey();
    
    echo json_encode([
        'success' => true,
        'publicKey' => $publicKey
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to get VAPID key: ' . $e->getMessage()
    ]);
}
?>
