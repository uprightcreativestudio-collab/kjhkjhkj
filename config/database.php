<?php
$host = 'localhost';
$dbname = 'u371035293_anastasyaputri';
$username = 'u371035293_anastasyaputri';
$password = 'AME2025mantap!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please contact the administrator.");
}

// Safe function to get settings that handles missing tables/columns
if (!function_exists('get_setting')) {
    function get_setting($pdo, $key, $default = null) {
    try {
        $stmt = $pdo->prepare("SELECT value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetchColumn();
        return $result !== false ? $result : $default;
    } catch (Exception $e) {
        error_log("Settings error for key '$key': " . $e->getMessage());

        // Return default values for common settings
        $defaults = [
            'harga_kursus_standar' => 800000,
            'biaya_pendaftaran_standar' => 200000,
            'midtrans_server_key' => null
        ];

        return isset($defaults[$key]) ? $defaults[$key] : $default;
    }
    }
}
?>