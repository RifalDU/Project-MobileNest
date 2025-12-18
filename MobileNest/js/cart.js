/**
 * Shopping Cart Functions
 * AJAX integration untuk keranjang belanja
 */

/**
 * Add product to cart
 */
async function addToCart(productId, quantity = 1) {
    try {
        UIHelper.showLoading(true);
        
        const response = await APIHandler.Cart.add(productId, quantity);
        
        UIHelper.showLoading(false);
        UIHelper.showSuccess('✅ Produk ditambahkan ke keranjang!');
        
        // Update cart count in navbar
        updateCartCount();
        
        return response.data;
    } catch (error) {
        UIHelper.showLoading(false);
        UIHelper.showError('❌ Gagal menambahkan ke keranjang: ' + error.message);
        return null;
    }
}

/**
 * Remove product from cart
 */
async function removeFromCart(productId) {
    try {
        const response = await APIHandler.Cart.remove(productId);
        UIHelper.showSuccess('✅ Produk dihapus dari keranjang');
        
        // Reload cart display
        loadCartItems();
        updateCartCount();
        
        return response.data;
    } catch (error) {
        UIHelper.showError('❌ Gagal menghapus dari keranjang');
        return null;
    }
}

/**
 * Update cart item quantity
 */
async function updateCartQuantity(productId, quantity) {
    try {
        if (quantity < 1) {
            await removeFromCart(productId);
            return;
        }
        
        const response = await APIHandler.Cart.update(productId, quantity);
        UIHelper.showSuccess('✅ Kuantitas diperbarui');
        
        // Reload cart display
        loadCartItems();
        updateCartCount();
        
        return response.data;
    } catch (error) {
        UIHelper.showError('❌ Gagal memperbarui kuantitas');
        return null;
    }
}

/**
 * Load and display cart items
 */
async function loadCartItems() {
    try {
        const cartContainer = document.getElementById('cart-items-container');
        const cartSummary = document.getElementById('cart-summary');
        
        if (!cartContainer) return; // Cart page not loaded
        
        const response = await APIHandler.Cart.get();
        const items = response.data.items;
        const summary = response.data.summary;
        
        // Clear container
        cartContainer.innerHTML = '';
        
        if (items.length === 0) {
            cartContainer.innerHTML = `
                <div class="alert alert-info text-center">
                    <i class="bi bi-cart-x"></i> Keranjang Anda kosong<br>
                    <a href="./produk/list-produk.php" class="btn btn-primary btn-sm mt-2">Belanja Sekarang</a>
                </div>
            `;
            
            if (cartSummary) {
                cartSummary.innerHTML = `
                    <h5 class="mb-3">📋 Ringkasan Belanja</h5>
                    <div class="text-center text-muted">
                        Keranjang kosong
                    </div>
                `;
            }
            return;
        }
        
        // Display cart items
        let html = '';
        items.forEach(item => {
            html += `
                <div class="cart-item card mb-3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <h6 class="mb-1">${item.nama}</h6>
                                <p class="text-muted mb-0">${UIHelper.formatRupiah(item.harga)}</p>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group input-group-sm">
                                    <button class="btn btn-outline-secondary" 
                                            onclick="updateCartQuantity(${item.id}, ${item.jumlah - 1})" 
                                            ${item.jumlah <= 1 ? 'disabled' : ''}>
                                        −
                                    </button>
                                    <input type="text" class="form-control text-center" 
                                           value="${item.jumlah}" disabled>
                                    <button class="btn btn-outline-secondary" 
                                            onclick="updateCartQuantity(${item.id}, ${item.jumlah + 1})">
                                        +
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3 text-end">
                                <strong>${UIHelper.formatRupiah(item.subtotal)}</strong>
                            </div>
                            <div class="col-md-2 text-end">
                                <button class="btn btn-sm btn-danger" 
                                        onclick="removeFromCart(${item.id})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        cartContainer.innerHTML = html;
        
        // Update summary
        if (cartSummary) {
            cartSummary.innerHTML = `
                <h5 class="mb-3">📋 Ringkasan Belanja</h5>
                <div class="row mb-2">
                    <div class="col-6">Total Item:</div>
                    <div class="col-6 text-end"><strong>${summary.total_items} item</strong></div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">Subtotal:</div>
                    <div class="col-6 text-end"><strong>${UIHelper.formatRupiah(summary.total_price)}</strong></div>
                </div>
                <hr>
                <div class="row mb-3">
                    <div class="col-6">Total:</div>
                    <div class="col-6 text-end"><h5 class="mb-0">${UIHelper.formatRupiah(summary.total_price)}</h5></div>
                </div>
                <a href="./checkout.php" class="btn btn-primary btn-lg w-100">
                    <i class="bi bi-credit-card"></i> Lanjut Checkout
                </a>
            `;
        }
    } catch (error) {
        UIHelper.showError('❌ Gagal memuat keranjang: ' + error.message);
    }
}

/**
 * Update cart count in navbar
 */
async function updateCartCount() {
    try {
        const response = await APIHandler.Cart.getCount();
        const cartBadge = document.getElementById('cart-count-badge');
        
        if (cartBadge) {
            const count = response.data.items_count;
            cartBadge.textContent = count;
            cartBadge.style.display = count > 0 ? 'inline-block' : 'none';
        }
    } catch (error) {
        console.error('Failed to update cart count:', error);
    }
}

/**
 * Clear entire cart
 */
async function clearCart() {
    if (!confirm('Yakin ingin menghapus semua item dari keranjang?')) {
        return;
    }
    
    try {
        await APIHandler.Cart.clear();
        UIHelper.showSuccess('✅ Keranjang dibersihkan');
        loadCartItems();
        updateCartCount();
    } catch (error) {
        UIHelper.showError('❌ Gagal membersihkan keranjang');
    }
}

/**
 * Initialize cart page on load
 */
document.addEventListener('DOMContentLoaded', function() {
    // Load cart items if on cart page
    if (document.getElementById('cart-items-container')) {
        loadCartItems();
    }
    
    // Update cart count in navbar
    updateCartCount();
});
