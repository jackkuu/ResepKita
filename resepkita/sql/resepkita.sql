-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 17, 2025 at 10:51 AM
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
-- Database: `resepkita`
--

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE `ingredients` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `calories` decimal(5,1) DEFAULT NULL COMMENT 'Kalori per 100g',
  `sugar_g` decimal(4,1) DEFAULT NULL COMMENT 'Gula per 100g (gram)',
  `fat_g` decimal(4,1) DEFAULT NULL COMMENT 'Lemak per 100g (gram)',
  `protein_g` decimal(4,1) DEFAULT NULL COMMENT 'Protein per 100g (gram)',
  `carbs_g` decimal(4,1) DEFAULT NULL COMMENT 'Karbohidrat per 100g (gram)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`id`, `name`, `calories`, `sugar_g`, `fat_g`, `protein_g`, `carbs_g`) VALUES
(1, 'nasi', 130.0, 0.0, 0.3, 2.7, 28.2),
(2, 'telur', 155.0, 1.1, 11.0, 13.0, 1.1),
(3, 'kecap manis', 60.0, 4.0, 0.3, 0.9, 15.6),
(4, 'bawang putih', 45.0, 0.7, 0.1, 1.7, 10.7),
(5, 'bawang merah', 40.0, 2.0, 0.1, 1.1, 9.0),
(6, 'minyak goreng', 884.0, 0.0, 100.0, 0.0, 0.0),
(7, 'garam', 0.0, 0.0, 0.0, 0.0, 0.0),
(8, 'lada', 251.0, 3.3, 3.3, 10.0, 63.7),
(9, 'daun bawang', 26.0, 1.0, 0.1, 0.9, 5.6),
(10, 'serai', 20.0, 0.5, 0.1, 0.8, 4.7),
(11, 'tomat', 18.0, 2.6, 0.2, 0.9, 3.9),
(12, 'kaldu bubuk', 5.0, 0.0, 0.0, 0.3, 1.0),
(13, 'ayam', 165.0, 1.2, 3.7, 31.0, 0.0),
(14, 'cabe', 31.0, 0.6, 0.1, 1.3, 7.2),
(15, 'jahe', 80.0, 0.0, 7.7, 1.5, 2.7),
(16, 'timun', 31.0, 4.7, 0.2, 0.9, 7.2),
(17, 'kol', 25.0, 0.5, 0.1, 1.5, 5.8),
(18, 'wortel', 50.0, 2.3, 0.3, 1.2, 11.7),
(19, 'ketumbar', 13.0, 0.3, 0.1, 0.6, 2.7),
(20, 'lengkuas', 43.0, 0.5, 0.8, 1.5, 7.3),
(21, 'daun salam', 22.0, 0.3, 0.1, 0.6, 4.8);

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE `recipes` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `ingredients` text NOT NULL,
  `instructions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipes`
--

