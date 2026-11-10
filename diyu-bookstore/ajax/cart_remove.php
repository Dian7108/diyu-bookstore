<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

if (!isLoggedIn()) { echo json_encode(['success' => false]); exit; }

$keranjangId = (int)($_POST['keranjang_id'] ?? 0);
$pdo->prepare("DELETE FROM keranjang WHERE id = ? AND user_id = ?")->execute([$keranjangId, $_SESSION['user_id']]);

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
    'cartCount' => getCartCount(),
    'subtotalAllFormatted' => formatRupiah($subtotalAll),
    'totalFormatted' => formatRupiah($subtotalAll + $ongkir),
]);
