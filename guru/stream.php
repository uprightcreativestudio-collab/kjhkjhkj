<?php
include 'partials/header.php';

$scanned_student_id = $_SESSION['scanned_student_id'] ?? null;
$scanned_student_name = $_SESSION['scanned_student_name'] ?? null;

if (!$scanned_student_id) {
    $_SESSION['flash_message'] = "Pindai QR siswa terlebih dahulu sebelum memulai streaming.";
    header('Location: dashboard.php');
    exit();
}
?>
<title>Live Streaming</title>
<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
</head>
<body class="bg-gray-900 text-white flex flex-col min-h-screen">
    <div class="flex-grow container mx-auto p-4 flex flex-col items-center justify-center">
        <h1 class="text-3xl font-bold mb-2 text-center">Live Class</h1>
        <p class="text-teal-400 mb-4">Streaming untuk: <span class="font-bold"><?php echo htmlspecialchars($scanned_student_name); ?></span></p>
        
        <div id="video-container" class="w-full max-w-4xl bg-black rounded-lg shadow-lg overflow-hidden aspect-video relative">
            <video id="local-video" autoplay muted playsinline class="w-full h-full object-cover transform scale-x-[-1]"></video>
            <div id="status-overlay" class="absolute inset-0 flex flex-col items-center justify-center bg-black bg-opacity-70 transition-opacity duration-500">
                <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-teal-400 mb-4"></div>
                <p id="status-text" class="text-xl font-semibold">Menyiapkan streaming...</p>
                <p id="connection-info" class="text-sm text-gray-300 mt-2">Menunggu koneksi siswa...</p>
            </div>
        </div>
        
        <div class="mt-6 flex items-center space-x-4">
            <div id="connection-status" class="flex items-center space-x-2">
                <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
                <span class="text-sm">Menunggu siswa...</span>
            </div>
            
            <button id="end-stream-btn" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-full flex items-center space-x-2 transition-transform transform hover:scale-105">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.68 13.31a16 16 0 0 0 3.42 3.42"/>
                    <path d="M3 6a9 9 0 0 1 12.13-6.71"/>
                    <path d="M21 15.36a9 9 0 0 1-6.71 5.33"/>
                    <path d="M2.71 2.71a1 1 0 0 0-1.42 1.42l18 18a1 1 0 0 0 1.42-1.42Z"/>
                </svg>
                <span>Akhiri Streaming</span>
            </button>
        </div>
    </div>

    <script>
        const localVideo = document.getElementById('local-video');
        const statusText = document.getElementById('status-text');
        const connectionInfo = document.getElementById('connection-info');
        const statusOverlay = document.getElementById('status-overlay');
        const endStreamBtn = document.getElementById('end-stream-btn');
        const connectionStatus = document.getElementById('connection-status');
        
        const studentId = <?php echo json_encode($scanned_student_id); ?>;
        let localStream;
        let socket;
        let isStreaming = false;
        let connectedStudents = 0;

        // Initialize Socket.IO connection
        function initializeSocket() {
            // Use Socket.IO for better real-time communication
            socket = io('/', {
                transports: ['websocket', 'polling'],
                upgrade: true,
                rememberUpgrade: true
            });

            socket.on('connect', () => {
                console.log('Connected to server');
                statusText.textContent = "Terhubung ke server...";
                
                // Join teacher room
                socket.emit('join-teacher-room', {
                    teacherId: <?php echo $_SESSION['guru_id']; ?>,
                    studentId: studentId,
                    teacherName: '<?php echo addslashes($_SESSION['guru_nama']); ?>'
                });
            });

            socket.on('student-joined', (data) => {
                console.log('Student joined:', data);
                connectedStudents++;
                updateConnectionStatus();
                
                if (statusOverlay.style.opacity !== '0') {
                    statusText.textContent = `Terhubung dengan ${data.studentName}`;
                    connectionInfo.textContent = "Streaming aktif";
                    setTimeout(() => {
                        statusOverlay.style.opacity = '0';
                        setTimeout(() => { statusOverlay.style.display = 'none'; }, 500);
                    }, 2000);
                }
            });

            socket.on('student-left', (data) => {
                console.log('Student left:', data);
                connectedStudents = Math.max(0, connectedStudents - 1);
                updateConnectionStatus();
                
                if (connectedStudents === 0) {
                    statusOverlay.style.display = 'flex';
                    statusOverlay.style.opacity = '1';
                    statusText.textContent = "Menunggu siswa bergabung...";
                    connectionInfo.textContent = "Tidak ada siswa yang terhubung";
                }
            });

            socket.on('disconnect', () => {
                console.log('Disconnected from server');
                statusText.textContent = "Koneksi terputus. Mencoba menyambung kembali...";
            });
        }

        function updateConnectionStatus() {
            const statusDot = connectionStatus.querySelector('.w-3');
            const statusText = connectionStatus.querySelector('span');
            
            if (connectedStudents > 0) {
                statusDot.className = 'w-3 h-3 bg-green-500 rounded-full';
                statusText.textContent = `${connectedStudents} siswa terhubung`;
            } else {
                statusDot.className = 'w-3 h-3 bg-red-500 rounded-full animate-pulse';
                statusText.textContent = 'Menunggu siswa...';
            }
        }

        async function startStreaming() {
            try {
                statusText.textContent = "Mengakses kamera...";
                
                localStream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        facingMode: 'user',
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    }, 
                    audio: true 
                });
                
                localVideo.srcObject = localStream;
                isStreaming = true;
                
                statusText.textContent = "Kamera siap. Menunggu siswa...";
                connectionInfo.textContent = "Siswa dapat bergabung sekarang";
                
                // Notify server that teacher is ready to stream
                if (socket) {
                    socket.emit('teacher-ready', {
                        studentId: studentId,
                        teacherId: <?php echo $_SESSION['guru_id']; ?>
                    });
                }
                
                // Update database
                fetch('api_update_stream.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        student_id: studentId, 
                        stream_id: `teacher-${studentId}-${Date.now()}`, 
                        action: 'start' 
                    })
                });
                
            } catch (err) {
                console.error("Failed to get local stream", err);
                statusText.textContent = "Gagal mengakses kamera/mikrofon. Izinkan akses dan refresh.";
                connectionInfo.textContent = "Periksa izin kamera dan mikrofon";
            }
        }
        
        function endStream() {
            if (socket) {
                socket.emit('teacher-end-stream', { studentId: studentId });
                socket.disconnect();
            }
            
            const data = JSON.stringify({ student_id: studentId, action: 'end' });
            navigator.sendBeacon('api_update_stream.php', new Blob([data], {type: 'application/json'}));

            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
            }
            
            window.location.href = 'scan.php?qr_data=<?php echo urlencode($user['qr_code_identifier'] ?? ''); ?>';
        }

        // Initialize everything
        initializeSocket();
        startStreaming();

        endStreamBtn.addEventListener('click', endStream);
        window.addEventListener('beforeunload', endStream);
    </script>
    
    <?php include 'partials/footer.php'; ?>
</body>
</html>