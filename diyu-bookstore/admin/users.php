<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$activeMenu = 'users';
$pageTitle = 'List User';

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if ($id !== (int)$_SESSION['user_id']) { // tidak bisa hapus diri sendiri
        $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'user'")->execute([$id]);
    }
    header('Location: users.php');
    exit;
}

$q = trim($_GET['q'] ?? '');
$where = "WHERE role = 'user'";
$params = [];
if ($q !== '') { $where .= " AND (nama_lengkap LIKE ? OR email LIKE ?)"; $params = ["%$q%", "%$q%"]; }

$stmt = $pdo->prepare("SELECT * FROM users $where ORDER BY tanggal_daftar DESC");
$stmt->execute($params);
$userList = $stmt->fetchAll();
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
            <h1>List User</h1>
            <div class="user-chip">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)) ?></div>
                <span style="font-size:13px;font-weight:600"><?= e($_SESSION['nama_lengkap']) ?></span>
            </div>
        </div>

        <div class="admin-card">
            <div class="table-toolbar">
                <form class="table-search" method="get" action="users.php">
                    <span>&#128269;</span>
                    <input type="text" name="q" placeholder="Cari user..." value="<?= e($q) ?>">
                </form>
                <span style="font-size:13px;color:var(--text-muted)"><?= count($userList) ?> user terdaftar</span>
            </div>

            <table class="data-table">
                <thead><tr><th>No</th><th>Nama</th><th>Email</th><th>Tanggal Daftar</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php foreach ($userList as $i => $u): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px">
                                <div class="user-avatar" style="width:30px;height:30px;font-size:12px"><?= strtoupper(substr($u['nama_lengkap'],0,1)) ?></div>
                                <?= e($u['nama_lengkap']) ?>
                            </div>
                        </td>
                        <td><?= e($u['email']) ?></td>
                        <td><?= formatTanggal($u['tanggal_daftar']) ?></td>
                        <td>
                            <div class="action-icons">
                                <button class="view" title="Lihat" data-modal-open="modalView<?= $u['id'] ?>">&#128065;</button>
                                <a class="del" title="Hapus" href="users.php?hapus=<?= $u['id'] ?>" data-confirm-delete="Yakin ingin menghapus user '<?= e($u['nama_lengkap']) ?>'?">&#128465;</a>
                            </div>
                        </td>
                    </tr>
                    <div class="modal-overlay" id="modalView<?= $u['id'] ?>">
                        <div class="modal-box">
                            <h3>Detail User</h3>
                            <p style="font-size:13px;margin-bottom:8px"><b>Nama:</b> <?= e($u['nama_lengkap']) ?></p>
                            <p style="font-size:13px;margin-bottom:8px"><b>Email:</b> <?= e($u['email']) ?></p>
                            <p style="font-size:13px;margin-bottom:8px"><b>Tanggal Daftar:</b> <?= formatTanggal($u['tanggal_daftar'], true) ?></p>
                            <div class="modal-actions"><button type="button" class="btn btn-outline btn-block" data-modal-close>Tutup</button></div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <?php if (!$userList): ?>
                    <tr><td colspan="5" style="text-align:center;color:var(--text-muted)">Belum ada user terdaftar.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>
