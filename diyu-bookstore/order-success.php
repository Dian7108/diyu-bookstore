
<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Pesanan Berhasil';
requireLogin('index.php');

$orderId = trim($_GET['id'] ?? '');

$stmt = $pdo->prepare("
    SELECT *
    FROM pesanan
    WHERE id_pesanan = ? AND user_id = ?
");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: index.php');
    exit;
}

// Proses pembayaran kartu kredit simulasi
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['simulasi_bayar'])) {

    if ($order['metode'] !== 'Kartu Kredit') {
        header('Location: order-success.php?id=' . urlencode($orderId));
        exit;
    }

    if ($order['status_pembayaran'] !== 'Lunas') {

        $stmtBayar = $pdo->prepare("
            UPDATE pesanan
            SET status_pembayaran = 'Lunas'
            WHERE id_pesanan = ?
            AND user_id = ?
            AND metode = 'Kartu Kredit'
            AND status_pembayaran = 'Belum Dibayar'
        ");

        $stmtBayar->execute([
            $orderId,
            $_SESSION['user_id']
        ]);
    }

    header('Location: order-success.php?id=' . urlencode($orderId));
    exit;
}

// Data rekening dummy
$rekening = [
    'BNI' => [
        'nama' => 'BNI',
        'nomor' => '1234567890',
        'pemilik' => 'DiYu BookStore'
    ],
    'BCA' => [
        'nama' => 'BCA',
        'nomor' => '0987654321',
        'pemilik' => 'DiYu BookStore'
    ]
];

$bankDipilih = $order['bank_tujuan'] ?? '';
$dataRekening = $rekening[$bankDipilih] ?? null;

require_once __DIR__ . '/includes/header.php';
?>

<div class="section" style="max-width:650px;margin:0 auto;text-align:center">

    <div class="cart-card" style="padding:40px 25px">

        <div style="
            width:70px;
            height:70px;
            border-radius:50%;
            background:var(--success-bg);
            color:var(--success);
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:32px;
            margin:0 auto 18px;
        ">
            &#10003;
        </div>

        <h2 style="margin-bottom:8px">
            Pesanan Berhasil Dibuat!
        </h2>

        <p style="color:var(--text-muted);margin-bottom:22px">
            Terima kasih sudah berbelanja di DiYu BookStore.
        </p>

        <!-- RINGKASAN PESANAN -->
        <div style="
            background:var(--bg);
            border-radius:12px;
            padding:20px;
            text-align:left;
            margin-bottom:20px;
        ">

            <div class="summary-row">
                <span>Nomor Pesanan</span>
                <b><?= e($order['id_pesanan']) ?></b>
            </div>

            <div class="summary-row">
                <span>Total Pembayaran</span>
                <b><?= formatRupiah($order['total']) ?></b>
            </div>

            <div class="summary-row">
                <span>Metode Pembayaran</span>
                <b><?= e($order['metode']) ?></b>
            </div>

            <?php if ($order['metode'] === 'Transfer Bank'): ?>
                <div class="summary-row">
                    <span>Bank Tujuan</span>
                    <b><?= e($bankDipilih ?: '-') ?></b>
                </div>
            <?php endif; ?>

            <div class="summary-row">
                <span>Status Pesanan</span>
                <b><?= e($order['status']) ?></b>
            </div>

            <div class="summary-row">
                <span>Status Pembayaran</span>
                <b><?= e($order['status_pembayaran']) ?></b>
            </div>

        </div>

        <!-- PEMBAYARAN KARTU KREDIT -->
        <?php if ($order['metode'] === 'Kartu Kredit'): ?>

            <?php if ($order['status_pembayaran'] === 'Belum Dibayar'): ?>

                <div style="
                    background:var(--bg);
                    border:1px solid var(--border);
                    border-radius:12px;
                    padding:20px;
                    margin-bottom:20px;
                    text-align:left;
                ">

                    <h3 style="margin-bottom:12px">
                        Pembayaran Kartu Kredit
                    </h3>

                    <div class="alert alert-info">
                        Ini adalah halaman simulasi pembayaran.
                        Gunakan data contoh di bawah, bukan data kartu asli.
                    </div>

                    <!-- GAMBAR KARTU SIMULASI -->
                    <div style="
                        background:linear-gradient(135deg,#4c2a85,#8b5cf6);
                        color:white;
                        border-radius:14px;
                        padding:22px;
                        margin:18px 0;
                    ">

                        <div style="
                            display:flex;
                            justify-content:space-between;
                            align-items:center;
                        ">
                            <strong>DiYu BookStore</strong>
                            <span style="font-size:13px">CREDIT CARD</span>
                        </div>

                        <div style="
                            font-size:21px;
                            letter-spacing:3px;
                            margin:28px 0 20px;
                        ">
                            XXXX XXXX XXXX 1234
                        </div>

                        <div style="
                            display:flex;
                            justify-content:space-between;
                            font-size:12px;
                        ">
                            <div>
                                <div style="opacity:.75">CARD HOLDER</div>
                                <strong>CONTOH PENGGUNA</strong>
                            </div>

                            <div>
                                <div style="opacity:.75">EXPIRES</div>
                                <strong>12/30</strong>
                            </div>
                        </div>

                    </div>

                    <h4 style="margin-bottom:12px">
                        Data Kartu (Contoh)
                    </h4>

                    <div class="form-group">
                        <label>Nama Pemegang Kartu</label>
                        <input type="text"
                            value="CONTOH PENGGUNA"
                            readonly>
                    </div>

                    <div class="form-group">
                        <label>Nomor Kartu</label>
                        <input type="text"
                            value="XXXX XXXX XXXX 1234"
                            readonly>
                    </div>

                    <div style="
                        display:grid;
                        grid-template-columns:1fr 1fr;
                        gap:12px;
                    ">

                        <div class="form-group">
                            <label>Masa Berlaku</label>
                            <input type="text"
                                value="12/30"
                                readonly>
                        </div>

                        <div class="form-group">
                            <label>CVV</label>
                            <input type="text"
                                value="***"
                                readonly>
                        </div>

                    </div>

                    <div class="alert alert-warning">
                        Data di atas hanya contoh tampilan.
                        Tidak ada nomor kartu atau CVV asli yang diminta maupun disimpan.
                    </div>

                    <div style="margin-top:18px">
                        <p style="font-size:13px;color:var(--text-muted)">
                            Total Pembayaran
                        </p>

                        <h2 style="color:#7c3aed;margin-top:5px">
                            <?= formatRupiah($order['total']) ?>
                        </h2>
                    </div>

                    <form method="post"
                        action="order-success.php?id=<?= urlencode($orderId) ?>"
                        onsubmit="return confirm('Lanjutkan simulasi pembayaran kartu kredit?')">

                        <input type="hidden" name="simulasi_bayar" value="1">

                        <button type="submit"
                            class="btn btn-primary btn-block"
                            style="margin-top:18px">
                            Bayar Sekarang (Simulasi)
                        </button>

                    </form>

                </div>

            <?php else: ?>

                <div class="alert alert-success" style="text-align:left">
                    <b>Pembayaran Kartu Kredit Berhasil!</b><br>
                    Pembayaran simulasi sudah dikonfirmasi.
                    Kamu dapat mengunduh struk pembelian.
                </div>

            <?php endif; ?>

        <!-- TRANSFER BANK -->
        <?php elseif ($order['metode'] === 'Transfer Bank'): ?>

            <?php if ($order['status_pembayaran'] === 'Belum Dibayar'): ?>

                <div style="
                    background:var(--bg);
                    border:1px solid var(--border);
                    border-radius:12px;
                    padding:20px;
                    margin-bottom:20px;
                    text-align:left;
                ">

                    <h3 style="margin-bottom:8px">
                        Instruksi Pembayaran
                    </h3>

                    <div class="alert alert-info">
                        Silakan transfer sesuai total pembayaran ke rekening berikut.
                        Setelah transfer, unggah foto bukti pembayaran.
                    </div>

                    <div style="
                        background:white;
                        border:1px dashed #8b5cf6;
                        border-radius:10px;
                        padding:18px;
                        margin-top:15px;
                    ">

                        <?php if ($dataRekening): ?>

                            <p style="font-size:13px;color:#777">
                                Bank Tujuan
                            </p>

                            <h3 style="color:#6d28d9;margin:5px 0 15px">
                                <?= e($dataRekening['nama']) ?>
                            </h3>

                            <p style="font-size:13px;color:#777">
                                Nomor Rekening
                            </p>

                            <div style="
                                display:flex;
                                align-items:center;
                                justify-content:space-between;
                                gap:10px;
                                flex-wrap:wrap;
                            ">

                                <h2 style="margin:5px 0;color:#333">
                                    <?= e($dataRekening['nomor']) ?>
                                </h2>

                                <button
                                    type="button"
                                    class="btn btn-outline"
                                    onclick="salinRekening('<?= e($dataRekening['nomor']) ?>')"
                                >
                                    Salin
                                </button>

                            </div>

                            <p style="font-size:13px;color:#777;margin-top:15px">
                                Atas Nama
                            </p>

                            <b><?= e($dataRekening['pemilik']) ?></b>

                        <?php else: ?>

                            <p style="color:#dc2626">
                                Data rekening bank belum tersedia.
                                Silakan hubungi admin.
                            </p>

                        <?php endif; ?>

                    </div>

                    <div style="margin-top:18px">
                        <p style="font-size:13px;color:#777">
                            Total yang harus dibayar
                        </p>

                        <h2 style="color:#7c3aed;margin-top:5px">
                            <?= formatRupiah($order['total']) ?>
                        </h2>
                    </div>

                    <div class="alert alert-warning" style="margin-top:15px">
                        <b>SIMULASI PEMBAYARAN</b><br>
                        Nomor rekening di atas merupakan data dummy untuk keperluan proyek,
                        bukan rekening asli.
                    </div>

                </div>

                <?php if ($dataRekening): ?>
                    <a
                        href="upload/upload_bukti.php?id=<?= urlencode($order['id_pesanan']) ?>"
                        class="btn btn-primary btn-block"
                        style="margin-bottom:10px"
                    >
                        Upload Bukti Transfer
                    </a>
                <?php endif; ?>

            <?php elseif ($order['status_pembayaran'] === 'Menunggu Verifikasi'): ?>

                <div class="alert alert-info" style="text-align:left">
                    <b>Bukti transfer sudah dikirim!</b><br>
                    Admin sedang memeriksa bukti pembayaran kamu.
                    Tunggu sampai pembayaran dikonfirmasi.
                </div>

                <a
                    href="upload/upload_bukti.php?id=<?= urlencode($order['id_pesanan']) ?>"
                    class="btn btn-outline btn-block"
                    style="margin-bottom:10px"
                >
                    Upload Ulang Bukti Transfer
                </a>

            <?php endif; ?>

        <?php endif; ?>

        <!-- STRUK -->
        <?php if ($order['status_pembayaran'] === 'Lunas'): ?>

            <div class="alert alert-success" style="text-align:left">
                <b>Pembayaran berhasil dikonfirmasi!</b><br>
                Struk pembelian sudah tersedia.
            </div>

            <a
                href="struk.php?id=<?= urlencode($order['id_pesanan']) ?>"
                class="btn btn-primary btn-block"
                style="margin-bottom:10px"
            >
                Unduh Struk PDF
            </a>

        <?php else: ?>

            <p style="
                font-size:13px;
                color:var(--text-muted);
                margin-bottom:15px;
            ">
                Struk PDF dapat diunduh setelah pembayaran dikonfirmasi.
            </p>

        <?php endif; ?>

        <a href="index.php" class="btn btn-outline">
            Kembali ke Beranda
        </a>

    </div>
</div>

<script>
function salinRekening(nomor) {
    navigator.clipboard.writeText(nomor)
        .then(function() {
            alert('Nomor rekening berhasil disalin!');
        })
        .catch(function() {
            alert('Nomor rekening gagal disalin. Silakan salin secara manual.');
        });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
