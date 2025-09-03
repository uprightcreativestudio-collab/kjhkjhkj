<?php
require_once 'config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_promo = $_POST['kode_promo'] ?? '';
    
    if (empty($kode_promo)) {
        echo json_encode(['valid' => false, 'message' => 'Kode promo tidak boleh kosong']);
        exit;
    }
    
    try {
        // Check if promo exists and is active
        $stmt = $pdo->prepare("SELECT * FROM promo_codes WHERE kode_promo = ? AND status = 'aktif'");
        $stmt->execute([$kode_promo]);
        $promo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$promo) {
            echo json_encode(['valid' => false, 'message' => 'Kode promo tidak valid atau sudah tidak aktif']);
            exit;
        }
        
        // Check usage limit if set
        if ($promo['max_usage'] !== null && $promo['current_usage'] >= $promo['max_usage']) {
            echo json_encode(['valid' => false, 'message' => 'Kode promo sudah mencapai batas maksimal penggunaan']);
            exit;
        }
        
        $total = (int)$promo['harga_kursus'] + (int)$promo['biaya_pendaftaran'];
        
        echo json_encode([
            'valid' => true,
            'message' => 'Kode promo valid',
            'nama_promo' => $promo['nama_promo'],
            'harga_kursus' => (int)$promo['harga_kursus'],
            'biaya_pendaftaran' => (int)$promo['biaya_pendaftaran'],
            'total_formatted' => number_format($total, 0, ',', '.'),
            'remaining_usage' => $promo['max_usage'] ? ($promo['max_usage'] - $promo['current_usage']) : null
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['valid' => false, 'message' => 'Terjadi kesalahan sistem']);
    }
} else {
    echo json_encode(['valid' => false, 'message' => 'Invalid request method']);
}
?>
