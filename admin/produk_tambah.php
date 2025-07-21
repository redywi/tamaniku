<?php
include '../templates/header_admin.php';

$error = '';
$success = '';

$kategori_list = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama_produk'] ?? '');
    $id_kategori = $_POST['id_kategori'] ?? '';
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $harga = $_POST['harga'] ?? '';
    $stok = $_POST['stok'] ?? '';
    $gambar = '';

    // Validasi input
    if (empty($nama) || empty($id_kategori) || empty($harga) || empty($stok)) {
        $error = "Semua field wajib harus diisi!";
    } elseif (!is_numeric($harga) || $harga <= 0) {
        $error = "Harga harus berupa angka positif!";
    } elseif (!is_numeric($stok) || $stok < 0) {
        $error = "Stok harus berupa angka tidak negatif!";
    } else {
        try {
            // Upload gambar jika ada
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
                    $gambar = time() . "_" . uniqid() . "." . $file_extension;
                    $target_file = $target_dir . $gambar;
                    
                    if (!move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                        $error = "Gagal mengupload gambar!";
                    }
                }
            }
            
            if (!$error) {
                $stmt = $conn->prepare("INSERT INTO produk (nama_produk, id_kategori, deskripsi, harga, stok, gambar) VALUES (?,?,?,?,?,?)");
                $stmt->bind_param("sisiis", $nama, $id_kategori, $deskripsi, $harga, $stok, $gambar);
                
                if ($stmt->execute()) {
                    $success = "Produk berhasil ditambahkan!";
                    // Reset form
                    $_POST = array();
                } else {
                    $error = "Gagal menambahkan produk!";
                }
                $stmt->close();
            }
        } catch (Exception $e) {
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}
?>

<h2>Tambah Produk Baru</h2>

<?php if ($error): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<form action="produk_tambah.php" method="post" enctype="multipart/form-data" class="form-container">
    <div class="form-group">
        <label for="nama_produk">Nama Produk</label>
        <input type="text" id="nama_produk" name="nama_produk" required 
               value="<?php echo isset($_POST['nama_produk']) ? htmlspecialchars($_POST['nama_produk']) : ''; ?>">
    </div>
    <div class="form-group">
        <label for="id_kategori">Kategori</label>
        <select id="id_kategori" name="id_kategori" required>
            <option value="">Pilih Kategori</option>
            <?php 
            $kategori_list = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");
            while ($kat = $kategori_list->fetch_assoc()): 
            ?>
                <option value="<?php echo $kat['id']; ?>" 
                        <?php echo (isset($_POST['id_kategori']) && $_POST['id_kategori'] == $kat['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($kat['nama_kategori']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="deskripsi">Deskripsi</label>
        <textarea id="deskripsi" name="deskripsi" rows="4" 
                  placeholder="Masukkan deskripsi produk (opsional)"><?php echo isset($_POST['deskripsi']) ? htmlspecialchars($_POST['deskripsi']) : ''; ?></textarea>
    </div>
    <div class="form-group">
        <label for="harga">Harga (Rp)</label>
        <input type="number" id="harga" name="harga" required min="0" step="1000"
               value="<?php echo isset($_POST['harga']) ? htmlspecialchars($_POST['harga']) : ''; ?>"
               placeholder="Contoh: 50000">
    </div>
    <div class="form-group">
        <label for="stok">Stok</label>
        <input type="number" id="stok" name="stok" required min="0"
               value="<?php echo isset($_POST['stok']) ? htmlspecialchars($_POST['stok']) : ''; ?>"
               placeholder="Contoh: 10">
    </div>
    <div class="form-group">
        <label for="gambar">Gambar Produk</label>
        <input type="file" id="gambar" name="gambar" accept="image/*">
        <small>Format: JPG, PNG, GIF. Maksimal 5MB.</small>
    </div>
    <div class="form-group">
        <button type="submit">Simpan Produk</button>
        <a href="produk.php" class="btn-cancel">Batal</a>
    </div>
</form>

<?php include '../templates/footer_admin.php';?>