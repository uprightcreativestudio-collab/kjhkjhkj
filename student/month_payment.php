<?php
require '../config/database.php';
include 'partials/header.php';

// Fetch payment history for the user
$stmt = $pdo->prepare("SELECT * FROM tagihan WHERE siswa_id = ? ORDER BY tanggal_terbit DESC");
$stmt->execute([$user['id']]);
$tagihan_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<title>Payment History</title>
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
                    <p class="text-sm text-cream/80 font-medium"> View your monthly course bills</p>
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
        <div class="space-y-4">
            <?php if (empty($tagihan_list)): ?>
                <div class="text-center text-gray-500 py-16">
                    <p>No payment history found.</p>
                </div>
            <?php else: ?>
                <?php foreach ($tagihan_list as $tagihan): ?>
                    <div class="bg-white rounded-lg shadow-md p-4 flex items-center justify-between">
                        <div>
                            <p class="font-bold text-gray-800">Bill for Month #<?php echo htmlspecialchars($tagihan['bulan_ke']); ?></p>
                            <p class="text-sm text-gray-600">Invoice: <?php echo htmlspecialchars($tagihan['invoice_kode']); ?></p>
                            <p class="text-lg font-semibold text-gray-900 mt-1">Rp <?php echo number_format($tagihan['jumlah'], 0, ',', '.'); ?></p>
                        </div>
                        <div class="text-right space-y-2">
                            <?php if ($tagihan['status'] == 'Lunas'): ?>
                                <div>
                                    <span class="px-3 py-1 text-xs font-semibold text-green-800 bg-green-200 rounded-full">Paid</span>
                                </div>
                                <div>
                                    <a href="invoice.php?type=tagihan&id=<?php echo $tagihan['id']; ?>" 
                                       class="inline-block px-3 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700" target="_blank">
                                        <i class="fas fa-file-pdf mr-1"></i>Invoice
                                    </a>
                                </div>
                            <?php else: ?>
                                <a href="select_payment.php?invoice=<?php echo $tagihan['invoice_kode']; ?>" class="px-4 py-2 text-sm font-medium text-white bg-cyan-600 rounded-lg hover:bg-cyan-700">
                                    Pay Now
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php include 'partials/footer.php'; ?>
</body>
</html>
