<?php
include 'partials/header.php';

// Get gallery items for this student
$stmt = $pdo->prepare("
    SELECT g.*, gr.nama_lengkap as guru_name
    FROM gallery_uploads g
    JOIN guru gr ON g.guru_id = gr.id
    WHERE g.siswa_id = ?
    ORDER BY g.upload_date DESC
");
$stmt->execute([$user['id']]);
$gallery_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<title>My Gallery - Learning Materials</title>
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
                    <h1 class="font-bold text-xl text-cream drop-shadow-sm">My Gallery</h1>
                    <p class="text-sm text-cream/80 font-medium">Materi pembelajaran dari guru</p>
                </div>
            </div>
            <a href="notifikasi.php" class="relative p-3 rounded-2xl hover:bg-cream/10 transition-all duration-300 group">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-cream group-hover:scale-110 transition-transform duration-300">
                    <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
                    <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
                </svg>
                <div class="absolute top-2 right-2 w-3 h-3 bg-yellow-bright rounded-full animate-bounce-soft"></div>
            </a>
        </div>
    </header>
    
    <div class="container mx-auto p-4 pb-24">
        <?php if (empty($gallery_items)): ?>
            <div class="text-center py-16">
                <div class="glass-effect p-8 rounded-2xl">
                    <div class="w-24 h-24 bg-gradient-to-br from-pink-light to-blue-soft rounded-full flex items-center justify-center mx-auto mb-4 opacity-50">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-pink-dark">
                            <rect x="3" y="3" width="18" height="12" rx="2" ry="2"/>
                            <circle cx="9" cy="9" r="2"/>
                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-pink-dark mb-2">Gallery Kosong</h3>
                    <p class="text-pink-dark/60">Belum ada materi yang diupload oleh guru</p>
                    <p class="text-pink-dark/40 text-sm mt-2">Materi akan muncul setelah sesi kelas</p>
                </div>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($gallery_items as $item): ?>
                <div class="glass-effect rounded-2xl overflow-hidden shadow-lg card-hover">
                    <div class="relative">
                        <?php if ($item['file_type'] === 'image'): ?>
                            <img src="../<?php echo htmlspecialchars($item['file_path']); ?>" 
                                 alt="Gallery Image" class="w-full h-48 object-cover">
                            <div class="absolute top-2 right-2 bg-blue-500 text-white px-2 py-1 rounded-full text-xs">
                                <i class="fas fa-image mr-1"></i>Foto
                            </div>
                        <?php else: ?>
                            <video controls class="w-full h-48 object-cover">
                                <source src="../<?php echo htmlspecialchars($item['file_path']); ?>" type="video/mp4">
                                Browser Anda tidak mendukung video.
                            </video>
                            <div class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded-full text-xs">
                                <i class="fas fa-video mr-1"></i>Video
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="p-4">
                        <?php if ($item['description']): ?>
                            <p class="text-pink-dark font-medium mb-2"><?php echo htmlspecialchars($item['description']); ?></p>
                        <?php endif; ?>
                        
                        <div class="flex justify-between items-center text-sm text-pink-dark/70">
                            <span>
                                <i class="fas fa-user-tie mr-1"></i>
                                <?php echo htmlspecialchars($item['guru_name']); ?>
                            </span>
                            <span>
                                <i class="fas fa-calendar mr-1"></i>
                                <?php echo date('d M Y', strtotime($item['upload_date'])); ?>
                            </span>
                        </div>
                        
                        <div class="mt-3">
                            <a href="../<?php echo htmlspecialchars($item['file_path']); ?>" 
                               download 
                               class="block w-full text-center bg-gradient-to-r from-pink-accent to-pink-dark text-cream py-2 px-4 rounded-xl hover:shadow-lg transition-all">
                                <i class="fas fa-download mr-2"></i>Download
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'partials/footer.php'; ?>
</body>
</html>