<?php 
require '../config/database.php';
include 'partials/header.php'; 

$message = '';
$message_type = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $nama_panggilan = trim($_POST['nama_panggilan']);
    $telepon = trim($_POST['telepon']);
    $alamat_lengkap = trim($_POST['alamat_lengkap']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $update_fields = [];
    $update_values = [];
    
    // Basic info updates
    if ($nama_lengkap !== $user['nama_lengkap']) {
        $update_fields[] = 'nama_lengkap = ?';
        $update_values[] = $nama_lengkap;
    }
    
    if ($nama_panggilan !== $user['nama_panggilan']) {
        $update_fields[] = 'nama_panggilan = ?';
        $update_values[] = $nama_panggilan;
    }
    
    if ($telepon !== $user['telepon']) {
        $update_fields[] = 'telepon = ?';
        $update_values[] = $telepon;
    }
    
    if ($alamat_lengkap !== $user['alamat_lengkap']) {
        $update_fields[] = 'alamat_lengkap = ?';
        $update_values[] = $alamat_lengkap;
    }
    
    // Password update
    if (!empty($new_password)) {
        if (empty($current_password)) {
            $message = 'Password lama harus diisi untuk mengubah password.';
            $message_type = 'error';
        } elseif (!password_verify($current_password, $user['password'])) {
            $message = 'Password lama tidak benar.';
            $message_type = 'error';
        } elseif (strlen($new_password) < 6) {
            $message = 'Password baru minimal 6 karakter.';
            $message_type = 'error';
        } elseif ($new_password !== $confirm_password) {
            $message = 'Password baru dan konfirmasi tidak cocok.';
            $message_type = 'error';
        } else {
            $update_fields[] = 'password = ?';
            $update_values[] = password_hash($new_password, PASSWORD_DEFAULT);
        }
    }
    
    // Handle photo upload
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/profil/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['foto_profil']['type'], $allowed_types)) {
            // Delete old photo if exists
            if ($user['foto_profil'] && file_exists('../' . $user['foto_profil'])) {
                unlink('../' . $user['foto_profil']);
            }
            
            $file_extension = pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $new_filepath = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $new_filepath)) {
                $update_fields[] = 'foto_profil = ?';
                $update_values[] = 'uploads/profil/' . $new_filename;
            }
        } else {
            $message = 'Format foto tidak valid. Gunakan JPG, PNG, atau GIF.';
            $message_type = 'error';
        }
    }
    
    // Update database if no errors and there are changes
    if (empty($message) && !empty($update_fields)) {
        try {
            $sql = "UPDATE siswa SET " . implode(', ', $update_fields) . " WHERE id = ?";
            $update_values[] = $user['id'];
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($update_values);
            
            $message = 'Profil berhasil diperbarui!';
            $message_type = 'success';
            
            // Refresh user data
            $stmt = $pdo->prepare("SELECT s.*, j.hari, j.jam_mulai, j.jam_selesai FROM siswa s LEFT JOIN jadwal j ON s.jadwal_id = j.id WHERE s.id = ?");
            $stmt->execute([$user['id']]);
            $user = $stmt->fetch();
            
        } catch (Exception $e) {
            $message = 'Terjadi kesalahan saat memperbarui profil.';
            $message_type = 'error';
        }
    } elseif (empty($message) && empty($update_fields)) {
        $message = 'Tidak ada perubahan yang disimpan.';
        $message_type = 'info';
    }
}
?>

