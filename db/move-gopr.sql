-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 24, 2026 at 07:49 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `move-gopr`
--

-- --------------------------------------------------------

--
-- Table structure for table `municipios`
--

CREATE TABLE `municipios` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `municipality` varchar(80) NOT NULL,
  `region` enum('Norte','Sur','Este','Oeste','Centro','Metro') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `municipios`
--

INSERT INTO `municipios` (`id`, `municipality`, `region`) VALUES
(10, 'Adjuntas', 'Centro'),
(11, 'Aguada', 'Oeste'),
(12, 'Aguadilla', 'Oeste'),
(13, 'Aguas Buenas', 'Centro'),
(14, 'Aibonito', 'Centro'),
(15, 'Añasco', 'Oeste'),
(16, 'Arecibo', 'Norte'),
(17, 'Arroyo', 'Sur'),
(18, 'Barceloneta', 'Norte'),
(19, 'Barranquitas', 'Centro'),
(20, 'Bayamón', 'Metro'),
(21, 'Cabo Rojo', 'Oeste'),
(22, 'Caguas', 'Centro'),
(23, 'Camuy', 'Norte'),
(24, 'Canóvanas', 'Este'),
(25, 'Carolina', 'Metro'),
(26, 'Cataño', 'Metro'),
(27, 'Cayey', 'Centro'),
(28, 'Ceiba', 'Este'),
(29, 'Ciales', 'Norte'),
(30, 'Cidra', 'Centro'),
(31, 'Coamo', 'Sur'),
(32, 'Comerío', 'Centro'),
(33, 'Corozal', 'Centro'),
(34, 'Culebra', 'Este'),
(35, 'Dorado', 'Norte'),
(36, 'Fajardo', 'Este'),
(37, 'Florida', 'Norte'),
(38, 'Guánica', 'Sur'),
(39, 'Guayama', 'Sur'),
(40, 'Guayanilla', 'Sur'),
(41, 'Guaynabo', 'Metro'),
(42, 'Gurabo', 'Centro'),
(43, 'Hatillo', 'Norte'),
(44, 'Hormigueros', 'Oeste'),
(45, 'Humacao', 'Este'),
(46, 'Isabela', 'Norte'),
(47, 'Jayuya', 'Centro'),
(48, 'Juana Díaz', 'Sur'),
(49, 'Juncos', 'Este'),
(50, 'Lajas', 'Oeste'),
(51, 'Lares', 'Oeste'),
(52, 'Las Marías', 'Oeste'),
(53, 'Las Piedras', 'Este'),
(54, 'Loíza', 'Este'),
(55, 'Luquillo', 'Este'),
(56, 'Manatí', 'Norte'),
(57, 'Maricao', 'Oeste'),
(58, 'Maunabo', 'Sur'),
(59, 'Mayagüez', 'Oeste'),
(60, 'Moca', 'Oeste'),
(61, 'Morovis', 'Norte'),
(62, 'Naguabo', 'Este'),
(63, 'Naranjito', 'Centro'),
(64, 'Orocovis', 'Centro'),
(65, 'Patillas', 'Sur'),
(66, 'Peñuelas', 'Sur'),
(67, 'Ponce', 'Sur'),
(68, 'Quebradillas', 'Norte'),
(69, 'Rincón', 'Oeste'),
(70, 'Río Grande', 'Este'),
(71, 'Sabana Grande', 'Sur'),
(72, 'Salinas', 'Sur'),
(73, 'San Germán', 'Oeste'),
(74, 'San Juan', 'Metro'),
(75, 'San Lorenzo', 'Centro'),
(76, 'San Sebastián', 'Oeste'),
(77, 'Santa Isabel', 'Sur'),
(78, 'Toa Alta', 'Metro'),
(79, 'Toa Baja', 'Metro'),
(80, 'Trujillo Alto', 'Metro'),
(81, 'Utuado', 'Centro'),
(82, 'Vega Alta', 'Norte'),
(83, 'Vega Baja', 'Norte'),
(84, 'Vieques', 'Este'),
(85, 'Villalba', 'Sur'),
(86, 'Yabucoa', 'Este'),
(87, 'Yauco', 'Sur');

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(150) NOT NULL,
  `property_type` enum('house','apartment','condo','land','commercial') NOT NULL,
  `listing_type` enum('sale','rent') NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `address` varchar(255) NOT NULL,
  `municipio_id` smallint(5) UNSIGNED NOT NULL,
  `sqft` int(10) UNSIGNED DEFAULT NULL,
  `beds` tinyint(3) UNSIGNED DEFAULT NULL,
  `baths` tinyint(3) UNSIGNED DEFAULT NULL,
  `parking` tinyint(3) UNSIGNED DEFAULT NULL,
  `furnished` tinyint(1) NOT NULL DEFAULT 0,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('available','sold','rented','inactive') NOT NULL DEFAULT 'available',
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `title`, `property_type`, `listing_type`, `price`, `address`, `municipio_id`, `sqft`, `beds`, `baths`, `parking`, `furnished`, `featured`, `status`, `description`, `image`, `lat`, `lng`, `created_at`) VALUES
(3, 'la casa de tus sueños', 'house', 'sale', 1448880.01, '1 dorado beach', 35, 845303, 8, 12, 16, 1, 1, 'available', 'mira que cosa mas hermosa', 'uploads/properties/prop_69cb8ada7b9df.jpg', NULL, NULL, '2026-03-31 08:20:09'),
(4, 'casa 3 cuartos', 'house', 'rent', 750.00, '2323 calle el rio', 10, NULL, NULL, NULL, NULL, 1, 0, 'available', 'incluye internet', 'uploads/properties/prop_69d06d9bddc0d.webp', 18.2028780, -66.7481480, '2026-03-31 08:29:28');

