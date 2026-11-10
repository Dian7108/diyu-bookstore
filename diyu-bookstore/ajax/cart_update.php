<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

if (!isLoggedIn()) { echo json_encode(['success' => false]); exit; }

$keranjangId = (int)($_POST['keranjang_id'] ?? 0);
$qty = max(1, (int)($_POST['qty'] ?? 1));

$stmt = $pdo->prepare("
    SELECT ke.*, b.harga, b.diskon, b.stok FROM keranjang ke
    JOIN buku b ON ke.buku_id = b.id
    WHERE ke.id = ? AND ke.user_id = ?
");
$stmt->execute([$keranjangId, $_SESSION['user_id']]);
$item = $stmt->fetch();

if (!$item) { echo json_encode(['success' => false]); exit; }

if ($qty > $item['stok']) $qty = $item['stok'];
$pdo->prepare("UPDATE keranjang SET qty = ? WHERE id = ?")->execute([$qty, $keranjangId]);

// hitung ulang total keranjang
$stmtAll = $pdo->prepare("
    SELECT ke.qty, b.harga, b.diskon FROM keranjang ke
    JOIN buku b ON ke.buku_id = b.id WHERE ke.user_id = ?
");
$stmtAll->execute([$_SESSION['user_id']]);
$all = $stmtAll->fetchAll();

$subtotalAll = 0;
foreach ($all as $a) $subtotalAll += hargaSetelahDiskon($a['harga'], $a['diskon']) * $a['qty'];
$ongkir = $subtotalAll > 0 ? 15000 : 0;

echo json_encode([
    'success' => true,
    'qty' => $qty,
    'subtotalFormatted' => formatRupiah(hargaSetelahDiskon($item['harga'], $item['diskon']) * $qty),
    'subtotalAllFormatted' => formatRupiah($subtotalAll),
    'totalFormatted' => formatRupiah($subtotalAll + $ongkir),
    'cartCount' => getCartCount(),
]);
