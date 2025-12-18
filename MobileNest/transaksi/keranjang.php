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
$cart_items = [];
$total_harga = 0;
$message = '';

// Ambil data keranjang dari session atau database
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    // Jika ada cart di session
    foreach ($_SESSION['cart'] as $item) {
        $id_produk = $item['id_produk'];
        $kuantitas = $item['kuantitas'];
        
        // Ambil data produk dari database
        $sql = "SELECT id_produk, nama_produk, harga, stok FROM produk WHERE id_produk = ?";
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
                'stok' => $produk['stok'],
                'kuantitas' => $kuantitas,
                'subtotal' => $subtotal
            ];
            $total_harga += $subtotal;
        }
    }
}

// Process TAMBAH KUANTITAS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'tambah') {
    $id_produk = intval($_POST['id_produk']);
    
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id_produk'] == $id_produk) {
                $item['kuantitas']++;
                break;
            }
        }
    }
    header('Location: keranjang.php');
    exit;
}

// Process KURANGI KUANTITAS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'kurangi') {
    $id_produk = intval($_POST['id_produk']);
    
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id_produk'] == $id_produk) {
                if ($item['kuantitas'] > 1) {
                    $item['kuantitas']--;
                }
                break;
            }
        }
    }
    header('Location: keranjang.php');
    exit;
}

// Process HAPUS ITEM
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'hapus') {
    $id_produk = intval($_POST['id_produk']);
    
    if (isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($id_produk) {
            return $item['id_produk'] != $id_produk;
        });
        $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index array
    }
    header('Location: keranjang.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - MobileNest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .cart-item {
            border-bottom: 1px solid #eee;
            padding: 1rem 0;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .quantity-control button {
            width: 36px;
            height: 36px;
            padding: 0;
            border: 1px solid #ddd;
        }
        
        .quantity-control input {
            width: 60px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .summary-card {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include '../header.php'; ?>

    <div class="container py-5">
        <h1 class="mb-4">🛒 Keranjang Belanja</h1>

        <div class="row">
            <div class="col-md-8">
                <?php if (!empty($cart_items)): ?>
                    <div class="card">
                        <div class="card-body">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="cart-item">
                                    <div class="row align-items-center">
                                        <div class="col-md-4">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($item['nama_produk']); ?></h6>
                                            <p class="text-muted mb-0">Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></p>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="quantity-control">
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="action" value="kurangi">
                                                    <input type="hidden" name="id_produk" value="<?php echo $item['id_produk']; ?>">
                                                    <button type="submit" class="btn btn-sm">−</button>
                                                </form>
                                                <input type="text" value="<?php echo $item['kuantitas']; ?>" disabled>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="action" value="tambah">
                                                    <input type="hidden" name="id_produk" value="<?php echo $item['id_produk']; ?>">
                                                    <button type="submit" class="btn btn-sm">+</button>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="col-md-2 text-end">
                                            <strong>Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></strong>
                                        </div>
                                        <div class="col-md-2 text-end">
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Yakin hapus item ini?');">
                                                <input type="hidden" name="action" value="hapus">
                                                <input type="hidden" name="id_produk" value="<?php echo $item['id_produk']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">🗑️</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center">
                        🛒 Keranjang Anda kosong. <a href="../list-produk.php">Belanja sekarang!</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-md-4">
                <div class="summary-card">
                    <h5 class="mb-3">📋 Ringkasan Belanja</h5>
                    <div class="row mb-2">
                        <div class="col-6">Total Item:</div>
                        <div class="col-6 text-end"><strong><?php echo count($cart_items); ?> item</strong></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">Subtotal:</div>
                        <div class="col-6 text-end"><strong>Rp <?php echo number_format($total_harga, 0, ',', '.'); ?></strong></div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-6">Total:</div>
                        <div class="col-6 text-end"><h5 class="mb-0">Rp <?php echo number_format($total_harga, 0, ',', '.'); ?></h5></div>
                    </div>
                    
                    <?php if (!empty($cart_items)): ?>
                        <a href="checkout.php" class="btn btn-primary btn-lg w-100">💳 Lanjut Checkout</a>
                    <?php else: ?>
                        <button class="btn btn-primary btn-lg w-100" disabled>💳 Lanjut Checkout</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>