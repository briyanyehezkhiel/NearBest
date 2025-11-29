-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 29, 2025 at 06:20 PM
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
-- Database: `nearbest`
--

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int NOT NULL,
  `sender_username` varchar(100) NOT NULL,
  `receiver_username` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_read` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `sender_username`, `receiver_username`, `message`, `created_at`, `is_read`) VALUES
(1, 'briyanyehezkhiel', 'admin', 'hello', '2025-11-27 19:11:18', 1),
(2, 'briyanyehezkhiel', 'admin', 'halo ', '2025-11-27 19:30:12', 1),
(3, 'admin', 'admin', 'hello ', '2025-11-27 19:52:13', 1),
(4, 'admin', 'briyanyehezkhiel', 'iya', '2025-11-27 19:52:23', 1),
(5, 'admin', 'briyanyehezkhiel', 'iya kenapa? ', '2025-11-29 18:08:37', 1),
(6, 'briyanyehezkhiel', 'admin', 'seep dah bisa jalan deng ', '2025-11-29 18:15:53', 1);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int NOT NULL,
  `user_username` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `link` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_username`, `title`, `message`, `type`, `is_read`, `created_at`, `link`) VALUES
(1, 'admin', 'Pesan Baru', 'Anda mendapat pesan dari briyanyehezkhiel', 'chat', 1, '2025-11-27 19:11:18', 'chat.php?to=briyanyehezkhiel'),
(2, 'admin', 'Order Baru', 'Anda mendapat order baru dari briyanyehezkhiel dengan total Rp 30.000', 'info', 1, '2025-11-27 19:29:53', 'orders.php?order_id=1'),
(3, 'briyanyehezkhiel', 'Order Berhasil', 'Order Anda telah dibuat. Silakan hubungi seller untuk pembayaran dan pengiriman.', 'success', 1, '2025-11-27 19:29:53', 'my_orders.php?order_id=1'),
(4, 'admin', 'Pesan Baru', 'Anda mendapat pesan dari briyanyehezkhiel', 'chat', 1, '2025-11-27 19:30:12', 'chat.php?to=briyanyehezkhiel'),
(5, 'admin', 'Pesan Baru', 'Anda mendapat pesan dari admin', 'chat', 1, '2025-11-27 19:52:13', 'chat.php?to=admin'),
(6, 'briyanyehezkhiel', 'Pesan Baru', 'Anda mendapat pesan dari admin', 'chat', 1, '2025-11-27 19:52:23', 'chat.php?to=admin'),
(7, 'briyanyehezkhiel', 'Pesan Baru', 'Anda mendapat pesan dari admin', 'chat', 1, '2025-11-29 18:08:37', 'chat.php?to=admin'),
(8, 'seller', 'Order Baru', 'Anda mendapat order baru dari briyanyehezkhiel dengan total Rp 20.000', 'info', 0, '2025-11-29 18:15:14', 'orders.php?order_id=2'),
(9, 'briyanyehezkhiel', 'Order Berhasil', 'Order Anda telah dibuat. Silakan hubungi seller untuk pembayaran dan pengiriman.', 'success', 1, '2025-11-29 18:15:14', 'my_orders.php?order_id=2'),
(10, 'admin', 'Pesan Baru', 'Anda mendapat pesan dari briyanyehezkhiel', 'chat', 1, '2025-11-29 18:15:53', 'chat.php?to=briyanyehezkhiel'),
(11, 'briyanyehezkhiel', 'Status Order Diupdate', 'Status order #1 telah diupdate menjadi: CONFIRMED', 'info', 0, '2025-11-29 18:18:52', 'my_orders.php?order_id=1');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `buyer_id` int DEFAULT NULL,
  `buyer_username` varchar(100) DEFAULT NULL,
  `seller_username` varchar(100) DEFAULT 'admin',
  `seller_store_name` varchar(255) DEFAULT NULL,
  `seller_store_address` text,
  `total` int DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `buyer_id`, `buyer_username`, `seller_username`, `seller_store_name`, `seller_store_address`, `total`, `total_amount`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, NULL, 'briyanyehezkhiel', 'admin', NULL, NULL, NULL, '30000.00', 'confirmed', NULL, '2025-11-28 02:29:53', '2025-11-29 18:18:52'),
(2, NULL, 'briyanyehezkhiel', 'seller', '', '', NULL, '20000.00', 'pending', NULL, '2025-11-30 01:15:14', '2025-11-29 18:15:14');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `qty` int DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `price` int DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `qty`, `quantity`, `price`, `subtotal`) VALUES
(1, 1, 1, 'Produk 1', NULL, 1, 30000, '30000.00'),
(2, 2, 4, 'aoaas', NULL, 1, 20000, '20000.00');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `price` int DEFAULT NULL,
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `seller_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `description`, `image`, `category`, `tags`, `seller_id`) VALUES
(1, 'Produk 1', 30000, 'Deskripsi Produk 1', 'default-1.jpg', 'makanan', 'sehat', 5),
(2, 'Produk 2', 25000, 'Deskripsi Produk 2', 'default-2.jpg', NULL, NULL, NULL),
(3, 'Produk 3', 20000, 'Deskripsi Produk 3', 'default-3.jpg', NULL, NULL, NULL),
(4, 'aoaas', 20000, 'makanan ringan', 'default-1.jpg', 'cobbaan', 'bisa ', 2);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','seller','buyer') DEFAULT 'buyer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`) VALUES
(1, 'briyanyehezkhiel', 'briyanyehezkhiel@gmail.com', '$2y$10$ZNKj0UHcQB9BiaEJn/BSDOFzyK2MnvDtAgjqSBSwcHdQvoJ6K2ItO', 'buyer'),
(2, 'seller', 'seller@gmail.com', '$2y$10$OBHp.fed95rYcg0SLfpTte4xd9UCDfWPDEMum21Z/3gZg/jEeJrCC', 'seller'),
(5, 'admin', 'admin@gmail.com', '$2y$10$IpE6BPpgS/QVnHng30R8x.Z8P/TAgpTUDrNynVOd7hrfJvwNQNnty', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sender` (`sender_username`),
  ADD KEY `idx_receiver` (`receiver_username`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_username`),
  ADD KEY `idx_read` (`is_read`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_buyer` (`buyer_username`),
  ADD KEY `idx_seller` (`seller_username`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_product` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
