<?php
// CRITICAL FIX: Include config to access is_logged_in() function and session variables
require_once dirname(__DIR__) . '/config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'MobileNest'; ?> - E-Commerce Smartphone</title>
    
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo isset($css_path) ? $css_path : 'assets/css/style.css'; ?>">
</head>
<body>
    
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <!-- Logo & Brand -->
            <a class="navbar-brand fw-bold d-flex align-items-center" href="<?php echo SITE_URL; ?>/index.php">
                <img src="<?php echo SITE_URL; ?>/assets/images/logo.jpg" alt="MobileNest Logo" height="40" class="me-2">
                <span class="text-primary">MobileNest</span>
            </a>
            
            <!-- Toggle Button Mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Menu Navigation -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="<?php echo SITE_URL; ?>/index.php">
                            <i class="bi bi-house"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/produk/list-produk.php">
                            <i class="bi bi-phone"></i> Produk
                        </a>
                    </li>
                    
                    <!-- Menu Jika User Sudah Login -->
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> 
                                <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li>
                                    <a class="dropdown-item" href="<?php echo SITE_URL; ?>/user/profil.php">
                                        <i class="bi bi-person"></i> Profil Saya
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo SITE_URL; ?>/user/pesanan.php">
                                        <i class="bi bi-box-seam"></i> Pesanan Saya
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="<?php echo SITE_URL; ?>/user/logout.php">
                                        <i class="bi bi-box-arrow-right"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/transaksi/keranjang.php">
                                <i class="bi bi-cart"></i> Keranjang
                                <span id="cart-count-badge" class="badge bg-danger" style="display: none;">0</span>
                            </a>
                        </li>
                    <!-- Menu Jika User Belum Login -->
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/user/login.php">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/user/register.php">
                                <i class="bi bi-person-plus"></i> Register
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/transaksi/keranjang.php">
                                <i class="bi bi-cart"></i> Keranjang
                                <span id="cart-count-badge" class="badge bg-danger" style="display: none;">0</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>