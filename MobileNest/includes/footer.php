    <!-- FOOTER -->
    <footer class="bg-dark text-white py-5 mt-5">
        <div class="container">
            <div class="row mb-4">
                <!-- Brand & Deskripsi -->
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="d-flex align-items-center mb-3">
                        <img src="<?php echo isset($logo_path) ? $logo_path : '../assets/images/logo.jpg'; ?>" alt="MobileNest" height="35" class="me-2">
                        <h5 class="mb-0">MobileNest</h5>
                    </div>
                    <p class="text-muted mb-0">E-Commerce Smartphone terpercaya dengan pilihan terbaik dan harga kompetitif.</p>
                </div>
                
                <!-- Navigasi -->
                <div class="col-md-4 mb-3 mb-md-0">
                    <h6 class="fw-bold mb-3">Navigasi</h6>
                    <ul class="list-unstyled text-muted">
                        <li><a href="<?php echo isset($home_url) ? $home_url : '../index.php'; ?>" class="text-decoration-none text-muted">Home</a></li>
                        <li><a href="<?php echo isset($produk_url) ? $produk_url : '../produk/list-produk.php'; ?>" class="text-decoration-none text-muted">Produk</a></li>
                        <li><a href="<?php echo isset($login_url) ? $login_url : '../user/login.php'; ?>" class="text-decoration-none text-muted">Login</a></li>
                    </ul>
                </div>
                
                <!-- Kontak -->
                <div class="col-md-4">
                    <h6 class="fw-bold mb-3">Hubungi Kami</h6>
                    <p class="text-muted mb-1">
                        <i class="bi bi-telephone"></i> +62 821 1234 5678
                    </p>
                    <p class="text-muted mb-1">
                        <i class="bi bi-envelope"></i> support@mobilenest.com
                    </p>
                    <p class="text-muted">
                        <i class="bi bi-geo-alt"></i> Jakarta, Indonesia
                    </p>
                </div>
            </div>
            
            <hr class="bg-secondary">
            
            <!-- Copyright -->
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-muted mb-0">&copy; 2025 MobileNest. All Rights Reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-muted me-3"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-muted me-3"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="text-muted"><i class="bi bi-twitter"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo isset($js_path) ? $js_path : '../assets/js/script.js'; ?>"></script>
    
    <!-- Cart JS API Handler (MUST BE BEFORE cart.js) -->
    <script src="../js/api-handler.js"></script>
    <script src="../js/cart.js"></script>
    
    <!-- Initialize Cart Count on Page Load -->
    <script>
        console.log('Footer scripts loaded');
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, calling updateCartCount');
            if (typeof updateCartCount === 'function') {
                updateCartCount();
            } else {
                console.error('updateCartCount function not found!');
            }
        });
    </script>
</body>
</html>
