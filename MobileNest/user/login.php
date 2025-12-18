<?php
require_once '../config.php';

$page_title = "Login";
$css_path = "../assets/css/style.css";
$js_path = "../assets/js/script.js";
$logo_path = "../assets/images/logo.jpg";
$home_url = "../index.php";
$produk_url = "../produk/list-produk.php";
$login_url = "login.php";
$register_url = "register.php";
$keranjang_url = "../transaksi/keranjang.php";

include '../includes/header.php';

// Get error or success message from session
$error_message = $_SESSION['error'] ?? '';
$success_message = $_SESSION['success'] ?? '';

// Clear messages from session after displaying
if (isset($_SESSION['error'])) {
    unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
    unset($_SESSION['success']);
}
?>

    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-12 col-sm-10 col-md-8 col-lg-5">
                <div class="card shadow border-0 rounded-lg">
                    <div class="card-body p-4 p-sm-5">
                        <!-- Logo & Title -->
                        <div class="text-center mb-4">
                            <img src="<?php echo $logo_path; ?>" alt="MobileNest Logo" height="50" class="mb-3">
                            <h3 class="fw-bold text-primary">MobileNest</h3>
                            <p class="text-muted">Masuk ke akun Anda</p>
                        </div>

                        <!-- Success Alert -->
                        <?php if (!empty($success_message)): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Error Alert -->
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Form -->
                        <form action="proses-login.php" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label fw-bold">Email atau Username</label>
                                <input type="text" class="form-control form-control-lg" id="email" name="username" placeholder="Masukkan email atau username" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-bold">Password</label>
                                <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="Masukkan password" required>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Ingat saya</label>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </button>

                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-google"></i> Login dengan Google
                                </button>
                            </div>
                        </form>

                        <!-- Divider -->
                        <div class="my-4 text-center">
                            <small class="text-muted">atau</small>
                        </div>

                        <!-- Register Link -->
                        <div class="text-center">
                            <p class="mb-0">Belum punya akun? 
                                <a href="register.php" class="text-decoration-none fw-bold">Daftar di sini</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include '../includes/footer.php'; ?>