<title>User Profile</title>
</head>
<body class="bg-gray-100">
    <header class="relative bg-gradient-to-br from-pink-accent via-pink-dark to-pink-light rounded-b-[35px] shadow-2xl p-6 text-cream z-10 mb-5 animate-slide-in">
        <div class="absolute inset-0 bg-gradient-to-br from-pink-accent/90 to-pink-dark/90 rounded-b-[35px] backdrop-blur-sm"></div>
        <div class="absolute top-0 right-0 w-32 h-32 bg-yellow-bright/20 rounded-full -translate-y-16 translate-x-16 animate-pulse-soft"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-blue-soft/20 rounded-full translate-y-12 -translate-x-12 animate-float"></div>

        <div class="relative flex items-center justify-between">
            <div class="flex items-center">
                <a href="dashboard.php" class="group mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-cream group-hover:scale-110 transition-transform duration-300">
                        <path d="m15 18-6-6 6-6"/>
                    </svg>
                </a>
                <div class="flex items-center">
                    <img src="<?php echo htmlspecialchars($user['foto_profil'] ? '../' . $user['foto_profil'] : '../avaaset/logo-ava.png'); ?>" alt="Profile" class="h-16 w-16 rounded-2xl border-3 border-cream/50 object-cover shadow-lg" />
                    <div class="ml-4">
                        <h1 class="font-bold text-xl text-cream drop-shadow-sm">My Profile</h1>
                        <p class="text-sm text-cream/80 font-medium"><?php echo htmlspecialchars($user['nama_lengkap']); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container mx-auto p-4 pb-24">
        <div class="max-w-2xl mx-auto">
            
            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-lg <?php 
                    echo $message_type === 'success' ? 'bg-green-100 border border-green-300 text-green-800' : 
                        ($message_type === 'info' ? 'bg-blue-100 border border-blue-300 text-blue-800' : 
                        'bg-red-100 border border-red-300 text-red-800'); 
                ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                
                <!-- Profile Photo Section -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Foto Profil</h3>
                    <div class="flex flex-col items-center">
                        <img id="photo-preview" src="<?php echo htmlspecialchars($user['foto_profil'] ? '../' . $user['foto_profil'] : '../avaaset/logo-ava.png'); ?>" 
                             alt="Profile" class="w-32 h-32 rounded-full object-cover border-4 border-pink-accent mb-4">
                        <input type="file" id="foto_profil" name="foto_profil" accept="image/*" class="hidden" onchange="previewPhoto(this)">
                        <label for="foto_profil" class="bg-pink-accent text-white px-6 py-2 rounded-lg hover:bg-pink-dark transition-colors cursor-pointer">
                            <i class="fas fa-camera mr-2"></i>Ubah Foto
                        </label>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pribadi</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-accent focus:border-pink-accent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Panggilan</label>
                            <input type="text" name="nama_panggilan" value="<?php echo htmlspecialchars($user['nama_panggilan']); ?>" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-accent focus:border-pink-accent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100" readonly>
                            <p class="text-xs text-gray-500 mt-1">Email tidak dapat diubah</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Telepon</label>
                            <input type="tel" name="telepon" value="<?php echo htmlspecialchars($user['telepon']); ?>" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-accent focus:border-pink-accent">
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alamat Lengkap</label>
                        <textarea name="alamat_lengkap" rows="3" 
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-accent focus:border-pink-accent"><?php echo htmlspecialchars($user['alamat_lengkap']); ?></textarea>
                    </div>
                </div>

                <!-- Course Information (Read Only) -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Kursus</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                            <input type="text" value="<?php echo date('d F Y', strtotime($user['tanggal_mulai'])); ?>" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Durasi Kursus</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['durasi_bulan']); ?> bulan" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Biaya per Bulan</label>
                            <input type="text" value="Rp <?php echo number_format($user['biaya_per_bulan'], 0, ',', '.'); ?>" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status Pembayaran</label>
                            <input type="text" value="<?php echo ucfirst($user['status_pembayaran']); ?>" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100" readonly>
                        </div>
                    </div>
                    
                    <?php if ($user['hari']): ?>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jadwal Kelas</label>
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <p class="font-semibold text-blue-800"><?php echo htmlspecialchars($user['hari']); ?></p>
                            <p class="text-sm text-blue-600"><?php echo date('H:i', strtotime($user['jam_mulai'])); ?> - <?php echo date('H:i', strtotime($user['jam_selesai'])); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Password Change Section -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Ubah Password</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password Lama</label>
                            <input type="password" name="current_password" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-accent focus:border-pink-accent"
                                   placeholder="Masukkan password lama">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
                            <input type="password" name="new_password" minlength="6"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-accent focus:border-pink-accent"
                                   placeholder="Masukkan password baru (minimal 6 karakter)">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password Baru</label>
                            <input type="password" name="confirm_password" minlength="6"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-accent focus:border-pink-accent"
                                   placeholder="Ulangi password baru">
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-4">
                    <button type="submit" class="flex-1 bg-gradient-to-r from-pink-accent to-pink-dark text-white py-3 px-6 rounded-lg hover:shadow-lg transition-all font-semibold">
                        <i class="fas fa-save mr-2"></i>Simpan Perubahan
                    </button>
                    <a href="logout.php" class="flex-1 text-center bg-red-500 text-white py-3 px-6 rounded-lg hover:bg-red-600 transition-colors font-semibold">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </form>

            <!-- Additional Info -->
            <div class="mt-6 bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Tambahan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="font-medium text-gray-600">QR Code ID:</span>
                        <p class="font-mono text-gray-800 break-all"><?php echo htmlspecialchars($user['qr_code_identifier']); ?></p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600">Tanggal Daftar:</span>
                        <p class="text-gray-800"><?php echo date('d F Y', strtotime($user['tanggal_pendaftaran'])); ?></p>
                    </div>
                    <?php if ($user['tempat_lahir'] && $user['tanggal_lahir']): ?>
                    <div>
                        <span class="font-medium text-gray-600">Tempat, Tanggal Lahir:</span>
                        <p class="text-gray-800"><?php echo htmlspecialchars($user['tempat_lahir']) . ', ' . date('d F Y', strtotime($user['tanggal_lahir'])); ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ($user['jenis_kelamin']): ?>
                    <div>
                        <span class="font-medium text-gray-600">Jenis Kelamin:</span>
                        <p class="text-gray-800"><?php echo htmlspecialchars($user['jenis_kelamin']); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function previewPhoto(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('photo-preview').src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Password confirmation validation
        document.querySelector('input[name="confirm_password"]').addEventListener('input', function() {
            const newPassword = document.querySelector('input[name="new_password"]').value;
            const confirmPassword = this.value;
            
            if (newPassword && confirmPassword && newPassword !== confirmPassword) {
                this.setCustomValidity('Password tidak cocok');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>

    <?php include 'partials/footer.php'; ?>
</body>
</html>