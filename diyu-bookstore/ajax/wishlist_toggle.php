<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

if (!isLoggedIn()) { echo json_encode(['success' => false, 'needLogin' => true]); exit; }

$bukuId = (int)($_POST['buku_id'] ?? 0);
$stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND buku_id = ?");
$stmt->execute([$_SESSION['user_id'], $bukuId]);
$existing = $stmt->fetch();

if ($existing) {
    $pdo->prepare("DELETE FROM wishlist WHERE id = ?")->execute([$existing['id']]);
    $inWishlist = false;
} else {
    $pdo->prepare("INSERT INTO wishlist (user_id, buku_id) VALUES (?, ?)")->execute([$_SESSION['user_id'], $bukuId]);
    $inWishlist = true;
}

echo json_encode(['success' => true, 'inWishlist' => $inWishlist]);
