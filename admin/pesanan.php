<?php
include '../templates/header_admin.php';

// Logika untuk mengubah status pesanan
if (isset($_POST['update_status'])) {
    $id_pesanan = $_POST['id_pesanan'];
    $status_baru = $_POST['status'];
    $stmt = $conn->prepare("UPDATE pesanan SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status_baru, $id_pesanan);
    $stmt->execute();
    header("Location: pesanan.php" . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : ''));
    exit();
}

// Filter dan pencarian
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$tanggal_dari = $_GET['tanggal_dari'] ?? '';
$tanggal_sampai = $_GET['tanggal_sampai'] ?? '';
$sort = $_GET['sort'] ?? 'ps.created_at';
$order = $_GET['order'] ?? 'DESC';

// Build query dengan filter
$where_conditions = [];
$params = [];
$param_types = '';

if (!empty($search)) {
    $where_conditions[] = "(ps.nama_pelanggan LIKE ? OR ps.nomor_whatsapp LIKE ? OR p.nama_produk LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $param_types .= 'sss';
}

if (!empty($status_filter)) {
    $where_conditions[] = "ps.status = ?";
    $params[] = $status_filter;
    $param_types .= 's';
}

if (!empty($tanggal_dari)) {
    $where_conditions[] = "DATE(ps.created_at) >= ?";
    $params[] = $tanggal_dari;
    $param_types .= 's';
}

if (!empty($tanggal_sampai)) {
    $where_conditions[] = "DATE(ps.created_at) <= ?";
    $params[] = $tanggal_sampai;
    $param_types .= 's';
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Validasi sort field
$allowed_sorts = ['ps.created_at', 'ps.nama_pelanggan', 'ps.status', 'p.nama_produk'];
if (!in_array($sort, $allowed_sorts)) {
    $sort = 'ps.created_at';
}

// Validasi order
$order = ($order === 'ASC') ? 'ASC' : 'DESC';

$sql = "SELECT ps.*, p.nama_produk, p.harga, k.nama_kategori 
        FROM pesanan ps 
        JOIN produk p ON ps.id_produk = p.id 
        JOIN kategori k ON p.id_kategori = k.id
        $where_clause 
        ORDER BY $sort $order";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $pesanan_list = $stmt->get_result();
} else {
    $pesanan_list = $conn->query($sql);
}
?>

<h2>Manajemen Pesanan</h2>

<!-- Filter dan Pencarian -->
<div class="filter-section">
    <form method="GET" action="pesanan.php" class="filter-form">
        <div class="filter-row">
            <div class="filter-group">
                <label for="search">Cari Pesanan:</label>
                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Nama pelanggan, nomor WA, atau produk..." class="search-input">
            </div>
            
            <div class="filter-group">
                <label for="status">Status:</label>
                <select id="status" name="status" class="status-select">
                    <option value="">Semua Status</option>
                    <option value="Baru" <?php echo ($status_filter === 'Baru') ? 'selected' : ''; ?>>Baru</option>
                    <option value="Diproses" <?php echo ($status_filter === 'Diproses') ? 'selected' : ''; ?>>Diproses</option>
                    <option value="Selesai" <?php echo ($status_filter === 'Selesai') ? 'selected' : ''; ?>>Selesai</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="tanggal_dari">Dari Tanggal:</label>
                <input type="date" id="tanggal_dari" name="tanggal_dari" value="<?php echo htmlspecialchars($tanggal_dari); ?>" 
                       class="date-input">
            </div>
            
            <div class="filter-group">
                <label for="tanggal_sampai">Sampai Tanggal:</label>
                <input type="date" id="tanggal_sampai" name="tanggal_sampai" value="<?php echo htmlspecialchars($tanggal_sampai); ?>" 
                       class="date-input">
            </div>
        </div>
        
        <div class="filter-row">
            <div class="filter-group">
                <label for="sort">Urutkan:</label>
                <select id="sort" name="sort" class="sort-select">
                    <option value="ps.created_at" <?php echo ($sort === 'ps.created_at') ? 'selected' : ''; ?>>Tanggal Pesanan</option>
                    <option value="ps.nama_pelanggan" <?php echo ($sort === 'ps.nama_pelanggan') ? 'selected' : ''; ?>>Nama Pelanggan</option>
                    <option value="ps.status" <?php echo ($sort === 'ps.status') ? 'selected' : ''; ?>>Status</option>
                    <option value="p.nama_produk" <?php echo ($sort === 'p.nama_produk') ? 'selected' : ''; ?>>Nama Produk</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="order">Urutan:</label>
                <select id="order" name="order" class="order-select">
                    <option value="ASC" <?php echo ($order === 'ASC') ? 'selected' : ''; ?>>A-Z / Lama-Baru</option>
                    <option value="DESC" <?php echo ($order === 'DESC') ? 'selected' : ''; ?>>Z-A / Baru-Lama</option>
                </select>
            </div>
            
            <div class="filter-group filter-actions">
                <button type="submit" class="btn-filter">Filter</button>
                <a href="pesanan.php" class="btn-reset">Reset</a>
            </div>
        </div>
    </form>
