<?php
session_start();
require '../config/database.php';
require '../email_helper.php';

header('Content-Type: application/json');

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$order_id = $input['order_id'] ?? '';
$action = $input['action'] ?? '';

if (empty($order_id) || empty($action)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    if ($action === 'approve') {
        // Update payment status to paid
        $stmt = $pdo->prepare("
            UPDATE payments 
            SET transaction_status = 'paid', 
                payment_type = 'manual_approval',
                settlement_time = NOW(),
                updated_at = NOW()
            WHERE order_id = ? AND transaction_status = 'pending'
        ");
        $stmt->execute([$order_id]);
        
        if ($stmt->rowCount() > 0) {
            // Update student status
            $stmt = $pdo->prepare("
                UPDATE siswa s
                JOIN payments p ON s.id = p.student_id
                SET s.status_pembayaran = 'paid'
                WHERE p.order_id = ?
            ");
            $stmt->execute([$order_id]);
            
            // Auto-book the selected schedule if payment is approved
            $stmt = $pdo->prepare("
                UPDATE jadwal 
                SET is_booked = 1 
                WHERE id = (
                    SELECT s.jadwal_id 
                    FROM siswa s 
                    JOIN payments p ON s.id = p.student_id 
                    WHERE p.order_id = ?
                ) AND is_booked = 0
            ");
            $stmt->execute([$order_id]);
            
            // Get student details for notification
            $stmt = $pdo->prepare("
                SELECT s.nama_lengkap, s.email, p.gross_amount, j.hari, j.jam_mulai, j.jam_selesai
                FROM siswa s 
                JOIN payments p ON s.id = p.student_id 
                LEFT JOIN jadwal j ON s.jadwal_id = j.id
                WHERE p.order_id = ?
            ");
            $stmt->execute([$order_id]);
            $student_data = $stmt->fetch();
            
            if ($student_data) {
                // Send WhatsApp notification to admin
                $whatsapp_message = "✅ PEMBAYARAN DISETUJUI MANUAL!\n\n";
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
                
                $whatsapp_message .= "Disetujui oleh: Admin\n";
                $whatsapp_message .= "Tanggal: " . date('d F Y, H:i') . "\n\n";
                $whatsapp_message .= "Status pembayaran siswa telah diupdate.";
                
                sendWhatsAppNotification($pdo, $whatsapp_message);
            }
            
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Payment approved successfully']);
        } else {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Payment not found or already processed']);
        }
        
    } elseif ($action === 'reject') {
        // Update payment status to failed
        $stmt = $pdo->prepare("
            UPDATE payments 
            SET transaction_status = 'failed', 
                payment_type = 'manual_rejection',
                updated_at = NOW()
            WHERE order_id = ? AND transaction_status = 'pending'
        ");
        $stmt->execute([$order_id]);
        
        if ($stmt->rowCount() > 0) {
            // Update student status
            $stmt = $pdo->prepare("
                UPDATE siswa s
                JOIN payments p ON s.id = p.student_id
                SET s.status_pembayaran = 'failed'
                WHERE p.order_id = ?
            ");
            $stmt->execute([$order_id]);
            
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Payment rejected successfully']);
        } else {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Payment not found or already processed']);
        }
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Payment approval error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
