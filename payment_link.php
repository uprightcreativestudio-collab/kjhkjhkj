<?php
session_start();
require_once 'config/database.php';

// Get order_id from URL parameter
$order_id = $_GET['order_id'] ?? '';
$email = $_GET['email'] ?? '';

if (empty($order_id)) {
    header('Location: index.php?error=' . urlencode('Link pembayaran tidak valid'));
    exit;
}

// Verify order exists and get payment details
$stmt = $pdo->prepare("
    SELECT p.*, s.nama_lengkap, s.email, s.id as student_id, s.kode_promo, pr.nama_promo 
    FROM payments p 
    JOIN siswa s ON p.student_id = s.id 
    LEFT JOIN promo_codes pr ON s.kode_promo = pr.kode_promo
    WHERE p.order_id = ?
");
$stmt->execute([$order_id]);
$payment = $stmt->fetch();

if (!$payment) {
    header('Location: index.php?error=' . urlencode('Order ID tidak ditemukan'));
    exit;
}

// If email is provided, verify it matches
if (!empty($email) && $payment['email'] !== $email) {
    header('Location: index.php?error=' . urlencode('Email tidak cocok dengan order ID'));
    exit;
}

// Check payment status
if ($payment['transaction_status'] === 'paid') {
    header('Location: finish_payment.php?order_id=' . urlencode($order_id) . '&transaction_status=settlement');
    exit;
}

// Redirect to payment page with order_id
header('Location: payment_page.php?order_id=' . urlencode($order_id));
exit;
?>
