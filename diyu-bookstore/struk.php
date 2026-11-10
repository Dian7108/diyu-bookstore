
<?php
require_once __DIR__ . '/includes/functions.php';

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

// Struk hanya bisa diakses setelah pembayaran lunas.
if ($order['status_pembayaran'] !== 'Lunas') {
    header('Location: order-success.php?id=' . urlencode($orderId));
    exit;
}

$itemStmt = $pdo->prepare("
    SELECT *
    FROM pesanan_detail
    WHERE pesanan_id = ?
");
$itemStmt->execute([$order['id']]);
$items = $itemStmt->fetchAll();

$pageTitle = 'Struk Pembelian';
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Struk <?= e($order['id_pesanan']) ?> - DiYu BookStore</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 30px 15px;
    background: #f4f1f8;
    font-family: Arial, sans-serif;
    color: #333;
}

.receipt {
    max-width: 700px;
    margin: auto;
    padding: 35px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 3px 15px rgba(0,0,0,.08);
}

.header {
    text-align: center;
    padding-bottom: 20px;
    border-bottom: 2px dashed #ccc;
}

.header h1 {
    margin: 0 0 8px;
    color: #8b5cf6;
}

.header p {
    margin: 5px 0;
    color: #777;
    font-size: 13px;
}

.info {
    margin: 22px 0;
}

.info-row {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 10px;
    font-size: 14px;
}

.info-row span:first-child {
    color: #777;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}

th, td {
    padding: 12px 8px;
    border-bottom: 1px solid #eee;
    text-align: left;
    font-size: 13px;
}

th {
    background: #f5f0ff;
    color: #6d28d9;
}

.right {
    text-align: right;
}

.total {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 2px dashed #ccc;
}

.total .info-row:last-child {
    font-size: 18px;
    font-weight: bold;
    color: #7c3aed;
}

.paid {
    margin: 20px 0;
    padding: 12px;
    text-align: center;
    color: #15803d;
    background: #dcfce7;
    border-radius: 8px;
    font-weight: bold;
}

.footer {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px dashed #ccc;
    text-align: center;
    color: #777;
    font-size: 12px;
}

.actions {
    max-width: 700px;
    margin: 20px auto;
    display: flex;
    gap: 10px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn {
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    background: #8b5cf6;
    color: white;
    cursor: pointer;
    text-decoration: none;
    font-size: 14px;
}

.btn-secondary {
    background: #666;
}

@media print {
    body {
        padding: 0;
        background: white;
    }

    .receipt {
        max-width: 100%;
        padding: 15px;
        box-shadow: none;
        border-radius: 0;
    }

    .actions {
        display: none;
    }

    @page {
        size: A4;
        margin: 15mm;
    }
}
</style>
</head>

<body>

<div class="receipt" id="strukPDF">

    <div class="header">
        <h1>DiYu BookStore</h1>
        <p>Struk Pembelian Buku</p>
        <p>Terima kasih telah berbelanja bersama kami.</p>
    </div>

    <div class="info">

        <div class="info-row">
            <span>Nomor Pesanan</span>
            <b><?= e($order['id_pesanan']) ?></b>
        </div>

        <div class="info-row">
            <span>Tanggal Pembelian</span>
            <b><?= e(formatTanggal($order['created_at'], true)) ?></b>
        </div>

        <div class="info-row">
            <span>Nama Penerima</span>
            <b><?= e($order['nama_penerima']) ?></b>
        </div>

        <div class="info-row">
            <span>Nomor Telepon</span>
            <b><?= e($order['no_telp']) ?></b>
        </div>

        <div class="info-row">
            <span>Alamat</span>
            <b style="text-align:right;max-width:60%">
                <?= e($order['alamat']) ?>
            </b>
        </div>

        <div class="info-row">
            <span>Metode Pembayaran</span>
            <b>
                <?= $order['metode'] === 'Transfer Bank'
                    ? 'Transfer Bank'
                    : 'Bayar di Tempat (COD)' ?>
            </b>
        </div>

        <?php if ($order['metode'] === 'Transfer Bank'): ?>
            <div class="info-row">
                <span>Bank Tujuan</span>
                <b><?= e($order['bank_tujuan'] ?? '-') ?></b>
            </div>
        <?php endif; ?>

        <div class="info-row">
            <span>Status Pembayaran</span>
            <b><?= e($order['status_pembayaran']) ?></b>
        </div>

    </div>

    <table>
        <thead>
            <tr>
                <th>Nama Buku</th>
                <th class="right">Harga</th>
                <th class="right">Qty</th>
                <th class="right">Subtotal</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($items as $it): ?>
                <tr>
                    <td><?= e($it['judul_buku']) ?></td>
                    <td class="right"><?= formatRupiah($it['harga']) ?></td>
                    <td class="right"><?= (int)$it['qty'] ?></td>
                    <td class="right"><?= formatRupiah($it['subtotal']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total">

        <div class="info-row">
            <span>Subtotal</span>
            <b><?= formatRupiah($order['subtotal']) ?></b>
        </div>

        <div class="info-row">
            <span>Ongkir</span>
            <b><?= formatRupiah($order['ongkir']) ?></b>
        </div>

        <div class="info-row">
            <span>Total Pembayaran</span>
            <b><?= formatRupiah($order['total']) ?></b>
        </div>

    </div>

    <div class="paid">
        PEMBAYARAN LUNAS
    </div>

    <div class="footer">
        <p>Struk ini merupakan bukti transaksi DiYu BookStore.</p>
        <p>Terima kasih telah berbelanja!</p>
    </div>

</div>

<div class="actions" id="tombolStruk">
    <button class="btn" id="downloadPDF" onclick="downloadStruk()">
        Download PDF
    </button>

    <button class="btn btn-secondary" onclick="window.print()">
        Cetak
    </button>

    <a href="index.php" class="btn btn-secondary">
        Kembali ke Beranda
    </a>
</div>

<!-- Library untuk membuat PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
function downloadStruk() {
    const element = document.getElementById('strukPDF');
    const button = document.getElementById('downloadPDF');

    if (typeof html2pdf === 'undefined') {
        alert('Library PDF belum berhasil dimuat. Periksa koneksi internet lalu coba lagi.');
        return;
    }

    button.disabled = true;
    button.textContent = 'Sedang membuat PDF...';

    const options = {
        margin: 10,
        filename: 'Struk-DiYu-<?= e($order['id_pesanan']) ?>.pdf',
        image: {
            type: 'jpeg',
            quality: 0.98
        },
        html2canvas: {
            scale: 2,
            useCORS: true,
            backgroundColor: '#ffffff'
        },
        jsPDF: {
            unit: 'mm',
            format: 'a4',
            orientation: 'portrait'
        },
        pagebreak: {
            mode: ['css', 'legacy']
        }
    };

    html2pdf()
        .set(options)
        .from(element)
        .save()
        .then(function() {
            button.disabled = false;
            button.textContent = 'Download PDF';
        })
        .catch(function(error) {
            console.error(error);
            alert('PDF gagal dibuat. Silakan coba kembali.');
            button.disabled = false;
            button.textContent = 'Download PDF';
        });
}
</script>

</body>
</html>
