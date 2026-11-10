<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$activeMenu = 'messages';
$pageTitle = 'Pesan Masuk';

if (isset($_GET['hapus'])) {
    $pdo->prepare("DELETE FROM pesan_kontak WHERE id = ?")->execute([(int)$_GET['hapus']]);
    header('Location: messages.php');
    exit;
}

$pesanList = $pdo->query("SELECT * FROM pesan_kontak ORDER BY created_at DESC")->fetchAll();
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
            <h1>Pesan Masuk dari User</h1>
        </div>

        <div class="admin-card">
            <?php if (!$pesanList): ?>
                <p style="color:var(--text-muted)">Belum ada pesan masuk.</p>
            <?php endif; ?>

            <?php foreach ($pesanList as $p): ?>
            <div style="border:1px solid var(--border);border-radius:10px;padding:16px;margin-bottom:12px">
                <div style="display:flex;justify-content:space-between;align-items:center">
                    <div>
                        <b><?= e($p['nama_lengkap']) ?></b>
                        <span style="color:var(--text-muted);font-size:12px"> &mdash; <?= e($p['email']) ?></span>
                    </div>
                    <a class="del btn btn-sm" style="background:var(--danger-bg);color:var(--danger)" href="messages.php?hapus=<?= $p['id'] ?>" data-confirm-delete="Hapus pesan ini?">Hapus</a>
                </div>
                <p style="font-weight:700;margin-top:8px;font-size:14px"><?= e($p['subjek']) ?></p>
                <p style="color:var(--text-muted);font-size:13px;margin-top:4px"><?= nl2br(e($p['pesan'])) ?></p>
                <p style="font-size:11px;color:var(--text-muted);margin-top:8px"><?= formatTanggal($p['created_at'], true) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>
