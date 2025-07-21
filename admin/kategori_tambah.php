<?php
include '../templates/header_admin.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_kategori = trim($_POST['nama_kategori'] ?? '');
    
    if (empty($nama_kategori)) {
        $error = "Nama kategori harus diisi!";
    } elseif (strlen($nama_kategori) < 3) {
        $error = "Nama kategori minimal 3 karakter!";
    } elseif (strlen($nama_kategori) > 150) {
        $error = "Nama kategori maksimal 150 karakter!";
    } else {
        try {
            // Cek apakah kategori sudah ada
            $check_stmt = $conn->prepare("SELECT id FROM kategori WHERE nama_kategori = ?");
            $check_stmt->bind_param("s", $nama_kategori);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $error = "Nama kategori sudah ada!";
            } else {
                $stmt = $conn->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
                $stmt->bind_param("s", $nama_kategori);
                
                if ($stmt->execute()) {
                    $success = "Kategori berhasil ditambahkan!";
                    $_POST = array(); // Reset form
                } else {
                    $error = "Gagal menambahkan kategori!";
                }
                $stmt->close();
            }
            $check_stmt->close();
        } catch (Exception $e) {
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}
?>

<h2>Tambah Kategori Baru</h2>

<?php if ($error): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<form action="kategori_tambah.php" method="post" class="form-container">
    <div class="form-group">
        <label for="nama_kategori">Nama Kategori</label>
        <input type="text" id="nama_kategori" name="nama_kategori" required 
               maxlength="150" minlength="3"
               value="<?php echo isset($_POST['nama_kategori']) ? htmlspecialchars($_POST['nama_kategori']) : ''; ?>"
               placeholder="Contoh: Tanaman Hias Daun">
        <small>Minimal 3 karakter, maksimal 150 karakter</small>
    </div>
    
    <div class="form-group">
        <button type="submit">Simpan Kategori</button>
        <a href="kategori.php" class="btn-cancel">Batal</a>
    </div>
</form>

<?php include '../templates/footer_admin.php'; ?>
