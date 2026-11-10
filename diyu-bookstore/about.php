<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Tentang Kami';

$totalBuku = $pdo->query("SELECT COUNT(*) c FROM buku")->fetch()['c'];
$totalUser = $pdo->query("SELECT COUNT(*) c FROM users WHERE role='user'")->fetch()['c'];

require_once __DIR__ . '/includes/header.php';
?>

<div class="section">
    <div class="about-hero">
        <img src="https://images.unsplash.com/photo-1507842217343-583bb7270b66?w=1200&q=80" alt="DiYu BookStore">
        <div style="position:absolute;inset:0;display:flex;flex-direction:column;justify-content:center;padding:40px;color:#fff">
            <h1 style="font-size:30px;margin-bottom:8px">Tentang DiYu BookStore</h1>
            <p style="max-width:480px;color:rgba(255,255,255,.9)">DiYu BookStore berdiri sejak 2026 dengan komitmen menyediakan buku berkualitas untuk semua kalangan.</p>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:40px;align-items:start">
        <div>
            <h2 style="margin-bottom:14px">Visi Kami</h2>
            <p style="color:var(--text-muted);margin-bottom:26px;line-height:1.8">
                Menjadi toko buku online terpercaya dan terbaik di Indonesia, serta menjadi jembatan menuju masa depan yang lebih baik lewat literasi.
            </p>

            <div class="about-feature">
                <div><strong>Koleksi Lengkap</strong><p style="font-size:13px;color:var(--text-muted)">Beragam pilihan buku original dari berbagai genre.</p></div>
            </div>
            <div class="about-feature">
                <div><strong>Harga Terbaik</strong><p style="font-size:13px;color:var(--text-muted)">Harga bersaing untuk semua kalangan pembaca.</p></div>
            </div>
            <div class="about-feature">
                <div><strong>Pelayanan</strong><p style="font-size:13px;color:var(--text-muted)">Kepuasan pelanggan adalah prioritas kami.</p></div>
            </div>

            <div class="about-stats">
                <div><strong><?= $totalBuku ?>+</strong><span>Buku Tersedia</span></div>
                <div><strong>25K+</strong><span>Pelanggan</span></div>
                <div><strong>4.8</strong><span>Rating Toko</span></div>
                <div><strong>99%</strong><span>Kepuasan</span></div>
            </div>
        </div>

        <div style="border-radius:var(--radius);overflow:hidden">
            <img src="https://images.unsplash.com/photo-1521123845560-14093637aa7d?w=800&q=80" alt="Toko buku" style="width:100%;height:100%;object-fit:cover;border-radius:var(--radius)">
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
