<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

// Include required files
require_once 'config/midtrans_config.php';
require_once 'midtrans_helper.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$upload_dir = 'uploads/profil/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$error = '';
$success = '';

try {
    // Get form data
    $nama_lengkap = $_POST['nama_lengkap'] ?? '';
    $nama_panggilan = $_POST['nama_panggilan'] ?? '';
    $tempat_lahir = $_POST['tempat_lahir'] ?? '';
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $alamat_lengkap = $_POST['alamat_lengkap'] ?? '';
    $telepon = $_POST['telepon'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $nama_orang_tua = $_POST['nama_orang_tua'] ?? '';
    $pekerjaan_orang_tua = $_POST['pekerjaan_orang_tua'] ?? '';
    $telepon_orang_tua = $_POST['telepon_orang_tua'] ?? '';
    $email_orang_tua = $_POST['email_orang_tua'] ?? '';
    $pendidikan_terakhir = $_POST['pendidikan_terakhir'] ?? '';
    $kelas_semester = $_POST['kelas_semester'] ?? '';
    $hobi_minat = $_POST['hobi_minat'] ?? '';
    $pengalaman_musik = $_POST['pengalaman_musik'] ?? '';
    $genre_favorit = $_POST['genre_favorit'] ?? '';
    $pernah_lomba = $_POST['pernah_lomba'] ?? '';
    $detail_lomba = $_POST['detail_lomba'] ?? '';
    $motivasi_harapan = $_POST['motivasi_harapan'] ?? '';
    $referensi_lagu = $_POST['referensi_lagu'] ?? '';
    $riwayat_kesehatan = $_POST['riwayat_kesehatan'] ?? '';
    $kode_promo = $_POST['kode_promo'] ?? '';

    // Validation
    if (empty($nama_lengkap) || empty($telepon) || empty($email) || empty($password)) {
        throw new Exception('Silahkan lengkapi semua field yang wajib');
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM siswa WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        throw new Exception('Email sudah terdaftar');
    }

    // Get promo details if provided
    $promo_data = null;
    $harga_kursus = get_setting($pdo, 'harga_kursus_standar'); // Default course price
    $biaya_pendaftaran = get_setting($pdo, 'biaya_pendaftaran_standar'); // Default registration fee

    if (!empty($kode_promo)) {
        $stmt = $pdo->prepare("SELECT * FROM promo_codes WHERE kode_promo = ? AND status = 'aktif'");
        $stmt->execute([$kode_promo]);
        $promo_data = $stmt->fetch();

        if ($promo_data) {
            $harga_kursus = $promo_data['harga_kursus'];
            $biaya_pendaftaran = $promo_data['biaya_pendaftaran'];
        } else {
            // Log error if promo not found for debugging
            error_log("Promo code not found or inactive: " . $kode_promo);
        }
    }

    // Calculate subtotal (course + registration)
    $subtotal = $harga_kursus + $biaya_pendaftaran;

    // No tax calculation
    $pajak_total = 0;

    // Calculate admin fee
    $admin_fee = 0;

    // Check if calculate_admin_fee function exists
    if (function_exists('calculate_admin_fee')) {
        $admin_fee = calculate_admin_fee($subtotal, $pdo);
    } else {
        // Fallback admin fee calculation
        try {
            $stmt = $pdo->prepare("SELECT admin_fee_type, admin_fee_amount, admin_fee_percentage FROM midtrans_settings WHERE id = 1");
            $stmt->execute();
            $fee_setting = $stmt->fetch();

            if ($fee_setting) {
                if ($fee_setting['admin_fee_type'] === 'percentage') {
                    $admin_fee = $subtotal * ($fee_setting['admin_fee_percentage'] / 100);
                } else {
                    $admin_fee = $fee_setting['admin_fee_amount'];
                }
            } else {
                $admin_fee = 5000; // Default admin fee
            }
        } catch (Exception $e) {
            error_log("Error calculating admin fee: " . $e->getMessage());
            $admin_fee = 5000; // Default admin fee
        }
    }

    // Calculate gross amount (subtotal + admin fee, no tax)
    $gross_amount = $subtotal + $admin_fee;


    // Handle file upload
    $foto_path = null;
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/profil/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_extension = pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION);
        $foto_filename = uniqid() . '.' . $file_extension;
        $foto_path = $upload_dir . $foto_filename;

        if (!move_uploaded_file($_FILES['foto_profil']['tmp_name'], $foto_path)) {
            throw new Exception('Gagal mengupload foto profil');
        }
    }

    $pdo->beginTransaction();

    // Insert student data
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $qr_code_identifier = 'AVA-' . uniqid();

    // Get jadwal_id from form
    $jadwal_id = $_POST['jadwal_id'] ?? null;

    $stmt = $pdo->prepare("
        INSERT INTO siswa (
            nama_lengkap, nama_panggilan, tempat_lahir, tanggal_lahir, jenis_kelamin,
            alamat_lengkap, telepon, email, password, nama_orang_tua, pekerjaan_orang_tua,
            telepon_orang_tua, email_orang_tua, pendidikan_terakhir, kelas_semester,
            hobi_minat, pengalaman_musik, genre_favorit, pernah_lomba, detail_lomba,
            motivasi_harapan, referensi_lagu, riwayat_kesehatan, foto_profil, kode_promo,
            jadwal_id, biaya_per_bulan, durasi_bulan, status_pembayaran, qr_code_identifier
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 'pending', ?
        )
    ");

    $stmt->execute([
        $nama_lengkap, $nama_panggilan, $tempat_lahir, $tanggal_lahir, $jenis_kelamin,
        $alamat_lengkap, $telepon, $email, $hashed_password, $nama_orang_tua, $pekerjaan_orang_tua,
        $telepon_orang_tua, $email_orang_tua, $pendidikan_terakhir, $kelas_semester,
        $hobi_minat, $pengalaman_musik, $genre_favorit, $pernah_lomba, $detail_lomba,
        $motivasi_harapan, $referensi_lagu, $riwayat_kesehatan, $foto_path, $kode_promo,
        $jadwal_id, $harga_kursus, $qr_code_identifier
    ]);

    $student_id = $pdo->lastInsertId();

    // Create payment record
    $order_id = 'REG-' . $student_id . '-' . time();

    $stmt = $pdo->prepare("
        INSERT INTO payments (student_id, order_id, gross_amount, harga_kursus, biaya_pendaftaran,
                            midtrans_fee, kode_promo, snap_token, pajak_total, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->execute([$student_id, $order_id, $gross_amount, $harga_kursus, $biaya_pendaftaran, $admin_fee, $kode_promo, null, $pajak_total]);


    // Increment promo usage if promo code is used
    if (!empty($kode_promo)) {
        $stmt = $pdo->prepare("UPDATE promo_codes SET current_usage = current_usage + 1 WHERE kode_promo = ?");
        $stmt->execute([$kode_promo]);
    }

    // Generate Midtrans payment using official library
    $snapToken = null;

    try {
        // Get Midtrans server key from database
        $stmt = $pdo->prepare("SELECT server_key FROM midtrans_settings WHERE id = 1");
        $stmt->execute();
        $midtrans_setting = $stmt->fetch();

        if ($midtrans_setting && !empty($midtrans_setting['server_key'])) {
            $midtrans_server_key = $midtrans_setting['server_key'];

            // Get enabled payment methods from settings
            $stmt = $pdo->prepare("SELECT enabled_payments FROM midtrans_settings WHERE id = 1");
            $stmt->execute();
            $enabled_payments_json = $stmt->fetchColumn();
            $enabled_payments = $enabled_payments_json ? json_decode($enabled_payments_json, true) : ['qris', 'bank_transfer'];

            // Get production mode from database
            $stmt = $pdo->prepare("SELECT is_production FROM midtrans_settings WHERE id = 1");
            $stmt->execute();
            $is_production = (bool)$stmt->fetchColumn();

            // Create Snap transaction using native PHP implementation
            $params = [
                'transaction_details' => [
                    'order_id' => $order_id,
                    'gross_amount' => (int)$gross_amount,
                ],
                'customer_details' => [
                    'first_name' => $nama_lengkap,
                    'email' => $email,
                    'phone' => $telepon,
                ],
                'item_details' => [
                    [
                        'id' => 'kursus',
                        'price' => (int)$harga_kursus,
                        'quantity' => 1,
                        'name' => $promo_data ? 'Kursus (Promo: ' . $kode_promo . ')' : 'Kursus (Reguler)'
                    ],
                    [
                        'id' => 'biaya_pendaftaran',
                        'price' => (int)$biaya_pendaftaran,
                        'quantity' => 1,
                        'name' => $promo_data ? 'Biaya Pendaftaran (Promo: ' . $kode_promo . ')' : 'Biaya Pendaftaran (Reguler)'
                    ],
                    
                ]
            ];

            if ($admin_fee > 0) {
                $params['item_details'][] = [
                    'id' => 'admin_fee',
                    'price' => (int)$admin_fee,
                    'quantity' => 1,
                    'name' => 'Biaya Admin'
                ];
            }

            $snapToken = createSnapToken($params, $midtrans_server_key, $enabled_payments, $is_production);

            // Update payment with snap token
            if ($snapToken) {
                $stmt = $pdo->prepare("UPDATE payments SET snap_token = ? WHERE order_id = ?");
                $stmt->execute([$snapToken, $order_id]);
            }

        }
    } catch (Exception $e) {
        // Log error but don't stop the process
        error_log('Midtrans error: ' . $e->getMessage());
        $snapToken = null;
    }

    $pdo->commit();

    // Send invoice email
    try {
        require_once 'email_helper.php';
        sendInvoiceEmail($pdo, $student_id, $order_id);
    } catch (Exception $e) {
        error_log('Email sending error: ' . $e->getMessage());
        // Don't stop the process if email fails
    }

    // Store registration data in session for payment page
    $_SESSION['registration_success'] = true;
    $_SESSION['student_id'] = $student_id;
    $_SESSION['order_id'] = $order_id;
    $_SESSION['student_name'] = $nama_lengkap;
    $_SESSION['gross_amount'] = $gross_amount;
    $_SESSION['promo_name'] = $promo_data ? $promo_data['nama_promo'] : 'Harga Reguler';
    $_SESSION['harga_kursus'] = $harga_kursus;
    $_SESSION['biaya_pendaftaran'] = $biaya_pendaftaran;
    $_SESSION['pajak_total'] = $pajak_total;
    $_SESSION['admin_fee'] = $admin_fee;
    $_SESSION['subtotal'] = $subtotal;


    header('Location: payment_page.php');
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Log the error for debugging
    error_log("Registration Error: " . $e->getMessage());
    error_log("File: " . $e->getFile() . " Line: " . $e->getLine());

    $error = $e->getMessage();
    $_SESSION['registration_error'] = $error;
    // Redirect back to register.php, passing the error message
    header('Location: index.php?error=' . urlencode($error));
    exit;
}
?>