INSERT INTO `recipes` (`id`, `title`, `ingredients`, `instructions`, `created_at`, `description`, `image_path`) VALUES
(1, 'Nasi Goreng Sederhana', 'bawang merah, bawang putih, daun bawang, garam, kecap manis, lada, minyak goreng, nasi, telur', '1. Panaskan minyak, tumis bawang putih & bawang merah hingga harum.\r\n 2. Masukkan telur, orak-arik.\r\n 3. Tambahkan nasi, kecap manis, garam, dan lada. Aduk rata.\r\n 4. Taburi daun bawang dan sajikan panas.', '2025-11-05 08:19:27', 'Resep nasi goreng sederhana ala rumahan dengan resep yang dapat dimodifikasi menjadi banyak varian', 'uploads/c6767e401c28c503.jpeg'),
(2, 'Rica Rica Ayam', 'ayam, bawang merah, bawang putih, cabe, garam, jahe, kaldu bubuk, minyak goreng, serai, tomat', '1. Siapkan bumbu halus dan haluskan sampai rata.\r\n 2. Panaskan minyak, lalu masukkan bumbu halus dan tumis hingga harum.\r\n 3. Masukkan potongan ayam.\r\n 4. Tambahkan perasan air jahe.\r\n 5. Masukkan air, lalu masak ayam hingga airnya menyusut dan bumbunya meresap dengan baik\r\n 6. Angkat dan sajikan di piring saji', '2025-11-05 08:41:18', 'Ayam rica-rica adalah hidangan khas Indonesia yang terkenal dengan rasa pedas dan gurih yang kuat, berasal dari masakan Manado, Sulawesi Utara', 'uploads/6f5ee41ae608fb15.webp'),
(3, 'Telur Goreng', 'garam, lada, minyak goreng, telur', '1. kocok telur dengan garam dan lada\r\n 2. panaskan minyak\r\n 3. goreng telur hingga matang', '2025-11-05 09:04:39', 'Telur goreng adalah hidangan sederhana dari telur yang dimasak dalam minyak atau mentega panas di wajan', 'uploads/b0d90be6cd37332b.jpeg'),
(4, 'Opor Ayam', 'ayam, bawang merah, bawang putih, cabe, daun salam, jahe, ketumbar, lengkuas, serai', '1. Rebus ayam setengah matang ditambah jahe agar tidak amis\r\n2. Chopper atau blender bumbu, kemudian tumis dengan minyak atau margarin sampai harum, tambah daun salam dan sereh\r\n3. Tambahkan air kedalam bumbu tumis, tunggu sampai mendidih\r\n4. Masukkan tahu, dan ayam yang sudah dimasak setengah matang sebelum nya\r\n5. Masukkan santan, aduk rata agar santan tidak pecah\r\n6. Koreksi rasa, tambahkan garam, lada, totole jika ingin, selesai, selamat menikmati', '2025-11-06 00:08:47', 'Resep opor ayam yang biasanya disajikan pada saat lebaran', 'uploads/7cf4701d959c0ca7.jpeg'),
(5, 'Kol Goreng', 'minyak goreng, kol', '1. Iris kol merata\r\n2. Cuci dan tiriskan\r\n3. Sesuaikan jumlah minyak\r\n4. Goreng dengan api kecil atau sedang', '2025-12-15 10:13:25', 'kol yang di goreng hingga kekuningan yang membuat teksturnya menjadi lebih renyah', 'uploads/b8ed085cedb5752b.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `recipe_ingredients`
--

CREATE TABLE `recipe_ingredients` (
  `recipe_id` int(11) NOT NULL,
  `ingredient_id` int(11) NOT NULL,
  `quantity` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipe_ingredients`
--

INSERT INTO `recipe_ingredients` (`recipe_id`, `ingredient_id`, `quantity`) VALUES
(1, 1, ''),
(1, 2, ''),
(1, 3, ''),
(1, 4, ''),
(1, 5, ''),
(1, 6, ''),
(1, 7, ''),
(1, 8, ''),
(1, 9, ''),
(2, 4, ''),
(2, 5, ''),
(2, 6, ''),
(2, 7, ''),
(2, 10, ''),
(2, 11, ''),
(2, 12, ''),
(2, 13, ''),
(2, 14, ''),
(2, 15, ''),
(3, 2, ''),
(3, 6, ''),
(3, 7, ''),
(3, 8, ''),
(4, 4, ''),
(4, 5, ''),
(4, 10, ''),
(4, 13, ''),
(4, 14, ''),
(4, 15, ''),
(4, 19, ''),
(4, 20, ''),
(4, 21, ''),
(5, 6, ''),
(5, 17, '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `recipe_ingredients`
--
ALTER TABLE `recipe_ingredients`
  ADD PRIMARY KEY (`recipe_id`,`ingredient_id`),
  ADD KEY `ingredient_id` (`ingredient_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `recipes`
--
ALTER TABLE `recipes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `recipe_ingredients`
--
ALTER TABLE `recipe_ingredients`
  ADD CONSTRAINT `recipe_ingredients_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipe_ingredients_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
