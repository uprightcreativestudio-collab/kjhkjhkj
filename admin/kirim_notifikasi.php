<?php
$page_title = 'Kirim Notifikasi ke Siswa';
include 'partials/header.php';
include '../push_notification_helper.php';

// Fetch all students for the dropdown
$stmt_siswa = $pdo->query("SELECT id, nama_lengkap FROM siswa ORDER BY nama_lengkap");
$siswa_list = $stmt_siswa->fetchAll(PDO::FETCH_ASSOC);

$notification_sent = false;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siswa_id = $_POST['siswa_id'] ?? null;
    $judul = $_POST['judul'] ?? '';
    $pesan = $_POST['pesan'] ?? '';

    if (empty($siswa_id) || empty($judul) || empty($pesan)) {
        $error_message = 'Semua field wajib diisi.';
    } else {
        try {
            // Save notification to database
            $stmt = $pdo->prepare("INSERT INTO notifikasi_siswa (siswa_id, judul, pesan, sent_as_push) VALUES (?, ?, ?, 1)");
            $stmt->execute([$siswa_id, $judul, $pesan]);
            
            // Send push notification
            $pushHelper = new PushNotificationHelper($pdo);
            $pushSent = $pushHelper->sendNotification($siswa_id, 'student', $judul, $pesan, '/user/notifikasi.php');
            
            if ($pushSent) {
                $_SESSION['flash_message'] = "Notifikasi berhasil dikirim ke siswa dan push notification telah dikirim.";
            } else {
                $_SESSION['flash_message'] = "Notifikasi tersimpan. Push notification mungkin gagal dikirim (pastikan siswa sudah install app dan izinkan notifikasi).";
            }
            
            header("Location: kirim_notifikasi.php");
            exit();
        } catch (PDOException $e) {
            $error_message = "Gagal mengirim notifikasi: " . $e->getMessage();
        }
    }
}
?>

<header class="bg-white shadow-sm p-4">
    <h1 class="text-2xl font-semibold text-gray-800"><?php echo $page_title; ?></h1>
</header>

<main class="flex-1 p-6">
    <div class="bg-white p-6 rounded-lg shadow-md max-w-2xl mx-auto">
        <h3 class="text-lg font-semibold mb-4">Form Notifikasi</h3>
        
        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $error_message; ?></span>
            </div>
        <?php endif; ?>

        <form action="kirim_notifikasi.php" method="POST">
            <div class="mb-4">
                <label for="siswa_id" class="block text-gray-700 text-sm font-bold mb-2">Pilih Siswa:</label>
                <select id="siswa_id" name="siswa_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    <option value="">-- Pilih Siswa --</option>
                    <?php foreach ($siswa_list as $siswa): ?>
                        <option value="<?php echo $siswa['id']; ?>"><?php echo htmlspecialchars($siswa['nama_lengkap']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <label for="judul" class="block text-gray-700 text-sm font-bold mb-2">Judul Notifikasi:</label>
                <input type="text" id="judul" name="judul" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>

            <div class="mb-6">
                <label for="pesan" class="block text-gray-700 text-sm font-bold mb-2">Isi Pesan:</label>
                <textarea id="pesan" name="pesan" rows="5" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required></textarea>
            </div>

            <div class="flex items-center justify-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    <i class="fas fa-paper-plane mr-2"></i>Kirim Notifikasi
                </button>
            </div>
        </form>
    </div>
</main>

<?php include 'partials/footer.php'; ?>
