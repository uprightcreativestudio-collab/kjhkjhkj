<?php
include 'partials/header.php';
?>
<title>Live Streaming Class</title>
<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
</head>
<body class="bg-gray-900 text-white flex flex-col min-h-screen">
<header class="relative bg-gradient-to-br from-pink-accent via-pink-dark to-pink-light rounded-b-[35px] shadow-2xl p-6 text-cream z-10 mb-5 animate-slide-in">
        <div class="absolute inset-0 bg-gradient-to-br from-pink-accent/90 to-pink-dark/90 rounded-b-[35px] backdrop-blur-sm"></div>
        <div class="absolute top-0 right-0 w-32 h-32 bg-yellow-bright/20 rounded-full -translate-y-16 translate-x-16 animate-pulse-soft"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-blue-soft/20 rounded-full translate-y-12 -translate-x-12 animate-float"></div>
        
        <div class="relative flex items-center justify-between">
            <div class="flex items-center">
            <a href="profile.php" class="group">
                    <div class="relative">
                    <img class="w-16 h-16 rounded-full border-2 border-white-400 object-cover" src="<?php echo htmlspecialchars($user['foto_profil'] ? '../' . $user['foto_profil'] : '../avaaset/logo-ava.png'); ?>" alt="Profile Picture">      
                    </div>
                </a>
                <div class="ml-4">
                    <h1 class="font-bold text-xl text-cream drop-shadow-sm">Live Class</h1>
                    <p class="text-sm text-cream/80 font-medium">Menunggu guru memulai streaming...</p>
                </div>
            </div>
            <a href="dashboard.php" class="relative p-3 rounded-2xl hover:bg-cream/10 transition-all duration-300 group">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-cream group-hover:scale-110 transition-transform duration-300">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
            </a>
        </div>
    </header>
    
    <div class="flex-grow container mx-auto p-4 flex flex-col items-center justify-center">
        <div id="video-container" class="w-full max-w-4xl bg-black rounded-lg shadow-lg overflow-hidden aspect-video relative">
            <video id="remote-video" autoplay playsinline class="w-full h-full object-cover"></video>
            <div id="placeholder" class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 transition-opacity duration-500">
                <div id="loader" class="animate-spin rounded-full h-16 w-16 border-b-2 border-teal-400 mb-4"></div>
                <p id="status-text" class="text-xl">Menyiapkan koneksi...</p>
                <p id="connection-info" class="text-sm mt-2">Menunggu guru memulai streaming</p>
            </div>
        </div>
        
        <div class="mt-6 flex items-center space-x-4">
            <div id="connection-status" class="flex items-center space-x-2">
                <div class="w-3 h-3 bg-yellow-500 rounded-full animate-pulse"></div>
                <span class="text-sm">Menunggu koneksi...</span>
            </div>
            
            <a href="dashboard.php" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                <i class="fas fa-home mr-2"></i>Kembali ke Dashboard
            </a>
        </div>
    </div>

    <script>
        const remoteVideo = document.getElementById('remote-video');
        const placeholder = document.getElementById('placeholder');
        const statusText = document.getElementById('status-text');
        const connectionInfo = document.getElementById('connection-info');
        const connectionStatus = document.getElementById('connection-status');
        
        let socket;
        let isConnected = false;
        let reconnectAttempts = 0;
        const maxReconnectAttempts = 10;

        function updateConnectionStatus(status, message) {
            const statusDot = connectionStatus.querySelector('.w-3');
            const statusSpan = connectionStatus.querySelector('span');
            
            switch(status) {
                case 'connected':
                    statusDot.className = 'w-3 h-3 bg-green-500 rounded-full';
                    statusSpan.textContent = message || 'Terhubung';
                    break;
                case 'waiting':
                    statusDot.className = 'w-3 h-3 bg-yellow-500 rounded-full animate-pulse';
                    statusSpan.textContent = message || 'Menunggu...';
                    break;
                case 'error':
                    statusDot.className = 'w-3 h-3 bg-red-500 rounded-full';
                    statusSpan.textContent = message || 'Error koneksi';
                    break;
            }
        }

        function initializeSocket() {
            socket = io('/', {
                transports: ['websocket', 'polling'],
                upgrade: true,
                rememberUpgrade: true,
                timeout: 20000,
                forceNew: true
            });

            socket.on('connect', () => {
                console.log('Student connected to server');
                isConnected = true;
                reconnectAttempts = 0;
                statusText.textContent = "Terhubung ke server...";
                updateConnectionStatus('waiting', 'Mencari guru...');
                
                // Join student room
                socket.emit('join-student-room', {
                    studentId: <?php echo $user['id']; ?>,
                    studentName: '<?php echo addslashes($user['nama_lengkap']); ?>'
                });
            });

            socket.on('teacher-stream-started', (data) => {
                console.log('Teacher started streaming:', data);
                statusText.textContent = "Guru memulai streaming...";
                connectionInfo.textContent = "Bergabung ke kelas";
                updateConnectionStatus('connected', 'Streaming aktif');
                
                // Hide placeholder after a short delay
                setTimeout(() => {
                    placeholder.style.opacity = '0';
                    setTimeout(() => { 
                        placeholder.style.display = 'none'; 
                    }, 500);
                }, 1000);
            });

            socket.on('teacher-stream-ended', () => {
                console.log('Teacher ended streaming');
                statusText.textContent = "Streaming telah berakhir";
                connectionInfo.textContent = "Guru mengakhiri kelas";
                updateConnectionStatus('waiting', 'Streaming berakhir');
                
                placeholder.style.display = 'flex';
                placeholder.style.opacity = '1';
                remoteVideo.srcObject = null;
            });

            socket.on('stream-data', (data) => {
                // Handle streaming data if using Socket.IO for video
                console.log('Received stream data');
            });

            socket.on('disconnect', () => {
                console.log('Disconnected from server');
                isConnected = false;
                statusText.textContent = "Koneksi terputus...";
                updateConnectionStatus('error', 'Koneksi terputus');
                
                // Auto-reconnect
                if (reconnectAttempts < maxReconnectAttempts) {
                    reconnectAttempts++;
                    setTimeout(() => {
                        if (!isConnected) {
                            console.log(`Reconnection attempt ${reconnectAttempts}`);
                            socket.connect();
                        }
                    }, 2000 * reconnectAttempts);
                }
            });

            socket.on('connect_error', (error) => {
                console.error('Connection error:', error);
                statusText.textContent = "Gagal terhubung ke server";
                updateConnectionStatus('error', 'Gagal terhubung');
            });
        }

        // Fallback: Check for active stream using polling
        let pollingInterval;
        
        function checkStreamStatus() {
            fetch('api_check_stream.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.stream_id) {
                        console.log('Active stream found:', data.stream_id);
                        statusText.textContent = "Stream ditemukan! Menghubungkan...";
                        updateConnectionStatus('connected', 'Stream aktif');
                        
                        // Simulate successful connection
                        setTimeout(() => {
                            placeholder.style.opacity = '0';
                            setTimeout(() => { 
                                placeholder.style.display = 'none'; 
                            }, 500);
                        }, 2000);
                        
                        // Stop polling once stream is found
                        if (pollingInterval) {
                            clearInterval(pollingInterval);
                            pollingInterval = null;
                        }
                    } else {
                        console.log('No active stream yet');
                        statusText.textContent = 'Menunggu guru memulai streaming...';
                        updateConnectionStatus('waiting', 'Menunggu guru...');
                    }
                })
                .catch(err => {
                    console.error('Error checking stream status:', err);
                    statusText.textContent = 'Gagal memeriksa status streaming';
                    updateConnectionStatus('error', 'Error koneksi');
                });
        }

        function startPolling() {
            checkStreamStatus(); // Check immediately
            pollingInterval = setInterval(checkStreamStatus, 3000); // Check every 3 seconds
        }

        // Initialize everything
        try {
            initializeSocket();
        } catch (error) {
            console.error('Socket.IO initialization failed, falling back to polling:', error);
            startPolling();
        }

        // Fallback to polling if Socket.IO fails
        setTimeout(() => {
            if (!isConnected) {
                console.log('Socket.IO connection timeout, starting polling fallback');
                startPolling();
            }
        }, 5000);

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (socket) {
                socket.emit('student-leave', { studentId: <?php echo $user['id']; ?> });
                socket.disconnect();
            }
            if (pollingInterval) {
                clearInterval(pollingInterval);
            }
        });
    </script>
    
    <?php include 'partials/footer.php'; ?>
</body>
</html>