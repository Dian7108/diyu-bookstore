<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Hubungi Admin';

$success = false;
$error = '';
$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama   = trim($_POST['nama_lengkap'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $subjek = trim($_POST['subjek'] ?? '');
    $pesan  = trim($_POST['pesan'] ?? '');

    if ($nama === '' || $email === '' || $subjek === '' || $pesan === '') {
        $error = 'Semua kolom wajib diisi.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO pesan_kontak (user_id, nama_lengkap, email, subjek, pesan) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user['id'] ?? null, $nama, $email, $subjek, $pesan]);
        $success = true;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="contact-layout">
    <div class="contact-panel">
        <div class="contact-icon">&#127911;</div>
        <h2>Hubungi Admin</h2>
        <p style="margin:10px 0 20px;font-size:14px;color:rgba(255,255,255,.8)">Ada pertanyaan? Kirimkan pesan kepada kami dan tim kami akan segera merespon.</p>
        <div class="contact-method">&#9993; admin@diyubookstore.com</div>
        <div class="contact-method"> WhatsApp: 0812-3456-7890</div>
        <div class="contact-method"> Jl. Melati No.10, Jakarta Selatan</div>
    </div>

    <div class="checkout-card">
        <h3 style="margin-bottom:18px">Kirim Pesan</h3>

        <?php if ($success): ?>
            <div class="alert alert-success">Pesan kamu berhasil terkirim! Admin akan segera menghubungimu.</div>
        <?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

        <form method="post" action="contact.php">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama_lengkap" required value="<?= e($_POST['nama_lengkap'] ?? $user['nama'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required value="<?= e($_POST['email'] ?? $user['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Subjek</label>
                <input type="text" name="subjek" placeholder="Pertanyaan tentang produk" required value="<?= e($_POST['subjek'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Pesan</label>
                <textarea name="pesan" rows="5" placeholder="Halo Admin, saya ingin menanyakan apakah buku 'Laut Bercerita' tersedia? Terima kasih." required><?= e($_POST['pesan'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Kirim Pesan</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
