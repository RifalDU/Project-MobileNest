<?php
require_once '../config.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: list-produk.php');
    exit;
}

$id_produk = mysqli_real_escape_string($conn, $_GET['id']);
$sql = "SELECT * FROM produk WHERE id_produk = '$id_produk' AND status_produk = 'Tersedia'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header('Location: list-produk.php');
    exit;
}

$product = mysqli_fetch_assoc($result);
$page_title = $product['nama_produk'];

include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center bg-light d-flex align-items-center justify-content-center" style="min-height: 400px;">
                    <i class="bi bi-phone" style="font-size: 5rem; color: #ccc;"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="list-produk.php">Produk</a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['nama_produk']); ?></li>
                </ol>
            </nav>
            
            <!-- Badge -->
            <span class="badge bg-info mb-3">Flagship</span>
            
            <!-- Product Name -->
            <h1 class="mb-3"><?php echo htmlspecialchars($product['nama_produk']); ?></h1>
            
            <!-- Ratings -->
            <div class="mb-3">
                <span class="text-warning">
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-half"></i>
                </span>
                <span class="text-muted">(152 ulasan)</span>
            </div>
            
            <!-- Brand -->
            <p class="text-muted mb-3">Merek: <strong><?php echo htmlspecialchars($product['merek']); ?></strong></p>
            
            <!-- Price -->
            <div class="row mt-4">
                <div class="col-md-8">
                    <h5 class="mb-3">
                        Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?>
                    </h5>
                    
                    <!-- Stock Status -->
                    <div class="mb-3">
                        <span class="badge bg-success">
                            ✓ Stok Tersedia (<?php echo $product['stok']; ?> unit)
                        </span>
                    </div>
                    
                    <!-- Quantity Input -->
                    <div class="input-group mb-3" style="width: 150px;">
                        <span class="input-group-text">Qty</span>
                        <input type="number" class="form-control" id="quantity" 
                               min="1" max="<?php echo $product['stok']; ?>" value="1">
                    </div>
                    
                    <!-- Add to Cart Button -->
                    <button class="btn btn-primary btn-lg" 
                            type="button"
                            onclick="addToCart(<?php echo $product['id_produk']; ?>, parseInt(document.getElementById('quantity').value))">
                        <i class="bi bi-cart-plus"></i> Tambah ke Keranjang
                    </button>
                </div>
            </div>
            
            <!-- Details Card -->
            <div class="card mt-4 border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informasi Pengiriman</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><i class="bi bi-truck"></i> Gratis ongkir untuk pembelian >Rp 500.000</p>
                    <p class="mb-0"><i class="bi bi-shield-check"></i> Garansi resmi 1 tahun</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Description Section -->
    <div class="row mt-5">
        <div class="col-md-12">
            <h3 class="mb-4">Deskripsi Produk</h3>
            <p><?php echo htmlspecialchars($product['deskripsi']); ?></p>
            
            <h4 class="mt-4 mb-3">Spesifikasi</h4>
            <table class="table table-hover">
                <tbody>
                    <tr>
                        <td class="fw-bold">Nama</td>
                        <td><?php echo htmlspecialchars($product['nama_produk']); ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Merek</td>
                        <td><?php echo htmlspecialchars($product['merek']); ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Harga</td>
                        <td>Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Stok</td>
                        <td><?php echo $product['stok']; ?> unit</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="../js/api-handler.js"></script>
<script src="../js/cart.js"></script>

<?php include '../includes/footer.php'; ?>
