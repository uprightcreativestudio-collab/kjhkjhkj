<?php
$page_title = 'Manajemen Ijin Siswa';
include 'partials/header.php';

// Handle status update
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = $_GET['id'];
    $status = '';

    if ($action == 'approve') {
        $status = 'disetujui';
    } elseif ($action == 'reject') {
        $status = 'ditolak';
    }

    if ($status) {
        $stmt = $pdo->prepare("UPDATE ijin SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        $_SESSION['flash_message'] = "Status ijin berhasil diperbarui.";
        header("Location: ijin_siswa.php");
        exit();
    }
}

// Fetch leave requests
$ijin_list = $pdo->query("
    SELECT i.id, s.nama_lengkap, i.tanggal_ijin, i.alasan, i.status, i.tanggal_pengajuan
    FROM ijin i
    JOIN siswa s ON i.siswa_id = s.id
    ORDER BY i.tanggal_pengajuan DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<header class="bg-white shadow-sm p-4">
    <h1 class="text-2xl font-semibold text-gray-800">Manajemen Ijin Siswa</h1>
</header>

<main class="flex-1 p-6">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold mb-4">Daftar Permintaan Ijin</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-2 px-4 text-left text-xs font-medium text-gray-500 uppercase">Nama Siswa</th>
                        <th class="py-2 px-4 text-left text-xs font-medium text-gray-500 uppercase">Tgl Ijin</th>
                        <th class="py-2 px-4 text-left text-xs font-medium text-gray-500 uppercase">Alasan</th>
                        <th class="py-2 px-4 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="py-2 px-4 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($ijin_list)): ?>
                        <tr>
                            <td colspan="5" class="py-3 px-4 text-center text-gray-500">Belum ada permintaan ijin.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ijin_list as $ijin): ?>
                        <tr>
                            <td class="py-3 px-4 whitespace-nowrap"><?php echo htmlspecialchars($ijin['nama_lengkap']); ?></td>
                            <td class="py-3 px-4 whitespace-nowrap"><?php echo date('d M Y', strtotime($ijin['tanggal_ijin'])); ?></td>
                            <td class="py-3 px-4 max-w-xs truncate"><?php echo htmlspecialchars($ijin['alasan']); ?></td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <?php
                                $status_color = 'bg-yellow-200 text-yellow-800';
                                if ($ijin['status'] == 'disetujui') $status_color = 'bg-green-200 text-green-800';
                                if ($ijin['status'] == 'ditolak') $status_color = 'bg-red-200 text-red-800';
                                ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $status_color; ?>">
                                    <?php echo ucfirst($ijin['status']); ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <?php if ($ijin['status'] == 'pending'): ?>
                                    <a href="ijin_siswa.php?action=approve&id=<?php echo $ijin['id']; ?>" class="text-green-600 hover:text-green-900 mr-3"><i class="fas fa-check"></i> Setujui</a>
                                    <a href="ijin_siswa.php?action=reject&id=<?php echo $ijin['id']; ?>" class="text-red-600 hover:text-red-900"><i class="fas fa-times"></i> Tolak</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
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
