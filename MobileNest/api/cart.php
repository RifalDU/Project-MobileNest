<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';

// Get action from request
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

try {
    if ($action === 'get') {
        // Get cart items
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            // User not logged in, return empty cart
            echo json_encode([
                'success' => true, 
                'items' => [], 
                'count' => 0,
                'message' => 'Not logged in'
            ]);
            exit;
        }

        $user_id = $_SESSION['user_id'];
        
        // Get cart from session
        $cart_items = [];
        
        if (isset($_SESSION['cart']) && is_array($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
            foreach ($_SESSION['cart'] as $id_produk => $quantity) {
                // Get product details
                $sql = "SELECT id_produk, nama_produk, harga FROM produk WHERE id_produk = '$id_produk'";
                $result = mysqli_query($conn, $sql);
                
                if ($result && mysqli_num_rows($result) > 0) {
                    $product = mysqli_fetch_assoc($result);
                    $cart_items[] = [
                        'id_produk' => (int)$product['id_produk'],
                        'nama_produk' => $product['nama_produk'],
                        'harga' => (int)$product['harga'],
                        'quantity' => (int)$quantity,
                        'subtotal' => (int)$product['harga'] * (int)$quantity
                    ];
                }
            }
        }

        echo json_encode([
            'success' => true,
            'items' => $cart_items,
            'count' => count($cart_items)
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
        
        // Initialize cart if not exists
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Add or update item in cart
        if (isset($_SESSION['cart'][$id_produk])) {
            $_SESSION['cart'][$id_produk] += $quantity;
        } else {
            $_SESSION['cart'][$id_produk] = $quantity;
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Item added to cart',
            'cart_count' => count($_SESSION['cart'])
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
        
        if (isset($_SESSION['cart'][$id_produk])) {
            unset($_SESSION['cart'][$id_produk]);
        }
        
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
        
        if ($quantity === 0) {
            unset($_SESSION['cart'][$id_produk]);
        } else {
            $_SESSION['cart'][$id_produk] = $quantity;
        }
        
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
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}
?>
