<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$activeMenu = 'categories';
$pageTitle = 'Kelola Kategori Buku';

// ===== Proses Aksi (Add / Update / Delete) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    if ($aksi === 'tambah') {
        $nama = trim($_POST['nama_kategori'] ?? '');
        if ($nama !== '') {
            $pdo->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)")->execute([$nama]);
        }
    } elseif ($aksi === 'edit') {
        $id = (int)$_POST['id'];
        $nama = trim($_POST['nama_kategori'] ?? '');
        if ($nama !== '') {
            $pdo->prepare("UPDATE kategori SET nama_kategori = ? WHERE id = ?")->execute([$nama, $id]);
        }
    }
    header('Location: categories.php');
    exit;
}

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $pdo->prepare("DELETE FROM kategori WHERE id = ?")->execute([$id]);
    header('Location: categories.php');
    exit;
}

$kategoriList = $pdo->query("
    SELECT k.*, (SELECT COUNT(*) FROM buku WHERE kategori_id = k.id) AS jml_buku
    FROM kategori k ORDER BY k.nama_kategori
")->fetchAll();
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
            <h1>Kelola Kategori Buku</h1>
            <div class="user-chip">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)) ?></div>
                <span style="font-size:13px;font-weight:600"><?= e($_SESSION['nama_lengkap']) ?></span>
            </div>
        </div>

        <div class="admin-card">
            <div class="table-toolbar">
                <h3 style="margin:0">Kategori Buku</h3>
                <button class="btn btn-primary btn-sm" data-modal-open="modalTambah">+ Tambah Kategori</button>
            </div>

            <table class="data-table">
                <thead><tr><th>No</th><th>Nama Kategori</th><th>Jumlah Buku</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php foreach ($kategoriList as $i => $k): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= e($k['nama_kategori']) ?></td>
                        <td><?= $k['jml_buku'] ?></td>
                        <td>
                            <div class="action-icons">
                                <button title="Edit" data-modal-open="modalEdit<?= $k['id'] ?>">&#9998;</button>
                                <a class="del" title="Hapus" href="categories.php?hapus=<?= $k['id'] ?>"
                                   data-confirm-delete="Yakin ingin menghapus kategori '<?= e($k['nama_kategori']) ?>'? Buku dalam kategori ini tidak akan terhapus.">&#128465;</a>
                            </div>
                        </td>
                    </tr>

                    <!-- Modal Edit per baris -->
                    <div class="modal-overlay" id="modalEdit<?= $k['id'] ?>">
                        <div class="modal-box">
                            <h3>Edit Kategori</h3>
                            <form method="post" action="categories.php">
                                <input type="hidden" name="aksi" value="edit">
                                <input type="hidden" name="id" value="<?= $k['id'] ?>">
                                <div class="form-group">
                                    <label>Nama Kategori</label>
                                    <input type="text" name="nama_kategori" value="<?= e($k['nama_kategori']) ?>" required>
                                </div>
                                <div class="modal-actions">
                                    <button type="button" class="btn btn-outline btn-block" data-modal-close>Batal</button>
                                    <button type="submit" class="btn btn-primary btn-block">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <?php if (!$kategoriList): ?>
                    <tr><td colspan="4" style="text-align:center;color:var(--text-muted)">Belum ada kategori.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Modal Tambah -->
<div class="modal-overlay" id="modalTambah">
    <div class="modal-box">
        <h3>Tambah Kategori Baru</h3>
        <form method="post" action="categories.php">
            <input type="hidden" name="aksi" value="tambah">
            <div class="form-group">
                <label>Nama Kategori</label>
                <input type="text" name="nama_kategori" placeholder="Contoh: Fiksi" required>
            </div>
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
