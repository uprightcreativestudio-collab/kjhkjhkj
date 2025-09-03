</div> <!-- Close main content area -->
        
        <!-- Bottom Navigation -->
        <nav class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-filter backdrop-blur-lg border-t border-pink-light/30 z-50">
            <div class="flex justify-around items-center py-2 px-4">
                <a href="dashboard.php" class="bottom-nav-item flex flex-col items-center p-2 text-pink-dark/70 hover:text-pink-accent transition-colors">
                    <i data-lucide="home" class="w-6 h-6"></i>
                    <span class="text-xs font-medium mt-1">Dashboard</span>
                </a>
                <a href="scan.php" class="bottom-nav-item flex flex-col items-center p-2 text-pink-dark/70 hover:text-pink-accent transition-colors">
                    <i data-lucide="qr-code" class="w-6 h-6"></i>
                    <span class="text-xs font-medium mt-1">Scan QR</span>
                </a>
                <a href="jadwal_calendar.php" class="bottom-nav-item flex flex-col items-center p-2 text-pink-dark/70 hover:text-pink-accent transition-colors">
                    <i data-lucide="calendar" class="w-6 h-6"></i>
                    <span class="text-xs font-medium mt-1">Jadwal</span>
                </a>
            </div>
        </nav>
        
        <footer class="glass-effect mt-8 py-4 text-center text-sm text-pink-dark/70">
            &copy; <?php echo date('Y'); ?> Anastasya Vocal Arts. All rights reserved.
        </footer>
    </div>
    
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
