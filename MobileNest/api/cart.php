<?php
/**
 * Shopping Cart API
 * Hybrid approach: Session for speed + Database for persistence
 * Sync to database on major operations
 */

require_once '../config.php';
require_once 'response.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? sanitize_input($_GET['action']) : 'get';

// Check if user is logged in
if (!is_logged_in()) {
    APIResponse::unauthorized('Please login to access cart');
}

if ($method === 'GET') {
    if ($action === 'get') {
        getCart();
    } elseif ($action === 'count') {
        getCartCount();
    } else {
        APIResponse::error('Invalid action', 400);
    }
} elseif ($method === 'POST') {
    if ($action === 'add') {
        addToCart();
    } elseif ($action === 'update') {
        updateCart();
    } elseif ($action === 'remove') {
        removeFromCart();
    } elseif ($action === 'clear') {
        clearCart();
    } else {
        APIResponse::error('Invalid action', 400);
    }
} else {
    APIResponse::error('Method not allowed', 405);
}

/**
 * Get cart items
 */
function getCart() {
    global $conn;
    
    $user_id = $_SESSION['user'];
    
    // Initialize cart in session if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $cart_items = [];
    $total_price = 0;
    $total_quantity = 0;
    
    foreach ($_SESSION['cart'] as $item) {
        $product_id = $item['id_produk'];
        $quantity = $item['jumlah'];
        
        // Get product details from database
        $stmt = $conn->prepare('SELECT id_produk, nama_produk, harga, stok FROM produk WHERE id_produk = ?');
        $stmt->bind_param('i', $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            $subtotal = $product['harga'] * $quantity;
            
            $cart_items[] = [
                'id' => $product['id_produk'],
                'nama' => $product['nama_produk'],
                'harga' => (float)$product['harga'],
                'stok' => (int)$product['stok'],
                'jumlah' => $quantity,
                'subtotal' => (float)$subtotal
            ];
            
            $total_price += $subtotal;
            $total_quantity += $quantity;
        }
        $stmt->close();
    }
    
    APIResponse::success([
        'items' => $cart_items,
        'summary' => [
            'total_items' => $total_quantity,
            'total_price' => (float)$total_price,
            'count' => count($cart_items)
        ]
    ], 'Cart retrieved successfully');
}

/**
 * Get cart item count only
 */
function getCartCount() {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $count = count($_SESSION['cart']);
    $total_qty = 0;
    
    foreach ($_SESSION['cart'] as $item) {
        $total_qty += $item['jumlah'];
    }
    
    APIResponse::success([
        'items_count' => $count,
        'total_qty' => $total_qty
    ], 'Cart count retrieved');
}

/**
 * Add item to cart
 */
function addToCart() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $errors = [];
    if (empty($data['id_produk'])) $errors['id_produk'] = 'Product ID is required';
    if (empty($data['jumlah']) || !is_numeric($data['jumlah']) || $data['jumlah'] < 1) {
        $errors['jumlah'] = 'Quantity must be at least 1';
    }
    
    if (!empty($errors)) {
        APIResponse::validationError($errors);
    }
    
    $product_id = intval($data['id_produk']);
    $quantity = intval($data['jumlah']);
    
    // Check if product exists and has stock
    $stmt = $conn->prepare('SELECT id_produk, stok FROM produk WHERE id_produk = ?');
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        APIResponse::notFound('Product not found');
    }
    
    $product = $result->fetch_assoc();
    if ($product['stok'] < $quantity) {
        APIResponse::error('Insufficient stock. Available: ' . $product['stok'], 400);
    }
    $stmt->close();
    
    // Initialize session cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Check if product already in cart
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id_produk'] == $product_id) {
            $item['jumlah'] += $quantity;
            $found = true;
            break;
        }
    }
    
    // If not found, add new item
    if (!$found) {
        $_SESSION['cart'][] = [
            'id_produk' => $product_id,
            'jumlah' => $quantity,
            'added_at' => date('Y-m-d H:i:s')
        ];
    }
    
    // Sync to database for persistence
    syncCartToDatabase();
    
    APIResponse::success([
        'product_id' => $product_id,
        'quantity' => $quantity
    ], 'Product added to cart', 201);
}

/**
 * Update cart item quantity
 */
function updateCart() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $errors = [];
    if (empty($data['id_produk'])) $errors['id_produk'] = 'Product ID is required';
    if (empty($data['jumlah']) || !is_numeric($data['jumlah']) || $data['jumlah'] < 1) {
        $errors['jumlah'] = 'Quantity must be at least 1';
    }
    
    if (!empty($errors)) {
        APIResponse::validationError($errors);
    }
    
    $product_id = intval($data['id_produk']);
    $new_quantity = intval($data['jumlah']);
    
    if (!isset($_SESSION['cart'])) {
        APIResponse::error('Cart is empty', 400);
    }
    
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id_produk'] == $product_id) {
            $item['jumlah'] = $new_quantity;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        APIResponse::notFound('Product not found in cart');
    }
    
    // Sync to database
    syncCartToDatabase();
    
    APIResponse::success(null, 'Cart updated successfully');
}

/**
 * Remove item from cart
 */
function removeFromCart() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    if (empty($data['id_produk'])) {
        APIResponse::validationError(['id_produk' => 'Product ID is required']);
    }
    
    $product_id = intval($data['id_produk']);
    
    if (!isset($_SESSION['cart'])) {
        APIResponse::error('Cart is empty', 400);
    }
    
    $before_count = count($_SESSION['cart']);
    $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($product_id) {
        return $item['id_produk'] != $product_id;
    });
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    
    if (count($_SESSION['cart']) === $before_count) {
        APIResponse::notFound('Product not found in cart');
    }
    
    // Sync to database
    syncCartToDatabase();
    
    APIResponse::success(null, 'Item removed from cart');
}

/**
 * Clear entire cart
 */
function clearCart() {
    $_SESSION['cart'] = [];
    
    // Clear from database too
    global $conn;
    $user_id = $_SESSION['user'];
    
    $stmt = $conn->prepare('DELETE FROM keranjang WHERE id_user = ?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->close();
    
    APIResponse::success(null, 'Cart cleared successfully');
}

/**
 * Sync session cart to database (for persistence across devices/sessions)
 */
function syncCartToDatabase() {
    global $conn;
    
    $user_id = $_SESSION['user'];
    
    // Clear old cart items for this user
    $stmt = $conn->prepare('DELETE FROM keranjang WHERE id_user = ?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->close();
    
    // Insert current session cart items
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        $stmt = $conn->prepare('INSERT INTO keranjang (id_user, id_produk, jumlah) VALUES (?, ?, ?)');
        
        foreach ($_SESSION['cart'] as $item) {
            $product_id = $item['id_produk'];
            $quantity = $item['jumlah'];
            
            $stmt->bind_param('iii', $user_id, $product_id, $quantity);
            $stmt->execute();
        }
        
        $stmt->close();
    }
}

/**
 * Load cart from database to session (for multi-device sync)
 */
function loadCartFromDatabase() {
    global $conn;
    
    $user_id = $_SESSION['user'];
    
    $stmt = $conn->prepare('SELECT id_produk, jumlah FROM keranjang WHERE id_user = ?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $_SESSION['cart'] = [];
    while ($row = $result->fetch_assoc()) {
        $_SESSION['cart'][] = [
            'id_produk' => $row['id_produk'],
            'jumlah' => $row['jumlah']
        ];
    }
    
    $stmt->close();
}

?>
