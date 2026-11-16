-- =====================================================================
-- MON CHÉRI - LUXURY HEELS MARKETPLACE
-- Skema Database Relasional (MySQL / MariaDB) - MySQLi
-- File: database/marketplace.sql  (versi LOKAL / XAMPP)
-- Catatan: untuk InfinityFree gunakan database/infinityfree_import.sql
-- =====================================================================

DROP DATABASE IF EXISTS db_marketplace;
CREATE DATABASE db_marketplace
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_general_ci;
USE db_marketplace;
SET NAMES utf8mb4;

-- ---------------------------------------------------------------------
-- 1. TABEL users  (Admin/Penjual dan Pembeli)
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id_user       INT AUTO_INCREMENT PRIMARY KEY,
    nama          VARCHAR(100)  NOT NULL,
    email         VARCHAR(100)  NOT NULL UNIQUE,
    password      VARCHAR(255)  NOT NULL,
    role          ENUM('admin','pembeli') NOT NULL DEFAULT 'pembeli',
    no_hp         VARCHAR(20)   DEFAULT NULL,
    alamat        TEXT          DEFAULT NULL,
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 2. TABEL kategori  (Kategori produk)
-- ---------------------------------------------------------------------
CREATE TABLE kategori (
    id_kategori   INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(50)   NOT NULL UNIQUE,
    deskripsi     VARCHAR(255)  DEFAULT NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 3. TABEL produk  (Produk yang dijual penjual)
-- ---------------------------------------------------------------------
CREATE TABLE produk (
    id_produk     INT AUTO_INCREMENT PRIMARY KEY,
    id_kategori   INT            NOT NULL,
    id_user       INT            NOT NULL,
    nama_produk   VARCHAR(150)   NOT NULL,
    deskripsi     TEXT           DEFAULT NULL,
    harga         DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
    stok          INT            NOT NULL DEFAULT 0,
    gambar        VARCHAR(255)   DEFAULT NULL,
    status        ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    created_at    TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_produk_kategori FOREIGN KEY (id_kategori)
        REFERENCES kategori(id_kategori) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_produk_user FOREIGN KEY (id_user)
        REFERENCES users(id_user) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 4. TABEL keranjang  (Item keranjang belanja pembeli)
-- ---------------------------------------------------------------------
CREATE TABLE keranjang (
    id_keranjang  INT AUTO_INCREMENT PRIMARY KEY,
    id_user       INT NOT NULL,
    id_produk     INT NOT NULL,
    jumlah        INT NOT NULL DEFAULT 1,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_keranjang_user FOREIGN KEY (id_user)
        REFERENCES users(id_user) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_keranjang_produk FOREIGN KEY (id_produk)
        REFERENCES produk(id_produk) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT uq_keranjang UNIQUE (id_user, id_produk)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 5. TABEL pesanan  (Header transaksi pemesanan)
-- ---------------------------------------------------------------------
CREATE TABLE pesanan (
    id_pesanan        INT AUTO_INCREMENT PRIMARY KEY,
    kode_pesanan      VARCHAR(20)   NOT NULL UNIQUE,
    id_user           INT           NOT NULL,
    nama_penerima     VARCHAR(100)  NOT NULL,
    no_hp             VARCHAR(20)   NOT NULL,
    alamat_kirim      TEXT          NOT NULL,
    catatan           VARCHAR(255)  DEFAULT NULL,
    metode_pembayaran ENUM('transfer_bank','cod','e_wallet') NOT NULL DEFAULT 'transfer_bank',
    total_bayar       DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    status            ENUM('menunggu','diproses','dikirim','selesai','dibatalkan')
                      NOT NULL DEFAULT 'menunggu',
    created_at        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pesanan_user FOREIGN KEY (id_user)
        REFERENCES users(id_user) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 6. TABEL detail_pesanan  (Rincian item pada setiap pesanan)
-- ---------------------------------------------------------------------
CREATE TABLE detail_pesanan (
    id_detail     INT AUTO_INCREMENT PRIMARY KEY,
    id_pesanan    INT           NOT NULL,
    id_produk     INT           NOT NULL,
    nama_produk   VARCHAR(150)  NOT NULL,
    harga_satuan  DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    jumlah        INT           NOT NULL DEFAULT 1,
    subtotal      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    CONSTRAINT fk_detail_pesanan FOREIGN KEY (id_pesanan)
        REFERENCES pesanan(id_pesanan) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_detail_produk FOREIGN KEY (id_produk)
        REFERENCES produk(id_produk) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =====================================================================
-- DATA AWAL (SEED)
-- Password: admin123 / pembeli123  (hash bcrypt)
-- =====================================================================
INSERT INTO users (nama, email, password, role, no_hp, alamat) VALUES
('Admin Mon Chéri', 'admin@marketplace.test',
 '$2y$10$QTVIkCAL8pfSjMhGxmvy9OUX0PvmeZbmWybGbFyjx.t2x4OSnpTry', 'admin', '081234567890',
 'Boutique MON CHÉRI, Jl. Merdeka No. 1, Jakarta'),
('Budi Santoso', 'pembeli@marketplace.test',
 '$2y$10$c1fC/JXHQn.8J0.fAANC5.gdJ1XtH.7lFUJZN4b9A0Pvqs4/3XCs2', 'pembeli', '089876543210',
 'Jl. Sudirman No. 45, Bandung');

-- 5 jenis kategori heels
INSERT INTO kategori (nama_kategori, deskripsi) VALUES
('Stiletto Heels',  'Hak stiletto runcing yang tinggi dan ramping, siluet paling elegan dan seksi'),
('Block Heels',     'Hak balok kokoh dan stabil, nyaman dipakai untuk aktivitas seharian'),
('Platform Heels',  'Hak platform dengan sol tebal di bagian depan, tampilan berani dan modern'),
('Kitten Heels',    'Hak pendek ramping yang nyaman namun tetap anggun dan feminin'),
('Slingback Heels', 'Heels dengan tali pengunci di belakang, tampilan klasik nan chic');

-- Contoh produk per kategori (rumah mode luxury)
INSERT INTO produk (id_kategori, id_user, nama_produk, deskripsi, harga, stok, status) VALUES
-- Stiletto Heels
(1, 1, 'YSL Opyum Stiletto 110mm',
 'Stiletto hak 110 mm dengan ornamen logo YSL, upper kulit paten premium, sol dalam kulit.',
 18500000.00, 6, 'aktif'),
(1, 1, 'Christian Louboutin So Kate 120mm',
 'Stiletto pointed-toe hak 120 mm dengan signature sol merah khas Louboutin, kulit nappa Italia.',
 12900000.00, 8, 'aktif'),
(1, 1, 'Jimmy Choo Romy 100mm',
 'Stiletto pointed-toe hak 100 mm berdetail glitter, sol dalam kulit lembut, siluet ramping.',
 9750000.00, 10, 'aktif'),
-- Block Heels
(2, 1, 'Gucci Horsebit Block Heel 75mm',
 'Block heel hak 75 mm dengan detail Horsebit logam, kulit GG Supreme, nyaman untuk harian.',
 14200000.00, 7, 'aktif'),
(2, 1, 'Prada Monolith Block Heel',
 'Block heel hak 75 mm bergaya Monolith, kulit Saffiano khas Prada, logo segitiga enamel.',
 16800000.00, 5, 'aktif'),
(2, 1, 'Saint Laurent Opyum Block Heel',
 'Block heel hak 85 mm dengan ornamen YSL, kulit paten hitam mengilap, tampilan tegas.',
 13500000.00, 6, 'aktif'),
-- Platform Heels
(3, 1, 'Saint Laurent Opyum Platform 110mm',
 'Platform hak 110 mm dengan sol tebal dan ornamen YSL, kulit paten premium.',
 17900000.00, 4, 'aktif'),
(3, 1, 'Versace Medusa Aevitas Platform',
 'Platform hak 155 mm dengan sol bergerigi, detail Medusa dan ikat kristal, statement piece.',
 21500000.00, 5, 'aktif'),
(3, 1, 'Prada Monolith Platform Boots',
 'Boots platform dengan sol karet tebal dan logo Monolith, kulit halus, gaya edgy.',
 19200000.00, 6, 'aktif'),
-- Kitten Heels
(4, 1, 'Chanel Slingback Kitten Heels',
 'Kitten heel dua warna khas Chanel dengan ujung lancip dan tali slingback elastis.',
 12400000.00, 9, 'aktif'),
(4, 1, 'Dior J''Adore Kitten Heel',
 'Kitten heel hak 40 mm dengan detail pita dan logo CD, kulit lambskin lembut.',
 11800000.00, 8, 'aktif'),
-- Slingback Heels
(5, 1, 'Christian Louboutin Slingback 100mm',
 'Slingback pointed-toe hak 100 mm dengan signature sol merah Louboutin, tali belakang elastis.',
 13700000.00, 7, 'aktif'),
(5, 1, 'Valentino Rockstud Slingback',
 'Slingback hak 65 mm dengan stud Rockstud berlapis platinum, kulit Italia premium.',
 15900000.00, 6, 'aktif'),
(5, 1, 'Manolo Blahnik Slingback',
 'Slingback klasik dengan hak 90 mm dan buckle berlapis kristal, satin Italia mewah.',
 14600000.00, 5, 'aktif');

-- Catatan: tabel pesanan & detail_pesanan sengaja dibiarkan kosong
-- (belum ada transaksi) agar "Pesanan Masuk" pada panel admin bersih.
