<?php
require_once '../config.php';
require_login();

$page_title = "Riwayat Pesanan";
include '../includes/header.php';
?>

<div class="container py-5">
    <h1 class="mb-4">📦 Riwayat Pesanan</h1>
    
    <div id="transactions-history">
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
</div>

<script src="../js/api-handler.js"></script>
<script src="../js/checkout.js"></script>

<?php include '../includes/footer.php'; ?>
