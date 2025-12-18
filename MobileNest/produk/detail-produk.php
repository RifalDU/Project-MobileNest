<?php
require_once '../config.php';

$id_produk = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_produk === 0) {
    header('Location: list-produk.php');
    exit;
}

$sql = "SELECT * FROM produk WHERE id_produk = $id_produk";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) === 0) {
    header('Location: list-produk.php');
    exit;
}

$product = mysqli_fetch_assoc($result);

$page_title = $product['nama_produk'];
$css_path = "../assets/css/style.css";
$js_path = "../assets/js/script.js";
$logo_path = "../assets/images/logo.jpg";
$home_url = "../index.php";
$produk_url = "list-produk.php";
$login_url = "../user/login.php";
$register_url = "../user/register.php";
$keranjang_url = "../transaksi/keranjang.php";

include '../includes/header.php';
?>

    <!-- BREADCRUMB -->
    <div class="bg-light py-3 mb-4">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="list-produk.php">Produk</a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['nama_produk']); ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container mb-5">
        <div class="row">
            <!-- Product Image -->
            <div class="col-lg-5 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center justify-content-center bg-light" style="height: 400px;">
                        <i class="bi bi-phone text-muted" style="font-size: 8rem;"></i>
                    </div>
                </div>
            </div>

            <!-- Product Details -->
            <div class="col-lg-7">
                <span class="badge bg-info mb-2"><?php echo htmlspecialchars($product['kategori'] ?? 'Lainnya'); ?></span>
                <h1 class="display-6 fw-bold mb-2"><?php echo htmlspecialchars($product['nama_produk']); ?></h1>
                <p class="text-muted mb-3"><strong>Merek:</strong> <?php echo htmlspecialchars($product['merek']); ?></p>

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

                <h3 class="text-primary fw-bold mb-4">Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></h3>

                <div class="mb-4">
                    <?php if ($product['stok'] > 0): ?>
                        <p class="text-success fw-bold"><i class="bi bi-check-circle"></i> Stok Tersedia (<?php echo $product['stok']; ?> unit)</p>
                    <?php else: ?>
                        <p class="text-danger fw-bold"><i class="bi bi-x-circle"></i> Stok Habis</p>
                    <?php endif; ?>
                </div>

                <div class="d-grid gap-2">
                    <?php if ($product['stok'] > 0): ?>
                        <?php if (is_logged_in()): ?>
                            <button class="btn btn-primary btn-lg" onclick="addToCart(<?php echo $product['id_produk']; ?>)">
                                <i class="bi bi-cart-plus"></i> Tambah ke Keranjang
                            </button>
                        <?php else: ?>
                            <a href="../user/login.php" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right"></i> Login untuk Membeli
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <button class="btn btn-danger btn-lg" disabled>
                            <i class="bi bi-x-circle"></i> Stok Habis
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Spesifikasi -->
        <div class="row mt-5">
            <div class="col-lg-8">
                <h3 class="fw-bold mb-3">Deskripsi Produk</h3>
                <p class="text-muted"><?php echo htmlspecialchars($product['deskripsi'] ?? 'Tidak ada deskripsi'); ?></p>

                <h3 class="fw-bold mb-3 mt-4">Spesifikasi</h3>
                <div class="table-responsive">
                    <table class="table table-borderless">
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
    </div>

<?php include '../includes/footer.php'; ?>

<script>
function addToCart(id_produk) {
    alert('Fitur tambah keranjang akan segera tersedia!');
}
</script>
