<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div id="sidebar" class="sidebar text-gray-300 min-h-screen flex-shrink-0 relative">
    <!-- Logo Area -->
    <div class="p-4 md:p-6 border-b border-gray-600 bg-gradient-to-r from-indigo-600 to-purple-600">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-music text-indigo-600 text-lg"></i>
            </div>
            <div class="nav-text min-w-0">
                <h2 class="text-lg md:text-xl font-bold text-white truncate">AVA Admin</h2>
                <p class="text-xs text-indigo-200 truncate">Management System</p>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="mt-4 md:mt-6 px-3 md:px-4 space-y-1 md:space-y-2 pb-20">
        <!-- Main Menu -->
        <div class="mb-4 md:mb-6">
            <h3 class="nav-text px-2 md:px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 md:mb-3">Main Menu</h3>

            <a href="dashboard.php" class="nav-link flex items-center py-3 px-3 md:px-4 rounded-xl transition-all duration-200 hover:bg-gray-700 hover:text-white group <?php echo $current_page == 'dashboard.php' ? 'active-link' : ''; ?> min-h-[44px]">
                <div class="flex-shrink-0 w-5 h-5">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <span class="nav-text ml-3 md:ml-4 font-medium">Dashboard</span>
                <i class="nav-text fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-100 transition-opacity"></i>
            </a>

            <a href="siswa.php" class="nav-link flex items-center py-3 px-4 rounded-xl transition-all duration-200 hover:bg-gray-700 hover:text-white group <?php echo in_array($current_page, ['siswa.php', 'siswa_form.php']) ? 'active-link' : ''; ?>">
                <div class="flex-shrink-0 w-5 h-5">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <span class="nav-text ml-4 font-medium">Students</span>
                <i class="nav-text fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-100 transition-opacity"></i>
            </a>

            <a href="pendaftar.php" class="nav-link flex items-center py-3 px-4 rounded-xl transition-all duration-200 hover:bg-gray-700 hover:text-white group <?php echo $current_page == 'pendaftar.php' ? 'active-link' : ''; ?>">
                <div class="flex-shrink-0 w-5 h-5">
                    <i class="fas fa-user-plus"></i>
                </div>
                <span class="nav-text ml-4 font-medium">New Registrations</span>
                <i class="nav-text fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-100 transition-opacity"></i>
            </a>
        </div>

        <!-- Schedule & Classes -->
        <div class="mb-6">
            <h3 class="nav-text px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Schedule & Classes</h3>

            <a href="jadwal.php" class="nav-link flex items-center py-3 px-4 rounded-xl transition-all duration-200 hover:bg-gray-700 hover:text-white group <?php echo in_array($current_page, ['jadwal.php', 'jadwal_form.php']) ? 'active-link' : ''; ?>">
                <div class="flex-shrink-0 w-5 h-5">
                    <i class="fas fa-clock"></i>
                </div>
                <span class="nav-text ml-4 font-medium">Schedule</span>
                <i class="nav-text fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-100 transition-opacity"></i>
            </a>

            <a href="jadwal_calendar.php" class="nav-link flex items-center py-3 px-4 rounded-xl transition-all duration-200 hover:bg-gray-700 hover:text-white group <?php echo $current_page == 'jadwal_calendar.php' ? 'active-link' : ''; ?>">
                <div class="flex-shrink-0 w-5 h-5">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <span class="nav-text ml-4 font-medium">Calendar View</span>
                <i class="nav-text fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-100 transition-opacity"></i>
            </a>

            <a href="ijin_siswa.php" class="nav-link flex items-center py-3 px-4 rounded-xl transition-all duration-200 hover:bg-gray-700 hover:text-white group <?php echo $current_page == 'ijin_siswa.php' ? 'active-link' : ''; ?>">
                <div class="flex-shrink-0 w-5 h-5">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <span class="nav-text ml-4 font-medium">Leave Requests</span>
                <i class="nav-text fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-100 transition-opacity"></i>
            </a>
        </div>

        <!-- Financial -->
        <div class="mb-6">
            <h3 class="nav-text px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Financial</h3>

            <a href="tagihan.php" class="nav-link flex items-center py-3 px-4 rounded-xl transition-all duration-200 hover:bg-gray-700 hover:text-white group <?php echo $current_page == 'tagihan.php' ? 'active-link' : ''; ?>">
                <div class="flex-shrink-0 w-5 h-5">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <span class="nav-text ml-4 font-medium">Billing</span>
                <i class="nav-text fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-100 transition-opacity"></i>
            </a>

            <a href="pembayaran.php" class="nav-link flex items-center py-3 px-4 rounded-xl transition-all duration-200 hover:bg-gray-700 hover:text-white group <?php echo $current_page == 'pembayaran.php' ? 'active-link' : ''; ?>">
                <div class="flex-shrink-0 w-5 h-5">
                    <i class="fas fa-credit-card"></i>
                </div>
                <span class="nav-text ml-4 font-medium">Pembayaran</span>
                <i class="nav-text fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-100 transition-opacity"></i>
            </a>

            <a href="manual_payment_verification.php" class="nav-link flex items-center py-3 px-4 rounded-xl transition-all duration-200 hover:bg-gray-700 hover:text-white group <?php echo $current_page == 'manual_payment_verification.php' ? 'active-link' : ''; ?>">
                <div class="flex-shrink-0 w-5 h-5">
                    <i class="fas fa-file-upload"></i>
                </div>
                <span class="nav-text ml-4 font-medium">Verifikasi Transfer Manual</span>
                <i class="nav-text fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-100 transition-opacity"></i>
            </a>

            <a href="keuangan.php" class="nav-link flex items-center py-3 px-4 rounded-xl transition-all duration-200 hover:bg-gray-700 hover:text-white group <?php echo $current_page == 'keuangan.php' ? 'active-link' : ''; ?>">
                <div class="flex-shrink-0 w-5 h-5">
                    <i class="fas fa-chart-line"></i>
                </div>
                <span class="nav-text ml-4 font-medium">Financial Reports</span>
                <i class="nav-text fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-100 transition-opacity"></i>
            </a>
        </div>

        <!-- Content Management -->
        <div class="mb-6">
            <h3 class="nav-text px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Content</h3>

            <a href="ebook.php" class="nav-link flex items-center py-3 px-4 rounded-xl transition-all duration-200 hover:bg-gray-700 hover:text-white group <?php echo in_array($current_page, ['ebook.php', 'ebook_form.php']) ? 'active-link' : ''; ?>">
                <div class="flex-shrink-0 w-5 h-5">
                    <i class="fas fa-book-open"></i>
                </div>
                <span class="nav-text ml-4 font-medium">E-Books</span>
                <i class="nav-text fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-100 transition-opacity"></i>
            </a>

            <a href="kirim_notifikasi.php" class="nav-link flex items-center py-3 px-4 rounded-xl transition-all duration-200 hover:bg-gray-700 hover:text-white group <?php echo $current_page == 'kirim_notifikasi.php' ? 'active-link' : ''; ?>">
                <div class="flex-shrink-0 w-5 h-5">
                    <i class="fas fa-bell"></i>
                </div>
                <span class="nav-text ml-4 font-medium">Notifications</span>
                <i class="nav-text fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-100 transition-opacity"></i>
            </a>
        </div>

        <!-- Settings -->
        <div class="border-t border-gray-600 pt-6">
            <h3 class="nav-text px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Settings</h3>

            <a href="promo.php" class="nav-link flex items-center py-3 px-4 rounded-xl transition-all duration-200 hover:bg-gray-700 hover:text-white group <?php echo $current_page == 'promo.php' ? 'active-link' : ''; ?>">
                <div class="flex-shrink-0 w-5 h-5">
                    <i class="fas fa-tags"></i>
                </div>
                <span class="nav-text ml-4 font-medium">Promo Codes</span>
                <i class="nav-text fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-100 transition-opacity"></i>
            </a>

            <a href="pengaturan_midtrans.php" class="nav-link flex items-center py-3 px-4 rounded-xl transition-all duration-200 hover:bg-gray-700 hover:text-white group <?php echo $current_page == 'pengaturan_midtrans.php' ? 'active-link' : ''; ?>">
                <div class="flex-shrink-0 w-5 h-5">
                    <i class="fas fa-credit-card"></i>
                </div>
                <span class="nav-text ml-4 font-medium">Payment Gateway</span>
                <i class="nav-text fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-100 transition-opacity"></i>
            </a>

            <a href="pwa_settings.php" class="nav-link flex items-center py-3 px-4 rounded-xl transition-all duration-200 hover:bg-gray-700 hover:text-white group <?php echo $current_page == 'pwa_settings.php' ? 'active-link' : ''; ?>">
                <div class="flex-shrink-0 w-5 h-5">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <span class="nav-text ml-4 font-medium">PWA Settings</span>
                <i class="nav-text fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-100 transition-opacity"></i>
            </a>
        </div>


    </nav>


</div>