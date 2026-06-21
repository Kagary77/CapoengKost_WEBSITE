-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 21, 2026 at 11:34 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_capoeng`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_booking`
--

CREATE TABLE `tb_booking` (
  `id` int NOT NULL,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_hp` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kamar_nama` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kamar_harga` int NOT NULL,
  `periode` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `metode` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bukti` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('menunggu','disetujui','ditolak') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'menunggu',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_kamar`
--

CREATE TABLE `tb_kamar` (
  `id` int NOT NULL,
  `nama` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipe` enum('Standard','Premium') COLLATE utf8mb4_unicode_ci NOT NULL,
  `harga` int NOT NULL,
  `status` enum('tersedia','terisi') COLLATE utf8mb4_unicode_ci DEFAULT 'tersedia',
  `fasilitas` text COLLATE utf8mb4_unicode_ci,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tb_kamar`
--

INSERT INTO `tb_kamar` (`id`, `nama`, `tipe`, `harga`, `status`, `fasilitas`, `foto`) VALUES
(11, 'A1', 'Standard', 450000, 'terisi', 'Kamar mandi dalam, Meja kecil, Lemari kecil, Kasur', 'kamar/1781989777_WhatsApp Image 2026-06-21 at 4.03.21 AM.jpeg'),
(13, 'A2', 'Standard', 450000, 'tersedia', 'Kamar mandi dalam, Meja kecil, Lemari kecil, Kasur', ''),
(14, 'A3', 'Standard', 450000, 'tersedia', 'Kamar mandi dalam, Meja kecil, Lemari kecil, Kasur', ''),
(15, 'A4', 'Standard', 450000, 'tersedia', 'Kamar mandi dalam, Meja kecil, Lemari kecil, Kasur', ''),
(16, 'A5', 'Standard', 450000, 'tersedia', 'Kamar mandi dalam, Meja kecil, Lemari kecil, Kasur', ''),
(17, 'B1', 'Premium', 850000, 'tersedia', 'AC, Kamar mandi dalam, Set-meja belajar, Lemari besar, Kasur besar', 'kamar/1781989858_WhatsApp Image 2026-06-21 at 4.05.43 AM.jpeg'),
(18, 'B2', 'Premium', 850000, 'tersedia', 'AC, Kamar mandi dalam, Set-meja belajar, Lemari besar, Kasur besar', ''),
(19, 'B3', 'Premium', 850000, 'tersedia', 'AC, Kamar mandi dalam, Set-meja belajar, Lemari besar, Kasur besar', ''),
(20, 'B4', 'Premium', 850000, 'tersedia', 'AC, Kamar mandi dalam, Set-meja belajar, Lemari besar, Kasur besar', ''),
(23, 'B5', 'Premium', 850000, 'tersedia', 'AC, Kamar mandi dalam, Set-meja belajar, Lemari besar, Kasur besar', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tb_komplain`
--

CREATE TABLE `tb_komplain` (
  `id` int NOT NULL,
  `no_tiket` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_kamar` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kategori` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `status` enum('baru','diproses','selesai') COLLATE utf8mb4_unicode_ci DEFAULT 'baru',
  `created_at` date DEFAULT (curdate()),
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_pesan`
--

CREATE TABLE `tb_pesan` (
  `id` int NOT NULL,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_hp` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `topik` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pesan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('baru','dibaca','dibalas') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'baru',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_transaksi`
--

CREATE TABLE `tb_transaksi` (
  `id` int NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_kamar` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bulan` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jumlah` int DEFAULT NULL,
  `metode` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '-',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT 'belum_bayar',
  `tanggal` date DEFAULT NULL,
  `no_hp` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bukti` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catatan` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_users`
--

CREATE TABLE `tb_users` (
  `id` int NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','penghuni') COLLATE utf8mb4_unicode_ci DEFAULT 'penghuni',
  `no_kamar` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat_asal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pekerjaan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` date DEFAULT (curdate()),
  `foto_ktp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tb_users`
--

INSERT INTO `tb_users` (`id`, `nama`, `email`, `password`, `role`, `no_kamar`, `no_hp`, `alamat_asal`, `pekerjaan`, `created_at`, `foto_ktp`) VALUES
(1, 'Admin Kost', 'admin@kost.com', '0192023a7bbd73250516f069df18b500', 'admin', '-', '08111111111', 'Banyuwangi', 'Lainnya', '2026-06-17', NULL),
(5, 'Asuma Toki', 'Asuma@gmail.com', '46b1619ace0c8daa0637c305bb2447bc', 'penghuni', 'A1', '081539823168', 'Palembang', 'Mahasiswa', '2026-06-17', 'ktp/1781981263_16.LEONARD_IMG_5063.JPG');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_booking`
--
ALTER TABLE `tb_booking`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_kamar`
--
ALTER TABLE `tb_kamar`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_komplain`
--
ALTER TABLE `tb_komplain`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_tiket` (`no_tiket`);

--
-- Indexes for table `tb_pesan`
--
ALTER TABLE `tb_pesan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_transaksi`
--
ALTER TABLE `tb_transaksi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tb_users`
--
ALTER TABLE `tb_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_booking`
--
ALTER TABLE `tb_booking`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tb_kamar`
--
ALTER TABLE `tb_kamar`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `tb_komplain`
--
ALTER TABLE `tb_komplain`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tb_pesan`
--
ALTER TABLE `tb_pesan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tb_transaksi`
--
ALTER TABLE `tb_transaksi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tb_users`
--
ALTER TABLE `tb_users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
