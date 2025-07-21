<?php include '../templates/header_admin.php';?>

<h2>Selamat Datang, <?php echo htmlspecialchars($_SESSION['admin_username']);?>!</h2>
<p>Ini adalah halaman dashboard Panel Admin Tamaniku. Gunakan navigasi di atas untuk mengelola konten website.</p>

<div class="dashboard-stats">
    <div class="stat-card">
        <h3>Total Produk</h3>
        <p><?php echo $conn->query("SELECT COUNT(*) as total FROM produk")->fetch_assoc()['total'];?></p>
    </div>
    <div class="stat-card">
        <h3>Total Kategori</h3>
        <p><?php echo $conn->query("SELECT COUNT(*) as total FROM kategori")->fetch_assoc()['total'];?></p>
    </div>
    <div class="stat-card">
        <h3>Pesanan Baru</h3>
        <p><?php echo $conn->query("SELECT COUNT(*) as total FROM pesanan WHERE status = 'Baru'")->fetch_assoc()['total'];?></p>
    </div>
</div>

<?php include '../templates/footer_admin.php';?>