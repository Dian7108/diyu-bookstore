
<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$activeMenu = 'orders';
$pageTitle = 'List Pesanan User';

$q       = trim($_GET['q'] ?? '');
$status  = $_GET['status'] ?? '';
$bayar   = $_GET['bayar'] ?? '';

$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset  = ($page - 1) * $perPage;

$where = [];
$params = [];

if ($q !== '') {
    $where[] = "(p.id_pesanan LIKE ? OR u.nama_lengkap LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

if (in_array($status, ['Menunggu', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'], true)) {
    $where[] = "p.status = ?";
    $params[] = $status;
} else {
    $status = '';
}

if (in_array($bayar, ['Belum Dibayar', 'Menunggu Verifikasi', 'Lunas'], true)) {
    $where[] = "p.status_pembayaran = ?";
    $params[] = $bayar;
} else {
    $bayar = '';
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$totalRow = $pdo->prepare("
    SELECT COUNT(*) AS c
    FROM pesanan p
    JOIN users u ON p.user_id = u.id
    $whereSql
");
$totalRow->execute($params);

$totalData = (int)$totalRow->fetch()['c'];
$totalPages = max(1, (int)ceil($totalData / $perPage));

$sql = "
    SELECT p.*, u.nama_lengkap, u.email
    FROM pesanan p
    JOIN users u ON p.user_id = u.id
    $whereSql
    ORDER BY p.created_at DESC
    LIMIT $perPage OFFSET $offset
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$statusList = [
    'Menunggu',
    'Diproses',
    'Dikirim',
    'Selesai',
    'Dibatalkan'
];

$statusPembayaranList = [
    'Belum Dibayar',
    'Menunggu Verifikasi',
    'Lunas'
];
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
            <h1>List Pesanan User</h1>

            <div class="user-chip">
                <div class="user-avatar">
                    <?= strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)) ?>
                </div>
                <span style="font-size:13px;font-weight:600">
                    <?= e($_SESSION['nama_lengkap']) ?>
                </span>
            </div>
        </div>

        <div class="admin-card">

            <div class="table-toolbar">

                <form class="table-search" method="get" action="orders.php" style="flex:1">
                    <span>&#128269;</span>

                    <input
                        type="text"
                        name="q"
                        placeholder="Cari ID Pesanan, nama user..."
                        value="<?= e($q) ?>"
                    >

                    <input type="hidden" name="status" value="<?= e($status) ?>">
                    <input type="hidden" name="bayar" value="<?= e($bayar) ?>">
                </form>

                <form method="get" action="orders.php">
                    <input type="hidden" name="q" value="<?= e($q) ?>">
                    <input type="hidden" name="bayar" value="<?= e($bayar) ?>">

                    <select
                        name="status"
                        onchange="this.form.submit()"
                        style="padding:9px 14px;border-radius:8px;border:1px solid var(--border);font-size:13px"
                    >
                        <option value="">Semua Status Pesanan</option>

                        <?php foreach ($statusList as $s): ?>
                            <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>>
                                <?= e($s) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <form method="get" action="orders.php">
                    <input type="hidden" name="q" value="<?= e($q) ?>">
                    <input type="hidden" name="status" value="<?= e($status) ?>">

                    <select
                        name="bayar"
                        onchange="this.form.submit()"
                        style="padding:9px 14px;border-radius:8px;border:1px solid var(--border);font-size:13px"
                    >
                        <option value="">Semua Pembayaran</option>

                        <?php foreach ($statusPembayaranList as $s): ?>
                            <option value="<?= e($s) ?>" <?= $bayar === $s ? 'selected' : '' ?>>
                                <?= e($s) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>

            </div>

            <div style="overflow-x:auto">
                <table class="data-table">

                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID Pesanan</th>
                            <th>User</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Metode</th>
                            <th>Status Pesanan</th>
                            <th>Status Pembayaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($orders as $i => $o): ?>
                            <tr>
                                <td><?= $offset + $i + 1 ?></td>

                                <td>
                                    <b><?= e($o['id_pesanan']) ?></b>
                                </td>

                                <td>
                                    <div style="display:flex;align-items:center;gap:8px">
                                        <div class="user-avatar" style="width:26px;height:26px;font-size:11px">
                                            <?= strtoupper(substr($o['nama_lengkap'], 0, 1)) ?>
                                        </div>

                                        <?= e($o['nama_lengkap']) ?>
                                    </div>
                                </td>

                                <td>
                                    <?= formatTanggal($o['created_at'], true) ?>
                                </td>

                                <td>
                                    <?= formatRupiah($o['total']) ?>
                                </td>

                                <td>
                                    <?= e($o['metode']) ?>
                                </td>

                                <td>
                                    <span class="<?= statusBadgeClass($o['status']) ?>">
                                        <?= e($o['status']) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="<?= $o['status_pembayaran'] === 'Lunas' ? 'badge-success' : 'badge-warning' ?>">
                                        <?= e($o['status_pembayaran']) ?>
                                    </span>
                                </td>

                                <td>
                                    <a
                                        href="order_detail.php?id=<?= (int)$o['id'] ?>"
                                        class="btn btn-outline btn-sm"
                                    >
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (!$orders): ?>
                            <tr>
                                <td colspan="9" style="text-align:center;color:var(--text-muted)">
                                    Tidak ada pesanan ditemukan.
                                </td>
                            </tr>
                        <?php endif; ?>

                    </tbody>
                </table>
            </div>

            <div class="pagination">

                <span>
                    Menampilkan <?= count($orders) ?> dari <?= $totalData ?> pesanan
                </span>

                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <a
                        href="orders.php?q=<?= urlencode($q) ?>&status=<?= urlencode($status) ?>&bayar=<?= urlencode($bayar) ?>&page=<?= $p ?>"
                        class="<?= $p === $page ? 'active' : '' ?>"
                    >
                        <?= $p ?>
                    </a>
                <?php endfor; ?>

            </div>

        </div>

    </main>
</div>

<script src="../assets/js/main.js"></script>
</body>
</html>
