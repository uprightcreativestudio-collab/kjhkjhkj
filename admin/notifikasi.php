<?php
$page_title = 'Notifikasi';
include 'partials/header.php';

// For simplicity, we generate notifications dynamically from recent activities
// A more robust system would use a dedicated notifications table and triggers.

// New Students
$new_students = $pdo->query("SELECT nama_lengkap, tanggal_mulai FROM siswa ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Paid Invoices
$paid_invoices = $pdo->query("
    SELECT s.nama_lengkap, t.jumlah, t.updated_at 
    FROM tagihan t
    JOIN siswa s ON t.siswa_id = s.id
    WHERE t.status = 'Lunas' 
    ORDER BY t.updated_at DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

?>

<header class="bg-white shadow-sm p-4">
    <h1 class="text-2xl font-semibold text-gray-800">Notifikasi & Aktivitas Terbaru</h1>
</header>

<main class="flex-1 p-6">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold mb-4">Aktivitas Sistem</h3>
        <div class="space-y-4">
            
            <?php foreach($new_students as $student): ?>
            <div class="flex items-start p-3 bg-blue-50 rounded-lg">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-full bg-blue-200 flex items-center justify-center">
                        <i class="fas fa-user-plus text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="font-semibold text-gray-800">Pendaftaran Siswa Baru</p>
                    <p class="text-sm text-gray-600">
                        Siswa <span class="font-medium"><?php echo htmlspecialchars($student['nama_lengkap']); ?></span> telah berhasil mendaftar.
                    </p>
                    <p class="text-xs text-gray-400 mt-1"><?php echo date('d M Y, H:i', strtotime($student['tanggal_mulai'])); ?></p>
                </div>
            </div>
            <?php endforeach; ?>

            <?php foreach($paid_invoices as $invoice): ?>
            <div class="flex items-start p-3 bg-green-50 rounded-lg">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-full bg-green-200 flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="font-semibold text-gray-800">Pembayaran Diterima</p>
                    <p class="text-sm text-gray-600">
                        Pembayaran sebesar <span class="font-medium">Rp <?php echo number_format($invoice['jumlah'], 0, ',', '.'); ?></span> dari <span class="font-medium"><?php echo htmlspecialchars($invoice['nama_lengkap']); ?></span> telah lunas.
                    </p>
                     <p class="text-xs text-gray-400 mt-1"><?php echo date('d M Y, H:i', strtotime($invoice['updated_at'])); ?></p>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (empty($new_students) && empty($paid_invoices)): ?>
                <p class="text-center text-gray-500">Tidak ada aktivitas terbaru.</p>
            <?php endif; ?>

        </div>
    </div>
</main>

<?php include 'partials/footer.php'; ?>
