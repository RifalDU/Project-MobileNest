<?php
/**
 * ============================================
 * FILE: dashboard.php
 * PURPOSE: Admin Dashboard with Analytics
 * LOCATION: MobileNest/admin/dashboard.php
 * ============================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/auth-check.php';
require_admin_login();
require_once '../config.php';

$admin_id = $_SESSION['admin'];
$stats = [];
$errors = [];

// ========================================
// GET STATISTICS
// ========================================

// Total Orders
$total_orders_sql = "SELECT COUNT(*) as total FROM transaksi";
$result = $conn->query($total_orders_sql);
$stats['total_orders'] = $result->fetch_assoc()['total'] ?? 0;

// Total Sales This Month
$current_month = date('Y-m');
$total_sales_sql = "SELECT COALESCE(SUM(total_harga), 0) as total FROM transaksi WHERE DATE_FORMAT(tanggal_transaksi, '%Y-%m') = ?";
$stmt = $conn->prepare($total_sales_sql);
$stmt->bind_param('s', $current_month);
$stmt->execute();
$stats['total_sales'] = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Total Users
$total_users_sql = "SELECT COUNT(*) as total FROM users WHERE role = 'user'";
$result = $conn->query($total_users_sql);
$stats['total_users'] = $result->fetch_assoc()['total'] ?? 0;

// Total Products
$total_products_sql = "SELECT COUNT(*) as total FROM produk";
$result = $conn->query($total_products_sql);
$stats['total_products'] = $result->fetch_assoc()['total'] ?? 0;

// Status Breakdown
$status_breakdown_sql = "SELECT status_pesanan, COUNT(*) as count FROM transaksi GROUP BY status_pesanan";
$result = $conn->query($status_breakdown_sql);
$stats['status_breakdown'] = [];
while ($row = $result->fetch_assoc()) {
    $stats['status_breakdown'][$row['status_pesanan']] = $row['count'];
}

// Recent Orders (5)
$recent_orders_sql = "SELECT t.id_transaksi, t.tanggal_transaksi, t.total_harga, t.status_pesanan, u.nama_lengkap FROM transaksi t JOIN users u ON t.id_user = u.id_user ORDER BY t.tanggal_transaksi DESC LIMIT 5";
$result = $conn->query($recent_orders_sql);
$stats['recent_orders'] = [];
while ($row = $result->fetch_assoc()) {
    $stats['recent_orders'][] = $row;
}

// Low Stock Products (stok <= 5)
$low_stock_sql = "SELECT id_produk, nama_produk, stok FROM produk WHERE stok <= 5 ORDER BY stok ASC";
$result = $conn->query($low_stock_sql);
$stats['low_stock'] = [];
while ($row = $result->fetch_assoc()) {
    $stats['low_stock'][] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - MobileNest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #007bff;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --info: #17a2b8;
            --card-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard-container {
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

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: var(--card-shadow);
            border-top: 4px solid var(--primary);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .stat-card.success { border-top-color: var(--success); }
        .stat-card.warning { border-top-color: var(--warning); }
        .stat-card.danger { border-top-color: var(--danger); }
        .stat-card.info { border-top-color: var(--info); }

        .stat-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .stat-card.success .stat-icon { color: var(--success); }
        .stat-card.warning .stat-icon { color: var(--warning); }
        .stat-card.danger .stat-icon { color: var(--danger); }
        .stat-card.info .stat-icon { color: var(--info); }

        .stat-label {
            color: #7f8c8d;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
        }

        .content-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: var(--card-shadow);
        }

        .card-title {
            font-size: 20px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-diproses { background: #cfe2ff; color: #084298; }
        .status-dikirim { background: #d1ecf1; color: #0c5460; }
        .status-selesai { background: #d1e7dd; color: #0f5132; }
        .status-dibatalkan { background: #f8d7da; color: #842029; }

        .table-custom {
            margin: 0;
        }

        .table-custom thead th {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border: none;
            color: #2c3e50;
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 15px 10px;
        }

        .table-custom tbody td {
            padding: 15px 10px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }

        .table-custom tbody tr:hover {
            background: #f8f9fa;
        }

        .alert-custom {
            border: none;
            border-left: 4px solid;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 20px;
        }

        .alert-warning-custom {
            background: #fff8e1;
            color: #856404;
            border-left-color: #ffc107;
        }

        .progress-bar-custom {
            background: linear-gradient(135deg, var(--primary), #764ba2);
            height: 8px;
            border-radius: 10px;
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 20px 15px;
            }

            .page-header h1 {
                font-size: 24px;
            }

            .stat-value {
                font-size: 24px;
            }

            .content-card {
                padding: 20px 15px;
            }
        }
    </style>
</head>
<body>
    <?php include '../header.php'; ?>

    <div class="dashboard-container">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <h1>
                    <i class="fas fa-chart-line"></i> Dashboard Admin
                </h1>
                <p>Kelola dan pantau seluruh aktivitas toko Anda</p>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                        <div class="stat-label">Total Pesanan</div>
                        <div class="stat-value"><?php echo $stats['total_orders']; ?></div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card success">
                        <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                        <div class="stat-label">Penjualan Bulan Ini</div>
                        <div class="stat-value">Rp <?php echo number_format($stats['total_sales'], 0, ',', '.'); ?></div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card warning">
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                        <div class="stat-label">Total User</div>
                        <div class="stat-value"><?php echo $stats['total_users']; ?></div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card info">
                        <div class="stat-icon"><i class="fas fa-box"></i></div>
                        <div class="stat-label">Total Produk</div>
                        <div class="stat-value"><?php echo $stats['total_products']; ?></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Status Breakdown -->
                <div class="col-lg-6">
                    <div class="content-card">
                        <div class="card-title">
                            <i class="fas fa-chart-pie"></i> Breakdown Status Pesanan
                        </div>
                        <?php foreach ($stats['status_breakdown'] as $status => $count): ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '', $status)); ?>">
                                        <?php echo htmlspecialchars($status); ?>
                                    </span>
                                    <strong><?php echo $count; ?> pesanan</strong>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar-custom" style="width: <?php echo ($count / $stats['total_orders']) * 100; ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Low Stock Alert -->
                <div class="col-lg-6">
                    <div class="content-card">
                        <div class="card-title">
                            <i class="fas fa-exclamation-triangle"></i> Produk Stok Rendah
                        </div>
                        <?php if (!empty($stats['low_stock'])): ?>
                            <div class="alert-custom alert-warning-custom">
                                <i class="fas fa-info-circle"></i> <strong><?php echo count($stats['low_stock']); ?> produk</strong> memiliki stok kurang dari 5
                            </div>
                            <div class="table-responsive">
                                <table class="table table-custom">
                                    <thead>
                                        <tr>
                                            <th>Nama Produk</th>
                                            <th class="text-center">Stok</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($stats['low_stock'] as $product): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($product['nama_produk']); ?></td>
                                                <td class="text-center">
                                                    <strong style="color: #dc3545;"><?php echo $product['stok']; ?></strong>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div style="text-align: center; padding: 30px; color: #7f8c8d;">
                                <i class="fas fa-check-circle" style="font-size: 40px; color: #28a745; margin-bottom: 10px; display: block;"></i>
                                <p>Semua produk memiliki stok yang cukup</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="row">
                <div class="col-12">
                    <div class="content-card">
                        <div class="card-title">
                            <i class="fas fa-history"></i> Pesanan Terbaru
                        </div>
                        <div class="table-responsive">
                            <table class="table table-custom">
                                <thead>
                                    <tr>
                                        <th>ID Pesanan</th>
                                        <th>User</th>
                                        <th>Tanggal</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($stats['recent_orders'])): ?>
                                        <?php foreach ($stats['recent_orders'] as $order): ?>
                                            <tr>
                                                <td><strong>#<?php echo $order['id_transaksi']; ?></strong></td>
                                                <td><?php echo htmlspecialchars(substr($order['nama_lengkap'], 0, 25)); ?></td>
                                                <td><?php echo date('d M Y', strtotime($order['tanggal_transaksi'])); ?></td>
                                                <td><strong>Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></strong></td>
                                                <td>
                                                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '', $order['status_pesanan'])); ?>">
                                                        <?php echo htmlspecialchars($order['status_pesanan']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox" style="font-size: 30px;"></i>
                                                <p>Belum ada pesanan</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
