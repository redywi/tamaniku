<?php
include '../templates/header_admin.php';

$error = '';
$success = '';

// Handle delete dengan validasi foreign key
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    
    try {
        // Cek apakah ada produk yang menggunakan kategori ini
        $check_stmt = $conn->prepare("SELECT COUNT(*) as total FROM produk WHERE id_kategori = ?");
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $produk_count = $check_stmt->get_result()->fetch_assoc()['total'];
        $check_stmt->close();
        
        if ($produk_count > 0) {
            $error = "Tidak dapat menghapus kategori karena masih ada $produk_count produk yang menggunakan kategori ini!";
        } else {
            $stmt = $conn->prepare("DELETE FROM kategori WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $success = "Kategori berhasil dihapus!";
            } else {
                $error = "Gagal menghapus kategori!";
            }
            $stmt->close();
        }
    } catch (Exception $e) {
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}

// Filter dan pencarian
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'nama_kategori';
$order = $_GET['order'] ?? 'ASC';

// Build query dengan filter
$where_conditions = [];
$params = [];
$param_types = '';

if (!empty($search)) {
    $where_conditions[] = "k.nama_kategori LIKE ?";
    $params[] = "%$search%";
    $param_types .= 's';
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Validasi sort field
$allowed_sorts = ['k.nama_kategori', 'k.id', 'jumlah_produk'];
if (!in_array($sort, $allowed_sorts)) {
    $sort = 'k.nama_kategori';
}

// Validasi order
$order = ($order === 'DESC') ? 'DESC' : 'ASC';

$sql = "SELECT k.*, COUNT(p.id) as jumlah_produk FROM kategori k LEFT JOIN produk p ON k.id = p.id_kategori $where_clause GROUP BY k.id ORDER BY $sort $order";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $kategori_list = $stmt->get_result();
} else {
    $kategori_list = $conn->query($sql);
}
?>

<h2>Manajemen Kategori</h2>

<?php if ($error): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<!-- Filter dan Pencarian -->
<div class="filter-section">
    <form method="GET" action="kategori.php" class="filter-form">
        <div class="filter-row">
            <div class="filter-group">
                <label for="search">Cari Kategori:</label>
                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Nama kategori..." class="search-input">
            </div>
            
            <div class="filter-group">
                <label for="sort">Urutkan:</label>
                <select id="sort" name="sort" class="sort-select">
                    <option value="k.nama_kategori" <?php echo ($sort === 'k.nama_kategori') ? 'selected' : ''; ?>>Nama Kategori</option>
                    <option value="k.id" <?php echo ($sort === 'k.id') ? 'selected' : ''; ?>>ID</option>
                    <option value="jumlah_produk" <?php echo ($sort === 'jumlah_produk') ? 'selected' : ''; ?>>Jumlah Produk</option>
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
                <a href="kategori.php" class="btn-reset">Reset</a>
            </div>
        </div>
    </form>
</div>

<div class="action-bar">
    <a href="kategori_tambah.php" class="btn-tambah">Tambah Kategori Baru</a>
    <?php
    $total_results = $kategori_list->num_rows;
    echo "<span class='results-info'>Menampilkan $total_results kategori";
    if (!empty($search)) {
        echo " untuk pencarian '<strong>" . htmlspecialchars($search) . "</strong>'";
    }
    echo "</span>";
    ?>
</div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nama Kategori</th>
            <th>Jumlah Produk</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $kategori_list->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['nama_kategori']); ?></td>
            <td><?php echo $row['jumlah_produk']; ?> produk</td>
            <td>
                <a href="kategori_edit.php?id=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                <?php if ($row['jumlah_produk'] == 0): ?>
                    <a href="kategori_hapus.php?id=<?php echo $row['id']; ?>" class="btn-hapus">Hapus</a>
                <?php else: ?>
                    <span style="color: #999; font-size: 0.9em;">Tidak dapat dihapus</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include '../templates/footer_admin.php';?>