<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'needLogin' => true, 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

$bukuId = (int)($_POST['buku_id'] ?? 0);
$qty    = max(1, (int)($_POST['qty'] ?? 1));

$stmt = $pdo->prepare("SELECT * FROM buku WHERE id = ?");
$stmt->execute([$bukuId]);
$buku = $stmt->fetch();

if (!$buku) {
    echo json_encode(['success' => false, 'message' => 'Buku tidak ditemukan']);
    exit;
}

$stmtCheck = $pdo->prepare("SELECT * FROM keranjang WHERE user_id = ? AND buku_id = ?");
$stmtCheck->execute([$_SESSION['user_id'], $bukuId]);
$existing = $stmtCheck->fetch();

$qtyBaru = $qty + ($existing['qty'] ?? 0);
if ($qtyBaru > $buku['stok']) $qtyBaru = $buku['stok'];

if ($existing) {
    $pdo->prepare("UPDATE keranjang SET qty = ? WHERE id = ?")->execute([$qtyBaru, $existing['id']]);
} else {
    $pdo->prepare("INSERT INTO keranjang (user_id, buku_id, qty) VALUES (?, ?, ?)")->execute([$_SESSION['user_id'], $bukuId, $qtyBaru]);
}

echo json_encode(['success' => true, 'cartCount' => getCartCount()]);
