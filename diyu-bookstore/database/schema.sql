-- =========================================================
-- DIYU BOOKSTORE - Database Schema
-- =========================================================
CREATE DATABASE IF NOT EXISTS diyu_bookstore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE diyu_bookstore;

-- ---------------------------------------------------------
-- Tabel users (Admin & User digabung dalam satu tabel, dibedakan lewat kolom role)
-- ---------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','user') NOT NULL DEFAULT 'user',
    tanggal_daftar DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel kategori buku
-- ---------------------------------------------------------
CREATE TABLE kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel data buku
-- ---------------------------------------------------------
CREATE TABLE buku (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    penulis VARCHAR(150) NOT NULL,
    kategori_id INT,
    harga DECIMAL(12,2) NOT NULL DEFAULT 0,
    diskon INT NOT NULL DEFAULT 0, -- persen
    stok INT NOT NULL DEFAULT 0,
    deskripsi TEXT,
    bahasa VARCHAR(50) DEFAULT 'Bahasa Indonesia',
    jumlah_halaman INT DEFAULT 0,
    rating DECIMAL(2,1) DEFAULT 0,
    gambar VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel keranjang (cart)
-- ---------------------------------------------------------
CREATE TABLE keranjang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    buku_id INT NOT NULL,
    qty INT NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (buku_id) REFERENCES buku(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel pesanan (orders)
-- ---------------------------------------------------------
CREATE TABLE pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pesanan VARCHAR(20) NOT NULL UNIQUE, -- contoh: ORD001
    user_id INT NOT NULL,
    nama_penerima VARCHAR(150) NOT NULL,
    no_telp VARCHAR(30) NOT NULL,
    alamat TEXT NOT NULL,
    catatan VARCHAR(255),
    subtotal DECIMAL(12,2) NOT NULL,
    ongkir DECIMAL(12,2) NOT NULL DEFAULT 15000,
    total DECIMAL(12,2) NOT NULL,
    metode ENUM('COD','Transfer Bank') NOT NULL DEFAULT 'COD',
    status ENUM('Menunggu','Diproses','Dikirim','Selesai','Dibatalkan') NOT NULL DEFAULT 'Menunggu',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel detail pesanan (item per pesanan)
-- ---------------------------------------------------------
CREATE TABLE pesanan_detail (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT NOT NULL,
    buku_id INT NOT NULL,
    judul_buku VARCHAR(200) NOT NULL,
    harga DECIMAL(12,2) NOT NULL,
    qty INT NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(id) ON DELETE CASCADE,
    FOREIGN KEY (buku_id) REFERENCES buku(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel pesan kontak ke admin
-- ---------------------------------------------------------
CREATE TABLE pesan_kontak (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    nama_lengkap VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subjek VARCHAR(200) NOT NULL,
    pesan TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel wishlist
-- ---------------------------------------------------------
CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    buku_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_wishlist (user_id, buku_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (buku_id) REFERENCES buku(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- DATA AWAL (SEED)
-- =========================================================

-- PENTING: Data akun (admin & user contoh) TIDAK di-insert lewat file SQL ini,
-- karena password harus di-hash dengan password_hash() PHP (bcrypt) agar aman & valid.
-- Setelah import schema ini, jalankan sekali file: database/seed.php lewat browser
-- (contoh: http://localhost/bookstore/database/seed.php) untuk membuat akun admin & user contoh.

INSERT INTO kategori (nama_kategori) VALUES
('Fiksi'),('Pengembangan Diri'),('Bisnis'),('Sejarah'),('Fantasy'),('Teknologi'),('Non-Fiksi');

INSERT INTO buku (judul, penulis, kategori_id, harga, diskon, stok, deskripsi, jumlah_halaman, rating, gambar) VALUES
('Laut Bercerita', 'Leila S. Chudori', 1, 79000, 20, 50, 'Laut Bercerita adalah novel yang mengangkat perjuangan, cinta, kehilangan, dan harapan masa lalu yang masih terasa gaungnya hingga kini.', 379, 4.8, 'laut-bercerita.jpg'),
('Atomic Habits', 'James Clear', 2, 89000, 0, 40, 'Panduan praktis dan terbukti untuk membangun kebiasaan baik dan menghilangkan kebiasaan buruk, sedikit demi sedikit.', 320, 4.9, 'atomic-habits.jpg'),
('The Hobbit', 'J.R.R. Tolkien', 5, 95000, 0, 30, 'Petualangan Bilbo Baggins yang tak terduga bersama para kurcaci dalam mencari harta yang dijaga naga Smaug.', 310, 4.7, 'the-hobbit.jpg'),
('1984', 'George Orwell', 1, 75000, 0, 45, 'Kisah distopia klasik tentang pengawasan totaliter dan perjuangan mempertahankan kebebasan berpikir.', 328, 4.8, '1984.jpg'),
('Laskar Pelangi', 'Andrea Hirata', 1, 75000, 0, 35, 'Kisah inspiratif sepuluh anak dari Belitung yang berjuang meraih pendidikan di tengah keterbatasan.', 529, 4.7, 'laskar-pelangi.jpg'),
('Laut dan Danau Misteri', 'Dee Lestari', 1, 80000, 0, 25, 'Kumpulan kisah misteri dan filosofi kehidupan dari penulis kenamaan Dee Lestari.', 280, 4.5, 'laut-misteri.jpg'),
('Laut dalam Sepi', 'Ayu Utami', 1, 78000, 0, 20, 'Kumpulan cerpen reflektif tentang kesunyian dan pencarian makna hidup.', 210, 4.4, 'laut-sepi.jpg'),
('Laut Kita', 'Ayu Utami', 1, 82000, 0, 22, 'Narasi puitis tentang laut dan hubungan manusia dengan alam.', 240, 4.3, 'laut-kita.jpg'),
('Di Bawah Laut', 'Jules Verne', 5, 88000, 0, 18, 'Petualangan klasik menjelajahi kedalaman samudra bersama Kapten Nemo.', 350, 4.6, 'di-bawah-laut.jpg'),
('Sapiens', 'Yuval Noah Harari', 3, 95000, 0, 40, 'Sejarah singkat umat manusia dari zaman batu hingga era modern.', 443, 4.9, 'sapiens.jpg');

-- =========================================================
-- CATATAN:
-- Semua akun contoh di atas menggunakan password: password123
-- Silakan login dengan salah satu email di atas + password123
-- =========================================================
