# DiYu BookStore

Website toko buku online (HTML, CSS, JavaScript, PHP native + MySQL) dengan 2 aktor:
**Admin** (kelola kategori, buku, user, dan pesanan) dan **User** (belanja buku).
Login Admin & User digabung dalam satu tabel `users` (dibedakan lewat kolom `role`).

## 1. Kebutuhan
- Web server dengan PHP 7.4+ (disarankan PHP 8.x) — misalnya **XAMPP**, **Laragon**, atau **MAMP**
- MySQL / MariaDB
- Browser modern

## 2. Instalasi

1. **Salin folder project**
   Salin seluruh folder `bookstore/` ke dalam folder web server Anda, contoh:
   - XAMPP (Windows): `C:\xampp\htdocs\bookstore`
   - Laragon: `C:\laragon\www\bookstore`
   - MAMP (Mac): `/Applications/MAMP/htdocs/bookstore`

2. **Buat database**
   - Buka **phpMyAdmin**, buat database baru bernama `diyu_bookstore` (atau import langsung, database akan otomatis dibuat oleh script).
   - Import file `database/schema.sql` lewat tab **Import** di phpMyAdmin. File ini akan otomatis membuat database `diyu_bookstore` beserta seluruh tabelnya (users, kategori, buku, keranjang, pesanan, pesanan_detail, pesan_kontak, wishlist), dan mengisi data kategori & buku contoh.

3. **Konfigurasi koneksi database**
   Buka `config/database.php`, sesuaikan jika perlu:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'diyu_bookstore');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

4. **Buat akun contoh (admin & user) dengan password ter-hash**
   Karena password harus di-hash dengan `password_hash()` PHP (bukan lewat SQL mentah), jalankan file
   `database/seed.php` **satu kali** lewat browser, contoh:
   ```
   http://localhost/bookstore/database/seed.php
   ```
   Ini akan membuat akun-akun berikut (semua password: **password123**):

   | Nama          | Email                         | Role  |
   |---------------|-------------------------------|-------|
   | Admin DiYu    | admin@diyubookstore.com       | admin |
   | Dian Saftiri  | dian.saftri@email.com         | user  |
   | Budi Santoso  | budi.santoso@email.com        | user  |
   | Siti Aisyah   | siti.aisyah@email.com         | user  |
   | Ahmad Rizki   | ahmad.rizki@email.com         | user  |
   | Rina Kartika  | rina.kartika@email.com        | user  |

   Setelah selesai, sebaiknya hapus/pindahkan file `database/seed.php` dari server.

5. **Beri izin folder upload**
   Pastikan folder `uploads/` bisa ditulisi oleh web server (untuk upload cover buku dari admin).

6. **Akses website**
   ```
   http://localhost/bookstore/index.php
   ```
   Login admin lewat `login.php`, lalu akan otomatis diarahkan ke `admin/dashboard.php`.

## 3. Struktur Folder

```
bookstore/
├── admin/                 -> Semua halaman khusus admin
│   ├── dashboard.php       (10. Dashboard Admin)
│   ├── categories.php      (11. Kelola Kategori Buku)
│   ├── books.php           (12. Kelola Data Buku)
│   ├── users.php           (13. List User)
│   ├── orders.php          (14. List Pesanan User)
│   ├── order_detail.php    (Detail pesanan + update status)
│   └── messages.php        (Pesan masuk dari Contact Admin)
├── ajax/                  -> Endpoint AJAX (cart & wishlist)
├── assets/
│   ├── css/style.css       -> Semua styling (sesuai desain)
│   └── js/main.js          -> Interaksi (cart, modal, toast, dll)
├── config/database.php    -> Koneksi PDO MySQL
├── database/
│   ├── schema.sql          -> Struktur tabel + data kategori/buku
│   └── seed.php             -> Buat akun admin/user contoh (hash password)
├── includes/
│   ├── functions.php       -> Helper (auth, format rupiah, dsb)
│   ├── header.php / footer.php  -> Layout halaman user
│   └── admin_sidebar.php   -> Sidebar admin
├── uploads/                -> Cover buku hasil upload admin
├── index.php               (1. Homepage / Landing Page)
├── login.php                (2. Halaman Login)
├── register.php              (3. Halaman Registrasi)
├── product-detail.php        (4. Halaman Detail Buku)
├── search.php                (5. Halaman Pencarian Buku)
├── cart.php                   (6. Halaman Keranjang)
├── checkout.php                (7. Halaman Payment/Checkout)
├── contact.php                  (8. Halaman Contact Admin)
├── about.php                     (9. Halaman About Us)
├── order-success.php
├── profile.php
└── logout.php
```

## 4. Fitur yang tersedia

**User**
- Registrasi & Login (password di-hash dengan bcrypt)
- Melihat halaman About Us
- Kirim pesan ke Admin (Contact Admin)
- Pencarian & filter buku (kategori, harga, rating)
- Tambah ke keranjang (AJAX, tanpa reload halaman)
- Tambah ke wishlist
- Checkout dengan pembayaran **Bayar di Tempat (COD)** atau Transfer Bank
- Riwayat pesanan pribadi (profile.php)

**Admin** (login dengan akun ber-role `admin`)
- Dashboard (statistik + grafik penjualan per bulan)
- Kelola Kategori Buku (Add / Update / Delete)
- Kelola Data Buku (Add / Update / Delete, termasuk upload cover)
- List User yang terdaftar
- List Pesanan User (filter status, pencarian, pagination) + ubah status pesanan
- Lihat pesan masuk dari Contact Admin

## 5. Catatan Keamanan
- Semua query database menggunakan **Prepared Statement (PDO)** untuk mencegah SQL Injection.
- Password disimpan menggunakan **password_hash() / bcrypt**, tidak pernah disimpan sebagai teks biasa.
- Validasi sesi (`requireLogin()`, `requireAdmin()`) diterapkan di setiap halaman yang butuh otentikasi.
- Untuk penggunaan produksi, tambahkan proteksi CSRF token pada form serta validasi upload file yang lebih ketat.
