<?php
require_once '../config/database.php';

// Cek apakah ada konfirmasi logout
if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    session_destroy();
    header("Location: login.php?message=logged_out");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Logout - Tamaniku</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .logout-confirmation {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            background-color: #fff;
            text-align: center;
        }
        .logout-confirmation h2 {
            color: #dc3545;
            margin-bottom: 20px;
        }
        .logout-confirmation p {
            margin-bottom: 30px;
            color: #666;
        }
        .logout-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        .btn-confirm {
            background-color: #dc3545;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-cancel {
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-confirm:hover {
            background-color: #c82333;
        }
        .btn-cancel:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="logout-confirmation">
        <h2>Konfirmasi Logout</h2>
        <p>Apakah Anda yakin ingin keluar dari panel admin?</p>
        <div class="logout-buttons">
            <a href="logout.php?confirm=yes" class="btn-confirm">Ya, Logout</a>
            <a href="javascript:history.back()" class="btn-cancel">Batal</a>
        </div>
    </div>
</body>
</html>