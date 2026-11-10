<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Registrasi';

if (isLoggedIn()) {
    header('Location: ' . (isAdmin() ? 'admin/dashboard.php' : 'index.php'));
    exit;
}

$error = '';
$old = ['nama_lengkap' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama       = trim($_POST['nama_lengkap'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi_password'] ?? '';
    $old = ['nama_lengkap' => $nama, 'email' => $email];

    if ($nama === '' || $email === '' || $password === '' || $konfirmasi === '') {
        $error = 'Semua kolom wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $konfirmasi) {
        $error = 'Konfirmasi password tidak sama.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email sudah terdaftar. Silakan login.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (nama_lengkap, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->execute([$nama, $email, $hash]);
            header('Location: login.php?registered=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registrasi - DiYu BookStore</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-visual">
        <div class="logo-icon-lg">D</div>
        <h2>Buat akun baru</h2>
        <p>Untuk pengalaman berbelanja buku yang lebih baik bersama DiYu BookStore.</p>
        <div class="auth-visual-books">
           
        </div>
    </div>

    <div class="auth-form-wrap">
        <form class="auth-form" method="post" action="register.php">
            <h1>Registrasi</h1>
            <p class="sub">Buat akun baru untuk mulai belanja buku</p>

            <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama_lengkap" placeholder="Masukkan nama lengkap" required value="<?= e($old['nama_lengkap']) ?>">
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Masukkan email" required value="<?= e($old['email']) ?>">
            </div>

            <div class="form-group">
                <label>Password</label>
                <div class="input-icon-wrap">
                    <input type="password" name="password" placeholder="Buat password" required minlength="6">
                    <button type="button" class="toggle-eye">&#128065;</button>
                </div>
            </div>

            <div class="form-group">
                <label>Konfirmasi Password</label>
                <div class="input-icon-wrap">
                    <input type="password" name="konfirmasi_password" placeholder="Ulangi password" required minlength="6">
                    <button type="button" class="toggle-eye">&#128065;</button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="margin-top:6px">Daftar</button>

            <p class="auth-switch">Sudah punya akun? <a href="login.php">Login di sini</a></p>
        </form>
    </div>
</div>

<script src="assets/js/main.js"></script>
</body>
</html>
