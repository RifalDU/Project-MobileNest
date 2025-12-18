<?php
// Mulai session
session_start();

// Cek user sudah login
if (!isset($_SESSION['user'])) {
    header('Location: ../user/login.php');
    exit;
}

// Include config database
require_once '../config.php';

$user_id = $_SESSION['user'];
$user_data = [];
$cart_items = [];
$total_harga = 0;
$message = '';
$message_type = '';

// Ambil data user
$sql = "SELECT id_user, nama_lengkap, email, no_telepon, alamat FROM users WHERE id_user = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
} else {
    header('Location: ../user/login.php');
    exit;
}

// Ambil data keranjang
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $id_produk = $item['id_produk'];
        $kuantitas = $item['kuantitas'];
        
        // Ambil data produk
        $sql = "SELECT id_produk, nama_produk, harga FROM produk WHERE id_produk = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id_produk);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $produk = $result->fetch_assoc();
            $subtotal = $produk['harga'] * $kuantitas;
            $cart_items[] = [
                'id_produk' => $id_produk,
                'nama_produk' => $produk['nama_produk'],
                'harga' => $produk['harga'],
                'kuantitas' => $kuantitas,
                'subtotal' => $subtotal
            ];
            $total_harga += $subtotal;
        }
    }
}

// Process CHECKOUT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'checkout') {
    // Validasi input
    $alamat_pengiriman = trim($_POST['alamat_pengiriman'] ?? '');
    $metode_pembayaran = trim($_POST['metode_pembayaran'] ?? '');
    
    $errors = [];
    
    if (empty($alamat_pengiriman)) {
        $errors[] = 'Alamat pengiriman harus diisi';
    }
    
    if (empty($metode_pembayaran)) {
        $errors[] = 'Metode pembayaran harus dipilih';
    }
    
    if (empty($cart_items)) {
        $errors[] = 'Keranjang kosong';
    }
    
    if (empty($errors)) {
        // Insert ke tabel transaksi
        $insert_sql = "INSERT INTO transaksi (id_user, tanggal_transaksi, total_harga, status_pesanan, metode_pembayaran, alamat_pengiriman) 
                       VALUES (?, NOW(), ?, 'Pending', ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param('idss', $user_id, $total_harga, $metode_pembayaran, $alamat_pengiriman);
        
        if ($insert_stmt->execute()) {
            $id_transaksi = $conn->insert_id;
            
            // Insert detail transaksi
            $detail_sql = "INSERT INTO detail_transaksi (id_transaksi, id_produk, kuantitas, harga_satuan, subtotal) 
                          VALUES (?, ?, ?, ?, ?)";
            $detail_stmt = $conn->prepare($detail_sql);
            
            $success = true;
            foreach ($cart_items as $item) {
                $id_produk = $item['id_produk'];
                $kuantitas = $item['kuantitas'];
                $harga_satuan = $item['harga'];
                $subtotal = $item['subtotal'];
                
                $detail_stmt->bind_param('iiiid', $id_transaksi, $id_produk, $kuantitas, $harga_satuan, $subtotal);
                
                if (!$detail_stmt->execute()) {
                    $success = false;
                    break;
                }
                
                // Update stok produk
                $update_stok = "UPDATE produk SET stok = stok - ? WHERE id_produk = ?";
                $update_stmt = $conn->prepare($update_stok);
                $update_stmt->bind_param('ii', $kuantitas, $id_produk);
                $update_stmt->execute();
            }
            
            if ($success) {
                // Clear cart
                unset($_SESSION['cart']);
                
                $_SESSION['success'] = 'Pesanan berhasil dibuat! No. Pesanan: #' . $id_transaksi;
                header('Location: ../user/pesanan.php');
                exit;
            } else {
                $message = 'Error: Gagal menyimpan detail transaksi';
                $message_type = 'danger';
            }
        } else {
            $message = 'Error: ' . $insert_stmt->error;
            $message_type = 'danger';
        }
    } else {
        $message = implode(', ', $errors);
        $message_type = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - MobileNest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .checkout-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .summary-item:last-child {
            border-bottom: none;
        }
        
        .total-section {
            background: #007bff;
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include '../header.php'; ?>

    <div class="container py-5">
        <h1 class="mb-4">💳 Checkout</h1>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <form method="POST">
                    <input type="hidden" name="action" value="checkout">
                    
                    <!-- Data Penerima -->
                    <div class="checkout-section">
                        <h5 class="mb-3">📋 Data Penerima</h5>
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user_data['nama_lengkap']); ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($user_data['email']); ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. Telepon</label>
                            <input type="tel" class="form-control" value="<?php echo htmlspecialchars($user_data['no_telepon']); ?>" disabled>
                        </div>
                    </div>
                    
                    <!-- Alamat Pengiriman -->
                    <div class="checkout-section">
                        <h5 class="mb-3">🏠 Alamat Pengiriman</h5>
                        <div class="mb-3">
                            <label class="form-label">Alamat *</label>
                            <textarea name="alamat_pengiriman" class="form-control" rows="4" required><?php echo htmlspecialchars($user_data['alamat'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Metode Pembayaran -->
                    <div class="checkout-section">
                        <h5 class="mb-3">💰 Metode Pembayaran</h5>
                        <div class="mb-3">
                            <label class="form-label">Pilih Metode Pembayaran *</label>
                            <select name="metode_pembayaran" class="form-select" required>
                                <option value="">-- Pilih --</option>
                                <option value="Transfer Bank">Transfer Bank</option>
                                <option value="E-Wallet">E-Wallet</option>
                                <option value="COD">COD (Bayar di Tempat)</option>
                                <option value="Kartu Kredit">Kartu Kredit</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Ringkasan Pesanan -->
                    <div class="checkout-section">
                        <h5 class="mb-3">📦 Ringkasan Pesanan</h5>
                        <div>
                            <?php foreach ($cart_items as $item): ?>
                                <div class="summary-item">
                                    <div>
                                        <strong><?php echo htmlspecialchars($item['nama_produk']); ?></strong><br>
                                        <small class="text-muted"><?php echo $item['kuantitas']; ?>x Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></small>
                                    </div>
                                    <div>
                                        Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg w-100">✅ Konfirmasi Pesanan</button>
                </form>
            </div>
            
            <!-- Ringkasan Total -->
            <div class="col-md-4">
                <div class="total-section" style="position: sticky; top: 20px;">
                    <h5 class="mb-3">Total Pesanan</h5>
                    <div style="font-size: 28px; font-weight: bold; margin-bottom: 1rem;">
                        Rp <?php echo number_format($total_harga, 0, ',', '.'); ?>
                    </div>
                    <small>
                        <?php echo count($cart_items); ?> item(s)
                    </small>
                </div>
                
                <div class="alert alert-info mt-3">
                    <strong>Informasi Penting:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Periksa kembali alamat pengiriman</li>
                        <li>Pastikan data penerima sudah benar</li>
                        <li>Pesanan akan diproses setelah pembayaran dikonfirmasi</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>