<?php
include '../templates/header_admin.php';

$error = '';
$success = '';
$produk = null;

// Ambil ID produk dari URL
$id = $_GET['id'] ?? '';
if (empty($id) || !is_numeric($id)) {
    header("Location: produk.php");
    exit();
}

// Ambil data produk
try {
    $stmt = $conn->prepare("SELECT * FROM produk WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        header("Location: produk.php");
        exit();
    }
    
    $produk = $result->fetch_assoc();
    $stmt->close();
} catch (Exception $e) {
    $error = "Gagal mengambil data produk: " . $e->getMessage();
}

// Proses form edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $produk) {
    $nama = trim($_POST['nama_produk'] ?? '');
    $id_kategori = $_POST['id_kategori'] ?? '';
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $harga = $_POST['harga'] ?? '';
    $stok = $_POST['stok'] ?? '';
    $gambar = $produk['gambar']; // Keep existing image

    // Validasi input
    if (empty($nama) || empty($id_kategori) || empty($harga) || empty($stok)) {
        $error = "Semua field wajib harus diisi!";
    } elseif (!is_numeric($harga) || $harga <= 0) {
        $error = "Harga harus berupa angka positif!";
    } elseif (!is_numeric($stok) || $stok < 0) {
        $error = "Stok harus berupa angka tidak negatif!";
    } else {
        try {
            // Upload gambar baru jika ada
            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
                $target_dir = "../uploads/";
                
                // Validasi file
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                $file_type = $_FILES['gambar']['type'];
                $file_size = $_FILES['gambar']['size'];
                
                if (!in_array($file_type, $allowed_types)) {
                    $error = "Format file tidak didukung! Gunakan JPG, PNG, atau GIF.";
                } elseif ($file_size > 5000000) { // 5MB
                    $error = "Ukuran file terlalu besar! Maksimal 5MB.";
                } else {
                    $file_extension = pathinfo($_FILES["gambar"]["name"], PATHINFO_EXTENSION);
                    $new_gambar = time() . "_" . uniqid() . "." . $file_extension;
                    $target_file = $target_dir . $new_gambar;
                    
                    if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                        // Hapus gambar lama
                        if ($produk['gambar'] && file_exists($target_dir . $produk['gambar'])) {
                            unlink($target_dir . $produk['gambar']);
                        }
                        $gambar = $new_gambar;
                    } else {
                        $error = "Gagal mengupload gambar!";
                    }
                }
            }
            
            if (!$error) {
                $stmt = $conn->prepare("UPDATE produk SET nama_produk = ?, id_kategori = ?, deskripsi = ?, harga = ?, stok = ?, gambar = ? WHERE id = ?");
                $stmt->bind_param("sisiisi", $nama, $id_kategori, $deskripsi, $harga, $stok, $gambar, $id);
                
                if ($stmt->execute()) {
                    $success = "Produk berhasil diperbarui!";
                    // Refresh data produk
                    $stmt2 = $conn->prepare("SELECT * FROM produk WHERE id = ?");
                    $stmt2->bind_param("i", $id);
                    $stmt2->execute();
                    $produk = $stmt2->get_result()->fetch_assoc();
                    $stmt2->close();
                } else {
                    $error = "Gagal memperbarui produk!";
                }
                $stmt->close();
            }
        } catch (Exception $e) {
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

// Ambil daftar kategori
$kategori_list = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");
?>

<h2>Edit Produk</h2>

<?php if ($error): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if ($produk): ?>
<form action="produk_edit.php?id=<?php echo $id; ?>" method="post" enctype="multipart/form-data" class="form-container">
    <div class="form-group">
        <label for="nama_produk">Nama Produk</label>
        <input type="text" id="nama_produk" name="nama_produk" required 
               value="<?php echo htmlspecialchars($produk['nama_produk']); ?>">
    </div>
    
    <div class="form-group">
        <label for="id_kategori">Kategori</label>
        <select id="id_kategori" name="id_kategori" required>
            <option value="">Pilih Kategori</option>
            <?php while ($kat = $kategori_list->fetch_assoc()): ?>
                <option value="<?php echo $kat['id']; ?>" 
                        <?php echo ($produk['id_kategori'] == $kat['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($kat['nama_kategori']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
    
    <div class="form-group">
        <label for="deskripsi">Deskripsi</label>
        <textarea id="deskripsi" name="deskripsi" rows="4" 
                  placeholder="Masukkan deskripsi produk (opsional)"><?php echo htmlspecialchars($produk['deskripsi']); ?></textarea>
    </div>
    
    <div class="form-group">
        <label for="harga">Harga (Rp)</label>
        <input type="number" id="harga" name="harga" required min="0" step="1000"
               value="<?php echo $produk['harga']; ?>"
               placeholder="Contoh: 50000">
    </div>
    
    <div class="form-group">
        <label for="stok">Stok</label>
        <input type="number" id="stok" name="stok" required min="0"
               value="<?php echo $produk['stok']; ?>"
               placeholder="Contoh: 10">
    </div>
    
    <div class="form-group">
        <label for="gambar">Gambar Produk</label>
        <?php if ($produk['gambar']): ?>
            <div class="current-image">
                <img src="../uploads/<?php echo htmlspecialchars($produk['gambar']); ?>" 
                     alt="<?php echo htmlspecialchars($produk['nama_produk']); ?>" 
                     style="max-width: 200px; margin-bottom: 10px; border-radius: 4px;">
                <p><small>Gambar saat ini</small></p>
            </div>
        <?php endif; ?>
        <input type="file" id="gambar" name="gambar" accept="image/*">
        <small>Format: JPG, PNG, GIF. Maksimal 5MB. Kosongkan jika tidak ingin mengubah gambar.</small>
    </div>
    
    <div class="form-group">
        <button type="submit">Perbarui Produk</button>
        <a href="produk.php" class="btn-cancel">Batal</a>
    </div>
</form>
<?php else: ?>
    <p>Produk tidak ditemukan.</p>
    <a href="produk.php" class="btn-cancel">Kembali ke Daftar Produk</a>
<?php endif; ?>

<?php include '../templates/footer_admin.php'; ?>
