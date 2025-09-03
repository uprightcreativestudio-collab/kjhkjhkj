<?php
// This file acts as a backend endpoint to generate the Midtrans Snap token.
header('Content-Type: application/json');
require '../config/database.php';
// Removed: require '../vendor/autoload.php'; // Midtrans PHP library via Composer
// Removed: use Midtrans\Config;
// Removed: use Midtrans\Snap;

// Using native implementation, include the helper file
require '../midtrans_helper.php';

session_start();

// Basic security check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Authentication required.']);
    exit;
}

$invoice_kode = $_GET['invoice'] ?? '';
$user_id = $_SESSION['user_id'];

if (empty($invoice_kode)) {
    echo json_encode(['error' => 'Invoice code is required.']);
    exit;
}

// Fetch invoice and user details
$stmt = $pdo->prepare("
    SELECT t.invoice_kode, t.jumlah, s.nama_lengkap, s.email, s.telepon
    FROM tagihan t
    JOIN siswa s ON t.siswa_id = s.id
    WHERE t.invoice_kode = ? AND t.siswa_id = ? AND t.status = 'Belum Lunas'
");
$stmt->execute([$invoice_kode, $user_id]);
$invoice_details = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$invoice_details) {
    echo json_encode(['error' => 'Invoice not found or already paid.']);
    exit;
}

// Get Midtrans keys from database
$stmt = $pdo->query("SELECT server_key, client_key, is_production FROM midtrans_settings WHERE id = 1");
$midtrans_settings = $stmt->fetch(PDO::FETCH_ASSOC);

$server_key = $midtrans_settings['server_key'] ?? null;
$client_key = $midtrans_settings['client_key'] ?? null;
$is_production = (bool)($midtrans_settings['is_production'] ?? false);

if (!$server_key || !$client_key) {
    echo json_encode(['error' => 'Midtrans API keys are not configured in the admin panel.']);
    exit;
}

// Configure Midtrans (not strictly needed for native function but good practice to have settings available)
// Config::$serverKey = $server_key;
// Config::$isProduction = $is_production;
// Config::$isSanitized = true;
// Config::$is3ds = true;

// Calculate tax for invoice amount (12%)
$amount_base = $invoice_details['jumlah']; // Base amount without tax
$total_amount = $invoice_details['jumlah']; // Total including tax

// Prepare transaction details for Midtrans
$params = [
    'transaction_details' => [
        'order_id' => $invoice_details['invoice_kode'],
        'gross_amount' => (int)$total_amount,
    ],
    'customer_details' => [
        'first_name' => $invoice_details['nama_lengkap'],
        'email' => $invoice_details['email'],
        'phone' => $invoice_details['telepon'],
    ],
    'item_details' => [
        [
            'id' => 'tagihan_bulanan',
            'price' => (int)$amount_base,
            'quantity' => 1,
            'name' => 'Tagihan Bulanan'
        ]
    ],
    // 'enabled_payments' => [ // Moved to native function call
    //     'qris',
    //     'bca_va', 'bni_va', 'bri_va', 'permata_va'
    // ]
];

try {
    // Get enabled payment methods
    $enabled_payments = ['qris', 'bca_va', 'bni_va', 'bri_va', 'permata_va'];

    $snapToken = createSnapToken($params, $server_key, $enabled_payments, $is_production);

    if ($snapToken) {
        echo json_encode([
            'success' => true,
            'snap_token' => $snapToken,
            'client_key' => $client_key,
            'invoice_details' => $invoice_details
        ]);
    } else {
        echo json_encode(['error' => 'Failed to create snap token.']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Failed to create payment token: ' . $e->getMessage()]);
}
?>