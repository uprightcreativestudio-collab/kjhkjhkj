
<?php
class PushNotificationHelper {
    private $pdo;
    private $vapidPublicKey;
    private $vapidPrivateKey;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->loadVapidKeys();
    }
    
    private function loadVapidKeys() {
        $stmt = $this->pdo->query("SELECT public_key, private_key FROM vapid_keys ORDER BY id DESC LIMIT 1");
        $keys = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($keys) {
            $this->vapidPublicKey = $keys['public_key'];
            $this->vapidPrivateKey = $keys['private_key'];
        } else {
            // Generate new VAPID keys if not exist
            $this->generateVapidKeys();
        }
    }
    
    private function generateVapidKeys() {
        // Generate proper VAPID keys for web push
        $keyPair = openssl_pkey_new([
            'curve_name' => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ]);
        
        $details = openssl_pkey_get_details($keyPair);
        $this->vapidPublicKey = base64url_encode($details['ec']['x'] . $details['ec']['y']);
        
        openssl_pkey_export($keyPair, $privateKeyPem);
        $this->vapidPrivateKey = base64url_encode($privateKeyPem);
        
        // Save to database
        $stmt = $this->pdo->prepare("INSERT INTO vapid_keys (public_key, private_key) VALUES (?, ?)");
        $stmt->execute([$this->vapidPublicKey, $this->vapidPrivateKey]);
    }
    
    public function getVapidPublicKey() {
        return $this->vapidPublicKey;
    }
    
    public function sendNotification($userId, $userType, $title, $body, $url = null) {
        try {
            // Get user's push subscription
            $stmt = $this->pdo->prepare("
                SELECT endpoint, p256dh_key, auth_key 
                FROM push_subscriptions 
                WHERE user_id = ? AND user_type = ?
            ");
            $stmt->execute([$userId, $userType]);
            $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$subscription) {
                return false; // No subscription found
            }
            
            $payload = json_encode([
                'title' => $title,
                'body' => $body,
                'url' => $url ?: '/user/dashboard.php'
            ]);
            
            return $this->sendPushNotification(
                $subscription['endpoint'],
                $subscription['p256dh_key'],
                $subscription['auth_key'],
                $payload
            );
            
        } catch (Exception $e) {
            error_log("Push notification error: " . $e->getMessage());
            return false;
        }
    }
    
    private function sendPushNotification($endpoint, $p256dh, $auth, $payload) {
        // Improved push notification sending with proper headers
        $headers = [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload),
            'TTL: 86400',
            'Urgency: normal'
        ];
        
        // Add authorization header if needed
        if (strpos($endpoint, 'fcm.googleapis.com') !== false) {
            // For FCM endpoints, we need proper authorization
            $headers[] = 'Authorization: key=' . $this->vapidPrivateKey;
        }
        
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $endpoint,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true
        ]);
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log("Push notification cURL error: " . $error);
            return false;
        }
        
        if ($httpCode < 200 || $httpCode >= 300) {
            error_log("Push notification failed with HTTP code: " . $httpCode . ", Response: " . $result);
            return false;
        }
        
        return $httpCode >= 200 && $httpCode < 300;
    }
}

// Helper function for base64url encoding
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    
    public function sendToAllStudents($title, $body, $url = null) {
        $stmt = $this->pdo->query("
            SELECT DISTINCT user_id 
            FROM push_subscriptions 
            WHERE user_type = 'student'
        ");
        
        $sent = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($this->sendNotification($row['user_id'], 'student', $title, $body, $url)) {
                $sent++;
            }
        }
        
        return $sent;
    }
}
?>
