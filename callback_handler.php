<?php
require_once 'config/database.php';

// Log callback request
error_log("Callback handler called at: " . date('Y-m-d H:i:s'));
error_log("Callback GET params: " . print_r($_GET, true));

// Handle different payment method callbacks
$payment_type = $_GET['paymentType'] ?? '';
$reference_id = $_GET['referenceId'] ?? '';
$merchant_id = $_GET['merchantId'] ?? '';

if (empty($reference_id)) {
    error_log("Missing referenceId in callback");
    http_response_code(400);
    exit('Missing reference ID');
}

// Extract order_id from referenceId
$reference_parts = explode('-', $reference_id);
if (count($reference_parts) >= 3) {
    $order_id = $reference_parts[0] . '-' . $reference_parts[1] . '-' . $reference_parts[2];
} else {
    error_log("Invalid referenceId format: " . $reference_id);
    http_response_code(400);
    exit('Invalid reference ID format');
}

try {
    // Check if payment exists in database
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $payment = $stmt->fetch();
    
    if (!$payment) {
        error_log("Payment not found for order: " . $order_id);
        http_response_code(404);
        exit('Payment not found');
    }
    
    // For callbacks, we should verify the actual transaction status with Midtrans
    $midtrans_server_key = get_setting($pdo, 'midtrans_server_key');
    
    if ($midtrans_server_key) {
        require_once 'midtrans_helper.php';
        
        try {
            // Get production mode setting
            $stmt = $pdo->prepare("SELECT is_production FROM midtrans_settings WHERE id = 1");
            $stmt->execute();
            $is_production = $stmt->fetchColumn();
            $is_production = (bool)$is_production; // Convert to boolean
            
            $transaction_data = getTransactionStatus($order_id, $midtrans_server_key, $is_production);
            
            $transaction_status = $transaction_data['transaction_status'] ?? 'pending';
            $payment_type_actual = $transaction_data['payment_type'] ?? $payment_type;
            $fraud_status = $transaction_data['fraud_status'] ?? '';
            
            // Determine status
            $status = 'pending';
            if ($transaction_status == 'settlement' || $transaction_status == 'capture') {
                $status = 'paid';
            } elseif ($transaction_status == 'deny' || $transaction_status == 'cancel') {
                $status = 'failed';
            } elseif ($transaction_status == 'expire') {
                $status = 'expired';
            }
            
            // Update payment record
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("
                UPDATE payments 
                SET transaction_status = ?, 
                    payment_type = ?, 
                    fraud_status = ?,
                    settlement_time = NOW(),
                    updated_at = NOW()
                WHERE order_id = ?
            ");
            $stmt->execute([$status, $payment_type_actual, $fraud_status, $order_id]);
            
            // Update student status if paid
            if ($status == 'paid') {
                $stmt = $pdo->prepare("UPDATE siswa SET status_pembayaran = 'paid' WHERE id = ?");
                $stmt->execute([$payment['student_id']]);
            }
            
            $pdo->commit();
            
            error_log("Payment updated successfully for order: " . $order_id . " with status: " . $status);
            
            // Handle redirect for browser-based payments
            if (isset($_GET['redirect']) && $_GET['redirect'] === 'true') {
                if ($status === 'paid') {
                    header("Location: /finish_payment.php?order_id=" . urlencode($order_id) . "&transaction_status=" . urlencode($transaction_status));
                } else {
                    header("Location: /error_payment.php?order_id=" . urlencode($order_id) . "&transaction_status=" . urlencode($transaction_status));
                }
                exit;
            }
            
            echo "OK";
            
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Error checking transaction status: " . $e->getMessage());
            
            // Still acknowledge the callback to prevent retries
            echo "OK";
        }
    } else {
        error_log("Midtrans server key not configured");
        echo "OK"; // Acknowledge to prevent retries
    }
    
} catch (Exception $e) {
    error_log("Callback error: " . $e->getMessage());
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
?>
