<?php
require '../config/database.php';
include 'partials/header.php';

// Fetch all payment history for the user - both registration and monthly
$stmt = $pdo->prepare("
    SELECT 
        'registration' as type,
        p.id as payment_id,
        p.order_id as reference,
        p.gross_amount as amount,
        p.transaction_status as status,
        p.payment_type,
        COALESCE(p.transaction_time, p.created_at) as date,
        'Pembayaran Pendaftaran' as description
    FROM payments p 
    WHERE p.student_id = ?
    
    UNION ALL
    
    SELECT 
        'monthly' as type,
        t.id as payment_id,
        t.invoice_kode as reference,
        t.jumlah as amount,
        CASE 
            WHEN t.status = 'Lunas' THEN 'paid'
            ELSE 'pending'
        END as status,
        'Manual/Transfer' as payment_type,
        t.tanggal_terbit as date,
        CONCAT('Pembayaran Bulan ke-', t.bulan_ke) as description
    FROM tagihan t
    WHERE t.siswa_id = ?
    
    ORDER BY date DESC
");
$stmt->execute([$user['id'], $user['id']]);
$payment_history = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total paid
$total_paid = 0;
foreach ($payment_history as $payment) {
    if ($payment['status'] == 'paid' || $payment['status'] == 'settlement') {
        $total_paid += $payment['amount'];
    }
}

// Get pending bills count
$stmt_pending = $pdo->prepare("SELECT COUNT(*) FROM tagihan WHERE siswa_id = ? AND status = 'Belum Lunas'");
$stmt_pending->execute([$user['id']]);
$pending_bills = $stmt_pending->fetchColumn();
?>

<title>Payment History - AVA</title>
</head>

<body class="bg-gray-100">
    <header class="relative bg-gradient-to-br from-pink-accent via-pink-dark to-pink-light rounded-b-[35px] shadow-2xl p-6 text-cream z-10 mb-5 animate-slide-in">
        <div class="absolute inset-0 bg-gradient-to-br from-pink-accent/90 to-pink-dark/90 rounded-b-[35px] backdrop-blur-sm"></div>
        <div class="absolute top-0 right-0 w-32 h-32 bg-yellow-bright/20 rounded-full -translate-y-16 translate-x-16 animate-pulse-soft"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-blue-soft/20 rounded-full translate-y-12 -translate-x-12 animate-float"></div>

        <div class="relative flex items-center justify-between">
            <div class="flex items-center">
            <a href="profile.php" class="group">
                    <div class="relative">
                    <img class="w-16 h-16 rounded-full border-2 border-white-400 object-cover" src="<?php echo htmlspecialchars($user['foto_profil'] ? '../' . $user['foto_profil'] : '../avaaset/logo-ava.png'); ?>" alt="Profile Picture">      
                    </div>
                </a>
                <div class="ml-4">
                    <h1 class="font-bold text-xl text-cream drop-shadow-sm">Payment History</h1>
                    <p class="text-sm text-cream/80 font-medium"> <?php echo htmlspecialchars($user['qr_code_identifier']); ?></p>
                </div>
            </div>
            <a href="notifikasi.php" class="relative p-3 rounded-2xl hover:bg-cream/10 transition-all duration-300 group">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-cream group-hover:scale-110 transition-transform duration-300">
                    <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                    <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
                </svg>
                <div class="absolute top-2 right-2 w-3 h-3 bg-yellow-bright rounded-full animate-bounce-soft"></div>
            </a>
        </div>
    </header>

    <div class="container mx-auto p-4 pb-24">


        <!-- Payment Summary Cards -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-gradient-to-r from-green-400 to-green-600 p-4 rounded-lg text-white">
                <div class="text-sm opacity-90">Total Paid</div>
                <div class="text-xl font-bold">Rp <?php echo number_format($total_paid, 0, ',', '.'); ?></div>
            </div>
            <div class="bg-gradient-to-r from-orange-400 to-orange-600 p-4 rounded-lg text-white">
                <div class="text-sm opacity-90">Pending Bills</div>
                <div class="text-xl font-bold"><?php echo $pending_bills; ?> bills</div>
            </div>
        </div>

        <!-- Payment History List -->
        <div class="space-y-4">
            <?php if (empty($payment_history)): ?>
                <div class="text-center text-gray-500 py-16">
                    <div class="mb-4">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                    </div>
                    <p>No payment history found.</p>
                </div>
            <?php else: ?>
                <?php foreach ($payment_history as $payment): ?>
                    <div class="bg-white rounded-lg shadow-md p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <?php if ($payment['type'] == 'registration'): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                            Registration
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                            Monthly
                                        </span>
                                    <?php endif; ?>

                                    <?php
                                    $status_class = '';
                                    $status_text = '';
                                    switch ($payment['status']) {
                                        case 'paid':
                                        case 'settlement':
                                            $status_class = 'bg-green-100 text-green-800';
                                            $status_text = 'Paid';
                                            break;
                                        case 'pending':
                                            $status_class = 'bg-yellow-100 text-yellow-800';
                                            $status_text = 'Pending';
                                            break;
                                        case 'failed':
                                        case 'expired':
                                            $status_class = 'bg-red-100 text-red-800';
                                            $status_text = 'Failed';
                                            break;
                                        default:
                                            $status_class = 'bg-gray-100 text-gray-800';
                                            $status_text = ucfirst($payment['status']);
                                    }
                                    ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?php echo $status_class; ?>">
                                        <?php echo $status_text; ?>
                                    </span>
                                </div>

                                <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($payment['description']); ?></p>
                                <p class="text-sm text-gray-600">
                                    <?php echo htmlspecialchars($payment['reference']); ?>
                                    <?php if ($payment['payment_type']): ?>
                                        • <?php echo htmlspecialchars($payment['payment_type']); ?>
                                    <?php endif; ?>
                                </p>
                                <p class="text-xs text-gray-500">
                                    <?php echo date('d M Y, H:i', strtotime($payment['date'])); ?>
                                </p>
                            </div>

                            <div class="text-right">
                                <p class="text-lg font-bold text-gray-900">
                                    Rp <?php echo number_format($payment['amount'], 0, ',', '.'); ?>
                                </p>

                                <div class="mt-2 space-y-1">
                                    <?php if ($payment['type'] == 'monthly' && $payment['status'] == 'pending'): ?>
                                        <a href="select_payment.php?invoice=<?php echo $payment['reference']; ?>"
                                            class="inline-block px-3 py-1 text-xs font-medium text-white bg-cyan-600 rounded hover:bg-cyan-700">
                                            Pay Now
                                        </a>
                                        <br>
                                    <?php endif; ?>

                                    <?php if (($payment['type'] == 'registration' && in_array($payment['status'], ['settlement', 'capture', 'paid'])) ||
                                        ($payment['type'] == 'monthly' && $payment['status'] == 'paid')
                                    ): ?>
                                        <a href="invoice.php?type=<?php echo $payment['type'] == 'registration' ? 'payment' : 'tagihan'; ?>&id=<?php echo $payment['payment_id']; ?>"
                                            class="inline-block px-3 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700" target="_blank">
                                            <i class="fas fa-file-pdf mr-1"></i>Invoice
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 bg-white rounded-lg shadow-md p-4">
            <h3 class="text-lg font-semibold mb-3">Quick Actions</h3>
            <div class="grid grid-cols-2 gap-3">
                <a href="month_payment.php" class="flex items-center justify-center p-3 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Monthly Bills
                </a>
                <a href="dashboard.php" class="flex items-center justify-center p-3 bg-green-50 text-green-700 rounded-lg hover:bg-green-100">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5v10l4-2 4 2V5a2 2 0 00-2-2H10a2 2 0 00-2 2z" />
                    </svg>
                    Dashboard
                </a>
            </div>
        </div>
    </div>

    <?php include 'partials/footer.php'; ?>
</body>

</html>