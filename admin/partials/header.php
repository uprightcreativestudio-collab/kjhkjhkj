<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require_once '../config/database.php';
$current_page = basename($_SERVER['PHP_SELF']);

// Flash message logic
$flash_message = '';
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Dashboard'; ?> - Anastasya Vocal Arts</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #6366f1;
            --primary-dark: #4f46e5;
            --secondary-color: #f1f5f9;
            --accent-color: #06b6d4;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --dark-color: #1e293b;
            --light-color: #f8fafc;
        }
        
        * {
            box-sizing: border-box;
        }
        
        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        .sidebar { 
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 50;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            background: linear-gradient(180deg, #1e293b 0%, #334155 100%);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow-y: auto;
            overflow-x: hidden;
        }
        
        .sidebar .nav-text { 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
        }
        
        .sidebar.collapsed { 
            width: 4rem; 
        }
        
        .sidebar.collapsed .nav-text { 
            opacity: 0; 
            transform: translateX(-20px); 
            pointer-events: none; 
            visibility: hidden;
        }
        
        .sidebar:not(.collapsed) { 
            width: 16rem; 
        }
        
        .main-content { 
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: transparent;
            min-height: 100vh;
            width: 100%;
        }
        
        .sidebar.collapsed ~ .main-content { 
            margin-left: 4rem; 
        }
        
        .sidebar:not(.collapsed) ~ .main-content { 
            margin-left: 16rem; 
        }
        
        .nav-link {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
        }
        
        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s;
        }
        
        .nav-link:hover::before {
            left: 100%;
        }
        
        .active-link { 
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }
        
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(226, 232, 240, 0.8);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .stats-card {
            position: relative;
            overflow: hidden;
        }
        
        .stats-card::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #667eea, #764ba2, #f093fb, #f5576c);
            border-radius: 18px;
            z-index: -1;
            animation: gradient-rotate 4s linear infinite;
        }
        
        @keyframes gradient-rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .header-bar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            position: sticky;
            top: 0;
            z-index: 40;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3);
        }
        
        .flash-message {
            animation: slideInRight 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .toggle-btn {
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.2);
            backdrop-filter: blur(10px);
            color: var(--primary-color);
        }
        
        .toggle-btn:hover {
            background: rgba(99, 102, 241, 0.2);
            transform: scale(1.05);
        }
        
        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 16rem !important;
            }
            
            .sidebar.mobile-open {
                transform: translateX(0);
            }
            
            .sidebar.collapsed {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0 !important;
                width: 100%;
            }
            
            .header-bar {
                padding: 1rem;
            }
            
            .header-bar h1 {
                font-size: 1.25rem;
            }
            
            .mobile-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 49;
                display: none;
            }
            
            .mobile-overlay.active {
                display: block;
            }
        }
        
        @media (max-width: 640px) {
            .header-bar {
                padding: 0.75rem;
            }
            
            .header-bar .flex {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .header-bar .hidden.md\\:flex {
                display: none !important;
            }
        }
        
        /* Tablet adjustments */
        @media (min-width: 769px) and (max-width: 1024px) {
            .sidebar:not(.collapsed) { 
                width: 14rem; 
            }
            
            .sidebar:not(.collapsed) ~ .main-content { 
                margin-left: 14rem; 
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="mobile-overlay" id="mobileOverlay" onclick="closeMobileSidebar()"></div>
    <div class="flex min-h-screen">
        <?php include 'sidebar.php'; ?>
        <div class="flex-1 flex flex-col main-content">
            <!-- Header Bar -->
            <div class="header-bar px-4 md:px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3 md:space-x-4">
                        <button onclick="toggleSidebar()" class="toggle-btn p-2 rounded-lg transition-all duration-300 hover:bg-indigo-100">
                            <i class="fas fa-bars text-lg"></i>
                        </button>
                        <div>
                            <h1 class="text-xl md:text-2xl font-bold text-gray-800"><?php echo $page_title ?? 'Dashboard'; ?></h1>
                            <p class="text-xs md:text-sm text-gray-500 hidden sm:block">Anastasya Vocal Arts Admin Panel</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 md:space-x-4">
                        <div class="hidden md:flex items-center space-x-2 text-sm text-gray-500">
                            <i class="fas fa-calendar"></i>
                            <span><?php echo date('d M Y'); ?></span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-white text-xs"></i>
                            </div>
                            <span class="hidden sm:block text-sm font-medium text-gray-700">Admin</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if ($flash_message): ?>
            <div class="flash-message mx-6 mt-4">
                <div class="bg-gradient-to-r from-green-400 to-green-500 text-white p-4 rounded-xl shadow-lg" role="alert">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-3"></i>
                        <p class="font-medium"><?php echo $flash_message; ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <script>
                function toggleSidebar() {
                    const sidebar = document.getElementById('sidebar');
                    const overlay = document.getElementById('mobileOverlay');
                    
                    if (window.innerWidth <= 768) {
                        // Mobile behavior
                        sidebar.classList.toggle('mobile-open');
                        overlay.classList.toggle('active');
                    } else {
                        // Desktop behavior
                        sidebar.classList.toggle('collapsed');
                    }
                }
                
                function closeMobileSidebar() {
                    const sidebar = document.getElementById('sidebar');
                    const overlay = document.getElementById('mobileOverlay');
                    
                    if (window.innerWidth <= 768) {
                        sidebar.classList.remove('mobile-open');
                        overlay.classList.remove('active');
                    }
                }
                
                // Close mobile sidebar when clicking on nav links
                document.addEventListener('DOMContentLoaded', function() {
                    const navLinks = document.querySelectorAll('.nav-link');
                    navLinks.forEach(link => {
                        link.addEventListener('click', function() {
                            if (window.innerWidth <= 768) {
                                closeMobileSidebar();
                            }
                        });
                    });
                    
                    // Handle window resize
                    window.addEventListener('resize', function() {
                        const sidebar = document.getElementById('sidebar');
                        const overlay = document.getElementById('mobileOverlay');
                        
                        if (window.innerWidth > 768) {
                            sidebar.classList.remove('mobile-open');
                            overlay.classList.remove('active');
                        }
                    });
                });
            </script>
