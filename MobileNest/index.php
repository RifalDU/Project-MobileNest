<?php
// Include database config
require_once 'config.php';

// Set page variables
$page_title = "Beranda";
$css_path = "assets/css/style.css";
$js_path = "assets/js/script.js";
$logo_path = "assets/images/logo.jpg";
$home_url = "index.php";
$produk_url = "produk/list-produk.php";
$login_url = "user/login.php";
$register_url = "user/register.php";
$keranjang_url = "transaksi/keranjang.php";

// Include header
include 'includes/header.php';
?>

    <!-- HERO SECTION -->
    <section class="hero bg-primary text-white py-5">
        <div class="container">
            <div class="row align-items-center gy-4">
                <div class="col-lg-7">
                    <h1 class="display-4 fw-bold mb-3">Temukan Smartphone Terbaru di MobileNest</h1>
                    <p class="lead mb-4">Cari, bandingkan, dan beli smartphone impianmu dengan harga terbaik dan kualitas terjamin.</p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="produk/list-produk.php" class="btn btn-warning btn-lg px-4">
                            <i class="bi bi-phone"></i> Lihat Produk
                        </a>
                        <a href="user/register.php" class="btn btn-outline-light btn-lg px-4">
                            <i class="bi bi-person-plus"></i> Daftar Sekarang
                        </a>
                    </div>
                </div>
                <div class="col-lg-5 text-center">
                    <img src="assets/images/logo.jpg" alt="MobileNest" class="img-fluid" style="max-width: 300px;">
                </div>
            </div>
        </div>
    </section>

    <!-- KATEGORI SMARTPHONE -->
    <section class="kategori py-5 bg-light">
        <div class="container">
            <h2 class="text-center fw-bold mb-5">Kategori Smartphone</h2>
            <div class="row g-4">
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="card text-center border-0 shadow-sm h-100 transition-card">
                        <div class="card-body py-4">
                            <i class="bi bi-phone text-danger display-5 mb-2"></i>
                            <h6 class="fw-bold">Samsung</h6>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="card text-center border-0 shadow-sm h-100 transition-card">
                        <div class="card-body py-4">
                            <i class="bi bi-apple text-dark display-5 mb-2"></i>
                            <h6 class="fw-bold">Apple</h6>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="card text-center border-0 shadow-sm h-100 transition-card">
                        <div class="card-body py-4">
                            <i class="bi bi-phone text-warning display-5 mb-2"></i>
                            <h6 class="fw-bold">Xiaomi</h6>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="card text-center border-0 shadow-sm h-100 transition-card">
                        <div class="card-body py-4">
                            <i class="bi bi-phone text-success display-5 mb-2"></i>
                            <h6 class="fw-bold">Oppo</h6>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="card text-center border-0 shadow-sm h-100 transition-card">
                        <div class="card-body py-4">
                            <i class="bi bi-phone text-info display-5 mb-2"></i>
                            <h6 class="fw-bold">Vivo</h6>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="card text-center border-0 shadow-sm h-100 transition-card">
                        <div class="card-body py-4">
                            <i class="bi bi-three-dots text-secondary display-5 mb-2"></i>
                            <h6 class="fw-bold">Lainnya</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- PRODUK UNGGULAN -->
    <section class="produk py-5">
        <div class="container">
            <h2 class="text-center fw-bold mb-5">Produk Unggulan</h2>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
                <?php
                // Query produk dari database
                $sql = "SELECT * FROM produk WHERE status_produk = 'Tersedia' ORDER BY tanggal_ditambahkan DESC LIMIT 8";
                $result = mysqli_query($conn, $sql);
                
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm border-0 transition-card">
                                <!-- Product Image Placeholder -->
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                                    <i class="bi bi-phone text-muted" style="font-size: 3rem;"></i>
                                </div>
                                
                                <div class="card-body">
                                    <h5 class="card-title fw-bold"><?php echo htmlspecialchars($row['nama_produk']); ?></h5>
                                    <p class="text-muted small mb-2"><?php echo htmlspecialchars($row['merek']); ?></p>
                                    
                                    <!-- Rating -->
                                    <div class="mb-2">
                                        <span class="text-warning">
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-half"></i>
                                        </span>
                                    </div>
                                    
                                    <!-- Price -->
                                    <h6 class="text-primary fw-bold mb-3">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></h6>
                                    
                                    <!-- Stock & Button -->
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-success">Stok: <?php echo $row['stok']; ?></span>
                                        <a href="produk/detail-produk.php?id=<?php echo $row['id_produk']; ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> Lihat
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<div class="col-12"><p class="text-center text-muted">Belum ada produk tersedia.</p></div>';
                }
                ?>
            </div>
            
            <!-- Lihat Semua Produk -->
            <div class="text-center mt-5">
                <a href="produk/list-produk.php" class="btn btn-primary btn-lg">
                    <i class="bi bi-arrow-right"></i> Lihat Semua Produk
                </a>
            </div>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>
