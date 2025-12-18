<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/auth-check.php';
require_user_login();
require_once '../config.php';

$user_id = $_SESSION['user'];
$transaksi = [];
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'semua';
$message = '';

if ($filter_status === 'semua') {
    $sql = "SELECT t.id_transaksi, t.tanggal_transaksi, t.total_harga, t.status_pesanan, t.metode_pembayaran, t.no_resi, GROUP_CONCAT(p.nama_produk SEPARATOR ', ') as produk_list, COUNT(dt.id_detail) as jumlah_item FROM transaksi t LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi LEFT JOIN produk p ON dt.id_produk = p.id_produk WHERE t.id_user = ? GROUP BY t.id_transaksi ORDER BY t.tanggal_transaksi DESC";
} else {
    $sql = "SELECT t.id_transaksi, t.tanggal_transaksi, t.total_harga, t.status_pesanan, t.metode_pembayaran, t.no_resi, GROUP_CONCAT(p.nama_produk SEPARATOR ', ') as produk_list, COUNT(dt.id_detail) as jumlah_item FROM transaksi t LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi LEFT JOIN produk p ON dt.id_produk = p.id_produk WHERE t.id_user = ? AND t.status_pesanan = ? GROUP BY t.id_transaksi ORDER BY t.tanggal_transaksi DESC";
}

