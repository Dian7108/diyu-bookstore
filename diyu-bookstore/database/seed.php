<?php
/**
 * Jalankan file ini SEKALI lewat browser setelah import schema.sql
 * untuk membuat akun admin & user contoh dengan password ter-hash yang valid.
 * Contoh akses: http://localhost/bookstore/database/seed.php
 *
 * Semua akun contoh memakai password: password123
 *
 * Setelah berhasil dijalankan, sebaiknya file ini dihapus/dipindah dari server produksi.
 */
require_once __DIR__ . '/../config/database.php';

$passwordHash = password_hash('password123', PASSWORD_BCRYPT);

$akun = [
    ['Admin DiYu', 'admin@diyubookstore.com', 'admin'],
    ['Dian Saftiri', 'dian.saftri@email.com', 'user'],
    ['Budi Santoso', 'budi.santoso@email.com', 'user'],
    ['Siti Aisyah', 'siti.aisyah@email.com', 'user'],
    ['Ahmad Rizki', 'ahmad.rizki@email.com', 'user'],
    ['Rina Kartika', 'rina.kartika@email.com', 'user'],
];

$stmt = $pdo->prepare("INSERT IGNORE INTO users (nama_lengkap, email, password, role) VALUES (?, ?, ?, ?)");

$berhasil = 0;
foreach ($akun as $a) {
    $stmt->execute([$a[0], $a[1], $passwordHash, $a[2]]);
    if ($stmt->rowCount() > 0) $berhasil++;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Seed Data - DiYu BookStore</title>
<style>
body{font-family:Arial,sans-serif;max-width:600px;margin:60px auto;padding:20px;background:#F8F7FB;color:#2B2540}
.box{background:#fff;border-radius:12px;padding:30px;box-shadow:0 2px 10px rgba(0,0,0,.08)}
h1{color:#4C1D95;font-size:20px}
table{width:100%;border-collapse:collapse;margin-top:15px}
td,th{padding:8px;border-bottom:1px solid #eee;text-align:left;font-size:14px}
.tag{display:inline-block;padding:2px 10px;border-radius:20px;font-size:12px;font-weight:bold}
.admin{background:#EDE9FE;color:#4C1D95}
.user{background:#E0F2FE;color:#0369A1}
</style>
</head>
<body>
<div class="box">
    <h1>&#10003; Seed data akun berhasil diproses (<?= $berhasil ?> akun baru ditambahkan)</h1>
    <p>Semua akun contoh di bawah memakai password: <b>password123</b></p>
    <table>
        <tr><th>Nama</th><th>Email</th><th>Role</th></tr>
        <?php foreach ($akun as $a): ?>
        <tr>
            <td><?= htmlspecialchars($a[0]) ?></td>
            <td><?= htmlspecialchars($a[1]) ?></td>
            <td><span class="tag <?= $a[2] ?>"><?= $a[2] ?></span></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <p style="margin-top:20px;font-size:13px;color:#777">
        Demi keamanan, hapus atau pindahkan file <code>database/seed.php</code> ini dari server setelah selesai digunakan.
    </p>
    <p><a href="../login.php" style="color:#4C1D95;font-weight:bold">&larr; Ke halaman Login</a></p>
</div>
</body>
</html>
