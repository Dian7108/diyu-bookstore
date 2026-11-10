<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Login';

if (isLoggedIn()) {
    header('Location: ' . (isAdmin() ? 'admin/dashboard.php' : 'index.php'));
    exit;
}

$error = '';
$redirect = $_GET['redirect'] ?? 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailOrUser = trim($_POST['email'] ?? '');
    $password    = $_POST['password'] ?? '';

    if ($emailOrUser === '' || $password === '') {
        $error = 'Email/username dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$emailOrUser]);
        $u = $stmt->fetch();

        if ($u && password_verify($password, $u['password'])) {
            $_SESSION['user_id']      = $u['id'];
            $_SESSION['nama_lengkap'] = $u['nama_lengkap'];
            $_SESSION['email']        = $u['email'];
            $_SESSION['role']         = $u['role'];

            if ($u['role'] === 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                $redirectTo = $_POST['redirect'] ?? 'index.php';
                header('Location: ' . ($redirectTo ?: 'index.php'));
            }
            exit;
        } else {
            $error = 'Email atau password yang Anda masukkan salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - DiYu BookStore</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-visual">
        <div class="logo-icon-lg">D</div>
        <h2>Selamat Datang!</h2>
        <p>Silakan login untuk melanjutkan berbelanja buku favoritmu di DiYu BookStore.</p>
    </div>

    <div class="auth-form-wrap">
        <form class="auth-form" method="post" action="login.php?redirect=<?= e($redirect) ?>">
            <h1>Login</h1>
            <p class="sub">Masukkan email atau username untuk melanjutkan</p>

            <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
            <?php if (isset($_GET['registered'])): ?><div class="alert alert-success">Registrasi berhasil! Silakan login.</div><?php endif; ?>

            <input type="hidden" name="redirect" value="<?= e($redirect) ?>">

            <div class="form-group">
                <label>Email atau Username</label>
                <input type="text" name="email" placeholder="Masukkan email atau username" required value="<?= e($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Password</label>
                <div class="input-icon-wrap">
                    <input type="password" name="password" placeholder="Masukkan password" required>
                    <button type="button" class="toggle-eye">&#128065;</button>
                </div>
            </div>

            <div class="form-row-between">
                <span></span>
                <a href="#">Lupa Password?</a>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Login</button>

            <p class="auth-switch">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
        </form>
    </div>
</div>

<script src="assets/js/main.js"></script>
</body>
</html>