<?php
$page_title = 'Manajemen Tagihan';
include 'partials/header.php';

// Handle Status Update
if (isset($_GET['action']) && $_GET['action'] == 'update_status' && isset($_GET['id']) && isset($_GET['status'])) {
    $id = $_GET['id'];
    $status = $_GET['status'];
    if ($status == 'Lunas' || $status == 'Belum Lunas') {
        $stmt = $pdo->prepare("UPDATE tagihan SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        $_SESSION['flash_message'] = "Status tagihan berhasil diperbarui.";
    }
    header("Location: tagihan.php");
    exit();
}

$tagihan_list = $pdo->query("
    SELECT tagihan.*, siswa.nama_lengkap 
    FROM tagihan 
    JOIN siswa ON tagihan.siswa_id = siswa.id 
    ORDER BY tagihan.tanggal_terbit DESC, tagihan.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<header class="bg-white shadow-sm p-4">
    <h1 class="text-2xl font-semibold text-gray-800">Manajemen Tagihan</h1>
</header>

<main class="flex-1 p-6">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold mb-4">Daftar Semua Tagihan</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-2 px-4 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                        <th class="py-2 px-4 text-left text-xs font-medium text-gray-500 uppercase">Nama Siswa</th>
                        <th class="py-2 px-4 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                        <th class="py-2 px-4 text-left text-xs font-medium text-gray-500 uppercase">Tgl Terbit</th>
                        <th class="py-2 px-4 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="py-2 px-4 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($tagihan_list)): ?>
                        <tr>
                            <td colspan="6" class="py-3 px-4 text-center text-gray-500">Belum ada tagihan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tagihan_list as $tagihan): ?>
                        <tr>
                            <td class="py-3 px-4 whitespace-nowrap font-mono text-sm"><?php echo htmlspecialchars($tagihan['invoice_kode']); ?></td>
                            <td class="py-3 px-4 whitespace-nowrap"><?php echo htmlspecialchars($tagihan['nama_lengkap']); ?></td>
                            <td class="py-3 px-4 whitespace-nowrap">Rp <?php echo number_format($tagihan['jumlah'], 0, ',', '.'); ?></td>
                            <td class="py-3 px-4 whitespace-nowrap"><?php echo date('d M Y', strtotime($tagihan['tanggal_terbit'])); ?></td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php echo $tagihan['status'] == 'Lunas' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo htmlspecialchars($tagihan['status']); ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-4">
                                    <?php if ($tagihan['status'] == 'Belum Lunas'): ?>
                                        <a href="tagihan.php?action=update_status&id=<?php echo $tagihan['id']; ?>&status=Lunas" onclick="return confirm('Ubah status menjadi Lunas?')" class="text-green-600 hover:text-green-900" title="Tandai Lunas (Manual)">
                                            <i class="fas fa-check-circle"></i>
                                        </a>
                                    <?php else: ?>
                                         <a href="tagihan.php?action=update_status&id=<?php echo $tagihan['id']; ?>&status=Belum Lunas" onclick="return confirm('Ubah status menjadi Belum Lunas?')" class="text-yellow-600 hover:text-yellow-900" title="Batal Lunas (Manual)">
                                            <i class="fas fa-times-circle"></i>
                                         </a>
                                    <?php endif; ?>
                                    <a href="invoice.php?type=tagihan&id=<?php echo $tagihan['id']; ?>" class="text-red-600 hover:text-red-900" title="Download Invoice" target="_blank">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>



<?php include 'partials/footer.php'; ?>
