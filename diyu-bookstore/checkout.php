<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Checkout';

// Data Beli Sekarang
$buyNowId = (int)($_GET['buy_now'] ?? $_POST['buy_now'] ?? 0);
$buyNowQty = max(1, (int)($_GET['qty'] ?? $_POST['buy_now_qty'] ?? 1));

$returnTo = 'checkout.php';

if ($buyNowId > 0) {
    $returnTo .= '?buy_now=' . $buyNowId . '&qty=' . $buyNowQty;
}

requireLogin($returnTo);

// Ambil data pesanan
if ($buyNowId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM buku WHERE id = ?");
    $stmt->execute([$buyNowId]);
    $buku = $stmt->fetch();

    if (!$buku || $buyNowQty > (int)$buku['stok']) {
        header('Location: product-detail.php?id=' . $buyNowId);
        exit;
    }

    $buku['qty'] = $buyNowQty;
    $items = [$buku];

} else {
    $stmt = $pdo->prepare("
        SELECT ke.id AS keranjang_id, ke.qty, b.*
        FROM keranjang ke
        JOIN buku b ON ke.buku_id = b.id
        WHERE ke.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $items = $stmt->fetchAll();
}

if (!$items) {
    header('Location: cart.php');
    exit;
}

// Hitung subtotal
$subtotal = 0;

foreach ($items as $it) {
    $subtotal += hargaSetelahDiskon($it['harga'], $it['diskon']) * $it['qty'];
}

// Pengaturan Gratis Ongkir Otomatis
// Ubah angka ini jika ingin mengganti batas minimal pembelian.
$minimalGratisOngkir = 150000;
$ongkirNormal = 15000;

$gratisOngkir = $subtotal >= $minimalGratisOngkir;
$ongkir = $gratisOngkir ? 0 : $ongkirNormal;
$sisaGratisOngkir = max(0, $minimalGratisOngkir - $subtotal);
$total = $subtotal + $ongkir;
$error = '';

// Proses pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

$nama = trim($_POST['nama_lengkap'] ?? '');
    $telp = trim($_POST['no_telp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $catatan = trim($_POST['catatan'] ?? '');
    $metode = $_POST['metode'] ?? '';
    $bankTujuan = $_POST['bank_tujuan'] ?? '';

    if ($nama === '' || $telp === '' || $alamat === '') {
        $error = 'Nama, nomor telepon, dan alamat wajib diisi.';

    } elseif (!in_array($metode, ['COD', 'Transfer Bank'], true)) {
        $error = 'Metode pembayaran tidak valid.';

    } elseif (
        $metode === 'Transfer Bank' &&
        !in_array($bankTujuan, ['BNI', 'BCA'], true)
    ) {
        $error = 'Silakan pilih bank tujuan transfer.';

    } else {

    if ($metode === 'COD') {
            $bankTujuan = null;
        }

        try {
            $pdo->beginTransaction();

            $orderId = generateOrderId($pdo);

            $stmtOrder = $pdo->prepare("
                INSERT INTO pesanan (
                    id_pesanan, user_id, nama_penerima,
                    no_telp, alamat, catatan, subtotal,
                    ongkir, total, metode, bank_tujuan,
                    status, status_pembayaran
                )
                VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                    'Menunggu', 'Belum Dibayar'
                )
            ");

            $stmtOrder->execute([
                $orderId,
                $_SESSION['user_id'],
                $nama,
                $telp,
                $alamat,
                $catatan,
                $subtotal,
                $ongkir,
                $total,
                $metode,
                $bankTujuan
            ]);

            $pesananId = $pdo->lastInsertId();

            $stmtDetail = $pdo->prepare("
                INSERT INTO pesanan_detail (
                    pesanan_id, buku_id, judul_buku,
                    harga, qty, subtotal
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmtStok = $pdo->prepare("
                UPDATE buku
                SET stok = stok - ?
                WHERE id = ? AND stok >= ?
            ");

            foreach ($items as $it) {

                $hargaFinal = hargaSetelahDiskon(
                    $it['harga'],
                    $it['diskon']
                );

                $stmtStok->execute([
                    $it['qty'],
                    $it['id'],
                    $it['qty']
                ]);

                if ($stmtStok->rowCount() === 0) {
                    throw new Exception('Stok buku tidak mencukupi.');
                }

                $stmtDetail->execute([
                    $pesananId,
                    $it['id'],
                    $it['judul'],
                    $hargaFinal,
                    $it['qty'],
                    $hargaFinal * $it['qty']
                ]);
            }

            if ($buyNowId <= 0) {
                $pdo->prepare("
                    DELETE FROM keranjang
                    WHERE user_id = ?
                ")->execute([$_SESSION['user_id']]);
            }

            $pdo->commit();

            header('Location: order-success.php?id=' . urlencode($orderId));
            exit;

        } catch (Exception $ex) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error = $ex->getMessage() === 'Stok buku tidak mencukupi.'
                ? $ex->getMessage()
                : 'Terjadi kesalahan saat memproses pesanan. Coba lagi.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';

$user = currentUser();

$metodeDipilih = $_POST['metode'] ?? 'COD';
$bankDipilih = $_POST['bank_tujuan'] ?? '';
?>

<div class="section">

    <div class="section-head">
        <h2>Checkout</h2>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger" style="max-width:1200px">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="checkout.php">

        <?php if ($buyNowId > 0): ?>
            <input type="hidden" name="buy_now" value="<?= $buyNowId ?>">
            <input type="hidden" name="buy_now_qty" value="<?= $buyNowQty ?>">
        <?php endif; ?>

        <div class="checkout-layout">

            <!-- INFORMASI PENGIRIMAN -->
            <div class="checkout-card">

                <h3>Informasi Pengiriman</h3>

                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" required
                        value="<?= e($_POST['nama_lengkap'] ?? $user['nama']) ?>">
                </div>

                <div class="form-group">
                    <label>No. Telepon</label>
                    <input type="text" name="no_telp"
                        placeholder="0812-3456-7890" required
                        value="<?= e($_POST['no_telp'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Alamat Lengkap</label>
                    <textarea name="alamat" rows="3"
                        placeholder="Masukkan alamat lengkap"
                        required><?= e($_POST['alamat'] ?? '') ?></textarea>
                </div>

                

                <h3 style="margin-top:22px">Metode Pembayaran</h3>

                <label class="payment-option" style="margin-top:10px">
                    <input type="radio" name="metode" value="Transfer Bank"
                        <?= $metodeDipilih === 'Transfer Bank' ? 'checked' : '' ?>
                        style="margin-top:4px">

                    <div>
                        <strong>Transfer Bank</strong>
                        <span>Transfer ke rekening toko sebelum pengiriman.</span>
                    </div>
                </label>

                <!-- PILIH BANK -->
                <div id="bankTujuanGroup"
                    style="margin-top:18px; display:<?= $metodeDipilih === 'Transfer Bank' ? 'block' : 'none' ?>;">

                    <h4 style="margin-bottom:10px">Pilih Bank Tujuan</h4>

                    <div style="display:flex; gap:12px; flex-wrap:wrap;">

                        <label class="payment-option"
                            style="flex:1; min-width:120px; cursor:pointer;">

                            <input type="radio" name="bank_tujuan"
                                value="BNI"
                                <?= $bankDipilih === 'BNI' ? 'checked' : '' ?>>

                            <div>
                                <strong>BNI</strong>
                                <span>Bank Negara Indonesia</span>
                            </div>
                        </label>

                        <label class="payment-option"
                            style="flex:1; min-width:120px; cursor:pointer;">

                            <input type="radio" name="bank_tujuan"
                                value="BCA"
                                <?= $bankDipilih === 'BCA' ? 'checked' : '' ?>>

                            <div>
                                <strong>BCA</strong>
                                <span>Bank Central Asia</span>
                            </div>
                        </label>

                    </div>
                </div>

            </div>

            <!-- RINGKASAN PESANAN -->
            <div class="checkout-card">

                <h3>Ringkasan Pesanan</h3>

                <?php foreach ($items as $it): ?>

                    <div class="order-item-row">

                        <img src="<?= bookCover($it) ?>"
                            alt="<?= e($it['judul']) ?>">

                        <div>
                            <div class="oi-name">
                                <?= e($it['judul']) ?>
                            </div>

                            <div class="oi-qty">
                                x<?= (int)$it['qty'] ?>
                            </div>
                        </div>

                        <div class="oi-price">
                            <?= formatRupiah(
                                hargaSetelahDiskon(
                                    $it['harga'],
                                    $it['diskon']
                                ) * $it['qty']
                            ) ?>
                        </div>

                    </div>

                <?php endforeach; ?>

                <div class="summary-row" style="margin-top:14px">
                    <span>Subtotal</span>
                    <span><?= formatRupiah($subtotal) ?></span>
                </div>

                <div class="summary-row">
                    <span>Ongkir</span>
                    <span>
                        <?php if ($gratisOngkir): ?>
                            <del style="color:var(--text-muted);margin-right:6px"><?= formatRupiah($ongkirNormal) ?></del>
                            <strong style="color:#16a34a">GRATIS</strong>
                        <?php else: ?>
                            <?= formatRupiah($ongkir) ?>
                        <?php endif; ?>
                    </span>
                </div>

                <?php if ($gratisOngkir): ?>
                    <div class="alert alert-success" style="margin-top:12px">
                        Selamat! Kamu mendapatkan gratis ongkir karena subtotal belanja mencapai <?= formatRupiah($minimalGratisOngkir) ?>.
                    </div>
                <?php else: ?>
                    <div class="alert alert-info" style="margin-top:12px">
                        Belanja lagi <strong><?= formatRupiah($sisaGratisOngkir) ?></strong> untuk mendapatkan gratis ongkir!
                        Minimal belanja <?= formatRupiah($minimalGratisOngkir) ?>.
                    </div>
                <?php endif; ?>

                <div class="summary-row total">
                    <span>Total</span>
                    <span><?= formatRupiah($total) ?></span>
                </div>

            </div>

            <!-- BUAT PESANAN -->
            <div class="summary-card">

                <h3>Buat Pesanan</h3>

                <div class="summary-row">
                    <span>Jumlah Item</span>
                    <span>
                        <?= array_sum(array_column($items, 'qty')) ?> buku
                    </span>
                </div>

                <div class="summary-row total">
                    <span>Total Bayar</span>
                    <span><?= formatRupiah($total) ?></span>
                </div>

                <button type="submit"
                    class="btn btn-primary btn-block"
                    style="margin-top:16px">
                    Buat Pesanan
                </button>

                <p class="summary-note">
                    Dengan membuat pesanan, kamu menyetujui syarat dan ketentuan toko.
                </p>

            </div>

        </div>
    </form>
</div>

<script>
(function () {
    const bankGroup = document.getElementById('bankTujuanGroup');
    const metodeRadios = document.querySelectorAll('input[name="metode"]');
    const bankRadios = document.querySelectorAll('input[name="bank_tujuan"]');

    if (!bankGroup || metodeRadios.length === 0) {
        return;
    }

    function cekMetode() {
        const transfer = document.querySelector(
            'input[name="metode"]:checked'
        );

        if (transfer && transfer.value === 'Transfer Bank') {
            bankGroup.style.setProperty('display', 'block', 'important');

            bankRadios.forEach(function (bank) {
                bank.required = true;
            });
             } else {
            bankGroup.style.setProperty('display', 'none', 'important');

            bankRadios.forEach(function (bank) {
                bank.required = false;
                bank.checked = false;
            });
        }
    }

    metodeRadios.forEach(function (radio) {
        radio.addEventListener('change', cekMetode);
        radio.addEventListener('click', cekMetode);
    });

    cekMetode();
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>