<?php
require_once '../config.php';

$page_title = "Produk";
$css_path = "../assets/css/style.css";
$js_path = "../assets/js/script.js";
$logo_path = "../assets/images/logo.jpg";
$home_url = "../index.php";
$produk_url = "list-produk.php";
$login_url = "../user/login.php";
$register_url = "../user/register.php";
$keranjang_url = "../transaksi/keranjang.php";

include '../includes/header.php';

// Get filter parameters from URL
$selected_brands = isset($_GET['brands']) ? $_GET['brands'] : array();
if (is_string($selected_brands)) {
    $selected_brands = array($selected_brands);
}

$selected_price = isset($_GET['price']) ? $_GET['price'] : '';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'terbaru';

// Build WHERE clause
$where_conditions = array("status_produk = 'Tersedia'");

// Search filter
if (!empty($search_query)) {
    $search = mysqli_real_escape_string($conn, $search_query);
    $where_conditions[] = "(nama_produk LIKE '%$search%' OR merek LIKE '%$search%')";
}

// Brand filter
if (!empty($selected_brands)) {
    $brands_list = array();
    foreach ($selected_brands as $brand) {
        $brand = mysqli_real_escape_string($conn, $brand);
        $brands_list[] = "merek = '$brand'";
    }
    if (!empty($brands_list)) {
        $where_conditions[] = '(' . implode(' OR ', $brands_list) . ')';
    }
}

// Price filter
if (!empty($selected_price)) {
    if ($selected_price === '0-3000000') {
        $where_conditions[] = "harga BETWEEN 0 AND 3000000";
    } elseif ($selected_price === '3000000-7000000') {
        $where_conditions[] = "harga BETWEEN 3000000 AND 7000000";
    } elseif ($selected_price === '7000000-15000000') {
        $where_conditions[] = "harga BETWEEN 7000000 AND 15000000";
    } elseif ($selected_price === '15000000') {
        $where_conditions[] = "harga >= 15000000";
    }
}

$where_sql = implode(' AND ', $where_conditions);

// Determine sort order
$order_sql = "tanggal_ditambahkan DESC";
if ($sort_by === 'termurah') {
    $order_sql = "harga ASC";
} elseif ($sort_by === 'termahal') {
    $order_sql = "harga DESC";
} elseif ($sort_by === 'populer') {
    $order_sql = "id_produk DESC";
}

// Execute query
$sql = "SELECT * FROM produk WHERE $where_sql ORDER BY $order_sql";
$result = mysqli_query($conn, $sql);
$total_products = mysqli_num_rows($result);
?>

    <!-- PAGE TITLE -->
    <div class="bg-light py-4 mb-5">
        <div class="container">
            <h1 class="display-5 fw-bold mb-2">Daftar Produk</h1>
            <p class="text-muted">Temukan smartphone pilihan terbaik di MobileNest</p>
        </div>
    </div>

    <div class="container mb-5">
        <div class="row">
            <!-- Sidebar Filter -->
            <div class="col-lg-3 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white fw-bold">
                        <i class="bi bi-funnel"></i> Filter
                    </div>
                    <div class="card-body">
                        <form method="GET" action="list-produk.php">
                            <!-- Search -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-2">Cari Produk</h6>
                                <input type="text" name="search" class="form-control" placeholder="Ketik nama produk..." value="<?php echo htmlspecialchars($search_query); ?>">
                            </div>

                            <!-- Filter by Brand -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-2">Merek</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="brands[]" value="Samsung" id="samsung" <?php echo in_array('Samsung', $selected_brands) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="samsung">Samsung</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="brands[]" value="Apple" id="apple" <?php echo in_array('Apple', $selected_brands) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="apple">Apple</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="brands[]" value="Xiaomi" id="xiaomi" <?php echo in_array('Xiaomi', $selected_brands) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="xiaomi">Xiaomi</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="brands[]" value="Oppo" id="oppo" <?php echo in_array('Oppo', $selected_brands) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="oppo">Oppo</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="brands[]" value="Realme" id="realme" <?php echo in_array('Realme', $selected_brands) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="realme">Realme</label>
                                </div>
                            </div>
                            
                            <!-- Filter by Price -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-2">Harga</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="price" value="0-3000000" id="price1" <?php echo $selected_price === '0-3000000' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="price1">Rp 1 - 3 Juta</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="price" value="3000000-7000000" id="price2" <?php echo $selected_price === '3000000-7000000' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="price2">Rp 3 - 7 Juta</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="price" value="7000000-15000000" id="price3" <?php echo $selected_price === '7000000-15000000' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="price3">Rp 7 - 15 Juta</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="price" value="15000000" id="price4" <?php echo $selected_price === '15000000' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="price4">Rp 15+ Juta</label>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-search"></i> Terapkan Filter
                                </button>
                                <a href="list-produk.php" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-arrow-counterclockwise"></i> Reset Filter
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Product List -->
            <div class="col-lg-9">
                <!-- Sorting & Info -->
                <div class="mb-4 d-flex justify-content-between align-items-center">
                    <p class="mb-0 text-muted">Menampilkan <strong><?php echo $total_products; ?></strong> produk</p>
                    <form method="GET" action="list-produk.php" class="d-flex gap-2">
                        <!-- Preserve other filters -->
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_query); ?>">
                        <input type="hidden" name="price" value="<?php echo htmlspecialchars($selected_price); ?>">
                        <?php foreach ($selected_brands as $brand): ?>
                            <input type="hidden" name="brands[]" value="<?php echo htmlspecialchars($brand); ?>">
                        <?php endforeach; ?>
                        
                        <select class="form-select form-select-sm w-auto" name="sort" onchange="this.form.submit();">
                            <option value="terbaru" <?php echo $sort_by === 'terbaru' ? 'selected' : ''; ?>>Terbaru</option>
                            <option value="termurah" <?php echo $sort_by === 'termurah' ? 'selected' : ''; ?>>Harga Terendah</option>
                            <option value="termahal" <?php echo $sort_by === 'termahal' ? 'selected' : ''; ?>>Harga Tertinggi</option>
                            <option value="populer" <?php echo $sort_by === 'populer' ? 'selected' : ''; ?>>Paling Populer</option>
                        </select>
                    </form>
                </div>

                <!-- Product Grid -->
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-5">
                    <?php
                    if ($total_products > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            ?>
                            <div class="col">
                                <div class="card h-100 shadow-sm border-0 transition-card">
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center position-relative" style="height: 200px;">
                                        <i class="bi bi-phone text-muted" style="font-size: 3rem;"></i>
                                        <span class="badge bg-danger position-absolute top-0 end-0 m-2">-15%</span>
                                    </div>
                                    
                                    <div class="card-body">
                                        <h5 class="card-title fw-bold"><?php echo htmlspecialchars($row['nama_produk']); ?></h5>
                                        <p class="text-muted small mb-2"><?php echo htmlspecialchars($row['merek']); ?></p>
                                        
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
                                        
                                        <h6 class="text-primary fw-bold mb-3">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></h6>
                                        
                                        <div class="d-grid gap-2">
                                            <a href="detail-produk.php?id=<?php echo $row['id_produk']; ?>" class="btn btn-primary btn-sm">
                                                <i class="bi bi-cart-plus"></i> Lihat Detail
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<div class="col-12"><p class="text-center text-muted py-5">Tidak ada produk yang sesuai dengan filter.</p></div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

<?php include '../includes/footer.php'; ?>
