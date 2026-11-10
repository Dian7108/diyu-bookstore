
<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Wishlist';

// Wishlist hanya bisa dibuka setelah login
requireLogin('wishlist.php');

// Ambil buku yang tersimpan di wishlist pengguna
$stmt = $pdo->prepare("
    SELECT b.*, k.nama_kategori
    FROM wishlist w
    JOIN buku b ON w.buku_id = b.id
    LEFT JOIN kategori k ON b.kategori_id = k.id
    WHERE w.user_id = ?
    ORDER BY w.id DESC
");

$stmt->execute([$_SESSION['user_id']]);
$wishlist = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="section">

    <div class="section-head">
        <h2>♡ Wishlist Saya</h2>
    </div>

    <?php if (!$wishlist): ?>

        <div style="text-align:center; padding:50px 20px;">
            <div style="font-size:50px; color:var(--primary);">
                &#9825;
            </div>

            <h3>Wishlist Masih Kosong</h3>

            <p style="color:var(--text-muted); margin:10px 0 20px;">
                Yuk, simpan buku favoritmu di sini!
            </p>

            <a href="search.php?tab=buku" class="btn btn-primary">
                Jelajahi Buku
            </a>
        </div>

    <?php else: ?>

        <div class="book-grid">

            <?php foreach ($wishlist as $b): ?>

                <div class="book-card">

                    <a href="product-detail.php?id=<?= $b['id'] ?>"
                       style="text-decoration:none; color:inherit;">

                        <div class="book-cover">

                            <?php if ($b['diskon'] > 0): ?>
                                <span class="discount-tag">
                                    -<?= $b['diskon'] ?>%
                                </span>
                            <?php endif; ?>

                            <img
                                src="<?= bookCover($b) ?>"
                                alt="<?= e($b['judul']) ?>"
                            >

                        </div>

                        <div class="book-info">

                            <span class="book-cat">
                                <?= e($b['nama_kategori'] ?? 'Umum') ?>
                            </span>

                            <h3><?= e($b['judul']) ?></h3>

                            <span class="book-author">
                                <?= e($b['penulis']) ?>
                            </span>

                            <div class="book-price">
                                <strong>
                                    <?= formatRupiah(
                                        hargaSetelahDiskon(
                                            $b['harga'],
                                            $b['diskon']
                                        )
                                    ) ?>
                                </strong>
                            </div>

                        </div>

                    </a>

                    <div style="padding:0 14px 14px;">
                        <button
                            type="button"
                            class="btn btn-outline"
                            style="width:100%;"
                            onclick="hapusWishlist(<?= (int)$b['id'] ?>, this)"
                        >
                            &#10084; Hapus dari Wishlist
                        </button>
                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>

<script>
async function hapusWishlist(bookId, button) {
    if (!confirm('Hapus buku ini dari Wishlist?')) return;

    try {
        const response = await fetch('ajax/wishlist_toggle.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                buku_id: bookId
            })
        });

        const data = await response.json();

        if (data.success && !data.inWishlist) {
            button.closest('.book-card').remove();

            if (!document.querySelector('.book-card')) {
                location.reload();
            }
        } else {
            alert('Buku gagal dihapus dari Wishlist.');
        }
    } catch (error) {
        alert('Terjadi kesalahan. Coba lagi.');
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
