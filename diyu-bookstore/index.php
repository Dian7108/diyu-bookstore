<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Beranda';

// Buku pilihan untuk hero (4 buku terbaru)
$stmtPick = $pdo->query("SELECT * FROM buku ORDER BY created_at DESC LIMIT 4");
$bukuPilihan = $stmtPick->fetchAll();

// Semua buku untuk grid "Buku Terlaris" / rekomendasi
$stmtAll = $pdo->query("SELECT b.*, k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.kategori_id = k.id ORDER BY b.rating DESC LIMIT 10");
$bukuList = $stmtAll->fetchAll();

// Statistik
$totalBuku = $pdo->query("SELECT COUNT(*) c FROM buku")->fetch()['c'];
$totalUser = $pdo->query("SELECT COUNT(*) c FROM users WHERE role='user'")->fetch()['c'];

// Kategori untuk quick links
$kategoriList = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="hero-badge">DISKON<strong>30%</strong></div>
    <div>
        <h1>Temukan buku<br>terbaik untuk<br>hidup yang lebih baik</h1>
        <p>DiYu BookStore menyediakan berbagai pilihan buku berkualitas untuk menemani setiap langkahmu.</p>
        <a href="search.php" class="btn btn-primary" style="background:#fff;color:var(--primary)">Belanja Sekarang</a>
        <div class="hero-stats">
            <div><strong><?= number_format($totalBuku >= 1000 ? $totalBuku : 10) ?>K+</strong><span>Buku Tersedia</span></div>
            <div><strong>25K+</strong><span>Pelanggan</span></div>
            <div><strong>4.8 &#9733;</strong><span>Rating Toko</span></div>
        </div>
    </div>
    <div class="hero-image">
        <img src="https://images.unsplash.com/photo-1521587760476-6c12a4b040da?w=800&q=80" alt="Toko buku">
        <div class="hero-pick-card">
            <div class="pick-title"><span>Buku Pilihan</span><a href="search.php">Lihat Semua</a></div>
            <div class="pick-books">
                <?php foreach ($bukuPilihan as $bp): ?>
                <a href="product-detail.php?id=<?= $bp['id'] ?>" class="pb">
                    <img src="<?= bookCover($bp) ?>" alt="<?= e($bp['judul']) ?>">
                    <?= e($bp['judul']) ?>
                    <div class="pb-price"><?= formatRupiah(hargaSetelahDiskon($bp['harga'], $bp['diskon'])) ?></div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="section-head">
        <h2>Jelajahi Kategori</h2>
        <a href="search.php">Lihat Semua &rarr;</a>
    </div>
    <div style="display:flex;gap:14px;flex-wrap:wrap">
        <?php foreach ($kategoriList as $kat): ?>
            <a href="search.php?kategori=<?= $kat['id'] ?>" class="btn btn-outline btn-sm"><?= e($kat['nama_kategori']) ?></a>
        <?php endforeach; ?>
    </div>
</section>

<section class="section">
    <div class="section-head">
        <h2>Rekomendasi Buku Terbaik</h2>
        <a href="search.php">Lihat Semua &rarr;</a>
    </div>
    <div class="book-grid">
        <?php foreach ($bukuList as $b): ?>
        <a href="product-detail.php?id=<?= $b['id'] ?>" class="book-card">
            <div class="book-cover">
                <?php if ($b['diskon'] > 0): ?><span class="discount-tag">-<?= $b['diskon'] ?>%</span><?php endif; ?>
                <img src="<?= bookCover($b) ?>" alt="<?= e($b['judul']) ?>">
            </div>
            <div class="book-info">
                <span class="book-cat"><?= e($b['nama_kategori'] ?? 'Umum') ?></span>
                <h3><?= e($b['judul']) ?></h3>
                <span class="book-author"><?= e($b['penulis']) ?></span>
                <div class="book-price">
                    <strong><?= formatRupiah(hargaSetelahDiskon($b['harga'], $b['diskon'])) ?></strong>
                    <?php if ($b['diskon'] > 0): ?><del><?= formatRupiah($b['harga']) ?></del><?php endif; ?>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
