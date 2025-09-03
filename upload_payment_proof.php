<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$order_id = $_POST['order_id'] ?? '';
$notes = $_POST['notes'] ?? '';

if (empty($order_id)) {
    echo json_encode(['success' => false, 'message' => 'Order ID is required']);
    exit;
}

// Validate file upload
if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'File upload failed']);
    exit;
}

$file = $_FILES['payment_proof'];
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
$max_size = 5 * 1024 * 1024; // 5MB

// Validate file type
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and PDF allowed']);
    exit;
}

// Validate file size
if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'File too large. Maximum 5MB allowed']);
    exit;
}

try {
    // Check if payment exists
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $payment = $stmt->fetch();
    
    if (!$payment) {
        echo json_encode(['success' => false, 'message' => 'Payment record not found']);
        exit;
    }
    
    // Create upload directory if not exists
    $upload_dir = 'uploads/payment_proofs/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $order_id . '_' . time() . '.' . $file_extension;
    $file_path = $upload_dir . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $file_path)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save file']);
        exit;
    }
    
    // Update payment record
    $stmt = $pdo->prepare("
        UPDATE payments 
        SET payment_proof_path = ?, 
            payment_proof_notes = ?, 
            transaction_status = 'manual_pending',
            updated_at = NOW()
        WHERE order_id = ?
    ");
    $stmt->execute([$file_path, $notes, $order_id]);
    
    // Create manual payment verification record
    $stmt = $pdo->prepare("
        INSERT INTO manual_payment_verification 
        (order_id, payment_proof_path, notes, status, created_at) 
        VALUES (?, ?, ?, 'pending', NOW())
        ON DUPLICATE KEY UPDATE 
        payment_proof_path = VALUES(payment_proof_path),
        notes = VALUES(notes),
        status = 'pending',
        updated_at = NOW()
    ");
    $stmt->execute([$order_id, $file_path, $notes]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Payment proof uploaded successfully'
    ]);
    
} catch (Exception $e) {
    error_log("Upload payment proof error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>
