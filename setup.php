<?php
/**
 * Setup Otomatis Tamaniku
 * Jalankan file ini sekali setelah upload ke server
 */

echo "<!DOCTYPE html>";
echo "<html><head><title>Setup Tamaniku</title>";
echo "<style>body{font-family:Arial,sans-serif;max-width:800px;margin:50px auto;padding:20px;line-height:1.6;} .success{color:#27ae60;background:#d5f4e6;padding:10px;border-radius:5px;margin:10px 0;} .error{color:#e74c3c;background:#fadbd8;padding:10px;border-radius:5px;margin:10px 0;} .warning{color:#f39c12;background:#fef5e7;padding:10px;border-radius:5px;margin:10px 0;} .step{background:#ecf0f1;padding:15px;margin:10px 0;border-radius:5px;border-left:4px solid #3498db;}</style>";
echo "</head><body>";

echo "<h1>🌱 Setup Tamaniku - Sistem Toko Tanaman Hias</h1>";

$steps = [];

// Step 1: Check PHP Version
echo "<div class='step'>";
echo "<h3>Step 1: Memeriksa Versi PHP</h3>";
if (version_compare(PHP_VERSION, '7.4.0') >= 0) {
    echo "<div class='success'>✅ PHP " . PHP_VERSION . " - Compatible</div>";
    $steps[] = true;
} else {
    echo "<div class='error'>❌ PHP " . PHP_VERSION . " - Memerlukan PHP 7.4 atau lebih tinggi</div>";
    $steps[] = false;
}
echo "</div>";

// Step 2: Check Required Extensions
echo "<div class='step'>";
echo "<h3>Step 2: Memeriksa Ekstensi PHP</h3>";
$required_extensions = ['mysqli', 'json', 'gd', 'session'];
$ext_ok = true;

foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<div class='success'>✅ $ext extension - Available</div>";
    } else {
        echo "<div class='error'>❌ $ext extension - Missing</div>";
        $ext_ok = false;
    }
}
$steps[] = $ext_ok;
echo "</div>";

// Step 3: Check Database Connection
echo "<div class='step'>";
echo "<h3>Step 3: Memeriksa Koneksi Database</h3>";
try {
    require_once 'config/database.php';
    if ($conn->connect_error) {
        echo "<div class='error'>❌ Koneksi Database Gagal: " . $conn->connect_error . "</div>";
        echo "<div class='warning'>Periksa konfigurasi di config/database.php</div>";
        $steps[] = false;
    } else {
        echo "<div class='success'>✅ Koneksi Database Berhasil</div>";
        $steps[] = true;
        
        // Check if tables exist
        $tables = ['admin', 'kategori', 'produk', 'pesanan'];
        $table_ok = true;
        
        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result->num_rows > 0) {
                echo "<div class='success'>✅ Tabel $table - Exists</div>";
            } else {
                echo "<div class='error'>❌ Tabel $table - Missing</div>";
                $table_ok = false;
            }
        }
        
        if (!$table_ok) {
            echo "<div class='warning'>Import file config/db_tamaniku.sql ke database Anda</div>";
        }
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    $steps[] = false;
}
echo "</div>";

// Step 4: Check Uploads Directory
echo "<div class='step'>";
echo "<h3>Step 4: Memeriksa Folder Uploads</h3>";
if (!file_exists('uploads')) {
    if (mkdir('uploads', 0755, true)) {
        echo "<div class='success'>✅ Folder uploads berhasil dibuat</div>";
    } else {
        echo "<div class='error'>❌ Gagal membuat folder uploads</div>";
        $steps[] = false;
    }
} else {
    echo "<div class='success'>✅ Folder uploads sudah ada</div>";
}

if (is_writable('uploads')) {
    echo "<div class='success'>✅ Folder uploads dapat ditulis</div>";
    $steps[] = true;
} else {
    echo "<div class='error'>❌ Folder uploads tidak dapat ditulis</div>";
    echo "<div class='warning'>Jalankan: chmod 755 uploads/</div>";
    $steps[] = false;
}
echo "</div>";

// Step 5: Test API Endpoints
echo "<div class='step'>";
echo "<h3>Step 5: Memeriksa API Endpoints</h3>";
$api_endpoints = [
    'api/kategori.php' => 'API Kategori',
    'api/produk.php' => 'API Produk',
    'api/pesanan.php' => 'API Pesanan'
];

foreach ($api_endpoints as $endpoint => $name) {
    if (file_exists($endpoint)) {
        echo "<div class='success'>✅ $name - File exists</div>";
    } else {
        echo "<div class='error'>❌ $name - File missing</div>";
    }
}
$steps[] = true;
echo "</div>";

