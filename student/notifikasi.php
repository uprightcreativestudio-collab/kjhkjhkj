<?php 
include 'partials/header.php'; 

// Fetch notifications for the user
$stmt = $pdo->prepare("SELECT * FROM notifikasi_siswa WHERE siswa_id = ? ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
$notifikasi_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mark notifications as read (optional, can be done via AJAX on open)
$pdo->prepare("UPDATE notifikasi_siswa SET is_read = 1 WHERE siswa_id = ?")->execute([$user['id']]);

?>
<title>Notifications</title>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4 pb-24">
        <header class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800">Notifications</h1>
            <a href="dashboard.php" class="text-cyan-600 text-sm font-medium">Back to Home</a>
        </header>

        <?php if (empty($notifikasi_list)): ?>
            <div class="text-center text-gray-500 py-16">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bell-off mx-auto h-12 w-12 text-gray-400 mb-4"><path d="M8.7 3A6 6 0 0 1 18 8a21.3 21.3 0 0 0 .6 5"/><path d="M17 17H3s3-2 3-9a4.67 4.67 0 0 1 .3-1.7"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/><path d="m2 2 20 20"/></svg>
                <p>You have no notifications.</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($notifikasi_list as $notif): ?>
                <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-cyan-400">
                    <p class="font-bold text-gray-800"><?php echo htmlspecialchars($notif['judul']); ?></p>
                    <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($notif['pesan']); ?></p>
                    <p class="text-xs text-gray-400 mt-2 text-right"><?php echo date('d M Y, H:i', strtotime($notif['created_at'])); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php include 'partials/footer.php'; ?>
</body>
</html>
