-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 23, 2025 at 04:18 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `user_creds`
--

-- --------------------------------------------------------

--
-- Table structure for table `user_creds`
--

CREATE TABLE `user_creds` (
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_creds`
--

INSERT INTO `user_creds` (`username`, `password`, `status`) VALUES
('badgur', '$2y$10$SPR4.a5nyRUs3MUbxxLJD.P9siHoi/4gwdMLbDsotTLRvgiMpnuK.', 'user'),
('eg6395', '$2y$10$YVOdS2q8dUeCRUhsF/7/8upGKcFqtLdb/bFb75Sd3FvL72.Z1FfsG', 'user'),
('gb', '$2y$10$h3m5PXiH9hT/OdjmD8jKY.t72lHoSNVaGjdjpRXg4nZ1pcA2RsJ5i', 'user'),
('smitty', '$2y$10$EqnqAFB7sIsf5UaCD/9aIuSToei43WRRm48jqk3HYLWlp/ItNw8Ju', 'viewer'),
('usa', '$2y$10$nvhANCdTvCkMAZWHaq26Eu091OaZloGRH8WYA6acxmkeew0a.RyWu', 'admin'),
('xam', '$2y$10$8hB27zEdDBtaZweq77Wql.ykhSt81yKqTERQYKN23kqj4bL7BD6Qe', 'viewer');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `user_creds`
--
ALTER TABLE `user_creds`
  ADD PRIMARY KEY (`username`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
