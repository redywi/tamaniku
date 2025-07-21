<?php
// Mulai sesi di setiap halaman
session_start();

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Ganti dengan username database Anda
define('DB_PASS', '');     // Ganti dengan password database Anda
define('DB_NAME', 'db_tamaniku'); // Ganti dengan nama database Anda

// Buat Koneksi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Periksa Koneksi
if ($conn->connect_error) {
    die("Koneksi Gagal: ". $conn->connect_error);
}

// Fungsi untuk memeriksa apakah admin sudah login
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Fungsi untuk mengalihkan jika admin belum login
function redirectIfNotAdmin() {
    if (!isAdminLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}
?>