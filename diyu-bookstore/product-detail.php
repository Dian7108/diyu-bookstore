
<?php
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("
    SELECT b.*, k.nama_kategori
    FROM buku b
    LEFT JOIN kategori k ON b.kategori_id = k.id
    WHERE b.id = ?
");
$stmt->execute([$id]);
$buku = $stmt->fetch();

if (!$buku) {
    header('Location: index.php');
    exit;
}

$pageTitle = $buku['judul'];

// Cek wishlist
$inWishlist = false;
if (isLoggedIn()) {
    $wstmt = $pdo->prepare("
        SELECT id FROM wishlist
        WHERE user_id = ? AND buku_id = ?
    ");
    $wstmt->execute([$_SESSION['user_id'], $id]);
    $inWishlist = (bool)$wstmt->fetch();
}

// Buku terkait
$relStmt = $pdo->prepare("
    SELECT * FROM buku
    WHERE kategori_id = ? AND id != ?
    LIMIT 4
");
$relStmt->execute([$buku['kategori_id'], $id]);
$related = $relStmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="breadcrumb">
    <a href="index.php">Beranda</a>
    <span class="sep">/</span>
    <a href="search.php?kategori=<?= $buku['kategori_id'] ?>">
        <?= e($buku['nama_kategori'] ?? 'Kategori') ?>
    </a>
    <span class="sep">/</span>
    <span class="current"><?= e($buku['judul']) ?></span>
</div>

<div class="detail-wrap">
    <div class="detail-cover">
        <img src="<?= bookCover($buku) ?>" alt="<?= e($buku['judul']) ?>">
    </div>

    <div class="detail-info">
        <h1><?= e($buku['judul']) ?></h1>

        <p style="color:var(--text-muted);margin-bottom:10px">
            oleh <?= e($buku['penulis']) ?>
        </p>

        <div class="detail-meta">
            <span>&#11088; <?= number_format($buku['rating'], 1) ?> (<?= rand(50, 300) ?> ulasan)</span>
            <span>&middot;</span>
            <span><?= e($buku['nama_kategori'] ?? 'Umum') ?></span>
            <span>&middot;</span>
            <span><?= e($buku['bahasa']) ?></span>
            <span>&middot;</span>
            <span><?= (int)$buku['jumlah_halaman'] ?> Halaman</span>
        </div>

        <div class="detail-price-row">
            <span class="price-now">
                <?= formatRupiah(hargaSetelahDiskon($buku['harga'], $buku['diskon'])) ?>
            </span>

            <?php if ($buku['diskon'] > 0): ?>
                <del><?= formatRupiah($buku['harga']) ?></del>
                <span class="discount-pill"><?= $buku['diskon'] ?>% OFF</span>
            <?php endif; ?>
        </div>

        <p class="detail-desc">
            <?= nl2br(e($buku['deskripsi'])) ?>
        </p>

        <p style="font-size:13px;font-weight:700;margin-bottom:16px">
            Stok Tersedia:
            <span style="color:var(--primary)">
                <?= (int)$buku['stok'] ?> buku
            </span>
        </p>

        <div class="qty-selector"
             data-min="1"
             data-max="<?= (int)$buku['stok'] ?>">

            <button type="button" data-qty-dec>&minus;</button>

            <input
                type="number"
                id="qtyDetail"
                value="1"
                min="1"
                max="<?= (int)$buku['stok'] ?>"
                readonly
            >

            <button type="button" data-qty-inc>+</button>
        </div>

        <div class="detail-actions">

            <?php if ($buku['stok'] > 0): ?>

                <!-- Tambah ke Keranjang -->
                <button
                    class="btn btn-primary"
                    data-add-cart="<?= $buku['id'] ?>"
                    data-qty-input="qtyDetail"
                >
                    &#128722; Tambah ke Keranjang
                </button>

                <!-- Beli Sekarang -->
                <button
                    type="button"
                    class="btn btn-primary"
                    id="buyNowBtn"
                    data-book-id="<?= $buku['id'] ?>"
                >
                    Beli Sekarang
                </button>

            <?php else: ?>

                <button class="btn btn-primary" disabled style="opacity:.5">
                    Stok Habis
                </button>

            <?php endif; ?>

            <!-- Wishlist tetap seperti semula -->
            <?php if (isLoggedIn()): ?>

                <button
                    class="btn btn-outline"
                    id="wishlistBtn"
                    data-book-id="<?= $buku['id'] ?>"
                >
                    <?= $inWishlist ? '&#10084; Wishlist' : '&#9825; Tambah ke Wishlist' ?>
                </button>

            <?php else: ?>

                <a href="login.php" class="btn btn-outline">
                    &#9825; Tambah ke Wishlist
                </a>

            <?php endif; ?>

        </div>

        <div class="detail-badges">
            <div> Garansi Original</div>
            <div> Pengiriman Cepat</div>
            <div> Bisa Bayar di Tempat</div>
        </div>
    </div>
</div>

<?php if ($related): ?>
<section class="section">
    <div class="section-head">
        <h2>Buku Terkait</h2>
    </div>

    <div class="book-grid">
        <?php foreach ($related as $b): ?>
            <a href="product-detail.php?id=<?= $b['id'] ?>" class="book-card">

                <div class="book-cover">
                    <?php if ($b['diskon'] > 0): ?>
                        <span class="discount-tag">-<?= $b['diskon'] ?>%</span>
                    <?php endif; ?>

                    <img src="<?= bookCover($b) ?>" alt="<?= e($b['judul']) ?>">
                </div>

                <div class="book-info">
                    <h3><?= e($b['judul']) ?></h3>
                    <span class="book-author"><?= e($b['penulis']) ?></span>

                    <div class="book-price">
                        <strong>
                            <?= formatRupiah(hargaSetelahDiskon($b['harga'], $b['diskon'])) ?>
                        </strong>
                    </div>
                </div>

            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<script>
// Tombol Beli Sekarang
document.getElementById('buyNowBtn')?.addEventListener('click', function () {
    const bookId = this.dataset.bookId;
    const qty = document.getElementById('qtyDetail').value;

    window.location.href =
        'checkout.php?buy_now=' + encodeURIComponent(bookId) +
        '&qty=' + encodeURIComponent(qty);
});

// Wishlist
document.getElementById('wishlistBtn')?.addEventListener('click', async function () {
    const bookId = this.dataset.bookId;

    const res = await fetch('ajax/wishlist_toggle.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
            buku_id: bookId
        })
    });

    const data = await res.json();

    if (data.success) {
        this.innerHTML = data.inWishlist
            ? '&#10084; Wishlist'
            : '&#9825; Tambah ke Wishlist';

        showToast(
            data.inWishlist
                ? 'Ditambahkan ke wishlist'
                : 'Dihapus dari wishlist'
        );
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
