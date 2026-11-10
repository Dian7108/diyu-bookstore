
<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$activeMenu = 'orders';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT p.*, u.nama_lengkap, u.email
    FROM pesanan p
    JOIN users u ON p.user_id = u.id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: orders.php');
    exit;
}

$error = '';
$success = '';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'status_pesanan') {
        $statusBaru = $_POST['status'] ?? '';

        if (in_array($statusBaru, $statusList, true)) {
            $update = $pdo->prepare("
                UPDATE pesanan
                SET status = ?
                WHERE id = ?
            ");
            $update->execute([$statusBaru, $id]);

            $success = 'Status pesanan berhasil diperbarui.';
        } else {
            $error = 'Status pesanan tidak valid.';
        }
    }

    if ($aksi === 'status_pembayaran') {
        $statusBayar = $_POST['status_pembayaran'] ?? '';

        if (!in_array($statusBayar, $statusPembayaranList, true)) {
            $error = 'Status pembayaran tidak valid.';
        } elseif (
            $statusBayar === 'Lunas' &&
            $order['status_pembayaran'] !== 'Lunas'
        ) {
            if (
                $order['metode'] === 'Transfer Bank' &&
                empty($order['bukti_transfer'])
            ) {
                $error = 'Belum ada bukti transfer yang diunggah pembeli.';
            } else {
                $update = $pdo->prepare("
                    UPDATE pesanan
                    SET status_pembayaran = ?
                    WHERE id = ?
                ");
                $update->execute([$statusBayar, $id]);

                $success = 'Pembayaran berhasil dikonfirmasi lunas.';
            }
        } else {
            $update = $pdo->prepare("
                UPDATE pesanan
                SET status_pembayaran = ?
                WHERE id = ?
            ");
            $update->execute([$statusBayar, $id]);

            $success = 'Status pembayaran berhasil diperbarui.';
        }
    }

    $stmt->execute([$id]);
    $order = $stmt->fetch();
}

$itemStmt = $pdo->prepare("
    SELECT *
    FROM pesanan_detail
    WHERE pesanan_id = ?
");
$itemStmt->execute([$id]);
$items = $itemStmt->fetchAll();

$pageTitle = 'Detail Pesanan ' . $order['id_pesanan'];
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
            <h1>Detail Pesanan <?= e($order['id_pesanan']) ?></h1>
            <a href="orders.php" class="btn btn-outline btn-sm">
                &larr; Kembali
            </a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger" style="margin-bottom:20px">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success" style="margin-bottom:20px">
                <?= e($success) ?>
            </div>
        <?php endif; ?>

        <div class="admin-grid-2">

            <div class="admin-card">

                <h3>Item Pesanan</h3>

                <div style="overflow-x:auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Buku</th>
                                <th>Harga</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($items as $it): ?>
                                <tr>
                                    <td><?= e($it['judul_buku']) ?></td>
                                    <td><?= formatRupiah($it['harga']) ?></td>
                                    <td><?= (int)$it['qty'] ?></td>
                                    <td><?= formatRupiah($it['subtotal']) ?></td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (!$items): ?>
                                <tr>
                                    <td colspan="4" style="text-align:center">
                                        Tidak ada item pesanan.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div style="margin-top:16px;border-top:1px solid var(--border);padding-top:12px">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span><?= formatRupiah($order['subtotal']) ?></span>
                    </div>

                    <div class="summary-row">
                        <span>Ongkir</span>
                        <span><?= formatRupiah($order['ongkir']) ?></span>
                    </div>

                    <div class="summary-row total">
                        <span>Total</span>
                        <span><?= formatRupiah($order['total']) ?></span>
                    </div>
                </div>

            </div>

            <div>

                <div class="admin-card" style="margin-bottom:20px">

                    <h3>Info Penerima</h3>

                    <p style="font-size:13px;margin-bottom:8px">
                        <b>Nama:</b> <?= e($order['nama_penerima']) ?>
                    </p>

                    <p style="font-size:13px;margin-bottom:8px">
                        <b>Email:</b> <?= e($order['email']) ?>
                    </p>

                    <p style="font-size:13px;margin-bottom:8px">
                        <b>Telepon:</b> <?= e($order['no_telp']) ?>
                    </p>

                    <p style="font-size:13px;margin-bottom:8px">
                        <b>Alamat:</b> <?= e($order['alamat']) ?>
                    </p>

                    <?php if ($order['catatan']): ?>
                        <p style="font-size:13px;margin-bottom:8px">
                            <b>Catatan:</b> <?= e($order['catatan']) ?>
                        </p>
                    <?php endif; ?>

                    <p style="font-size:13px;margin-bottom:8px">
                        <b>Metode Pembayaran:</b> <?= e($order['metode']) ?>
                    </p>

                    <p style="font-size:13px">
                        <b>Status Pembayaran:</b>
                        <?= e($order['status_pembayaran']) ?>
                    </p>

                </div>

                <div class="admin-card" style="margin-bottom:20px">

                    <h3>Bukti Pembayaran</h3>

                    <?php if ($order['metode'] === 'Transfer Bank'): ?>

                        <?php if (!empty($order['bukti_transfer'])): ?>

                            <p style="font-size:13px;margin-bottom:12px">
                                Pembeli sudah mengunggah bukti transfer.
                            </p>

                            <a
                                href="../uploads/bukti-transfer/<?= rawurlencode(basename($order['bukti_transfer'])) ?>"
                                target="_blank"
                                rel="noopener"
                                class="btn btn-outline btn-sm"
                            >
                                Lihat Bukti Transfer
                            </a>

                        <?php else: ?>

                            <p style="font-size:13px;color:var(--text-muted)">
                                Pembeli belum mengunggah bukti transfer.
                            </p>

                        <?php endif; ?>

                    <?php else: ?>

                        <p style="font-size:13px;color:var(--text-muted)">
                            Metode pembayaran adalah COD. Konfirmasi pembayaran setelah uang diterima.
                        </p>

                    <?php endif; ?>

                </div>

                <div class="admin-card" style="margin-bottom:20px">

                    <h3>Status Pembayaran</h3>

                    <p style="margin-bottom:14px">
                        <b><?= e($order['status_pembayaran']) ?></b>
                    </p>

                    <form method="post" action="order_detail.php?id=<?= $id ?>">

                        <input type="hidden" name="aksi" value="status_pembayaran">

                        <div class="form-group">
                            <label>Ubah Status Pembayaran</label>

                            <select name="status_pembayaran">
                                <?php foreach ($statusPembayaranList as $s): ?>
                                    <option
                                        value="<?= e($s) ?>"
                                        <?= $order['status_pembayaran'] === $s ? 'selected' : '' ?>
                                    >
                                        <?= e($s) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            Simpan Pembayaran
                        </button>

                    </form>

                </div>

                <div class="admin-card">

                    <h3>Status Pesanan</h3>

                    <p style="margin-bottom:14px">
                        <span class="<?= statusBadgeClass($order['status']) ?>">
                            <?= e($order['status']) ?>
                        </span>
                    </p>

                    <form method="post" action="order_detail.php?id=<?= $id ?>">

                        <input type="hidden" name="aksi" value="status_pesanan">

                        <div class="form-group">
                            <label>Ubah Status Pesanan</label>

                            <select name="status">
                                <?php foreach ($statusList as $s): ?>
                                    <option
                                        value="<?= e($s) ?>"
                                        <?= $order['status'] === $s ? 'selected' : '' ?>
                                    >
                                        <?= e($s) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            Simpan Status Pesanan
                        </button>

                    </form>

                </div>

            </div>
        </div>

    </main>
</div>

<script src="../assets/js/main.js"></script>
</body>
</html>