-- --------------------------------------------------------

--
-- Table structure for table `quote_requests`
--

CREATE TABLE `quote_requests` (
  `id` int(11) NOT NULL,
  `tipo_servicio` varchar(20) NOT NULL DEFAULT '',
  `nombre` varchar(100) NOT NULL DEFAULT '',
  `telefono` varchar(20) NOT NULL DEFAULT '',
  `email` varchar(100) NOT NULL DEFAULT '',
  `servicio` varchar(100) NOT NULL DEFAULT '',
  `ubicacion` varchar(200) NOT NULL DEFAULT '',
  `mensaje` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quote_requests`
--

INSERT INTO `quote_requests` (`id`, `tipo_servicio`, `nombre`, `telefono`, `email`, `servicio`, `ubicacion`, `mensaje`, `status`, `created_at`) VALUES
(1, 'mejoras', 'PRUEBA', '7873-313-20222', 'pruebasolicitud@gmail.com', 'Propiedades', 'Maya', 'Mudarme!', 'new', '2026-04-24 04:17:22'),
(2, 'mejoras', 'PRUEBA', '7873-313-20222', 'pruebasolicitud@gmail.com', 'Propiedades', 'Maya', 'Mudarme!', 'closed', '2026-04-24 04:17:24'),
(3, 'mudanza', 'cliente favorito', '787-123-4567', 'test@email.com', 'Transporte de Muebles', 'san juan a caguas', 'necesito mover 2 muebles que usamos para entrevistas', 'contacted', '2026-04-24 04:35:51'),
(4, 'mudanza', 'Juan del Pueblo', '787-332-9940', 'test@mail.com', 'Carga y Descarga', 'Colombia a Puerto Rico', 'No preguntes mucho.', 'contacted', '2026-04-24 05:20:18');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(254) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('customer','admin') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `created_at`) VALUES
(1, 'Jaime Muñoz', 'jaime@movegopr.com', '$2y$10$fZBsbnKmb4xawR9sWP6Cmu7QAwckIi7sotblbfJd8or9oKutzx0bG', 'admin', '2026-02-28 08:05:44'),
(2, 'testuser', 'test@movegopr.com', '$2y$10$4G9SWbzcmEDdd2ZUAIdHT.9YCN.jf2Sry1eRU/Sn0cA46kGGHWD9G', 'admin', '2026-04-04 23:44:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `municipios`
--
ALTER TABLE `municipios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_locations_municipality` (`municipality`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_properties_status` (`status`),
  ADD KEY `idx_properties_listing` (`listing_type`),
  ADD KEY `fk_properties_municipio` (`municipio_id`);

--
-- Indexes for table `quote_requests`
--
ALTER TABLE `quote_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `municipios`
--
ALTER TABLE `municipios`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `quote_requests`
--
ALTER TABLE `quote_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `properties`
--
ALTER TABLE `properties`
  ADD CONSTRAINT `fk_properties_municipio` FOREIGN KEY (`municipio_id`) REFERENCES `municipios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
