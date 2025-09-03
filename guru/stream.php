<?php
include 'partials/header.php';

$scanned_student_id = $_SESSION['scanned_student_id'] ?? null;
$scanned_student_name = $_SESSION['scanned_student_name'] ?? null;

if (!$scanned_student_id) {
    $_SESSION['flash_message'] = "Pindai QR siswa terlebih dahulu sebelum mengakses gallery.";
    header('Location: dashboard.php');
    exit();
}

// Handle file upload
$upload_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['gallery_file'])) {
    $upload_dir = '../uploads/gallery/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $file = $_FILES['gallery_file'];
    $description = $_POST['description'] ?? '';
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'video/mp4', 'video/webm'];
        if (in_array($file['type'], $allowed_types)) {
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'gallery_' . $scanned_student_id . '_' . time() . '.' . $file_extension;
            $filepath = $upload_dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Save to database
                $stmt = $pdo->prepare("
                    INSERT INTO gallery_uploads (siswa_id, guru_id, file_path, file_type, description, upload_date) 
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $file_type = strpos($file['type'], 'image') !== false ? 'image' : 'video';
                $stmt->execute([$scanned_student_id, $_SESSION['guru_id'], 'uploads/gallery/' . $filename, $file_type, $description]);
                
                $upload_message = 'File berhasil diupload!';
            } else {
                $upload_message = 'Gagal mengupload file.';
            }
        } else {
            $upload_message = 'Tipe file tidak didukung. Gunakan JPG, PNG, MP4, atau WebM.';
        }
    } else {
        $upload_message = 'Error upload: ' . $file['error'];
    }
}

// Get existing gallery for this student
$stmt = $pdo->prepare("
    SELECT g.*, s.nama_lengkap as student_name, gr.nama_lengkap as guru_name
    FROM gallery_uploads g
    JOIN siswa s ON g.siswa_id = s.id
    JOIN guru gr ON g.guru_id = gr.id
    WHERE g.siswa_id = ?
    ORDER BY g.upload_date DESC
");
$stmt->execute([$scanned_student_id]);
$gallery_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<title>Gallery Upload - <?php echo htmlspecialchars($scanned_student_name); ?></title>
</head>
<body class="bg-gray-900 text-white min-h-screen">
    <div class="container mx-auto p-4">
        <header class="mb-6 text-center">
            <h1 class="text-3xl font-bold mb-2">Gallery Upload</h1>
            <p class="text-teal-400">Upload materi untuk: <span class="font-bold"><?php echo htmlspecialchars($scanned_student_name); ?></span></p>
        </header>
        
        <?php if ($upload_message): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo strpos($upload_message, 'berhasil') !== false ? 'bg-green-600' : 'bg-red-600'; ?>">
            <?php echo $upload_message; ?>
        </div>
        <?php endif; ?>

        <!-- Upload Form -->
        <div class="bg-gray-800 rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Upload File Baru</h2>
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Pilih File (Gambar atau Video)</label>
                    <input type="file" name="gallery_file" accept="image/*,video/mp4,video/webm" required
                           class="w-full p-3 border border-gray-600 rounded-lg bg-gray-700 text-white">
                    <p class="text-xs text-gray-400 mt-1">Format: JPG, PNG, MP4, WebM (Max 50MB)</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2">Deskripsi</label>
                    <textarea name="description" rows="3" placeholder="Deskripsi materi atau catatan..."
                              class="w-full p-3 border border-gray-600 rounded-lg bg-gray-700 text-white"></textarea>
                </div>
                
                <button type="submit" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition">
                    <i data-lucide="upload" class="w-5 h-5 inline mr-2"></i>Upload File
                </button>
            </form>
        </div>

        <!-- Gallery Grid -->
        <div class="bg-gray-800 rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Gallery Siswa</h2>
            
            <?php if (empty($gallery_items)): ?>
                <div class="text-center py-8 text-gray-400">
                    <i data-lucide="image" class="w-16 h-16 mx-auto mb-4"></i>
                    <p>Belum ada file yang diupload</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($gallery_items as $item): ?>
                    <div class="bg-gray-700 rounded-lg overflow-hidden">
                        <?php if ($item['file_type'] === 'image'): ?>
                            <img src="../<?php echo htmlspecialchars($item['file_path']); ?>" 
                                 alt="Gallery Image" class="w-full h-48 object-cover">
                        <?php else: ?>
                            <video controls class="w-full h-48 object-cover">
                                <source src="../<?php echo htmlspecialchars($item['file_path']); ?>" type="video/mp4">
                                Browser Anda tidak mendukung video.
                            </video>
                        <?php endif; ?>
                        
                        <div class="p-4">
                            <p class="text-sm text-gray-300 mb-2"><?php echo htmlspecialchars($item['description']); ?></p>
                            <div class="flex justify-between items-center text-xs text-gray-400">
                                <span>By: <?php echo htmlspecialchars($item['guru_name']); ?></span>
                                <span><?php echo date('d M Y', strtotime($item['upload_date'])); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Back Button -->
        <div class="mt-6 text-center">
            <a href="dashboard.php" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg transition">
                <i data-lucide="arrow-left" class="w-5 h-5 inline mr-2"></i>Kembali ke Dashboard
            </a>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
    
    <?php include 'partials/footer.php'; ?>
</body>
</html>