-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 06 Nov 2025 pada 01.18
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

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
-- Struktur dari tabel `ingredients`
--

CREATE TABLE `ingredients` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `ingredients`
--

INSERT INTO `ingredients` (`id`, `name`) VALUES
(13, 'ayam'),
(5, 'bawang merah'),
(4, 'bawang putih'),
(14, 'cabe'),
(9, 'daun bawang'),
(21, 'daun salam'),
(7, 'garam'),
(15, 'jahe'),
(12, 'kaldu bubuk'),
(3, 'kecap manis'),
(19, 'ketumbar'),
(17, 'kol'),
(8, 'lada'),
(20, 'lengkuas'),
(6, 'minyak goreng'),
(1, 'nasi'),
(10, 'serai'),
(2, 'telur'),
(16, 'timun'),
(11, 'tomat'),
(18, 'wortel');

-- --------------------------------------------------------

--
-- Struktur dari tabel `recipes`
--

CREATE TABLE `recipes` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `ingredients` text NOT NULL,
  `instructions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `description` text,
  `image_path` varchar(255) DEFAULT NULL 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
--
-- Dumping data untuk tabel `recipes`
--

INSERT INTO `recipes` (`id`, `title`, `ingredients`, `instructions`, `created_at`) VALUES
(1, 'Nasi Goreng Sederhana', 'nasi, telur, kecap manis, bawang putih, bawang merah, minyak goreng, garam, lada, daun bawang', '1. Panaskan minyak, tumis bawang putih & bawang merah hingga harum.\r\n 2. Masukkan telur, orak-arik.\r\n 3. Tambahkan nasi, kecap manis, garam, dan lada. Aduk rata.\r\n 4. Taburi daun bawang dan sajikan panas.', '2025-11-05 08:19:27'),
(2, 'Rica Rica Ayam', 'ayam, bawang merah, bawang putih, cabe, garam, jahe, kaldu bubuk, minyak goreng, serai, tomat', '1. Siapkan bumbu halus dan haluskan sampai rata.\r\n 2. Panaskan minyak, lalu masukkan bumbu halus dan tumis hingga harum.\r\n 3. Masukkan potongan ayam.\r\n 4. Tambahkan perasan air jahe.\r\n 5. Masukkan air, lalu masak ayam hingga airnya menyusut dan bumbunya meresap dengan baik\r\n 6. Angkat dan sajikan di piring saji', '2025-11-05 08:41:18'),
(3, 'Telur Goreng', 'telur, minyak goreng, garam, lada', '1. kocok telur dengan garam dan lada\r\n 2. panaskan minyak\r\n 3. goreng telur hingga matang', '2025-11-05 09:04:39'),
(4, 'Opor Ayam', 'ayam, bawang merah, bawang putih, cabe, daun salam, jahe, ketumbar, lengkuas, serai', '1. Rebus ayam setengah matang ditambah jahe agar tidak amis\r\n2. Chopper atau blender bumbu, kemudian tumis dengan minyak atau margarin sampai harum, tambah daun salam dan sereh\r\n3. Tambahkan air kedalam bumbu tumis, tunggu sampai mendidih\r\n4. Masukkan tahu, dan ayam yang sudah dimasak setengah matang sebelum nya\r\n5. Masukkan santan, aduk rata agar santan tidak pecah\r\n6. Koreksi rasa, tambahkan garam, lada, totole jika ingin, selesai, selamat menikmati', '2025-11-06 00:08:47');

-- --------------------------------------------------------

--
-- Struktur dari tabel `recipe_ingredients`
--

CREATE TABLE `recipe_ingredients` (
  `recipe_id` int(11) NOT NULL,
  `ingredient_id` int(11) NOT NULL,
  `quantity` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `recipe_ingredients`
--

INSERT INTO `recipe_ingredients` (`recipe_id`, `ingredient_id`, `quantity`) VALUES
(1, 1, NULL),
(1, 2, NULL),
(1, 3, NULL),
(1, 4, NULL),
(1, 5, NULL),
(1, 6, NULL),
(1, 7, NULL),
(1, 8, NULL),
(1, 9, NULL),
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
(3, 2, NULL),
(3, 6, NULL),
(3, 7, NULL),
(3, 8, NULL),
(4, 4, ''),
(4, 5, ''),
(4, 10, ''),
(4, 13, ''),
(4, 14, ''),
(4, 15, ''),
(4, 19, ''),
(4, 20, ''),
(4, 21, '');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indeks untuk tabel `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `recipe_ingredients`
--
ALTER TABLE `recipe_ingredients`
  ADD PRIMARY KEY (`recipe_id`,`ingredient_id`),
  ADD KEY `ingredient_id` (`ingredient_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `recipes`
--
ALTER TABLE `recipes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `recipe_ingredients`
--
ALTER TABLE `recipe_ingredients`
  ADD CONSTRAINT `recipe_ingredients_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipe_ingredients_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
