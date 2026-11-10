
<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Profil Saya';
requireLogin('profile.php');

$stmt = $pdo->prepare("
    SELECT *
    FROM pesanan
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$pesananSaya = $stmt->fetchAll();

$user = currentUser();

require_once __DIR__ . '/includes/header.php';
?>

<div class="section">

    <!-- PROFIL USER -->
    <div class="cart-card" style="
        padding:26px;
        margin-bottom:24px;
        display:flex;
        align-items:center;
        gap:16px;
    ">
        <div class="user-avatar" style="
            width:60px;
            height:60px;
            font-size:22px;
        ">
            <?= strtoupper(substr($user['nama'], 0, 1)) ?>
        </div>

        <div>
            <h2 style="font-size:18px">
                <?= e($user['nama']) ?>
            </h2>

            <p style="color:var(--text-muted);font-size:13px">
                <?= e($user['email']) ?>
            </p>
        </div>
    </div>

    <!-- RIWAYAT PESANAN -->
    <div class="section-head">
        <h2 style="font-size:18px">Riwayat Pesanan</h2>
    </div>

    <?php if (!$pesananSaya): ?>

        <p style="color:var(--text-muted)">
            Kamu belum memiliki pesanan.
        </p>

    <?php else: ?>

        <div class="cart-card" style="overflow-x:auto">

            <table class="cart-table">

                <thead>
                    <tr>
                        <th>ID Pesanan</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th>Status Pembayaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($pesananSaya as $p): ?>

                    <tr>

                        <td>
                            <b><?= e($p['id_pesanan']) ?></b>
                        </td>

                        <td>
                            <?= formatTanggal($p['created_at'], true) ?>
                        </td>

                        <td>
                            <?= formatRupiah($p['total']) ?>
                        </td>

                        <td>
                            <?= e($p['metode']) ?>
                        </td>

                        <td>
                            <span class="<?= statusBadgeClass($p['status']) ?>">
                                <?= e($p['status']) ?>
                            </span>
                        </td>

                        <td>
                            <?= e($p['status_pembayaran']) ?>
                        </td>

                        <td style="min-width:160px">

                            <?php if ($p['status_pembayaran'] === 'Lunas'): ?>

                                <a
                                    href="struk.php?id=<?= urlencode($p['id_pesanan']) ?>"
                                    class="btn btn-primary"
                                    style="font-size:12px;padding:9px 12px;white-space:nowrap"
                                >
                                    Unduh Struk PDF
                                </a>

                            <?php else: ?>

                                <a
                                    href="order-success.php?id=<?= urlencode($p['id_pesanan']) ?>"
                                    class="btn btn-outline"
                                    style="font-size:12px;padding:9px 12px;white-space:nowrap"
                                >
                                    Detail Pesanan
                                </a>

                            <?php endif; ?>

                        </td>

                    </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
