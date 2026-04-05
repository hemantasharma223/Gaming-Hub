-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 05, 2026 at 08:07 PM
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
-- Database: `gaming_hub`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `username`, `password`, `created_at`) VALUES
(2, 'admin', '$2y$10$lqiaY7dmvKEoT9mlSGdAtenzRahvoYtt1VmjvJdO800nqhE255F.G', '2025-06-18 04:44:57');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `product_id`, `quantity`, `added_at`) VALUES
(24, 5, 10, 1, '2026-03-27 12:15:44');

-- --------------------------------------------------------

--
-- Table structure for table `main_categories`
--

CREATE TABLE `main_categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `main_categories`
--

INSERT INTO `main_categories` (`category_id`, `name`, `slug`, `image`, `is_active`, `created_at`) VALUES
(1, 'PS5', 'ps5', '685d8d27ceef6.png', 1, '2025-06-18 05:09:26'),
(2, 'PS4', 'ps4', '685d8d985f940.PNG', 1, '2025-06-26 18:12:40'),
(3, 'PC', 'pc', '685d8de4b4a2a.jpg', 1, '2025-06-26 18:13:56');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `shipping_address` text NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `payment_method` varchar(50) DEFAULT 'Cash on Delivery',
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `admin_response` text DEFAULT NULL,
  `response_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `order_date`, `shipping_address`, `contact_number`, `payment_method`, `total_amount`, `status`, `admin_response`, `response_date`) VALUES
