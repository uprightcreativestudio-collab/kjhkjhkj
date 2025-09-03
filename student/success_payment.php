<?php
require '../config/database.php';
include 'partials/header.php';

$invoice_kode = $_GET['invoice'] ?? '';
?>
<title>Payment Success</title>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4 pb-24 flex flex-col items-center justify-center min-h-screen text-center">
        <div class="bg-white rounded-lg shadow-lg p-8 max-w-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-green-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h1 class="text-2xl font-bold text-gray-800">Payment Successful!</h1>
            <p class="text-gray-600 mt-2">
                Thank you for your payment. Your invoice <span class="font-semibold"><?php echo htmlspecialchars($invoice_kode); ?></span> has been processed.
            </p>
            <p class="text-gray-500 mt-1 text-sm">
                The status will be updated to "Paid" shortly after confirmation from the payment gateway.
            </p>
            <a href="month_payment.php" class="mt-6 inline-block w-full px-4 py-2 text-sm font-medium text-white bg-cyan-600 rounded-lg hover:bg-cyan-700">
                Back to Payment History
            </a>
        </div>
    </div>
    <?php include 'partials/footer.php'; ?>
</body>
</html>
