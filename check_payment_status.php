<?php
require_once 'config/database.php';

header('Content-Type: application/json');

$order_id = $_GET['order_id'] ?? '';

if (empty($order_id)) {
    echo json_encode(['error' => 'Order ID required']);
    exit;
}

try {
    // Get current status from database - using only existing columns
    $stmt = $pdo->prepare("SELECT transaction_status, payment_type FROM payments WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $payment = $stmt->fetch();
    
    if (!$payment) {
        echo json_encode(['error' => 'Payment not found']);
        exit;
    }
    
    // If already paid, no need to check Midtrans
    if ($payment['transaction_status'] === 'paid') {
        echo json_encode(['status' => 'paid']);
        exit;
    }
    
    // Check with Midtrans for real-time status - only if midtrans_settings table exists
    try {
        $stmt = $pdo->prepare("SELECT server_key, is_production FROM midtrans_settings WHERE id = 1");
        $stmt->execute();
        $midtrans_setting = $stmt->fetch();
        
        if ($midtrans_setting && !empty($midtrans_setting['server_key'])) {
            require_once 'midtrans_helper.php';
            
            try {
                $is_production = (bool)$midtrans_setting['is_production'];
                $transaction_data = getTransactionStatus($order_id, $midtrans_setting['server_key'], $is_production);
                
                $midtrans_status = $transaction_data['transaction_status'] ?? 'pending';
                $fraud_status = $transaction_data['fraud_status'] ?? '';
                
                // Update database with latest status
                $final_status = 'pending';
                if ($midtrans_status == 'settlement' || $midtrans_status == 'capture') {
                    $final_status = 'paid';
                } elseif ($midtrans_status == 'deny' || $midtrans_status == 'cancel') {
                    $final_status = 'failed';
                } elseif ($midtrans_status == 'expire') {
                    $final_status = 'expired';
                }
                
                // Update payment status if different
                if ($final_status !== $payment['transaction_status']) {
                    $pdo->beginTransaction();
                    
                    $stmt = $pdo->prepare("
                        UPDATE payments 
                        SET transaction_status = ?, 
                            fraud_status = ?,
                            updated_at = NOW()
                        WHERE order_id = ?
                    ");
                    $stmt->execute([$final_status, $fraud_status, $order_id]);
                    
                    // Update student status if paid
                    if ($final_status == 'paid') {
                        $stmt = $pdo->prepare("
                            UPDATE siswa s 
                            JOIN payments p ON s.id = p.student_id 
                            SET s.status_pembayaran = 'paid' 
                            WHERE p.order_id = ?
                        ");
                        $stmt->execute([$order_id]);
                    }
                    
                    $pdo->commit();
                }
                
                echo json_encode([
                    'status' => $final_status,
                    'midtrans_status' => $midtrans_status,
                    'fraud_status' => $fraud_status
                ]);
                
            } catch (Exception $e) {
                error_log("Error checking Midtrans status: " . $e->getMessage());
                echo json_encode(['status' => $payment['transaction_status']]);
            }
        } else {
            echo json_encode(['status' => $payment['transaction_status']]);
        }
    } catch (Exception $e) {
        // If midtrans_settings table doesn't exist, just return current status
        error_log("Midtrans settings not found: " . $e->getMessage());
        echo json_encode(['status' => $payment['transaction_status']]);
    }
    
} catch (Exception $e) {
    error_log("Payment status check error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error']);
}
?>