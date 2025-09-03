<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['guru_id'])) {
    header('Location: index.php');
    exit();
}
require '../config/database.php';

// Get guru data
$stmt = $pdo->prepare("SELECT * FROM guru WHERE id = ?");
$stmt->execute([$_SESSION['guru_id']]);
$guru = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru - Anastasya Vocal Arts</title>

    <!-- PWA Configuration -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#EE3A6A">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="AVA Guru">
    <link rel="apple-touch-icon" href="../user/assets/images/icon-192x192.png">

    <!-- CSS -->
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
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(135deg, #FFFEE0 0%, #F5A6BB 50%, #78B2FB 100%);
            min-height: 100vh;
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

        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(158, 2, 50, 0.2);
        }

        .animate-slide-in {
            animation: slideInFromLeft 0.5s ease-out forwards;
        }
        @keyframes slideInFromLeft {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>

    <!-- PWA Scripts -->
    <script src="main.js" defer></script>
</head>
<body class="min-h-screen">
    <!-- Install PWA Prompt -->
    <div id="install-container" class="fixed top-4 left-4 right-4 z-50 p-4 glass-effect rounded-2xl shadow-lg border border-pink-light/30 animate-slide-in" style="display: none;">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-pink-accent to-pink-dark rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-cream" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 011 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                    </svg>
                </div>
                <div>
                    <h4 class="font-semibold text-pink-dark">Install Guru App</h4>
                    <p class="text-sm text-pink-dark/70">Akses mudah di HP Anda</p>
                </div>
            </div>
            <button id="install-button" class="px-4 py-2 bg-gradient-to-r from-pink-accent to-pink-dark text-cream rounded-xl font-medium hover:shadow-lg transition-all duration-300">
                Install
            </button>
        </div>
    </div>

    <div class="flex flex-col min-h-screen relative overflow-hidden">
        <!-- Background Animation -->
        <div class="fixed inset-0 pointer-events-none">
            <div class="absolute top-10 left-10 w-20 h-20 bg-yellow-bright rounded-full opacity-20 animate-float"></div>
            <div class="absolute top-32 right-20 w-16 h-16 bg-pink-light rounded-full opacity-30 animate-bounce-soft" style="animation-delay: 1s;"></div>
            <div class="absolute bottom-40 left-20 w-24 h-24 bg-blue-soft rounded-full opacity-25 animate-float" style="animation-delay: 2s;"></div>
            <div class="absolute bottom-20 right-10 w-18 h-18 bg-pink-accent rounded-full opacity-20 animate-pulse-soft"></div>
        </div>

        <!-- Modern Header -->
        <header class="relative bg-gradient-to-br from-pink-accent via-pink-dark to-pink-light rounded-b-[35px] shadow-2xl p-6 text-cream z-10  animate-slide-in">
            <div class="absolute inset-0 bg-gradient-to-br from-pink-accent/90 to-pink-dark/90 rounded-b-[35px] backdrop-blur-sm"></div>
            <div class="absolute top-0 right-0 w-32 h-32 bg-yellow-bright/20 rounded-full -translate-y-16 translate-x-16 animate-pulse-soft"></div>
            <div class="absolute bottom-0 left-0 w-24 h-24 bg-blue-soft/20 rounded-full translate-y-12 -translate-x-12 animate-float"></div>

            <div class="relative flex items-center justify-between">
                <div class="flex items-center">
                    <div class="group">
                        <div class="relative">
                            <div class="h-16 w-16 rounded-2xl border-3 border-cream/50 bg-gradient-to-br from-yellow-bright to-pink-accent flex items-center justify-center shadow-lg group-hover:scale-105 transition-transform duration-300">
                                <i data-lucide="graduation-cap" class="w-8 h-8 text-pink-dark"></i>
                            </div>
                            <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-yellow-bright rounded-full border-2 border-cream animate-pulse-soft"></div>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h1 class="font-bold text-xl text-cream drop-shadow-sm"><?php echo htmlspecialchars($guru['nama_lengkap']); ?></h1>
                        <p class="text-sm text-cream/80 font-medium">AVA Teacher</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="logout.php" class="relative p-3 rounded-2xl hover:bg-cream/10 transition-all duration-300 group">
                        <i data-lucide="log-out" class="w-6 h-6 text-cream group-hover:scale-110 transition-transform duration-300"></i>
                    </a>
                </div>
            </div>
        </header>

    <!-- Main content area -->
    <div class="flex-1 p-4">
    
    <script>
        lucide.createIcons();
    </script>