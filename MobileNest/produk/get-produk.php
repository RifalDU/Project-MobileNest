<?php
header('Content-Type: application/json');
require_once '../config.php';

// Get all available products
$sql = "SELECT id_produk, nama_produk, merek, deskripsi, harga, stok, kategori, status_produk, tanggal_ditambahkan FROM produk WHERE status_produk = 'Tersedia' ORDER BY tanggal_ditambahkan DESC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
    exit;
}

$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Convert harga to integer for proper filtering
    $row['harga'] = (int)$row['harga'];
    $row['stok'] = (int)$row['stok'];
    $products[] = $row;
}

echo json_encode($products);
?>
