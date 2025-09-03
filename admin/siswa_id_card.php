
<?php
include 'partials/header.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: siswa.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM siswa WHERE id = ?");
$stmt->execute([$id]);
$siswa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$siswa) {
    $_SESSION['flash_message'] = "Siswa tidak ditemukan.";
    header("Location: siswa.php");
    exit();
}

$page_title = 'ID Card: ' . htmlspecialchars($siswa['nama_lengkap']);
$qr_data = urlencode($siswa['qr_code_identifier']);
$qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={$qr_data}";
?>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    .id-card-container, .id-card-container * {
        visibility: visible;
    }
    .id-card-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .no-print {
        display: none !important;
    }
}

.id-card {
    width: 324px; /* 85.6mm in pixels at 96 DPI */
    height: 204px; /* 53.98mm in pixels at 96 DPI */
    background: linear-gradient(135deg, #FFF8DC 0%, #F5F5DC 100%);
    border: 2px solid #8B0000;
    border-radius: 8px;
    position: relative;
    font-family: 'Arial', sans-serif;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.id-card-header {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    margin-bottom: 4px;
}

.logo-section {
    display: flex;
    align-items: center;
    margin-right: 8px;
}

.music-note {
    color: #8B0000;
    font-size: 24px;
    font-weight: bold;
    margin-right: 4px;
}

.ava-text {
    color: #8B0000;
    font-size: 20px;
    font-weight: bold;
    margin-right: 8px;
}

.brand-text {
    color: #333;
    font-size: 10px;
    line-height: 1.2;
}

.student-name {
    color: #000;
    font-size: 16px;
    font-weight: bold;
    margin: 4px 12px;
    line-height: 1.1;
}

.student-title {
    color: #8B0000;
    font-size: 10px;
    margin: 2px 12px 8px 12px;
    font-weight: 500;
}

.qr-section {
    display: flex;
    justify-content: center;
    margin: 8px 0;
}

.qr-code img {
    width: 80px;
    height: 80px;
    border: 1px solid #ddd;
}

.footer {
    position: absolute;
    bottom: 0;
    width: 100%;
    background: #8B0000;
    color: white;
    text-align: center;
    padding: 4px 0;
}

.footer-text {
    font-size: 8px;
    line-height: 1.2;
}

.print-controls {
    margin: 20px 0;
    text-align: center;
}
</style>

<header class="bg-white shadow-sm p-4 no-print">
    <h1 class="text-2xl font-semibold text-gray-800">ID Card Siswa</h1>
</header>

<main class="flex-1 p-6 bg-gray-100 flex flex-col items-center justify-center">
    <div class="print-controls no-print mb-6">
        <button onclick="window.print()" class="bg-indigo-600 text-white py-2 px-6 rounded-md hover:bg-indigo-700 mr-2">
            <i class="fas fa-print mr-2"></i>Cetak ID Card
        </button>
        <a href="siswa.php" class="bg-gray-500 text-white py-2 px-6 rounded-md hover:bg-gray-600">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>
    
    <div class="id-card-container">
        <div class="id-card">
            <div class="id-card-header">
                <div class="logo-section">
                    <div class="music-note">♪</div>
                    <div class="ava-text">AVA</div>
                </div>
                <div class="brand-text">
                    <div>Anastasya</div>
                    <div>Vocal Arts</div>
                </div>
            </div>
            
            <div class="student-name">
                <?php 
                $nama = htmlspecialchars($siswa['nama_panggilan'] ?: $siswa['nama_lengkap']);
                if (strlen($nama) > 20) {
                    $nama = substr($nama, 0, 20) . '...';
                }
                echo "($nama)";
                ?>
            </div>
            
            <div class="student-title">AVA's Student</div>
            
            <div class="qr-section">
                <div class="qr-code">
                    <img src="<?php echo $qr_api_url; ?>" alt="QR Code">
                </div>
            </div>
            
            <div class="footer">
                <div class="footer-text">
                    @anastasyavocalarts<br>
                    www.anastasya.co
                </div>
            </div>
        </div>
    </div>
    
    <div class="no-print mt-6 text-center">
        <p class="text-sm text-gray-600 mb-2">
            <strong>Nama Lengkap:</strong> <?php echo htmlspecialchars($siswa['nama_lengkap']); ?>
        </p>
        <p class="text-sm text-gray-600 mb-2">
            <strong>QR Code ID:</strong> <?php echo htmlspecialchars($siswa['qr_code_identifier']); ?>
        </p>
        <p class="text-xs text-gray-500">
            ID Card berukuran standar (85.6 x 53.98 mm). Gunakan tombol cetak untuk mencetak dalam ukuran yang tepat.
        </p>
    </div>
</main>

<?php include 'partials/footer.php'; ?>
