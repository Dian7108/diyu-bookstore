
<?php
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Upload Bukti Transfer';
requireLogin('index.php');

$orderId = trim($_GET['id'] ?? $_POST['id_pesanan'] ?? '');

$stmt = $pdo->prepare("
    SELECT *
    FROM pesanan
    WHERE id_pesanan = ? AND user_id = ?
");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: ../index.php');
    exit;
}

if ($order['metode'] !== 'Transfer Bank') {
    header('Location: ../order-success.php?id=' . urlencode($orderId));
    exit;
}

if ($order['status_pembayaran'] === 'Lunas') {
    header('Location: ../order-success.php?id=' . urlencode($orderId));
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        !isset($_FILES['bukti_transfer']) ||
        $_FILES['bukti_transfer']['error'] !== UPLOAD_ERR_OK
    ) {
        $error = 'Pilih foto bukti transfer terlebih dahulu.';
    } else {
        $file = $_FILES['bukti_transfer'];

        if ($file['size'] > 3 * 1024 * 1024) {
            $error = 'Ukuran file maksimal 3 MB.';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);

            $allowedTypes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'application/pdf' => 'pdf'
            ];

            if (!isset($allowedTypes[$mime])) {
                $error = 'Format file harus JPG, PNG, WEBP, atau PDF.';
            } else {
                // Folder penyimpanan berada di folder utama proyek.
                $uploadDir = __DIR__ . '/../uploads/bukti-transfer/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $htaccess = $uploadDir . '.htaccess';

                if (!file_exists($htaccess)) {
                    file_put_contents(
                        $htaccess,
                        "Options -Indexes\n<FilesMatch \"\\.(php|phtml|php3|php4|php5|phar)$\">\nRequire all denied\n</FilesMatch>"
                    );
                }

                $namaFile = 'bukti_' . bin2hex(random_bytes(16))
                    . '.' . $allowedTypes[$mime];

                $tujuan = $uploadDir . $namaFile;

                if (move_uploaded_file($file['tmp_name'], $tujuan)) {
                    try {
                        $pdo->beginTransaction();

                        $update = $pdo->prepare("
                            UPDATE pesanan
                            SET bukti_transfer = ?,
                                status_pembayaran = 'Menunggu Verifikasi'
                            WHERE id_pesanan = ?
                              AND user_id = ?
                              AND status_pembayaran != 'Lunas'
                        ");

                        $update->execute([
                            $namaFile,
                            $orderId,
                            $_SESSION['user_id']
                        ]);

                        if ($update->rowCount() === 0) {
                            throw new Exception('Pesanan tidak dapat diperbarui.');
                        }

                        $pdo->commit();

                        $success = 'Bukti transfer berhasil diunggah. Silakan tunggu verifikasi admin.';

                        $stmt->execute([
                            $orderId,
                            $_SESSION['user_id']
                        ]);
                        $order = $stmt->fetch();

                    } catch (Exception $ex) {
                        if ($pdo->inTransaction()) {
                            $pdo->rollBack();
                        }

                        if (file_exists($tujuan)) {
                            unlink($tujuan);
                        }

                        $error = 'Gagal menyimpan bukti transfer. Silakan coba lagi.';
                    }
                } else {
                    $error = 'File gagal diunggah. Periksa folder uploads.';
                }
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="section" style="max-width:650px;margin:0 auto">

    <div class="section-head">
        <h2>Upload Bukti Transfer</h2>
    </div>

    <div class="cart-card" style="padding:25px">

        <?php if ($error): ?>
            <div class="alert alert-danger" style="margin-bottom:18px">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success" style="margin-bottom:18px">
                <?= e($success) ?>
            </div>
        <?php endif; ?>

        <div style="margin-bottom:20px">
            <p style="margin-bottom:8px">
                <b>Nomor Pesanan:</b> <?= e($order['id_pesanan']) ?>
            </p>

            <p style="margin-bottom:8px">
                <b>Total Pembayaran:</b> <?= formatRupiah($order['total']) ?>
            </p>

            <p>
                <b>Status Pembayaran:</b> <?= e($order['status_pembayaran']) ?>
            </p>
        </div>

        <?php if ($order['status_pembayaran'] === 'Menunggu Verifikasi'): ?>
            <div class="alert alert-info">
                Bukti transfer sudah dikirim. Admin sedang memeriksa pembayaran kamu.
                Kamu masih bisa mengunggah ulang jika diperlukan.
            </div>
        <?php endif; ?>

        <?php if ($order['status_pembayaran'] !== 'Lunas'): ?>

            <form
                method="post"
                action="upload_bukti.php"
                enctype="multipart/form-data"
            >
                <input
                    type="hidden"
                    name="id_pesanan"
                    value="<?= e($order['id_pesanan']) ?>"
                >

                <div class="form-group">
                    <label for="bukti_transfer">
                        Pilih Foto Bukti Transfer
                    </label>

                    <input
                        type="file"
                        id="bukti_transfer"
                        name="bukti_transfer"
                        accept=".jpg,.jpeg,.png,.webp,.pdf"
                        required
                    >

                    <small style="color:var(--text-muted)">
                        Format JPG, PNG, WEBP, atau PDF. Maksimal 3 MB.
                    </small>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Kirim Bukti Transfer
                </button>
            </form>

        <?php endif; ?>

        <a
            href="../order-success.php?id=<?= urlencode($order['id_pesanan']) ?>"
            class="btn btn-outline btn-block"
            style="margin-top:12px"
        >
            Kembali ke Pesanan
        </a>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
