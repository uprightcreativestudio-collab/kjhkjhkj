
<?php
$page_title = 'Pengaturan PWA';
include 'partials/header.php';

$upload_dir = '../uploads/pwa_icons/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$error = '';
$success = '';

// Handle form submission
if ($_POST) {
    $app_name = trim($_POST['app_name'] ?? '');
    $app_short_name = trim($_POST['app_short_name'] ?? '');
    $app_description = trim($_POST['app_description'] ?? '');
    $theme_color = trim($_POST['theme_color'] ?? '');
    $background_color = trim($_POST['background_color'] ?? '');
    
    if (empty($app_name)) {
        $error = 'Nama aplikasi wajib diisi.';
    }
    
    // Handle icon uploads
    $icon_paths = [];
    $icon_sizes = ['72', '96', '128', '144', '152', '192', '384', '512'];
    
    foreach ($icon_sizes as $size) {
        $file_key = "icon_{$size}";
        if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES[$file_key]['tmp_name'];
            $file_ext = pathinfo($_FILES[$file_key]['name'], PATHINFO_EXTENSION);
            $allowed_ext = ['png', 'jpg', 'jpeg'];
            
            if (!in_array(strtolower($file_ext), $allowed_ext)) {
                $error = "Format file icon {$size}x{$size} tidak valid. Gunakan PNG, JPG, atau JPEG.";
                break;
            }
            
            $file_name = "icon-{$size}x{$size}.{$file_ext}";
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($file_tmp, $file_path)) {
                $icon_paths[$size] = "/uploads/pwa_icons/{$file_name}";
            }
        }
    }
    
    if (empty($error)) {
        try {
            // Save or update PWA settings
            $pdo->beginTransaction();
            
            $settings = [
                'pwa_app_name' => $app_name,
                'pwa_app_short_name' => $app_short_name,
                'pwa_app_description' => $app_description,
                'pwa_theme_color' => $theme_color,
                'pwa_background_color' => $background_color
            ];
            
            foreach ($icon_paths as $size => $path) {
                $settings["pwa_icon_{$size}"] = $path;
            }
            
            foreach ($settings as $key => $value) {
                $stmt = $pdo->prepare("
                    INSERT INTO settings (setting_key, value) VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE value = VALUES(value)
                ");
                $stmt->execute([$key, $value]);
            }
            
            $pdo->commit();
            
            // Generate new manifest files
            generateManifestFiles($pdo);
            
            $success = 'Pengaturan PWA berhasil disimpan!';
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Gagal menyimpan pengaturan: ' . $e->getMessage();
        }
    }
}

