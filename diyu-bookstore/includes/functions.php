
<?php
/**
 * Fungsi-fungsi bantu global untuk DiYu BookStore
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

/* ================== AUTH HELPERS ================== */

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

function currentUser() {
    if (!isLoggedIn()) return null;
    return [
        'id'    => $_SESSION['user_id'],
        'nama'  => $_SESSION['nama_lengkap'],
        'email' => $_SESSION['email'],
        'role'  => $_SESSION['role'],
    ];
}

/** Wajib login user biasa, kalau belum -> redirect ke login */
function requireLogin($redirectAfter = '') {
    if (!isLoggedIn()) {
        $target = $redirectAfter ?: basename($_SERVER['PHP_SELF']);
        header('Location: login.php?redirect=' . urlencode($target));
        exit;
    }
}

/** Wajib admin, kalau bukan admin -> tolak akses */
function requireAdmin() {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit;
    }
    if ($_SESSION['role'] !== 'admin') {
        header('Location: ../index.php');
        exit;
    }
}

/* ================== FORMAT HELPERS ================== */

function formatRupiah($angka) {
    return 'Rp' . number_format((float)$angka, 0, ',', '.');
}

function formatTanggal($tanggal, $withTime = false) {
    $bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    $ts = strtotime($tanggal);
    $hasil = date('d', $ts) . ' ' . $bulan[(int)date('n', $ts) - 1] . ' ' . date('Y', $ts);
    if ($withTime) $hasil .= ' ' . date('H:i', $ts);
    return $hasil;
}

function hargaSetelahDiskon($harga, $diskonPersen) {
    if ($diskonPersen <= 0) return $harga;
    return $harga - ($harga * $diskonPersen / 100);
}

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/* ================== CART HELPERS ================== */

function getCartCount() {
    global $pdo;
    if (!isLoggedIn()) return 0;
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(qty),0) AS total FROM keranjang WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return (int)$stmt->fetch()['total'];
}

/* ================== ORDER ID GENERATOR ================== */

function generateOrderId($pdo) {
    // Format tanggal DDMMYY
    $tanggal = date('dmy');

    // Awalan nomor pesanan hari ini
    $prefix = 'ORD-' . $tanggal . '-';

    // Ambil nomor urut terakhir pada tanggal yang sama
    $stmt = $pdo->prepare("
        SELECT id_pesanan
        FROM pesanan
        WHERE id_pesanan LIKE ?
        ORDER BY id_pesanan DESC
        LIMIT 1
    ");

    $stmt->execute([$prefix . '%']);
    $lastOrder = $stmt->fetch(PDO::FETCH_ASSOC);

    // Tentukan nomor urut berikutnya
    $nextNumber = $lastOrder
        ? ((int) substr($lastOrder['id_pesanan'], -3) + 1)
        : 1;

    return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
}

/* ================== BOOK COVER HELPER ================== */

/** Ambil URL cover buku: pakai file upload kalau ada, kalau tidak pakai placeholder unik per buku */

function bookCover($buku) {
    $namaFile = $buku['gambar'] ?? '';
    $file = __DIR__ . '/../uploads/' . $namaFile;

    if (!empty($namaFile) && file_exists($file)) {
        // Jika dibuka dari folder admin, mundur satu folder
        $basePath = strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false
            ? '../'
            : '';

        return $basePath . 'uploads/' . rawurlencode($namaFile);
    }

    $seed = urlencode($buku['judul'] ?? ('buku' . ($buku['id'] ?? rand())));
    return 'https://picsum.photos/seed/' . $seed . '/300/420';
}

/* ================== STATUS BADGE HELPER ================== */

function statusBadgeClass($status) {
    switch ($status) {
        case 'Menunggu':  return 'badge badge-warning';
        case 'Diproses':  return 'badge badge-info';
        case 'Dikirim':   return 'badge badge-primary';
        case 'Selesai':   return 'badge badge-success';
        case 'Dibatalkan':return 'badge badge-danger';
        default: return 'badge';
    }
}