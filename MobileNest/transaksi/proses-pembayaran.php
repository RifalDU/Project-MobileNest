<?php
// Mulai session
session_start();

// Cek user sudah login
if (!isset($_SESSION['user'])) {
    header('Location: ../user/login.php');
    exit;
}

// Include config database
require_once '../config.php';

$user_id = $_SESSION['user'];
$transaksi_data = [];
$message = '';
$message_type = '';

// Ambil ID transaksi dari URL
$id_transaksi = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_transaksi <= 0) {
    header('Location: ../user/pesanan.php');
    exit;
}

// Ambil data transaksi
$sql = "SELECT t.id_transaksi, t.tanggal_transaksi, t.total_harga, 
        t.status_pesanan, t.metode_pembayaran, t.alamat_pengiriman,
        u.nama_lengkap, u.email, u.no_telepon
        FROM transaksi t
        JOIN users u ON t.id_user = u.id_user
        WHERE t.id_transaksi = ? AND t.id_user = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $id_transaksi, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ../user/pesanan.php');
    exit;
}

$transaksi_data = $result->fetch_assoc();

// Ambil detail transaksi
$detail_items = [];
$sql_detail = "SELECT dt.id_detail, dt.id_produk, dt.kuantitas, dt.harga_satuan, dt.subtotal,
               p.nama_produk
               FROM detail_transaksi dt
               JOIN produk p ON dt.id_produk = p.id_produk
               WHERE dt.id_transaksi = ?";
$stmt_detail = $conn->prepare($sql_detail);
$stmt_detail->bind_param('i', $id_transaksi);
$stmt_detail->execute();
$result_detail = $stmt_detail->get_result();

while ($row = $result_detail->fetch_assoc()) {
    $detail_items[] = $row;
}

