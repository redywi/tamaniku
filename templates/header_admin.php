<?php
require_once '../config/database.php';
redirectIfNotAdmin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Tamaniku</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="admin-header">
        <h1>Tamaniku - Panel Admin</h1>
        <nav>
            <a href="index.php">Dashboard</a>
            <a href="kategori.php">Kategori</a>
            <a href="produk.php">Produk</a>
            <a href="pesanan.php">Pesanan</a>
            <a href="logout.php" class="logout-link">Logout</a>
        </nav>
    </header>
    <main class="admin-container">