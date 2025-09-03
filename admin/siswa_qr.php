<?php
include 'partials/header.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: siswa.php");
    exit();
}

$stmt = $pdo->prepare("SELECT nama_lengkap, qr_code_identifier FROM siswa WHERE id = ?");
$stmt->execute([$id]);
$siswa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$siswa) {
    $_SESSION['flash_message'] = "Siswa tidak ditemukan.";
    header("Location: siswa.php");
    exit();
}

$page_title = 'QR Code: ' . htmlspecialchars($siswa['nama_lengkap']);
$qr_data = urlencode($siswa['qr_code_identifier']);
$qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={$qr_data}";
?>

<main class="flex-1 p-6 bg-gray-100 flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-xl text-center max-w-sm w-full">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">QR Code Absensi</h1>
        <h2 class="text-xl font-semibold text-indigo-600 mb-4"><?php echo htmlspecialchars($siswa['nama_lengkap']); ?></h2>
        
        <div class="flex justify-center my-6">
            <img src="<?php echo $qr_api_url; ?>" alt="QR Code" class="border-4 border-gray-200 rounded-lg">
        </div>
        
        <p class="text-gray-600">Tunjukkan kode ini kepada admin untuk melakukan absensi.</p>
        <p class="text-sm text-gray-500 mt-2">Kode Unik: <?php echo htmlspecialchars($siswa['qr_code_identifier']); ?></p>
        
        <div class="mt-8">
            <a href="javascript:window.print()" class="bg-indigo-600 text-white py-2 px-6 rounded-md hover:bg-indigo-700 mr-2">
                <i class="fas fa-print mr-2"></i>Cetak
            </a>
            <a href="siswa.php" class="bg-gray-200 text-gray-800 py-2 px-6 rounded-md hover:bg-gray-300">
                Kembali
            </a>
        </div>
    </div>
</main>

<?php include 'partials/footer.php'; ?>