// Get current settings
function getSetting($pdo, $key, $default = '') {
    $stmt = $pdo->prepare("SELECT value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch();
    return $result ? $result['value'] : $default;
}

$current_settings = [
    'app_name' => getSetting($pdo, 'pwa_app_name', 'Anastasya Vocal Arts'),
    'app_short_name' => getSetting($pdo, 'pwa_app_short_name', 'AVA'),
    'app_description' => getSetting($pdo, 'pwa_app_description', 'Aplikasi Anastasya Vocal Arts'),
    'theme_color' => getSetting($pdo, 'pwa_theme_color', '#EE3A6A'),
    'background_color' => getSetting($pdo, 'pwa_background_color', '#FFFEE0')
];

function generateManifestFiles($pdo) {
    $settings = [];
    $stmt = $pdo->query("SELECT setting_key, value FROM settings WHERE setting_key LIKE 'pwa_%'");
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['value'];
    }
    
    $manifest = [
        'name' => $settings['pwa_app_name'] ?? 'Anastasya Vocal Arts',
        'short_name' => $settings['pwa_app_short_name'] ?? 'AVA',
        'description' => $settings['pwa_app_description'] ?? 'Aplikasi Anastasya Vocal Arts',
        'start_url' => '/',
        'display' => 'standalone',
        'theme_color' => $settings['pwa_theme_color'] ?? '#EE3A6A',
        'background_color' => $settings['pwa_background_color'] ?? '#FFFEE0',
        'icons' => []
    ];
    
    $icon_sizes = ['72', '96', '128', '144', '152', '192', '384', '512'];
    foreach ($icon_sizes as $size) {
        if (isset($settings["pwa_icon_{$size}"])) {
            $manifest['icons'][] = [
                'src' => $settings["pwa_icon_{$size}"],
                'sizes' => "{$size}x{$size}",
                'type' => 'image/png',
                'purpose' => 'any maskable'
            ];
        }
    }
    
    // Generate user manifest
    file_put_contents('../user/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
    
    // Generate guru manifest
    $guru_manifest = $manifest;
    $guru_manifest['name'] = $manifest['name'] . ' - Guru';
    $guru_manifest['short_name'] = $manifest['short_name'] . ' Guru';
    $guru_manifest['start_url'] = '/guru/';
    file_put_contents('../guru/manifest.json', json_encode($guru_manifest, JSON_PRETTY_PRINT));
}
?>

<header class="bg-white shadow-sm p-4">
    <h1 class="text-2xl font-semibold text-gray-800">Pengaturan PWA</h1>
    <p class="text-gray-600 mt-1">Kelola pengaturan Progressive Web App (PWA) untuk user dan guru</p>
</header>

<main class="flex-1 p-6">
    <div class="max-w-4xl mx-auto">
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow">
            <!-- App Info -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">Informasi Aplikasi</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Aplikasi</label>
                        <input type="text" name="app_name" value="<?php echo htmlspecialchars($current_settings['app_name']); ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Pendek</label>
                        <input type="text" name="app_short_name" value="<?php echo htmlspecialchars($current_settings['app_short_name']); ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                </div>
                
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi Aplikasi</label>
                    <textarea name="app_description" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($current_settings['app_description']); ?></textarea>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Warna Tema</label>
                        <input type="color" name="theme_color" value="<?php echo htmlspecialchars($current_settings['theme_color']); ?>" 
                               class="w-full h-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Warna Background</label>
                        <input type="color" name="background_color" value="<?php echo htmlspecialchars($current_settings['background_color']); ?>" 
                               class="w-full h-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- Icons -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">Icon PWA</h3>
                <p class="text-sm text-gray-600 mb-4">Upload icon dalam berbagai ukuran. Gunakan format PNG untuk hasil terbaik.</p>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <?php 
                    $icon_sizes = [
                        '72' => '72x72',
                        '96' => '96x96', 
                        '128' => '128x128',
                        '144' => '144x144',
                        '152' => '152x152',
                        '192' => '192x192',
                        '384' => '384x384',
                        '512' => '512x512'
                    ];
                    
                    foreach ($icon_sizes as $size => $display): 
                        $current_icon = getSetting($pdo, "pwa_icon_{$size}", '');
                    ?>
                    <div class="text-center">
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo $display; ?></label>
                        <?php if ($current_icon): ?>
                            <img src="<?php echo $current_icon; ?>" alt="Icon <?php echo $display; ?>" 
                                 class="w-16 h-16 mx-auto mb-2 border border-gray-300 rounded">
                        <?php else: ?>
                            <div class="w-16 h-16 mx-auto mb-2 border-2 border-dashed border-gray-300 rounded flex items-center justify-center">
                                <i class="fas fa-image text-gray-400"></i>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="icon_<?php echo $size; ?>" accept="image/png,image/jpg,image/jpeg" 
                               class="w-full text-xs border border-gray-300 rounded px-2 py-1">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Current Icons Preview -->
            <?php 
            $has_icons = false;
            foreach ($icon_sizes as $size => $display) {
                if (getSetting($pdo, "pwa_icon_{$size}", '')) {
                    $has_icons = true;
                    break;
                }
            }
            ?>
            
            <?php if ($has_icons): ?>
            <div class="mb-8">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">Icon Saat Ini</h3>
                <div class="flex flex-wrap gap-4">
                    <?php foreach ($icon_sizes as $size => $display): 
                        $current_icon = getSetting($pdo, "pwa_icon_{$size}", '');
                        if ($current_icon):
                    ?>
                    <div class="text-center">
                        <img src="<?php echo $current_icon; ?>" alt="Icon <?php echo $display; ?>" 
                             class="w-16 h-16 border border-gray-300 rounded">
                        <p class="text-xs text-gray-600 mt-1"><?php echo $display; ?></p>
                    </div>
                    <?php endif; endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fas fa-save mr-2"></i>
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</main>

<?php include 'partials/footer.php'; ?>
