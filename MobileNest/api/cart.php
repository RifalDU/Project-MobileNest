<?php
header('Content-Type: application/json; charset=utf-8');

// Start session to get session ID
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', 86400);
    ini_set('session.cookie_lifetime', 86400);
    session_start();
}

require_once '../config.php';

// Use session_id as temporary user identifier for anonymous carts
$session_id = session_id();
$temp_user_id = 'temp_' . substr($session_id, 0, 10); // Create a temp identifier

// Get action from request
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

// Helper function to get parameters from GET, POST, or JSON
function getParam($key, $default = null) {
    // Check GET first
    if (isset($_GET[$key])) {
        return $_GET[$key];
    }
    // Check POST second
    if (isset($_POST[$key])) {
        return $_POST[$key];
    }
    // Check JSON body last
    $json = json_decode(file_get_contents('php://input'), true);
    if (is_array($json) && isset($json[$key])) {
        return $json[$key];
    }
    return $default;
}

try {
    if ($action === 'get') {
        // Get cart items from database
        $cart_items = [];
        
        $sql = "SELECT 
                    k.id_keranjang,
                    k.id_produk,
                    k.jumlah as quantity,
                    p.nama_produk,
                    p.harga
                FROM keranjang k
                JOIN produk p ON k.id_produk = p.id_produk
                WHERE k.id_user = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 's', $temp_user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $cart_items[] = [
                'id_cart' => (int)$row['id_keranjang'],
                'id_produk' => (int)$row['id_produk'],
                'nama_produk' => $row['nama_produk'],
                'harga' => (int)$row['harga'],
                'quantity' => (int)$row['quantity'],
                'subtotal' => (int)$row['harga'] * (int)$row['quantity']
            ];
        }
        
        mysqli_stmt_close($stmt);
        
        echo json_encode([
            'success' => true,
            'items' => $cart_items,
            'count' => count($cart_items)
        ]);
        exit;
    }
    
    elseif ($action === 'add') {
        // Add item to cart in database
        $id_produk = intval(getParam('id_produk', 0));
        $jumlah = intval(getParam('jumlah', 1)); // Support 'jumlah' from frontend
        $quantity = intval(getParam('quantity', $jumlah)); // Also support 'quantity'
        
        if ($quantity <= 0) {
            $quantity = 1;
        }
        
        if ($id_produk <= 0 || $quantity <= 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'Invalid product or quantity'
            ]);
            exit;
        }
        
        // Verify product exists
        $sql_check = "SELECT id_produk FROM produk WHERE id_produk = ? LIMIT 1";
        $stmt_check = mysqli_prepare($conn, $sql_check);
        mysqli_stmt_bind_param($stmt_check, 'i', $id_produk);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        
        if (mysqli_num_rows($result_check) === 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'Product not found'
            ]);
            mysqli_stmt_close($stmt_check);
            exit;
        }
        
        mysqli_stmt_close($stmt_check);
        
        // Check if item already in cart
        $sql_exist = "SELECT id_keranjang, jumlah FROM keranjang WHERE id_user = ? AND id_produk = ? LIMIT 1";
        $stmt_exist = mysqli_prepare($conn, $sql_exist);
        mysqli_stmt_bind_param($stmt_exist, 'si', $temp_user_id, $id_produk);
        mysqli_stmt_execute($stmt_exist);
        $result_exist = mysqli_stmt_get_result($stmt_exist);
        
        if (mysqli_num_rows($result_exist) > 0) {
            // Update quantity if already exists
            $row_exist = mysqli_fetch_assoc($result_exist);
            $new_quantity = $row_exist['jumlah'] + $quantity;
            
            $sql_update = "UPDATE keranjang SET jumlah = ? WHERE id_user = ? AND id_produk = ?";
            $stmt_update = mysqli_prepare($conn, $sql_update);
            mysqli_stmt_bind_param($stmt_update, 'isi', $new_quantity, $temp_user_id, $id_produk);
            $exec_update = mysqli_stmt_execute($stmt_update);
            mysqli_stmt_close($stmt_update);
            
            if (!$exec_update) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to update cart'
                ]);
                exit;
            }
        } else {
            // Insert new cart item
            $sql_insert = "INSERT INTO keranjang (id_user, id_produk, jumlah) VALUES (?, ?, ?)";
            $stmt_insert = mysqli_prepare($conn, $sql_insert);
            mysqli_stmt_bind_param($stmt_insert, 'sii', $temp_user_id, $id_produk, $quantity);
            $exec_insert = mysqli_stmt_execute($stmt_insert);
            mysqli_stmt_close($stmt_insert);
            
            if (!$exec_insert) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to add to cart'
                ]);
                exit;
            }
        }
        
        mysqli_stmt_close($stmt_exist);
        
        // Get updated cart count
        $sql_count = "SELECT COUNT(*) as count FROM keranjang WHERE id_user = ?";
        $stmt_count = mysqli_prepare($conn, $sql_count);
        mysqli_stmt_bind_param($stmt_count, 's', $temp_user_id);
        mysqli_stmt_execute($stmt_count);
        $result_count = mysqli_stmt_get_result($stmt_count);
        $row_count = mysqli_fetch_assoc($result_count);
        mysqli_stmt_close($stmt_count);
        
        echo json_encode([
            'success' => true,
            'message' => 'Item added to cart',
            'cart_count' => (int)$row_count['count']
        ]);
        exit;
    }
    
    elseif ($action === 'remove') {
        // Remove item from cart
        $id_produk = intval(getParam('id_produk', 0));
        
        if ($id_produk <= 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'Invalid product ID'
            ]);
            exit;
        }
        
        $sql_delete = "DELETE FROM keranjang WHERE id_user = ? AND id_produk = ?";
        $stmt_delete = mysqli_prepare($conn, $sql_delete);
        mysqli_stmt_bind_param($stmt_delete, 'si', $temp_user_id, $id_produk);
        $exec_delete = mysqli_stmt_execute($stmt_delete);
        mysqli_stmt_close($stmt_delete);
        
        if (!$exec_delete) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to remove item'
            ]);
            exit;
        }
        
        // Get updated cart count
        $sql_count = "SELECT COUNT(*) as count FROM keranjang WHERE id_user = ?";
        $stmt_count = mysqli_prepare($conn, $sql_count);
        mysqli_stmt_bind_param($stmt_count, 's', $temp_user_id);
        mysqli_stmt_execute($stmt_count);
        $result_count = mysqli_stmt_get_result($stmt_count);
        $row_count = mysqli_fetch_assoc($result_count);
        mysqli_stmt_close($stmt_count);
        
        echo json_encode([
            'success' => true,
            'message' => 'Item removed from cart',
            'cart_count' => (int)$row_count['count']
        ]);
        exit;
    }
    
    elseif ($action === 'update') {
        // Update item quantity
        $id_produk = intval(getParam('id_produk', 0));
        $quantity = intval(getParam('quantity', 0));
        
        if ($id_produk <= 0 || $quantity < 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'Invalid product or quantity'
            ]);
            exit;
        }
        
        if ($quantity === 0) {
            // Delete if quantity is 0
            $sql_delete = "DELETE FROM keranjang WHERE id_user = ? AND id_produk = ?";
            $stmt_delete = mysqli_prepare($conn, $sql_delete);
            mysqli_stmt_bind_param($stmt_delete, 'si', $temp_user_id, $id_produk);
            mysqli_stmt_execute($stmt_delete);
            mysqli_stmt_close($stmt_delete);
        } else {
            // Update quantity
            $sql_update = "UPDATE keranjang SET jumlah = ? WHERE id_user = ? AND id_produk = ?";
            $stmt_update = mysqli_prepare($conn, $sql_update);
            mysqli_stmt_bind_param($stmt_update, 'isi', $quantity, $temp_user_id, $id_produk);
            mysqli_stmt_execute($stmt_update);
            mysqli_stmt_close($stmt_update);
        }
        
        // Get updated cart count
        $sql_count = "SELECT COUNT(*) as count FROM keranjang WHERE id_user = ?";
        $stmt_count = mysqli_prepare($conn, $sql_count);
        mysqli_stmt_bind_param($stmt_count, 's', $temp_user_id);
        mysqli_stmt_execute($stmt_count);
        $result_count = mysqli_stmt_get_result($stmt_count);
        $row_count = mysqli_fetch_assoc($result_count);
        mysqli_stmt_close($stmt_count);
        
        echo json_encode([
            'success' => true,
            'message' => 'Cart updated',
            'cart_count' => (int)$row_count['count']
        ]);
        exit;
    }
    
    elseif ($action === 'count') {
        // Get cart item count
        $sql_count = "SELECT COUNT(*) as count FROM keranjang WHERE id_user = ?";
        $stmt_count = mysqli_prepare($conn, $sql_count);
        mysqli_stmt_bind_param($stmt_count, 's', $temp_user_id);
        mysqli_stmt_execute($stmt_count);
        $result_count = mysqli_stmt_get_result($stmt_count);
        $row_count = mysqli_fetch_assoc($result_count);
        mysqli_stmt_close($stmt_count);
        
        echo json_encode([
            'success' => true,
            'count' => (int)$row_count['count']
        ]);
        exit;
    }
    
    elseif ($action === 'clear') {
        // Clear entire cart
        $sql_clear = "DELETE FROM keranjang WHERE id_user = ?";
        $stmt_clear = mysqli_prepare($conn, $sql_clear);
        mysqli_stmt_bind_param($stmt_clear, 's', $temp_user_id);
        mysqli_stmt_execute($stmt_clear);
        mysqli_stmt_close($stmt_clear);
        
        echo json_encode([
            'success' => true,
            'message' => 'Cart cleared'
        ]);
        exit;
    }
    
    else {
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid action'
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