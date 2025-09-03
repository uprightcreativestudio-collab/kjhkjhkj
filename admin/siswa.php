<?php
$page_title = 'Manajemen Siswa';
include 'partials/header.php';

// Handle Delete Action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Get foto_profil to delete file
    $stmt_select = $pdo->prepare("SELECT foto_profil FROM siswa WHERE id = ?");
    $stmt_select->execute([$id]);
    $siswa = $stmt_select->fetch(PDO::FETCH_ASSOC);

    if ($siswa && $siswa['foto_profil'] && file_exists($siswa['foto_profil'])) {
        unlink($siswa['foto_profil']);
    }

    $stmt = $pdo->prepare("DELETE FROM siswa WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['flash_message'] = "Siswa berhasil dihapus.";
    header("Location: siswa.php");
    exit();
}

$siswa_list = $pdo->query("SELECT * FROM siswa ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<header class="bg-white shadow-sm p-4 flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-800">Manajemen Siswa</h1>
    <a href="siswa_form.php" class="bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 flex items-center">
        <i class="fas fa-plus mr-2"></i>Tambah Siswa
    </a>
</header>

<main class="flex-1 p-6 bg-gray-50">
    <?php if (empty($siswa_list)): ?>
        <div class="text-center py-10">
            <p class="text-gray-500">Belum ada siswa. Silakan tambahkan siswa baru.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php foreach ($siswa_list as $siswa): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden transform hover:-translate-y-1 transition-transform duration-300">
                <div class="p-4 flex items-center">
                    <img src="<?php echo htmlspecialchars($siswa['foto_profil'] ? '../' . $siswa['foto_profil'] : '../avaaset/logo-ava.png'); ?>" alt="Foto Profil" class="w-16 h-16 rounded-full object-cover mr-4 border-2 border-indigo-200">
                    <div>
                        <h3 class="font-semibold text-lg text-gray-800"><?php echo htmlspecialchars($siswa['nama_lengkap']); ?></h3>
                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($siswa['email']); ?></p>
                    </div>
                </div>
                <div class="px-4 pb-4 border-t border-gray-200">
                    <div class="text-sm text-gray-600 mt-3">
                        <p><i class="fas fa-money-bill-wave w-4 mr-2 text-green-500"></i> Rp <?php echo number_format($siswa['biaya_per_bulan'], 0, ',', '.'); ?>/bulan</p>
                        <p><i class="fas fa-clock w-4 mr-2 text-blue-500"></i> <?php echo htmlspecialchars($siswa['durasi_bulan']); ?> bulan</p>
                    </div>
                </div>
                <div class="bg-gray-50 p-3 flex justify-around">
                    <a href="siswa_id_card.php?id=<?php echo $siswa['id']; ?>" class="text-gray-600 hover:text-purple-600" title="Cetak ID Card">
                        <i class="fas fa-id-card fa-lg"></i>
                    </a>
                    <a href="siswa_qr.php?id=<?php echo $siswa['id']; ?>" class="text-gray-600 hover:text-indigo-600" title="Tampilkan QR Code">
                        <i class="fas fa-qrcode fa-lg"></i>
                    </a>
                    <a href="absensi.php?siswa_id=<?php echo $siswa['id']; ?>" class="text-gray-600 hover:text-indigo-600" title="Lihat Absensi">
                        <i class="fas fa-user-check fa-lg"></i>
                    </a>
                    <a href="siswa_form.php?id=<?php echo $siswa['id']; ?>" class="text-gray-600 hover:text-indigo-600" title="Edit Siswa">
                        <i class="fas fa-edit fa-lg"></i>
                    </a>
                    <a href="siswa.php?action=delete&id=<?php echo $siswa['id']; ?>" onclick="return confirm('Yakin ingin menghapus siswa ini?')" class="text-gray-600 hover:text-red-600" title="Hapus Siswa">
                        <i class="fas fa-trash fa-lg"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php include 'partials/footer.php'; ?>
