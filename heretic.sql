-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for rental_heretic
DROP DATABASE IF EXISTS `rental_heretic`;
CREATE DATABASE IF NOT EXISTS `rental_heretic` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `rental_heretic`;

-- Dumping structure for table rental_heretic.alat
DROP TABLE IF EXISTS `alat`;
CREATE TABLE IF NOT EXISTS `alat` (
  `id_alat` int NOT NULL AUTO_INCREMENT,
  `nama_alat` varchar(100) NOT NULL,
  `id_kategori` int NOT NULL,
  `harga_perhari` int NOT NULL,
  `stok` int NOT NULL DEFAULT '0',
  `foto` varchar(255) DEFAULT 'default.jpg',
  `gambar` varchar(255) DEFAULT NULL,
  `deskripsi` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_alat`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table rental_heretic.alat: ~10 rows (approximately)
REPLACE INTO `alat` (`id_alat`, `nama_alat`, `id_kategori`, `harga_perhari`, `stok`, `foto`, `gambar`, `deskripsi`, `created_at`) VALUES
	(1, 'Sony Alpha A6400 Mirrorless', 1, 150000, 20, 'camera_sony.jpg', 'camera_sony.jpg', 'Kamera handal untuk video sinematik dan foto outdoor.', '2026-06-08 18:12:02'),
	(2, 'Tenda Camping Dome Eiger 4P', 2, 45000, 19, 'tenda.jpg', 'tenda.jpg', 'Tenda double layer anti badai kapasitas 4 orang.', '2026-06-08 18:12:02'),
	(3, 'Tas Carrier Deuter Future Pro 40L', 1, 35000, 20, 'tas40.jpg', 'tas40.jpg', 'Tas gunung dengan sirkulasi udara terbaik, sangat nyaman.', '2026-06-08 18:12:02'),
	(4, 'Tas Carrier Heretic 666', 1, 85000, 20, 'heretic666.jpg', 'heretic666.jpg', 'Tas gunung premium edisi khusus dengan material ultra-durable, kapasitas 40L+, nyaman untuk hiking berat dan didesain dengan warna stealth black.', '2026-06-09 16:36:16'),
	(5, 'Sepatu Gunung', 1, 50000, 20, 'sepatu.jpg', 'sepatu.jpg', 'Sepatu gunung kece', '2026-06-10 16:18:27'),
	(6, 'Kompor Camping Portable', 1, 35000, 20, 'kompor.jpg', 'kompor.jpg', 'Kompor gas portable buat masak di alam terbuka', '2026-06-10 16:23:37'),
	(7, 'Matras Camping + Sleeping Bag', 1, 50000, 20, 'Matras Camping_Sleeping Bag.jpg.png', 'Matras Camping_Sleeping Bag.jpg.png', 'Paket tidur outdoor, matras lipat + sleeping bag tebal', '2026-06-10 16:27:12'),
	(8, 'Power Bank Solar 20.000mAh', 1, 32000, 20, 'powerbank.jpg', 'powerbank.jpg', 'Power bank tahan air, bisa charge pake matahari', '2026-06-10 16:29:08'),
	(9, 'Headlamp LED 300 Lumen', 1, 50000, 19, 'Headlamp.jpg', 'Headlamp.jpg', 'Lampu kepala tahan air, cocok buat aktivitas malam', '2026-06-10 16:31:56');

-- Dumping structure for table rental_heretic.alat_rental_backup
DROP TABLE IF EXISTS `alat_rental_backup`;
CREATE TABLE IF NOT EXISTS `alat_rental_backup` (
  `id_alat` int NOT NULL AUTO_INCREMENT,
  `nama_alat` varchar(100) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `harga_per_hari` int NOT NULL,
  `stok` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_alat`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table rental_heretic.alat_rental_backup: ~3 rows (approximately)
REPLACE INTO `alat_rental_backup` (`id_alat`, `nama_alat`, `kategori`, `harga_per_hari`, `stok`) VALUES
	(1, 'Kamera Canon EOS 3000D', 'Kamera', 2500000, 15),
	(2, 'Sony Alpha a6000', 'Kamera', 1500000, 15),
	(3, 'Tripod Takara ECO-196', 'Aksesoris', 250000, 15);

-- Dumping structure for table rental_heretic.audit_log
DROP TABLE IF EXISTS `audit_log`;
CREATE TABLE IF NOT EXISTS `audit_log` (
  `id_log` int NOT NULL AUTO_INCREMENT,
  `aktor` varchar(100) DEFAULT NULL,
  `aktivitas` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table rental_heretic.audit_log: ~9 rows (approximately)
REPLACE INTO `audit_log` (`id_log`, `aktor`, `aktivitas`, `created_at`) VALUES
	(1, 'Super Admin', 'Masuk ke sistem', '2026-06-10 14:40:05'),
	(2, 'Admin', 'Menambah alat baru', '2026-06-10 14:40:05'),
	(3, 'User', 'Melakukan booking', '2026-06-10 14:40:05'),
	(4, 'Super Admin', 'Menambahkan unit alat baru: Tas Geoffmax', '2026-06-11 12:44:02'),
	(5, 'Super Admin', 'Menghapus permanen unit alat ID: 10', '2026-06-11 13:33:50'),
	(6, 'Super Admin', 'Menambahkan unit alat baru: Tas Geoffmax', '2026-06-11 13:34:26'),
	(7, 'Super Admin', 'Menghapus permanen unit alat ID: 14', '2026-06-11 14:11:17'),
	(8, 'Super Admin', 'Menghapus permanen unit alat ID: 15', '2026-06-11 19:39:18'),
	(9, 'Super Admin', 'Menambahkan unit alat baru: Tas Geoffmax', '2026-06-11 19:44:11'),
	(10, 'Super Admin', 'Memperbarui data logistik alat ID: 16 (Tas bloods)', '2026-06-11 20:01:47'),
	(11, 'Super Admin', 'Memperbarui data logistik alat ID: 16 (Tas Geoffmax)', '2026-06-12 07:31:42'),
	(12, 'Super Admin', 'Menghapus permanen unit alat ID: 16', '2026-06-12 07:31:52'),
	(13, 'Super Admin', 'Membuka blokir akun pelanggan ID: 12', '2026-06-12 15:02:21'),
	(14, 'Super Admin', 'Menambahkan unit alat baru: Tas Geoffmax', '2026-06-14 13:33:50'),
	(15, 'Super Admin', 'Memperbarui data logistik alat ID: 3 (Tas Carrier Deuter Future Pro 40L)', '2026-06-19 18:27:14'),
	(16, 'Super Admin', 'Menambahkan staff admin baru: admin', '2026-06-19 18:51:18'),
	(17, 'Super Admin', 'Menonaktifkan akses admin ID: 14', '2026-06-19 18:52:33'),
	(18, 'Super Admin', 'Menghapus permanen admin ID: 14', '2026-06-19 18:52:41'),
	(19, 'Super Admin', 'Menambahkan staff admin baru: rival', '2026-06-19 19:21:13'),
	(20, 'System', 'Pelanggan baru berhasil mendaftar: rival', '2026-06-19 19:41:39'),
	(21, 'Super Admin', 'Menghapus permanen unit alat ID: 21', '2026-06-20 08:32:28');

-- Dumping structure for table rental_heretic.detail_transaksi
DROP TABLE IF EXISTS `detail_transaksi`;
CREATE TABLE IF NOT EXISTS `detail_transaksi` (
  `id_detail` int NOT NULL AUTO_INCREMENT,
  `rental_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price_per_day` int NOT NULL,
  `days` int NOT NULL,
  `subtotal` int NOT NULL,
  `qty` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_detail`),
  KEY `rental_id` (`rental_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE,
  CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `alat` (`id_alat`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table rental_heretic.detail_transaksi: ~2 rows (approximately)
REPLACE INTO `detail_transaksi` (`id_detail`, `rental_id`, `product_id`, `quantity`, `price_per_day`, `days`, `subtotal`, `qty`) VALUES
	(31, 57, 9, 1, 50000, 3, 150000, 1),
	(32, 58, 8, 1, 32000, 1, 32000, 1),
	(33, 59, 2, 2, 45000, 2, 180000, 2),
	(34, 60, 2, 1, 45000, 2, 90000, 1);

-- Dumping structure for table rental_heretic.kategori
DROP TABLE IF EXISTS `kategori`;
CREATE TABLE IF NOT EXISTS `kategori` (
  `id_kategori` int NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(100) NOT NULL,
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table rental_heretic.kategori: ~2 rows (approximately)
REPLACE INTO `kategori` (`id_kategori`, `nama_kategori`) VALUES
	(1, 'outdoor'),
	(2, 'camera');

-- Dumping structure for table rental_heretic.log_stok
DROP TABLE IF EXISTS `log_stok`;
CREATE TABLE IF NOT EXISTS `log_stok` (
  `id_log` int NOT NULL AUTO_INCREMENT,
  `id_alat` int NOT NULL,
  `jumlah` int NOT NULL,
  `jenis` enum('masuk','keluar') NOT NULL,
  `keterangan` text,
  `tgl_log` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log`),
  KEY `id_alat` (`id_alat`),
  CONSTRAINT `log_stok_ibfk_1` FOREIGN KEY (`id_alat`) REFERENCES `alat_rental_backup` (`id_alat`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table rental_heretic.log_stok: ~0 rows (approximately)

-- Dumping structure for table rental_heretic.pengaturan_sistem
DROP TABLE IF EXISTS `pengaturan_sistem`;
CREATE TABLE IF NOT EXISTS `pengaturan_sistem` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_perusahaan` varchar(255) NOT NULL,
  `slogan` varchar(255) DEFAULT NULL,
  `no_hp_admin` varchar(20) DEFAULT NULL,
  `alamat_basecamp` text,
  `syarat_ketentuan` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table rental_heretic.pengaturan_sistem: ~0 rows (approximately)
REPLACE INTO `pengaturan_sistem` (`id`, `nama_perusahaan`, `slogan`, `no_hp_admin`, `alamat_basecamp`, `syarat_ketentuan`) VALUES
	(1, 'Heretic Outdoor & Camera Rental', 'Petualangan dan Momen Terbaikmu Dimulai di Sini!', '083847473124', 'Jl. Leuwigoong No. 6, Kota Garut', 'Wajib meninggalkan kartu identitas (KTP/SIM) asli sebagai jaminan selama masa penyewaan.');

-- Dumping structure for table rental_heretic.transaksi
DROP TABLE IF EXISTS `transaksi`;
CREATE TABLE IF NOT EXISTS `transaksi` (
  `id_transaksi` int NOT NULL AUTO_INCREMENT,
  `invoice_code` varchar(50) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `customer_address` text,
  `emergency_name` varchar(100) DEFAULT NULL,
  `emergency_phone` varchar(20) DEFAULT NULL,
  `jenis_jaminan` varchar(30) DEFAULT NULL,
  `nomor_identitas` varchar(50) DEFAULT NULL,
  `foto_identitas` varchar(255) DEFAULT NULL,
  `foto_selfie` varchar(255) DEFAULT NULL,
  `rent_date` date NOT NULL,
  `return_date` date NOT NULL,
  `total_bayar` int NOT NULL,
  `status` enum('active','completed','pending') NOT NULL DEFAULT 'pending',
  `metode_ambil` varchar(50) DEFAULT NULL,
  `metode_bayar` varchar(50) DEFAULT NULL,
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `total_amount` int NOT NULL DEFAULT '0',
  `catatan_tambahan` text,
  `deposit` int DEFAULT '100000',
  PRIMARY KEY (`id_transaksi`),
  UNIQUE KEY `invoice_code` (`invoice_code`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table rental_heretic.transaksi: ~12 rows (approximately)
REPLACE INTO `transaksi` (`id_transaksi`, `invoice_code`, `customer_name`, `customer_phone`, `customer_email`, `customer_address`, `emergency_name`, `emergency_phone`, `jenis_jaminan`, `nomor_identitas`, `foto_identitas`, `foto_selfie`, `rent_date`, `return_date`, `total_bayar`, `status`, `metode_ambil`, `metode_bayar`, `bukti_pembayaran`, `created_at`, `total_amount`, `catatan_tambahan`, `deposit`) VALUES
	(57, 'HTC-278901', 'helena', '083847473124', 'helena@gmail.com', 'cbn', NULL, NULL, 'KTP Asli', '3205111104050003', NULL, NULL, '2028-01-21', '2028-01-24', 150000, 'completed', 'Ambil di Toko', NULL, NULL, '2026-06-20 07:53:33', 0, NULL, 100000),
	(58, 'HTC-BD7EC2', 'helena', '083847473124', 'helena@gmail.com', 'cbn', NULL, NULL, 'KTP Asli', '3205111104050003', NULL, NULL, '2026-12-20', '2026-12-21', 32000, 'completed', 'Ambil di Toko', NULL, NULL, '2026-06-20 08:05:00', 0, NULL, 100000),
	(59, 'HTC-B431F6', 'ibrahim', '085846650450', NULL, NULL, NULL, NULL, 'KTP', NULL, NULL, NULL, '2026-06-20', '2026-06-22', 180000, 'completed', NULL, NULL, NULL, '2026-06-20 08:19:44', 0, NULL, 100000),
	(60, 'HTC-C63200', 'helena', '085846650450', 'helena@gmail.com', 'cmd', NULL, NULL, 'KTP Asli', '3205111104050003', NULL, NULL, '2026-06-22', '2026-06-24', 90000, 'active', 'Ambil di Toko', NULL, NULL, '2026-06-20 08:22:16', 0, NULL, 100000);

-- Dumping structure for table rental_heretic.user
DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `nama_user` varchar(100) DEFAULT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `role` enum('super_admin','admin','user') NOT NULL DEFAULT 'user',
  `email` varchar(100) DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `status_akun` varchar(20) DEFAULT 'Active',
  `email_token` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table rental_heretic.user: ~4 rows (approximately)
REPLACE INTO `user` (`id_user`, `username`, `password`, `nama`, `nama_user`, `no_telp`, `telepon`, `role`, `email`, `no_hp`, `is_active`, `status_akun`, `email_token`) VALUES
	(1, 'superadmin', 'admin123', 'Super Admin Heretic', 'Super Admin Heretic', '08123', '08123', 'super_admin', NULL, NULL, 1, 'Active', NULL),
	(2, 'adminlapangan', 'admin', 'Admin Lapangan', 'Admin Lapangan', '08123', '08123', 'admin', NULL, NULL, 1, 'Active', NULL),
	(13, 'helena', '123', 'dicki', 'dicki', '-', '-', 'user', 'helena@gmail.com', NULL, 1, 'Active', NULL),
	(16, 'rival', '123456', 'rival jokowi', 'rival aliadi', NULL, NULL, 'admin', 'rival@gmail.com', '08765432123', 1, 'Active', NULL);

-- Dumping structure for table rental_heretic.users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `no_telp` varchar(20) NOT NULL,
  `role` enum('super_admin','admin','user') NOT NULL DEFAULT 'user',
  `email` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table rental_heretic.users: ~3 rows (approximately)
REPLACE INTO `users` (`id_user`, `username`, `password`, `nama`, `no_telp`, `role`, `email`, `is_active`) VALUES
	(1, 'superadmin', '0192023a7bbd73250516f069df18b500', 'Super Admin Heretic', '081234567890', 'super_admin', NULL, 1),
	(2, 'adminlapangan', '123456', 'Admin Lapangan Heretic', '089876543210', 'admin', NULL, 1),
	(3, 'pelanggan', '6ad14ba9986e3615423dfca256d04e3f', 'Ahmad Pelanggan', '085544332212', 'user', NULL, 1);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
