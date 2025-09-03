<?php 
require_once 'config/database.php'; 

// Check if user has pending payment via GET parameter (from email link)
if (isset($_GET['email'])) {
    $email = $_GET['email'];
    $stmt = $pdo->prepare("
        SELECT p.order_id, p.transaction_status, s.nama_lengkap 
        FROM payments p 
        JOIN siswa s ON p.student_id = s.id 
        WHERE s.email = ? AND p.transaction_status IN ('pending', 'challenge')
    ");
    $stmt->execute([$email]);
    $pending_payment = $stmt->fetch();
    
    if ($pending_payment) {
        header('Location: payment_page.php?order_id=' . $pending_payment['order_id']);
        exit;
    }
}

// Check if user has pending payment via POST (from form submission)
if (isset($_POST['email'])) {
    $email = $_POST['email'];
    $stmt = $pdo->prepare("
        SELECT p.order_id, p.transaction_status, s.nama_lengkap 
        FROM payments p 
        JOIN siswa s ON p.student_id = s.id 
        WHERE s.email = ? AND p.transaction_status IN ('pending', 'challenge')
    ");
    $stmt->execute([$email]);
    $pending_payment = $stmt->fetch();
    
    if ($pending_payment) {
        $error_message = "Anda masih memiliki pembayaran pending dengan Order ID: " . $pending_payment['order_id'] . ". Anda akan diarahkan ke halaman pembayaran.";
        header('Location: payment_page.php?order_id=' . $pending_payment['order_id'] . '&error=' . urlencode($error_message));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anastasya Vocal Arts</title>
     <link rel="icon" type="image/png" sizes="32x32" href="avaaset/logo-ava.png">
  <link rel="icon" type="image/png" sizes="16x16" href="avaaset/logo-ava.png">

  <!-- Untuk iOS (Apple Touch Icon) -->
  <link rel="apple-touch-icon" href="avaaset/logo-ava.png">

  <!-- Untuk Android/Chrome -->
  <link rel="icon" sizes="192x192" href="avaaset/logo-ava.png">
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    <style>
        :root {
            --cream: #FFFEE0;
            --pink-light: #F5A6BB;
            --pink-dark: #9E0232;
            --pink-accent: #EE3A6A;
            --yellow-bright: #FFE66D;
            --blue-soft: #78B2FB;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Hanken Grotesk", Arial, Helvetica, sans-serif;
            background: linear-gradient(135deg, var(--blue-soft) 0%, var(--pink-light) 50%, var(--cream) 100%);
            min-height: 100vh;
            line-height: 1.6;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background Elements */
        .bg-element {
            position: fixed;
            pointer-events: none;
            z-index: 1;
        }

        .bg-circle-1 {
            top: 10%;
            left: 10%;
            width: 150px;
            height: 150px;
            background: url('avaaset/AVA-Concentric-1-Gold.svg') no-repeat center;
            background-size: contain;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }

        .bg-circle-2 {
            top: 20%;
            right: 15%;
            width: 120px;
            height: 120px;
            background: url('avaaset/AVA-Bars-Blue.svg') no-repeat center;
            background-size: contain;
            opacity: 0.1;
            animation: float 8s ease-in-out infinite reverse;
        }

        .bg-circle-3 {
            bottom: 20%;
            left: 20%;
            width: 100px;
            height: 100px;
            background: url('avaaset/AVA-Flow-Burgundy.svg') no-repeat center;
            background-size: contain;
            opacity: 0.1;
            animation: float 7s ease-in-out infinite;
        }

        .bg-circle-4 {
            bottom: 30%;
            right: 10%;
            width: 130px;
            height: 130px;
            background: url('avaaset/AVA-Dots-Gold.svg') no-repeat center;
            background-size: contain;
            opacity: 0.1;
            animation: float 9s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }

        /* Header */
        .header {
            position: relative;
            z-index: 100;
            background: rgba(255, 254, 224, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(158, 2, 50, 0.1);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
            color: var(--pink-dark);
            text-decoration: none;
        }

        .logo img {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }

        .logo-text {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--pink-dark), var(--pink-accent));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .nav-link {
            color: var(--pink-dark);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            color: var(--pink-accent);
            transform: translateY(-2px);
        }

        .nav-btn {
            background: linear-gradient(135deg, var(--pink-accent), var(--pink-dark));
            color: var(--cream);
            padding: 12px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .nav-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(238, 58, 106, 0.4);
        }

        /* Main Container */
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
            position: relative;
            z-index: 10;
        }

        /* Error Message */
        .error-message {
            background: rgba(220, 38, 38, 0.1);
            border: 2px solid rgba(220, 38, 38, 0.3);
            color: #dc2626;
            padding: 20px;
            border-radius: 20px;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
            opacity: 0;
            transform: translateY(-20px);
        }

        /* Hero Section */
        .hero-section {
            text-align: center;
            margin-bottom: 50px;
            opacity: 0;
            transform: translateY(30px);
        }

        .hero-title {
            font-size: clamp(2.5rem, 6vw, 4rem);
            font-weight: 900;
            background: linear-gradient(135deg, var(--pink-dark), var(--pink-accent));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: var(--pink-dark);
            opacity: 0.8;
        }

        /* Progress Indicator */
        .progress-container {
            display: flex;
            justify-content: center;
            margin-bottom: 50px;
            opacity: 0;
            transform: translateY(20px);
        }

        .progress-wrapper {
            display: flex;
            align-items: center;
            background: rgba(255, 254, 224, 0.8);
            padding: 20px 40px;
            border-radius: 50px;
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 30px rgba(158, 2, 50, 0.1);
        }

        .step-indicator {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
            margin-right: 15px;
            transition: all 0.3s ease;
            background: rgba(245, 166, 187, 0.3);
            color: var(--pink-dark);
        }

        .step-indicator.active {
            background: linear-gradient(135deg, var(--pink-accent), var(--pink-dark));
            color: var(--cream);
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(238, 58, 106, 0.4);
        }

        .step-indicator.completed {
            background: linear-gradient(135deg, var(--yellow-bright), #fbbf24);
            color: var(--pink-dark);
        }

        .step-text {
            font-size: 16px;
            font-weight: 500;
            color: var(--pink-dark);
            margin-right: 40px;
        }

        .step-text:last-child {
            margin-right: 0;
        }

        /* Form Container */
        .form-container {
            background: rgba(255, 254, 224, 0.95);
            border-radius: 30px;
            padding: 50px;
            box-shadow: 0 25px 60px rgba(158, 2, 50, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 254, 224, 0.2);
            position: relative;
            overflow: hidden;
        }

        .form-container::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: url('avaaset/AVA-Waves-Gold.svg') no-repeat center;
            background-size: 300px;
            opacity: 0.05;
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .step {
            display: none;
            opacity: 0;
            transform: translateX(50px);
        }

        .step.active {
            display: block;
            animation: slideIn 0.6s ease forwards;
        }

        @keyframes slideIn {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .step-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--pink-dark);
            margin-bottom: 30px;
            text-align: center;
            position: relative;
        }

        .step-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: linear-gradient(135deg, var(--pink-accent), var(--yellow-bright));
            border-radius: 2px;
        }

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .form-grid-full {
            grid-column: 1 / -1;
        }

        .form-group {
            position: relative;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: var(--pink-dark);
            margin-bottom: 10px;
            font-size: 16px;
        }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 18px 20px;
            border: 2px solid rgba(245, 166, 187, 0.3);
            border-radius: 15px;
            font-size: 16px;
            background: var(--cream);
            color: var(--pink-dark);
            transition: all 0.3s ease;
            font-family: "Hanken Grotesk", sans-serif;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            border-color: var(--pink-accent);
            outline: none;
            box-shadow: 0 0 0 4px rgba(238, 58, 106, 0.1);
            transform: translateY(-2px);
        }

        .form-textarea {
            resize: vertical;
            min-height: 120px;
        }

        /* Photo Upload */
        .photo-upload {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px;
            border: 2px dashed rgba(245, 166, 187, 0.5);
            border-radius: 15px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .photo-upload:hover {
            border-color: var(--pink-accent);
            background: rgba(245, 166, 187, 0.05);
        }

        .photo-preview {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--pink-accent);
        }

        .photo-upload-text {
            flex: 1;
        }

        .photo-upload-btn {
            background: linear-gradient(135deg, var(--pink-accent), var(--pink-dark));
            color: var(--cream);
            border: none;
            padding: 12px 20px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .photo-upload-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(238, 58, 106, 0.3);
        }

        /* Promo Section */
        .promo-section {
            background: linear-gradient(135deg, rgba(255, 230, 109, 0.2), rgba(245, 166, 187, 0.1));
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            border: 2px solid rgba(255, 230, 109, 0.3);
        }

        .promo-input-group {
            display: flex;
            gap: 15px;
            align-items: end;
        }

        .promo-btn {
            background: linear-gradient(135deg, var(--yellow-bright), #fbbf24);
            color: var(--pink-dark);
            border: none;
            padding: 18px 25px;
            border-radius: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .promo-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 230, 109, 0.4);
        }

        .promo-result {
            margin-top: 15px;
            font-weight: 600;
            font-size: 16px;
        }

        /* Price Display */
        .price-display {
            background: linear-gradient(135deg, rgba(255, 254, 224, 0.9), rgba(245, 166, 187, 0.1));
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            border: 2px solid rgba(238, 58, 106, 0.2);
        }

        .price-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--pink-dark);
            margin-bottom: 20px;
            text-align: center;
        }

        .price-grid {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 20px;
            align-items: center;
        }

        .price-details {
            color: var(--pink-dark);
        }

        .price-total {
            text-align: right;
        }

        .price-amount {
            font-size: 2.5rem;
            font-weight: 900;
            color: var(--pink-accent);
        }

        .price-label {
            color: var(--pink-dark);
            opacity: 0.7;
        }

        /* Schedule Selection */
        .schedule-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            max-height: 400px;
            overflow-y: auto;
            padding: 20px;
            border: 2px solid rgba(245, 166, 187, 0.3);
            border-radius: 15px;
            background: rgba(255, 254, 224, 0.5);
        }

        .schedule-card {
            background: var(--cream);
            padding: 20px;
            border-radius: 15px;
            border: 2px solid rgba(245, 166, 187, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .schedule-card:hover {
            border-color: var(--pink-accent);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(158, 2, 50, 0.1);
        }

        .schedule-card.selected {
            border-color: var(--pink-accent);
            background: linear-gradient(135deg, rgba(238, 58, 106, 0.1), rgba(255, 230, 109, 0.1));
        }

        .schedule-radio {
            position: absolute;
            opacity: 0;
        }

        /* Terms */
        .terms-container {
            display: flex;
            align-items: start;
            gap: 15px;
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(245, 166, 187, 0.1);
            border-radius: 15px;
        }

        .terms-checkbox {
            width: 20px;
            height: 20px;
            margin-top: 2px;
        }

        /* Buttons */
        .button-group {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-top: 40px;
        }

        .btn {
            padding: 18px 35px;
            border: none;
            border-radius: 15px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: "Hanken Grotesk", sans-serif;
        }

        .btn-secondary {
            background: rgba(156, 163, 175, 0.8);
            color: white;
        }

        .btn-secondary:hover {
            background: #6b7280;
            transform: translateY(-2px);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--pink-accent), var(--pink-dark));
            color: var(--cream);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(238, 58, 106, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            flex: 1;
            max-width: 300px;
        }

        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-container {
                padding: 15px 20px;
            }

            .nav-links {
                gap: 15px;
            }

            .nav-link {
                display: none;
            }

            .form-container {
                padding: 30px 20px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .progress-wrapper {
                padding: 15px 20px;
                flex-wrap: wrap;
            }

            .step-text {
                font-size: 14px;
                margin-right: 20px;
            }

            .step-indicator {
                width: 40px;
                height: 40px;
                font-size: 16px;
                margin-right: 10px;
            }

            .button-group {
                flex-direction: column;
            }

            .promo-input-group {
                flex-direction: column;
                align-items: stretch;
            }

            .price-grid {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background Elements -->
    <div class="bg-element bg-circle-1"></div>
    <div class="bg-element bg-circle-2"></div>
    <div class="bg-element bg-circle-3"></div>
    <div class="bg-element bg-circle-4"></div>

 
   

    <div class="main-container">
        <!-- Error Message -->
        <?php if (isset($_GET['error'])): ?>
        <div class="error-message" id="error-message">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Error:</strong> <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Progress Indicator -->
        <div class="progress-container" id="progress-container">
            <div class="progress-wrapper">
                <div class="step-indicator active" id="indicator-1">1</div>
                <div class="step-text">Data Pribadi</div>
                <div class="step-indicator" id="indicator-2">2</div>
                <div class="step-text">Data Orangtua</div>
                <div class="step-indicator" id="indicator-3">3</div>
                <div class="step-text">Informasi Musik</div>
                <div class="step-indicator" id="indicator-4">4</div>
                <div class="step-text">Konfirmasi</div>
            </div>
        </div>

        <div class="form-container" id="form-container">
            <form id="registrationForm" action="process_registration.php" method="POST" enctype="multipart/form-data">
                <!-- Step 1: Data Pribadi -->
                <div class="step active">
                    <h2 class="step-title">Data Pribadi</h2>
                    
                    <div class="form-group form-grid-full">
                        <label class="form-label">Foto Profil</label>
                        <div class="photo-upload" onclick="document.getElementById('foto_profil').click()">
                            <img id="preview" src="avaaset/logo-ava.png" alt="Preview" class="photo-preview">
                            <div class="photo-upload-text">
                                <div style="font-weight: 600; color: var(--pink-dark);">Upload Foto Profil</div>
                                <div style="font-size: 14px; color: var(--pink-dark); opacity: 0.7;">Pilih foto dengan format JPG, PNG atau GIF</div>
                            </div>
                            <button type="button" class="photo-upload-btn">
                                <i class="fas fa-upload"></i> Pilih Foto
                            </button>
                        </div>
                        <input type="file" name="foto_profil" id="foto_profil" style="display: none;" accept="image/*" onchange="previewImage(this)">
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Nama Lengkap *</label>
                            <input type="text" name="nama_lengkap" class="form-input" placeholder="Masukkan nama lengkap" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Nama Panggilan</label>
                            <input type="text" name="nama_panggilan" class="form-input" placeholder="Nama panggilan">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-input" placeholder="Kota kelahiran">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-input">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select">
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">No. Telepon *</label>
                            <input type="tel" name="telepon" class="form-input" placeholder="08xxxxxxxxxx" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-input" placeholder="email@example.com" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Password *</label>
                            <input type="password" name="password" class="form-input" placeholder="Minimal 6 karakter" required>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group form-grid-full">
                            <label class="form-label">Alamat Lengkap</label>
                            <textarea name="alamat_lengkap" class="form-textarea" placeholder="Masukkan alamat lengkap"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Pendidikan Terakhir</label>
                            <input type="text" name="pendidikan_terakhir" class="form-input" placeholder="Contoh: SMA/SMK/Sarjana">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Kelas/Semester</label>
                            <input type="text" name="kelas_semester" class="form-input" placeholder="Contoh: Kelas 12 / Semester 6">
                        </div>
                    </div>
                    
                    <div class="button-group">
                        <div></div>
                        <button type="button" onclick="nextStep()" class="btn btn-primary">Selanjutnya</button>
                    </div>
                </div>

                <!-- Step 2: Data Orangtua -->
                <div class="step">
                    <h2 class="step-title">Data Orangtua/Wali</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Nama Orangtua/Wali</label>
                            <input type="text" name="nama_orang_tua" class="form-input" placeholder="Nama lengkap orangtua/wali">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Pekerjaan Orangtua/Wali</label>
                            <input type="text" name="pekerjaan_orang_tua" class="form-input" placeholder="Pekerjaan orangtua/wali">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">No. Telepon Orangtua/Wali</label>
                            <input type="tel" name="telepon_orang_tua" class="form-input" placeholder="08xxxxxxxxxx">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Email Orangtua/Wali</label>
                            <input type="email" name="email_orang_tua" class="form-input" placeholder="email@example.com">
                        </div>
                    </div>
                    
                    <div class="button-group">
                        <button type="button" onclick="prevStep()" class="btn btn-secondary">Sebelumnya</button>
                        <button type="button" onclick="nextStep()" class="btn btn-primary">Selanjutnya</button>
                    </div>
                </div>

                <!-- Step 3: Informasi Musik -->
                <div class="step">
                    <h2 class="step-title">Informasi Musik & Kesehatan</h2>
                    <div class="form-grid">
                        <div class="form-group form-grid-full">
                            <label class="form-label">Hobi & Minat</label>
                            <textarea name="hobi_minat" class="form-textarea" placeholder="Ceritakan hobi dan minat Anda"></textarea>
                        </div>
                        
                        <div class="form-group form-grid-full">
                            <label class="form-label">Pengalaman Musik</label>
                            <textarea name="pengalaman_musik" class="form-textarea" placeholder="Ceritakan pengalaman musik Anda sebelumnya"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Genre Favorit</label>
                            <input type="text" name="genre_favorit" class="form-input" placeholder="Contoh: Pop, Jazz, Rock">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Pernah Ikut Lomba?</label>
                            <select name="pernah_lomba" onchange="toggleLombaDetail(this)" class="form-select">
                                <option value="">Pilih</option>
                                <option value="Ya">Ya</option>
                                <option value="Tidak">Tidak</option>
                            </select>
                        </div>
                        
                        <div class="form-group form-grid-full" id="detail_lomba_container" style="display: none;">
                            <label class="form-label">Detail Lomba</label>
                            <textarea name="detail_lomba" class="form-textarea" placeholder="Ceritakan pengalaman lomba Anda"></textarea>
                        </div>
                        
                        <div class="form-group form-grid-full">
                            <label class="form-label">Motivasi & Harapan</label>
                            <textarea name="motivasi_harapan" class="form-textarea" placeholder="Apa motivasi dan harapan Anda mengikuti kursus vokal?"></textarea>
                        </div>
                        
                        <div class="form-group form-grid-full">
                            <label class="form-label">Referensi Lagu yang Disukai</label>
                            <textarea name="referensi_lagu" class="form-textarea" placeholder="Sebutkan lagu-lagu yang Anda sukai"></textarea>
                        </div>
                        
                        <div class="form-group form-grid-full">
                            <label class="form-label">Riwayat Kesehatan</label>
                            <textarea name="riwayat_kesehatan" class="form-textarea" placeholder="Ceritakan jika ada masalah kesehatan yang perlu diketahui (opsional)"></textarea>
                        </div>
                    </div>
                    
                    <div class="button-group">
                        <button type="button" onclick="prevStep()" class="btn btn-secondary">Sebelumnya</button>
                        <button type="button" onclick="nextStep()" class="btn btn-primary">Selanjutnya</button>
                    </div>
                </div>

                <!-- Step 4: Konfirmasi & Promo -->
                <div class="step">
                    <h2 class="step-title">Konfirmasi & Pilih Paket</h2>
                    
                    <!-- Promo Code Section -->
                    <div class="promo-section">
                        <label class="form-label">
                            <i class="fas fa-tags"></i> Kode Promo (Opsional)
                        </label>
                        <div class="promo-input-group">
                            <div style="flex: 1;">
                                <input type="text" name="kode_promo" id="kode_promo" class="form-input" placeholder="Masukkan kode promo atau kosongkan untuk harga reguler">
                            </div>
                            <button type="button" onclick="checkPromo()" class="promo-btn">
                                <i class="fas fa-search"></i> Cek Harga
                            </button>
                        </div>
                        <div id="promo-result" class="promo-result"></div>
                        <p style="font-size: 14px; color: var(--pink-dark); opacity: 0.7; margin-top: 10px;">
                            Jika tidak mengisi kode promo, akan digunakan harga reguler
                        </p>
                    </div>

                    <!-- Price Display -->
                    <div id="price-display" class="price-display">
                        <h3 class="price-title">
                            <i class="fas fa-money-bill-wave"></i> Detail Harga
                        </h3>
                        <div id="price-details" class="price-grid">
                            <div class="price-details">
                                <p><strong>Paket:</strong> Harga Reguler</p>
                                <p><strong>Biaya Kursus:</strong> Rp 800.000</p>
                                <p><strong>Biaya Pendaftaran:</strong> Rp 200.000</p>
                            </div>
                            <div class="price-total">
                                <div class="price-amount">Rp 1.000.000</div>
                                <div class="price-label">Total Pembayaran</div>
                            </div>
                        </div>
                    </div>

                    <!-- Schedule Selection -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-calendar-alt"></i> Pilih Jadwal
                        </label>
                        <div id="schedule-selection" class="schedule-grid">
                            <!-- Schedule options will be loaded here -->
                        </div>
                        <input type="hidden" name="jadwal_id" id="selected_jadwal_id" required>
                        <p style="font-size: 14px; color: var(--pink-dark); opacity: 0.7; margin-top: 10px;">
                            Pilih jadwal yang tersedia untuk kelas Anda
                        </p>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="terms-container">
                        <input type="checkbox" name="agree_terms" id="agree_terms" class="terms-checkbox" required>
                        <div style="font-size: 14px; color: var(--pink-dark);">
                            Saya setuju dengan <a href="#" style="color: var(--pink-accent); text-decoration: none; font-weight: 600;">syarat dan ketentuan</a> yang berlaku di Anastasya Vocal Arts dan bersedia mengikuti peraturan yang telah ditetapkan.
                        </div>
                    </div>
                    
                    <div class="button-group">
                        <button type="button" onclick="prevStep()" class="btn btn-secondary">Sebelumnya</button>
                        <button type="button" onclick="submitForm()" class="btn btn-success">
                            <i class="fas fa-check-circle"></i> Daftar Sekarang
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // GSAP Animation Setup
        gsap.registerPlugin(ScrollTrigger);

        // Initial animations
        gsap.timeline()
            .to("#hero-section", {
                opacity: 1,
                y: 0,
                duration: 1,
                ease: "power2.out",
                delay: 0.3
            })
            .to("#progress-container", {
                opacity: 1,
                y: 0,
                duration: 0.8,
                ease: "power2.out"
            }, "-=0.5")
            .to("#form-container", {
                opacity: 1,
                scale: 1,
                duration: 1,
                ease: "back.out(1.7)"
            }, "-=0.3");

        // Error message animation
        if (document.getElementById('error-message')) {
            gsap.to("#error-message", {
                opacity: 1,
                y: 0,
                duration: 0.6,
                ease: "power2.out"
            });
        }

        let currentStep = 1;
        const totalSteps = 4;

        function nextStep() {
            if (validateCurrentStep()) {
                if (currentStep < totalSteps) {
                    // Hide current step
                    gsap.to(`.step:nth-child(${currentStep})`, {
                        opacity: 0,
                        x: -50,
                        duration: 0.3,
                        onComplete: () => {
                            document.querySelector(`.step:nth-child(${currentStep})`).classList.remove('active');
                            currentStep++;
                            document.querySelector(`.step:nth-child(${currentStep})`).classList.add('active');
                            updateStepIndicator();
                            
                            // Show new step
                            gsap.fromTo(`.step:nth-child(${currentStep})`, {
                                opacity: 0,
                                x: 50
                            }, {
                                opacity: 1,
                                x: 0,
                                duration: 0.6,
                                ease: "power2.out"
                            });

                            // Load schedules when entering step 4
                            if (currentStep === 4) {
                                loadSchedules();
                            }
                        }
                    });
                }
            }
        }

        function prevStep() {
            if (currentStep > 1) {
                // Hide current step
                gsap.to(`.step:nth-child(${currentStep})`, {
                    opacity: 0,
                    x: 50,
                    duration: 0.3,
                    onComplete: () => {
                        document.querySelector(`.step:nth-child(${currentStep})`).classList.remove('active');
                        currentStep--;
                        document.querySelector(`.step:nth-child(${currentStep})`).classList.add('active');
                        updateStepIndicator();
                        
                        // Show previous step
                        gsap.fromTo(`.step:nth-child(${currentStep})`, {
                            opacity: 0,
                            x: -50
                        }, {
                            opacity: 1,
                            x: 0,
                            duration: 0.6,
                            ease: "power2.out"
                        });
                    }
                });
            }
        }

        function updateStepIndicator() {
            // Update step indicators with animation
            for (let i = 1; i <= totalSteps; i++) {
                const indicator = document.getElementById(`indicator-${i}`);
                
                if (i < currentStep) {
                    indicator.classList.remove('active');
                    indicator.classList.add('completed');
                    gsap.to(indicator, {
                        scale: 1,
                        duration: 0.3,
                        ease: "power2.out"
                    });
                } else if (i === currentStep) {
                    indicator.classList.remove('completed');
                    indicator.classList.add('active');
                    gsap.to(indicator, {
                        scale: 1.1,
                        duration: 0.3,
                        ease: "back.out(1.7)"
                    });
                } else {
                    indicator.classList.remove('active', 'completed');
                    gsap.to(indicator, {
                        scale: 1,
                        duration: 0.3,
                        ease: "power2.out"
                    });
                }
            }
        }

        function validateCurrentStep() {
            const currentStepElement = document.querySelector(`.step:nth-child(${currentStep})`);
            const requiredFields = currentStepElement.querySelectorAll('input[required], select[required], textarea[required]');
            
            for (let field of requiredFields) {
                if (!field.value.trim()) {
                    field.focus();
                    
                    // Shake animation for invalid field
                    gsap.fromTo(field, {
                        x: 0
                    }, {
                        x: [5, -5, 5, -5, 0],
                        duration: 0.5,
                        ease: "power2.out"
                    });
                    
                    // Show error message
                    showErrorMessage('Mohon lengkapi semua field yang wajib diisi.');
                    return false;
                }
            }
            
            // Additional validation for step 4 (final step)
            if (currentStep === 4) {
                const selectedSchedule = document.getElementById('selected_jadwal_id').value;
                const agreeTerms = document.querySelector('input[name="agree_terms"]:checked');
                
                if (!selectedSchedule) {
                    showErrorMessage('Mohon pilih jadwal yang tersedia.');
                    return false;
                }
                
                if (!agreeTerms) {
                    showErrorMessage('Mohon centang persetujuan syarat dan ketentuan.');
                    return false;
                }
            }
            
            return true;
        }

        function showErrorMessage(message) {
            // Create temporary error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.innerHTML = `<div style="display: flex; align-items: center; gap: 10px;"><i class="fas fa-exclamation-triangle"></i><strong>Error:</strong> ${message}</div>`;
            errorDiv.style.position = 'fixed';
            errorDiv.style.top = '20px';
            errorDiv.style.left = '50%';
            errorDiv.style.transform = 'translateX(-50%)';
            errorDiv.style.zIndex = '1000';
            errorDiv.style.maxWidth = '500px';
            
            document.body.appendChild(errorDiv);
            
            // Animate in
            gsap.fromTo(errorDiv, {
                opacity: 0,
                y: -20
            }, {
                opacity: 1,
                y: 0,
                duration: 0.5,
                ease: "power2.out"
            });
            
            // Remove after 3 seconds
            setTimeout(() => {
                gsap.to(errorDiv, {
                    opacity: 0,
                    y: -20,
                    duration: 0.3,
                    onComplete: () => errorDiv.remove()
                });
            }, 3000);
        }

        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('preview');
                    preview.src = e.target.result;
                    
                    // Animate image change
                    gsap.fromTo(preview, {
                        scale: 0.8,
                        opacity: 0.5
                    }, {
                        scale: 1,
                        opacity: 1,
                        duration: 0.5,
                        ease: "back.out(1.7)"
                    });
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function toggleLombaDetail(select) {
            const container = document.getElementById('detail_lomba_container');
            if (select.value === 'Ya') {
                container.style.display = 'block';
                gsap.fromTo(container, {
                    opacity: 0,
                    height: 0
                }, {
                    opacity: 1,
                    height: 'auto',
                    duration: 0.5,
                    ease: "power2.out"
                });
            } else {
                gsap.to(container, {
                    opacity: 0,
                    height: 0,
                    duration: 0.3,
                    onComplete: () => container.style.display = 'none'
                });
            }
        }

        function loadSchedules() {
            const scheduleContainer = document.getElementById('schedule-selection');
            scheduleContainer.innerHTML = '<div style="text-align: center; padding: 40px; color: var(--pink-dark);"><i class="fas fa-spinner fa-spin"></i> Memuat jadwal...</div>';
            
            fetch('get_available_schedules.php')
                .then(response => response.json())
                .then(schedules => {
                    if (schedules.length === 0) {
                        scheduleContainer.innerHTML = '<div style="text-align: center; padding: 40px; color: var(--pink-dark);">Tidak ada jadwal yang tersedia saat ini.</div>';
                        return;
                    }
                    
                    scheduleContainer.innerHTML = '';
                    schedules.forEach((schedule, index) => {
                        const scheduleCard = document.createElement('div');
                        scheduleCard.className = 'schedule-card';
                        scheduleCard.innerHTML = `
                            <input type="radio" name="schedule_option" value="${schedule.id}" class="schedule-radio" id="schedule-${schedule.id}">
                            <div style="font-weight: 600; color: var(--pink-dark); margin-bottom: 5px;">
                                <i class="fas fa-calendar"></i> ${schedule.hari}
                            </div>
                            <div style="color: var(--pink-dark); opacity: 0.8;">
                                <i class="fas fa-clock"></i> ${schedule.jam_mulai.slice(0,5)} - ${schedule.jam_selesai.slice(0,5)}
                            </div>
                        `;
                        
                        scheduleCard.addEventListener('click', function() {
                            // Remove selection from other cards
                            document.querySelectorAll('.schedule-card').forEach(card => {
                                card.classList.remove('selected');
                            });
                            
                            // Select this card
                            this.classList.add('selected');
                            this.querySelector('input[type="radio"]').checked = true;
                            document.getElementById('selected_jadwal_id').value = schedule.id;
                            
                            // Animation
                            gsap.to(this, {
                                scale: 1.02,
                                duration: 0.2,
                                yoyo: true,
                                repeat: 1,
                                ease: "power2.out"
                            });
                        });
                        
                        scheduleContainer.appendChild(scheduleCard);
                        
                        // Animate schedule cards
                        gsap.fromTo(scheduleCard, {
                            opacity: 0,
                            y: 20
                        }, {
                            opacity: 1,
                            y: 0,
                            duration: 0.5,
                            delay: index * 0.1,
                            ease: "power2.out"
                        });
                    });
                })
                .catch(error => {
                    console.error('Error loading schedules:', error);
                    scheduleContainer.innerHTML = '<div style="text-align: center; padding: 40px; color: #dc2626;"><i class="fas fa-exclamation-triangle"></i> Error loading schedules.</div>';
                });
        }

        function checkPromo() {
            const kodePromo = document.getElementById('kode_promo').value.trim();
            const resultDiv = document.getElementById('promo-result');
            const priceDisplay = document.getElementById('price-display');
            const priceDetails = document.getElementById('price-details');
            
            // Show loading
            resultDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengecek kode promo...';
            
            if (!kodePromo) {
                // Show regular price
                resultDiv.innerHTML = '<span style="color: var(--blue-soft);"><i class="fas fa-info-circle"></i> Menggunakan harga reguler</span>';
                priceDetails.innerHTML = `
                    <div class="price-details">
                        <p><strong>Paket:</strong> Harga Reguler</p>
                        <p><strong>Biaya Kursus:</strong> Rp 800.000</p>
                        <p><strong>Biaya Pendaftaran:</strong> Rp 200.000</p>
                    </div>
                    <div class="price-total">
                        <div class="price-amount">Rp 1.000.000</div>
                        <div class="price-label">Total Pembayaran</div>
                    </div>
                `;
                
                // Animate price update
                gsap.fromTo(priceDisplay, {
                    scale: 0.95
                }, {
                    scale: 1,
                    duration: 0.3,
                    ease: "back.out(1.7)"
                });
                return;
            }
            
            fetch('check_promo.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'kode_promo=' + encodeURIComponent(kodePromo)
            })
            .then(response => response.json())
            .then(data => {
                if (data.valid) {
                    resultDiv.innerHTML = `<span style="color: #10b981;"><i class="fas fa-check-circle"></i> Kode promo valid: ${data.nama_promo}</span>`;
                    
                    // Show price details
                    priceDetails.innerHTML = `
                        <div class="price-details">
                            <p><strong>Paket:</strong> ${data.nama_promo}</p>
                            <p><strong>Biaya Kursus:</strong> Rp ${new Intl.NumberFormat('id-ID').format(data.harga_kursus)}</p>
                            <p><strong>Biaya Pendaftaran:</strong> Rp ${new Intl.NumberFormat('id-ID').format(data.biaya_pendaftaran)}</p>
                        </div>
                        <div class="price-total">
                            <div class="price-amount">Rp ${data.total_formatted}</div>
                            <div class="price-label">Total Pembayaran</div>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `<span style="color: #dc2626;"><i class="fas fa-times-circle"></i> ${data.message}</span>`;
                    // Show regular price when promo is invalid
                    priceDetails.innerHTML = `
                        <div class="price-details">
                            <p><strong>Paket:</strong> Harga Reguler</p>
                            <p><strong>Biaya Kursus:</strong> Rp 800.000</p>
                            <p><strong>Biaya Pendaftaran:</strong> Rp 200.000</p>
                        </div>
                        <div class="price-total">
                            <div class="price-amount">Rp 1.000.000</div>
                            <div class="price-label">Total Pembayaran</div>
                        </div>
                    `;
                }
                
                // Animate price update
                gsap.fromTo(priceDisplay, {
                    scale: 0.95
                }, {
                    scale: 1,
                    duration: 0.3,
                    ease: "back.out(1.7)"
                });
            })
            .catch(error => {
                resultDiv.innerHTML = '<span style="color: #dc2626;"><i class="fas fa-exclamation-triangle"></i> Terjadi kesalahan saat mengecek promo.</span>';
            });
        }

        function submitForm() {
            if (validateCurrentStep()) {
                // Check for pending payments first
                const email = document.querySelector('input[name="email"]').value;
                
                fetch('check_pending_payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'email=' + encodeURIComponent(email)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.has_pending) {
                        showErrorMessage('Anda masih memiliki pembayaran pending dengan Order ID: ' + data.order_id + '. Anda akan diarahkan ke halaman pembayaran.');
                        setTimeout(() => {
                            window.location.href = 'payment_page.php?order_id=' + data.order_id;
                        }, 2000);
                        return;
                    }
                    
                    // No pending payment, proceed with registration
                    const submitBtn = document.querySelector('.btn-success');
                    const originalText = submitBtn.innerHTML;
                    
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
                    submitBtn.disabled = true;
                    
                    // Animate submit button
                    gsap.to(submitBtn, {
                        scale: 0.95,
                        duration: 0.1,
                        yoyo: true,
                        repeat: 1
                    });
                    
                    document.getElementById('registrationForm').submit();
                })
                .catch(error => {
                    console.error('Error checking pending payment:', error);
                    // Proceed anyway if check fails
                    const submitBtn = document.querySelector('.btn-success');
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
                    submitBtn.disabled = true;
                    
                    document.getElementById('registrationForm').submit();
                });
            }
        }

        // Initialize animations when page loads
        window.addEventListener('load', function() {
            // Animate background elements
            gsap.to('.bg-element', {
                opacity: 1,
                duration: 2,
                stagger: 0.3,
                ease: "power2.out"
            });
        });
    </script>
</body>
</html>