</div>

<div class="action-bar">
    <?php
    $total_results = $pesanan_list->num_rows;
    echo "<span class='results-info'>Menampilkan $total_results pesanan";
    if (!empty($search) || !empty($status_filter) || !empty($tanggal_dari) || !empty($tanggal_sampai)) {
        echo " (terfilter)";
    }
    echo "</span>";
    
    // Tampilkan statistik status
    $status_stats = $conn->query("SELECT status, COUNT(*) as total FROM pesanan GROUP BY status");
    echo "<div class='status-stats'>";
    while ($stat = $status_stats->fetch_assoc()) {
        $status_class = strtolower($stat['status']);
        echo "<span class='status-stat status-$status_class'>{$stat['status']}: {$stat['total']}</span>";
    }
    echo "</div>";
    ?>
</div>

<table>
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Pelanggan</th>
            <th>No. WhatsApp</th>
            <th>Produk Dipesan</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $pesanan_list->fetch_assoc()):?>
        <tr>
            <td><?php echo date('d M Y, H:i', strtotime($row['created_at'])); ?></td>
            <td>
                <strong><?php echo htmlspecialchars($row['nama_pelanggan']); ?></strong>
                <br><small>Kategori: <?php echo htmlspecialchars($row['nama_kategori']); ?></small>
            </td>
            <td>
                <a href="https://wa.me/<?php echo htmlspecialchars($row['nomor_whatsapp']); ?>" 
                   target="_blank" class="whatsapp-link">
                    <?php echo htmlspecialchars($row['nomor_whatsapp']); ?>
                </a>
            </td>
            <td>
                <strong><?php echo htmlspecialchars($row['nama_produk']); ?></strong>
                <br><small>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></small>
            </td>
            <td>
                <span class="status-<?php echo strtolower($row['status']); ?>">
                    <?php echo $row['status']; ?>
                </span>
            </td>
            <td>
                <form action="pesanan.php" method="post" style="display:inline;" class="status-form">
                    <input type="hidden" name="id_pesanan" value="<?php echo $row['id']; ?>">
                    <?php 
                    // Preserve current filters in form
                    foreach ($_GET as $key => $value) {
                        if ($key !== 'id') {
                            echo "<input type='hidden' name='$key' value='" . htmlspecialchars($value) . "'>";
                        }
                    }
                    ?>
                    <select name="status" onchange="if(confirm('Yakin ingin mengubah status pesanan?')) this.form.submit();">
                        <option value="Baru" <?php if($row['status'] == 'Baru') echo 'selected'; ?>>Baru</option>
                        <option value="Diproses" <?php if($row['status'] == 'Diproses') echo 'selected'; ?>>Diproses</option>
                        <option value="Selesai" <?php if($row['status'] == 'Selesai') echo 'selected'; ?>>Selesai</option>
                    </select>
                </form>
            </td>
        </tr>
        <?php endwhile;?>
    </tbody>
</table>

<?php include '../templates/footer_admin.php';?>