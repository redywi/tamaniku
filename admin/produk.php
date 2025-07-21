<?php
include '../templates/header_admin.php';

$error = '';
$success = '';

// Handle delete dengan validasi
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    
    try {
        // Cek apakah ada pesanan yang menggunakan produk ini
        $check_stmt = $conn->prepare("SELECT COUNT(*) as total FROM pesanan WHERE id_produk = ?");
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $pesanan_count = $check_stmt->get_result()->fetch_assoc()['total'];
        $check_stmt->close();
        
        if ($pesanan_count > 0) {
            $error = "Tidak dapat menghapus produk karena masih ada $pesanan_count pesanan yang terkait!";
        } else {
            // Hapus gambar lama jika ada
            $old_img_query = $conn->query("SELECT gambar FROM produk WHERE id = $id");
            if ($old_img_query->num_rows > 0) {
                $old_img = $old_img_query->fetch_assoc()['gambar'];
                if ($old_img && file_exists("../uploads/" . $old_img)) {
                    unlink("../uploads/" . $old_img);
                }
            }
            
            $stmt = $conn->prepare("DELETE FROM produk WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $success = "Produk berhasil dihapus!";
            } else {
                $error = "Gagal menghapus produk!";
            }
            $stmt->close();
        }
    } catch (Exception $e) {
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}

// Filter dan pencarian
$search = $_GET['search'] ?? '';
$kategori_filter = $_GET['kategori'] ?? '';
$stok_filter = $_GET['stok'] ?? '';
$harga_min = $_GET['harga_min'] ?? '';
$harga_max = $_GET['harga_max'] ?? '';
$sort = $_GET['sort'] ?? 'p.created_at';
$order = $_GET['order'] ?? 'DESC';

// Build query dengan filter
$where_conditions = [];
$params = [];
$param_types = '';

if (!empty($search)) {
    $where_conditions[] = "(p.nama_produk LIKE ? OR p.deskripsi LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $param_types .= 'ss';
}

if (!empty($kategori_filter)) {
    $where_conditions[] = "p.id_kategori = ?";
    $params[] = $kategori_filter;
    $param_types .= 'i';
}

if (!empty($stok_filter)) {
    if ($stok_filter === 'habis') {
        $where_conditions[] = "p.stok = 0";
    } elseif ($stok_filter === 'sedikit') {
        $where_conditions[] = "p.stok > 0 AND p.stok <= 10";
    } elseif ($stok_filter === 'tersedia') {
        $where_conditions[] = "p.stok > 10";
    }
}

if (!empty($harga_min) && is_numeric($harga_min)) {
    $where_conditions[] = "p.harga >= ?";
    $params[] = $harga_min;
    $param_types .= 'i';
}

if (!empty($harga_max) && is_numeric($harga_max)) {
    $where_conditions[] = "p.harga <= ?";
    $params[] = $harga_max;
    $param_types .= 'i';
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Validasi sort field
$allowed_sorts = ['p.nama_produk', 'p.harga', 'p.stok', 'p.created_at', 'k.nama_kategori', 'jumlah_pesanan'];
if (!in_array($sort, $allowed_sorts)) {
    $sort = 'p.created_at';
}

// Validasi order
$order = ($order === 'ASC') ? 'ASC' : 'DESC';

$sql = "SELECT p.*, k.nama_kategori, COUNT(ps.id) as jumlah_pesanan 
        FROM produk p 
        JOIN kategori k ON p.id_kategori = k.id 
        LEFT JOIN pesanan ps ON p.id = ps.id_produk 
        $where_clause 
        GROUP BY p.id 
        ORDER BY $sort $order";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $produk_list = $stmt->get_result();
} else {
    $produk_list = $conn->query($sql);
}

// Ambil daftar kategori untuk filter
$kategori_options = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");
?>

<h2>Manajemen Produk</h2>

