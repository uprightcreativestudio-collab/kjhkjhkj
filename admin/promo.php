<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    if ($action == 'add') {
        $kode_promo = $_POST['kode_promo'];
        $nama_promo = $_POST['nama_promo'];
        $harga_kursus = $_POST['harga_kursus'];
        $biaya_pendaftaran = $_POST['biaya_pendaftaran'];
        $deskripsi = $_POST['deskripsi'];
        $status = $_POST['status'];
        $max_usage = $_POST['max_usage'] ?? null;
        
        $stmt = $pdo->prepare("INSERT INTO promo_codes (kode_promo, nama_promo, harga_kursus, biaya_pendaftaran, deskripsi, status, max_usage, current_usage) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
        $stmt->execute([$kode_promo, $nama_promo, $harga_kursus, $biaya_pendaftaran, $deskripsi, $status, $max_usage]);
        $_SESSION['flash_message'] = "Promo berhasil ditambahkan.";
    } elseif ($action == 'edit') {
        $id = $_POST['id'];
        $kode_promo = $_POST['kode_promo'];
        $nama_promo = $_POST['nama_promo'];
        $harga_kursus = $_POST['harga_kursus'];
        $biaya_pendaftaran = $_POST['biaya_pendaftaran'];
        $deskripsi = $_POST['deskripsi'];
        $status = $_POST['status'];
        $max_usage = $_POST['max_usage'] ?? null;
        
        $stmt = $pdo->prepare("UPDATE promo_codes SET kode_promo = ?, nama_promo = ?, harga_kursus = ?, biaya_pendaftaran = ?, deskripsi = ?, status = ?, max_usage = ? WHERE id = ?");
        $stmt->execute([$kode_promo, $nama_promo, $harga_kursus, $biaya_pendaftaran, $deskripsi, $status, $max_usage, $id]);
        $_SESSION['flash_message'] = "Promo berhasil diperbarui.";
    } elseif ($action == 'delete') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM promo_codes WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['flash_message'] = "Promo berhasil dihapus.";
    }
    
    header("Location: promo.php");
    exit();
}

// Get all promos
$stmt = $pdo->query("SELECT * FROM promo_codes ORDER BY created_at DESC");
$promos = $stmt->fetchAll();

include 'partials/header.php';
?>

