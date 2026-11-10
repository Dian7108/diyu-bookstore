<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$activeMenu = 'books';
$pageTitle = 'Kelola Data Buku';

function handleUpload($fileField) {
    if (empty($_FILES[$fileField]['name'])) return null;
    if ($_FILES[$fileField]['error'] !== UPLOAD_ERR_OK) return null;
    $ext = strtolower(pathinfo($_FILES[$fileField]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) return null;
    $newName = 'buku_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
    $target = __DIR__ . '/../uploads/' . $newName;
    if (move_uploaded_file($_FILES[$fileField]['tmp_name'], $target)) return $newName;
    return null;
}

// ===== Proses Aksi (Add / Update / Delete) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    $judul     = trim($_POST['judul'] ?? '');
    $penulis   = trim($_POST['penulis'] ?? '');
    $kategori  = (int)($_POST['kategori_id'] ?? 0) ?: null;
    $harga     = (float)($_POST['harga'] ?? 0);
    $diskon    = (int)($_POST['diskon'] ?? 0);
    $stok      = (int)($_POST['stok'] ?? 0);
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $halaman   = (int)($_POST['jumlah_halaman'] ?? 0);

    if ($aksi === 'tambah' && $judul !== '') {
        $gambar = handleUpload('gambar');
        $stmt = $pdo->prepare("
            INSERT INTO buku (judul, penulis, kategori_id, harga, diskon, stok, deskripsi, jumlah_halaman, gambar)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$judul, $penulis, $kategori, $harga, $diskon, $stok, $deskripsi, $halaman, $gambar]);
    } elseif ($aksi === 'edit') {
        $id = (int)$_POST['id'];
        $gambarBaru = handleUpload('gambar');
        if ($gambarBaru) {
            $pdo->prepare("
                UPDATE buku SET judul=?, penulis=?, kategori_id=?, harga=?, diskon=?, stok=?, deskripsi=?, jumlah_halaman=?, gambar=? WHERE id=?
            ")->execute([$judul, $penulis, $kategori, $harga, $diskon, $stok, $deskripsi, $halaman, $gambarBaru, $id]);
        } else {
            $pdo->prepare("
                UPDATE buku SET judul=?, penulis=?, kategori_id=?, harga=?, diskon=?, stok=?, deskripsi=?, jumlah_halaman=? WHERE id=?
            ")->execute([$judul, $penulis, $kategori, $harga, $diskon, $stok, $deskripsi, $halaman, $id]);
        }
    }
    header('Location: books.php');
    exit;
}

if (isset($_GET['hapus'])) {
    $pdo->prepare("DELETE FROM buku WHERE id = ?")->execute([(int)$_GET['hapus']]);
    header('Location: books.php');
    exit;
}

$kategoriList = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();

$q = trim($_GET['q'] ?? '');
$where = ''; $params = [];
if ($q !== '') { $where = "WHERE b.judul LIKE ? OR b.penulis LIKE ?"; $params = ["%$q%", "%$q%"]; }
$stmt = $pdo->prepare("SELECT b.*, k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.kategori_id = k.id $where ORDER BY b.id DESC");
$stmt->execute($params);
$bukuList = $stmt->fetchAll();
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
            <h1>Kelola Data Buku</h1>
            <div class="user-chip">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)) ?></div>
                <span style="font-size:13px;font-weight:600"><?= e($_SESSION['nama_lengkap']) ?></span>
            </div>
        </div>

        <div class="admin-card">
            <div class="table-toolbar">
                <form class="table-search" method="get" action="books.php">
                    <span>&#128269;</span>
                    <input type="text" name="q" placeholder="Cari judul atau penulis..." value="<?= e($q) ?>">
                </form>
                <button class="btn btn-primary btn-sm" data-modal-open="modalTambah">+ Tambah Buku</button>
            </div>

            <div style="overflow-x:auto">
            <table class="data-table">
                <thead><tr><th>No</th><th>Cover</th><th>Judul Buku</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php foreach ($bukuList as $i => $b): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><img src="<?= bookCover($b) ?>" style="width:34px;height:46px;object-fit:cover;border-radius:4px"></td>
                        <td><b><?= e($b['judul']) ?></b><br><small style="color:var(--text-muted)"><?= e($b['penulis']) ?></small></td>
                        <td><?= e($b['nama_kategori'] ?? '-') ?></td>
                        <td><?= formatRupiah($b['harga']) ?><?= $b['diskon'] > 0 ? ' <span style="color:var(--danger);font-size:11px">(-'.$b['diskon'].'%)</span>' : '' ?></td>
                        <td><?= $b['stok'] ?></td>
                        <td>
                            <div class="action-icons">
                                <button title="Edit" data-modal-open="modalEditBuku<?= $b['id'] ?>">&#9998;</button>
                                <a class="del" title="Hapus" href="books.php?hapus=<?= $b['id'] ?>"
                                   data-confirm-delete="Yakin ingin menghapus buku '<?= e($b['judul']) ?>'?">&#128465;</a>
                            </div>
                        </td>
                    </tr>

                    <!-- Modal Edit Buku -->
                    <div class="modal-overlay" id="modalEditBuku<?= $b['id'] ?>">
                        <div class="modal-box" style="max-width:480px">
                            <h3>Edit Buku</h3>
                            <form method="post" action="books.php" enctype="multipart/form-data">
                                <input type="hidden" name="aksi" value="edit">
                                <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                <div class="form-group"><label>Judul Buku</label><input type="text" name="judul" value="<?= e($b['judul']) ?>" required></div>
                                <div class="form-group"><label>Penulis</label><input type="text" name="penulis" value="<?= e($b['penulis']) ?>" required></div>
                                <div class="form-group">
                                    <label>Kategori</label>
                                    <select name="kategori_id">
                                        <?php foreach ($kategoriList as $k): ?>
                                        <option value="<?= $k['id'] ?>" <?= $b['kategori_id'] == $k['id'] ? 'selected' : '' ?>><?= e($k['nama_kategori']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div style="display:flex;gap:10px">
                                    <div class="form-group" style="flex:1"><label>Harga</label><input type="number" name="harga" value="<?= $b['harga'] ?>" required></div>
                                    <div class="form-group" style="flex:1"><label>Diskon (%)</label><input type="number" name="diskon" value="<?= $b['diskon'] ?>"></div>
                                </div>
                                <div style="display:flex;gap:10px">
                                    <div class="form-group" style="flex:1"><label>Stok</label><input type="number" name="stok" value="<?= $b['stok'] ?>" required></div>
                                    <div class="form-group" style="flex:1"><label>Jumlah Halaman</label><input type="number" name="jumlah_halaman" value="<?= $b['jumlah_halaman'] ?>"></div>
                                </div>
                                <div class="form-group"><label>Deskripsi</label><textarea name="deskripsi" rows="3"><?= e($b['deskripsi']) ?></textarea></div>
                                <div class="form-group"><label>Ganti Cover (opsional)</label><input type="file" name="gambar" accept="image/*"></div>
                                <div class="modal-actions">
                                    <button type="button" class="btn btn-outline btn-block" data-modal-close>Batal</button>
                                    <button type="submit" class="btn btn-primary btn-block">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <?php if (!$bukuList): ?>
                    <tr><td colspan="7" style="text-align:center;color:var(--text-muted)">Belum ada data buku.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </main>
</div>

<!-- Modal Tambah Buku -->
<div class="modal-overlay" id="modalTambah">
    <div class="modal-box" style="max-width:480px">
        <h3>Tambah Buku Baru</h3>
        <form method="post" action="books.php" enctype="multipart/form-data">
            <input type="hidden" name="aksi" value="tambah">
            <div class="form-group"><label>Judul Buku</label><input type="text" name="judul" required></div>
            <div class="form-group"><label>Penulis</label><input type="text" name="penulis" required></div>
            <div class="form-group">
                <label>Kategori</label>
                <select name="kategori_id">
                    <?php foreach ($kategoriList as $k): ?>
                    <option value="<?= $k['id'] ?>"><?= e($k['nama_kategori']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display:flex;gap:10px">
                <div class="form-group" style="flex:1"><label>Harga</label><input type="number" name="harga" required></div>
                <div class="form-group" style="flex:1"><label>Diskon (%)</label><input type="number" name="diskon" value="0"></div>
            </div>
            <div style="display:flex;gap:10px">
                <div class="form-group" style="flex:1"><label>Stok</label><input type="number" name="stok" required></div>
                <div class="form-group" style="flex:1"><label>Jumlah Halaman</label><input type="number" name="jumlah_halaman" value="0"></div>
            </div>
            <div class="form-group"><label>Deskripsi</label><textarea name="deskripsi" rows="3"></textarea></div>
            <div class="form-group"><label>Cover Buku</label><input type="file" name="gambar" accept="image/*"></div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline btn-block" data-modal-close>Batal</button>
                <button type="submit" class="btn btn-primary btn-block">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/main.js"></script>
</body>
</html>
