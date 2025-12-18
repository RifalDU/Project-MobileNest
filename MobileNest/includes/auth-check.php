<?php
/**
 * AUTH-CHECK.PHP
 * 
 * File ini menangani:
 * 1. Proteksi halaman user & admin
 * 2. Role-Based Access Control (RBAC) dengan tabel admin terpisah
 * 3. Session management
 * 4. CSRF token generation & verification
 * 5. Helper functions untuk security
 * 
 * 🔑 PENTING: Sistem menggunakan tabel ADMIN terpisah untuk diferensiasi role
 * User yang ada di tabel admin = ADMIN
 * User yang TIDAK ada di tabel admin = REGULAR USER
 */

// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
if (!isset($conn)) {
    require_once __DIR__ . '/config.php';
}

/**
 * 🔐 PROTEKSI LOGIN - USER
 * Memastikan hanya user yang login yang bisa akses halaman user
 * Jika admin mencoba akses, redirect ke admin panel
 */
function require_user_login() {
    // 1. Cek apakah ada session user atau admin
    if (!isset($_SESSION['user']) && !isset($_SESSION['admin'])) {
        $_SESSION['error'] = '🔒 Anda harus login terlebih dahulu!';
        header('Location: ' . getBaseUrl() . '/login.php');
        exit;
    }
    
    // 2. Jika yang login adalah admin, redirect ke admin panel
    if (isset($_SESSION['admin']) && !isset($_SESSION['user'])) {
        header('Location: ' . getBaseUrl() . '/admin/dashboard.php');
        exit;
    }
}

/**
 * 🔒 PROTEKSI LOGIN - ADMIN
 * Memastikan hanya admin yang bisa akses halaman admin
 * Jika user biasa mencoba akses, redirect ke user page
 */
function require_admin_login() {
    // 1. Cek apakah ada session admin
    if (!isset($_SESSION['admin'])) {
        $_SESSION['error'] = '🔒 Anda harus login sebagai admin!';
        header('Location: ' . getBaseUrl() . '/login.php');
        exit;
    }
    
    // 2. Jika yang login adalah user biasa, redirect ke user page
    if (isset($_SESSION['user']) && !isset($_SESSION['admin'])) {
        header('Location: ' . getBaseUrl() . '/user/pesanan.php');
        exit;
    }
}

/**
 * 🔍 CEK ADMIN VIA DATABASE
 * Digunakan untuk double-check apakah user adalah admin
 * (untuk verifikasi tambahan di tengah proses)
 */
function is_user_admin($user_id, $conn) {
    $sql = "SELECT id_admin FROM admin WHERE id_user = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    return $result->num_rows > 0;
}

/**
 * 📄 GET USER/ADMIN ID
 */
function get_user_id() {
    if (isset($_SESSION['user'])) {
        return $_SESSION['user'];
    } elseif (isset($_SESSION['admin'])) {
        return $_SESSION['admin'];
    }
    return null;
}

function get_admin_id() {
    return $_SESSION['admin'] ?? null;
}

function get_user_name() {
    if (isset($_SESSION['user_name'])) {
        return $_SESSION['user_name'];
    } elseif (isset($_SESSION['admin_name'])) {
        return $_SESSION['admin_name'];
    }
    return 'Unknown';
}

/**
 * ✅ CEK LOGIN STATUS
 */
function is_user_logged_in() {
    return isset($_SESSION['user']);
}

function is_admin_logged_in() {
    return isset($_SESSION['admin']);
}

function is_logged_in() {
    return isset($_SESSION['user']) || isset($_SESSION['admin']);
}

/**
 * 🚳 GET USER ROLE
 */
function get_user_role() {
    if (isset($_SESSION['admin'])) {
        return 'admin';
    } elseif (isset($_SESSION['user'])) {
        return 'user';
    }
    return 'guest';
}

/**
 * 🔐 LOGOUT FUNCTIONS
 */
function user_logout() {
    unset($_SESSION['user']);
    unset($_SESSION['user_name']);
    unset($_SESSION['user_email']);
}

function admin_logout() {
    unset($_SESSION['admin']);
    unset($_SESSION['admin_name']);
    unset($_SESSION['admin_email']);
}

function logout_all() {
    user_logout();
    admin_logout();
    session_destroy();
}

/**
 * 💳 CSRF TOKEN FUNCTIONS
 * Untuk protect dari Cross-Site Request Forgery attacks
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * 🆕 HASH PASSWORD
 * Generate hash untuk password baru
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

/**
 * ✅ VERIFY PASSWORD
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * 📁 GET BASE URL
 * Helper untuk generate base URL aplikasi
 */
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $basePath = dirname(dirname($_SERVER['SCRIPT_NAME']));
    if ($basePath === '\\' || $basePath === '/') {
        $basePath = '';
    }
    return $protocol . '://' . $host . $basePath;
}

/**
 * 📋 LOG ACTIVITY (optional)
 * Catat setiap aktivitas penting untuk audit
 */
function log_activity($action, $details, $conn) {
    $user_id = get_user_id();
    $role = get_user_role();
    $ip = $_SERVER['REMOTE_ADDR'];
    $timestamp = date('Y-m-d H:i:s');
    
    // TODO: Buat tabel activity_log untuk audit trail
    // INSERT INTO activity_log (user_id, role, action, details, ip, timestamp)
    // VALUES (?, ?, ?, ?, ?, ?)
}

/**
 * 🎯 SECURITY HEADERS
 * Set security headers untuk prevent common attacks
 */
function set_security_headers() {
    // Prevent clickjacking
    header('X-Frame-Options: SAMEORIGIN');
    
    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Enable XSS protection
    header('X-XSS-Protection: 1; mode=block');
    
    // Content Security Policy
    header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' https://cdnjs.cloudflare.com; style-src \'self\' \'unsafe-inline\' https://cdnjs.cloudflare.com; img-src \'self\' data: https:;');
}

// Jalankan security headers
set_security_headers();

?>
