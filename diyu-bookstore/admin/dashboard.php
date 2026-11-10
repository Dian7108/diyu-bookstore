
<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$activeMenu = 'dashboard';
$pageTitle = 'Dashboard Admin';

$totalBuku = $pdo->query("SELECT COUNT(*) c FROM buku")->fetch()['c'];
$totalKategori = $pdo->query("SELECT COUNT(*) c FROM kategori")->fetch()['c'];
$totalUser = $pdo->query("SELECT COUNT(*) c FROM users WHERE role='user'")->fetch()['c'];
$totalPesanan = $pdo->query("SELECT COUNT(*) c FROM pesanan")->fetch()['c'];

// Pendapatan hanya menghitung pesanan yang sudah lunas
$totalPendapatan = $pdo->query("
    SELECT COALESCE(SUM(total), 0) c
    FROM pesanan
    WHERE status_pembayaran = 'Lunas'
      AND status != 'Dibatalkan'
")->fetch()['c'];

// Pesanan terbaru
$recentOrders = $pdo->query("
    SELECT p.*, u.nama_lengkap
    FROM pesanan p
    JOIN users u ON p.user_id = u.id
    ORDER BY p.created_at DESC
    LIMIT 5
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= e($pageTitle) ?> - DiYu BookStore</title>

    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="admin-body">

    <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <main class="admin-main">

        <div class="admin-topbar">
            <h1>Dashboard</h1>

            <div class="user-chip">
                <div class="user-avatar">
                    <?= strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)) ?>
                </div>

                <span style="font-size:13px;font-weight:600">
                    <?= e($_SESSION['nama_lengkap']) ?>
                </span>
            </div>
        </div>

        <!-- STATISTIK -->
        <div class="stat-grid">

            <div class="stat-card">
                <div class="stat-icon"></div>
                <strong><?= $totalBuku ?></strong>
                <span>Total Buku</span>
            </div>

            <div class="stat-card">
                <div class="stat-icon"></div>
                <strong><?= $totalKategori ?></strong>
                <span>Total Kategori</span>
            </div>

            <div class="stat-card">
                <div class="stat-icon"></div>
                <strong><?= $totalUser ?></strong>
                <span>Total User</span>
            </div>

            <div class="stat-card">
                <div class="stat-icon"></div>
                <strong><?= $totalPesanan ?></strong>
                <span>Total Pesanan</span>
            </div>

        </div>

        <!-- PENDAPATAN -->
        <div class="admin-card" style="margin-bottom:20px">

            <h3>Pendapatan</h3>

            <strong style="font-size:26px;color:var(--primary)">
                <?= formatRupiah($totalPendapatan) ?>
            </strong>

        </div>

        <!-- PESANAN TERBARU -->
        <div class="admin-card">

            <h3>Pesanan Terbaru</h3>

            <?php foreach ($recentOrders as $o): ?>

                <div style="
                    display:flex;
                    align-items:center;
                    gap:10px;
                    padding:10px 0;
                    border-bottom:1px solid var(--border)
                ">

                    <div class="user-avatar"
                        style="width:32px;height:32px;font-size:12px">

                        <?= strtoupper(substr($o['nama_lengkap'], 0, 1)) ?>

                    </div>

                    <div style="flex:1">

                        <div style="font-size:13px;font-weight:700">
                            <?= e($o['nama_lengkap']) ?>
                        </div>

                        <div style="font-size:11px;color:var(--text-muted)">
                            <?= e($o['id_pesanan']) ?>
                        </div>

                    </div>

                    <span class="<?= statusBadgeClass($o['status']) ?>">
                        <?= e($o['status']) ?>
                    </span>

                </div>

            <?php endforeach; ?>

            <?php if (!$recentOrders): ?>

                <p style="color:var(--text-muted);font-size:13px">
                    Belum ada pesanan.
                </p>

            <?php endif; ?>

        </div>

    </main>

</div>

<script src="../assets/js/main.js"></script>

</body>
</html>