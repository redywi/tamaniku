<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$response = [];

try {
    if (isset($_GET['id'])) {
        // Mengambil detail satu produk
        $id = intval($_GET['id']);
        $stmt = $conn->prepare("SELECT p.*, k.nama_kategori FROM produk p JOIN kategori k ON p.id_kategori = k.id WHERE p.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
    } else {
        // Mengambil semua produk dengan filter
        $where_conditions = [];
        $params = [];
        $param_types = '';
        
        // Base query
        $sql = "SELECT p.*, k.nama_kategori FROM produk p JOIN kategori k ON p.id_kategori = k.id";
        
        // Filter by category
        if (isset($_GET['kategori']) && !empty($_GET['kategori'])) {
            $where_conditions[] = "p.id_kategori = ?";
            $params[] = intval($_GET['kategori']);
            $param_types .= 'i';
        }
        
        // Filter by search
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = '%' . $_GET['search'] . '%';
            $where_conditions[] = "(p.nama_produk LIKE ? OR p.deskripsi LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $param_types .= 'ss';
        }
        
        // Filter by price range
        if (isset($_GET['harga_min']) && !empty($_GET['harga_min'])) {
            $where_conditions[] = "p.harga >= ?";
            $params[] = intval($_GET['harga_min']);
            $param_types .= 'i';
        }
        
        if (isset($_GET['harga_max']) && !empty($_GET['harga_max'])) {
            $where_conditions[] = "p.harga <= ?";
            $params[] = intval($_GET['harga_max']);
            $param_types .= 'i';
        }
        
        // Add WHERE clause if there are conditions
        if (!empty($where_conditions)) {
            $sql .= " WHERE " . implode(" AND ", $where_conditions);
        }
        
        // Sort
        $sort = $_GET['sort'] ?? 'nama';
        switch ($sort) {
            case 'harga_asc':
                $sql .= " ORDER BY p.harga ASC";
                break;
            case 'harga_desc':
                $sql .= " ORDER BY p.harga DESC";
                break;
            case 'terbaru':
                $sql .= " ORDER BY p.created_at DESC";
                break;
            default:
                $sql .= " ORDER BY p.nama_produk ASC";
        }
        
        // Execute query
        if (!empty($params)) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($param_types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            $result = $conn->query($sql);
            $data = $result->fetch_all(MYSQLI_ASSOC);
        }
    }
    
    $response['success'] = true;
    $response['data'] = $data;
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>