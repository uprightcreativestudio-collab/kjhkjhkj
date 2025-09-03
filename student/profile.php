<?php 
require '../config/database.php';
include 'partials/header.php'; 
?>
<title>User Profile</title>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4 pb-24">
        <div class="w-full max-w-sm mx-auto">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex flex-col items-center">
                    <img class="w-24 h-24 rounded-full border-4 border-cyan-400 object-cover" src="<?php echo htmlspecialchars($user['foto_profil'] ? '../' . $user['foto_profil'] : '../avaaset/logo-ava.png'); ?>" alt="Profile Picture">
                    <h2 class="text-xl font-bold mt-4"><?php echo htmlspecialchars($user['nama_lengkap']); ?></h2>
                    <p class="text-gray-500"><?php echo htmlspecialchars($user['email']); ?></p>
                </div>

                <div class="mt-8 border-t pt-6 space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-500">Full Name</span>
                        <span class="text-gray-800 font-semibold text-right"><?php echo htmlspecialchars($user['nama_lengkap']); ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-500">Phone</span>
                        <span class="text-gray-800 font-semibold"><?php echo htmlspecialchars($user['telepon'] ?? 'Not set'); ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-500">Start Date</span>
                        <span class="text-gray-800 font-semibold"><?php echo date('d M Y', strtotime($user['tanggal_mulai'])); ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-500">Duration</span>
                        <span class="text-gray-800 font-semibold"><?php echo htmlspecialchars($user['durasi_bulan']); ?> months</span>
                    </div>
                </div>

                <div class="mt-6 border-t pt-6">
                    <h3 class="text-md font-bold text-gray-700 mb-2">My Schedule</h3>
                    <?php if ($user['hari']): ?>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($user['hari']); ?></p>
                        <p class="text-sm text-gray-600"><?php echo date('H:i', strtotime($user['jam_mulai'])); ?> - <?php echo date('H:i', strtotime($user['jam_selesai'])); ?></p>
                    </div>
                    <?php else: ?>
                    <p class="text-sm text-gray-500">Your schedule is not set yet.</p>
                    <?php endif; ?>
                </div>

                <div class="mt-8">
                    <a href="logout.php" class="w-full text-center bg-red-500 text-white py-2.5 rounded-lg hover:bg-red-600 transition-colors font-semibold">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php include 'partials/footer.php'; ?>
</body>
</html>