$stmt = $conn->prepare($sql);
if (!$stmt) { die("Prepare failed: " . $conn->error); }
if ($filter_status === 'semua') {
    $stmt->bind_param('i', $user_id);
} else {
    $stmt->bind_param('is', $user_id, $filter_status);
}
if (!$stmt->execute()) { die("Execute failed: " . $stmt->error); }
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) { $transaksi[] = $row; }
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'batal') {
    $id_transaksi = intval($_POST['id_transaksi']);
    $check_sql = "SELECT id_transaksi, status_pesanan FROM transaksi WHERE id_transaksi = ? AND id_user = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('ii', $id_transaksi, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        $pesanan = $check_result->fetch_assoc();
        if ($pesanan['status_pesanan'] === 'Pending') {
            $update_sql = "UPDATE transaksi SET status_pesanan = 'Dibatalkan' WHERE id_transaksi = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param('i', $id_transaksi);
            if ($update_stmt->execute()) {
                $_SESSION['success'] = 'Pesanan berhasil dibatalkan!';
                header('Location: pesanan.php');
                exit;
            } else {
                $message = 'Error: ' . $update_stmt->error;
            }
            $update_stmt->close();
        } else {
            $message = 'Pesanan tidak bisa dibatalkan karena sudah diproses!';
        }
    }
    $check_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - MobileNest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #007bff;
            --primary-dark: #0056b3;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --card-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .pesanan-container {
            padding: 40px 20px;
        }

        .page-header {
            background: white;
            border-radius: 20px;
            padding: 35px 30px;
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
        }

        .page-header h1 {
            font-size: 32px;
            color: #2c3e50;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .page-header p {
            color: #7f8c8d;
            margin: 10px 0 0 0;
            font-size: 15px;
        }

        /* Filter Buttons */
        .filter-section {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
        }

        .filter-btn {
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            margin-right: 10px;
            margin-bottom: 10px;
            font-weight: 600;
            transition: var(--transition);
            cursor: pointer;
            font-size: 14px;
        }

        .filter-btn {
            background: #f0f3f7;
            color: #7f8c8d;
        }

        .filter-btn:hover {
            background: #e8ecf1;
            transform: translateY(-2px);
        }

        .filter-btn.active {
            background: linear-gradient(135deg, var(--primary), #764ba2);
            color: white;
        }

        /* Order Card */
        .order-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--card-shadow);
            border-left: 5px solid var(--primary);
            transition: var(--transition);
        }

        .order-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .order-id {
            font-size: 16px;
            font-weight: 700;
            color: #2c3e50;
        }

        .order-date {
            font-size: 14px;
            color: #7f8c8d;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-diproses {
            background: #cfe2ff;
            color: #084298;
        }

        .status-dikirim {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-selesai {
            background: #d1e7dd;
            color: #0f5132;
        }

        .status-dibatalkan {
            background: #f8d7da;
            color: #842029;
        }

        .order-body {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
            padding: 15px 0;
        }

        .order-info {
            display: flex;
            flex-direction: column;
        }

        .order-info-label {
            font-size: 12px;
            color: #95a5a6;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .order-info-value {
            font-size: 16px;
            color: #2c3e50;
            font-weight: 600;
        }

        .order-total {
            font-size: 20px;
            color: var(--primary);
        }

        .order-footer {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-action {
            border: none;
            border-radius: 10px;
            padding: 10px 16px;
            font-weight: 600;
            transition: var(--transition);
            cursor: pointer;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-detail {
            background: linear-gradient(135deg, var(--primary), #764ba2);
            color: white;
        }

        .btn-detail:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }

        .btn-cancel {
            background: linear-gradient(135deg, #f093fb, #f5576c);
            color: white;
        }

        .btn-cancel:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(245, 87, 108, 0.3);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state-icon {
            font-size: 60px;
            color: #bdc3c7;
            margin-bottom: 15px;
        }

        .empty-state-text {
            color: #7f8c8d;
            margin-bottom: 20px;
        }

        .empty-state-link {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary), #764ba2);
            color: white;
            padding: 12px 30px;
            border-radius: 10px;
            text-decoration: none;
            transition: var(--transition);
        }

        .empty-state-link:hover {
            transform: translateY(-2px);
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

        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .order-body {
                grid-template-columns: 1fr;
            }

            .filter-section {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <?php include '../header.php'; ?>

    <div class="pesanan-container">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <h1>
                    <i class="fas fa-box"></i> Pesanan Saya
                </h1>
                <p>Kelola dan pantau status pesanan Anda di sini</p>
            </div>

            <!-- Notifications -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success-modern alert-modern">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if ($message): ?>
                <div class="alert alert-danger alert-modern" style="background: #f8d7da; color: #721c24; border-left-color: #dc3545;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Filter Section -->
            <div class="filter-section">
                <strong style="display: block; margin-bottom: 15px; color: #2c3e50;">Filter Status:</strong>
                <a href="pesanan.php?status=semua" class="filter-btn <?php echo $filter_status === 'semua' ? 'active' : ''; ?>">
                    <i class="fas fa-list"></i> Semua
                </a>
                <a href="pesanan.php?status=Pending" class="filter-btn <?php echo $filter_status === 'Pending' ? 'active' : ''; ?>">
                    <i class="fas fa-hourglass-half"></i> Pending
                </a>
                <a href="pesanan.php?status=Diproses" class="filter-btn <?php echo $filter_status === 'Diproses' ? 'active' : ''; ?>">
                    <i class="fas fa-spinner"></i> Diproses
                </a>
                <a href="pesanan.php?status=Dikirim" class="filter-btn <?php echo $filter_status === 'Dikirim' ? 'active' : ''; ?>">
                    <i class="fas fa-truck"></i> Dikirim
                </a>
                <a href="pesanan.php?status=Selesai" class="filter-btn <?php echo $filter_status === 'Selesai' ? 'active' : ''; ?>">
                    <i class="fas fa-check"></i> Selesai
                </a>
            </div>

            <!-- Orders List -->
            <?php if (!empty($transaksi)): ?>
                <?php foreach ($transaksi as $item): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <div class="order-id">#<?php echo $item['id_transaksi']; ?></div>
                                <div class="order-date">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('d M Y, H:i', strtotime($item['tanggal_transaksi'])); ?>
                                </div>
                            </div>
                            <span class="status-badge status-<?php echo strtolower(str_replace(' ', '', $item['status_pesanan'])); ?>">
                                <?php echo htmlspecialchars($item['status_pesanan']); ?>
                            </span>
                        </div>

                        <div class="order-body">
                            <div class="order-info">
                                <div class="order-info-label">📦 Jumlah Item</div>
                                <div class="order-info-value"><?php echo $item['jumlah_item']; ?> Item</div>
                            </div>
                            <div class="order-info">
                                <div class="order-info-label">💳 Metode Pembayaran</div>
                                <div class="order-info-value"><?php echo ucfirst($item['metode_pembayaran']); ?></div>
                            </div>
                            <div class="order-info">
                                <div class="order-info-label">💰 Total Pembayaran</div>
                                <div class="order-info-value order-total">Rp <?php echo number_format($item['total_harga'], 0, ',', '.'); ?></div>
                            </div>
                        </div>

                        <div class="order-footer">
                            <button class="btn-action btn-detail" data-bs-toggle="modal" data-bs-target="#detailModal" onclick="showDetail(<?php echo htmlspecialchars(json_encode($item)); ?>)">
                                <i class="fas fa-eye"></i> Lihat Detail
                            </button>
                            <?php if ($item['status_pesanan'] === 'Pending'): ?>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?');">
                                    <input type="hidden" name="action" value="batal">
                                    <input type="hidden" name="id_transaksi" value="<?php echo $item['id_transaksi']; ?>">
                                    <button type="submit" class="btn-action btn-cancel">
                                        <i class="fas fa-times"></i> Batalkan
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="background: white; border-radius: 15px; box-shadow: var(--card-shadow);">
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-inbox"></i>
                        </div>
                        <p class="empty-state-text">Anda belum memiliki pesanan</p>
                        <a href="../MobileNest/cari-produk.php" class="empty-state-link">
                            <i class="fas fa-shopping-bag"></i> Mulai Belanja Sekarang
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Detail -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: var(--card-shadow);">
                <div class="modal-header" style="background: linear-gradient(135deg, var(--primary), #764ba2); color: white; border-radius: 20px 20px 0 0; border: none;">
                    <h5 class="modal-title"><i class="fas fa-receipt"></i> Detail Pesanan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding: 30px;">
                    <div id="detailContent"></div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showDetail(pesanan) {
            const statusClass = 'status-' + pesanan.status_pesanan.toLowerCase().replace(' ', '');
            const html = `
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="order-info">
                            <div class="order-info-label">📋 ID Pesanan</div>
                            <div class="order-info-value">#${pesanan.id_transaksi}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="order-info">
                            <div class="order-info-label">📅 Tanggal</div>
                            <div class="order-info-value">${new Date(pesanan.tanggal_transaksi).toLocaleDateString('id-ID')}</div>
                        </div>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="order-info">
                            <div class="order-info-label">🏷️ Status</div>
                            <div style="margin-top: 8px;"><span class="status-badge ${statusClass}">${pesanan.status_pesanan}</span></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="order-info">
                            <div class="order-info-label">💳 Metode Pembayaran</div>
                            <div class="order-info-value">${pesanan.metode_pembayaran}</div>
                        </div>
                    </div>
                </div>
                ${pesanan.no_resi ? `
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="order-info">
                            <div class="order-info-label">📦 No. Resi</div>
                            <div class="order-info-value">${pesanan.no_resi}</div>
                        </div>
                    </div>
                </div>
                ` : ''}
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="order-info">
                            <div class="order-info-label">🛍️ Produk</div>
                            <div class="order-info-value">${pesanan.produk_list || 'N/A'}</div>
                        </div>
                    </div>
                </div>
                <hr style="border-color: #e0e6ed;">
                <div class="row">
                    <div class="col-12">
                        <div class="order-info">
                            <div class="order-info-label">💰 Total Pembayaran</div>
                            <div class="order-info-value order-total">Rp ${pesanan.total_harga.toLocaleString('id-ID')}</div>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('detailContent').innerHTML = html;
        }

        // Auto hide alerts
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
