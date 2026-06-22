-- =========================================
-- Database: comuter_link_nusantara
-- =========================================
DROP DATABASE IF EXISTS `comuter_link_nusantara`;
CREATE DATABASE IF NOT EXISTS `comuter_link_nusantara`;
USE `comuter_link_nusantara`;

-- =========================================
-- Tabel: petugas
-- =========================================
CREATE TABLE `petugas` (
  `id_petugas` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_petugas` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `nomor_telepon` VARCHAR(20) NOT NULL,
  `image` TEXT,
  PRIMARY KEY (`id_petugas`),
  UNIQUE KEY `email_petugas` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- Tabel: pelapor
-- =========================================
CREATE TABLE `pelapor` (
  `id_pelapor` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_pelapor` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `nomor_telepon` VARCHAR(20) NOT NULL,
  `image` TEXT,
  PRIMARY KEY (`id_pelapor`),
  UNIQUE KEY `email_pelapor` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- Tabel: barang_temuan
-- =========================================
CREATE TABLE `barang_temuan` (
  `id_barang_temuan` INT(11) NOT NULL AUTO_INCREMENT,
  `id_petugas` INT(11) NOT NULL,
  `nama_barang` VARCHAR(100) NOT NULL,
  `kategori` VARCHAR(50) NOT NULL,
  `deskripsi` TEXT NOT NULL,
  `tanggal_ditemukan` DATE NOT NULL,
  `waktu_ditemukan` TIME NOT NULL,
  `lokasi_ditemukan` VARCHAR(100) NOT NULL,
  `image` TEXT NOT NULL,
  `status` ENUM('Tersedia','Sedang Diklaim','Sudah Dikembalikan') NOT NULL DEFAULT 'Tersedia',
  PRIMARY KEY (`id_barang_temuan`),
  KEY `id_petugas` (`id_petugas`),
  CONSTRAINT `barang_temuan_ibfk_1` 
    FOREIGN KEY (`id_petugas`) REFERENCES `petugas` (`id_petugas`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- Tabel: laporan_kehilangan
-- =========================================
CREATE TABLE `laporan_kehilangan` (
  `id_laporan` INT(11) NOT NULL AUTO_INCREMENT,
  `id_pelapor` INT(11) NULL,    
  `id_petugas` INT(11) NULL,    
  `nama_barang` VARCHAR(100) NOT NULL,
  `kategori` VARCHAR(50) NOT NULL,
  `deskripsi_ciri_ciri` TEXT NOT NULL,
  `tanggal_hilang` DATE NOT NULL,
  `waktu_hilang` TIME NOT NULL,
  `lokasi_terakhir` VARCHAR(100) NOT NULL,
  `foto_referensi` TEXT,
  `status` ENUM('Mencari','Ditemukan','Selesai') NOT NULL DEFAULT 'Mencari',
  PRIMARY KEY (`id_laporan`),
  KEY `id_pelapor` (`id_pelapor`),
  KEY `id_petugas` (`id_petugas`), -- Index untuk id_petugas
  CONSTRAINT `laporan_kehilangan_ibfk_1` 
    FOREIGN KEY (`id_pelapor`) REFERENCES `pelapor` (`id_pelapor`) ON DELETE SET NULL, 
  CONSTRAINT `laporan_kehilangan_ibfk_2` 
    FOREIGN KEY (`id_petugas`) REFERENCES `petugas` (`id_petugas`) ON DELETE SET NULL  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- Tabel: klaim_penyerahan
-- =========================================
CREATE TABLE `klaim_penyerahan` (
  `id_klaim` INT(11) NOT NULL AUTO_INCREMENT,
  `id_barang_temuan` INT(11) NOT NULL,
  `id_laporan` INT(11) NOT NULL,
  `id_petugas` INT(11) NOT NULL,
  `tanggal_klaim` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `alasan_klaim` TEXT NOT NULL,
  `foto_bukti` TEXT NULL,
  `tanggal_penyerahan` DATETIME DEFAULT NULL,
  `status_klaim` ENUM('Menunggu Verifikasi','Disetujui & Diserahkan','Ditolak') NOT NULL DEFAULT 'Menunggu Verifikasi',
  `bukti_penyerahan` TEXT,
  PRIMARY KEY (`id_klaim`),
  KEY `id_barang_temuan` (`id_barang_temuan`),
  KEY `id_laporan` (`id_laporan`),
  KEY `id_petugas` (`id_petugas`),
  CONSTRAINT `klaim_penyerahan_ibfk_1` 
    FOREIGN KEY (`id_barang_temuan`) REFERENCES `barang_temuan` (`id_barang_temuan`) ON DELETE CASCADE,
  CONSTRAINT `klaim_penyerahan_ibfk_2` 
    FOREIGN KEY (`id_laporan`) REFERENCES `laporan_kehilangan` (`id_laporan`) ON DELETE CASCADE,
  CONSTRAINT `klaim_penyerahan_ibfk_3` 
    FOREIGN KEY (`id_petugas`) REFERENCES `petugas` (`id_petugas`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- Data: petugas
-- =========================================
INSERT INTO `petugas` (`nama_petugas`, `email`, `password`, `nomor_telepon`, `image`) VALUES
('Rendra Kusuma',  'rendra@krl.com', MD5('petugas123'), '081298765401', 'rendra.jpg'),
('Melati Putri',   'melati@krl.com', MD5('petugas123'), '081298765402', 'melati.jpg'),
('Dodik Setiawan', 'dodik@krl.com',  MD5('petugas123'), '081298765403', 'dodik.jpg');

-- =========================================
-- Data: pelapor
-- =========================================
INSERT INTO `pelapor` (`nama_pelapor`, `email`, `password`, `nomor_telepon`, `image`) VALUES
('Anisa Fitriani',   'anisa@gmail.com', MD5('pelapor123'), '085712340001', 'anisa.jpg'),
('Bagas Nugroho',    'bagas@gmail.com', MD5('pelapor123'), '085712340002', 'bagas.jpg'),
('Citra Lestari',    'citra@gmail.com', MD5('pelapor123'), '085712340003', 'citra.jpg'),
('Dimas Ardiansyah', 'dimas@gmail.com', MD5('pelapor123'), '085712340004', 'dimas.jpg'),
('Eka Wahyuni',      'eka@gmail.com',   MD5('pelapor123'), '085712340005', 'eka.jpg');

-- =========================================
-- Data: barang_temuan
-- =========================================
INSERT INTO `barang_temuan`
(`id_petugas`, `nama_barang`, `kategori`, `deskripsi`, `tanggal_ditemukan`, `waktu_ditemukan`, `lokasi_ditemukan`, `image`, `status`)
VALUES
(1, 'Dompet Hitam', 'Aksesoris', 'Dompet kulit hitam berisi KTP dan ATM', '2026-03-20', '08:15:00', 'Stasiun Bogor', 'dompet.jpg', 'Tersedia'),
(2, 'Handphone Samsung', 'Elektronik', 'HP Samsung warna biru, layar retak sedikit', '2026-03-20', '09:30:00', 'Stasiun Depok', 'hp.jpg', 'Tersedia'),
(3, 'Tas Ransel', 'Tas', 'Tas ransel abu-abu berisi buku dan charger', '2026-03-21', '07:45:00', 'Stasiun Manggarai', 'tas.jpg', 'Tersedia'),
(1, 'Kartu Identitas', 'Dokumen', 'KTP atas nama Budi Santoso', '2026-03-21', '10:10:00', 'Stasiun Tebet', 'ktp.jpg', 'Tersedia'),
(3, 'Jam Tangan', 'Aksesoris', 'Jam tangan digital warna hitam', '2026-03-22', '12:00:00', 'Stasiun Sudirman', 'jam.jpg', 'Tersedia'),
(2, 'Power Bank', 'Elektronik', 'Power bank 10000mAh warna putih', '2026-03-22', '13:20:00', 'Stasiun Tanah Abang', 'powerbank.jpg', 'Tersedia'),
(1, 'Kacamata', 'Aksesoris', 'Kacamata minus dengan frame hitam', '2026-03-23', '08:50:00', 'Stasiun Bekasi', 'kacamata.jpg', 'Tersedia'),
(3, 'Sepatu Olahraga', 'Pakaian', 'Sepatu olahraga warna putih ukuran 42', '2026-03-23', '14:10:00', 'Stasiun Cikarang', 'sepatu.jpg', 'Tersedia'),
(2, 'Botol Minum', 'Lainnya', 'Botol minum stainless warna silver', '2026-03-24', '16:25:00', 'Stasiun Jatinegara', 'botol.jpg', 'Tersedia'),
(1, 'Laptop Asus', 'Elektronik', 'Laptop Asus warna hitam dengan stiker', '2026-03-24', '18:40:00', 'Stasiun Jakarta Kota', 'laptop.jpg', 'Tersedia');

-- =========================================
-- Data: laporan_kehilangan
-- =========================================
INSERT INTO `laporan_kehilangan`
(`id_pelapor`, `id_petugas`, `nama_barang`, `kategori`, `deskripsi_ciri_ciri`,
 `tanggal_hilang`, `waktu_hilang`, `lokasi_terakhir`, `foto_referensi`, `status`)
VALUES
(1, NULL, 'Dompet Hitam', 'Aksesoris',
 'Dompet kulit warna hitam berisi KTP dan kartu ATM BCA',
 '2026-03-20', '07:50:00', 'Stasiun Bogor', 'dompet.jpg', 'Ditemukan'),

(2, NULL, 'Handphone Samsung', 'Elektronik',
 'HP Samsung warna biru, ada retak kecil di layar bagian kanan',
 '2026-03-20', '09:00:00', 'Stasiun Depok', 'hp.jpg', 'Ditemukan'),

(3, NULL, 'Tas Ransel', 'Tas',
 'Tas ransel abu-abu berisi buku kuliah dan charger laptop',
 '2026-03-21', '07:20:00', 'Stasiun Manggarai', 'tas.jpg', 'Ditemukan'),

(4, NULL, 'Kartu Identitas', 'Dokumen',
 'KTP atas nama Dimas Ardiansyah',
 '2026-03-21', '09:40:00', 'Stasiun Tebet', 'ktp.jpg', 'Ditemukan'),

(5, NULL, 'Jam Tangan', 'Aksesoris',
 'Jam tangan digital hitam merek Casio',
 '2026-03-22', '11:30:00', 'Stasiun Sudirman', 'jam.jpg', 'Ditemukan'),

(2, NULL, 'Earphone Bluetooth', 'Elektronik',
 'Earphone wireless warna putih dalam case transparan',
 '2026-03-25', '17:15:00', 'Stasiun Pasar Senen', 'earphone.jpg', 'Mencari');

