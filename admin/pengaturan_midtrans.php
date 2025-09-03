<?php
$page_title = 'Pengaturan Midtrans';
include 'partials/header.php';

// Include database connection
require_once '../config/database.php';
require_once '../config/midtrans_config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $server_key = $_POST['server_key'] ?? '';
    $client_key = $_POST['client_key'] ?? '';

    try {
        // Update keys
        update_setting($pdo, 'midtrans_server_key', $server_key);
        update_setting($pdo, 'midtrans_client_key', $client_key);

        $_SESSION['flash_message'] = 'Pengaturan Midtrans berhasil disimpan.';
    } catch (PDOException $e) {
        $_SESSION['flash_message'] = 'Gagal menyimpan pengaturan: ' . $e->getMessage();
    }

    // Redirect to prevent form resubmission
    header("Location: pengaturan_midtrans.php");
    exit();
}

// Fetch current settings
$current_server_key = get_setting($pdo, 'midtrans_server_key');
$current_client_key = get_setting($pdo, 'midtrans_client_key');

// Generate Webhook URL to display
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$base_path = preg_replace('/\/admin(\/.*)?$/', '', $_SERVER['REQUEST_URI']);
$webhook_url = $protocol . $host . rtrim($base_path, '/') . '/webhook.php';
?>

<header class="bg-white shadow-sm p-4">
    <h1 class="text-2xl font-semibold text-gray-800"><?php echo $page_title; ?></h1>
</header>

<main class="flex-1 p-6">
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
        <form method="POST">
            <div class="mb-6">
                <label for="server_key" class="block text-sm font-medium text-gray-700 mb-1">Midtrans Server Key (Production)</label>
                <input type="text" name="server_key" id="server_key" value="<?php echo htmlspecialchars($current_server_key); ?>" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Mid-server-xxxxxxxxxxxx" required>
                <p class="text-xs text-gray-500 mt-1">Dapatkan dari Portal Midtrans Production (MAP).</p>
            </div>

            <div class="mb-6">
                <label for="client_key" class="block text-sm font-medium text-gray-700 mb-1">Midtrans Client Key (Production)</label>
                <input type="text" name="client_key" id="client_key" value="<?php echo htmlspecialchars($current_client_key); ?>" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Mid-client-xxxxxxxxxxxx" required>
                 <p class="text-xs text-gray-500 mt-1">Dapatkan dari Portal Midtrans Production (MAP).</p>
            </div>

            <div class="mb-8 p-4 bg-gray-50 rounded-lg border">
                <label for="webhook_url" class="block text-sm font-medium text-gray-800 mb-2">URL Notifikasi Webhook</label>
                <div class="flex items-center">
                    <input type="text" id="webhook_url" value="<?php echo htmlspecialchars($webhook_url); ?>" readonly class="flex-grow p-2 border border-gray-300 rounded-l-md bg-gray-100 text-gray-600 focus:outline-none">
                    <button type="button" id="copy-btn" class="bg-gray-600 text-white px-4 py-2 rounded-r-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-2">Salin URL ini dan masukkan ke pengaturan Webhook di Portal Midtrans Production Anda.</p>
            </div>

            <div class="mb-6 p-4 bg-blue-50 rounded-lg border">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Penting</h3>
                <div class="space-y-2 text-sm">
                    <p class="flex items-start">
                        <i class="fas fa-info-circle text-blue-500 mr-2 mt-1"></i>
                        Sistem ini menggunakan mode <strong>Production</strong>. Pastikan key yang dimasukkan adalah production key.
                    </p>
                    <p class="flex items-start">
                        <i class="fas fa-credit-card text-blue-500 mr-2 mt-1"></i>
                        Metode pembayaran akan mengikuti pengaturan yang ada di akun Midtrans Anda.
                    </p>
                    <p class="flex items-start">
                        <i class="fas fa-shield-alt text-blue-500 mr-2 mt-1"></i>
                        Webhook URL harus dikonfigurasi di Portal Midtrans untuk notifikasi pembayaran.
                    </p>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-indigo-600 text-white py-2 px-5 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-save mr-2"></i>Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</main>

<script>
document.getElementById('copy-btn').addEventListener('click', function() {
    const webhookUrlInput = document.getElementById('webhook_url');
    webhookUrlInput.select();
    webhookUrlInput.setSelectionRange(0, 99999); // For mobile devices

    try {
        document.execCommand('copy');
        // Optional: Show a temporary message
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="fas fa-check"></i>';
        setTimeout(() => {
            this.innerHTML = originalText;
        }, 1500);
    } catch (err) {
        alert('Gagal menyalin URL. Silakan salin secara manual.');
    }
});
</script>

<?php include 'partials/footer.php'; ?>