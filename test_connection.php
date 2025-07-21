<?php
// Test koneksi database dan struktur
require_once 'config/database.php';

echo "<h2>Test Koneksi Database Tamaniku</h2>";

// Test koneksi
if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Koneksi gagal: " . $conn->connect_error . "</p>";
    exit();
} else {
    echo "<p style='color: green;'>✅ Koneksi database berhasil!</p>";
}

// Test tabel admin
echo "<h3>Test Tabel Admin</h3>";
try {
    $result = $conn->query("SELECT COUNT(*) as total FROM admin");
    if ($result) {
        $count = $result->fetch_assoc()['total'];
        echo "<p style='color: green;'>✅ Tabel admin tersedia dengan $count akun</p>";
        
        // Tampilkan akun admin
        $admin_result = $conn->query("SELECT username FROM admin");
        echo "<ul>";
        while ($admin = $admin_result->fetch_assoc()) {
            echo "<li>Username: " . htmlspecialchars($admin['username']) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>❌ Tabel admin tidak ditemukan</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

// Test tabel kategori
echo "<h3>Test Tabel Kategori</h3>";
try {
    $result = $conn->query("SELECT COUNT(*) as total FROM kategori");
    if ($result) {
        $count = $result->fetch_assoc()['total'];
        echo "<p style='color: green;'>✅ Tabel kategori tersedia dengan $count kategori</p>";
        
        // Tampilkan kategori
        $kategori_result = $conn->query("SELECT nama_kategori FROM kategori LIMIT 5");
        echo "<ul>";
        while ($kategori = $kategori_result->fetch_assoc()) {
            echo "<li>" . htmlspecialchars($kategori['nama_kategori']) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>❌ Tabel kategori tidak ditemukan</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

// Test tabel produk
echo "<h3>Test Tabel Produk</h3>";
try {
    $result = $conn->query("SELECT COUNT(*) as total FROM produk");
    if ($result) {
        $count = $result->fetch_assoc()['total'];
        echo "<p style='color: green;'>✅ Tabel produk tersedia dengan $count produk</p>";
        
        // Tampilkan beberapa produk
        $produk_result = $conn->query("SELECT p.nama_produk, k.nama_kategori, p.harga FROM produk p JOIN kategori k ON p.id_kategori = k.id LIMIT 5");
        echo "<ul>";
        while ($produk = $produk_result->fetch_assoc()) {
            echo "<li>" . htmlspecialchars($produk['nama_produk']) . " (" . htmlspecialchars($produk['nama_kategori']) . ") - Rp " . number_format($produk['harga'], 0, ',', '.') . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>❌ Tabel produk tidak ditemukan</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

// Test tabel pesanan
echo "<h3>Test Tabel Pesanan</h3>";
try {
    $result = $conn->query("SELECT COUNT(*) as total FROM pesanan");
    if ($result) {
        $count = $result->fetch_assoc()['total'];
        echo "<p style='color: green;'>✅ Tabel pesanan tersedia dengan $count pesanan</p>";
    } else {
        echo "<p style='color: red;'>❌ Tabel pesanan tidak ditemukan</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

// Test API
echo "<h3>Test API</h3>";
echo "<ul>";
echo "<li><a href='api/kategori.php' target='_blank'>Test API Kategori</a></li>";
echo "<li><a href='api/produk.php' target='_blank'>Test API Produk</a></li>";
echo "<li><a href='api/produk.php?kategori=1' target='_blank'>Test API Produk by Kategori</a></li>";
echo "</ul>";

// Test folder uploads
echo "<h3>Test Folder Uploads</h3>";
if (is_dir('uploads')) {
    if (is_writable('uploads')) {
        echo "<p style='color: green;'>✅ Folder uploads tersedia dan dapat ditulis</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Folder uploads tersedia tapi tidak dapat ditulis</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Folder uploads tidak ditemukan</p>";
    echo "<p>Membuat folder uploads...</p>";
    if (mkdir('uploads', 0755, true)) {
        echo "<p style='color: green;'>✅ Folder uploads berhasil dibuat</p>";
    } else {
        echo "<p style='color: red;'>❌ Gagal membuat folder uploads</p>";
    }
}

// Informasi login
echo "<h3>Informasi Login Admin</h3>";
echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>URL Admin:</strong> <a href='admin/login.php'>admin/login.php</a></p>";
echo "<p><strong>Username:</strong> admin</p>";
echo "<p><strong>Password:</strong> admin</p>";
echo "</div>";

echo "<h3>Link Navigasi</h3>";
echo "<ul>";
echo "<li><a href='index.php'>Halaman Utama</a></li>";
echo "<li><a href='admin/login.php'>Login Admin</a></li>";
echo "<li><a href='admin/index.php'>Dashboard Admin</a> (setelah login)</li>";
echo "</ul>";

$conn->close();
?>
