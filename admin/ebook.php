<?php
$page_title = 'Manajemen E-Book';
include 'partials/header.php';

// Handle Delete Action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $stmt_select = $pdo->prepare("SELECT file_path, gambar_cover FROM ebooks WHERE id = ?");
    $stmt_select->execute([$id]);
    $ebook = $stmt_select->fetch(PDO::FETCH_ASSOC);

    if ($ebook) {
        if ($ebook['file_path'] && file_exists($ebook['file_path'])) unlink($ebook['file_path']);
        if ($ebook['gambar_cover'] && file_exists($ebook['gambar_cover'])) unlink($ebook['gambar_cover']);
        
        $stmt_delete = $pdo->prepare("DELETE FROM ebooks WHERE id = ?");
        $stmt_delete->execute([$id]);
        
        $_SESSION['flash_message'] = "E-Book berhasil dihapus.";
    }
    header("Location: ebook.php");
    exit();
}

$ebook_list = $pdo->query("SELECT * FROM ebooks ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<header class="bg-white shadow-sm p-4 flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-800">Manajemen E-Book</h1>
    <a href="ebook_form.php" class="bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 flex items-center">
        <i class="fas fa-plus mr-2"></i>Tambah E-Book
    </a>
</header>

<main class="flex-1 p-6 bg-gray-50">
    <?php if (empty($ebook_list)): ?>
        <div class="text-center py-10">
            <p class="text-gray-500">Belum ada e-book. Silakan tambahkan e-book baru.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($ebook_list as $ebook): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden group">
                <div class="relative">
                    <img src="<?php echo htmlspecialchars($ebook['gambar_cover'] ?? '../uploads/ebook_covers/default.png'); ?>" alt="Cover E-Book" class="h-56 w-full object-cover">
                    <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <a href="<?php echo htmlspecialchars($ebook['file_path']); ?>" download class="text-white mx-2 transform hover:scale-110" title="Unduh"><i class="fas fa-download fa-2x"></i></a>
                        <a href="ebook_form.php?id=<?php echo $ebook['id']; ?>" class="text-white mx-2 transform hover:scale-110" title="Edit"><i class="fas fa-edit fa-2x"></i></a>
                        <a href="ebook.php?action=delete&id=<?php echo $ebook['id']; ?>" onclick="return confirm('Yakin ingin menghapus e-book ini?')" class="text-white mx-2 transform hover:scale-110" title="Hapus"><i class="fas fa-trash fa-2x"></i></a>
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="font-semibold text-lg text-gray-800 truncate"><?php echo htmlspecialchars($ebook['judul']); ?></h3>
                    <p class="text-sm text-gray-600 mt-1 h-10 overflow-hidden"><?php echo htmlspecialchars($ebook['deskripsi']); ?></p>
                    <p class="text-xs text-gray-400 mt-2">Diupload: <?php echo date('d M Y', strtotime($ebook['tanggal_upload'])); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php include 'partials/footer.php'; ?>
