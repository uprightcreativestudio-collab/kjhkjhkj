
<?php
$page_title = 'Form Jadwal';
include 'partials/header.php';

$jadwal = null;
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM jadwal WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $jadwal = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hari = $_POST['hari'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    
    // Check for time conflicts
    $conflict_check = "SELECT id FROM jadwal WHERE hari = ? AND ((jam_mulai <= ? AND jam_selesai > ?) OR (jam_mulai < ? AND jam_selesai >= ?) OR (jam_mulai >= ? AND jam_selesai <= ?))";
    $params = [$hari, $jam_mulai, $jam_mulai, $jam_selesai, $jam_selesai, $jam_mulai, $jam_selesai];
    
    if ($jadwal) {
        $conflict_check .= " AND id != ?";
        $params[] = $jadwal['id'];
    }
    
    $stmt_conflict = $pdo->prepare($conflict_check);
    $stmt_conflict->execute($params);
    
    if ($stmt_conflict->fetch()) {
        $_SESSION['flash_message'] = "Jadwal bentrok dengan jadwal yang sudah ada.";
        $_SESSION['flash_type'] = 'error';
    } else {
        try {
            if ($jadwal) {
                // Update
                $stmt = $pdo->prepare("UPDATE jadwal SET hari = ?, jam_mulai = ?, jam_selesai = ? WHERE id = ?");
                $stmt->execute([$hari, $jam_mulai, $jam_selesai, $jadwal['id']]);
                $_SESSION['flash_message'] = "Jadwal berhasil diperbarui.";
            } else {
                // Insert
                $stmt = $pdo->prepare("INSERT INTO jadwal (hari, jam_mulai, jam_selesai) VALUES (?, ?, ?)");
                $stmt->execute([$hari, $jam_mulai, $jam_selesai]);
                $_SESSION['flash_message'] = "Jadwal berhasil ditambahkan.";
            }
            header("Location: jadwal.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['flash_message'] = "Error: " . $e->getMessage();
            $_SESSION['flash_type'] = 'error';
        }
    }
}

$days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
?>

<header class="bg-white shadow-sm p-4">
    <h1 class="text-2xl font-semibold text-gray-800">
        <?php echo $jadwal ? 'Edit Jadwal' : 'Tambah Jadwal'; ?>
    </h1>
</header>

<main class="flex-1 p-6">
    <div class="bg-white p-6 rounded-lg shadow-md max-w-md">
        <form method="POST">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Hari</label>
                <select name="hari" class="w-full border border-gray-300 rounded-md p-2" required>
                    <option value="">Pilih Hari</option>
                    <?php foreach ($days as $day): ?>
                        <option value="<?php echo $day; ?>" <?php echo ($jadwal && $jadwal['hari'] == $day) ? 'selected' : ''; ?>>
                            <?php echo $day; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Jam Mulai</label>
                <input type="time" name="jam_mulai" value="<?php echo $jadwal ? $jadwal['jam_mulai'] : ''; ?>" 
                       class="w-full border border-gray-300 rounded-md p-2" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Jam Selesai</label>
                <input type="time" name="jam_selesai" value="<?php echo $jadwal ? $jadwal['jam_selesai'] : ''; ?>" 
                       class="w-full border border-gray-300 rounded-md p-2" required>
            </div>
            
            <div class="flex justify-between">
                <a href="jadwal.php" class="bg-gray-500 text-white py-2 px-4 rounded-md hover:bg-gray-600">
                    Kembali
                </a>
                <button type="submit" class="bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700">
                    <?php echo $jadwal ? 'Update' : 'Simpan'; ?>
                </button>
            </div>
        </form>
    </div>
</main>

<?php include 'partials/footer.php'; ?>
