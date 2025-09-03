<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Get user data
$stmt = $pdo->prepare("
    SELECT s.*, j.hari, j.jam_mulai, j.jam_selesai
    FROM siswa s
    LEFT JOIN jadwal j ON s.jadwal_id = j.id
    WHERE s.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: index.php');
    exit();
}

function isActive(...$pages) {
    $current_page = basename($_SERVER['PHP_SELF']);

    foreach ($pages as $page) {
        if ($current_page == $page) {
            return 'active';
        }
    }

    // Special handling for payment-related pages
    $payment_pages = ['payment_history.php', 'month_payment.php', 'select_payment.php', 'qris_pay.php', 'va_payment.php', 'success_payment.php'];
    if (in_array($pages[0], ['payment_history.php', 'month_payment.php']) && in_array($current_page, $payment_pages)) {
        return 'active';
    }

    return '';
}

// Get page title based on current file
$current_page = basename($_SERVER['PHP_SELF']);
$page_titles = [
    'month_payment.php' => 'Payment History',
    'select_payment.php' => 'Select Payment',
    'qris_pay.php' => 'QRIS Payment',
    'va_payment.php' => 'Virtual Account',
    'success_payment.php' => 'Payment Success',
    'ebook.php' => 'E-Books',
    'profile.php' => 'Profile',
    'qr_attendance.php' => 'QR Attendance',
    'notifikasi.php' => 'Notifications',
    'stream.php' => 'Live Stream',
    'stream_gallery.php' => 'Stream Gallery'
];
$page_title = isset($page_titles[$current_page]) ? $page_titles[$current_page] : 'Page';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            'cream': '#FFFEE0',
            'pink-light': '#F5A6BB',
            'pink-dark': '#9E0232',
            'pink-accent': '#EE3A6A',
            'yellow-bright': '#FFE66D',
            'blue-soft': '#78B2FB'
          },
          animation: {
            'float': 'float 6s ease-in-out infinite',
            'pulse-soft': 'pulse-soft 2s infinite',
            'bounce-soft': 'bounce-soft 3s infinite',
            'slide-in': 'slide-in 0.5s ease-out',
            'fade-in': 'fade-in 0.3s ease-out'
          }
        }
      }
    }
  </script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script type="text/javascript"
      src="https://app.midtrans.com/snap/snap.js"
      data-client-key="<?php echo get_setting($pdo, 'midtrans_client_key'); ?>"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="manifest" href="manifest.json">
  <meta name="theme-color" content="#EE3A6A">
  <link rel="apple-touch-icon" href="assets/images/icon-192x192.png">
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #FFFEE0 0%, #F5A6BB 50%, #78B2FB 100%);
      min-height: 80vh;
      padding-bottom: 100px;
    }

    .bottom-nav-item.active svg, .bottom-nav-item.active span {
      color: #EE3A6A;
    }

    #install-container {
      display: none;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
    }

    @keyframes pulse-soft {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.8; }
    }

    @keyframes bounce-soft {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-5px); }
    }

    @keyframes slide-in {
      from { transform: translateY(20px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }

    @keyframes fade-in {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    .glass-effect {
      background: rgba(255, 254, 224, 0.9);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 230, 109, 0.3);
    }

    .gradient-bg {
      background: linear-gradient(135deg, #EE3A6A 0%, #9E0232 100%);
    }

    .card-hover {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .card-hover:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 40px rgba(158, 2, 50, 0.2);
    }
  </style>
</head>
<body class="min-h-screen">
  <div class="flex flex-col min-h-screen relative overflow-hidden">
    <!-- Background Animation -->
    <div class="fixed inset-0 pointer-events-none">
      <div class="absolute top-10 left-10 w-20 h-20 bg-yellow-bright rounded-full opacity-20 animate-float"></div>
      <div class="absolute top-32 right-20 w-16 h-16 bg-pink-light rounded-full opacity-30 animate-bounce-soft" style="animation-delay: 1s;"></div>
      <div class="absolute bottom-40 left-20 w-24 h-24 bg-blue-soft rounded-full opacity-25 animate-float" style="animation-delay: 2s;"></div>
      <div class="absolute bottom-20 right-10 w-18 h-18 bg-pink-accent rounded-full opacity-20 animate-pulse-soft"></div>
    </div>

    <!-- Simple Header for Pages -->
    <header class="relative bg-gradient-to-br from-pink-accent via-pink-dark to-pink-light rounded-b-[35px] shadow-2xl p-6 text-cream z-10 mx-4 mt-4 animate-slide-in">
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
                    <img src="<?php echo htmlspecialchars($user['foto_profil'] ?? 'assets/images/mask_group.svg'); ?>" alt="Profile" class="h-12 w-12 rounded-xl border-2 border-cream/50 object-cover shadow-lg" />
                    <div class="ml-3">
                        <h1 class="font-bold text-xl text-cream drop-shadow-sm"><?php echo htmlspecialchars($page_title); ?></h1>
                        <p class="text-sm text-cream/80 font-medium"><?php echo htmlspecialchars($user['nama_lengkap']); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow overflow-y-auto p-4 space-y-6">
        <!-- Navigation Sidebar -->
        <aside class="fixed bottom-0 left-0 right-0 bg-cream/95 shadow-lg border-t border-pink-light/30 z-20 p-4 rounded-t-3xl backdrop-blur-sm">
            <ul class="flex justify-around items-center">
                <li><a href="dashboard.php" class="block px-4 py-2 text-gray-800 hover:bg-pink-100 rounded-lg transition-colors">Dashboard</a></li>
                    <li><a href="progress.php" class="block px-4 py-2 text-gray-800 hover:bg-pink-100 rounded-lg transition-colors">Progress</a></li>
                <li><a href="jadwal.php" class="block px-4 py-2 text-gray-800 hover:bg-pink-100 rounded-lg transition-colors">Jadwal</a></li>
                <li><a href="profile.php" class="block px-4 py-2 text-gray-800 hover:bg-pink-100 rounded-lg transition-colors">Profile</a></li>
            </ul>
        </aside>
    <!-- End Main Content -->
    </main>
  </div>
</body>
</html>