// Step 6: Generate Sample Images (if needed)
echo "<div class='step'>";
echo "<h3>Step 6: Sample Images</h3>";
$sample_images = [
    'monstera.jpg', 'philodendron_pink.jpg', 'alocasia_polly.jpg', 
    'calathea_ornata.jpg', 'snake_plant.jpg'
];

$images_found = 0;
foreach ($sample_images as $img) {
    if (file_exists("uploads/$img")) {
        $images_found++;
    }
}

if ($images_found > 0) {
    echo "<div class='success'>✅ $images_found sample images ditemukan</div>";
} else {
    echo "<div class='warning'>⚠️ Tidak ada sample images. Upload gambar produk ke folder uploads/</div>";
}
echo "</div>";

// Final Summary
echo "<div class='step'>";
echo "<h3>Ringkasan Setup</h3>";
$success_count = array_sum($steps);
$total_steps = count($steps);

if ($success_count == $total_steps) {
    echo "<div class='success'>";
    echo "<h4>🎉 Setup Berhasil Sempurna!</h4>";
    echo "<p>Semua komponen telah dikonfigurasi dengan benar.</p>";
    echo "</div>";
} else {
    echo "<div class='warning'>";
    echo "<h4>⚠️ Setup Partial ($success_count/$total_steps)</h4>";
    echo "<p>Beberapa komponen perlu perbaikan sebelum sistem dapat berjalan optimal.</p>";
    echo "</div>";
}
echo "</div>";

// Navigation Links
echo "<div class='step'>";
echo "<h3>🔗 Link Navigasi</h3>";
echo "<p><strong>Frontend:</strong></p>";
echo "<ul>";
echo "<li><a href='index.php' target='_blank'>🏠 Halaman Utama</a></li>";
echo "<li><a href='detail_produk.php?id=1' target='_blank'>📄 Detail Produk Sample</a></li>";
echo "</ul>";

echo "<p><strong>Backend Admin:</strong></p>";
echo "<ul>";
echo "<li><a href='admin/login.php' target='_blank'>🔐 Login Admin</a> (admin/admin)</li>";
echo "<li><a href='admin/index.php' target='_blank'>📊 Dashboard Admin</a></li>";
echo "<li><a href='admin/kategori.php' target='_blank'>📂 Manajemen Kategori</a></li>";
echo "<li><a href='admin/produk.php' target='_blank'>🌿 Manajemen Produk</a></li>";
echo "<li><a href='admin/pesanan.php' target='_blank'>📋 Manajemen Pesanan</a></li>";
echo "</ul>";

echo "<p><strong>API Testing:</strong></p>";
echo "<ul>";
echo "<li><a href='api/kategori.php' target='_blank'>🔧 Test API Kategori</a></li>";
echo "<li><a href='api/produk.php' target='_blank'>🔧 Test API Produk</a></li>";
echo "<li><a href='api/produk.php?search=monstera' target='_blank'>🔧 Test API Search</a></li>";
echo "</ul>";

echo "<p><strong>Development:</strong></p>";
echo "<ul>";
echo "<li><a href='test_connection.php' target='_blank'>🧪 Test Connection Detail</a></li>";
echo "</ul>";
echo "</div>";

// Login Info
echo "<div class='step'>";
echo "<h3>🔑 Informasi Login</h3>";
echo "<div style='background:#e8f4fd;padding:15px;border-radius:5px;border:1px solid #3498db;'>";
echo "<p><strong>Admin Login:</strong></p>";
echo "<p>URL: <code>admin/login.php</code></p>";
echo "<p>Username: <code>admin</code></p>";
echo "<p>Password: <code>admin</code></p>";
echo "<p><em>Silakan ganti password setelah login pertama!</em></p>";
echo "</div>";
echo "</div>";

// Next Steps
echo "<div class='step'>";
echo "<h3>📋 Langkah Selanjutnya</h3>";
echo "<ol>";
echo "<li>Import file <code>config/db_tamaniku.sql</code> jika belum dilakukan</li>";
echo "<li>Upload gambar produk ke folder <code>uploads/</code></li>";
echo "<li>Login ke admin panel dan ganti password default</li>";
echo "<li>Sesuaikan nomor WhatsApp admin di <code>assets/js/main.js</code></li>";
echo "<li>Customize branding dan konten sesuai kebutuhan</li>";
echo "<li>Hapus file setup.php ini setelah selesai</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p style='text-align:center;color:#7f8c8d;'>";
echo "Tamaniku - Sistem Manajemen Toko Tanaman Hias<br>";
echo "Setup completed at " . date('Y-m-d H:i:s');
echo "</p>";

echo "</body></html>";
?>
