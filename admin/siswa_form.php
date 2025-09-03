
<?php
$page_title = 'Form Siswa';
include 'partials/header.php';

// --- CONFIGURATION ---
$upload_dir = '../uploads/profil/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$id = $_GET['id'] ?? null;
$is_edit = $id !== null;
$siswa = null;
$error = '';
$success = '';

if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM siswa WHERE id = ?");
    $stmt->execute([$id]);
    $siswa = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    $telepon = $_POST['telepon'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $jadwal_id = $_POST['jadwal_id'] ?? null;
    $durasi_bulan = $_POST['durasi_bulan'] ?? 1;
    $biaya_per_bulan = $_POST['biaya_per_bulan'] ?? 500000;
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $status_pembayaran = $_POST['status_pembayaran'];
    $current_foto = $_POST['current_foto'] ?? null;

    // Additional fields from new structure
    $nama_panggilan = $_POST['nama_panggilan'] ?? '';
    $tempat_lahir = $_POST['tempat_lahir'] ?? '';
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? null;
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $alamat_lengkap = $_POST['alamat_lengkap'] ?? '';
    $kode_promo = $_POST['kode_promo'] ?? '';

    // Password validation
    if (!$is_edit && empty($password)) {
        $error = 'Password wajib diisi untuk siswa baru.';
    } elseif (!empty($password) && $password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak cocok.';
    }

    // Handle file upload
    $foto_profil_path = $current_foto;
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['foto_profil']['type'], $allowed_types)) {
            $error = 'Tipe file foto tidak valid. Gunakan JPG, PNG, atau GIF.';
        } else {
            if ($is_edit && $current_foto && $current_foto !== '../uploads/profil/default.png' && file_exists($current_foto)) {
                unlink($current_foto);
            }
            $file_name = uniqid() . '-' . basename($_FILES['foto_profil']['name']);
            $foto_profil_path = $upload_dir . $file_name;
            if (!move_uploaded_file($_FILES['foto_profil']['tmp_name'], $foto_profil_path)) {
                $error = 'Gagal mengupload foto.';
                $foto_profil_path = $current_foto; // revert on failure
            }
        }
    }

    if (empty($error)) {
        $pdo->beginTransaction();
        try {
            if ($is_edit) {
                $params = [
                    $nama_lengkap, $nama_panggilan, $tempat_lahir, $tanggal_lahir, $jenis_kelamin,
                    $alamat_lengkap, $email, $telepon, $jadwal_id, $durasi_bulan, $biaya_per_bulan,
                    $tanggal_mulai, $status_pembayaran, $foto_profil_path, $kode_promo
                ];
                
                $sql = "UPDATE siswa SET 
                    nama_lengkap=?, nama_panggilan=?, tempat_lahir=?, tanggal_lahir=?, jenis_kelamin=?,
                    alamat_lengkap=?, email=?, telepon=?, jadwal_id=?, durasi_bulan=?, biaya_per_bulan=?,
                    tanggal_mulai=?, status_pembayaran=?, foto_profil=?, kode_promo=?";
                
                if (!empty($password)) {
                    $sql .= ", password=?";
                    $params[] = password_hash($password, PASSWORD_DEFAULT);
                }
                
                $sql .= " WHERE id=?";
                $params[] = $id;

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $_SESSION['flash_message'] = "Data siswa berhasil diperbarui.";

            } else {
                $qr_code_identifier = 'AVA-' . uniqid();
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("INSERT INTO siswa (
                    nama_lengkap, nama_panggilan, tempat_lahir, tanggal_lahir, jenis_kelamin,
                    alamat_lengkap, email, password, telepon, jadwal_id, durasi_bulan, 
                    biaya_per_bulan, tanggal_mulai, status_pembayaran, foto_profil, 
                    kode_promo, qr_code_identifier
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $stmt->execute([
                    $nama_lengkap, $nama_panggilan, $tempat_lahir, $tanggal_lahir, $jenis_kelamin,
                    $alamat_lengkap, $email, $hashed_password, $telepon, $jadwal_id, $durasi_bulan,
                    $biaya_per_bulan, $tanggal_mulai, $status_pembayaran, $foto_profil_path,
                    $kode_promo, $qr_code_identifier
                ]);
                
                $siswa_id = $pdo->lastInsertId();

                // Create billing records for the duration
                for ($i = 1; $i <= $durasi_bulan; $i++) {
                    $invoice_kode = 'INV-' . $siswa_id . '-' . date('Ym') . '-' . $i;
                    $jumlah = $biaya_per_bulan;
                    $tanggal_terbit = date('Y-m-d', strtotime("+$i -1 month", strtotime($tanggal_mulai)));
                    $stmt_tagihan = $pdo->prepare("INSERT INTO tagihan (siswa_id, invoice_kode, jumlah, bulan_ke, tanggal_terbit, status) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt_tagihan->execute([$siswa_id, $invoice_kode, $jumlah, $i, $tanggal_terbit, 'Belum Lunas']);
                }
                $_SESSION['flash_message'] = "Siswa baru berhasil ditambahkan.";
            }
            $pdo->commit();
            header("Location: siswa.php");
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

$jadwal_options = $pdo->query("SELECT id, hari, jam_mulai, jam_selesai FROM jadwal")->fetchAll(PDO::FETCH_ASSOC);
$promo_options = $pdo->query("SELECT kode_promo, nama_promo FROM promo_codes WHERE status = 'aktif'")->fetchAll(PDO::FETCH_ASSOC);
?>
<header class="bg-white shadow-sm p-4">
    <h1 class="text-2xl font-semibold text-gray-800"><?php echo $is_edit ? 'Edit Siswa' : 'Tambah Siswa Baru'; ?></h1>
</header>
<main class="flex-1 p-6">
    <div class="max-w-6xl mx-auto bg-white p-8 rounded-lg shadow-md">
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="current_foto" value="<?php echo htmlspecialchars($siswa['foto_profil'] ?? ''); ?>">
            
            <!-- Profile Picture -->
            <div class="mb-8 text-center">
                <label class="block text-sm font-medium text-gray-700 mb-2">Foto Profil</label>
                <img id="preview" src="<?php echo htmlspecialchars($siswa['foto_profil'] ?? '../uploads/profil/default.png'); ?>" alt="Preview" class="w-32 h-32 rounded-full object-cover mb-4 border-2 border-gray-300 mx-auto">
                <input type="file" name="foto_profil" id="foto_profil" class="hidden" accept="image/*" onchange="document.getElementById('preview').src = window.URL.createObjectURL(this.files[0])">
                <label for="foto_profil" class="cursor-pointer bg-gray-200 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-300 text-sm">
                    <i class="fas fa-upload mr-2"></i>Pilih Foto
                </label>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Personal Information -->
                <div class="md:col-span-3">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Informasi Pribadi</h3>
                </div>
                
                <div>
                    <label for="nama_lengkap" class="block text-sm font-medium text-gray-700">Nama Lengkap *</label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap" value="<?php echo htmlspecialchars($siswa['nama_lengkap'] ?? ''); ?>" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                </div>
                
                <div>
                    <label for="nama_panggilan" class="block text-sm font-medium text-gray-700">Nama Panggilan</label>
                    <input type="text" name="nama_panggilan" id="nama_panggilan" value="<?php echo htmlspecialchars($siswa['nama_panggilan'] ?? ''); ?>" class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($siswa['email'] ?? ''); ?>" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                </div>
                
                <div>
                    <label for="telepon" class="block text-sm font-medium text-gray-700">Telepon</label>
                    <input type="tel" name="telepon" id="telepon" value="<?php echo htmlspecialchars($siswa['telepon'] ?? ''); ?>" class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                </div>
                
                <div>
                    <label for="tempat_lahir" class="block text-sm font-medium text-gray-700">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" id="tempat_lahir" value="<?php echo htmlspecialchars($siswa['tempat_lahir'] ?? ''); ?>" class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                </div>
                
                <div>
                    <label for="tanggal_lahir" class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" id="tanggal_lahir" value="<?php echo htmlspecialchars($siswa['tanggal_lahir'] ?? ''); ?>" class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                </div>
                
                <div>
                    <label for="jenis_kelamin" class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
                    <select name="jenis_kelamin" id="jenis_kelamin" class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                        <option value="">Pilih</option>
                        <option value="Laki-laki" <?php echo ($siswa && $siswa['jenis_kelamin'] == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                        <option value="Perempuan" <?php echo ($siswa && $siswa['jenis_kelamin'] == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                    </select>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" id="password" class="mt-1 block w-full p-2 border border-gray-300 rounded-md" placeholder="<?php echo $is_edit ? 'Kosongkan jika tidak diubah' : 'Wajib diisi'; ?>" <?php echo !$is_edit ? 'required' : ''; ?>>
                </div>
                
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="mt-1 block w-full p-2 border border-gray-300 rounded-md" placeholder="Ulangi password" <?php echo !$is_edit ? 'required' : ''; ?>>
                </div>
                
                <div class="md:col-span-3">
                    <label for="alamat_lengkap" class="block text-sm font-medium text-gray-700">Alamat Lengkap</label>
                    <textarea name="alamat_lengkap" id="alamat_lengkap" rows="3" class="mt-1 block w-full p-2 border border-gray-300 rounded-md"><?php echo htmlspecialchars($siswa['alamat_lengkap'] ?? ''); ?></textarea>
                </div>

                <!-- Course Information -->
                <div class="md:col-span-3 mt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Informasi Kursus</h3>
                </div>
                
                <div>
                    <label for="kode_promo" class="block text-sm font-medium text-gray-700">Kode Promo</label>
                    <select name="kode_promo" id="kode_promo" class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                        <option value="">-- Pilih Promo --</option>
                        <?php foreach ($promo_options as $promo): ?>
                            <option value="<?php echo $promo['kode_promo']; ?>" <?php echo ($siswa && $siswa['kode_promo'] == $promo['kode_promo']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($promo['nama_promo'] . ' (' . $promo['kode_promo'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="jadwal_id" class="block text-sm font-medium text-gray-700">Pilih Jadwal</label>
                    <select name="jadwal_id" id="jadwal_id" class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                        <option value="">-- Pilih Jadwal --</option>
                        <?php foreach ($jadwal_options as $jadwal): ?>
                            <option value="<?php echo $jadwal['id']; ?>" <?php echo ($siswa && $siswa['jadwal_id'] == $jadwal['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($jadwal['hari'] . ' | ' . date('H:i', strtotime($jadwal['jam_mulai'])) . ' - ' . date('H:i', strtotime($jadwal['jam_selesai']))); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="durasi_bulan" class="block text-sm font-medium text-gray-700">Durasi (Bulan)</label>
                    <input type="number" name="durasi_bulan" id="durasi_bulan" value="<?php echo htmlspecialchars($siswa['durasi_bulan'] ?? '1'); ?>" min="1" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md <?php echo $is_edit ? 'bg-gray-100' : ''; ?>" <?php echo $is_edit ? 'readonly' : ''; ?>>
                </div>
                
                <div>
                    <label for="biaya_per_bulan" class="block text-sm font-medium text-gray-700">Biaya/Bulan (Rp)</label>
                    <input type="number" name="biaya_per_bulan" id="biaya_per_bulan" value="<?php echo htmlspecialchars($siswa['biaya_per_bulan'] ?? '500000'); ?>" min="0" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                </div>
                
                <div>
                    <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" id="tanggal_mulai" value="<?php echo htmlspecialchars($siswa['tanggal_mulai'] ?? date('Y-m-d')); ?>" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                </div>
                
                <div>
                    <label for="status_pembayaran" class="block text-sm font-medium text-gray-700">Status Pembayaran</label>
                    <select name="status_pembayaran" id="status_pembayaran" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                        <option value="pending" <?php echo ($siswa && $siswa['status_pembayaran'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="paid" <?php echo ($siswa && $siswa['status_pembayaran'] == 'paid') ? 'selected' : ''; ?>>Paid</option>
                        <option value="Belum Lunas" <?php echo ($siswa && $siswa['status_pembayaran'] == 'Belum Lunas') ? 'selected' : ''; ?>>Belum Lunas</option>
                        <option value="Lunas" <?php echo ($siswa && $siswa['status_pembayaran'] == 'Lunas') ? 'selected' : ''; ?>>Lunas</option>
                        <option value="Cicil" <?php echo ($siswa && $siswa['status_pembayaran'] == 'Cicil') ? 'selected' : ''; ?>>Cicil</option>
                    </select>
                </div>
            </div>
            
            <div class="mt-8 flex justify-end space-x-3">
                <a href="siswa.php" class="bg-gray-200 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-300">Batal</a>
                <button type="submit" class="bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700">
                    <?php echo $is_edit ? 'Update Siswa' : 'Simpan Siswa'; ?>
                </button>
            </div>
        </form>
    </div>
</main>
<?php include 'partials/footer.php'; ?>
