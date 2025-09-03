<?php
$page_title = 'Form E-Book';
include 'partials/header.php';

// --- CONFIGURATION ---
$upload_dir_files = '../uploads/ebooks/';
$upload_dir_covers = '../uploads/ebook_covers/';
if (!is_dir($upload_dir_files)) mkdir($upload_dir_files, 0777, true);
if (!is_dir($upload_dir_covers)) mkdir($upload_dir_covers, 0777, true);

$id = $_GET['id'] ?? null;
$is_edit = $id !== null;
$ebook = null;
$error = '';

if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM ebooks WHERE id = ?");
    $stmt->execute([$id]);
    $ebook = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $current_file = $_POST['current_file_path'] ?? null;
    $current_cover = $_POST['current_gambar_cover'] ?? null;

    $file_path = $current_file;
    if (isset($_FILES['file_path']) && $_FILES['file_path']['error'] == UPLOAD_ERR_OK) {
        if ($_FILES['file_path']['type'] !== 'application/pdf') {
            $error = 'Hanya file PDF yang diizinkan untuk e-book.';
        } else {
            if ($is_edit && $current_file) unlink($current_file);
            $file_name = uniqid() . '-' . basename($_FILES['file_path']['name']);
            $file_path = $upload_dir_files . $file_name;
            if (!move_uploaded_file($_FILES['file_path']['tmp_name'], $file_path)) {
                $error = 'Gagal mengupload file e-book.';
                $file_path = $current_file;
            }
        }
    }

    $cover_path = $current_cover;
    if (isset($_FILES['gambar_cover']) && $_FILES['gambar_cover']['error'] == UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['gambar_cover']['type'], $allowed_types)) {
            $error = 'Tipe file cover tidak valid. Gunakan JPG, PNG, atau GIF.';
        } else {
            if ($is_edit && $current_cover) unlink($current_cover);
            $cover_name = uniqid() . '-' . basename($_FILES['gambar_cover']['name']);
            $cover_path = $upload_dir_covers . $cover_name;
            if (!move_uploaded_file($_FILES['gambar_cover']['tmp_name'], $cover_path)) {
                $error = 'Gagal mengupload gambar cover.';
                $cover_path = $current_cover;
            }
        }
    }

    if (empty($error)) {
        if ($is_edit) {
            $stmt = $pdo->prepare("UPDATE ebooks SET judul=?, deskripsi=?, file_path=?, gambar_cover=? WHERE id=?");
            $stmt->execute([$judul, $deskripsi, $file_path, $cover_path, $id]);
            $_SESSION['flash_message'] = "E-Book berhasil diperbarui.";
        } else {
            if (empty($file_path)) {
                 $error = 'File e-book wajib diupload.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO ebooks (judul, deskripsi, file_path, gambar_cover) VALUES (?, ?, ?, ?)");
                $stmt->execute([$judul, $deskripsi, $file_path, $cover_path]);
                $_SESSION['flash_message'] = "E-Book baru berhasil ditambahkan.";
            }
        }
        if (empty($error)) {
            header("Location: ebook.php");
            exit();
        }
    }
}
?>
<header class="bg-white shadow-sm p-4">
    <h1 class="text-2xl font-semibold text-gray-800"><?php echo $is_edit ? 'Edit E-Book' : 'Tambah E-Book Baru'; ?></h1>
</header>
<main class="flex-1 p-6">
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="current_file_path" value="<?php echo htmlspecialchars($ebook['file_path'] ?? ''); ?>">
            <input type="hidden" name="current_gambar_cover" value="<?php echo htmlspecialchars($ebook['gambar_cover'] ?? ''); ?>">
            
            <div class="mb-4">
                <label for="judul" class="block text-sm font-medium text-gray-700">Judul E-Book</label>
                <input type="text" name="judul" id="judul" value="<?php echo htmlspecialchars($ebook['judul'] ?? ''); ?>" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
            </div>
            <div class="mb-4">
                <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi Singkat</label>
                <textarea name="deskripsi" id="deskripsi" rows="4" class="mt-1 block w-full p-2 border border-gray-300 rounded-md"><?php echo htmlspecialchars($ebook['deskripsi'] ?? ''); ?></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">File E-Book (PDF)</label>
                    <input type="file" name="file_path" id="file_path" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" <?php echo !$is_edit ? 'required' : ''; ?>>
                    <?php if ($is_edit && !empty($ebook['file_path'])): ?>
                        <p class="text-xs text-gray-500 mt-1">File saat ini: <?php echo basename($ebook['file_path']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Gambar Cover</label>
                    <input type="file" name="gambar_cover" id="gambar_cover" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <?php if ($is_edit && !empty($ebook['gambar_cover'])): ?>
                        <p class="text-xs text-gray-500 mt-1">Ganti cover saat ini:</p>
                        <img src="<?php echo htmlspecialchars($ebook['gambar_cover']); ?>" class="h-16 mt-1 rounded">
                    <?php endif; ?>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <a href="ebook.php" class="bg-gray-200 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-300">Batal</a>
                <button type="submit" class="bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700">
                    <?php echo $is_edit ? 'Update E-Book' : 'Simpan E-Book'; ?>
                </button>
            </div>
        </form>
    </div>
</main>
<?php include 'partials/footer.php'; ?>
