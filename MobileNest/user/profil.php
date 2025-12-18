<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/auth-check.php';
require_user_login();
require_once '../config.php';

$user_id = $_SESSION['user'];
$user_data = [];
$errors = [];
$message = '';

$sql = "SELECT id_user, nama_lengkap, email, no_telepon, alamat FROM users WHERE id_user = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) { die("Prepare failed: " . $conn->error); }
$stmt->bind_param('i', $user_id);
if (!$stmt->execute()) { die("Execute failed: " . $stmt->error); }
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    if ($action === 'edit_profil') {
        $nama = isset($_POST['nama_lengkap']) ? trim($_POST['nama_lengkap']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $telepon = isset($_POST['no_telepon']) ? trim($_POST['no_telepon']) : '';
        $alamat = isset($_POST['alamat']) ? trim($_POST['alamat']) : '';
        
        if (empty($nama)) { $errors[] = 'Nama lengkap tidak boleh kosong'; }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Email tidak valid'; }
        
        $email_check_sql = "SELECT id_user FROM users WHERE email = ? AND id_user != ?";
        $email_check = $conn->prepare($email_check_sql);
        $email_check->bind_param('si', $email, $user_id);
        $email_check->execute();
        if ($email_check->get_result()->num_rows > 0) {
            $errors[] = 'Email sudah digunakan oleh user lain';
        }
        $email_check->close();
        
        if (empty($errors)) {
            $update_sql = "UPDATE users SET nama_lengkap = ?, email = ?, no_telepon = ?, alamat = ? WHERE id_user = ?";
            $update_stmt = $conn->prepare($update_sql);
            if (!$update_stmt) {
                $errors[] = 'Prepare failed: ' . $conn->error;
            } else {
                $update_stmt->bind_param('ssssi', $nama, $email, $telepon, $alamat, $user_id);
                if ($update_stmt->execute()) {
                    $message = '✅ Profil berhasil diperbarui!';
                    $user_data['nama_lengkap'] = $nama;
                    $user_data['email'] = $email;
                    $user_data['no_telepon'] = $telepon;
                    $user_data['alamat'] = $alamat;
                } else {
                    $errors[] = 'Error: ' . $update_stmt->error;
                }
                $update_stmt->close();
            }
        }
    } elseif ($action === 'ubah_password') {
        $password_lama = isset($_POST['password_lama']) ? $_POST['password_lama'] : '';
        $password_baru = isset($_POST['password_baru']) ? $_POST['password_baru'] : '';
        $password_konfirm = isset($_POST['password_konfirm']) ? $_POST['password_konfirm'] : '';
        
        if (empty($password_lama)) { $errors[] = 'Password lama tidak boleh kosong'; }
        if (empty($password_baru) || strlen($password_baru) < 6) { $errors[] = 'Password baru minimal 6 karakter'; }
        if ($password_baru !== $password_konfirm) { $errors[] = 'Password baru tidak sama dengan konfirmasi'; }
        
        if (empty($errors)) {
            $check_pwd_sql = "SELECT password FROM users WHERE id_user = ?";
            $check_pwd = $conn->prepare($check_pwd_sql);
            $check_pwd->bind_param('i', $user_id);
            $check_pwd->execute();
            $pwd_result = $check_pwd->get_result();
            $user = $pwd_result->fetch_assoc();
            $check_pwd->close();
            
            if (password_verify($password_lama, $user['password'])) {
                $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
                $pwd_update_sql = "UPDATE users SET password = ? WHERE id_user = ?";
                $pwd_update = $conn->prepare($pwd_update_sql);
                $pwd_update->bind_param('si', $password_hash, $user_id);
                if ($pwd_update->execute()) {
                    $message = '✅ Password berhasil diubah!';
                } else {
                    $errors[] = 'Error: ' . $pwd_update->error;
                }
                $pwd_update->close();
            } else {
                $errors[] = 'Password lama tidak sesuai';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - MobileNest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #007bff;
            --primary-dark: #0056b3;
            --secondary: #6c757d;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --light-bg: #f8f9fa;
            --card-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .profile-container {
            padding: 40px 20px;
        }

        /* Header dengan Avatar */
        .profile-header {
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
            text-align: center;
            position: relative;
        }

        .avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--primary), #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 50px;
            color: white;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .profile-header h1 {
            font-size: 32px;
            color: #2c3e50;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .profile-header p {
            color: var(--secondary);
            font-size: 16px;
            margin: 0;
        }

        .user-id-badge {
            display: inline-block;
            background: var(--light-bg);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            color: var(--secondary);
            margin-top: 10px;
        }

        /* Navigation Tabs Modern */
        .nav-tabs-modern {
            border: none;
            background: white;
            border-radius: 15px;
            padding: 10px;
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
            display: flex;
            gap: 10px;
        }

        .nav-tabs-modern .nav-link {
            border: none;
            color: var(--secondary);
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 12px;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-tabs-modern .nav-link:hover {
            background: var(--light-bg);
            color: var(--primary);
        }

        .nav-tabs-modern .nav-link.active {
            background: linear-gradient(135deg, var(--primary), #764ba2);
            color: white;
        }

        /* Content Cards */
        .content-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: var(--card-shadow);
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 15px;
        }

        .form-control {
            border: 2px solid #e0e6ed;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 15px;
            transition: var(--transition);
            background: white;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
            outline: none;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Buttons */
        .btn-modern {
            border: none;
            border-radius: 12px;
            padding: 12px 32px;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-size: 15px;
        }

        .btn-primary-modern {
            background: linear-gradient(135deg, var(--primary), #764ba2);
            color: white;
            width: 100%;
            justify-content: center;
            padding: 14px 32px;
        }

        .btn-primary-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-danger-modern {
            background: linear-gradient(135deg, #f093fb, #f5576c);
            color: white;
            width: 100%;
            justify-content: center;
            padding: 14px 32px;
        }

        .btn-danger-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 87, 108, 0.4);
        }

        /* Alerts */
        .alert-modern {
            border: none;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 25px;
            border-left: 4px solid;
            animation: slideInDown 0.3s ease;
        }

        .alert-success-modern {
            background: #d4edda;
            color: #155724;
            border-left-color: #28a745;
        }

        .alert-danger-modern {
            background: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }

        /* Info Box */
        .info-box {
            background: linear-gradient(135deg, #667eea15, #764ba215);
            border-left: 4px solid var(--primary);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .info-box strong {
            color: #2c3e50;
        }

        .info-box p {
            margin: 0;
            color: var(--secondary);
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .profile-container {
                padding: 20px 10px;
            }

            .profile-header {
                padding: 25px 15px;
            }

            .profile-header h1 {
                font-size: 24px;
            }

            .content-card {
                padding: 25px 15px;
            }

            .nav-tabs-modern {
                flex-direction: column;
            }

            .nav-tabs-modern .nav-link {
                width: 100%;
                justify-content: center;
            }
        }

        @keyframes slideInDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Icon styling */
        .icon-field {
            position: relative;
        }

        .icon-field i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary);
            font-size: 18px;
        }

        .icon-field .form-control {
            padding-left: 45px;
        }
    </style>
</head>
<body>
    <?php include '../header.php'; ?>

    <div class="profile-container">
        <div class="container">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="avatar">
                    <i class="fas fa-user"></i>
                </div>
                <h1><?php echo htmlspecialchars($user_data['nama_lengkap'] ?? 'User'); ?></h1>
                <p><?php echo htmlspecialchars($user_data['email'] ?? ''); ?></p>
                <div class="user-id-badge">
                    <i class="fas fa-id-card"></i> ID: #<?php echo $user_id; ?>
                </div>
            </div>

            <!-- Notifications -->
            <?php if ($message): ?>
                <div class="alert alert-success-modern alert-modern">
                    <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger-modern alert-modern">
                    <?php foreach ($errors as $error): ?>
                        <div><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Navigation Tabs -->
            <ul class="nav nav-tabs-modern" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="data-tab" data-bs-toggle="tab" href="#data-pribadi" role="tab">
                        <i class="fas fa-user-circle"></i> Data Pribadi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="password-tab" data-bs-toggle="tab" href="#ubah-password" role="tab">
                        <i class="fas fa-lock"></i> Keamanan
                    </a>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Tab 1: Data Pribadi -->
                <div class="tab-pane fade show active" id="data-pribadi" role="tabpanel">
                    <div class="content-card">
                        <h3 style="margin-bottom: 30px; color: #2c3e50;"><i class="fas fa-pen-to-square"></i> Edit Informasi Pribadi</h3>

                        <div class="info-box">
                            <p><strong>📝 Tips:</strong> Pastikan semua data yang Anda masukkan akurat dan terbaru.</p>
                        </div>

                        <form method="POST">
                            <input type="hidden" name="action" value="edit_profil">

                            <div class="form-group">
                                <label><i class="fas fa-user" style="color: var(--primary);"></i> Nama Lengkap</label>
                                <input type="text" class="form-control" name="nama_lengkap" value="<?php echo htmlspecialchars($user_data['nama_lengkap'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-envelope" style="color: var(--primary);"></i> Email</label>
                                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-phone" style="color: var(--primary);"></i> No. Telepon</label>
                                <input type="text" class="form-control" name="no_telepon" value="<?php echo htmlspecialchars($user_data['no_telepon'] ?? ''); ?>" placeholder="Contoh: 081234567890">
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-map-location-dot" style="color: var(--primary);"></i> Alamat Lengkap</label>
                                <textarea class="form-control" name="alamat" placeholder="Jln. Contoh No. 123, Kota, Provinsi"><?php echo htmlspecialchars($user_data['alamat'] ?? ''); ?></textarea>
                            </div>

                            <button type="submit" class="btn-modern btn-primary-modern">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Tab 2: Ubah Password -->
                <div class="tab-pane fade" id="ubah-password" role="tabpanel">
                    <div class="content-card">
                        <h3 style="margin-bottom: 30px; color: #2c3e50;"><i class="fas fa-shield-halved"></i> Ubah Password</h3>

                        <div class="info-box">
                            <p><strong>🔒 Keamanan:</strong> Gunakan password yang kuat dengan kombinasi huruf, angka, dan simbol.</p>
                        </div>

                        <form method="POST">
                            <input type="hidden" name="action" value="ubah_password">

                            <div class="form-group">
                                <label><i class="fas fa-key" style="color: var(--primary);"></i> Password Lama</label>
                                <input type="password" class="form-control" name="password_lama" placeholder="Masukkan password saat ini" required>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-lock-open" style="color: var(--primary);"></i> Password Baru</label>
                                <input type="password" class="form-control" name="password_baru" placeholder="Minimal 6 karakter" required>
                                <small style="color: var(--secondary); display: block; margin-top: 8px;">
                                    <i class="fas fa-info-circle"></i> Password harus minimal 6 karakter
                                </small>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-check-circle" style="color: var(--primary);"></i> Konfirmasi Password</label>
                                <input type="password" class="form-control" name="password_konfirm" placeholder="Ulangi password baru" required>
                            </div>

                            <button type="submit" class="btn-modern btn-danger-modern">
                                <i class="fas fa-key"></i> Ubah Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto hide alerts after 5 seconds
        document.querySelectorAll('.alert-modern').forEach(alert => {
            setTimeout(() => {
                alert.style.animation = 'slideInDown 0.3s ease reverse';
                setTimeout(() => {
                    alert.remove();
                }, 300);
            }, 5000);
        });
    </script>
</body>
</html>
