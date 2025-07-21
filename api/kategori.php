<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$result = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");
$data = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode(['success' => true, 'data' => $data]);
?>