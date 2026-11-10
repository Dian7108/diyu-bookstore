
<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Buku';

$tab = $_GET['tab'] ?? 'buku';
$q = trim($_GET['q'] ?? '');
$kategori = (int)($_GET['kategori'] ?? 0);
$sort = $_GET['sort'] ?? 'terbaru';

// Pastikan tab hanya kategori atau buku
if (!in_array($tab, ['buku', 'kategori'], true)) {
    $tab = 'buku';
}

// Ambil daftar kategori beserta jumlah bukunya
$kategoriList = $pdo->query("
    SELECT 
        k.id,
        k.nama_kategori,
        COUNT(b.id) AS jml
    FROM kategori k
    LEFT JOIN buku b ON b.kategori_id = k.id
    GROUP BY k.id, k.nama_kategori
    ORDER BY k.nama_kategori ASC
")->fetchAll();

// Variabel awal
$hasil = [];
$namaKategori = '';

// Proses hanya jika halaman buku dibuka
if ($tab === 'buku') {

    // Cek apakah pencarian sama persis dengan nama kategori
    if ($q !== '' && $kategori === 0) {
        $cekKategori = $pdo->prepare("
            SELECT id
            FROM kategori
            WHERE LOWER(TRIM(nama_kategori)) = LOWER(TRIM(?))
            LIMIT 1
        ");

        $cekKategori->execute([$q]);
        $kategoriDitemukan = $cekKategori->fetchColumn();

        if ($kategoriDitemukan) {
            $kategori = (int)$kategoriDitemukan;
        }
    }

    // Filter buku
    $where = [];
    $params = [];

    if ($kategori > 0) {
        $where[] = "b.kategori_id = ?";
        $params[] = $kategori;
    } elseif ($q !== '') {
        $where[] = "(b.judul LIKE ? OR b.penulis LIKE ?)";

        $keyword = "%$q%";
        $params[] = $keyword;
        $params[] = $keyword;
    }

    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // Pengurutan buku
    $orderSql = "b.created_at DESC";

    if ($sort === 'termurah') {
        $orderSql = "b.harga ASC";
    } elseif ($sort === 'termahal') {
        $orderSql = "b.harga DESC";
    } elseif ($sort === 'rating') {
        $orderSql = "b.rating DESC";
    }

    // Ambil data buku
    $sql = "
        SELECT b.*, k.nama_kategori
        FROM buku b
        LEFT JOIN kategori k ON b.kategori_id = k.id
        $whereSql
        ORDER BY $orderSql
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $hasil = $stmt->fetchAll();

    // Nama kategori yang dipilih
    if ($kategori > 0) {
        foreach ($kategoriList as $kat) {
            if ((int)$kat['id'] === $kategori) {
                $namaKategori = $kat['nama_kategori'];
                break;
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($tab === 'kategori'): ?>

    <!-- HALAMAN DAFTAR KATEGORI -->

    <div class="section">

        <div class="section-head">
            <h2>Kategori Buku</h2>
        </div>

        <?php if (!$kategoriList): ?>

            <p style="color:var(--text-muted)">
                Belum ada kategori buku.
            </p>

        <?php else: ?>

            <ol style="padding-left:25px;">

                <?php foreach ($kategoriList as $kat): ?>

                    <li style="
                        padding:14px 8px;
                        border-bottom:1px solid var(--border);
                    ">

                        <a
                            href="search.php?tab=buku&kategori=<?= $kat['id'] ?>"
                            style="
                                color:var(--text);
                                font-weight:600;
                                text-decoration:none;
                            "
                        >
                            <?= e($kat['nama_kategori']) ?>
                        </a>

                        <span style="
                            color:var(--text-muted);
                            font-size:13px;
                            margin-left:6px;
                        ">
                            (<?= $kat['jml'] ?> buku)
                        </span>

                    </li>

                <?php endforeach; ?>

            </ol>

        <?php endif; ?>

    </div>

<?php else: ?>

    <!-- FORM PENCARIAN BUKU -->

    <div class="section" style="margin-bottom:0">

        <form
            action="search.php"
            method="get"
            class="search-box"
            style="max-width:650px;margin:0 auto 10px;min-width:unset"
        >

            <input type="hidden" name="tab" value="buku">

            <span>&#128269;</span>

            <input
                type="text"
                name="q"
                placeholder="Cari judul, penulis, atau kategori..."
                value="<?= e($q) ?>"
            >

            <?php if ($kategori > 0 && $q === ''): ?>
                <input
                    type="hidden"
                    name="kategori"
                    value="<?= $kategori ?>"
                >
            <?php endif; ?>

            <button type="submit" class="btn btn-primary btn-sm">
                Cari
            </button>

        </form>

    </div>

    <!-- DAFTAR BUKU -->

    <div class="section">

        <div class="section-head" style="margin-bottom:16px">

            <h2 style="font-size:20px">

                <?php if ($namaKategori !== ''): ?>

                    <?= e($namaKategori) ?>

                <?php elseif ($q !== ''): ?>

                    Hasil Pencarian untuk "<?= e($q) ?>"

                <?php else: ?>

                    Semua Buku

                <?php endif; ?>

                <span style="
                    color:var(--text-muted);
                    font-weight:500;
                ">
                    (<?= count($hasil) ?> buku)
                </span>

            </h2>

        </div>

        <?php if (!$hasil): ?>

            <p style="color:var(--text-muted)">
                Tidak ada buku yang ditemukan.
            </p>

        <?php else: ?>

            <div class="book-grid">

                <?php foreach ($hasil as $b): ?>

                    <a
                        href="product-detail.php?id=<?= $b['id'] ?>"
                        class="book-card"
                    >

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

                            <h3>
                                <?= e($b['judul']) ?>
                            </h3>

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

                                <?php if ($b['diskon'] > 0): ?>

                                    <del>
                                        <?= formatRupiah($b['harga']) ?>
                                    </del>

                                <?php endif; ?>

                            </div>

                        </div>

                    </a>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>

<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
