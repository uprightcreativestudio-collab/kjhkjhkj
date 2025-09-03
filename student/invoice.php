
<?php
require '../config/database.php';
include 'partials/header.php';

$type = $_GET['type'] ?? '';
$id = $_GET['id'] ?? '';

if (empty($type) || empty($id)) {
    header('Location: dashboard.php');
    exit;
}

$invoice_data = null;

if ($type == 'payment') {
    // For registration payments
    $stmt = $pdo->prepare("
        SELECT p.*, s.nama_lengkap, s.email, s.telepon, s.alamat_lengkap,
               pc.nama_promo, pc.harga_kursus, pc.biaya_pendaftaran
        FROM payments p
        JOIN siswa s ON p.student_id = s.id
        LEFT JOIN promo_codes pc ON p.kode_promo = pc.kode_promo
        WHERE p.id = ? AND p.student_id = ?
    ");
    $stmt->execute([$id, $user['id']]);
    $invoice_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $invoice_type = 'registration';
} else if ($type == 'tagihan') {
    // For monthly bills
    $stmt = $pdo->prepare("
        SELECT t.*, s.nama_lengkap, s.email, s.telepon, s.alamat_lengkap
        FROM tagihan t
        JOIN siswa s ON t.siswa_id = s.id
        WHERE t.id = ? AND t.siswa_id = ?
    ");
    $stmt->execute([$id, $user['id']]);
    $invoice_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $invoice_type = 'monthly';
}

if (!$invoice_data) {
    header('Location: dashboard.php');
    exit;
}

// Calculate admin fee if needed
$admin_fee = 0;
if ($invoice_type == 'monthly') {
    $original_amount = $invoice_data['jumlah'];
    require_once '../midtrans_helper.php';
    $admin_fee = calculate_admin_fee($original_amount, $pdo);
}
?>

<title>Invoice - AVA</title>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4 pb-24">
        <header class="mb-6 flex items-center justify-between">
            <div>
                <a href="payment_history.php" class="text-cyan-600 hover:underline">&larr; Back to Payment History</a>
                <h1 class="text-2xl font-bold text-gray-800 mt-2">Invoice</h1>
            </div>
            <div class="flex space-x-2">
                <button onclick="downloadPDF()" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors text-sm">
                    <i class="fas fa-download mr-2"></i>Download PDF
                </button>
            </div>
        </header>

        <div class="bg-white p-6 rounded-lg shadow-md" id="invoice-content">
            <!-- Invoice Header -->
            <div class="border-b-2 border-gray-200 pb-6 mb-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-2">INVOICE</h2>
                        <p class="text-gray-600">Anastasya Vocal Arts</p>
                        <p class="text-sm text-gray-500">Lembaga Kursus Vokal Profesional</p>
                        <p class="text-sm text-gray-500">Email: info@anastasyavocalarts.com</p>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-600">
                            <p>Tanggal: <?php echo date('d M Y'); ?></p>
                            <p>Invoice #: <?php echo $invoice_type == 'registration' ? $invoice_data['order_id'] : $invoice_data['invoice_kode']; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bill To -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Ditagihkan Kepada:</h3>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="font-semibold"><?php echo htmlspecialchars($invoice_data['nama_lengkap']); ?></p>
                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($invoice_data['email']); ?></p>
                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($invoice_data['telepon']); ?></p>
                </div>
            </div>

            <!-- Invoice Details -->
            <div class="mb-6">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-300">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-300 px-4 py-3 text-left">Deskripsi</th>
                                <th class="border border-gray-300 px-4 py-3 text-center">Qty</th>
                                <th class="border border-gray-300 px-4 py-3 text-right">Harga</th>
                                <th class="border border-gray-300 px-4 py-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($invoice_type == 'registration'): ?>
                                <tr>
                                    <td class="border border-gray-300 px-4 py-3">Biaya Pendaftaran Kursus (Dasar)</td>
                                    <td class="border border-gray-300 px-4 py-3 text-center">1</td>
                                    <td class="border border-gray-300 px-4 py-3 text-right">Rp <?php echo number_format($invoice_data['biaya_pendaftaran_base'] ?? ($invoice_data['biaya_pendaftaran'] / 1.12), 0, ',', '.'); ?></td>
                                    <td class="border border-gray-300 px-4 py-3 text-right">Rp <?php echo number_format($invoice_data['biaya_pendaftaran_base'] ?? ($invoice_data['biaya_pendaftaran'] / 1.12), 0, ',', '.'); ?></td>
                                </tr>
                                <tr>
                                    <td class="border border-gray-300 px-4 py-3">Pajak Pendaftaran (12%)</td>
                                    <td class="border border-gray-300 px-4 py-3 text-center">1</td>
                                    <td class="border border-gray-300 px-4 py-3 text-right">Rp <?php echo number_format($invoice_data['pajak_pendaftaran'] ?? (($invoice_data['biaya_pendaftaran'] / 1.12) * 0.12), 0, ',', '.'); ?></td>
                                    <td class="border border-gray-300 px-4 py-3 text-right">Rp <?php echo number_format($invoice_data['pajak_pendaftaran'] ?? (($invoice_data['biaya_pendaftaran'] / 1.12) * 0.12), 0, ',', '.'); ?></td>
                                </tr>
                                <tr>
                                    <td class="border border-gray-300 px-4 py-3">Biaya Kursus Bulan Pertama (Dasar)
                                        <?php if ($invoice_data['kode_promo']): ?>
                                            <br><small class="text-green-600">Promo: <?php echo htmlspecialchars($invoice_data['nama_promo']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="border border-gray-300 px-4 py-3 text-center">1</td>
                                    <td class="border border-gray-300 px-4 py-3 text-right">Rp <?php echo number_format($invoice_data['harga_kursus_base'] ?? ($invoice_data['harga_kursus'] / 1.12), 0, ',', '.'); ?></td>
                                    <td class="border border-gray-300 px-4 py-3 text-right">Rp <?php echo number_format($invoice_data['harga_kursus_base'] ?? ($invoice_data['harga_kursus'] / 1.12), 0, ',', '.'); ?></td>
                                </tr>
                                <tr>
                                    <td class="border border-gray-300 px-4 py-3">Pajak Kursus (12%)</td>
                                    <td class="border border-gray-300 px-4 py-3 text-center">1</td>
                                    <td class="border border-gray-300 px-4 py-3 text-right">Rp <?php echo number_format($invoice_data['pajak_kursus'] ?? (($invoice_data['harga_kursus'] / 1.12) * 0.12), 0, ',', '.'); ?></td>
                                    <td class="border border-gray-300 px-4 py-3 text-right">Rp <?php echo number_format($invoice_data['pajak_kursus'] ?? (($invoice_data['harga_kursus'] / 1.12) * 0.12), 0, ',', '.'); ?></td>
                                </tr>
                                <?php if ($invoice_data['midtrans_fee'] > 0): ?>
                                <tr>
                                    <td class="border border-gray-300 px-4 py-3">Biaya Admin</td>
                                    <td class="border border-gray-300 px-4 py-3 text-center">1</td>
                                    <td class="border border-gray-300 px-4 py-3 text-right">Rp <?php echo number_format($invoice_data['midtrans_fee'], 0, ',', '.'); ?></td>
                                    <td class="border border-gray-300 px-4 py-3 text-right">Rp <?php echo number_format($invoice_data['midtrans_fee'], 0, ',', '.'); ?></td>
                                </tr>
                                <?php endif; ?>
                            <?php else: ?>
                                <tr>
                                    <td class="border border-gray-300 px-4 py-3">Biaya Kursus Bulan ke-<?php echo $invoice_data['bulan_ke']; ?></td>
                                    <td class="border border-gray-300 px-4 py-3 text-center">1</td>
                                    <td class="border border-gray-300 px-4 py-3 text-right">Rp <?php echo number_format($invoice_data['jumlah'], 0, ',', '.'); ?></td>
                                    <td class="border border-gray-300 px-4 py-3 text-right">Rp <?php echo number_format($invoice_data['jumlah'], 0, ',', '.'); ?></td>
                                </tr>
                                <?php if ($admin_fee > 0): ?>
                                <tr>
                                    <td class="border border-gray-300 px-4 py-3">Biaya Admin</td>
                                    <td class="border border-gray-300 px-4 py-3 text-center">1</td>
                                    <td class="border border-gray-300 px-4 py-3 text-right">Rp <?php echo number_format($admin_fee, 0, ',', '.'); ?></td>
                                    <td class="border border-gray-300 px-4 py-3 text-right">Rp <?php echo number_format($admin_fee, 0, ',', '.'); ?></td>
                                </tr>
                                <?php endif; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-100 font-bold">
                                <td colspan="3" class="border border-gray-300 px-4 py-3 text-right">TOTAL:</td>
                                <td class="border border-gray-300 px-4 py-3 text-right">
                                    Rp <?php echo number_format($invoice_type == 'registration' ? $invoice_data['gross_amount'] : ($invoice_data['jumlah'] + $admin_fee), 0, ',', '.'); ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Payment Status -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Status Pembayaran:</h3>
                <div class="bg-<?php echo ($invoice_type == 'registration' && in_array($invoice_data['transaction_status'], ['settlement', 'capture', 'paid'])) || ($invoice_type == 'monthly' && $invoice_data['status'] == 'Lunas') ? 'green' : 'yellow'; ?>-100 border border-<?php echo ($invoice_type == 'registration' && in_array($invoice_data['transaction_status'], ['settlement', 'capture', 'paid'])) || ($invoice_type == 'monthly' && $invoice_data['status'] == 'Lunas') ? 'green' : 'yellow'; ?>-300 p-4 rounded-lg">
                    <p class="font-semibold text-<?php echo ($invoice_type == 'registration' && in_array($invoice_data['transaction_status'], ['settlement', 'capture', 'paid'])) || ($invoice_type == 'monthly' && $invoice_data['status'] == 'Lunas') ? 'green' : 'yellow'; ?>-800">
                        <?php 
                        if ($invoice_type == 'registration') {
                            echo in_array($invoice_data['transaction_status'], ['settlement', 'capture', 'paid']) ? 'LUNAS' : strtoupper($invoice_data['transaction_status']);
                        } else {
                            echo strtoupper($invoice_data['status']);
                        }
                        ?>
                    </p>
                    <?php if (($invoice_type == 'registration' && $invoice_data['transaction_time']) || ($invoice_type == 'monthly' && $invoice_data['tanggal_bayar'])): ?>
                    <p class="text-sm text-gray-600">
                        Dibayar pada: <?php echo date('d M Y H:i', strtotime($invoice_type == 'registration' ? $invoice_data['transaction_time'] : $invoice_data['tanggal_bayar'])); ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center text-sm text-gray-500 border-t pt-6">
                <p>Terima kasih atas kepercayaan Anda kepada Anastasya Vocal Arts.</p>
                <p>Untuk pertanyaan mengenai invoice ini, silakan hubungi kami di info@anastasyavocalarts.com</p>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
    function downloadPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        // Get invoice data
        const invoiceType = '<?php echo $invoice_type; ?>';
        const invoiceNumber = '<?php echo $invoice_type == "registration" ? $invoice_data["order_id"] : $invoice_data["invoice_kode"]; ?>';
        const customerName = '<?php echo addslashes($invoice_data["nama_lengkap"]); ?>';
        const customerEmail = '<?php echo addslashes($invoice_data["email"]); ?>';
        const customerPhone = '<?php echo addslashes($invoice_data["telepon"]); ?>';
        const totalAmount = <?php echo $invoice_type == 'registration' ? $invoice_data['gross_amount'] : ($invoice_data['jumlah'] + $admin_fee); ?>;
        
        // Header
        doc.setFontSize(20);
        doc.setFont('helvetica', 'bold');
        doc.text('INVOICE', 105, 20, { align: 'center' });
        
        doc.setFontSize(14);
        doc.text('Anastasya Vocal Arts', 14, 35);
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(10);
        doc.text('Lembaga Kursus Vokal Profesional', 14, 41);
        doc.text('Email: info@anastasyavocalarts.com', 14, 46);
        
        // Invoice details
        doc.setFont('helvetica', 'bold');
        doc.text('Invoice #:', 130, 35);
        doc.setFont('helvetica', 'normal');
        doc.text(invoiceNumber, 155, 35);
        
        doc.setFont('helvetica', 'bold');
        doc.text('Tanggal:', 130, 41);
        doc.setFont('helvetica', 'normal');
        doc.text('<?php echo date("d M Y"); ?>', 155, 41);
        
        // Customer details
        doc.setFont('helvetica', 'bold');
        doc.text('Ditagihkan Kepada:', 14, 60);
        doc.setFont('helvetica', 'normal');
        doc.text(customerName, 14, 66);
        doc.text(customerEmail, 14, 71);
        doc.text(customerPhone, 14, 76);
        
        // Table header
        let yPos = 90;
        doc.setFillColor(240, 240, 240);
        doc.rect(14, yPos, 182, 8, 'F');
        doc.setFont('helvetica', 'bold');
        doc.text('Deskripsi', 16, yPos + 5);
        doc.text('Jumlah', 180, yPos + 5, { align: 'right' });
        
        // Table content
        yPos += 10;
        doc.setFont('helvetica', 'normal');
        
        <?php if ($invoice_type == 'registration'): ?>
            doc.text('Biaya Pendaftaran Kursus', 16, yPos);
            doc.text('Rp <?php echo number_format($invoice_data["biaya_pendaftaran"], 0, ",", "."); ?>', 180, yPos, { align: 'right' });
            yPos += 6;
            
            doc.text('Biaya Kursus Bulan Pertama', 16, yPos);
            doc.text('Rp <?php echo number_format($invoice_data["harga_kursus"], 0, ",", "."); ?>', 180, yPos, { align: 'right' });
            yPos += 6;
            
            <?php if ($invoice_data['midtrans_fee'] > 0): ?>
            doc.text('Biaya Admin', 16, yPos);
            doc.text('Rp <?php echo number_format($invoice_data["midtrans_fee"], 0, ",", "."); ?>', 180, yPos, { align: 'right' });
            yPos += 6;
            <?php endif; ?>
        <?php else: ?>
            doc.text('Biaya Kursus Bulan ke-<?php echo $invoice_data["bulan_ke"]; ?>', 16, yPos);
            doc.text('Rp <?php echo number_format($invoice_data["jumlah"], 0, ",", "."); ?>', 180, yPos, { align: 'right' });
            yPos += 6;
            
            <?php if ($admin_fee > 0): ?>
            doc.text('Biaya Admin', 16, yPos);
            doc.text('Rp <?php echo number_format($admin_fee, 0, ",", "."); ?>', 180, yPos, { align: 'right' });
            yPos += 6;
            <?php endif; ?>
        <?php endif; ?>
        
        // Total
        yPos += 5;
        doc.setFillColor(240, 240, 240);
        doc.rect(14, yPos, 182, 8, 'F');
        doc.setFont('helvetica', 'bold');
        doc.text('TOTAL:', 150, yPos + 5);
        doc.text('Rp ' + totalAmount.toLocaleString('id-ID'), 180, yPos + 5, { align: 'right' });
        
        // Status
        yPos += 20;
        doc.setFont('helvetica', 'bold');
        doc.text('Status Pembayaran:', 14, yPos);
        doc.setFont('helvetica', 'normal');
        doc.text('<?php echo ($invoice_type == "registration" && in_array($invoice_data["transaction_status"], ["settlement", "capture", "paid"])) || ($invoice_type == "monthly" && $invoice_data["status"] == "Lunas") ? "LUNAS" : "PENDING"; ?>', 14, yPos + 6);
        
        // Footer
        yPos += 20;
        doc.setFontSize(8);
        doc.text('Terima kasih atas kepercayaan Anda kepada Anastasya Vocal Arts.', 105, yPos, { align: 'center' });
        doc.text('Untuk pertanyaan mengenai invoice ini, silakan hubungi kami di info@anastasyavocalarts.com', 105, yPos + 4, { align: 'center' });
        
        doc.save('invoice-' + invoiceNumber + '.pdf');
    }
    </script>

    <?php include 'partials/footer.php'; ?>
</body>
</html>
