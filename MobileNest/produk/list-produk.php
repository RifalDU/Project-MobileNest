<?php
session_start();
require_once '../config.php';

$page_title = "Daftar Produk";
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="container">
        <!-- Header -->
        <h1 class="mb-2">Daftar Produk</h1>
        <p class="text-muted mb-4">Temukan smartphone pilihan terbaik di MobileNest</p>
        
        <div class="row">
            <!-- Filter Sidebar -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="card-title fw-bold mb-3">Filter</h6>
                        
                        <!-- Cari Produk -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Cari Produk</label>
                            <input type="text" class="form-control" placeholder="Ketik nama produk..." id="search_produk">
                        </div>
                        
                        <!-- Filter Merek -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Merek</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="Samsung" id="merek_samsung" disabled>
                                <label class="form-check-label" for="merek_samsung">Samsung</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="Apple" id="merek_apple" disabled>
                                <label class="form-check-label" for="merek_apple">Apple</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="Xiaomi" id="merek_xiaomi" disabled>
                                <label class="form-check-label" for="merek_xiaomi">Xiaomi</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="Oppo" id="merek_oppo" disabled>
                                <label class="form-check-label" for="merek_oppo">Oppo</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="Realme" id="merek_realme" disabled>
                                <label class="form-check-label" for="merek_realme">Realme</label>
                            </div>
                        </div>
                        
                        <!-- Filter Harga -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Harga</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="harga_1" disabled>
                                <label class="form-check-label" for="harga_1">Rp 1 - 3 Juta</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="2" id="harga_2" disabled>
                                <label class="form-check-label" for="harga_2">Rp 3 - 7 Juta</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="3" id="harga_3" disabled>
                                <label class="form-check-label" for="harga_3">Rp 7 - 15 Juta</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="4" id="harga_4" disabled>
                                <label class="form-check-label" for="harga_4">Rp 15+ Juta</label>
                            </div>
                        </div>
                        
                        <!-- Tombol Filter - DISABLED FOR NOW -->
                        <button class="btn btn-primary w-100 mb-2" disabled>
                            <i class="bi bi-funnel"></i> Terapkan Filter
                        </button>
                        <button class="btn btn-outline-secondary w-100" disabled>
                            <i class="bi bi-arrow-clockwise"></i> Reset Filter
                        </button>
                        <p class="text-muted small mt-2">⚙️ Filter sedang dalam perbaikan</p>
                    </div>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div class="col-md-9">
                <!-- Products Count & Sort -->
                <div class="row mb-4 align-items-center">
                    <div class="col-md-6">
                        <?php
                        $sql = "SELECT COUNT(*) as total FROM produk WHERE status_produk = 'Tersedia'";
                        $result = mysqli_query($conn, $sql);
                        $row = mysqli_fetch_assoc($result);
                        ?>
                        <p class="text-muted mb-0">Menampilkan <strong><?php echo $row['total']; ?></strong> produk</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <select class="form-select form-select-sm w-auto" style="display: inline-block;">
                            <option>Terbaru</option>
                            <option>Harga Terendah</option>
                            <option>Harga Tertinggi</option>
                            <option>Paling Laris</option>
                        </select>
                    </div>
                </div>
                
                <!-- Products Grid -->
                <div class="row g-4">
                    <?php
                    // Show ALL products, not just first 12
                    $sql = "SELECT * FROM produk WHERE status_produk = 'Tersedia' ORDER BY id_produk DESC";
                    $result = mysqli_query($conn, $sql);
                    
                    if (mysqli_num_rows($result) > 0) {
                        while ($produk = mysqli_fetch_assoc($result)) {
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm h-100 transition" style="cursor: pointer;">
                            <!-- Product Image -->
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px; position: relative;">
                                <i class="bi bi-phone" style="font-size: 3rem; color: #ccc;"></i>
                                <span class="badge bg-danger position-absolute top-0 end-0 m-2">-15%</span>
                            </div>
                            
                            <!-- Product Info -->
                            <div class="card-body">
                                <h6 class="card-title mb-2"><?php echo htmlspecialchars($produk['nama_produk']); ?></h6>
                                <p class="text-muted small mb-2"><?php echo htmlspecialchars($produk['merek']); ?></p>
                                
                                <!-- Rating -->
                                <div class="mb-2">
                                    <span class="text-warning">
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-half"></i>
                                    </span>
                                    <span class="text-muted small">(152)</span>
                                </div>
                                
                                <!-- Price -->
                                <h5 class="text-primary mb-3">Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></h5>
                                
                                <!-- Buttons -->
                                <div class="d-grid gap-2">
                                    <a href="detail-produk.php?id=<?php echo $produk['id_produk']; ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-search"></i> Lihat Detail
                                    </a>
                                    <button class="btn btn-primary btn-sm" onclick="addToCart(<?php echo $produk['id_produk']; ?>, 1)">
                                        <i class="bi bi-cart-plus"></i> Tambah Keranjang
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                        }
                    } else {
                        echo '<p class="text-center text-muted">Tidak ada produk tersedia</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/api-handler.js"></script>
<script src="../assets/js/cart.js"></script>
<script src="../assets/js/filter.js"></script>

<script>
/**
 * Override addToCart to add notification
 * This wraps the API handler function
 */
const originalAddToCart = window.addToCart;
window.addToCart = async function(id_produk, quantity = 1) {
    console.log('Adding to cart from list:', id_produk, quantity);
    
    try {
        const result = await originalAddToCart(id_produk, quantity);
        console.log('Result:', result);
        
        if (result.success) {
            // Show success notification
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
            alert.style.zIndex = '9999';
            alert.innerHTML = `
                <i class="bi bi-check-circle"></i> Produk berhasil ditambahkan ke keranjang!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alert);
            
            // Update cart count in navbar
            updateCartCount();
            
            // Remove alert after 3 seconds
            setTimeout(() => alert.remove(), 3000);
        } else {
            console.error('Add to cart failed:', result);
            alert('Gagal menambahkan ke keranjang: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan: ' + error.message);
    }
};
</script>

<?php include '../includes/footer.php'; ?>