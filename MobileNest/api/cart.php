<?php
header('Content-Type: application/json; charset=utf-8');

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config.php';

// Get action from request
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

try {
    if ($action === 'get') {
        // Get cart items - allow anonymous users
        $cart_items = [];
        
        if (isset($_SESSION['cart']) && is_array($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
            foreach ($_SESSION['cart'] as $id_produk => $quantity) {
                // Get product details
                $id_produk = intval($id_produk);
                $quantity = intval($quantity);
                
                $sql = "SELECT id_produk, nama_produk, harga FROM produk WHERE id_produk = $id_produk";
                $result = mysqli_query($conn, $sql);
                
                if ($result && mysqli_num_rows($result) > 0) {
                    $product = mysqli_fetch_assoc($result);
                    $cart_items[] = [
                        'id_produk' => (int)$product['id_produk'],
                        'nama_produk' => $product['nama_produk'],
                        'harga' => (int)$product['harga'],
                        'quantity' => $quantity,
                        'subtotal' => (int)$product['harga'] * $quantity
                    ];
                }
            }
        }

        echo json_encode([
            'success' => true,
            'items' => $cart_items,
            'count' => count($cart_items),
            'session_id' => session_id(),
            'debug' => [
                'session_cart' => $_SESSION['cart'] ?? 'not set',
                'session_status' => session_status()
            ]
        ]);
        exit;
    }
    
    elseif ($action === 'add') {
        // Add item to cart
        $input = json_decode(file_get_contents('php://input'), true);
        $id_produk = isset($input['id_produk']) ? intval($input['id_produk']) : 0;
        $quantity = isset($input['quantity']) ? intval($input['quantity']) : 1;
        
        if ($id_produk <= 0 || $quantity <= 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'Invalid product or quantity'
            ]);
            exit;
        }
        
        // Verify product exists
        $sql = "SELECT id_produk FROM produk WHERE id_produk = $id_produk";
        $result = mysqli_query($conn, $sql);
        if (!$result || mysqli_num_rows($result) === 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'Product not found'
            ]);
            exit;
        }
        
        // Initialize cart if not exists
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Add or update item in cart
        $key = (string)$id_produk; // Use string key for consistency
        if (isset($_SESSION['cart'][$key])) {
            $_SESSION['cart'][$key] += $quantity;
        } else {
            $_SESSION['cart'][$key] = $quantity;
        }
        
        // Force session to save
        session_write_close();
        session_start();
        
        echo json_encode([
            'success' => true,
            'message' => 'Item added to cart',
            'cart_count' => count($_SESSION['cart']),
            'session_id' => session_id(),
            'cart_data' => $_SESSION['cart']
        ]);
        exit;
    }
    
    elseif ($action === 'remove') {
        // Remove item from cart
        $input = json_decode(file_get_contents('php://input'), true);
        $id_produk = isset($input['id_produk']) ? intval($input['id_produk']) : 0;
        
        if ($id_produk <= 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'Invalid product ID'
            ]);
            exit;
        }
        
        $key = (string)$id_produk;
        if (isset($_SESSION['cart'][$key])) {
            unset($_SESSION['cart'][$key]);
        }
        
        // Force session to save
        session_write_close();
        session_start();
        
        echo json_encode([
            'success' => true,
            'message' => 'Item removed from cart',
            'cart_count' => count($_SESSION['cart'])
        ]);
        exit;
    }
    
    elseif ($action === 'update') {
        // Update item quantity
        $input = json_decode(file_get_contents('php://input'), true);
        $id_produk = isset($input['id_produk']) ? intval($input['id_produk']) : 0;
        $quantity = isset($input['quantity']) ? intval($input['quantity']) : 0;
        
        if ($id_produk <= 0 || $quantity < 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'Invalid product or quantity'
            ]);
            exit;
        }
        
        $key = (string)$id_produk;
        if ($quantity === 0) {
            unset($_SESSION['cart'][$key]);
        } else {
            $_SESSION['cart'][$key] = $quantity;
        }
        
        // Force session to save
        session_write_close();
        session_start();
        
        echo json_encode([
            'success' => true,
            'message' => 'Cart updated',
            'cart_count' => count($_SESSION['cart'])
        ]);
        exit;
    }
    
    elseif ($action === 'count') {
        // Get cart item count
        $count = 0;
        if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            $count = count($_SESSION['cart']);
        }
        
        echo json_encode([
            'success' => true,
            'count' => $count
        ]);
        exit;
    }
    
    elseif ($action === 'clear') {
        // Clear entire cart
        $_SESSION['cart'] = [];
        session_write_close();
        session_start();
        
        echo json_encode([
            'success' => true,
            'message' => 'Cart cleared'
        ]);
        exit;
    }
    
    else {
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid action: ' . $action
        ]);
        exit;
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    exit;
}
?>
