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
    
    // Cek apakah ada produk yang menggunakan kategori ini
    $check_produk = $conn->prepare("SELECT COUNT(*) as total FROM produk WHERE id_kategori = ?");
    $check_produk->bind_param("i", $id);
    $check_produk->execute();
    $produk_count = $check_produk->get_result()->fetch_assoc()['total'];
    $check_produk->close();
    
    // Cek apakah ada pesanan terkait dengan produk dalam kategori ini
    $check_pesanan = $conn->prepare("SELECT COUNT(*) as total FROM pesanan ps JOIN produk p ON ps.id_produk = p.id WHERE p.id_kategori = ?");
    $check_pesanan->bind_param("i", $id);
    $check_pesanan->execute();
    $pesanan_count = $check_pesanan->get_result()->fetch_assoc()['total'];
    $check_pesanan->close();
    
} catch (Exception $e) {
    $error = "Gagal mengambil data kategori: " . $e->getMessage();
}

// Proses konfirmasi hapus
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_delete']) && $kategori) {
    try {
        // Cek lagi apakah ada produk yang menggunakan kategori ini
        $check_produk = $conn->prepare("SELECT COUNT(*) as total FROM produk WHERE id_kategori = ?");
        $check_produk->bind_param("i", $id);
        $check_produk->execute();
        $current_produk_count = $check_produk->get_result()->fetch_assoc()['total'];
        $check_produk->close();
        
        // Cek lagi pesanan terkait
        $check_pesanan = $conn->prepare("SELECT COUNT(*) as total FROM pesanan ps JOIN produk p ON ps.id_produk = p.id WHERE p.id_kategori = ?");
        $check_pesanan->bind_param("i", $id);
        $check_pesanan->execute();
        $current_pesanan_count = $check_pesanan->get_result()->fetch_assoc()['total'];
        $check_pesanan->close();
        
        if ($current_produk_count > 0) {
            $error = "Tidak dapat menghapus kategori ini karena masih ada <strong>$current_produk_count produk</strong> yang menggunakan kategori ini!";
        } elseif ($current_pesanan_count > 0) {
            $error = "Tidak dapat menghapus kategori ini karena masih ada <strong>$current_pesanan_count pesanan</strong> yang terkait dengan produk dalam kategori ini!";
        } else {
            $stmt = $conn->prepare("DELETE FROM kategori WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $success = "Kategori berhasil dihapus!";
                // Redirect after 2 seconds
                header("refresh:2;url=kategori.php");
            } else {
                $error = "Gagal menghapus kategori!";
            }
            $stmt->close();
        }
    } catch (Exception $e) {
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}
?>

<h2>Hapus Kategori</h2>

<?php if ($error): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="success">
        <?php echo htmlspecialchars($success); ?>
        <br><small>Anda akan dialihkan ke halaman kategori dalam 2 detik...</small>
    </div>
<?php endif; ?>

<?php if ($kategori && !$success): ?>
<div class="form-container">
    <h3>Konfirmasi Penghapusan</h3>
    <p>Anda akan menghapus kategori: <strong><?php echo htmlspecialchars($kategori['nama_kategori']); ?></strong></p>
    
    <?php if ($produk_count > 0 || $pesanan_count > 0): ?>
        <div class="error">
            <strong>Peringatan!</strong><br>
            Kategori ini tidak dapat dihapus karena:
            <ul style="margin: 10px 0; padding-left: 20px;">
                <?php if ($produk_count > 0): ?>
                    <li>Masih ada <strong><?php echo $produk_count; ?> produk</strong> yang menggunakan kategori ini</li>
                <?php endif; ?>
                <?php if ($pesanan_count > 0): ?>
                    <li>Masih ada <strong><?php echo $pesanan_count; ?> pesanan</strong> yang terkait dengan produk dalam kategori ini</li>
                <?php endif; ?>
            </ul>
            <p>Silakan selesaikan pesanan dan hapus atau pindahkan produk-produk tersebut ke kategori lain terlebih dahulu.</p>
        </div>
        <div class="form-group">
            <a href="kategori.php" class="btn-cancel">Kembali</a>
            <?php if ($produk_count > 0): ?>
                <a href="produk.php?kategori=<?php echo $id; ?>" class="btn-edit">Lihat Produk</a>
            <?php endif; ?>
            <?php if ($pesanan_count > 0): ?>
                <a href="pesanan.php" class="btn-edit">Lihat Pesanan</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="success" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">
            <strong>Kategori Siap Dihapus</strong><br>
            Tidak ada produk atau pesanan yang terkait dengan kategori ini.
        </div>
        <p><strong>Apakah Anda yakin ingin menghapus kategori ini?</strong></p>
        <p><small>Tindakan ini tidak dapat dibatalkan.</small></p>
        
        <form action="kategori_hapus.php?id=<?php echo $id; ?>" method="post">
            <div class="form-group">
                <button type="submit" name="confirm_delete" class="btn-hapus" 
                        style="background-color: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px;">
                    Ya, Hapus Kategori
                </button>
                <a href="kategori.php" class="btn-cancel">Batal</a>
            </div>
        </form>
    <?php endif; ?>
</div>
<?php else: ?>
    <p>Kategori tidak ditemukan.</p>
    <a href="kategori.php" class="btn-cancel">Kembali ke Daftar Kategori</a>
<?php endif; ?>

<?php include '../templates/footer_admin.php'; ?>
