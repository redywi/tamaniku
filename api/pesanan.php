<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $id_produk = $data['id_produk']?? null;
    $nama_pelanggan = $data['nama_pelanggan']?? null;
    $nomor_whatsapp = $data['nomor_whatsapp']?? null;

    if ($id_produk && $nama_pelanggan && $nomor_whatsapp) {
        try {
            $stmt = $conn->prepare("INSERT INTO pesanan (id_produk, nama_pelanggan, nomor_whatsapp) VALUES (?,?,?)");
            $stmt->bind_param("iss", $id_produk, $nama_pelanggan, $nomor_whatsapp);
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Pesanan berhasil dicatat.';
            } else {
                $response['message'] = 'Gagal menyimpan pesanan.';
            }
        } catch (Exception $e) {
            $response['message'] = 'Terjadi kesalahan: '. $e->getMessage();
        }
    } else {
        $response['message'] = 'Data tidak lengkap.';
    }
}

echo json_encode($response);
?>