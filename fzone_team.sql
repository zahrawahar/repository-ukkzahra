-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 25, 2026 at 12:00 AM
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
-- Database: `fzone_team`
--

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--

CREATE TABLE `barang` (
  `id_barang` int NOT NULL,
  `nama` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `stok` int DEFAULT NULL,
  `harga` int DEFAULT NULL,
  `stok_baik` int DEFAULT '0',
  `stok_rusak` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`id_barang`, `nama`, `stok`, `harga`, `stok_baik`, `stok_rusak`) VALUES
(22, 'kursi', 12, 20000, 0, 0),
(23, 'laptop', 3, 30000, 0, 0),
(24, 'kursi', 3, 23000, 0, 3),
(25, 'meja', 4, 30000, 2, 2),
(26, 'sepatu', 5, 70000, 5, 0),
(27, 'cermin', 8, 50000, 8, 0),
(28, 'kipas', 7, 65000, 7, 0),
(29, 'tas', 7, 55000, 7, 0),
(30, 'lemari', 8, 150000, 8, 0),
(31, 'laptop', 4, 30000, 4, 0),
(32, 'jendela', 7, 170000, 7, 0),
(33, 'pipa', 11, 50000, 11, 0);

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int NOT NULL,
  `id_user` int NOT NULL,
  `id_barang` int NOT NULL,
  `stok` int NOT NULL,
  `tanggal_transaksi` date NOT NULL,
  `status` enum('keluar','masuk') COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `id_user`, `id_barang`, `stok`, `tanggal_transaksi`, `status`) VALUES
(31, 5, 28, 0, '2026-01-21', 'masuk'),
(32, 5, 29, 0, '2026-01-23', 'masuk'),
(33, 5, 29, 0, '2026-01-23', 'keluar'),
(34, 5, 26, 0, '2026-01-23', 'keluar'),
(35, 5, 28, 0, '2026-01-24', 'masuk'),
(36, 5, 28, 0, '2026-01-24', 'keluar'),
(37, 5, 25, 0, '2026-01-24', 'keluar'),
(38, 5, 22, 5, '2026-01-24', 'keluar'),
(39, 5, 29, 7, '2026-01-24', 'masuk'),
(40, 5, 25, 2, '2026-01-24', 'masuk'),
(41, 5, 30, 8, '2026-01-24', 'masuk'),
(42, 5, 25, 2, '2026-01-24', 'keluar'),
(43, 5, 31, 4, '2026-02-02', 'masuk'),
(44, 5, 22, 5, '2026-02-02', 'keluar'),
(45, 5, 27, 6, '2026-02-04', 'masuk'),
(46, 5, 32, 7, '2026-02-07', 'masuk'),
(47, 5, 33, 11, '2026-02-07', 'masuk');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `username` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gmail` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` enum('admin','petugas') COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `gmail`, `password`, `role`) VALUES
(5, 'zahra', 'latifaazahro@gmail.com', '21232f297a57a5a743894a0e4a801fc3', 'admin'),
(6, 'petugas', 'petugas@gmail.com', '$2y$10$L.RvRFCakMul3P.Z5ju5xOAsOmv3SKIwfOg./4nOSFS4yla/bjJBm', 'petugas'),
(11, 'fadila', NULL, '$2y$10$/arAy2FYJIRwrvuEIHBdgOJPEkdBSEA02H./H4PTXpb9tj1pBJTkq', 'petugas');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id_barang`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_barang` (`id_barang`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `barang`
--
ALTER TABLE `barang`
  MODIFY `id_barang` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `fk_transaksi_barang` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`),
  ADD CONSTRAINT `fk_transaksi_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
