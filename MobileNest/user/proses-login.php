<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_or_email = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
    $password          = $_POST['password'] ?? '';

    if ($username_or_email === '' || $password === '') {
        $_SESSION['error'] = "Username/email dan password wajib diisi.";
        header('Location: login.php');
        exit;
    }

    // Query user berdasarkan email atau username
    $sql = "SELECT id_user, nama_lengkap, email, username, password FROM users 
            WHERE username='$username_or_email' OR email='$username_or_email' 
            LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        // Verifikasi password (hashed dengan password_hash)
        if (password_verify($password, $user['password'])) {
            // PENTING: Gunakan session key 'user' (bukan 'id_user') agar sesuai dengan config.php
            $_SESSION['user']       = $user['id_user'];        // ID user
            $_SESSION['user_name']  = $user['nama_lengkap'];   // Nama lengkap untuk ditampilkan
            $_SESSION['user_email'] = $user['email'];          // Email
            $_SESSION['username']   = $user['username'];       // Username
            $_SESSION['role']       = 'user';                  // Role (customer)

            // Success message
            $_SESSION['success'] = "Login berhasil. Selamat datang, " . $user['nama_lengkap'] . "!";
            
            // Redirect ke halaman sebelumnya atau home
            header('Location: ../index.php');
            exit;
        } else {
            $_SESSION['error'] = "Password salah.";
            header('Location: login.php');
            exit;
        }
    } else {
        $_SESSION['error'] = "Username atau email tidak ditemukan.";
        header('Location: login.php');
        exit;
    }
} else {
    header('Location: login.php');
    exit;
}
?>
