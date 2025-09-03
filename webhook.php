<?php
require_once 'config/database.php';

// Add error logging
error_log("Webhook called at: " . date('Y-m-d H:i:s'));
error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
error_log("User agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'none'));

// Get the notification data from the request body
$json_notification = file_get_contents('php://input');
error_log("Webhook raw data: " . $json_notification);

// For localhost testing - allow direct browser access
if (empty($json_notification) && isset($_GET['test'])) {
    echo "Webhook endpoint is working. Ready to receive notifications.";
    exit;
}

$notification = json_decode($json_notification, true);

// Get the Midtrans server key from settings
$midtrans_server_key = get_setting($pdo, 'midtrans_server_key');

if (!$midtrans_server_key) {
    error_log("Midtrans server key not found");
    http_response_code(500);
    exit('Server configuration error');
}

if (!$notification || !isset($notification['order_id']) || !isset($notification['transaction_status'])) {
    error_log("Invalid notification data: " . print_r($notification, true));
    http_response_code(400);
    exit('Invalid notification');
}

// Handle different types of notifications (direct callback vs webhook)
$is_callback = isset($_GET['type']) && $_GET['type'] === 'merchant';

if ($is_callback) {
    // This is a direct callback from payment method like Akulaku
    error_log("Received direct callback: " . print_r($_GET, true));
    
    // Extract order_id from referenceId for Akulaku callbacks
    if (isset($_GET['referenceId'])) {
        $reference_parts = explode('-', $_GET['referenceId']);
        if (count($reference_parts) >= 2) {
            $order_id = $reference_parts[0] . '-' . $reference_parts[1] . '-' . $reference_parts[2];
            $transaction_status = 'pending'; // Default for callback
            $payment_type = $_GET['paymentType'] ?? '';
            $fraud_status = '';
        } else {
            error_log("Invalid referenceId format: " . $_GET['referenceId']);
            http_response_code(400);
            exit('Invalid reference ID');
        }
    } else {
        error_log("Missing referenceId in callback");
        http_response_code(400);
        exit('Missing reference ID');
    }
} else {
    // This is a webhook notification - validate signature
    if (isset($notification['signature_key'])) {
        require_once 'midtrans_helper.php';
use Midtrans\Config;
use Midtrans\Notification;
        
        if (!verifySignature($notification, $midtrans_server_key)) {
            error_log("Invalid signature for order: " . $notification['order_id']);
            http_response_code(403);
            exit('Invalid signature');
        }
    }
    
    $order_id = $notification['order_id'];
    $transaction_status = $notification['transaction_status'];
    $payment_type = $notification['payment_type'] ?? '';
    $fraud_status = $notification['fraud_status'] ?? '';
}

// Order ID, transaction status, payment type, and fraud status are now set above based on callback type
error_log("Processing transaction - Order ID: $order_id, Status: $transaction_status, Payment Type: $payment_type");

$status = 'pending'; // Default status

try {
    // Determine the payment status based on Midtrans transaction status and fraud status
    if ($transaction_status == 'capture') {
        if ($payment_type == 'credit_card') {
            if ($fraud_status == 'challenge') {
                $status = 'challenge'; // Payment requires further verification
            } else {
                $status = 'paid'; // Payment successfully captured
            }
        }
    } else if ($transaction_status == 'settlement') {
        $status = 'paid'; // Payment is settled
    } else if ($transaction_status == 'pending') {
        $status = 'pending'; // Payment is pending
    } else if ($transaction_status == 'deny') {
        $status = 'failed'; // Payment denied
    } else if ($transaction_status == 'expire') {
        $status = 'expired'; // Payment expired
    } else if ($transaction_status == 'cancel') {
        $status = 'failed'; // Payment cancelled
    }

    $pdo->beginTransaction(); // Start a database transaction

    // Update the 'payments' table with the new transaction details
    $stmt = $pdo->prepare("
        UPDATE payments 
        SET transaction_status = ?, 
            payment_type = ?, 
            fraud_status = ?,
            settlement_time = NOW(), -- Assuming settlement_time is relevant for all transaction types for timestamping
            updated_at = NOW()
        WHERE order_id = ?
    ");
    $stmt->execute([$status, $payment_type, $fraud_status, $order_id]);

    // If the payment is successful ('paid'), update the student's payment status
    if ($status == 'paid') {
        // Retrieve the student_id associated with this order
        $stmt = $pdo->prepare("SELECT student_id FROM payments WHERE order_id = ?");
        $stmt->execute([$order_id]);
        $payment = $stmt->fetch();

        if ($payment) {
            // Update the 'siswa' table to reflect the paid status
            $stmt = $pdo->prepare("UPDATE siswa SET status_pembayaran = 'paid' WHERE id = ?");
            $stmt->execute([$payment['student_id']]);
            
            // Auto-book the selected schedule if payment is successful
            $stmt = $pdo->prepare("
                UPDATE jadwal 
                SET is_booked = 1 
                WHERE id = (SELECT jadwal_id FROM siswa WHERE id = ?) 
                AND is_booked = 0
            ");
            $stmt->execute([$payment['student_id']]);
            
            // Get student details for WhatsApp notification
            $stmt = $pdo->prepare("
                SELECT s.nama_lengkap, s.email, p.gross_amount, j.hari, j.jam_mulai, j.jam_selesai
                FROM siswa s 
                JOIN payments p ON s.id = p.student_id 
                LEFT JOIN jadwal j ON s.jadwal_id = j.id
                WHERE s.id = ?
            ");
            $stmt->execute([$payment['student_id']]);
            $student_data = $stmt->fetch();
            
            if ($student_data) {
                // Send WhatsApp notification to admin
                require_once 'email_helper.php';
                $whatsapp_message = "🎉 PEMBAYARAN BERHASIL!\n\n";
                $whatsapp_message .= "Nama: " . $student_data['nama_lengkap'] . "\n";
                $whatsapp_message .= "Email: " . $student_data['email'] . "\n";
                $whatsapp_message .= "Order ID: " . $order_id . "\n";
                $whatsapp_message .= "Jumlah: Rp " . number_format($student_data['gross_amount'], 0, ',', '.') . "\n";
                
                if ($student_data['hari']) {
                    $whatsapp_message .= "Jadwal: " . $student_data['hari'] . " " . 
                                       date('H:i', strtotime($student_data['jam_mulai'])) . " - " . 
                                       date('H:i', strtotime($student_data['jam_selesai'])) . "\n";
                    $whatsapp_message .= "✅ Jadwal telah otomatis terkonfirmasi!\n";
                }
                
                $whatsapp_message .= "Tanggal: " . date('d F Y, H:i') . "\n\n";
                $whatsapp_message .= "Silakan konfirmasi di admin panel: https://" . $_SERVER['HTTP_HOST'] . "/admin/pendaftar.php";
                
                sendWhatsAppNotification($pdo, $whatsapp_message);
            }
        }
    }

    $pdo->commit(); // Commit the transaction if all operations were successful

    echo "OK"; // Acknowledge receipt of the notification

} catch (Exception $e) {
    $pdo->rollBack(); // Roll back the transaction in case of any error
    error_log("Webhook error: " . $e->getMessage());
    error_log("Webhook error trace: " . $e->getTraceAsString());
    http_response_code(500); // Internal Server Error
    echo "Error: " . $e->getMessage(); // Output the error message
}
?>