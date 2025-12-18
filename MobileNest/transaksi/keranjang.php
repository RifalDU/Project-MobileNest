<?php
require_once '../config.php';
require_login();

$page_title = "Keranjang Belanja";
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-8">
            <h1 class="mb-4"><i class="bi bi-cart"></i> Keranjang Belanja</h1>
            <div id="cart-items-container">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card" style="position: sticky; top: 20px;">
                <div class="card-body" id="cart-summary">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../js/api-handler.js"></script>
<script src="../js/cart.js"></script>

<?php include '../includes/footer.php'; ?>
