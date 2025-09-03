<?php
// session_start() is now handled by header.php
include 'partials/header.php';
?>
<title>Live Streaming Class</title>
<script src="https://unpkg.com/peerjs@1.5.2/dist/peerjs.min.js"></script>
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
                    <h1 class="font-bold text-xl text-cream drop-shadow-sm"><?php echo htmlspecialchars($user['nama_lengkap']); ?></h1>
                    <p class="text-sm text-cream/80 font-medium"> <?php echo htmlspecialchars($user['qr_code_identifier']); ?></p>
                </div>
            </div>
            <a href="notifikasi.php" class="relative p-3 rounded-2xl hover:bg-cream/10 transition-all duration-300 group">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-cream group-hover:scale-110 transition-transform duration-300">
                    <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
                    <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
                </svg>
                <div class="absolute top-2 right-2 w-3 h-3 bg-yellow-bright rounded-full animate-bounce-soft"></div>
            </a>
        </div>
    </header>
    <div class="flex-grow container mx-auto p-4 flex flex-col items-center justify-center">
      
        <div id="video-container" class="w-full max-w-4xl bg-black rounded-lg shadow-lg overflow-hidden aspect-video relative">
            <video id="remote-video" autoplay playsinline class="w-full h-full object-cover"></video>
            <div id="placeholder" class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 transition-opacity duration-500">
                <div id="loader" class="animate-spin rounded-full h-16 w-16 border-b-2 border-teal-400 mb-4"></div>
                <p id="status-text" class="text-xl">Menyiapkan koneksi...</p>
            </div>
        </div>
    </div>

    <script>
        const remoteVideo = document.getElementById('remote-video');
        const placeholder = document.getElementById('placeholder');
        const statusText = document.getElementById('status-text');
        const loader = document.getElementById('loader');
        
        let pollingInterval = null;
        let currentCall = null;

        // 1. Initialize Peer object immediately on page load.
        const peer = new Peer(undefined, {
            config: {
                'iceServers': [
                    { urls: 'stun:stun.l.google.com:19302' },
                    { urls: 'stun:stun1.l.google.com:19302' }
                ]
            }
        });

        // 2. Wait for our own peer connection to be open.
        peer.on('open', id => {
            console.log('Student PeerJS is ready. My ID is: ' + id);
            statusText.textContent = 'Koneksi siap. Mencari sesi guru...';
            // 3. ONLY NOW, start looking for the teacher's stream.
            startPolling();
        });

        function connectToTeacher(teacherStreamId) {
            if (currentCall) {
                console.log('Already in a call. Ignoring new connection attempt.');
                return;
            }
            
            console.log('Teacher stream found:', teacherStreamId, '. Attempting to call.');
            statusText.textContent = 'Stream ditemukan! Menghubungkan...';

            // 4. Call the teacher using the already-opened peer connection.
            // We send an empty stream because we are only receiving.
            const call = peer.call(teacherStreamId, new MediaStream());
            currentCall = call;

            call.on('stream', remoteStream => {
                console.log('Stream received from teacher.');
                placeholder.style.display = 'none';
                remoteVideo.srcObject = remoteStream;
                remoteVideo.play().catch(e => console.error("Video play failed:", e));
                if (pollingInterval) {
                    clearInterval(pollingInterval);
                    pollingInterval = null;
                }
            });

            call.on('close', () => {
                console.log('Stream ended by teacher.');
                statusText.textContent = 'Streaming telah berakhir.';
                placeholder.style.display = 'flex';
                remoteVideo.srcObject = null;
                currentCall = null;
                // Optional: restart polling if you want to auto-reconnect to a new session
                // startPolling(); 
            });

            call.on('error', err => {
                console.error('Peer call error:', err);
                statusText.textContent = 'Gagal terhubung. Mencoba lagi...';
                currentCall = null;
            });
        }

        function checkStreamStatus() {
            fetch('api_check_stream.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.stream_id) {
                        // Stop polling and attempt to connect
                        if (pollingInterval) {
                            clearInterval(pollingInterval);
                            pollingInterval = null;
                        }
                        connectToTeacher(data.stream_id);
                    } else {
                        console.log('No active stream yet. Waiting...');
                        statusText.textContent = 'Menunggu guru memulai streaming...';
                    }
                })
                .catch(err => {
                    console.error('Error checking stream status:', err);
                    statusText.textContent = 'Gagal memeriksa status. Memeriksa kembali...';
                });
        }

        function startPolling() {
            if (pollingInterval) clearInterval(pollingInterval);
            checkStreamStatus(); // Check immediately
            pollingInterval = setInterval(checkStreamStatus, 5000); // Then check every 5 seconds
        }

        peer.on('error', err => {
            console.error('PeerJS main error:', err);
            statusText.textContent = `Error Koneksi: ${err.type}. Refresh halaman.`;
            loader.style.display = 'none';
        });

        window.addEventListener('beforeunload', () => {
            if (currentCall) currentCall.close();
            if (peer && !peer.destroyed) peer.destroy();
            if (pollingInterval) clearInterval(pollingInterval);
        });
    </script>
    <?php include 'partials/footer.php'; ?>
</body>
</html>