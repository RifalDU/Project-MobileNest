/**
 * API Handler - Utility untuk handle API requests
 */

const API_BASE = '/MobileNest/api/';

/**
 * Make API request
 */
async function apiRequest(endpoint, method = 'GET', data = null) {
    try {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            }
        };

        if (method !== 'GET' && data) {
            options.body = JSON.stringify(data);
        }

        const url = `${API_BASE}${endpoint}`;
        const response = await fetch(url, options);
        const result = await response.json();
        
        return result;
    } catch (error) {
        console.error('API Error:', error);
        return { success: false, message: error.message };
    }
}

/**
 * Get cart items
 */
async function getCartItems() {
    return await apiRequest('cart.php?action=get');
}

/**
 * Add item to cart
 */
async function addToCart(id_produk, quantity = 1) {
    return await apiRequest('cart.php?action=add', 'POST', {
        id_produk: id_produk,
        quantity: quantity
    });
}

/**
 * Remove item from cart
 */
async function removeFromCart(id_produk) {
    return await apiRequest('cart.php?action=remove', 'POST', {
        id_produk: id_produk
    });
}

/**
 * Update cart item quantity
 */
async function updateCartQuantity(id_produk, quantity) {
    return await apiRequest('cart.php?action=update', 'POST', {
        id_produk: id_produk,
        quantity: quantity
    });
}

/**
 * Get cart count
 */
async function getCartCount() {
    return await apiRequest('cart.php?action=count');
}
