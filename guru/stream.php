<?php
include 'partials/header.php';

// Retrieve student info from session, which was set during QR scan
$scanned_student_id = $_SESSION['scanned_student_id'] ?? null;
$scanned_student_name = $_SESSION['scanned_student_name'] ?? null;

// If no student was scanned, redirect back to the dashboard.
if (!$scanned_student_id) {
    $_SESSION['flash_message'] = "Pindai QR siswa terlebih dahulu sebelum memulai streaming.";
    header('Location: dashboard.php');
    exit();
}
?>
<title>Live Streaming</title>
<script src="https://unpkg.com/peerjs@1.5.2/dist/peerjs.min.js"></script>
</head>
<body class="bg-gray-900 text-white flex flex-col min-h-screen">
    <div class="flex-grow container mx-auto p-4 flex flex-col items-center justify-center">
        <h1 class="text-3xl font-bold mb-2 text-center">Live Class</h1>
        <p class="text-teal-400 mb-4">Streaming untuk: <span class="font-bold"><?php echo htmlspecialchars($scanned_student_name); ?></span></p>
        
        <div id="video-container" class="w-full max-w-4xl bg-black rounded-lg shadow-lg overflow-hidden aspect-video relative">
            <video id="local-video" autoplay muted playsinline class="w-full h-full object-cover transform scale-x-[-1]"></video>
            <div id="status-overlay" class="absolute inset-0 flex flex-col items-center justify-center bg-black bg-opacity-70 transition-opacity duration-500">
                <p id="status-text" class="text-xl font-semibold">Menyiapkan koneksi peer...</p>
            </div>
        </div>
        
        <div class="mt-6 flex items-center space-x-4">
            <button id="end-stream-btn" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-full flex items-center space-x-2 transition-transform transform hover:scale-105">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone-off"><path d="M10.68 13.31a16 16 0 0 0 3.42 3.42"/><path d="M3 6a9 9 0 0 1 12.13-6.71"/><path d="M21 15.36a9 9 0 0 1-6.71 5.33"/><path d="M2.71 2.71a1 1 0 0 0-1.42 1.42l18 18a1 1 0 0 0 1.42-1.42Z"/><path d="M15 6.66A5 5 0 0 0 10 4c-2.21 0-4 1.79-4 4a5 5 0 0 0 .34 1.5M17.34 14A5 5 0 0 0 20 10c0-2.21-1.79-4-4-4a5 5 0 0 0-1.5.34"/></svg>
                <span>Akhiri Streaming</span>
            </button>
        </div>
    </div>

    <script>
        const localVideo = document.getElementById('local-video');
        const statusText = document.getElementById('status-text');
        const statusOverlay = document.getElementById('status-overlay');
        const endStreamBtn = document.getElementById('end-stream-btn');
        
        const studentId = <?php echo json_encode($scanned_student_id); ?>;
        let localStream;
        let peer;

        function initializePeer(peerId) {
            peer = new Peer(peerId, {
                config: {
                    'iceServers': [
                        { urls: 'stun:stun.l.google.com:19302' },
                        { urls: 'stun:stun1.l.google.com:19302' }
                    ]
                }
            });

            // STEP 1: Be ready to answer calls immediately
            peer.on('call', call => {
                console.log('Incoming call received from', call.peer);
                
                if (localStream) {
                    console.log('Answering call with local stream.');
                    statusText.textContent = `Terhubung dengan <?php echo htmlspecialchars(addslashes($scanned_student_name)); ?>.`;
                    statusOverlay.style.opacity = '0';
                    setTimeout(() => { statusOverlay.style.display = 'none'; }, 500);
                    
                    call.answer(localStream); // Answer the call with the stream.
                } else {
                    console.error('Call received but local stream is not available yet. This should not happen.');
                }

                call.on('close', () => {
                    console.log('Call closed by student.');
                    statusText.textContent = "Siswa terputus.";
                    statusOverlay.style.display = 'flex';
                    statusOverlay.style.opacity = '1';
                });
            });

            peer.on('open', id => {
                console.log('PeerJS is ready. My ID is: ' + id);
                statusText.textContent = "Menyiapkan kamera...";

                // STEP 2: Get camera AFTER peer is ready to receive calls
                navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: true })
                    .then(stream => {
                        console.log('Camera and microphone access granted.');
                        localStream = stream;
                        localVideo.srcObject = stream;
                        statusText.textContent = "Menunggu siswa untuk terhubung...";

                        // STEP 3: Announce to the student ONLY AFTER EVERYTHING is ready
                        console.log('Updating database. Teacher is now discoverable.');
                        fetch('api_update_stream.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ student_id: studentId, stream_id: id, action: 'start' })
                        });
                    })
                    .catch(err => {
                        console.error("Failed to get local stream", err);
                        statusText.textContent = "Gagal mengakses kamera/mikrofon. Izinkan akses dan refresh.";
                    });
            });

            peer.on('error', err => {
                console.error('PeerJS error:', err);
                statusText.textContent = `Error: ${err.type}. Coba refresh halaman.`;
            });
        }

        // Generate a more unique ID for the teacher to avoid conflicts
        const teacherPeerId = `teacher-${studentId}-${Date.now()}`;
        initializePeer(teacherPeerId);
        
        function endStream() {
            const data = JSON.stringify({ student_id: studentId, action: 'end' });
            navigator.sendBeacon('api_update_stream.php', new Blob([data], {type: 'application/json'}));

            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
            }
            if (peer && !peer.destroyed) {
                peer.destroy();
            }
            window.location.href = 'dashboard.php';
        }

        endStreamBtn.addEventListener('click', endStream);
        window.addEventListener('beforeunload', endStream);

    </script>
    <?php include 'partials/footer.php'; ?>
</body>
</html>