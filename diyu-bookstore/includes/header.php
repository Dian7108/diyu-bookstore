
<?php
require_once __DIR__ . '/functions.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$cartCount = getCartCount();
$user = currentUser();

// Menyesuaikan alamat jika halaman berada di folder upload
$basePath = (strpos($_SERVER['SCRIPT_NAME'], '/upload/') !== false) ? '../' : '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>
<?= isset($pageTitle) ? e($pageTitle) . ' - DiYu BookStore' : 'DiYu BookStore' ?>
</title>

<link rel="stylesheet" href="<?= $basePath ?>assets/css/style.css">
</head>

<body>

<header class="topbar">
    <div class="topbar-inner">

        <!-- LOGO -->
        <a href="<?= $basePath ?>index.php" class="logo">
            <div class="logo-icon">D</div>
            <div class="logo-text">
                DIYU<br>
                <small>BOOKSTORE</small>
            </div>
        </a>

        <!-- MENU NAVIGASI -->
        <nav class="nav-main">

            <a href="<?= $basePath ?>index.php"
               class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">
                Beranda
            </a>

            <a href="<?= $basePath ?>search.php?tab=kategori"
               class="<?= isset($_GET['tab']) && $_GET['tab'] === 'kategori' ? 'active' : '' ?>">
                Kategori
            </a>

            <a href="<?= $basePath ?>search.php?tab=buku"
               class="<?= $currentPage === 'search.php' && (!isset($_GET['tab']) || $_GET['tab'] === 'buku') ? 'active' : '' ?>">
                Buku
            </a>

            <a href="<?= $basePath ?>about.php"
               class="<?= $currentPage === 'about.php' ? 'active' : '' ?>">
                About Us
            </a>

            <a href="<?= $basePath ?>contact.php"
               class="<?= $currentPage === 'contact.php' ? 'active' : '' ?>">
                Contact
            </a>

        </nav>

        <!-- AKSI HEADER -->
        <div class="topbar-actions">

            <!-- PENCARIAN -->
            <form action="<?= $basePath ?>search.php" method="get" class="search-box">
                <input type="hidden" name="tab" value="buku">
                <span>&#128269;</span>
                <input type="text" name="q" placeholder="Cari judul atau penulis...">
            </form>

            <!-- WISHLIST -->
            <a href="<?= $basePath ?>wishlist.php"
               class="icon-btn"
               title="Wishlist">
                <span>&#9825;</span>
            </a>

            <?php if ($user): ?>

                <?php if ($user['role'] === 'admin'): ?>
                    <a href="<?= $basePath ?>admin/dashboard.php"
                       class="icon-btn" title="Dashboard Admin">
                        <span>&#9881;</span>
                    </a>
                <?php endif; ?>

                <!-- KERANJANG -->
                <a href="<?= $basePath ?>cart.php"
                   class="icon-btn" title="Keranjang">
                    <span>&#128722;</span>

                    <span class="cart-badge"
                          style="<?= $cartCount == 0 ? 'display:none' : '' ?>">
                        <?= $cartCount ?>
                    </span>
                </a>

                <!-- PROFIL -->
                <a href="<?= $basePath ?>profile.php"
                   class="user-chip" title="<?= e($user['nama']) ?>">
                    <div class="user-avatar">
                        <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                    </div>
                </a>

                <!-- LOGOUT -->
                <a href="<?= $basePath ?>logout.php"
                   class="icon-btn" title="Keluar">
                    <span>&#128682;</span>
                </a>

            <?php else: ?>

                <a href="<?= $basePath ?>login.php"
                   class="icon-btn" title="Login">
                    <span>&#128100;</span>
                </a>

                <a href="<?= $basePath ?>cart.php"
                   class="icon-btn" title="Keranjang">
                    <span>&#128722;</span>
                    <span class="cart-badge" style="display:none">0</span>
                </a>

            <?php endif; ?>

        </div>

    </div>
</header>
