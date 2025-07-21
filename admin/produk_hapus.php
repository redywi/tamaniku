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
    $stmt = $conn->prepare("SELECT p.*, k.nama_kategori FROM produk p JOIN kategori k ON p.id_kategori = k.id WHERE p.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        header("Location: produk.php");
        exit();
    }
    
    $produk = $result->fetch_assoc();
    $stmt->close();
    
    // Cek apakah ada pesanan yang menggunakan produk ini
    $check_pesanan = $conn->prepare("SELECT COUNT(*) as total FROM pesanan WHERE id_produk = ?");
    $check_pesanan->bind_param("i", $id);
    $check_pesanan->execute();
    $pesanan_count = $check_pesanan->get_result()->fetch_assoc()['total'];
    $check_pesanan->close();
    
} catch (Exception $e) {
    $error = "Gagal mengambil data produk: " . $e->getMessage();
}

// Proses konfirmasi hapus
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_delete']) && $produk) {
    try {
        // Cek lagi apakah ada pesanan yang menggunakan produk ini
        $check_pesanan = $conn->prepare("SELECT COUNT(*) as total FROM pesanan WHERE id_produk = ?");
        $check_pesanan->bind_param("i", $id);
        $check_pesanan->execute();
        $pesanan_count = $check_pesanan->get_result()->fetch_assoc()['total'];
        $check_pesanan->close();
        
        if ($pesanan_count > 0) {
            $error = "Tidak dapat menghapus produk ini karena masih ada $pesanan_count pesanan yang terkait dengan produk ini!";
        } else {
            // Hapus gambar jika ada
            if ($produk['gambar'] && file_exists("../uploads/" . $produk['gambar'])) {
                unlink("../uploads/" . $produk['gambar']);
            }
            
            $stmt = $conn->prepare("DELETE FROM produk WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $success = "Produk berhasil dihapus!";
                // Redirect after 2 seconds
                header("refresh:2;url=produk.php");
            } else {
                $error = "Gagal menghapus produk!";
            }
            $stmt->close();
        }
    } catch (Exception $e) {
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}
?>

<h2>Hapus Produk</h2>

<?php if ($error): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="success">
        <?php echo htmlspecialchars($success); ?>
        <br><small>Anda akan dialihkan ke halaman produk dalam 2 detik...</small>
    </div>
<?php endif; ?>

<?php if ($produk && !$success): ?>
<div class="form-container">
    <h3>Konfirmasi Penghapusan</h3>
    
    <div style="display: flex; gap: 20px; margin-bottom: 20px;">
        <?php if ($produk['gambar']): ?>
            <div>
                <img src="../uploads/<?php echo htmlspecialchars($produk['gambar']); ?>" 
                     alt="<?php echo htmlspecialchars($produk['nama_produk']); ?>" 
                     style="max-width: 150px; border-radius: 4px;">
            </div>
        <?php endif; ?>
        
        <div>
            <h4><?php echo htmlspecialchars($produk['nama_produk']); ?></h4>
            <p><strong>Kategori:</strong> <?php echo htmlspecialchars($produk['nama_kategori']); ?></p>
            <p><strong>Harga:</strong> Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></p>
            <p><strong>Stok:</strong> <?php echo $produk['stok']; ?></p>
            <?php if ($produk['deskripsi']): ?>
                <p><strong>Deskripsi:</strong> <?php echo htmlspecialchars(substr($produk['deskripsi'], 0, 100)); ?>...</p>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($pesanan_count > 0): ?>
        <div class="error">
            <strong>Peringatan!</strong><br>
            Produk ini tidak dapat dihapus karena masih ada <strong><?php echo $pesanan_count; ?> pesanan</strong> yang terkait dengan produk ini.
            <br><br>
            Silakan selesaikan atau hapus pesanan-pesanan tersebut terlebih dahulu, atau ubah status produk menjadi tidak aktif.
        </div>
        <div class="form-group">
            <a href="produk.php" class="btn-cancel">Kembali</a>
            <a href="pesanan.php" class="btn-edit">Lihat Pesanan</a>
        </div>
    <?php else: ?>
        <div class="success" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">
            <strong>Produk Siap Dihapus</strong><br>
            Tidak ada pesanan yang terkait dengan produk ini.
        </div>
        <p><strong>Apakah Anda yakin ingin menghapus produk ini?</strong></p>
        <p><small>Tindakan ini tidak dapat dibatalkan dan akan menghapus gambar produk juga.</small></p>
        
        <form action="produk_hapus.php?id=<?php echo $id; ?>" method="post">
            <div class="form-group">
                <button type="submit" name="confirm_delete" class="btn-hapus" 
                        style="background-color: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px;">
                    Ya, Hapus Produk
                </button>
                <a href="produk.php" class="btn-cancel">Batal</a>
            </div>
        </form>
    <?php endif; ?>
</div>
<?php else: ?>
    <p>Produk tidak ditemukan.</p>
    <a href="produk.php" class="btn-cancel">Kembali ke Daftar Produk</a>
<?php endif; ?>

<?php include '../templates/footer_admin.php'; ?>