<div class="container mx-auto px-4 md:px-6 py-8">
    <div class="flex flex-col space-y-4 md:flex-row md:justify-between md:items-center md:space-y-0 mb-6">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Kelola Kode Promo</h1>
        <button onclick="openModal('add')" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm md:text-base">
            <i class="fas fa-plus mr-2"></i>Tambah Promo
        </button>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?>
        </div>
    <?php endif; ?>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Promo</th>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Nama Promo</th>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Kursus</th>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Biaya Daftar</th>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Limit Penggunaan</th>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($promos as $promo): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 md:px-6 py-4 text-sm font-medium text-gray-900">
                            <div class="font-bold"><?php echo htmlspecialchars($promo['kode_promo']); ?></div>
                            <div class="text-xs text-gray-500 sm:hidden"><?php echo htmlspecialchars($promo['nama_promo']); ?></div>
                        </td>
                        <td class="px-3 md:px-6 py-4 text-sm text-gray-900 hidden sm:table-cell">
                            <?php echo htmlspecialchars($promo['nama_promo']); ?>
                        </td>
                        <td class="px-3 md:px-6 py-4 text-sm text-gray-900">
                            <div class="font-medium">Rp <?php echo number_format($promo['harga_kursus'], 0, ',', '.'); ?></div>
                            <div class="text-xs text-gray-500 md:hidden">
                                Daftar: Rp <?php echo number_format($promo['biaya_pendaftaran'], 0, ',', '.'); ?>
                            </div>
                        </td>
                        <td class="px-3 md:px-6 py-4 text-sm text-gray-900 hidden md:table-cell">
                            Rp <?php echo number_format($promo['biaya_pendaftaran'], 0, ',', '.'); ?>
                        </td>
                        <td class="px-3 md:px-6 py-4 text-sm text-gray-900 hidden lg:table-cell">
                            <?php if ($promo['max_usage']): ?>
                                <?php echo $promo['current_usage']; ?> / <?php echo $promo['max_usage']; ?>
                                <?php if ($promo['current_usage'] >= $promo['max_usage']): ?>
                                    <span class="text-red-500 text-xs">(Habis)</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-gray-500">Unlimited</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 md:px-6 py-4">
                            <?php 
                            $status_class = 'bg-red-100 text-red-800';
                            $status_text = 'Nonaktif';
                            
                            if ($promo['status'] == 'aktif') {
                                if ($promo['max_usage'] && $promo['current_usage'] >= $promo['max_usage']) {
                                    $status_class = 'bg-yellow-100 text-yellow-800';
                                    $status_text = 'Habis';
                                } else {
                                    $status_class = 'bg-green-100 text-green-800';
                                    $status_text = 'Aktif';
                                }
                            }
                            ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $status_class; ?>">
                                <?php echo $status_text; ?>
                            </span>
                            <div class="text-xs text-gray-500 lg:hidden mt-1">
                                <?php if ($promo['max_usage']): ?>
                                    <?php echo $promo['current_usage']; ?>/<?php echo $promo['max_usage']; ?>
                                <?php else: ?>
                                    Unlimited
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-3 md:px-6 py-4 text-sm font-medium">
                            <div class="flex flex-col space-y-1 md:flex-row md:space-y-0 md:space-x-2">
                                <button onclick="editPromo(<?php echo htmlspecialchars(json_encode($promo)); ?>)" class="text-indigo-600 hover:text-indigo-900 text-xs md:text-sm">Edit</button>
                                <button onclick="deletePromo(<?php echo $promo['id']; ?>)" class="text-red-600 hover:text-red-900 text-xs md:text-sm">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="promoModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
        <div class="bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full max-h-screen overflow-y-auto"></div>
            <form id="promoForm" method="POST">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 id="modalTitle" class="text-lg leading-6 font-medium text-gray-900 mb-4">Tambah Promo</h3>
                    
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="id" id="promoId">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kode Promo</label>
                            <input type="text" name="kode_promo" id="kode_promo" required class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Promo</label>
                            <input type="text" name="nama_promo" id="nama_promo" required class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Harga Kursus</label>
                            <input type="number" name="harga_kursus" id="harga_kursus" required class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Biaya Pendaftaran</label>
                            <input type="number" name="biaya_pendaftaran" id="biaya_pendaftaran" required class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="200000">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Batas Maksimal Penggunaan</label>
                            <input type="number" name="max_usage" id="max_usage" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Kosongkan untuk unlimited">
                            <p class="text-xs text-gray-500 mt-1">Kosongkan jika ingin unlimited usage</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" id="status" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Non-aktif</option>
                            </select>
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsi" rows="3" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-col-reverse space-y-2 space-y-reverse sm:flex-row sm:space-y-0 sm:space-x-2 sm:justify-end">
                    <button type="button" onclick="closeModal()" class="w-full sm:w-auto inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-6 py-3 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Batal
                    </button>
                    <button type="submit" class="w-full sm:w-auto inline-flex justify-center rounded-md border border-transparent shadow-sm px-6 py-3 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
@media (max-width: 768px) {
    /* Mobile table adjustments */
    .table-responsive {
        font-size: 14px;
    }
    
    /* Modal adjustments for mobile */
    #promoModal .bg-white {
        margin: 1rem;
        max-height: calc(100vh - 2rem);
    }
    
    /* Better button spacing on mobile */
    .mobile-buttons {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    /* Prevent body scroll when modal is open */
    body.modal-open {
        overflow: hidden;
    }
}

/* Ensure modal content is scrollable */
#promoModal .bg-white {
    overflow-y: auto;
}
</style>

<script>
function openModal(action) {
    document.getElementById('promoModal').classList.remove('hidden');
    document.getElementById('formAction').value = action;
    document.getElementById('modalTitle').textContent = action === 'add' ? 'Tambah Promo' : 'Edit Promo';
    
    // Prevent body scroll when modal is open
    document.body.classList.add('modal-open');
    
    if (action === 'add') {
        document.getElementById('promoForm').reset();
        document.getElementById('biaya_pendaftaran').value = '200000';
    }
}

function closeModal() {
    document.getElementById('promoModal').classList.add('hidden');
    
    // Restore body scroll
    document.body.classList.remove('modal-open');
}

function editPromo(promo) {
    openModal('edit');
    document.getElementById('promoId').value = promo.id;
    document.getElementById('kode_promo').value = promo.kode_promo;
    document.getElementById('nama_promo').value = promo.nama_promo;
    document.getElementById('harga_kursus').value = promo.harga_kursus;
    document.getElementById('biaya_pendaftaran').value = promo.biaya_pendaftaran;
    document.getElementById('max_usage').value = promo.max_usage || '';
    document.getElementById('status').value = promo.status;
    document.getElementById('deskripsi').value = promo.deskripsi || '';
}

function deletePromo(id) {
    if (confirm('Apakah Anda yakin ingin menghapus promo ini?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include 'partials/footer.php'; ?>
