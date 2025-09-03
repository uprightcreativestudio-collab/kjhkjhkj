<?php
require_once '../config/database.php';
require_once '../config/midtrans_config.php';
require_once '../midtrans_helper.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$invoice_kode = $_GET['invoice'] ?? '';

if (empty($invoice_kode)) {
    header('Location: month_payment.php');
    exit;
}

// Get invoice details
$stmt = $pdo->prepare("
    SELECT t.*, s.nama_lengkap, s.email, s.telepon, s.biaya_per_bulan
    FROM tagihan t
    JOIN siswa s ON t.siswa_id = s.id
    WHERE t.invoice_kode = ? AND t.siswa_id = ? AND t.status = 'Belum Lunas'
");
$stmt->execute([$invoice_kode, $user_id]);
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$invoice) {
    $_SESSION['error'] = 'Invoice tidak ditemukan atau sudah dibayar';
    header('Location: month_payment.php');
    exit;
}

// Calculate admin fee
$original_amount = $invoice['jumlah'];
$admin_fee = calculate_admin_fee($original_amount, $pdo);
$total_amount = $original_amount + $admin_fee;

// Fetch Snap Token from our backend API
$api_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/../admin/api_get_invoice.php?invoice=' . $invoice_kode;
// Note: Using file_get_contents with cookies to pass the session
$options = [
    'http' => [
        'header' => 'Cookie: ' . $_SERVER['HTTP_COOKIE'] . "\r\n"
    ]
];
$context = stream_context_create($options);
$response = @file_get_contents($api_url, false, $context);
$snapToken = '';
if ($response) {
    $data = json_decode($response, true);
    if (isset($data['snap_token'])) {
        $snapToken = $data['snap_token'];
    }
}

// Midtrans payment parameters
$snapToken = null;
$server_key = get_setting($pdo, 'midtrans_server_key');

if ($server_key) {
    $enabled_payments = get_midtrans_enabled_payments($pdo);

    $params = [
        'transaction_details' => [
            'order_id' => $invoice['invoice_kode'],
            'gross_amount' => (int)$total_amount,
        ],
        'customer_details' => [
            'first_name' => $invoice['nama_lengkap'],
            'email' => $invoice['email'],
            'phone' => $invoice['telepon'],
        ],
        'item_details' => [
            [
                'id' => 'monthly_payment',
                'price' => (int)$original_amount,
                'quantity' => 1,
                'name' => 'Pembayaran Bulanan Bulan ke-' . $invoice['bulan_ke']
            ],
            [
                'id' => 'admin_fee',
                'price' => (int)$admin_fee,
                'quantity' => 1,
                'name' => 'Biaya Admin'
            ]
        ]
    ];
}
?>
<title>Select Payment Method</title>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4 pb-24">
        <header class="mb-6">
            <a href="month_payment.php" class="text-cyan-600 hover:underline">&larr; Back to Payment History</a>
            <h1 class="text-2xl font-bold text-gray-800 mt-2">Select Payment Method</h1>
        </header>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="border-b pb-4 mb-4">
                <h2 class="text-lg font-semibold text-gray-700">Invoice Details</h2>
                <div class="flex justify-between mt-2">
                    <span class="text-gray-500">Invoice ID</span>
                    <span class="font-mono text-gray-800"><?php echo htmlspecialchars($invoice['invoice_kode']); ?></span>
                </div>
                <div class="flex justify-between mt-1">
                    <span class="text-gray-500">Total Amount</span>
                    <span class="font-bold text-xl text-gray-900">Rp <?php echo number_format($total_amount, 0, ',', '.'); ?></span>
                </div>
            </div>

            <div class="space-y-4">
                <h3 class="font-semibold text-gray-700">Choose a method:</h3>

                <?php if ($snapToken): ?>
                <!-- Midtrans Snap Payment -->
                <button id="pay-button" class="flex items-center justify-between w-full p-4 border rounded-lg hover:bg-gray-50 transition">
                    <div>
                        <p class="font-semibold">QRIS & Virtual Account</p>
                        <p class="text-sm text-gray-500">Bayar dengan QRIS atau Transfer Bank (VA) melalui Midtrans.</p>
                    </div>
                    <img src="https://docs.midtrans.com/asset/image/main/midtrans-logo.png" alt="Midtrans" class="h-6">
                </button>
                <?php else: ?>
                <div class="text-center text-red-500 bg-red-50 p-4 rounded-lg">
                    <p>Could not initialize payment gateway. Please contact administrator.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-6">
            <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="font-semibold text-gray-700 mb-2">Detail Pembayaran</h3>
            <div class="flex justify-between mb-1">
                <span>Invoice:</span>
                <span class="font-semibold"><?php echo htmlspecialchars($invoice['invoice_kode']); ?></span>
            </div>
            <div class="flex justify-between mb-1">
                <span>Bulan Ke:</span>
                <span><?php echo $invoice['bulan_ke']; ?></span>
            </div>
            <hr class="my-2">
            <div class="flex justify-between mb-1">
                <span>Biaya Kursus:</span>
                <span>Rp <?php echo number_format($original_amount, 0, ',', '.'); ?></span>
            </div>
            <div class="flex justify-between mb-1">
                <span>Biaya Admin:</span>
                <span>Rp <?php echo number_format($admin_fee, 0, ',', '.'); ?></span>
            </div>
            <hr class="my-2">
            <div class="flex justify-between font-semibold text-lg">
                <span>Total:</span>
                <span class="text-blue-600">Rp <?php echo number_format($total_amount, 0, ',', '.'); ?></span>
            </div>
        </div>
        </div>
    </div>

    <?php if ($snapToken): ?>
    <script type="text/javascript">
      document.getElementById('pay-button').onclick = function(){
        snap.pay('<?php echo $snapToken; ?>', {
          onSuccess: function(result){
            window.location.href = 'success_payment.php?invoice=<?php echo $invoice_kode; ?>';
          },
          onPending: function(result){
            alert("Waiting for your payment!");
            console.log(result);
          },
          onError: function(result){
            alert("Payment failed!");
            console.log(result);
          },
          onClose: function(){
            // User closed the popup without finishing the payment
          }
        })
      };
    </script>
    <?php endif; ?>
    <?php include 'partials/footer.php'; ?>
</body>
</html>