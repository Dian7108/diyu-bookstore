<?php
// $activeMenu harus di-set di halaman pemanggil sebelum include file ini
$activeMenu = $activeMenu ?? '';
?>
<aside class="admin-sidebar">
    <a href="dashboard.php" class="logo">
        <div class="logo-icon">D</div>
        <div class="logo-text">DIYU<br><small>BOOKSTORE</small></div>
    </a>
    <nav class="admin-nav">
    <a href="dashboard.php" class="<?= $activeMenu === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
    <a href="categories.php" class="<?= $activeMenu === 'categories' ? 'active' : '' ?>">Kategori Buku</a>
    <a href="books.php" class="<?= $activeMenu === 'books' ? 'active' : '' ?>">Data Buku</a>
    <a href="users.php" class="<?= $activeMenu === 'users' ? 'active' : '' ?>">List User</a>
    <a href="orders.php" class="<?= $activeMenu === 'orders' ? 'active' : '' ?>">List Pesanan</a>
    <a href="messages.php" class="<?= $activeMenu === 'messages' ? 'active' : '' ?>">Pesan Masuk</a>
    <a href="../logout.php">Logout</a>
</nav>
</aside>