<?php if ($error): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<!-- Filter dan Pencarian -->
<div class="filter-section">
    <form method="GET" action="produk.php" class="filter-form">
        <div class="filter-row">
            <div class="filter-group">
                <label for="search">Cari Produk:</label>
                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Nama atau deskripsi produk..." class="search-input">
            </div>
            
            <div class="filter-group">
                <label for="kategori">Kategori:</label>
                <select id="kategori" name="kategori" class="kategori-select">
                    <option value="">Semua Kategori</option>
                    <?php while ($kat = $kategori_options->fetch_assoc()): ?>
                        <option value="<?php echo $kat['id']; ?>" 
                                <?php echo ($kategori_filter == $kat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($kat['nama_kategori']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="stok">Stok:</label>
                <select id="stok" name="stok" class="stok-select">
                    <option value="">Semua Stok</option>
                    <option value="habis" <?php echo ($stok_filter === 'habis') ? 'selected' : ''; ?>>Habis (0)</option>
                    <option value="sedikit" <?php echo ($stok_filter === 'sedikit') ? 'selected' : ''; ?>>Sedikit (1-10)</option>
                    <option value="tersedia" <?php echo ($stok_filter === 'tersedia') ? 'selected' : ''; ?>>Tersedia (>10)</option>
                </select>
            </div>
        </div>
        
        <div class="filter-row">
            <div class="filter-group">
                <label for="harga_min">Harga Min:</label>
                <input type="number" id="harga_min" name="harga_min" value="<?php echo htmlspecialchars($harga_min); ?>" 
                       placeholder="0" class="price-input" step="1000">
            </div>
            
            <div class="filter-group">
                <label for="harga_max">Harga Max:</label>
                <input type="number" id="harga_max" name="harga_max" value="<?php echo htmlspecialchars($harga_max); ?>" 
                       placeholder="999999999" class="price-input" step="1000">
            </div>
            
            <div class="filter-group">
                <label for="sort">Urutkan:</label>
                <select id="sort" name="sort" class="sort-select">
                    <option value="p.created_at" <?php echo ($sort === 'p.created_at') ? 'selected' : ''; ?>>Tanggal Ditambah</option>
                    <option value="p.nama_produk" <?php echo ($sort === 'p.nama_produk') ? 'selected' : ''; ?>>Nama Produk</option>
                    <option value="p.harga" <?php echo ($sort === 'p.harga') ? 'selected' : ''; ?>>Harga</option>
                    <option value="p.stok" <?php echo ($sort === 'p.stok') ? 'selected' : ''; ?>>Stok</option>
                    <option value="k.nama_kategori" <?php echo ($sort === 'k.nama_kategori') ? 'selected' : ''; ?>>Kategori</option>
                    <option value="jumlah_pesanan" <?php echo ($sort === 'jumlah_pesanan') ? 'selected' : ''; ?>>Jumlah Pesanan</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="order">Urutan:</label>
                <select id="order" name="order" class="order-select">
                    <option value="ASC" <?php echo ($order === 'ASC') ? 'selected' : ''; ?>>A-Z / Kecil-Besar</option>
                    <option value="DESC" <?php echo ($order === 'DESC') ? 'selected' : ''; ?>>Z-A / Besar-Kecil</option>
                </select>
            </div>
            
            <div class="filter-group filter-actions">
                <button type="submit" class="btn-filter">Filter</button>
                <a href="produk.php" class="btn-reset">Reset</a>
            </div>
        </div>
    </form>
</div>

<div class="action-bar">
    <a href="produk_tambah.php" class="btn-tambah">Tambah Produk Baru</a>
    <?php
    $total_results = $produk_list->num_rows;
    echo "<span class='results-info'>Menampilkan $total_results produk";
    if (!empty($search) || !empty($kategori_filter) || !empty($stok_filter) || !empty($harga_min) || !empty($harga_max)) {
        echo " (terfilter)";
    }
    echo "</span>";
    ?>
</div>

<div class="table-responsive">
<table>
    <thead>
        <tr>
            <th>Gambar</th>
            <th>Nama Produk</th>
            <th>Kategori</th>
            <th>Harga</th>
            <th>Stok</th>
            <th>Pesanan</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $produk_list->fetch_assoc()): ?>
        <tr>
            <td>
                <?php if ($row['gambar']): ?>
                    <img src="../uploads/<?php echo htmlspecialchars($row['gambar']); ?>" 
                         alt="<?php echo htmlspecialchars($row['nama_produk']); ?>" 
                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                <?php else: ?>
                    <div style="width: 50px; height: 50px; background-color: #f0f0f0; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #999;">
                        No Image
                    </div>
                <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
            <td><?php echo htmlspecialchars($row['nama_kategori']); ?></td>
            <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
            <td><?php echo $row['stok']; ?></td>
            <td><?php echo $row['jumlah_pesanan']; ?> pesanan</td>
            <td>
                <a href="produk_edit.php?id=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                <?php if ($row['jumlah_pesanan'] == 0): ?>
                    <a href="produk_hapus.php?id=<?php echo $row['id']; ?>" class="btn-hapus">Hapus</a>
                <?php else: ?>
                    <span style="color: #999; font-size: 0.9em;">Tidak dapat dihapus</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
</div>

<?php include '../templates/footer_admin.php';?>