(2, 2, '2025-06-22 08:31:41', 'Kawasoti\r\nNawalpur East', '9869837027', '', 10000.00, 'cancelled', 'payment method is not confirmed!!!', '2025-06-22 10:18:25'),
(3, 2, '2025-06-22 10:11:51', 'Kawasoti\r\nNawalpur East', '9869837027', 'khaltiPayment', 10000.00, 'cancelled', 'payment not done', '2025-06-26 18:29:47'),
(4, 2, '2025-06-22 10:15:06', 'Kawasoti\r\nNawalpur East', '9869837027', 'khaltiPayment', 20000.00, 'delivered', 'payment confirmed...\r\nshipped...', '2025-06-26 18:28:57'),
(5, 1, '2025-06-26 16:28:29', 'ktm', '9811111111', 'khaltiPayment', 100.00, 'processing', 'processing', '2025-06-26 16:29:46'),
(6, 3, '2025-06-26 18:25:55', 'KTM', '9810100289', 'khaltiPayment', 6000.00, 'processing', 'Verifying Payment', '2025-06-26 18:26:48'),
(7, 3, '2025-06-27 02:22:18', 'KTM', '9812345678', 'khaltiPayment', 135000.00, 'processing', 'Payment done', '2025-06-27 02:22:34'),
(8, 1, '2025-12-30 15:37:44', 'Kawasoti\r\nNawalpur East', '9869837027', 'khaltiPayment', 6000.00, 'pending', NULL, NULL),
(9, 1, '2026-03-27 10:00:40', 'Kawasoti\r\nNawalpur East', '9869837027', 'cod', 5000.00, 'pending', NULL, NULL),
(10, 1, '2026-03-27 11:14:49', 'Machhapokhari', '9810100269', 'khaltiPayment', 2500.00, 'pending', NULL, NULL),
(11, 5, '2026-03-27 12:00:16', 'Machhapokhari', '9810100269', 'cod', 4000.00, 'pending', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`item_id`, `order_id`, `product_id`, `quantity`, `unit_price`) VALUES
(5, 5, 4, 1, 100.00),
(6, 6, 9, 1, 6000.00),
(7, 7, 4, 1, 135000.00),
(8, 8, 22, 1, 6000.00),
(9, 9, 19, 1, 5000.00),
(10, 10, 17, 1, 2500.00),
(11, 11, 15, 1, 4000.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `subcategory_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tags` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `subcategory_id`, `name`, `slug`, `description`, `price`, `discount_price`, `image`, `stock`, `is_featured`, `is_active`, `created_at`, `updated_at`, `tags`) VALUES
(4, 1, 'ps5', '', 'Ps5', 135000.00, NULL, '685d8195dd965.png', 9, 1, 1, '2025-06-26 16:26:53', '2026-03-27 10:18:21', 'playstation, console, high-end'),
(7, 2, 'PS4', NULL, 'PS4', 90000.00, NULL, '685d8f4e1badb.PNG', 10, 1, 1, '2025-06-26 18:19:58', '2025-06-26 18:19:58', NULL),
(8, 3, 'PC', NULL, 'PC', 150000.00, NULL, '685d8f8844846.jpg', 20, 1, 1, '2025-06-26 18:20:56', '2025-06-26 18:20:56', NULL),
(9, 4, 'God of War', NULL, 'Ragnarok', 6000.00, NULL, '685d902448a0b.png', 9, 0, 1, '2025-06-26 18:23:32', '2026-03-27 10:18:21', 'action, adventure'),
(10, 6, 'Grand Theft Auto', NULL, 'GTA V', 2500.00, NULL, '685d922d1aab2.PNG', 10, 1, 1, '2025-06-26 18:32:13', '2025-06-26 19:27:34', NULL),
(11, 6, 'Proto Type', NULL, 'Prototype', 2500.00, NULL, '685d925d642a2.jpg', 10, 0, 1, '2025-06-26 18:33:01', '2025-06-26 19:27:27', NULL),
(12, 5, 'Uncharted', NULL, 'Uncharted 4', 4000.00, NULL, '685d92d898672.PNG', 10, 1, 1, '2025-06-26 18:35:04', '2026-03-27 10:18:21', 'action, adventure'),
(13, 5, 'WWE', NULL, '2k25', 4000.00, NULL, '685d93e4ee950.jpg', 10, 0, 1, '2025-06-26 18:39:32', '2026-03-27 10:18:21', 'sports, multiplayer'),
(14, 5, 'Moto Kombat', NULL, 'MotoKombat', 4000.00, NULL, '685d94235bb55.PNG', 10, 0, 1, '2025-06-26 18:40:35', '2025-06-26 19:25:49', NULL),
(15, 5, 'Call of Duty', NULL, 'Call of Duty', 4000.00, NULL, '685d943fdff55.jpg', 9, 1, 1, '2025-06-26 18:41:03', '2026-03-27 12:00:16', NULL),
(16, 6, 'WWE', NULL, '2k20', 2500.00, NULL, '685d94eff248a.PNG', 10, 0, 1, '2025-06-26 18:43:59', '2026-03-27 10:18:21', 'sports, multiplayer'),
(17, 6, 'Call of Duty', NULL, 'Call of Duty', 2500.00, NULL, '685d95156ddff.jpg', 9, 1, 1, '2025-06-26 18:44:37', '2026-03-27 11:14:49', NULL),
(18, 6, 'Witcher', NULL, 'Witcher', 2500.00, NULL, '685d952b26236.png', 10, 0, 1, '2025-06-26 18:44:59', '2025-06-26 19:26:54', NULL),
(19, 4, 'FIFA', NULL, 'FIFA 25', 5000.00, NULL, '685d95ba01913.jpg', 9, 1, 1, '2025-06-26 18:47:22', '2026-03-27 10:18:21', 'sports, multiplayer'),
(20, 4, 'Tekken', NULL, 'Tekken', 5000.00, NULL, '685d95cee2dc0.jpg', 10, 0, 1, '2025-06-26 18:47:42', '2026-03-27 10:18:21', 'fighting, multiplayer'),
(21, 4, 'Sucide Squad', NULL, 'Sucide Squad', 4000.00, NULL, '685dfb293545b.jpeg', 10, 0, 1, '2025-06-27 02:00:09', '2025-06-27 02:00:09', NULL),
(22, 4, 'Black Myth', NULL, 'Wukong', 6000.00, NULL, '685dfbff9dae4.PNG', 9, 1, 1, '2025-06-27 02:03:43', '2025-12-30 15:37:44', NULL),
(23, 5, 'Street Fighter', NULL, 'VI', 3000.00, NULL, '685dfcaa42dda.PNG', 10, 0, 1, '2025-06-27 02:06:34', '2026-03-27 10:18:21', 'fighting, multiplayer'),
(24, 5, 'Ghost of Tsushima', NULL, 'Ghost of Tsushima', 3000.00, NULL, '685dfcf3944d2.jpg', 10, 0, 1, '2025-06-27 02:07:47', '2025-06-27 02:07:47', NULL),
(25, 5, 'Mafia', NULL, 'III', 2500.00, NULL, '685dfd1a7cd2a.jpg', 10, 0, 1, '2025-06-27 02:08:26', '2025-06-27 02:08:26', NULL),
(26, 6, 'Farcry', NULL, 'One', 1000.00, NULL, '685dfe0447270.jpg', 10, 0, 1, '2025-06-27 02:12:20', '2025-06-27 02:12:20', NULL),
(27, 6, 'FARCRY', NULL, 'III', 2000.00, NULL, '685dfe3179388.jpg', 10, 0, 1, '2025-06-27 02:13:05', '2025-06-27 02:13:05', NULL),
(28, 6, 'TEKKEN', NULL, 'VIII', 4000.00, NULL, '685dfe8db264c.PNG', 10, 0, 1, '2025-06-27 02:14:37', '2026-03-27 10:18:21', 'fighting, multiplayer'),
(29, 6, 'Watchdog', NULL, 'One', 2500.00, NULL, '685dfeb75abda.PNG', 10, 1, 1, '2025-06-27 02:15:19', '2025-06-27 02:15:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `subcategories`
--

CREATE TABLE `subcategories` (
  `subcategory_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subcategories`
--

INSERT INTO `subcategories` (`subcategory_id`, `category_id`, `name`, `slug`, `is_active`, `created_at`) VALUES
(1, 1, 'PS5 Box', 'ps5-box', 1, '2025-06-18 12:59:27'),
(2, 2, 'PS4', 'ps4', 1, '2025-06-26 18:12:58'),
(3, 3, 'PC', 'pc', 1, '2025-06-26 18:20:24'),
(4, 1, 'Ps5 Game', 'ps5-game', 1, '2025-06-26 19:23:09'),
(5, 2, 'Ps4 Game', 'ps4-game', 1, '2025-06-26 19:24:26'),
(6, 3, 'Game', 'game', 1, '2025-06-26 19:26:45');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password`, `full_name`, `phone`, `address`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'hemantasharma223@gmail.com', '$2y$10$47z9XPzyDcXkKRb9F8.AT.Dp70rktVGlt4xNVXvB1ZFFRQa4u2drS', 'Kshitish Bhurtel', NULL, NULL, 1, '2025-06-18 13:03:44', '2025-06-26 16:55:53'),
(2, 'kshitishbhurtel@tuicms.edu.np', '$2y$10$ydnjyb5D1whCyojX4H2j2uMvuZOcXFQRLiYtd/KKtYB6jP2iaMT4K', 'Kshitish Bhurtel', NULL, NULL, 1, '2025-06-22 08:08:03', '2025-06-26 16:54:45'),
(3, 'dipeshkarki758@gmail.com', '$2y$10$jaxxwIDqqTD02DqHUZ.2c.QZtvi98W5GgeyQfqI/jfLFOnV8eo6lW', 'Dipesh Karki', NULL, NULL, 0, '2025-06-26 18:24:04', '2025-06-27 02:26:45'),
(4, 'karki@gmail.com', '$2y$10$2PAKOrrASVidA.bYtadRauxVYVxgixoO3IuwQUBx2Uqmg10fPkbPy', 'Karki', NULL, NULL, 1, '2025-06-27 02:43:35', '2025-06-27 02:43:35'),
(5, 'sagarkarki@tuicms.edu.np', '$2y$10$aOGZ7IP52g4Za5GOc68Dyua6zjQ/g9Ywxa/sPtC37uWTm.UxAeVee', 'Sagar Karki', NULL, NULL, 1, '2026-03-27 11:59:20', '2026-03-27 11:59:20'),
(6, 'tester@test.com', '$2y$10$BJbb9/U2bGXuAsGVcTFsTug5pmlEmHI7RfULIx17h57e0SITYHf2W', 'Tester', NULL, NULL, 1, '2026-04-05 17:46:07', '2026-04-05 17:46:07');

-- --------------------------------------------------------

--
-- Table structure for table `user_activity`
--

CREATE TABLE `user_activity` (
  `activity_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `action_type` enum('view','cart','purchase') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_activity`
--

INSERT INTO `user_activity` (`activity_id`, `user_id`, `product_id`, `action_type`, `created_at`) VALUES
(1, 1, 29, 'view', '2026-03-27 11:00:54'),
(2, 1, 17, 'view', '2026-03-27 11:13:19'),
(3, NULL, 9, 'view', '2026-03-27 11:25:24'),
(4, NULL, 10, 'view', '2026-03-27 11:38:42'),
(5, 1, 19, 'view', '2026-03-27 11:55:10'),
(6, 1, 10, 'view', '2026-03-27 11:57:47'),
(7, 5, 29, 'view', '2026-03-27 12:07:42'),
(8, 5, 10, 'view', '2026-03-27 12:15:24'),
(9, 5, 10, 'cart', '2026-03-27 12:15:44'),
(10, NULL, 22, 'view', '2026-04-01 12:42:54'),
(11, NULL, 22, 'view', '2026-04-01 12:43:12'),
(12, NULL, 22, 'cart', '2026-04-01 12:43:26'),
(13, NULL, 22, 'view', '2026-04-01 12:43:59'),
(14, NULL, 22, 'cart', '2026-04-01 12:44:08'),
(15, NULL, 22, 'cart', '2026-04-01 12:44:08'),
(16, NULL, 22, 'cart', '2026-04-01 12:44:08'),
(17, NULL, 22, 'cart', '2026-04-01 12:44:08'),
(18, NULL, 22, 'cart', '2026-04-01 12:44:08'),
(19, NULL, 22, 'cart', '2026-04-01 12:44:08'),
(20, NULL, 22, 'cart', '2026-04-01 12:44:08'),
(21, NULL, 22, 'cart', '2026-04-01 12:44:08'),
(22, NULL, 22, 'cart', '2026-04-01 12:44:08'),
(23, NULL, 22, 'cart', '2026-04-01 12:44:08'),
(24, NULL, 22, 'view', '2026-04-05 16:04:44'),
(25, NULL, 22, 'cart', '2026-04-05 16:04:52'),
(26, NULL, 22, 'view', '2026-04-05 16:05:00'),
(27, NULL, 10, 'view', '2026-04-05 17:09:47'),
(28, NULL, 10, 'cart', '2026-04-05 17:10:02'),
(29, NULL, 22, 'view', '2026-04-05 17:10:45'),
(30, NULL, 22, 'cart', '2026-04-05 17:10:51'),
(31, NULL, 22, 'view', '2026-04-05 17:11:15'),
(32, NULL, 22, 'cart', '2026-04-05 17:11:25'),
(33, NULL, 22, 'view', '2026-04-05 17:20:34'),
(34, NULL, 22, 'cart', '2026-04-05 17:20:40'),
(35, NULL, 4, 'view', '2026-04-05 17:23:35'),
(36, NULL, 4, 'view', '2026-04-05 17:24:43'),
(37, NULL, 4, 'view', '2026-04-05 17:25:02'),
(38, NULL, 4, 'cart', '2026-04-05 17:25:15'),
(39, NULL, 4, 'view', '2026-04-05 17:25:50'),
(40, NULL, 4, 'cart', '2026-04-05 17:26:04'),
(41, NULL, 4, 'view', '2026-04-05 17:26:38'),
(42, NULL, 4, 'view', '2026-04-05 17:28:38'),
(43, NULL, 4, 'view', '2026-04-05 17:29:56'),
(44, NULL, 4, 'view', '2026-04-05 17:32:28'),
(45, NULL, 4, 'view', '2026-04-05 17:32:53'),
(46, NULL, 4, 'view', '2026-04-05 17:33:31'),
(47, NULL, 4, 'cart', '2026-04-05 17:33:46'),
(48, NULL, 4, 'view', '2026-04-05 17:34:16'),
(49, NULL, 4, 'cart', '2026-04-05 17:34:30'),
(50, NULL, 4, 'view', '2026-04-05 17:35:00'),
(51, NULL, 4, 'view', '2026-04-05 17:37:37'),
(52, NULL, 4, 'cart', '2026-04-05 17:37:49'),
(53, NULL, 22, 'view', '2026-04-05 17:41:02'),
(54, NULL, 22, 'cart', '2026-04-05 17:41:13'),
(55, NULL, 4, 'view', '2026-04-05 17:41:36'),
(56, NULL, 4, 'view', '2026-04-05 17:48:31'),
(57, NULL, 4, 'view', '2026-04-05 17:53:05'),
(58, NULL, 4, 'cart', '2026-04-05 17:54:40'),
(59, NULL, 4, 'view', '2026-04-05 17:55:39'),
(60, NULL, 4, 'view', '2026-04-05 17:56:34'),
(61, NULL, 4, 'cart', '2026-04-05 17:57:03'),
(62, NULL, 22, 'view', '2026-04-05 17:59:24'),
(63, NULL, 22, 'view', '2026-04-05 17:59:32'),
(64, NULL, 22, 'view', '2026-04-05 17:59:44'),
(65, NULL, 22, 'cart', '2026-04-05 17:59:48'),
(66, NULL, 4, 'view', '2026-04-05 18:01:56'),
(67, NULL, 4, 'view', '2026-04-05 18:03:35'),
(68, NULL, 4, 'view', '2026-04-05 18:04:01'),
(69, NULL, 22, 'view', '2026-04-05 18:04:13'),
(70, NULL, 22, 'cart', '2026-04-05 18:04:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `main_categories`
--
ALTER TABLE `main_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `subcategory_id` (`subcategory_id`);

--
-- Indexes for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD PRIMARY KEY (`subcategory_id`),
  ADD UNIQUE KEY `category_id` (`category_id`,`slug`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_activity`
--
ALTER TABLE `user_activity`
  ADD PRIMARY KEY (`activity_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `main_categories`
--
ALTER TABLE `main_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `subcategories`
--
ALTER TABLE `subcategories`
  MODIFY `subcategory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_activity`
--
ALTER TABLE `user_activity`
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`subcategory_id`) ON DELETE CASCADE;

--
-- Constraints for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD CONSTRAINT `subcategories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `main_categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_activity`
--
ALTER TABLE `user_activity`
  ADD CONSTRAINT `user_activity_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `user_activity_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