// Process UPLOAD BUKTI PEMBAYARAN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_bukti') {
    if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['bukti_pembayaran'];
        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
        
        if (in_array($file['type'], $allowed_types)) {
            // Buat folder jika belum ada
            $upload_dir = '../assets/bukti-pembayaran/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Simpan file
            $filename = 'bukti_' . $id_transaksi . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            $file_path = $upload_dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                // Update transaksi dengan file bukti
                $update_sql = "UPDATE transaksi SET bukti_pembayaran = ?, status_pesanan = 'Diproses' 
                              WHERE id_transaksi = ? AND id_user = ?";
                $update_stmt = $conn->prepare($update_sql);
                $file_path_db = 'assets/bukti-pembayaran/' . $filename;
                $update_stmt->bind_param('sii', $file_path_db, $id_transaksi, $user_id);
                
                if ($update_stmt->execute()) {
                    $message = 'Bukti pembayaran berhasil diupload! Pesanan Anda sedang diproses.';
                    $message_type = 'success';
                    
                    // Update status transaksi
                    $transaksi_data['status_pesanan'] = 'Diproses';
                } else {
                    $message = 'Error: Gagal menyimpan data';
                    $message_type = 'danger';
                }
            } else {
                $message = 'Error: Gagal mengupload file';
                $message_type = 'danger';
            }
        } else {
            $message = 'Error: Tipe file tidak diizinkan (JPG, PNG, PDF)';
            $message_type = 'danger';
        }
    } else {
        $message = 'Error: Pilih file untuk diupload';
        $message_type = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proses Pembayaran - MobileNest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .payment-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }
        
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .status-pending { background-color: #ffc107; color: #000; }
        .status-diproses { background-color: #17a2b8; color: #fff; }
        .status-dikirim { background-color: #007bff; color: #fff; }
        .status-selesai { background-color: #28a745; color: #fff; }
        
        .instruction-box {
            background: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
        }
        
        .item-list {
            border-bottom: 1px solid #dee2e6;
            padding: 0.75rem 0;
        }
        
        .item-list:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include '../header.php'; ?>

    <div class="container py-5">
        <h1 class="mb-4">💳 Proses Pembayaran</h1>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <!-- Status Pesanan -->
                <div class="payment-section">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h5 class="mb-2">No. Pesanan</h5>
                            <p><strong>#<?php echo $transaksi_data['id_transaksi']; ?></strong></p>
                        </div>
                        <div class="col-md-6 text-end">
                            <h5 class="mb-2">Status</h5>
                            <span class="status-badge status-<?php echo strtolower($transaksi_data['status_pesanan']); ?>">
                                <?php echo ucfirst($transaksi_data['status_pesanan']); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Instruksi Pembayaran -->
                <?php if ($transaksi_data['status_pesanan'] === 'Pending'): ?>
                    <div class="instruction-box">
                        <h5 class="mb-3">📝 Instruksi Pembayaran</h5>
                        <ol class="mb-0">
                            <li>Lakukan pembayaran sesuai metode: <strong><?php echo htmlspecialchars($transaksi_data['metode_pembayaran']); ?></strong></li>
                            <li>Jumlah yang harus dibayar: <strong>Rp <?php echo number_format($transaksi_data['total_harga'], 0, ',', '.'); ?></strong></li>
                            <li>Gunakan No. Pesanan sebagai referensi: <strong>#<?php echo $transaksi_data['id_transaksi']; ?></strong></li>
                            <li>Upload bukti pembayaran di bawah ini</li>
                            <li>Pesanan akan diproses setelah kami verifikasi pembayaran</li>
                        </ol>
                    </div>
                <?php endif; ?>

                <!-- Detail Pesanan -->
                <div class="payment-section">
                    <h5 class="mb-3">📦 Detail Pesanan</h5>
                    <?php foreach ($detail_items as $item): ?>
                        <div class="item-list">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <strong><?php echo htmlspecialchars($item['nama_produk']); ?></strong><br>
                                    <small class="text-muted"><?php echo $item['kuantitas']; ?>x Rp <?php echo number_format($item['harga_satuan'], 0, ',', '.'); ?></small>
                                </div>
                                <div class="col-md-6 text-end">
                                    <strong>Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></strong>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Data Pengiriman -->
                <div class="payment-section">
                    <h5 class="mb-3">🚚 Informasi Pengiriman</h5>
                    <div class="row mb-2">
                        <div class="col-md-4">Penerima:</div>
                        <div class="col-md-8"><?php echo htmlspecialchars($transaksi_data['nama_lengkap']); ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4">No. Telepon:</div>
                        <div class="col-md-8"><?php echo htmlspecialchars($transaksi_data['no_telepon']); ?></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">Alamat:</div>
                        <div class="col-md-8"><?php echo htmlspecialchars($transaksi_data['alamat_pengiriman']); ?></div>
                    </div>
                </div>

                <!-- Upload Bukti Pembayaran -->
                <?php if ($transaksi_data['status_pesanan'] === 'Pending'): ?>
                    <div class="payment-section">
                        <h5 class="mb-3">📸 Upload Bukti Pembayaran</h5>
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="upload_bukti">
                            <div class="mb-3">
                                <label class="form-label">Pilih File Bukti Pembayaran (JPG, PNG, PDF) *</label>
                                <input type="file" name="bukti_pembayaran" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                                <small class="text-muted">Maksimal ukuran file: 5MB</small>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">✅ Upload Bukti</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success">
                        ✅ Terima kasih! Bukti pembayaran Anda sudah diterima. Tim kami akan memverifikasi dan segera mengirimkan pesanan Anda.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Summary -->
            <div class="col-md-4">
                <div class="payment-section">
                    <h5 class="mb-3">💰 Ringkasan Pembayaran</h5>
                    <div class="row mb-2">
                        <div class="col-6">Subtotal:</div>
                        <div class="col-6 text-end">Rp <?php echo number_format($transaksi_data['total_harga'], 0, ',', '.'); ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6">Ongkos Kirim:</div>
                        <div class="col-6 text-end">Gratis</div>
                    </div>
                    <hr>
                    <div class="row" style="font-size: 18px;">
                        <div class="col-6"><strong>Total:</strong></div>
                        <div class="col-6 text-end"><strong>Rp <?php echo number_format($transaksi_data['total_harga'], 0, ',', '.'); ?></strong></div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <strong>Metode Pembayaran:</strong><br>
                    <?php echo htmlspecialchars($transaksi_data['metode_pembayaran']); ?>
                </div>

                <a href="../user/pesanan.php" class="btn btn-outline-secondary w-100">
                    ➡️ Lihat Pesanan Lainnya
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>