<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Keranjang Belanja';
requireLogin('cart.php');

$stmt = $pdo->prepare("
    SELECT ke.id AS keranjang_id, ke.qty, b.*
    FROM keranjang ke
    JOIN buku b ON ke.buku_id = b.id
    WHERE ke.user_id = ?
    ORDER BY ke.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$items = $stmt->fetchAll();

$subtotal = 0;
foreach ($items as $it) {
    $subtotal += hargaSetelahDiskon($it['harga'], $it['diskon']) * $it['qty'];
}
$ongkir = $items ? 15000 : 0;
$total = $subtotal + $ongkir;

require_once __DIR__ . '/includes/header.php';
?>

<div class="section">
    <div class="section-head"><h2>Keranjang Belanja</h2></div>

    <?php if (!$items): ?>
        <div class="cart-card" style="padding:60px;text-align:center">
            <p style="font-size:40px;margin-bottom:12px">&#128722;</p>
            <p style="color:var(--text-muted);margin-bottom:18px">Keranjang belanja kamu masih kosong.</p>
            <a href="index.php" class="btn btn-primary">Mulai Belanja</a>
        </div>
    <?php else: ?>
    <div class="cart-layout">
        <div class="cart-card">
            <table class="cart-table">
                <thead>
                    <tr><th>Produk</th><th>Harga</th><th>Qty</th><th>Subtotal</th><th></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $it):
                        $hargaFinal = hargaSetelahDiskon($it['harga'], $it['diskon']);
                        $subItem = $hargaFinal * $it['qty'];
                    ?>
                    <tr data-row-for="<?= $it['keranjang_id'] ?>">
                        <td>
                            <div class="cart-item-info">
                                <img src="<?= bookCover($it) ?>" alt="<?= e($it['judul']) ?>">
                                <div>
                                    <span><?= e($it['judul']) ?></span>
                                    <small><?= e($it['penulis']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?= formatRupiah($hargaFinal) ?></td>
                        <td>
                            <div class="qty-mini" data-min="1" data-max="<?= (int)$it['stok'] ?>">
                                <button type="button" data-qty-dec>&minus;</button>
                                <input type="number" style="width:26px;border:none;text-align:center;font-size:13px;font-weight:700"
                                       value="<?= $it['qty'] ?>" min="1" max="<?= (int)$it['stok'] ?>"
                                       data-cart-qty="<?= $it['keranjang_id'] ?>">
                                <button type="button" data-qty-inc>+</button>
                            </div>
                        </td>
                        <td data-subtotal-for="<?= $it['keranjang_id'] ?>"><?= formatRupiah($subItem) ?></td>
                        <td><button class="remove-link" data-cart-remove="<?= $it['keranjang_id'] ?>">&#128465;</button></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="summary-card">
            <h3>Ringkasan Belanja</h3>
            <div class="summary-row"><span>Subtotal</span><span data-cart-subtotal-all><?= formatRupiah($subtotal) ?></span></div>
            <div class="summary-row"><span>Ongkir</span><span><?= formatRupiah($ongkir) ?></span></div>
            <div class="summary-row total"><span>Total</span><span data-cart-total><?= formatRupiah($total) ?></span></div>
            <a href="checkout.php" class="btn btn-primary btn-block" style="margin-top:16px">Checkout</a>
            <p class="summary-note">Bayar saat pesanan diterima di alamatmu.</p>
        </div>
    </div>

    <a href="index.php" class="btn btn-outline" style="margin-top:20px">&larr; Lanjut Belanja</a>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
