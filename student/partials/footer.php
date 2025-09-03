<!-- Modern Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 z-30">
        <div class="mx-2 mb-2 glass-effect rounded-2xl shadow-2xl border border-pink-light/30">
            <div class="flex justify-around items-center py-1 px-1">
                <a href="dashboard.php" class="bottom-nav-item flex flex-col items-center p-2 rounded-xl transition-all duration-300 <?php echo isActive('dashboard.php'); ?> hover:bg-pink-light/20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-pink-dark">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9,22 9,12 15,12 15,22"/>
                    </svg>
                    <span class="text-xs font-semibold mt-1 text-pink-dark">Home</span>
                </a>
                
                <a href="payment_history.php" class="bottom-nav-item flex flex-col items-center p-2 rounded-xl transition-all duration-300 <?php echo isActive('payment_history.php', 'month_payment.php'); ?> hover:bg-pink-light/20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-pink-dark">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                    <span class="text-xs font-semibold mt-1 text-pink-dark">Payment</span>
                </a>
                
                <a href="qr_attendance.php" class="bottom-nav-item flex flex-col items-center p-3 rounded-xl transition-all duration-300 hover:bg-pink-light/20">
                    <div class="relative">
                        <div class="w-12 h-12 bg-gradient-to-br from-pink-accent to-pink-dark rounded-xl flex items-center justify-center shadow-lg transform hover:scale-110 transition-transform duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-cream">
                                <rect x="3" y="3" width="5" height="5"/>
                                <rect x="16" y="3" width="5" height="5"/>
                                <rect x="3" y="16" width="5" height="5"/>
                                <rect x="14" y="14" width="7" height="7"/>
                                <rect x="13" y="11" width="3" height="3"/>
                                <rect x="11" y="13" width="3" height="3"/>
                            </svg>
                        </div>
                        <div class="absolute -top-1 -right-1 w-4 h-4 bg-yellow-bright rounded-full animate-pulse-soft"></div>
                    </div>
                    <span class="text-xs font-bold mt-1 text-pink-dark">QR</span>
                </a>
                
                <a href="stream.php" class="bottom-nav-item flex flex-col items-center p-3 rounded-xl transition-all duration-300 hover:bg-pink-light/20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-pink-dark">
                        <rect x="3" y="3" width="18" height="12" rx="2" ry="2"/>
                        <circle cx="9" cy="9" r="2"/>
                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                    </svg>
                    <span class="text-xs font-semibold mt-1 text-pink-dark">Gallery</span>
                </a>
                
                <a href="profile.php" class="bottom-nav-item flex flex-col items-center p-3 rounded-xl transition-all duration-300 hover:bg-pink-light/20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-pink-dark">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <span class="text-xs font-semibold mt-1 text-pink-dark">Profile</span>
                </a>
            </div>
        </div>
    </nav>
    
    <script src="main.js"></script>
</body>
</html>