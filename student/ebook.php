<?php 
include 'partials/header.php'; 

$ebooks = $pdo->query("SELECT * FROM ebooks ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<title>E-Book</title>
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
                        <img src="<?php echo htmlspecialchars($user['foto_profil'] ?? 'assets/images/mask_group.svg'); ?>" alt="Profile" class="h-16 w-16 rounded-2xl border-3 border-cream/50 object-cover shadow-lg group-hover:scale-105 transition-transform duration-300" />
                        <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-yellow-bright rounded-full border-2 border-cream animate-pulse-soft"></div>
                    </div>
                </a>
                <div class="ml-4">
                    <h1 class="font-bold text-xl text-cream drop-shadow-sm">Ebook</h1>
                    <p class="text-sm text-cream/80 font-medium"> Learning materials available for you.</p>
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
       
        <?php if (empty($ebooks)): ?>
            <div class="text-center text-gray-500 py-16">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-x mx-auto h-12 w-12 text-gray-400 mb-4"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/><path d="m14.5 10.5-5 5"/><path d="m9.5 10.5 5 5"/></svg>
                <p>No e-books are available at the moment.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <?php foreach ($ebooks as $ebook): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden flex">
                    <img src="<?php echo htmlspecialchars('../' . $ebook['gambar_cover'] ?? 'assets/images/mask_group_2.svg'); ?>" alt="Cover" class="w-1/3 h-auto object-cover">
                    <div class="p-4 flex flex-col justify-between">
                        <div>
                            <h3 class="font-bold text-gray-800"><?php echo htmlspecialchars($ebook['judul']); ?></h3>
                            <p class="text-sm text-gray-600 mt-1 line-clamp-2"><?php echo htmlspecialchars($ebook['deskripsi']); ?></p>
                        </div>
                        <a href="<?php echo htmlspecialchars('../' . $ebook['file_path']); ?>" download class="mt-4 inline-block bg-cyan-500 text-white text-center text-sm font-semibold py-2 px-4 rounded-lg hover:bg-cyan-600 transition">
                            Download
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php include 'partials/footer.php'; ?>
</body>
</html>
