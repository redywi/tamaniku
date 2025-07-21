<?php
include '../templates/header_admin.php';

$error = '';
$success = '';
$kategori = null;

// Ambil ID kategori dari URL
$id = $_GET['id'] ?? '';
if (empty($id) || !is_numeric($id)) {
    header("Location: kategori.php");
    exit();
}

// Ambil data kategori
try {
    $stmt = $conn->prepare("SELECT * FROM kategori WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        header("Location: kategori.php");
        exit();
    }
    
    $kategori = $result->fetch_assoc();
    $stmt->close();
} catch (Exception $e) {
    $error = "Gagal mengambil data kategori: " . $e->getMessage();
}

// Proses form edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $kategori) {
    $nama_kategori = trim($_POST['nama_kategori'] ?? '');
    
    if (empty($nama_kategori)) {
        $error = "Nama kategori harus diisi!";
    } elseif (strlen($nama_kategori) < 3) {
        $error = "Nama kategori minimal 3 karakter!";
    } elseif (strlen($nama_kategori) > 150) {
        $error = "Nama kategori maksimal 150 karakter!";
    } else {
        try {
            // Cek apakah kategori dengan nama yang sama sudah ada (selain kategori ini)
            $check_stmt = $conn->prepare("SELECT id FROM kategori WHERE nama_kategori = ? AND id != ?");
            $check_stmt->bind_param("si", $nama_kategori, $id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $error = "Nama kategori sudah ada!";
            } else {
                $stmt = $conn->prepare("UPDATE kategori SET nama_kategori = ? WHERE id = ?");
                $stmt->bind_param("si", $nama_kategori, $id);
                
                if ($stmt->execute()) {
                    $success = "Kategori berhasil diperbarui!";
                    // Refresh data kategori
                    $kategori['nama_kategori'] = $nama_kategori;
                } else {
                    $error = "Gagal memperbarui kategori!";
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

<h2>Edit Kategori</h2>

<?php if ($error): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if ($kategori): ?>
<form action="kategori_edit.php?id=<?php echo $id; ?>" method="post" class="form-container">
    <div class="form-group">
        <label for="nama_kategori">Nama Kategori</label>
        <input type="text" id="nama_kategori" name="nama_kategori" required 
               maxlength="150" minlength="3"
               value="<?php echo htmlspecialchars($kategori['nama_kategori']); ?>"
               placeholder="Contoh: Tanaman Hias Daun">
        <small>Minimal 3 karakter, maksimal 150 karakter</small>
    </div>
    
    <div class="form-group">
        <button type="submit">Perbarui Kategori</button>
        <a href="kategori.php" class="btn-cancel">Batal</a>
    </div>
</form>
<?php else: ?>
    <p>Kategori tidak ditemukan.</p>
    <a href="kategori.php" class="btn-cancel">Kembali ke Daftar Kategori</a>
<?php endif; ?>

<?php include '../templates/footer_admin.php'; ?>
