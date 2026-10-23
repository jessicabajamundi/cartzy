-- ==========================================================
-- Cartzy Live Database Dump / Schema
-- Database: `ecommerce_db`
-- Auto-synced at: 2026-10-20 04:57:43
-- ==========================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `ecommerce_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ecommerce_db`;

-- --------------------------------------------------------
-- Table structure for table `activity_logs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `method` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `activity_logs_user_id_foreign` (`user_id`),
  KEY `activity_logs_event_created_at_index` (`event`,`created_at`),
  KEY `activity_logs_created_at_index` (`created_at`),
  CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `activity_logs` (57 rows)
INSERT INTO `activity_logs` (`id`, `user_id`, `email`, `role`, `event`, `method`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
('1', '10', 'jessica.p.bajamundi@gmail.com', 'buyer', 'logout', NULL, NULL, '111.90.198.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36 Edg/155.0.0.0', '2026-10-19 06:21:54'),
('3', '37', 'adidas@cartzy.com', 'seller', 'login', 'password', NULL, '111.90.198.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36 Edg/155.0.0.0', '2026-10-19 06:22:34'),
('4', '37', 'adidas@cartzy.com', 'seller', 'logout', NULL, NULL, '111.90.198.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36 Edg/155.0.0.0', '2026-10-19 06:26:00'),
('5', '5', 'administrationa570@gmail.com', 'admin', 'login', 'password', NULL, '111.90.198.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36 Edg/155.0.0.0', '2026-10-19 06:26:16'),
('6', '41', 'nike@cartzy.com', 'seller', 'failed_login', 'password', 'Wrong login portal (buyer)', '111.90.198.68', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Mobile Safari/537.36', '2026-10-19 06:28:52'),
('7', NULL, 'nike@cartzy.com', NULL, 'failed_login', 'password', 'Invalid credentials', '111.90.198.68', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Mobile Safari/537.36', '2026-10-19 06:29:55'),
('8', '41', 'nike@cartzy.com', 'seller', 'failed_login', 'password', 'Wrong login portal (buyer)', '111.90.198.68', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Mobile Safari/537.36', '2026-10-19 06:30:44'),
('9', '41', 'nike@cartzy.com', 'seller', 'login', 'password', NULL, '111.90.198.68', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Mobile Safari/537.36', '2026-10-19 06:31:02'),
('10', '5', 'administrationa570@gmail.com', 'admin', 'login', 'password', NULL, '209.35.172.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-19 06:40:29'),
('11', '5', 'administrationa570@gmail.com', 'admin', 'logout', NULL, NULL, '209.35.172.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36 Edg/155.0.0.0', '2026-10-19 06:43:17'),
('12', '11', 'logistics@cartzy.ph', 'logistics', 'login', 'password', NULL, '209.35.172.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36 Edg/155.0.0.0', '2026-10-19 06:43:50'),
('13', '5', 'administrationa570@gmail.com', 'admin', 'logout', NULL, NULL, '209.35.172.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-19 06:50:49'),
('14', '10', 'jessica.p.bajamundi@gmail.com', 'buyer', 'login', 'google', NULL, '209.35.172.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-19 06:51:50'),
('15', '41', 'nike@cartzy.com', 'seller', 'logout', NULL, NULL, '209.35.172.28', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Mobile Safari/537.36', '2026-10-19 06:52:06'),
('16', '3', 'tiffany.leonardolspu@gmail.com', 'buyer', 'login', 'google', NULL, '175.158.218.73', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Mobile/15E148 Safari/604.1', '2026-10-19 06:52:19'),
('17', '10', 'jessica.p.bajamundi@gmail.com', 'buyer', 'logout', NULL, NULL, '209.35.172.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-19 06:53:03'),
('18', '42', 'logitech@cartzy.com', 'seller', 'login', 'password', NULL, '209.35.172.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-19 06:53:30'),
('19', '42', 'logitech@cartzy.com', 'seller', 'logout', NULL, NULL, '209.35.172.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-19 06:55:36'),
('20', '10', 'jessica.p.bajamundi@gmail.com', 'buyer', 'login', 'google', NULL, '209.35.172.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-19 06:55:46'),
('21', '10', 'jessica.p.bajamundi@gmail.com', 'buyer', 'logout', NULL, NULL, '209.35.172.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-19 06:59:28'),
('22', '5', 'administrationa570@gmail.com', 'admin', 'login', 'password', NULL, '209.35.172.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-19 06:59:56'),
('23', '5', 'administrationa570@gmail.com', 'admin', 'logout', NULL, NULL, '209.35.172.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-19 07:08:36'),
('24', '42', 'logitech@cartzy.com', 'seller', 'login', 'password', NULL, '209.35.172.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-19 07:13:15'),
('25', '42', 'logitech@cartzy.com', 'seller', 'logout', NULL, NULL, '209.35.172.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-19 07:18:54'),
('26', '10', 'jessica.p.bajamundi@gmail.com', 'buyer', 'login', 'google', NULL, '209.35.172.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-19 07:19:02'),
('27', '10', 'jessica.p.bajamundi@gmail.com', 'buyer', 'logout', NULL, NULL, '209.35.172.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-19 07:21:13'),
('28', '42', 'logitech@cartzy.com', 'seller', 'login', 'password', NULL, '209.35.172.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-19 07:21:40'),
('29', '42', 'logitech@cartzy.com', 'seller', 'logout', NULL, NULL, '209.35.172.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-19 07:23:04'),
('30', '11', 'logistics@cartzy.ph', 'logistics', 'login', 'password', NULL, '209.35.172.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-19 07:23:18'),
('31', '11', 'logistics@cartzy.ph', 'logistics', 'logout', NULL, NULL, '209.35.172.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-19 07:26:12'),
('32', '42', 'logitech@cartzy.com', 'seller', 'login', 'password', NULL, '209.35.172.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-19 07:26:36'),
('33', '10', 'jessica.p.bajamundi@gmail.com', 'buyer', 'login', 'google', NULL, '2001:fd8:2698:bd5d:18df:d055:f1e7:28d5', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-19 07:29:11'),
('34', '42', 'logitech@cartzy.com', 'seller', 'logout', NULL, NULL, '209.35.172.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-19 07:30:33'),
('35', '22', 'arnel.rider@cartzy.ph', 'courier', 'login', 'password', NULL, '209.35.172.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-19 07:31:12'),
('36', '11', 'logistics@cartzy.ph', 'logistics', 'logout', NULL, NULL, '209.35.172.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36 Edg/155.0.0.0', '2026-10-19 07:33:51'),
('37', '10', 'jessica.p.bajamundi@gmail.com', 'buyer', 'login', 'google', NULL, '209.35.172.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36 Edg/155.0.0.0', '2026-10-19 07:34:08'),
('38', '22', 'arnel.rider@cartzy.ph', 'courier', 'logout', NULL, NULL, '209.35.172.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-19 07:35:14'),
('39', '42', 'logitech@cartzy.com', 'seller', 'login', 'password', NULL, '209.35.172.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-19 07:35:45'),
('40', '42', 'logitech@cartzy.com', 'seller', 'logout', NULL, NULL, '209.35.172.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-19 08:04:46'),
('41', '5', 'administrationa570@gmail.com', 'admin', 'login', 'password', NULL, '209.35.172.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-19 08:05:04'),
('42', '10', 'jessica.p.bajamundi@gmail.com', 'buyer', 'logout', NULL, NULL, '209.35.172.28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36 Edg/155.0.0.0', '2026-10-19 08:16:03'),
('43', '10', 'jessica.p.bajamundi@gmail.com', 'buyer', 'login', 'google', NULL, '216.247.82.26', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36 Edg/155.0.0.0', '2026-10-20 03:11:01'),
('44', '10', 'jessica.p.bajamundi@gmail.com', 'buyer', 'logout', NULL, NULL, '216.247.82.26', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36 Edg/155.0.0.0', '2026-10-20 03:50:27'),
('45', '42', 'logitech@cartzy.com', 'seller', 'login', 'password', NULL, '216.247.82.26', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36 Edg/155.0.0.0', '2026-10-20 03:50:56'),
('46', '42', 'logitech@cartzy.com', 'seller', 'logout', NULL, NULL, '216.247.82.26', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36 Edg/155.0.0.0', '2026-10-20 03:51:58'),
('47', NULL, 'administrationa570@gmail.com', NULL, 'failed_login', 'password', 'Invalid credentials', '216.247.82.26', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36 Edg/155.0.0.0', '2026-10-20 03:52:15'),
('48', '5', 'administrationa570@gmail.com', 'admin', 'login', 'password', NULL, '216.247.82.26', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36 Edg/155.0.0.0', '2026-10-20 03:52:23'),
('49', '48', 'cancer.mike2@gmail.com', 'buyer', 'login', 'registration', NULL, '175.158.218.97', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-20 04:00:05'),
('50', '5', 'administrationa570@gmail.com', 'admin', 'logout', NULL, NULL, '216.247.82.26', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36 Edg/155.0.0.0', '2026-10-20 04:12:09'),
('51', '10', 'jessica.p.bajamundi@gmail.com', 'buyer', 'login', 'google', NULL, '216.247.82.26', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36 Edg/155.0.0.0', '2026-10-20 04:12:51'),
('52', '10', 'jessica.p.bajamundi@gmail.com', 'buyer', 'logout', NULL, NULL, '216.247.82.26', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36 Edg/155.0.0.0', '2026-10-20 04:13:55'),
('53', '5', 'administrationa570@gmail.com', 'admin', 'login', 'password', NULL, '216.247.82.26', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36 Edg/155.0.0.0', '2026-10-20 04:14:13'),
('54', '5', 'administrationa570@gmail.com', 'admin', 'logout', NULL, NULL, '216.247.82.26', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36 Edg/155.0.0.0', '2026-10-20 04:16:40'),
('55', '10', 'jessica.p.bajamundi@gmail.com', 'buyer', 'login', 'google', NULL, '216.247.82.26', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36 Edg/155.0.0.0', '2026-10-20 04:17:01'),
('56', '9', 'reybendelmundo2005@gmail.com', 'buyer', 'login', 'google', NULL, '2001:fd8:d23f:e600:48ba:5707:d72:b2f3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-20 04:18:18'),
('57', '41', 'nike@cartzy.com', 'seller', 'failed_login', 'password', 'Wrong login portal (buyer)', '1.37.89.16', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Mobile Safari/537.36', '2026-10-20 04:19:22'),
('58', '41', 'nike@cartzy.com', 'seller', 'login', 'password', NULL, '1.37.89.16', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Mobile Safari/537.36', '2026-10-20 04:20:02');

-- --------------------------------------------------------
-- Table structure for table `addresses`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `addresses`;
CREATE TABLE `addresses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recipient` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `line1` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `barangay` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `province` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `addresses_user_id_foreign` (`user_id`),
  CONSTRAINT `addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `addresses` (21 rows)
INSERT INTO `addresses` (`id`, `user_id`, `label`, `recipient`, `phone`, `line1`, `barangay`, `city`, `province`, `postal_code`, `is_default`, `created_at`, `updated_at`) VALUES
('1', '1', 'Default Address', 'jess Pambago', '09773587409', '1011, purok 4', 'Masapang', 'Victoria', 'Laguna', '4011', '1', '2026-09-21 06:03:57', '2026-09-21 06:03:57'),
('2', '4', 'Default Address', 'Pajavera, Nhieckaella Ashley R.', '09152608445', 'St. Burol', 'Magdapio', 'Pagsanjan', 'Laguna', '4008', '0', '2026-09-21 06:03:57', '2026-10-14 03:21:03'),
('5', '10', 'Home', 'Jessica Pambago', '09773587409', '1011, purok 4', 'Masapang', 'Victoria', 'Laguna', '4011', '1', '2026-10-10 06:04:29', '2026-10-10 06:04:29'),
('6', '28', 'Main Warehouse & Pickup Hub', 'happyskinphil Warehouse', '09178889900', 'High Street Central, BGC', 'Fort Bonifacio', 'Taguig City', 'Metro Manila', '1634', '1', '2026-10-11 07:23:16', '2026-10-11 07:23:16'),
('7', '4', 'School', 'Pajavera, Nhieckaella Ashley R.', '09152608445', 'LSPU Campus', 'Bagumbayan', 'Santa Cruz', 'Laguna', '4009', '1', '2026-10-14 03:21:03', '2026-10-14 03:21:03'),
('8', '35', 'Home', 'Ella Rivera', '09152608445', 'St. Burol', 'Magdapio', 'Pagsanjan', 'Laguna', '4008', '1', '2026-10-14 11:12:35', '2026-10-14 11:12:35'),
('9', '37', 'Adidas Main Logistics Hub', 'Adidas Official Store Warehouse', '09178883344', 'Bonifacio High Street Central, 9th Ave', 'Fort Bonifacio', 'Taguig City', 'Metro Manila', '1634', '1', '2026-10-15 13:25:00', '2026-10-15 13:25:00'),
('10', '38', 'Shop pickup', 'Nhieckaella Pajavera', '09561736901', 'St. Burol', 'Magdapio', 'Pagsanjan', 'Laguna', '4008', '1', '2026-10-15 14:48:24', '2026-10-15 14:48:24'),
('14', '39', 'Home', 'Juan Miguel Jr.', '0917123', '14F Pacific Tower', 'Makina', 'Naic', 'Cavite', '2213', '1', '2026-10-16 08:01:29', '2026-10-16 08:15:25'),
('16', '39', 'Home', 'Juan Miguel Jr.', '0917123655', 'Herald Building Residence', 'San Pablo', 'City of Sto. Tomas', 'Batangas', '3131', '0', '2026-10-16 08:04:05', '2026-10-16 08:15:25'),
('17', '39', 'Home', 'Juan Miguel Jr.', '0917123', 'Cavite GMA, Subdivision 1125', 'Malainen Bago', 'Naic', 'Cavite', '5034', '0', '2026-10-16 08:04:33', '2026-10-16 08:15:25'),
('18', '39', 'Home', 'Juan Miguel Jr.', '0917123', 'City of Cainta 1190', 'Santa Rosa', 'Cainta', 'Rizal', '0913', '0', '2026-10-16 08:05:06', '2026-10-16 08:15:25'),
('19', '39', 'Home', 'Juan Miguel Jr.', '0917123', 'Revilla Residence 4565', 'Ligas II', 'City of Bacoor', 'Cavite', '2314', '0', '2026-10-16 08:05:39', '2026-10-16 08:15:25'),
('20', '39', 'Home', 'Juan Miguel Jr.', '09171234234', 'villame, 7841 subdivision', 'Zillovia', 'Talacogon', 'Agusan Del Sur', '6565', '0', '2026-10-16 08:06:12', '2026-10-16 08:15:25'),
('21', '39', 'Home', 'Juan Miguel Jr.', '09171232311', 'Luisiana, Majayjay Road 231', 'San Jose', 'Luisiana', 'Laguna', '1312', '0', '2026-10-16 08:06:54', '2026-10-16 08:15:25'),
('22', '39', 'Home', 'Juan Miguel Jr.', '09171236746', 'Marcos Residence 2313', 'Anabu II-C', 'City of Imus', 'Cavite', '7456', '0', '2026-10-16 08:07:35', '2026-10-16 08:15:25'),
('23', '9', 'Home', 'Jan Reyben Del Mundo', '09875643272', '2100 San Luis Street', 'Putho Tuntungin', 'Los Baños', 'Laguna', '4030', '1', '2026-10-17 09:24:27', '2026-10-17 09:24:27'),
('24', '41', 'Nike Main Logistics Hub', 'Nike Official Store Warehouse', '09179994455', 'Bonifacio High Street Central, 7th Ave', 'Fort Bonifacio', 'Taguig City', 'Metro Manila', '1634', '1', '2026-10-18 03:25:51', '2026-10-18 03:25:51'),
('25', '42', 'Logitech Main Logistics Hub', 'Logitech Official Store Warehouse', '09176662233', 'Cyberzone, SM Megamall, EDSA', 'Wack-Wack Greenhills', 'Mandaluyong City', 'Metro Manila', '1550', '1', '2026-10-18 05:23:12', '2026-10-18 05:23:12'),
('26', '47', 'Gaabor Main Logistics Hub', 'Gaabor Store Warehouse', '09175551122', 'Unit 12, Ortigas Avenue', 'San Antonio', 'Pasig City', 'Metro Manila', '1605', '1', '2026-10-18 12:04:55', '2026-10-18 12:04:55'),
('27', '48', 'Home', 'buyer tester', '09656565656', 'sitio sampaguita', 'Bubukal', 'Santa Cruz', 'Laguna', '4010', '1', '2026-10-20 04:04:19', '2026-10-20 04:04:19');

-- --------------------------------------------------------
-- Table structure for table `admin_audit_logs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `admin_audit_logs`;
CREATE TABLE `admin_audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_audit_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `admin_audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `admin_audit_logs` (8 rows)
INSERT INTO `admin_audit_logs` (`id`, `user_id`, `action`, `description`, `created_at`, `updated_at`) VALUES
('1', '5', 'Product visibility changed', 'Ceramic Coffee Mug Saucer Set — inactive', '2026-10-12 06:26:11', '2026-10-12 06:26:11'),
('2', '5', 'Registration reviewed', 'sofie b — rejected', '2026-10-14 03:15:00', '2026-10-14 03:15:00'),
('3', '5', 'Account status changed', 'Mak Mac — active', '2026-10-14 03:18:07', '2026-10-14 03:18:07'),
('4', '5', 'Product visibility changed', 'Smart Fitness Tracker Watch IP68 — inactive', '2026-10-14 08:20:37', '2026-10-14 08:20:37'),
('5', '5', 'Registration reviewed', 'Nhieckaella Pajavera — approved', '2026-10-15 15:50:17', '2026-10-15 15:50:17'),
('6', '5', 'Registration reviewed', 'anya garcia — approved', '2026-10-17 10:05:54', '2026-10-17 10:05:54'),
('7', '5', 'Registration reviewed', 'Juan Miguel Jr. — approved', '2026-10-17 13:36:05', '2026-10-17 13:36:05'),
('8', '5', 'Account status changed', 'Noel Torres — suspended', '2026-10-19 07:02:46', '2026-10-19 07:02:46');

-- --------------------------------------------------------
-- Table structure for table `admin_disputes`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `admin_disputes`;
CREATE TABLE `admin_disputes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `seller_order_id` bigint unsigned DEFAULT NULL,
  `reporter_id` bigint unsigned DEFAULT NULL,
  `category` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requested_resolution` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `items` json DEFAULT NULL,
  `evidence` json DEFAULT NULL,
  `contact_phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_disputes_order_id_foreign` (`order_id`),
  KEY `admin_disputes_status_index` (`status`),
  KEY `admin_disputes_reporter_id_foreign` (`reporter_id`),
  KEY `admin_disputes_seller_order_id_status_index` (`seller_order_id`,`status`),
  CONSTRAINT `admin_disputes_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `admin_disputes_reporter_id_foreign` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `admin_disputes_seller_order_id_foreign` FOREIGN KEY (`seller_order_id`) REFERENCES `seller_orders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `app_notifications`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `app_notifications`;
CREATE TABLE `app_notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'order',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `archived_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `app_notifications_user_id_foreign` (`user_id`),
  CONSTRAINT `app_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `app_notifications` (118 rows)
INSERT INTO `app_notifications` (`id`, `user_id`, `type`, `title`, `message`, `link`, `is_read`, `archived_at`, `created_at`, `updated_at`) VALUES
('1', '10', 'order', 'Order Placed', 'Your order ORD-2026-53254 was successfully placed.', 'http://cartzyy.shop/dashboard?tab=orders', '1', NULL, '2026-10-10 06:21:01', '2026-10-10 06:21:08'),
('2', '10', 'payment', 'Payment Confirmed', 'Payment for order ORD-2026-53254 has been received.', 'http://cartzyy.shop/dashboard?tab=orders', '1', NULL, '2026-10-12 13:01:08', '2026-10-16 15:39:15'),
('3', '33', 'registration_rejected', 'Application Status Update', 'Your registration application was not approved. Reason: walang business permit', 'http://127.0.0.1:8000/login', '0', NULL, '2026-10-14 03:15:00', '2026-10-14 03:15:00'),
('4', '10', 'order', 'Order Placed', 'Your order ORD-2026-20969 was successfully placed.', 'http://cartzyy.shop/dashboard?tab=orders', '1', NULL, '2026-10-14 08:14:38', '2026-10-16 15:39:17'),
('5', '10', 'payment', 'Payment Confirmed', 'Payment for order ORD-2026-20969 has been received.', 'http://cartzyy.shop/dashboard?tab=orders', '1', NULL, '2026-10-14 08:14:46', '2026-10-16 15:39:19'),
('6', '13', 'product_update', 'Product visibility updated by admin', 'Smart Fitness Tracker Watch IP68 was suspended by admin.', 'http://cartzyy.shop/seller/products?tab=archived&search=Smart%20Fitness%20Tracker%20Watch%20IP68', '0', NULL, '2026-10-14 08:20:37', '2026-10-14 08:20:37'),
('7', '38', 'registration_approved', 'Registration & Identity Verified', 'Congratulations! Your account registration and documents have been approved by the administration team.', 'http://127.0.0.1:8000/login', '1', NULL, '2026-10-15 15:50:17', '2026-10-16 08:02:29'),
('8', '6', 'product_update', 'Product added', 'thd was added to your catalog.', 'http://127.0.0.1:8000/seller/products?search=thd', '1', NULL, '2026-10-15 15:52:59', '2026-10-15 15:53:18'),
('9', '6', 'product_update', 'Product visibility updated', 'thd was archived.', 'http://127.0.0.1:8000/seller/products?tab=archived&search=thd', '1', NULL, '2026-10-15 15:53:25', '2026-10-15 17:34:18'),
('10', '6', 'product_update', 'Product visibility updated', 'thd is now active.', 'http://127.0.0.1:8000/seller/products?tab=active&search=thd', '0', NULL, '2026-10-15 16:15:06', '2026-10-15 16:15:06'),
('11', '6', 'product_update', 'Product visibility updated', 'thd was archived.', 'http://127.0.0.1:8000/seller/products?tab=archived&search=thd', '1', NULL, '2026-10-15 16:16:16', '2026-10-15 17:33:38'),
('20', '6', 'product_update', 'Product added', 'adidas was added to your catalog.', 'http://127.0.0.1:8000/seller/products?search=adidas', '0', NULL, '2026-10-16 01:06:04', '2026-10-16 01:06:04'),
('21', '6', 'product_update', 'Product updated', 'adidas details were updated.', 'http://127.0.0.1:8000/seller/products/13/edit', '1', NULL, '2026-10-16 01:07:17', '2026-10-16 01:07:26'),
('22', '6', 'product_update', 'Product updated', 'adidas details were updated.', 'http://127.0.0.1:8000/seller/products/13/edit', '0', NULL, '2026-10-16 01:09:27', '2026-10-16 01:09:27'),
('23', '6', 'product_update', 'Product updated', 'adidas details were updated.', 'http://127.0.0.1:8000/seller/products/13/edit', '0', NULL, '2026-10-16 01:09:35', '2026-10-16 01:09:35'),
('24', '38', 'product_update', 'Product added', 'Rice Cooker was added to your catalog.', 'http://cartzyy.shop/seller/products?search=Rice%20Cooker', '1', NULL, '2026-10-16 08:22:53', '2026-10-16 13:15:58'),
('25', '9', 'order', 'Order Placed', 'Your order ORD-2026-87180 was successfully placed.', 'http://cartzyy.shop/dashboard?tab=orders', '0', NULL, '2026-10-17 09:26:28', '2026-10-17 09:26:28'),
('26', '36', 'registration_approved', 'Registration & Identity Verified', 'Congratulations! Your account registration and documents have been approved by the administration team.', 'http://cartzyy.shop/login', '1', NULL, '2026-10-17 10:05:54', '2026-10-17 10:07:01'),
('27', '13', 'cancellation_requested', 'Cancellation request', 'The buyer requested cancellation of ORD-2026-87180. Reason: I placed a duplicate order', 'http://cartzyy.shop/seller/orders?status=cancellation_requested', '0', NULL, '2026-10-17 10:48:33', '2026-10-17 10:48:33'),
('28', '9', 'order', 'Order Placed', 'Your order ORD-2026-36530 was successfully placed.', 'http://cartzyy.shop/dashboard?tab=orders', '0', NULL, '2026-10-17 10:50:46', '2026-10-17 10:50:46'),
('29', '13', 'cancellation_requested', 'Cancellation request', 'The buyer requested cancellation of ORD-2026-36530. Reason: I placed a duplicate order', 'https://cartzyy.shop/seller/orders?status=cancellation_requested', '0', NULL, '2026-10-17 11:12:04', '2026-10-17 11:12:04'),
('30', '36', 'product_update', 'Product added', 'Hehehhehe was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Hehehhehe', '0', NULL, '2026-10-17 11:20:41', '2026-10-17 11:20:41'),
('31', '36', 'product_update', 'Product added', 'Bags was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Bags', '0', NULL, '2026-10-17 12:57:36', '2026-10-17 12:57:36'),
('32', '36', 'product_update', 'Product updated', 'Kitchen and DInigs details were updated.', 'https://cartzyy.shop/seller/products/17/edit', '0', NULL, '2026-10-17 12:59:21', '2026-10-17 12:59:21'),
('33', '36', 'product_update', 'Product updated', 'Bags details were updated.', 'https://cartzyy.shop/seller/products/17/edit', '0', NULL, '2026-10-17 12:59:37', '2026-10-17 12:59:37'),
('34', '36', 'product_update', 'Product updated', 'Bags details were updated.', 'https://cartzyy.shop/seller/products/16/edit', '0', NULL, '2026-10-17 12:59:59', '2026-10-17 12:59:59'),
('35', '36', 'product_update', 'Product updated', 'Bags details were updated.', 'https://cartzyy.shop/seller/products/16/edit', '0', NULL, '2026-10-17 13:05:26', '2026-10-17 13:05:26'),
('36', '36', 'product_update', 'Product added', 'Buri Placemat Set was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Buri%20Placemat%20Set', '0', NULL, '2026-10-17 13:21:13', '2026-10-17 13:21:13'),
('37', '36', 'product_update', 'Product updated', 'tote details were updated.', 'https://cartzyy.shop/seller/products/18/edit', '0', NULL, '2026-10-17 13:24:56', '2026-10-17 13:24:56'),
('38', '36', 'product_update', 'Product updated', '120 details were updated.', 'https://cartzyy.shop/seller/products/18/edit', '0', NULL, '2026-10-17 13:25:29', '2026-10-17 13:25:29'),
('39', '36', 'product_update', 'Product updated', '121 details were updated.', 'https://cartzyy.shop/seller/products/18/edit', '0', NULL, '2026-10-17 13:25:35', '2026-10-17 13:25:35'),
('40', '36', 'product_update', 'Product updated', 'Buri Placemat Set details were updated.', 'https://cartzyy.shop/seller/products/18/edit', '0', NULL, '2026-10-17 13:26:35', '2026-10-17 13:26:35'),
('41', '36', 'product_update', 'Product updated', 'Buri Placemat Set details were updated.', 'https://cartzyy.shop/seller/products/18/edit', '0', NULL, '2026-10-17 13:33:12', '2026-10-17 13:33:12'),
('42', '36', 'product_update', 'Product updated', 'Buri Placemat Set details were updated.', 'https://cartzyy.shop/seller/products/18/edit', '0', NULL, '2026-10-17 13:34:34', '2026-10-17 13:34:34'),
('43', '39', 'registration_approved', 'Registration & Identity Verified', 'Congratulations! Your account registration and documents have been approved by the administration team.', 'https://cartzyy.shop/login', '0', NULL, '2026-10-17 13:36:05', '2026-10-17 13:36:05'),
('44', '36', 'product_update', 'Product added', 'Buri Coin Purse was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Buri%20Coin%20Purse', '0', NULL, '2026-10-17 13:47:54', '2026-10-17 13:47:54'),
('45', '36', 'product_update', 'Product updated', 'Buri Coin Purse details were updated.', 'https://cartzyy.shop/seller/products/19/edit', '0', NULL, '2026-10-17 13:50:58', '2026-10-17 13:50:58'),
('46', '36', 'product_update', 'Product updated', 'Buri Coin Purse details were updated.', 'https://cartzyy.shop/seller/products/19/edit', '0', NULL, '2026-10-17 13:51:54', '2026-10-17 13:51:54'),
('47', '36', 'product_update', 'Stock updated', 'Bags stock quantities were updated.', 'https://cartzyy.shop/seller/inventory?search=Bags', '0', NULL, '2026-10-17 14:01:41', '2026-10-17 14:01:41'),
('48', '28', 'product_update', 'Product added', 'Happy skin Lip Jelly was added to your catalog.', 'http://127.0.0.1:8000/seller/products?search=Happy%20skin%20Lip%20Jelly', '1', NULL, '2026-10-18 03:04:16', '2026-10-18 03:06:02'),
('49', '28', 'product_update', 'Product added', 'Happy skin Lip Jelly was added to your catalog.', 'http://127.0.0.1:8000/seller/products?search=Happy%20skin%20Lip%20Jelly', '0', NULL, '2026-10-18 03:15:06', '2026-10-18 03:15:06'),
('50', '41', 'product_update', 'Product added', 'Tatum 5 PF was added to your catalog.', 'http://127.0.0.1:8000/seller/products?search=Tatum%205%20PF', '1', NULL, '2026-10-18 03:34:06', '2026-10-18 03:39:08'),
('51', '41', 'product_update', 'Product updated', 'Tatum 5 PF details were updated.', 'http://127.0.0.1:8000/seller/products/22/edit', '0', NULL, '2026-10-18 04:36:37', '2026-10-18 04:36:37'),
('52', '41', 'product_update', 'Product added', 'Air Jordan 1 Low SE was added to your catalog.', 'http://127.0.0.1:8000/seller/products?search=Air%20Jordan%201%20Low%20SE', '0', NULL, '2026-10-18 05:02:33', '2026-10-18 05:02:33'),
('53', '41', 'product_update', 'Product updated', 'Air Jordan 1 Low SE details were updated.', 'http://127.0.0.1:8000/seller/products/23/edit', '0', NULL, '2026-10-18 05:03:38', '2026-10-18 05:03:38'),
('54', '41', 'product_update', 'Stock updated', 'Tatum 5 PF stock quantities were updated.', 'http://127.0.0.1:8000/seller/inventory?search=Tatum%205%20PF', '1', NULL, '2026-10-18 05:05:21', '2026-10-18 05:05:33'),
('55', '41', 'product_update', 'Product updated', 'Air Jordan 1 Low SE details were updated.', 'http://127.0.0.1:8000/seller/products/23/edit', '0', NULL, '2026-10-18 05:15:43', '2026-10-18 05:15:43'),
('56', '41', 'product_update', 'Product added', 'Nike Zoom Skylon 11 was added to your catalog.', 'http://127.0.0.1:8000/seller/products?search=Nike%20Zoom%20Skylon%2011', '0', NULL, '2026-10-18 05:19:52', '2026-10-18 05:19:52'),
('57', '37', 'product_update', 'Product added', 'HEATHER 3S CALI TEE was added to your catalog.', 'http://127.0.0.1:8000/seller/products?search=HEATHER%203S%20CALI%20TEE', '0', NULL, '2026-10-18 05:42:18', '2026-10-18 05:42:18'),
('58', '37', 'product_update', 'Product added', 'ADIDAS PIXAR TOY STORY SAMBA JANE INFANT SHOES was added to your catalog.', 'http://127.0.0.1:8000/seller/products?search=ADIDAS%20PIXAR%20TOY%20STORY%20SAMBA%20JANE%20INFANT%20SHOES', '0', NULL, '2026-10-18 06:17:30', '2026-10-18 06:17:30'),
('59', '37', 'product_update', 'Stock updated', 'ADIDAS PIXAR TOY STORY SAMBA JANE INFANT SHOES stock quantities were updated.', 'http://127.0.0.1:8000/seller/inventory?search=ADIDAS%20PIXAR%20TOY%20STORY%20SAMBA%20JANE%20INFANT%20SHOES', '0', NULL, '2026-10-18 06:18:02', '2026-10-18 06:18:02'),
('60', '42', 'product_update', 'Product added', 'Signature Comfort Plus M850 L was added to your catalog.', 'http://127.0.0.1:8000/seller/products?search=Signature%20Comfort%20Plus%20M850%20L', '0', NULL, '2026-10-18 07:30:18', '2026-10-18 07:30:18'),
('61', '42', 'product_update', 'Product added', 'MX Keys S was added to your catalog.', 'http://127.0.0.1:8000/seller/products?search=MX%20Keys%20S', '0', NULL, '2026-10-18 08:14:24', '2026-10-18 08:14:24'),
('62', '6', 'pickup_accepted', 'Rider Assigned for Pickup', 'Courier rider John Mark has accepted your pickup request (6 parcels). ETA: Within 30 minutes.', 'https://cartzyy.shop/seller/courier', '0', NULL, '2026-10-18 08:16:13', '2026-10-18 08:16:13'),
('63', '6', 'order_delivered', 'Order Delivered (ORD-MTR-101)', 'Parcel CTZY-RD-884901 was delivered to Ma. Cristina Alcantara by rider John Mark.', 'https://cartzyy.shop/seller/orders/9/waybill', '0', NULL, '2026-10-18 08:16:29', '2026-10-18 08:16:29'),
('64', '1', 'order_delivered', 'Package Delivered! (ORD-MTR-101)', 'Your Cartzy order ORD-MTR-101 was successfully delivered by rider John Mark. Thank you!', 'https://cartzyy.shop/dashboard?tab=orders', '0', NULL, '2026-10-18 08:16:29', '2026-10-18 08:16:29'),
('65', '42', 'product_update', 'Product added', 'Astro A10 was added to your catalog.', 'http://127.0.0.1:8000/seller/products?search=Astro%20A10', '0', NULL, '2026-10-18 08:18:03', '2026-10-18 08:18:03'),
('66', '42', 'product_update', 'Product added', 'Logitech MX Master 3S Wireless Mouse was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Logitech%20MX%20Master%203S%20Wireless%20Mouse', '0', NULL, '2026-10-18 11:44:08', '2026-10-18 11:44:08'),
('67', '42', 'product_update', 'Stock updated', 'Logitech MX Master 3S Wireless Mouse stock quantities were updated.', 'https://cartzyy.shop/seller/inventory?search=Logitech%20MX%20Master%203S%20Wireless%20Mouse', '0', NULL, '2026-10-18 11:44:31', '2026-10-18 11:44:31'),
('68', '42', 'product_update', 'Product added', 'Logitech MX Keys S Wireless Keyboard  Copy was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Logitech%20MX%20Keys%20S%20Wireless%20Keyboard%20%20Copy', '0', NULL, '2026-10-18 11:47:56', '2026-10-18 11:47:56'),
('69', '42', 'product_update', 'Product added', 'Logitech BRIO 4K Ultra HD Webcam  Copy was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Logitech%20BRIO%204K%20Ultra%20HD%20Webcam%20%20Copy', '0', NULL, '2026-10-18 11:49:23', '2026-10-18 11:49:23'),
('70', '42', 'product_update', 'Product added', 'Logitech G PRO X SUPERLIGHT 2 Wireless Gaming Mouse  Copy was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Logitech%20G%20PRO%20X%20SUPERLIGHT%202%20Wireless%20Gaming%20Mouse%20%20Copy', '0', NULL, '2026-10-18 11:51:00', '2026-10-18 11:51:00'),
('71', '42', 'product_update', 'Product added', 'Logitech Zone Vibe 100 Wireless Headphones  Copy was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Logitech%20Zone%20Vibe%20100%20Wireless%20Headphones%20%20Copy', '0', NULL, '2026-10-18 11:52:20', '2026-10-18 11:52:20'),
('72', '42', 'product_update', 'Product added', 'Logitech C920s PRO HD was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Logitech%20C920s%20PRO%20HD', '0', NULL, '2026-10-18 11:53:36', '2026-10-18 11:53:36'),
('73', '42', 'product_update', 'Product added', 'Logitech Lift Vertical Ergonomic Mouse was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Logitech%20Lift%20Vertical%20Ergonomic%20Mouse', '0', NULL, '2026-10-18 11:54:48', '2026-10-18 11:54:48'),
('74', '42', 'product_update', 'Product added', 'Logitech Pebble Mouse 2 M350s was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Logitech%20Pebble%20Mouse%202%20M350s', '0', NULL, '2026-10-18 11:55:47', '2026-10-18 11:55:47'),
('75', '42', 'product_update', 'Product added', 'Logitech Pebble Keys 2 K380s Wireless Keyboard  Copy was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Logitech%20Pebble%20Keys%202%20K380s%20Wireless%20Keyboard%20%20Copy', '0', NULL, '2026-10-18 11:58:09', '2026-10-18 11:58:09'),
('76', '41', 'product_update', 'Product added', 'Ja 4 \'Heat Check\' EP Basketball Shoes was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Ja%204%20%27Heat%20Check%27%20EP%20Basketball%20Shoes', '0', NULL, '2026-10-18 11:58:36', '2026-10-18 11:58:36'),
('77', '42', 'product_update', 'Product added', 'Logitech Z407 Bluetooth Computer Speakers with Subwoofer  Copy product name was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Logitech%20Z407%20Bluetooth%20Computer%20Speakers%20with%20Subwoofer%20%20Copy%20product%20name', '0', NULL, '2026-10-18 12:00:15', '2026-10-18 12:00:15'),
('78', '42', 'product_update', 'Product added', 'Logitech Rally Bar Mini All-in-One Video Conferencing System was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Logitech%20Rally%20Bar%20Mini%20All-in-One%20Video%20Conferencing%20System', '0', NULL, '2026-10-18 12:01:57', '2026-10-18 12:01:57'),
('79', '42', 'product_update', 'Product added', 'Logitech Litra Glow Premium Streaming Light was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Logitech%20Litra%20Glow%20Premium%20Streaming%20Light', '0', NULL, '2026-10-18 12:03:57', '2026-10-18 12:03:57'),
('80', '42', 'product_update', 'Product added', 'Logitech G923 TRUEFORCE Racing Wheel and Pedals was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Logitech%20G923%20TRUEFORCE%20Racing%20Wheel%20and%20Pedals', '0', NULL, '2026-10-18 12:05:43', '2026-10-18 12:05:43'),
('81', '42', 'product_update', 'Product added', 'Logitech Mevo Start Wireless Live Streaming Camera was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Logitech%20Mevo%20Start%20Wireless%20Live%20Streaming%20Camera', '0', NULL, '2026-10-18 12:06:57', '2026-10-18 12:06:57'),
('82', '42', 'product_update', 'Product added', 'Logitech G Yeti GX Gaming Microphone was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Logitech%20G%20Yeti%20GX%20Gaming%20Microphone', '0', NULL, '2026-10-18 12:08:19', '2026-10-18 12:08:19'),
('83', '42', 'product_update', 'Product added', 'Logitech StreamCam Full HD Webcam was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Logitech%20StreamCam%20Full%20HD%20Webcam', '0', NULL, '2026-10-18 12:09:56', '2026-10-18 12:09:56'),
('84', '42', 'product_update', 'Product added', 'Logitech Zone Wireless 2 Business Headset was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Logitech%20Zone%20Wireless%202%20Business%20Headset', '0', NULL, '2026-10-18 12:11:07', '2026-10-18 12:11:07'),
('85', '41', 'product_update', 'Product added', 'Nike V5 RNR Premium was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Nike%20V5%20RNR%20Premium', '0', NULL, '2026-10-18 12:12:23', '2026-10-18 12:12:23'),
('86', '42', 'product_update', 'Product added', 'Logitech G ASTRO A50 X LIGHTSPEED Gaming Headset was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Logitech%20G%20ASTRO%20A50%20X%20LIGHTSPEED%20Gaming%20Headset', '0', NULL, '2026-10-18 12:12:24', '2026-10-18 12:12:24'),
('87', '41', 'product_update', 'Stock updated', 'Nike V5 RNR Premium stock quantities were updated.', 'https://cartzyy.shop/seller/inventory?search=Nike%20V5%20RNR%20Premium', '0', NULL, '2026-10-18 12:13:28', '2026-10-18 12:13:28'),
('88', '42', 'product_update', 'Product added', 'Logitech Zone True Wireless Earbuds was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Logitech%20Zone%20True%20Wireless%20Earbuds', '0', NULL, '2026-10-18 12:14:18', '2026-10-18 12:14:18'),
('89', '42', 'product_update', 'Product added', 'Logitech Rally Mic Pod Conference Microphone was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Logitech%20Rally%20Mic%20Pod%20Conference%20Microphone', '0', NULL, '2026-10-18 12:15:57', '2026-10-18 12:15:57'),
('90', '41', 'product_update', 'Product added', 'Nike Calm 2.0 was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Nike%20Calm%202.0', '0', NULL, '2026-10-18 12:28:17', '2026-10-18 12:28:17'),
('91', '41', 'product_update', 'Stock updated', 'Nike Calm 2.0 stock quantities were updated.', 'https://cartzyy.shop/seller/inventory?search=Nike%20Calm%202.0', '0', NULL, '2026-10-18 12:29:03', '2026-10-18 12:29:03'),
('92', '41', 'product_update', 'Product added', 'Nike Air Superfly was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Nike%20Air%20Superfly', '0', NULL, '2026-10-18 12:34:52', '2026-10-18 12:34:52'),
('93', '41', 'product_update', 'Product added', 'Nike Dri-FIT Form was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Nike%20Dri-FIT%20Form', '0', NULL, '2026-10-18 13:14:18', '2026-10-18 13:14:18'),
('94', '41', 'product_update', 'Product added', 'Women\'s Slim Long-Sleeve Sheer Top was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Women%27s%20Slim%20Long-Sleeve%20Sheer%20Top', '0', NULL, '2026-10-18 13:21:48', '2026-10-18 13:21:48'),
('95', '41', 'product_update', 'Product added', 'Women\'s Dri-FIT Pleated Tennis Skirt was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Women%27s%20Dri-FIT%20Pleated%20Tennis%20Skirt', '0', NULL, '2026-10-18 13:29:16', '2026-10-18 13:29:16'),
('96', '41', 'product_update', 'Product updated', 'Women\'s Dri-FIT Pleated Tennis Skirt details were updated.', 'https://cartzyy.shop/seller/products/56/edit', '0', NULL, '2026-10-18 13:30:03', '2026-10-18 13:30:03'),
('97', '41', 'product_update', 'Product updated', 'Women\'s Dri-FIT Pleated Tennis Skirt details were updated.', 'https://cartzyy.shop/seller/products/56/edit', '0', NULL, '2026-10-18 13:30:08', '2026-10-18 13:30:08'),
('98', '41', 'product_update', 'Product added', 'Air Jordan 1 Mid was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Air%20Jordan%201%20Mid', '0', NULL, '2026-10-18 13:36:50', '2026-10-18 13:36:50'),
('99', '41', 'product_update', 'Product updated', 'Air Jordan 1 Mid details were updated.', 'https://cartzyy.shop/seller/products/57/edit', '0', NULL, '2026-10-18 13:37:54', '2026-10-18 13:37:54'),
('100', '41', 'product_update', 'Stock updated', 'Air Jordan 1 Mid stock quantities were updated.', 'https://cartzyy.shop/seller/inventory?search=Air%20Jordan%201%20Mid', '0', NULL, '2026-10-18 13:38:31', '2026-10-18 13:38:31'),
('105', '41', 'product_update', 'Product updated', 'Women\'s Dri-FIT Pleated Tennis Skirt details were updated.', 'https://cartzyy.shop/seller/products/56/edit', '0', NULL, '2026-10-19 02:38:31', '2026-10-19 02:38:31'),
('106', '42', 'product_update', 'Stock updated', 'Logitech Mevo Start Wireless Live Streaming Camera stock quantities were updated.', 'http://127.0.0.1:8000/seller/inventory?search=Logitech%20Mevo%20Start%20Wireless%20Live%20Streaming%20Camera', '0', NULL, '2026-10-19 02:41:07', '2026-10-19 02:41:07'),
('107', '42', 'product_update', 'Product updated', 'Logitech Zone True Wireless Earbuds details were updated.', 'http://127.0.0.1:8000/seller/products/50/edit', '0', NULL, '2026-10-19 02:42:50', '2026-10-19 02:42:50'),
('108', '42', 'product_update', 'Stock updated', 'Logitech Rally Mic Pod Conference Microphone stock quantities were updated.', 'http://127.0.0.1:8000/seller/inventory?search=Logitech%20Rally%20Mic%20Pod%20Conference%20Microphone', '0', NULL, '2026-10-19 02:43:20', '2026-10-19 02:43:20'),
('109', '41', 'product_update', 'Product updated', 'Women\'s Dri-FIT Pleated Tennis Skirt details were updated.', 'https://cartzyy.shop/seller/products/56/edit', '0', NULL, '2026-10-19 06:33:37', '2026-10-19 06:33:37'),
('110', '41', 'product_update', 'Stock updated', 'Women\'s Slim Long-Sleeve Sheer Top stock quantities were updated.', 'https://cartzyy.shop/seller/inventory?search=Women%27s%20Slim%20Long-Sleeve%20Sheer%20Top', '0', NULL, '2026-10-19 06:35:20', '2026-10-19 06:35:20'),
('111', '41', 'product_update', 'Product updated', 'Air Jordan 1 Mid details were updated.', 'https://cartzyy.shop/seller/products/57/edit', '0', NULL, '2026-10-19 06:37:03', '2026-10-19 06:37:03'),
('112', '10', 'order', 'Order Placed', 'Your order ORD-2026-42864 was successfully placed.', 'https://cartzyy.shop/dashboard?tab=orders', '0', NULL, '2026-10-19 06:52:55', '2026-10-19 06:52:55'),
('113', '42', 'product_update', 'Stock updated', 'Logitech Rally Mic Pod Conference Microphone stock quantities were updated.', 'https://cartzyy.shop/seller/inventory?search=Logitech%20Rally%20Mic%20Pod%20Conference%20Microphone', '0', NULL, '2026-10-19 07:14:02', '2026-10-19 07:14:02'),
('114', '42', 'product_update', 'Stock updated', 'Logitech Rally Mic Pod Conference Microphone stock quantities were updated.', 'https://cartzyy.shop/seller/inventory?search=Logitech%20Rally%20Mic%20Pod%20Conference%20Microphone', '0', NULL, '2026-10-19 07:14:55', '2026-10-19 07:14:55'),
('115', '10', 'order', 'Order Placed', 'Your order ORD-2026-96349 was successfully placed.', 'https://cartzyy.shop/dashboard?tab=orders', '0', NULL, '2026-10-19 07:20:20', '2026-10-19 07:20:20'),
('116', '42', 'product_update', 'Stock updated', 'Logitech Rally Mic Pod Conference Microphone stock quantities were updated.', 'https://cartzyy.shop/seller/inventory?search=Logitech%20Rally%20Mic%20Pod%20Conference%20Microphone', '0', NULL, '2026-10-19 07:27:49', '2026-10-19 07:27:49'),
('117', '10', 'order', 'Order Placed', 'Your order ORD-2026-37747 was successfully placed.', 'https://cartzyy.shop/dashboard?tab=orders', '0', NULL, '2026-10-19 07:30:00', '2026-10-19 07:30:00'),
('118', '42', 'order_delivered', 'Order Delivered (ORD-2026-96349)', 'Parcel CTZ-IS9QZIXYUJ was delivered to Jessica Pambago by rider Arnel Gomez.', 'https://cartzyy.shop/seller/orders/18/waybill', '0', NULL, '2026-10-19 07:32:12', '2026-10-19 07:32:12'),
('119', '10', 'order_delivered', 'Package Delivered! (ORD-2026-96349)', 'Your Cartzy order ORD-2026-96349 was successfully delivered by rider Arnel Gomez. Thank you!', 'https://cartzyy.shop/dashboard?tab=orders', '1', NULL, '2026-10-19 07:32:12', '2026-10-19 07:34:24'),
('120', '42', 'product_update', 'Stock updated', 'Logitech Rally Mic Pod Conference Microphone stock quantities were updated.', 'https://cartzyy.shop/seller/inventory?search=Logitech%20Rally%20Mic%20Pod%20Conference%20Microphone', '0', NULL, '2026-10-19 07:36:03', '2026-10-19 07:36:03'),
('121', '42', 'product_update', 'Stock updated', 'Logitech Rally Mic Pod Conference Microphone stock quantities were updated.', 'https://cartzyy.shop/seller/inventory?search=Logitech%20Rally%20Mic%20Pod%20Conference%20Microphone', '0', NULL, '2026-10-19 08:01:26', '2026-10-19 08:01:26'),
('122', '48', 'order', 'Order Placed', 'Your order ORD-2026-42773 was successfully placed.', 'https://cartzyy.shop/dashboard?tab=orders', '0', NULL, '2026-10-20 04:05:09', '2026-10-20 04:05:09'),
('123', '10', 'order', 'Order Placed', 'Your order ORD-2026-52176 was successfully placed.', 'https://cartzyy.shop/dashboard?tab=orders', '0', NULL, '2026-10-20 04:18:37', '2026-10-20 04:18:37'),
('124', '9', 'order', 'Order Placed', 'Your order ORD-2026-61636 was successfully placed.', 'https://cartzyy.shop/dashboard?tab=orders', '0', NULL, '2026-10-20 04:19:24', '2026-10-20 04:19:24'),
('125', '41', 'product_update', 'Product added', 'Nike Air Force 1 \'07 Men\'s Shoe was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Nike%20Air%20Force%201%20%2707%20Men%27s%20Shoe', '0', NULL, '2026-10-20 04:28:23', '2026-10-20 04:28:23'),
('126', '41', 'product_update', 'Product added', 'Nike Dunk Low Retro was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Nike%20Dunk%20Low%20Retro', '0', NULL, '2026-10-20 04:40:11', '2026-10-20 04:40:11'),
('127', '41', 'product_update', 'Product updated', 'Nike Air Force 1 \'07 Men\'s Shoe details were updated.', 'https://cartzyy.shop/seller/products/58/edit', '0', NULL, '2026-10-20 04:41:06', '2026-10-20 04:41:06'),
('128', '41', 'product_update', 'Product updated', 'Nike Air Force 1 \'07 Men\'s Shoe details were updated.', 'https://cartzyy.shop/seller/products/58/edit', '0', NULL, '2026-10-20 04:41:42', '2026-10-20 04:41:42'),
('129', '41', 'product_update', 'Product added', 'Nike Sportswear Club Fleece Hoodie was added to your catalog.', 'https://cartzyy.shop/seller/products?search=Nike%20Sportswear%20Club%20Fleece%20Hoodie', '0', NULL, '2026-10-20 04:49:50', '2026-10-20 04:49:50'),
('130', '41', 'cancellation_requested', 'Cancellation request', 'The buyer requested cancellation of ORD-2026-52176. Reason: Other reason: ayaw kona', 'https://cartzyy.shop/seller/orders?status=cancellation_requested', '0', NULL, '2026-10-20 04:57:43', '2026-10-20 04:57:43');

-- --------------------------------------------------------
-- Table structure for table `buyers`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `buyers`;
CREATE TABLE `buyers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_initial` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sex` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `age` tinyint unsigned DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_address_id` bigint unsigned DEFAULT NULL,
  `id_photo` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_status` enum('unverified','pending','verified','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unverified',
  `id_rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `id_verified_at` timestamp NULL DEFAULT NULL,
  `loyalty_points` int unsigned NOT NULL DEFAULT '0',
  `membership_tier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Regular',
  `status` enum('active','suspended','deactivated') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `buyers_user_id_unique` (`user_id`),
  KEY `buyers_default_address_id_foreign` (`default_address_id`),
  CONSTRAINT `buyers_default_address_id_foreign` FOREIGN KEY (`default_address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `buyers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `buyers` (14 rows)
INSERT INTO `buyers` (`id`, `user_id`, `name`, `middle_initial`, `sex`, `birthday`, `age`, `phone`, `default_address_id`, `id_photo`, `id_type`, `id_number`, `id_status`, `id_rejection_reason`, `id_verified_at`, `loyalty_points`, `membership_tier`, `status`, `notes`, `created_at`, `updated_at`) VALUES
('1', '1', 'jess Pambago', NULL, 'Female', '2005-12-05', '20', '09773587409', '1', NULL, NULL, NULL, 'unverified', NULL, NULL, '0', 'Regular', 'active', NULL, '2026-09-06 10:07:45', '2026-09-19 09:39:48'),
('2', '2', 'Jessie Bajamundi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, '0', 'Regular', 'active', NULL, '2026-09-19 09:38:11', '2026-10-10 05:27:55'),
('3', '3', 'Leonardo, Tiffany Joy O.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, '0', 'Regular', 'active', NULL, '2026-09-19 10:08:30', '2026-10-10 05:27:55'),
('4', '4', 'Pajavera, Nhieckaella Ashley R.', NULL, NULL, '2005-11-14', '20', '09152608445', '2', NULL, NULL, NULL, 'unverified', NULL, NULL, '0', 'Regular', 'active', NULL, '2026-09-19 10:08:50', '2026-10-10 05:27:55'),
('5', '8', 'Maria Dela Cruz', NULL, NULL, NULL, NULL, '09175556666', NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, '0', 'Regular', 'active', NULL, '2026-09-19 10:14:46', '2026-09-19 10:14:46'),
('6', '9', 'Jan Reyben Del Mundo', NULL, 'Male', '2005-09-20', '21', NULL, NULL, 'id_documents/H7b1P328TEM0waoFW8KJXDSrxvVV6eVRuP5oT9m4.png', 'Philippine National ID', '1234-5678-9101-1213', 'pending', NULL, NULL, '0', 'Regular', 'active', NULL, '2026-09-19 10:22:32', '2026-10-17 08:57:10'),
('7', '10', 'Jessica Pambago', NULL, NULL, NULL, NULL, NULL, '5', NULL, 'National ID', '1234-5678-9012', 'verified', NULL, '2026-10-10 05:54:17', '0', 'Regular', 'active', NULL, '2026-09-23 02:11:29', '2026-10-10 06:04:25'),
('10', '34', 'Hatdog Itlog', NULL, NULL, '2008-10-14', '18', '09566525116', NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, '0', 'Regular', 'active', NULL, '2026-10-14 03:37:30', '2026-10-14 03:37:30'),
('11', '35', 'Ella Rivera', NULL, NULL, '2005-11-14', '20', '09152608445', NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, '0', 'Regular', 'active', NULL, '2026-10-14 06:14:42', '2026-10-14 06:14:42'),
('12', '39', 'Juan Miguel Jr.', NULL, 'Male', '2006-09-18', '20', '09171233131', NULL, 'id_documents/NJ7KpxgXF7DTqB9CKmCgdwk4seegM4sk1pakwqzX.png', 'Philippine National ID', '12321312321', 'verified', NULL, '2026-10-17 13:36:05', '0', 'Regular', 'active', NULL, '2026-10-16 07:42:07', '2026-10-17 13:36:05'),
('13', '29', 'Mark_1123 Kram*sdas', NULL, NULL, '2017-01-01', '9', '09532584860', NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, '0', 'Regular', 'active', NULL, '2026-10-18 05:23:11', '2026-10-18 05:23:11'),
('14', '30', 'Mak Mac', NULL, NULL, '2026-01-01', '0', '09532584', NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, '0', 'Regular', 'active', NULL, '2026-10-18 05:23:11', '2026-10-18 05:23:11'),
('15', '45', 'Jonnel Araneta', NULL, NULL, '2005-11-14', '20', '09137856768', NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, '0', 'Regular', 'active', NULL, '2026-10-18 08:05:15', '2026-10-18 08:05:15'),
('16', '48', 'buyer tester', NULL, NULL, '2004-07-20', '22', '09656565656', NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, '0', 'Regular', 'active', NULL, '2026-10-20 04:00:05', '2026-10-20 04:00:05');

-- --------------------------------------------------------
-- Table structure for table `cache`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `cache_locks`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `cart_items`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `cart_items`;
CREATE TABLE `cart_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cart_id` bigint unsigned NOT NULL,
  `product_variant_id` bigint unsigned DEFAULT NULL,
  `quantity` int unsigned NOT NULL DEFAULT '1',
  `selected` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `cart_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `snapshot` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cart_items_cart_id_product_variant_id_unique` (`cart_id`,`product_variant_id`),
  UNIQUE KEY `cart_items_cart_id_cart_key_unique` (`cart_id`,`cart_key`),
  KEY `cart_items_product_variant_id_foreign` (`product_variant_id`),
  CONSTRAINT `cart_items_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `cart_items` (15 rows)
INSERT INTO `cart_items` (`id`, `cart_id`, `product_variant_id`, `quantity`, `selected`, `created_at`, `updated_at`, `cart_key`, `snapshot`) VALUES
('6', '7', NULL, '1', '1', '2026-10-14 05:55:24', '2026-10-14 05:55:24', '17_7c8f72d7f1be80bc052f6e34d8c94556', '{\"id\": \"17\", \"name\": \"Luxury Eau De Parfum 100ml Unisex\", \"image\": \"https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?w=500&h=500&fit=crop&q=80\", \"price\": 1120, \"cart_key\": \"17_7c8f72d7f1be80bc052f6e34d8c94556\", \"quantity\": 1, \"variation\": \"100ml\", \"variant_id\": null}'),
('7', '7', NULL, '1', '1', '2026-10-14 05:56:31', '2026-10-14 05:56:31', '22_a07c8d0cdcc1e4012d1b97b218fac0ba', '{\"id\": \"22\", \"name\": \"Curve Plus-Size Flattering Wrap Dress\", \"image\": \"https://images.unsplash.com/photo-1574634534894-89d7576c8259?w=500&h=500&fit=crop&q=80\", \"price\": 650, \"cart_key\": \"22_a07c8d0cdcc1e4012d1b97b218fac0ba\", \"quantity\": 1, \"variation\": \"2XL / Plum\", \"variant_id\": null}'),
('9', '10', NULL, '1', '1', '2026-10-14 11:04:23', '2026-10-14 12:00:44', '2_e90dfb84e30edf611e326eeb04d680de', '{\"id\": \"2\", \"name\": \"Minimalist Chronograph Watch\", \"image\": \"https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500&h=500&fit=crop&q=80\", \"price\": 459, \"cart_key\": \"2_e90dfb84e30edf611e326eeb04d680de\", \"quantity\": 1, \"variation\": \"Black\", \"variant_id\": null}'),
('10', '8', NULL, '1', '0', '2026-10-14 12:34:45', '2026-10-19 06:53:32', '3_e90dfb84e30edf611e326eeb04d680de', '{\"id\": \"3\", \"name\": \"ANC Pro Wireless Earphones BT5.3\", \"image\": \"https://images.unsplash.com/photo-1583394838336-acd977736f90?w=500&h=500&fit=crop&q=80\", \"price\": 890, \"cart_key\": \"3_e90dfb84e30edf611e326eeb04d680de\", \"quantity\": 1, \"variation\": \"Black\", \"variant_id\": null}'),
('13', '15', NULL, '1', '1', '2026-10-16 07:44:31', '2026-10-16 07:44:31', 'variant_20', '{\"id\": \"13\", \"name\": \"adidas\", \"image\": \"http://cartzyy.shop/storage/products/pP8k6zCkMLipzRwe9PehOui46b4aUQSiADLW7dTS.png\", \"price\": 4999.88, \"cart_key\": \"variant_20\", \"quantity\": 1, \"variation\": \"white / 35\", \"variant_id\": 20}'),
('14', '15', NULL, '1', '1', '2026-10-16 08:08:32', '2026-10-16 08:08:32', 'variant_21', '{\"id\": \"13\", \"name\": \"adidas\", \"image\": \"http://cartzyy.shop/storage/products/pP8k6zCkMLipzRwe9PehOui46b4aUQSiADLW7dTS.png\", \"price\": 4999.88, \"cart_key\": \"variant_21\", \"quantity\": 1, \"variation\": \"white / 36\", \"variant_id\": 21}'),
('15', '15', NULL, '2', '1', '2026-10-16 08:20:28', '2026-10-16 08:27:56', '12_af33593ba1e04cc7f211ec740c88bf8c', '{\"id\": \"12\", \"name\": \"Modern 3-Seater Scandinavian Sofa\", \"image\": \"https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=500&h=500&fit=crop&q=80\", \"price\": 12500, \"cart_key\": \"12_af33593ba1e04cc7f211ec740c88bf8c\", \"quantity\": 1, \"variation\": \"Grey Velvet\", \"variant_id\": null}'),
('16', '15', NULL, '1', '1', '2026-10-16 08:29:08', '2026-10-16 08:29:08', '29_22f6e3ab6efc15d0e3f8ba43dfe97360', '{\"id\": \"29\", \"name\": \"Touchscreen Digital Air Fryer 4.5L\", \"image\": \"https://images.unsplash.com/photo-1585515320310-259814833e62?w=500&h=500&fit=crop&q=80\", \"price\": 2199, \"cart_key\": \"29_22f6e3ab6efc15d0e3f8ba43dfe97360\", \"quantity\": 1, \"variation\": \"Matte Black\", \"variant_id\": null}'),
('17', '15', NULL, '1', '1', '2026-10-16 08:34:52', '2026-10-16 08:34:52', '19_caba3bd8c7089e19620a6a6f75bb21ec', '{\"id\": \"19\", \"name\": \"Women Floral Summer Bohemian Midi Dress\", \"image\": \"https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=500&h=500&fit=crop&q=80\", \"price\": 489, \"cart_key\": \"19_caba3bd8c7089e19620a6a6f75bb21ec\", \"quantity\": 1, \"variation\": \"Floral / Medium\", \"variant_id\": null}'),
('18', '15', NULL, '1', '1', '2026-10-16 08:45:10', '2026-10-16 08:45:10', '3_e90dfb84e30edf611e326eeb04d680de', '{\"id\": \"3\", \"name\": \"ANC Pro Wireless Earphones BT5.3\", \"image\": \"https://images.unsplash.com/photo-1583394838336-acd977736f90?w=500&h=500&fit=crop&q=80\", \"price\": 890, \"cart_key\": \"3_e90dfb84e30edf611e326eeb04d680de\", \"quantity\": 1, \"variation\": \"Black\", \"variant_id\": null}'),
('20', '8', NULL, '1', '0', '2026-10-17 08:25:24', '2026-10-19 06:53:32', '2_e90dfb84e30edf611e326eeb04d680de', '{\"id\": \"2\", \"name\": \"Minimalist Chronograph Watch\", \"image\": \"https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500&h=500&fit=crop&q=80\", \"price\": 459, \"cart_key\": \"2_e90dfb84e30edf611e326eeb04d680de\", \"quantity\": 1, \"variation\": \"Black\", \"variant_id\": null}'),
('21', '8', NULL, '1', '0', '2026-10-17 08:25:40', '2026-10-19 06:53:32', '12_af33593ba1e04cc7f211ec740c88bf8c', '{\"id\": \"12\", \"name\": \"Modern 3-Seater Scandinavian Sofa\", \"image\": \"https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=500&h=500&fit=crop&q=80\", \"price\": 12500, \"cart_key\": \"12_af33593ba1e04cc7f211ec740c88bf8c\", \"quantity\": 1, \"variation\": \"Grey Velvet\", \"variant_id\": null}'),
('22', '8', NULL, '1', '0', '2026-10-17 08:25:42', '2026-10-19 06:53:32', '15_e38bfa6dfee2af6570a4d9913d8d33eb', '{\"id\": \"15\", \"name\": \"Vacuum Insulated Steel Tumbler 32oz\", \"image\": \"https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=500&h=500&fit=crop&q=80\", \"price\": 480, \"cart_key\": \"15_e38bfa6dfee2af6570a4d9913d8d33eb\", \"quantity\": 1, \"variation\": \"Midnight Blue\", \"variant_id\": null}'),
('29', '8', NULL, '1', '1', '2026-10-19 06:53:22', '2026-10-19 06:53:32', '50_9e23f5f57a219d77d81a9193c8cc2e2c', '{\"id\": \"50\", \"name\": \"Logitech Zone True Wireless Earbuds\", \"image\": \"https://cartzyy.shop/storage/products/GUGLlkch49ozBIQQa9B8C4SyM5fYTnBpa9fqFmmb.jpg\", \"price\": 899, \"cart_key\": \"50_9e23f5f57a219d77d81a9193c8cc2e2c\", \"quantity\": 1, \"variation\": \"Graphite\", \"variant_id\": null}'),
('35', '22', NULL, '301', '1', '2026-10-20 04:06:40', '2026-10-20 04:07:46', 'variant_126', '{\"id\": \"56\", \"name\": \"Women\'s Dri-FIT Pleated Tennis Skirt\", \"image\": \"https://cartzyy.shop/storage/products/6L2q9jNmhhq9f0pfbW2fmHn6A637Qr0iOBjmhZYw.png\", \"price\": 2989, \"cart_key\": \"variant_126\", \"quantity\": 300, \"variation\": \"Black / S\", \"variant_id\": 126}');

-- --------------------------------------------------------
-- Table structure for table `carts`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `carts`;
CREATE TABLE `carts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `carts_user_id_unique` (`user_id`),
  CONSTRAINT `carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `carts` (22 rows)
INSERT INTO `carts` (`id`, `user_id`, `created_at`, `updated_at`) VALUES
('1', '1', '2026-09-21 06:03:58', '2026-09-21 06:03:58'),
('2', '10', '2026-10-12 12:39:00', '2026-10-12 12:39:00'),
('3', '28', '2026-10-12 13:19:12', '2026-10-12 13:19:12'),
('4', '6', '2026-10-13 11:16:45', '2026-10-13 11:16:45'),
('5', '5', '2026-10-14 01:09:53', '2026-10-14 01:09:53'),
('6', '11', '2026-10-14 02:23:12', '2026-10-14 02:23:12'),
('7', '4', '2026-10-14 03:16:59', '2026-10-14 03:16:59'),
('8', '3', '2026-10-14 03:36:54', '2026-10-14 03:36:54'),
('9', '34', '2026-10-14 03:37:33', '2026-10-14 03:37:33'),
('10', '35', '2026-10-14 06:14:43', '2026-10-14 06:14:43'),
('11', '36', '2026-10-14 08:07:37', '2026-10-14 08:07:37'),
('12', '9', '2026-10-15 13:32:42', '2026-10-15 13:32:42'),
('13', '37', '2026-10-15 13:38:46', '2026-10-15 13:38:46'),
('14', '38', '2026-10-15 14:54:52', '2026-10-15 14:54:52'),
('15', '39', '2026-10-16 07:42:10', '2026-10-16 07:42:10'),
('16', '22', '2026-10-16 13:33:42', '2026-10-16 13:33:42'),
('17', '40', '2026-10-17 13:09:37', '2026-10-17 13:09:37'),
('18', '41', '2026-10-18 03:30:33', '2026-10-18 03:30:33'),
('19', '42', '2026-10-18 06:19:05', '2026-10-18 06:19:05'),
('20', '44', '2026-10-18 07:25:09', '2026-10-18 07:25:09'),
('21', '45', '2026-10-18 08:05:17', '2026-10-18 08:05:17'),
('22', '48', '2026-10-20 04:00:06', '2026-10-20 04:00:06');

-- --------------------------------------------------------
-- Table structure for table `categories`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`),
  KEY `categories_parent_id_foreign` (`parent_id`),
  CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `categories` (28 rows)
INSERT INTO `categories` (`id`, `parent_id`, `name`, `slug`, `position`, `is_active`, `created_at`, `updated_at`) VALUES
('1', NULL, 'Women Clothing', 'women-clothing', '1', '1', '2026-10-04 05:54:23', '2026-10-04 05:54:23'),
('2', NULL, 'Beachwear', 'beachwear', '2', '1', '2026-10-04 05:54:23', '2026-10-04 05:54:23'),
('3', NULL, 'Kids', 'kids', '3', '1', '2026-10-04 05:54:23', '2026-10-04 05:54:23'),
('4', NULL, 'Curve', 'curve', '4', '1', '2026-10-04 05:54:23', '2026-10-04 05:54:23'),
('5', NULL, 'Men Clothing', 'men-clothing', '5', '1', '2026-10-04 05:54:23', '2026-10-04 05:54:23'),
('6', NULL, 'Shoes', 'shoes', '6', '1', '2026-10-04 05:54:23', '2026-10-04 05:54:23'),
('7', NULL, 'Jewelry & Accessories', 'jewelry-accessories', '7', '1', '2026-10-04 05:54:23', '2026-10-04 05:54:23'),
('8', NULL, 'Underwear & Sleepwear', 'underwear-sleepwear', '8', '1', '2026-10-04 05:54:23', '2026-10-04 05:54:23'),
('9', NULL, 'Baby & Maternity', 'baby-maternity', '9', '1', '2026-10-04 05:54:23', '2026-10-04 05:54:23'),
('10', NULL, 'Bags & Luggage', 'bags-luggage', '10', '1', '2026-10-04 05:54:23', '2026-10-04 05:54:23'),
('11', NULL, 'Home & Living', 'home-living', '11', '1', '2026-10-04 05:54:23', '2026-10-04 05:54:23'),
('12', NULL, 'Beauty & Health', 'beauty-health', '12', '1', '2026-10-04 05:54:23', '2026-10-04 05:54:23'),
('13', NULL, 'Sports & Outdoors', 'sports-outdoors', '13', '1', '2026-10-04 05:54:23', '2026-10-04 05:54:23'),
('14', NULL, 'Home Textiles', 'home-textiles', '14', '1', '2026-10-04 05:54:23', '2026-10-04 05:54:23'),
('15', NULL, 'Cell Phones & Accessories', 'cell-phones-accessories', '15', '1', '2026-10-04 05:54:23', '2026-10-04 05:54:23'),
('16', NULL, 'Electronics', 'electronics', '16', '1', '2026-10-04 05:54:23', '2026-10-04 05:54:23'),
('17', NULL, 'Tools & Home Improvement', 'tools-home-improvement', '17', '1', '2026-10-04 05:54:23', '2026-10-04 05:54:23'),
('18', NULL, 'Toys & Games', 'toys-games', '18', '1', '2026-10-04 05:54:23', '2026-10-04 05:54:23'),
('19', NULL, 'Pet Supplies', 'pet-supplies', '19', '1', '2026-10-04 05:54:23', '2026-10-04 05:54:23'),
('20', NULL, 'Appliances', 'appliances', '20', '1', '2026-10-04 05:54:23', '2026-10-04 05:54:23'),
('21', NULL, 'Office & School Supplies', 'office-school-supplies', '21', '1', '2026-10-04 05:54:23', '2026-10-04 05:54:23'),
('22', NULL, 'Automotive', 'automotive', '22', '1', '2026-10-04 05:54:23', '2026-10-04 05:54:23'),
('23', NULL, 'General', 'general', '0', '1', '2026-10-10 06:21:01', '2026-10-10 06:21:01'),
('24', '16', 'Headphones & Audio', 'audio-headphones', '1', '1', '2026-10-18 05:23:11', '2026-10-18 05:23:11'),
('25', '16', 'Smart Watches & Wearables', 'smart-wearables', '2', '1', '2026-10-18 05:23:11', '2026-10-18 05:23:11'),
('26', NULL, 'Fashion & Apparel', 'fashion', '2', '1', '2026-10-18 05:23:11', '2026-10-18 05:23:11'),
('27', '26', 'Sneakers & Footwear', 'sneakers-footwear', '1', '1', '2026-10-18 05:23:11', '2026-10-18 05:23:11'),
('28', '26', 'Bags & Accessories', 'bags-accessories', '2', '1', '2026-10-18 05:23:11', '2026-10-18 05:23:11');

-- --------------------------------------------------------
-- Table structure for table `category_product`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `category_product`;
CREATE TABLE `category_product` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_product_product_id_category_id_unique` (`product_id`,`category_id`),
  KEY `category_product_category_id_product_id_index` (`category_id`,`product_id`),
  CONSTRAINT `category_product_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `category_product_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `category_product` (61 rows)
INSERT INTO `category_product` (`id`, `product_id`, `category_id`, `created_at`, `updated_at`) VALUES
('1', '13', '6', '2026-10-18 07:39:30', '2026-10-18 07:39:30'),
('2', '20', '12', '2026-10-18 07:39:30', '2026-10-18 07:39:30'),
('3', '21', '12', '2026-10-18 07:39:30', '2026-10-18 07:39:30'),
('4', '22', '6', '2026-10-18 07:39:30', '2026-10-18 07:39:30'),
('5', '23', '6', '2026-10-18 07:39:30', '2026-10-18 07:39:30'),
('6', '24', '6', '2026-10-18 07:39:30', '2026-10-18 07:39:30'),
('7', '25', '1', '2026-10-18 07:39:30', '2026-10-18 07:39:30'),
('8', '26', '6', '2026-10-18 07:39:30', '2026-10-18 07:39:30'),
('9', '27', '16', '2026-10-18 07:39:30', '2026-10-18 07:39:30'),
('10', '28', '21', '2026-10-18 08:14:22', '2026-10-18 08:14:22'),
('11', '28', '16', '2026-10-18 08:14:22', '2026-10-18 08:14:22'),
('12', '28', '15', '2026-10-18 08:14:22', '2026-10-18 08:14:22'),
('13', '29', '24', '2026-10-18 08:18:03', '2026-10-18 08:18:03'),
('14', '29', '16', '2026-10-18 08:18:03', '2026-10-18 08:18:03'),
('15', '29', '25', '2026-10-18 08:18:03', '2026-10-18 08:18:03'),
('16', '30', '16', '2026-10-18 11:44:07', '2026-10-18 11:44:07'),
('17', '31', '16', '2026-10-18 11:47:56', '2026-10-18 11:47:56'),
('18', '32', '16', '2026-10-18 11:49:23', '2026-10-18 11:49:23'),
('19', '33', '16', '2026-10-18 11:51:00', '2026-10-18 11:51:00'),
('20', '34', '16', '2026-10-18 11:52:20', '2026-10-18 11:52:20'),
('21', '34', '15', '2026-10-18 11:52:20', '2026-10-18 11:52:20'),
('22', '35', '16', '2026-10-18 11:53:36', '2026-10-18 11:53:36'),
('23', '36', '16', '2026-10-18 11:54:48', '2026-10-18 11:54:48'),
('24', '37', '16', '2026-10-18 11:55:47', '2026-10-18 11:55:47'),
('25', '38', '16', '2026-10-18 11:58:09', '2026-10-18 11:58:09'),
('26', '39', '13', '2026-10-18 11:58:36', '2026-10-18 11:58:36'),
('27', '39', '6', '2026-10-18 11:58:36', '2026-10-18 11:58:36'),
('28', '40', '16', '2026-10-18 12:00:15', '2026-10-18 12:00:15'),
('29', '40', '15', '2026-10-18 12:00:15', '2026-10-18 12:00:15'),
('30', '41', '16', '2026-10-18 12:01:57', '2026-10-18 12:01:57'),
('31', '42', '16', '2026-10-18 12:03:57', '2026-10-18 12:03:57'),
('32', '43', '16', '2026-10-18 12:05:43', '2026-10-18 12:05:43'),
('33', '44', '15', '2026-10-18 12:06:57', '2026-10-18 12:06:57'),
('34', '44', '16', '2026-10-18 12:06:57', '2026-10-18 12:06:57'),
('35', '45', '16', '2026-10-18 12:08:19', '2026-10-18 12:08:19'),
('36', '46', '16', '2026-10-18 12:09:56', '2026-10-18 12:09:56'),
('37', '47', '16', '2026-10-18 12:11:07', '2026-10-18 12:11:07'),
('38', '48', '6', '2026-10-18 12:12:23', '2026-10-18 12:12:23'),
('39', '48', '27', '2026-10-18 12:12:23', '2026-10-18 12:12:23'),
('40', '48', '13', '2026-10-18 12:12:23', '2026-10-18 12:12:23'),
('41', '49', '16', '2026-10-18 12:12:24', '2026-10-18 12:12:24'),
('42', '50', '16', '2026-10-18 12:14:18', '2026-10-18 12:14:18'),
('43', '51', '16', '2026-10-18 12:15:57', '2026-10-18 12:15:57'),
('44', '52', '27', '2026-10-18 12:28:17', '2026-10-18 12:28:17'),
('45', '53', '6', '2026-10-18 12:34:52', '2026-10-18 12:34:52'),
('46', '54', '5', '2026-10-18 13:14:18', '2026-10-18 13:14:18'),
('47', '54', '1', '2026-10-18 13:14:18', '2026-10-18 13:14:18'),
('48', '55', '1', '2026-10-18 13:21:48', '2026-10-18 13:21:48'),
('49', '55', '13', '2026-10-18 13:21:48', '2026-10-18 13:21:48'),
('50', '56', '13', '2026-10-18 13:29:16', '2026-10-18 13:29:16'),
('51', '56', '1', '2026-10-18 13:29:16', '2026-10-18 13:29:16'),
('52', '57', '3', '2026-10-18 13:36:50', '2026-10-18 13:36:50'),
('53', '57', '6', '2026-10-18 13:36:50', '2026-10-18 13:36:50'),
('54', '57', '27', '2026-10-18 13:36:50', '2026-10-18 13:36:50'),
('55', '50', '24', '2026-10-19 02:42:50', '2026-10-19 02:42:50'),
('56', '50', '25', '2026-10-19 02:42:50', '2026-10-19 02:42:50'),
('57', '58', '6', '2026-10-20 04:28:23', '2026-10-20 04:28:23'),
('58', '59', '6', '2026-10-20 04:40:11', '2026-10-20 04:40:11'),
('59', '59', '27', '2026-10-20 04:40:11', '2026-10-20 04:40:11'),
('60', '60', '5', '2026-10-20 04:49:50', '2026-10-20 04:49:50'),
('61', '60', '1', '2026-10-20 04:49:50', '2026-10-20 04:49:50');

-- --------------------------------------------------------
-- Table structure for table `delivery_events`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `delivery_events`;
CREATE TABLE `delivery_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `shipment_id` bigint unsigned NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempt` int unsigned NOT NULL DEFAULT '1',
  `user_id` bigint unsigned NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `occurred_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `delivery_events_shipment_id_status_attempt_unique` (`shipment_id`,`status`,`attempt`),
  KEY `delivery_events_user_id_foreign` (`user_id`),
  CONSTRAINT `delivery_events_shipment_id_foreign` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `delivery_events_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `delivery_events` (36 rows)
INSERT INTO `delivery_events` (`id`, `shipment_id`, `status`, `attempt`, `user_id`, `note`, `photo_path`, `occurred_at`, `created_at`, `updated_at`) VALUES
('1', '1', 'order_placed', '1', '10', 'Order placed by buyer.', NULL, '2026-10-10 06:21:01', '2026-10-10 06:21:01', '2026-10-10 06:21:01'),
('2', '2', 'in_transit', '1', '11', 'Status update: in_transit', NULL, '2026-10-11 06:51:57', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('3', '3', 'out_for_delivery', '1', '11', 'Status update: out_for_delivery', NULL, '2026-10-11 06:51:57', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('4', '4', 'unassigned', '1', '11', 'Status update: unassigned', NULL, '2026-10-11 06:51:57', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('5', '5', 'delivered', '1', '11', 'Status update: delivered', NULL, '2026-10-11 06:51:57', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('6', '6', 'unassigned', '1', '11', 'Status update: unassigned', NULL, '2026-10-11 06:51:57', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('7', '7', 'failed', '1', '11', 'Status update: failed', NULL, '2026-10-11 06:51:57', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('8', '6', 'sorted', '1', '11', 'Parcel sorted to area: Laguna - Los Baños', NULL, '2026-10-14 05:12:24', '2026-10-14 05:12:24', '2026-10-14 05:12:24'),
('9', '8', 'order_placed', '1', '10', 'Order placed by buyer.', NULL, '2026-10-14 08:14:38', '2026-10-14 08:14:38', '2026-10-14 08:14:38'),
('10', '9', 'out_for_delivery', '1', '22', 'Assigned to rider Arnel Gomez. Out for delivery to consignee.', NULL, '2026-10-16 08:08:22', '2026-10-16 10:08:22', '2026-10-16 10:08:22'),
('11', '10', 'out_for_delivery', '1', '22', 'Assigned to rider Arnel Gomez. Out for delivery to consignee.', NULL, '2026-10-16 08:08:22', '2026-10-16 10:08:22', '2026-10-16 10:08:22'),
('12', '11', 'in_transit', '1', '22', 'Assigned to rider Arnel Gomez. Out for delivery to consignee.', NULL, '2026-10-16 08:08:22', '2026-10-16 10:08:22', '2026-10-16 10:08:22'),
('13', '12', 'delivered', '1', '22', 'Delivered successfully to Jerome Villafuerte. Barcode verified by rider.', NULL, '2026-10-16 07:08:22', '2026-10-16 10:08:22', '2026-10-16 10:08:22'),
('14', '13', 'delivered', '1', '22', 'Delivered successfully to Liza Soberano Tan. Barcode verified by rider.', NULL, '2026-10-16 08:08:22', '2026-10-16 10:08:22', '2026-10-16 10:08:22'),
('15', '14', 'delivered', '1', '22', 'Delivered successfully to Capt. Ferdinand Cruz. Barcode verified by rider.', NULL, '2026-10-16 09:08:22', '2026-10-16 10:08:22', '2026-10-16 10:08:22'),
('16', '6', 'assigned_to_rider', '1', '11', 'Parcel assigned to rider Arnel Gomez.', NULL, '2026-10-17 06:05:01', '2026-10-17 06:05:01', '2026-10-17 06:05:01'),
('17', '9', 'assigned_to_rider', '1', '11', 'Parcel assigned to rider Noel Torres.', NULL, '2026-10-17 06:09:08', '2026-10-17 06:09:08', '2026-10-17 06:09:08'),
('18', '1', 'received_at_hub', '1', '11', 'Parcel scanned and received at sorting hub.', NULL, '2026-10-17 07:26:44', '2026-10-17 07:26:44', '2026-10-17 07:26:44'),
('19', '15', 'order_placed', '1', '9', 'Order placed by buyer.', NULL, '2026-10-17 09:26:28', '2026-10-17 09:26:28', '2026-10-17 09:26:28'),
('20', '16', 'order_placed', '1', '9', 'Order placed by buyer.', NULL, '2026-10-17 10:50:46', '2026-10-17 10:50:46', '2026-10-17 10:50:46'),
('21', '4', 'picked_up', '1', '43', 'Picked up from seller warehouse (TechZone Official Mall) by rider John Mark.', NULL, '2026-10-18 08:16:13', '2026-10-18 08:16:13', '2026-10-18 08:16:13'),
('22', '6', 'picked_up', '1', '43', 'Picked up from seller warehouse (TechZone Official Mall) by rider John Mark.', NULL, '2026-10-18 08:16:13', '2026-10-18 08:16:13', '2026-10-18 08:16:13'),
('23', '9', 'picked_up', '1', '43', 'Picked up from seller warehouse (TechZone Official Mall) by rider John Mark.', NULL, '2026-10-18 08:16:13', '2026-10-18 08:16:13', '2026-10-18 08:16:13'),
('24', '9', 'delivered', '1', '43', 'Package successfully delivered by rider John Mark. Received by: Ma. Cristina Alcantara', NULL, '2026-10-18 08:16:29', '2026-10-18 08:16:29', '2026-10-18 08:16:29'),
('25', '17', 'order_placed', '1', '10', 'Order placed by buyer.', NULL, '2026-10-19 06:52:55', '2026-10-19 06:52:55', '2026-10-19 06:52:55'),
('26', '18', 'order_placed', '1', '10', 'Order placed by buyer.', NULL, '2026-10-19 07:20:20', '2026-10-19 07:20:20', '2026-10-19 07:20:20'),
('27', '18', 'packed', '1', '42', 'The seller packed the parcel and marked it ready for pickup.', NULL, '2026-10-19 07:21:58', '2026-10-19 07:21:58', '2026-10-19 07:21:58'),
('28', '18', 'pickup_requested', '1', '42', 'Seller requested pickup on 2026-10-19. Awaiting courier confirmation.', NULL, '2026-10-19 07:22:36', '2026-10-19 07:22:36', '2026-10-19 07:22:36'),
('29', '18', 'received_at_hub', '1', '11', 'Parcel scanned and received at sorting hub.', NULL, '2026-10-19 07:24:39', '2026-10-19 07:24:39', '2026-10-19 07:24:39'),
('30', '18', 'sorted', '1', '11', 'Parcel sorted to area: Laguna - Santa Cruz', NULL, '2026-10-19 07:25:00', '2026-10-19 07:25:00', '2026-10-19 07:25:00'),
('31', '18', 'assigned_to_rider', '1', '11', 'Parcel assigned to rider Arnel Gomez.', NULL, '2026-10-19 07:25:31', '2026-10-19 07:25:31', '2026-10-19 07:25:31'),
('32', '19', 'order_placed', '1', '10', 'Order placed by buyer.', NULL, '2026-10-19 07:30:00', '2026-10-19 07:30:00', '2026-10-19 07:30:00'),
('33', '18', 'delivered', '1', '22', 'Package successfully delivered by rider Arnel Gomez. Received by: Jessica Pambago', NULL, '2026-10-19 07:32:12', '2026-10-19 07:32:12', '2026-10-19 07:32:12'),
('34', '20', 'order_placed', '1', '48', 'Order placed by buyer.', NULL, '2026-10-20 04:05:09', '2026-10-20 04:05:09', '2026-10-20 04:05:09'),
('35', '21', 'order_placed', '1', '10', 'Order placed by buyer.', NULL, '2026-10-20 04:18:37', '2026-10-20 04:18:37', '2026-10-20 04:18:37'),
('36', '22', 'order_placed', '1', '9', 'Order placed by buyer.', NULL, '2026-10-20 04:19:24', '2026-10-20 04:19:24', '2026-10-20 04:19:24');

-- --------------------------------------------------------
-- Table structure for table `failed_jobs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `job_batches`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `jobs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `logistics_messages`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `logistics_messages`;
CREATE TABLE `logistics_messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `logistics_provider_id` bigint unsigned NOT NULL,
  `contact_user_id` bigint unsigned NOT NULL,
  `sender_type` enum('hub','contact') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'contact',
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `logistics_messages_contact_user_id_foreign` (`contact_user_id`),
  KEY `logistics_messages_logistics_provider_id_contact_user_id_index` (`logistics_provider_id`,`contact_user_id`),
  CONSTRAINT `logistics_messages_contact_user_id_foreign` FOREIGN KEY (`contact_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `logistics_messages_logistics_provider_id_foreign` FOREIGN KEY (`logistics_provider_id`) REFERENCES `logistics_providers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `logistics_messages` (11 rows)
INSERT INTO `logistics_messages` (`id`, `logistics_provider_id`, `contact_user_id`, `sender_type`, `message`, `read_at`, `created_at`, `updated_at`) VALUES
('1', '1', '5', 'contact', 'Hello Hub! When will the rider arrive for parcel collection?', '2026-10-14 05:56:19', '2026-10-11 06:26:57', '2026-10-14 05:56:19'),
('2', '1', '5', 'hub', 'Rider has been dispatched. ETA is around 15 to 20 minutes.', NULL, '2026-10-11 06:41:57', '2026-10-11 06:51:57'),
('3', '1', '6', 'contact', 'Hello Hub! When will the rider arrive for parcel collection?', NULL, '2026-10-11 06:26:57', '2026-10-11 06:51:57'),
('4', '1', '6', 'hub', 'Rider has been dispatched. ETA is around 15 to 20 minutes.', NULL, '2026-10-11 06:41:57', '2026-10-11 06:51:57'),
('5', '1', '7', 'contact', 'Hello Hub! When will the rider arrive for parcel collection?', '2026-10-14 05:43:34', '2026-10-11 06:26:57', '2026-10-14 05:43:34'),
('6', '1', '7', 'hub', 'Rider has been dispatched. ETA is around 15 to 20 minutes.', NULL, '2026-10-11 06:41:57', '2026-10-11 06:51:57'),
('7', '1', '12', 'contact', 'Hello Hub! When will the rider arrive for parcel collection?', NULL, '2026-10-11 06:26:57', '2026-10-11 06:51:57'),
('8', '1', '12', 'hub', 'Rider has been dispatched. ETA is around 15 to 20 minutes.', NULL, '2026-10-11 06:41:57', '2026-10-11 06:51:57'),
('9', '1', '7', 'hub', '😀', NULL, '2026-10-14 06:21:52', '2026-10-14 06:21:52'),
('10', '1', '22', 'hub', 'Good day Arnel! 2 priority merchant pickups are waiting in your Pasig and Alabang sector. Drive safely!', NULL, '2026-10-16 10:08:22', '2026-10-16 10:08:22'),
('11', '1', '22', 'hub', 'Good day Arnel! 2 priority merchant pickups are waiting in your Pasig and Alabang sector. Drive safely!', NULL, '2026-10-16 11:26:26', '2026-10-16 11:26:26');

-- --------------------------------------------------------
-- Table structure for table `logistics_providers`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `logistics_providers`;
CREATE TABLE `logistics_providers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','approved','suspended','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `contact_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `logistics_providers_slug_unique` (`slug`),
  KEY `logistics_providers_user_id_foreign` (`user_id`),
  CONSTRAINT `logistics_providers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `logistics_providers` (4 rows)
INSERT INTO `logistics_providers` (`id`, `user_id`, `name`, `slug`, `status`, `rejection_reason`, `contact_phone`, `created_at`, `updated_at`) VALUES
('1', '11', 'Metro South Sorting & Fulfillment Hub', 'cartzy-express-logistics', 'approved', NULL, '09173334444', '2026-09-21 06:03:57', '2026-10-11 06:51:54'),
('2', '32', 'Nexora Shop', 'nexora-shop', 'pending', NULL, '09231234141', '2026-10-14 01:53:13', '2026-10-14 01:53:13'),
('3', '33', 'FireNation', 'firenation', 'rejected', 'walang business permit', '09283492342', '2026-10-14 02:10:51', '2026-10-14 03:15:00'),
('4', '46', 'LuxuryParadise', 'luxuryparadise', 'pending', NULL, '09821391237', '2026-10-18 08:43:01', '2026-10-18 08:43:01');

-- --------------------------------------------------------
-- Table structure for table `migrations`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `migrations` (49 rows)
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
('1', '0001_01_01_000000_create_users_table', '1'),
('2', '0001_01_01_000001_create_cache_table', '1'),
('3', '0001_01_01_000002_create_jobs_table', '1'),
('4', '2026_08_19_000001_add_role_and_details_to_users_table', '1'),
('5', '2026_08_26_000002_add_address_contact_to_users_table', '1'),
('6', '2026_09_06_000001_add_detailed_address_to_users_table', '2'),
('7', '2026_09_06_000002_add_buyer_fields_to_users_table', '3'),
('8', '2026_09_13_000001_add_id_verification_fields_to_users_table', '4'),
('9', '2026_09_19_000001_add_google_id_to_users_table', '5'),
('10', '2026_09_21_000001_add_is_suspended_to_users_table', '6'),
('11', '2026_09_21_000002_create_roles_and_role_user_tables', '6'),
('12', '2026_09_21_000003_create_addresses_table', '6'),
('13', '2026_09_21_000004_create_sellers_table', '6'),
('14', '2026_09_21_000005_create_logistics_and_riders_tables', '6'),
('15', '2026_09_21_000006_create_categories_table', '6'),
('16', '2026_09_21_000007_create_products_variants_images_tables', '6'),
('17', '2026_09_21_000008_create_carts_and_cart_items_tables', '6'),
('18', '2026_09_21_000009_create_orders_and_fulfillment_tables', '6'),
('19', '2026_09_25_040805_add_seller_business_fields_to_users_table', '7'),
('20', '2026_10_04_000001_add_dti_permit_to_users_table', '8'),
('21', '2026_10_07_000001_create_wishlists_and_reviews_tables', '9'),
('22', '2026_10_07_000002_create_app_notifications_table', '10'),
('23', '2026_10_07_000003_create_admin_management_tables', '11'),
('24', '2026_10_08_000001_add_seller_communication', '12'),
('25', '2026_10_08_000002_add_gender_and_badge_to_products_table', '13'),
('26', '2026_10_11_000001_enhance_logistics_and_fulfillment_tables', '14'),
('27', '2026_10_11_000002_add_image_path_to_product_variants_table', '15'),
('29', '2026_10_12_000001_create_buyers_table', '16'),
('30', '2026_10_12_000003_add_cancellation_review_to_seller_orders', '17'),
('31', '2026_10_12_000004_persist_storefront_cart_items', '18'),
('32', '2026_10_13_000001_add_seller_discounts_to_products', '19'),
('33', '2026_10_13_000002_add_sale_schedule_to_products', '20'),
('34', '2026_10_14_000001_add_telephone_to_users_table', '21'),
('35', '2026_10_14_000002_add_tin_number_to_users_table', '22'),
('36', '2026_10_14_000003_add_seller_registration_details_to_users', '23'),
('37', '2026_10_16_000001_add_archived_at_to_app_notifications', '24'),
('38', '2026_10_16_000002_add_low_stock_threshold_to_products', '25'),
('39', '2026_10_16_000003_create_product_comments_table', '26'),
('40', '2026_10_16_000004_add_suspended_to_riders_status', '27'),
('41', '2026_10_17_000001_create_product_inquiries_tables', '28'),
('42', '2026_10_18_000001_create_category_product_table', '29'),
('43', '2026_10_18_000001_add_replies_and_reactions_to_product_comments', '30'),
('44', '2026_10_19_000001_create_seller_followers_table', '31'),
('45', '2026_10_19_000002_allow_store_wide_product_inquiries', '32'),
('46', '2026_10_19_000003_create_activity_logs_table', '33'),
('47', '2026_10_19_000004_apply_flat_ten_percent_commission', '34'),
('48', '2026_10_20_000001_create_vouchers_tables', '35'),
('49', '2026_10_20_000002_add_delivered_at_to_messages_tables', '36'),
('50', '2026_10_20_000002_add_buyer_reports_to_admin_disputes', '37');

-- --------------------------------------------------------
-- Table structure for table `order_items`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `seller_order_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `product_variant_id` bigint unsigned NOT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `variant_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit_price_minor` bigint unsigned NOT NULL,
  `quantity` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_seller_order_id_foreign` (`seller_order_id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  KEY `order_items_product_variant_id_foreign` (`product_variant_id`),
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_seller_order_id_foreign` FOREIGN KEY (`seller_order_id`) REFERENCES `seller_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `order_items` (6 rows)
INSERT INTO `order_items` (`id`, `seller_order_id`, `product_id`, `product_variant_id`, `product_name`, `variant_name`, `unit_price_minor`, `quantity`, `created_at`, `updated_at`) VALUES
('14', '17', '46', '94', 'Logitech StreamCam Full HD Webcam', 'Black', '49900', '1', '2026-10-19 06:52:55', '2026-10-19 06:52:55'),
('15', '18', '51', '104', 'Logitech Rally Mic Pod Conference Microphone', 'Graphite', '129900', '9', '2026-10-19 07:20:20', '2026-10-19 07:20:20'),
('16', '19', '51', '104', 'Logitech Rally Mic Pod Conference Microphone', 'Graphite', '129900', '9', '2026-10-19 07:30:00', '2026-10-19 07:30:00'),
('17', '20', '57', '129', 'Air Jordan 1 Mid', 'US 10C', '499408', '355', '2026-10-20 04:05:09', '2026-10-20 04:05:09'),
('18', '21', '56', '126', 'Women\'s Dri-FIT Pleated Tennis Skirt', 'Black / S', '298900', '344', '2026-10-20 04:18:37', '2026-10-20 04:18:37'),
('19', '22', '55', '123', 'Women\'s Slim Long-Sleeve Sheer Top', 'White / S', '199900', '456', '2026-10-20 04:19:24', '2026-10-20 04:19:24');

-- --------------------------------------------------------
-- Table structure for table `orders`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `buyer_id` bigint unsigned NOT NULL,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_minor` bigint unsigned NOT NULL,
  `payment_method` enum('cod','wallet') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cod',
  `payment_status` enum('pending','paid','refunded','partially_refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `shipping_address` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_reference_unique` (`reference`),
  KEY `orders_buyer_id_foreign` (`buyer_id`),
  CONSTRAINT `orders_buyer_id_foreign` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `orders` (22 rows)
INSERT INTO `orders` (`id`, `buyer_id`, `reference`, `total_minor`, `payment_method`, `payment_status`, `shipping_address`, `created_at`, `updated_at`) VALUES
('1', '10', 'ORD-2026-53254', '34000', 'wallet', 'paid', '{\"phone\": \"09773587409\", \"address\": \"1011, purok 4, Brgy. Masapang, Victoria, Laguna, 4011\", \"recipient\": \"Jessica Pambago\"}', '2026-10-10 06:21:01', '2026-10-12 13:01:08'),
('2', '1', 'ORD-LOG-1000', '899000', 'cod', 'pending', '{\"phone\": \"09171234567\", \"address\": \"45 Magsaysay St, Brgy. 1, Santa Cruz, Laguna\", \"recipient\": \"Juan Dela Cruz\"}', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('3', '1', 'ORD-LOG-1001', '250000', 'wallet', 'paid', '{\"phone\": \"09271234567\", \"address\": \"12 Maharlika Hwy, Brgy. San Jose, San Pablo, Laguna\", \"recipient\": \"Elena Reyes\"}', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('4', '1', 'ORD-LOG-1002', '549900', 'cod', 'pending', '{\"phone\": \"09451234567\", \"address\": \"7 Aguinaldo Ave, Brgy. Canlubang, Calamba, Laguna\", \"recipient\": \"Pedro Garcia\"}', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('5', '1', 'ORD-LOG-1003', '250000', 'wallet', 'paid', '{\"phone\": \"09181234567\", \"address\": \"33 JP Laurel Hwy, Brgy. Marawoy, Lipa, Batangas\", \"recipient\": \"Ana Lim\"}', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('6', '1', 'ORD-LOG-1004', '8999000', 'cod', 'pending', '{\"phone\": \"09921234567\", \"address\": \"88 Bonifacio St, Brgy. Centro 1, Lucena, Quezon\", \"recipient\": \"Mark Torres\"}', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('7', '1', 'ORD-LOG-1005', '129900', 'cod', 'pending', '{\"phone\": \"09311234567\", \"address\": \"14 Lapu-Lapu St, Brgy. Palho, Calamba, Laguna\", \"recipient\": \"Rhea Castillo\"}', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('8', '10', 'ORD-2026-20969', '130900', 'wallet', 'paid', '{\"phone\": \"09773587409\", \"address\": \"1011, purok 4, Brgy. Masapang, Victoria, Laguna, 4011\", \"recipient\": \"Jessica Pambago\"}', '2026-10-14 08:14:38', '2026-10-14 08:14:46'),
('9', '1', 'ORD-MTR-101', '450000', 'cod', 'paid', '{\"phone\": \"09179998877\", \"address\": \"Unit 14B Emerald Tower, F. Ortigas Jr. Rd, Pasig City\", \"recipient\": \"Ma. Cristina Alcantara\"}', '2026-10-16 10:08:22', '2026-10-18 08:16:29'),
('10', '1', 'ORD-MTR-102', '250000', 'wallet', 'paid', '{\"phone\": \"09287776655\", \"address\": \"Block 8 Lot 12, Villa Olympia Subd, San Pedro, Laguna\", \"recipient\": \"Engr. Roberto Dalisay\"}', '2026-10-16 10:08:22', '2026-10-16 10:08:22'),
('11', '1', 'ORD-MTR-103', '185000', 'cod', 'pending', '{\"phone\": \"09183332211\", \"address\": \"Clinica Medica, 2nd Flr, National Highway, Biñan, Laguna\", \"recipient\": \"Dra. Patricia Evangelista\"}', '2026-10-16 10:08:22', '2026-10-16 10:08:22'),
('12', '1', 'ORD-MTR-091', '125000', 'cod', 'paid', '{\"phone\": \"09171112233\", \"address\": \"18 Narra St, San Pedro, Laguna\", \"recipient\": \"Jerome Villafuerte\"}', '2026-10-16 10:08:22', '2026-10-16 10:08:22'),
('13', '1', 'ORD-MTR-092', '150000', 'wallet', 'paid', '{\"phone\": \"09228887766\", \"address\": \"Phase 3, Southwoods City, Biñan, Laguna\", \"recipient\": \"Liza Soberano Tan\"}', '2026-10-16 10:08:22', '2026-10-16 10:08:22'),
('14', '1', 'ORD-MTR-093', '285000', 'cod', 'paid', '{\"phone\": \"09335554433\", \"address\": \"77 Real St, Santa Rosa, Laguna\", \"recipient\": \"Capt. Ferdinand Cruz\"}', '2026-10-16 10:08:22', '2026-10-16 10:08:22'),
('15', '9', 'ORD-2026-87180', '95000', 'cod', 'pending', '{\"phone\": \"09875643272\", \"address\": \"2100 San Luis Street, Brgy. Putho Tuntungin, Los Baños, Laguna, 4030\", \"recipient\": \"Jan Reyben Del Mundo\"}', '2026-10-17 09:26:28', '2026-10-17 09:26:28'),
('16', '9', 'ORD-2026-36530', '95000', 'cod', 'pending', '{\"phone\": \"09875643272\", \"address\": \"2100 San Luis Street, Brgy. Putho Tuntungin, Los Baños, Laguna, 4030\", \"recipient\": \"Jan Reyben Del Mundo\"}', '2026-10-17 10:50:46', '2026-10-17 10:50:46'),
('17', '10', 'ORD-2026-42864', '55900', 'cod', 'pending', '{\"phone\": \"09773587409\", \"address\": \"1011, purok 4, Brgy. Masapang, Victoria, Laguna, 4011\", \"recipient\": \"Jessica Pambago\"}', '2026-10-19 06:52:55', '2026-10-19 06:52:55'),
('18', '10', 'ORD-2026-96349', '1170100', 'cod', 'paid', '{\"phone\": \"09773587409\", \"address\": \"1011, purok 4, Brgy. Masapang, Victoria, Laguna, 4011\", \"recipient\": \"Jessica Pambago\"}', '2026-10-19 07:20:20', '2026-10-19 07:32:12'),
('19', '10', 'ORD-2026-37747', '1170100', 'cod', 'pending', '{\"phone\": \"09773587409\", \"address\": \"1011, purok 4, Brgy. Masapang, Victoria, Laguna, 4011\", \"recipient\": \"Jessica Pambago\"}', '2026-10-19 07:30:00', '2026-10-19 07:30:00'),
('20', '48', 'ORD-2026-42773', '177290840', 'cod', 'pending', '{\"phone\": \"09656565656\", \"address\": \"sitio sampaguita, Brgy. Bubukal, Santa Cruz, Laguna, 4010\", \"recipient\": \"buyer tester\"}', '2026-10-20 04:05:09', '2026-10-20 04:05:09'),
('21', '10', 'ORD-2026-52176', '102822600', 'cod', 'pending', '{\"phone\": \"09773587409\", \"address\": \"1011, purok 4, Brgy. Masapang, Victoria, Laguna, 4011\", \"recipient\": \"Jessica Pambago\"}', '2026-10-20 04:18:37', '2026-10-20 04:18:37'),
('22', '9', 'ORD-2026-61636', '91155400', 'cod', 'pending', '{\"phone\": \"09875643272\", \"address\": \"2100 San Luis Street, Brgy. Putho Tuntungin, Los Baños, Laguna, 4030\", \"recipient\": \"Jan Reyben Del Mundo\"}', '2026-10-20 04:19:24', '2026-10-20 04:19:24');

-- --------------------------------------------------------
-- Table structure for table `password_reset_tokens`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `payments`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount_minor` bigint unsigned NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `provider_ref` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_order_id_foreign` (`order_id`),
  CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `payments` (10 rows)
INSERT INTO `payments` (`id`, `order_id`, `method`, `amount_minor`, `status`, `provider_ref`, `created_at`, `updated_at`) VALUES
('1', '1', 'GCash', '34000', 'paid', NULL, '2026-10-10 06:21:01', '2026-10-12 13:01:08'),
('2', '8', 'GCash', '130900', 'paid', NULL, '2026-10-14 08:14:38', '2026-10-14 08:14:46'),
('3', '15', 'COD', '95000', 'pending', NULL, '2026-10-17 09:26:28', '2026-10-17 09:26:28'),
('4', '16', 'COD', '95000', 'pending', NULL, '2026-10-17 10:50:46', '2026-10-17 10:50:46'),
('5', '17', 'COD', '55900', 'pending', NULL, '2026-10-19 06:52:55', '2026-10-19 06:52:55'),
('6', '18', 'COD', '1170100', 'completed', NULL, '2026-10-19 07:20:20', '2026-10-19 07:32:12'),
('7', '19', 'COD', '1170100', 'pending', NULL, '2026-10-19 07:30:00', '2026-10-19 07:30:00'),
('8', '20', 'COD', '177290840', 'pending', NULL, '2026-10-20 04:05:09', '2026-10-20 04:05:09'),
('9', '21', 'COD', '102822600', 'pending', NULL, '2026-10-20 04:18:37', '2026-10-20 04:18:37'),
('10', '22', 'COD', '91155400', 'pending', NULL, '2026-10-20 04:19:24', '2026-10-20 04:19:24');

-- --------------------------------------------------------
-- Table structure for table `pickup_requests`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `pickup_requests`;
CREATE TABLE `pickup_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `logistics_provider_id` bigint unsigned NOT NULL,
  `seller_id` bigint unsigned NOT NULL,
  `seller_order_id` bigint unsigned DEFAULT NULL,
  `parcel_count` int unsigned NOT NULL DEFAULT '1',
  `weight_kg` decimal(8,2) NOT NULL DEFAULT '1.00',
  `pickup_date` date NOT NULL,
  `pickup_time` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','confirmed','picked_up','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pickup_requests_logistics_provider_id_foreign` (`logistics_provider_id`),
  KEY `pickup_requests_seller_id_foreign` (`seller_id`),
  KEY `pickup_requests_seller_order_id_foreign` (`seller_order_id`),
  CONSTRAINT `pickup_requests_logistics_provider_id_foreign` FOREIGN KEY (`logistics_provider_id`) REFERENCES `logistics_providers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pickup_requests_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pickup_requests_seller_order_id_foreign` FOREIGN KEY (`seller_order_id`) REFERENCES `seller_orders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `pickup_requests` (5 rows)
INSERT INTO `pickup_requests` (`id`, `logistics_provider_id`, `seller_id`, `seller_order_id`, `parcel_count`, `weight_kg`, `pickup_date`, `pickup_time`, `address`, `status`, `confirmed_at`, `notes`, `created_at`, `updated_at`) VALUES
('1', '1', '1', NULL, '8', '12.40', '2026-10-11', '2:00 PM', '123 Rizal St, Brgy. Poblacion, Santa Cruz, Laguna', 'confirmed', '2026-10-19 07:23:32', 'Fragile electronics. Handle with care.', '2026-10-11 06:51:57', '2026-10-19 07:23:32'),
('2', '1', '2', NULL, '5', '3.80', '2026-10-11', '3:30 PM', '45 Mabini Ave, Brgy. San Jose, San Pablo, Laguna', 'confirmed', '2026-10-11 04:51:57', NULL, '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('3', '1', '3', NULL, '14', '22.10', '2026-10-12', '10:00 AM', '88 Quezon Blvd, Brgy. Halang, Calamba, Laguna', 'pending', NULL, 'Boxes are large. Bring extra packaging tape.', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('4', '1', '1', NULL, '6', '8.50', '2026-10-16', '2:00 PM - 4:00 PM', '123 Tech Hub Bldg, Brgy. San Antonio, Pasig City', 'confirmed', '2026-10-18 08:16:13', 'High-value electronics. Sealed in bubblewrap security bags.', '2026-10-16 10:08:22', '2026-10-18 08:16:13'),
('5', '1', '1', NULL, '4', '5.20', '2026-10-16', '4:30 PM - 5:30 PM', '45 Commerce Center, Alabang, Muntinlupa City', 'confirmed', '2026-10-16 10:26:26', 'Contact warehouse manager Kuya Ben at 0917-888-9900.', '2026-10-16 10:08:22', '2026-10-16 11:26:26');

-- --------------------------------------------------------
-- Table structure for table `platform_announcements`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `platform_announcements`;
CREATE TABLE `platform_announcements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `platform_settings`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `platform_settings`;
CREATE TABLE `platform_settings` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `product_comment_reactions`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `product_comment_reactions`;
CREATE TABLE `product_comment_reactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_comment_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_comment_reactions_product_comment_id_user_id_unique` (`product_comment_id`,`user_id`),
  KEY `product_comment_reactions_user_id_foreign` (`user_id`),
  CONSTRAINT `product_comment_reactions_product_comment_id_foreign` FOREIGN KEY (`product_comment_id`) REFERENCES `product_comments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_comment_reactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `product_comments`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `product_comments`;
CREATE TABLE `product_comments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_comments_user_id_foreign` (`user_id`),
  KEY `product_comments_product_id_created_at_index` (`product_id`,`created_at`),
  KEY `product_comments_parent_id_foreign` (`parent_id`),
  CONSTRAINT `product_comments_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `product_comments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_comments_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `product_comments` (2 rows)
INSERT INTO `product_comments` (`id`, `product_id`, `user_id`, `parent_id`, `body`, `created_at`, `updated_at`) VALUES
('27', '55', '9', NULL, 'Hello', '2026-10-18 14:34:44', '2026-10-18 14:34:44'),
('28', '55', '9', '27', 'Hi', '2026-10-18 14:35:10', '2026-10-18 14:35:10');

-- --------------------------------------------------------
-- Table structure for table `product_images`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `product_images`;
CREATE TABLE `product_images` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_images_product_id_foreign` (`product_id`),
  CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=117 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `product_images` (108 rows)
INSERT INTO `product_images` (`id`, `product_id`, `path`, `position`, `created_at`, `updated_at`) VALUES
('5', '13', 'products/pP8k6zCkMLipzRwe9PehOui46b4aUQSiADLW7dTS.png', '0', '2026-10-16 01:07:17', '2026-10-16 01:07:17'),
('8', '16', 'products/qSioS2YiAdU6iub0aHENUPP0kCAqscrhRced7m5e.png', '0', '2026-10-17 11:20:41', '2026-10-17 11:20:41'),
('9', '16', 'products/FzNZ6Z0XQITE09YdUrJQIXSYVjoOpeR32dfvRlRt.png', '1', '2026-10-17 11:20:41', '2026-10-17 11:20:41'),
('10', '16', 'products/ue5W8d6qX6YCEUTng7D5wRPVTovklWnTfKtPutGF.png', '2', '2026-10-17 11:20:41', '2026-10-17 11:20:41'),
('13', '20', 'products/cHoDDwaSlmUIGM82gc3reDLrOZYivmEIXZU2H6Jb.jpg', '0', '2026-10-18 03:04:16', '2026-10-18 03:04:16'),
('14', '20', 'products/t7jWrrhVDEj2I3LFob3mqNoGbMBXudvWZOvq22zZ.jpg', '1', '2026-10-18 03:04:16', '2026-10-18 03:04:16'),
('15', '20', 'products/rimf78tSr27kWZ3qp4UXlHPFudgex7XCxOf2xTth.jpg', '2', '2026-10-18 03:04:16', '2026-10-18 03:04:16'),
('16', '21', 'products/W7eDLl1UWodnyD8Q63RjcC9RwG5AehD5St6HBx0b.jpg', '0', '2026-10-18 03:15:06', '2026-10-18 03:15:06'),
('17', '22', 'products/GaV6JE0HOzFpuZWzDAoQSUPZf7CCfxqTXuxiusXn.png', '0', '2026-10-18 03:34:06', '2026-10-18 03:34:06'),
('18', '22', 'products/G8NDouTxqZHqsMtneuGAoSBGpu0xjpYwfRTyNb3r.png', '1', '2026-10-18 03:34:06', '2026-10-18 03:34:06'),
('19', '22', 'products/HEnbsuj7X2EEHukH0hgivfV4WTtd6zm1MvCItxVI.png', '2', '2026-10-18 03:34:06', '2026-10-18 03:34:06'),
('20', '23', 'products/czVp9l8p7Chs5WFSPyXTL5POR0ctNl2gHn9RErHn.png', '0', '2026-10-18 05:03:38', '2026-10-18 05:03:38'),
('21', '23', 'products/zTaWUuVEmLNofAG2W2j9W0cWErgjnfIeoKoC9pTM.png', '1', '2026-10-18 05:15:43', '2026-10-18 05:15:43'),
('22', '23', 'products/4FJrvWDtsXAsJ7w3oKF0jIJs7dP73XvkmW7domyg.png', '2', '2026-10-18 05:15:43', '2026-10-18 05:15:43'),
('23', '24', 'products/xow3FaTg3xdEMT71tWqTCUEevan1vclFUWZihWk2.png', '0', '2026-10-18 05:19:52', '2026-10-18 05:19:52'),
('24', '24', 'products/alL1DdekKrNeAfeZDHav7iNB2iEtVxiMSTFRHUdx.png', '1', '2026-10-18 05:19:52', '2026-10-18 05:19:52'),
('25', '24', 'products/i7hsESEhwDaZaIuLxvTefp9ZxF7UXisF40e0bBUk.png', '2', '2026-10-18 05:19:52', '2026-10-18 05:19:52'),
('26', '25', 'products/24Z5YmNiiMkA05YLhzrP3mAZpFQQzAbKHUcO8NWN.png', '0', '2026-10-18 05:42:18', '2026-10-18 05:42:18'),
('27', '25', 'products/WNS1ECxrkzJeKggjiG7r65q9dulA2lCxChIej8CD.png', '1', '2026-10-18 05:42:18', '2026-10-18 05:42:18'),
('28', '25', 'products/U3CaQ0wTL7Hry2bOAb891PhdQvu08LUXS2yNBX0u.png', '2', '2026-10-18 05:42:18', '2026-10-18 05:42:18'),
('29', '26', 'products/FDUeWULJBAIHhQZEpAN7lyfU4iIB0K4LTaErvt5K.png', '0', '2026-10-18 06:17:30', '2026-10-18 06:17:30'),
('30', '26', 'products/LFLebp5UHPYMOFX4Z2oxi6Io8gEaX8TyQLkORX0v.png', '1', '2026-10-18 06:17:30', '2026-10-18 06:17:30'),
('31', '26', 'products/g69dzagyPbHB3sMQ3rw3Rthgl0emaMcx3t1ceXTy.png', '2', '2026-10-18 06:17:30', '2026-10-18 06:17:30'),
('32', '27', 'products/UcvIj2BgyK1dB55o8y1cp6JVNoa1oIotHk1UBc63.png', '0', '2026-10-18 07:30:18', '2026-10-18 07:30:18'),
('33', '27', 'products/QAxNjv0dGplpltxL9Fto1uwAmlDogHWh4UaSmI4z.png', '1', '2026-10-18 07:30:18', '2026-10-18 07:30:18'),
('34', '27', 'products/ipcT9nJb1W8zl3eSN1guMIOPSEeS9z3XjVf1Wsi4.png', '2', '2026-10-18 07:30:18', '2026-10-18 07:30:18'),
('35', '28', 'products/xeDrdgZujuOVe12dQPARy6c3eh0PhvOlMguZ53d1.png', '0', '2026-10-18 08:14:24', '2026-10-18 08:14:24'),
('36', '28', 'products/NkOhvpEDes9PYX4rv6vj3rPHFQ1FMKvwoKZnNBqe.png', '1', '2026-10-18 08:14:24', '2026-10-18 08:14:24'),
('37', '28', 'products/tPCnS06ly86sxO0T85SdSs7s9FLl949H45xPKLwt.png', '2', '2026-10-18 08:14:24', '2026-10-18 08:14:24'),
('38', '28', 'products/838nmgdJd57WeuqfoRQKopyecNx53HnDWjRx79EV.png', '3', '2026-10-18 08:14:24', '2026-10-18 08:14:24'),
('39', '29', 'products/ddaZg2s7ZXiBXkSwFXF8G13j7Hz522IryCo1drJd.png', '0', '2026-10-18 08:18:03', '2026-10-18 08:18:03'),
('40', '29', 'products/g2ATGKFo23HRtKVYudY9LGN6zOKfpeunqpaYYOkY.png', '1', '2026-10-18 08:18:03', '2026-10-18 08:18:03'),
('41', '29', 'products/pjSoh93t2kNGyC7XcFJCHY7kSunOle8vjK67PcKc.png', '2', '2026-10-18 08:18:03', '2026-10-18 08:18:03'),
('42', '30', 'products/EPTdD6Ot0XvFfFhAxSX8iNRloKrPJcNOOhu5dWBT.jpg', '0', '2026-10-18 11:44:08', '2026-10-18 11:44:08'),
('43', '31', 'products/UqahgjuGHeZSsaXpxVzS77ba8bLTVySMxnlGQ6GO.jpg', '0', '2026-10-18 11:47:56', '2026-10-18 11:47:56'),
('44', '32', 'products/SO30tX1KMiVhLR8wo2olusJ2d7eb2nmfbcJWs8w2.jpg', '0', '2026-10-18 11:49:23', '2026-10-18 11:49:23'),
('45', '33', 'products/U1JXACpDYrPqXb1WB9URY3jvcIdkAkRkxP5am0xF.jpg', '0', '2026-10-18 11:51:00', '2026-10-18 11:51:00'),
('46', '34', 'products/YX5OL7sPj9XtR49AZ1NwYjG0VSg96T1IevgDOOc0.jpg', '0', '2026-10-18 11:52:20', '2026-10-18 11:52:20'),
('47', '35', 'products/lN7U6sk839aqNu66NUK2oL13j7dFKrlRfPifynyw.jpg', '0', '2026-10-18 11:53:36', '2026-10-18 11:53:36'),
('48', '36', 'products/FSrbl5O6QLTCGSsbvxTqdM8QwT6DD5qFj4EpaLL6.jpg', '0', '2026-10-18 11:54:48', '2026-10-18 11:54:48'),
('49', '37', 'products/M1VHWwjFG9MCC4a9iYKoiGzFRZ4PXuMRraFpSc4m.jpg', '0', '2026-10-18 11:55:47', '2026-10-18 11:55:47'),
('50', '38', 'products/4U4pZZfgxk2Uik3DdwBgcZEyGxQMlDkdMZjWSL8L.jpg', '0', '2026-10-18 11:58:09', '2026-10-18 11:58:09'),
('51', '38', 'products/cWby228cqw6dcuOgJyLdLiIq6kuCwZBksLL2xxFN.jpg', '1', '2026-10-18 11:58:09', '2026-10-18 11:58:09'),
('52', '38', 'products/zSmWWastVpoRuKe8O3sCrHIyChdw8PkpgRQK33hA.jpg', '2', '2026-10-18 11:58:09', '2026-10-18 11:58:09'),
('53', '39', 'products/FEJa89H6fVN4X6uRVwIy04F8MDIbHh5gmSqsILmR.png', '0', '2026-10-18 11:58:36', '2026-10-18 11:58:36'),
('54', '39', 'products/JU8P4mEq7JmtN2XFvubcZIlQ9DBnNfa8US1WSzbg.png', '1', '2026-10-18 11:58:36', '2026-10-18 11:58:36'),
('55', '39', 'products/c3bBOiKzWzeHUQ1aVwGuDzBQfgJbK0snSaXAAple.png', '2', '2026-10-18 11:58:36', '2026-10-18 11:58:36'),
('56', '40', 'products/mLFGM2rnjY9vRF78PsgRZQF5A61Nb7MRrmUVzMBq.jpg', '0', '2026-10-18 12:00:15', '2026-10-18 12:00:15'),
('57', '40', 'products/qDopQ9LTJBtMX1c9N2W8ZNt8HjQhV9vewGN7NXpw.jpg', '1', '2026-10-18 12:00:15', '2026-10-18 12:00:15'),
('58', '40', 'products/hDt5WuBJjf8BvFI7NmhZBe1wa1hWbi1e7Rv6IYBl.jpg', '2', '2026-10-18 12:00:15', '2026-10-18 12:00:15'),
('59', '41', 'products/Csi2078tjjgtaKL1h7VE7pnTn6EG4SBwXnJGLFji.jpg', '0', '2026-10-18 12:01:57', '2026-10-18 12:01:57'),
('60', '41', 'products/bYxBEBp5PCEy3BTCcJZPPoPRxMNaQeOQdR89AQLZ.jpg', '1', '2026-10-18 12:01:57', '2026-10-18 12:01:57'),
('61', '41', 'products/SfWOOBkuFgFbbFyCh7VTSMX2GlzshiMMkGpxS2qV.jpg', '2', '2026-10-18 12:01:57', '2026-10-18 12:01:57'),
('62', '42', 'products/VVqZY7g4qCr02hv9eE1UtbYCCEDa5a0BUYemTfGC.jpg', '0', '2026-10-18 12:03:57', '2026-10-18 12:03:57'),
('63', '42', 'products/uVqcryCL8tO14BHDw2BusifEbP3dlozbjTaUG2gl.jpg', '1', '2026-10-18 12:03:57', '2026-10-18 12:03:57'),
('64', '42', 'products/8rqfVfrcXkmOo2YpwQx6iPxJqdhBk24yLLWOJuG1.jpg', '2', '2026-10-18 12:03:57', '2026-10-18 12:03:57'),
('65', '43', 'products/dPrFF4lRTfKRpEFiiM2kT1oKVyE5SLkgFb1XnVUZ.jpg', '0', '2026-10-18 12:05:43', '2026-10-18 12:05:43'),
('66', '43', 'products/Jv1M5hySNxKZpEbZAk6PNdRKKYJkHRIBLafd5xhX.jpg', '1', '2026-10-18 12:05:43', '2026-10-18 12:05:43'),
('67', '43', 'products/pxNR2T0xzHT2lsEyvYPTuSrXA8O3AshH0BacfUO9.jpg', '2', '2026-10-18 12:05:43', '2026-10-18 12:05:43'),
('68', '44', 'products/saHqpCVKc3v0JlivdUDkLflcLVuYuEzPolCTNapW.jpg', '0', '2026-10-18 12:06:57', '2026-10-18 12:06:57'),
('69', '44', 'products/Uei0B4Ha3VrTxzQHztmAR9niH5zQgJHMECXXoWIM.jpg', '1', '2026-10-18 12:06:57', '2026-10-18 12:06:57'),
('70', '44', 'products/YzheChNdT6llFyRU3NDr17xWT1V6o23B4dL57Fdx.jpg', '2', '2026-10-18 12:06:57', '2026-10-18 12:06:57'),
('71', '45', 'products/4OzW4oeaUEcnnhRIyrc8ct89AHDtIQeQhbKqc8YH.jpg', '0', '2026-10-18 12:08:19', '2026-10-18 12:08:19'),
('72', '45', 'products/1JrgvC5KgtYRO4QWOxn45bg01YXvwSgfw5YvDEIb.jpg', '1', '2026-10-18 12:08:19', '2026-10-18 12:08:19'),
('73', '45', 'products/TKo4TT7SaUWsDzWkO5Dims8y9hcB34KYYaE0C5pW.jpg', '2', '2026-10-18 12:08:19', '2026-10-18 12:08:19'),
('74', '46', 'products/avewBj8hEMqFotdtFeOvkGfTJ3zbubKMONXhqKey.jpg', '0', '2026-10-18 12:09:56', '2026-10-18 12:09:56'),
('75', '46', 'products/AHt9zZzsjCdYzN6fMgYJfYaf8O0gAVDuaVsr4Lvw.jpg', '1', '2026-10-18 12:09:56', '2026-10-18 12:09:56'),
('76', '46', 'products/7SniCw48FFdROHY5kcyesFVbE9cSSXv3JB2imOPR.jpg', '2', '2026-10-18 12:09:56', '2026-10-18 12:09:56'),
('77', '47', 'products/QDqwakNLvabA11AZHllqJpwOmJoeFr28zDsd4BsB.jpg', '0', '2026-10-18 12:11:07', '2026-10-18 12:11:07'),
('78', '47', 'products/mWsnaMzK4BiTMTLyalhOHbZMrHDP541hB9GWp8gY.jpg', '1', '2026-10-18 12:11:07', '2026-10-18 12:11:07'),
('79', '47', 'products/mQkWWqTH63qNDmITsy7x9MYVrWKJIAGtVxirfkV5.jpg', '2', '2026-10-18 12:11:07', '2026-10-18 12:11:07'),
('80', '48', 'products/oVZVRbnCeso6DJjnE47FmXLjpN4hPxzVLzoSn6Tr.png', '0', '2026-10-18 12:12:23', '2026-10-18 12:12:23'),
('81', '48', 'products/QHsAzsZpaeL1vxM7IaKNb4OWHE4Jqi17AATZdMX9.png', '1', '2026-10-18 12:12:23', '2026-10-18 12:12:23'),
('82', '48', 'products/yqToWLr1WoI3ATGUHJVzyxuMQVRnG1OBgwOd7f9O.png', '2', '2026-10-18 12:12:23', '2026-10-18 12:12:23'),
('83', '48', 'products/dMGNbHy6FZ2Pv4bXnGO19jQhRhrkZ7laoxxeI5q3.png', '3', '2026-10-18 12:12:23', '2026-10-18 12:12:23'),
('84', '48', 'products/Ua3SUfobb4s9vC3MhJ4Liae9x2ylZ79S1cfHxuXa.png', '4', '2026-10-18 12:12:23', '2026-10-18 12:12:23'),
('85', '49', 'products/dd03lyXvnY5rbTyui0Hq1yuQiyYEfArFaMN5n2eO.jpg', '0', '2026-10-18 12:12:24', '2026-10-18 12:12:24'),
('86', '49', 'products/YPPRl929egI7QKGF8QQbrdbPYU5cg4ZJq8YJdNEW.jpg', '1', '2026-10-18 12:12:24', '2026-10-18 12:12:24'),
('87', '49', 'products/E7hZV8WtD4hIzJ4CeBitqFyG6Me2Wocf7D8W4Y20.jpg', '2', '2026-10-18 12:12:24', '2026-10-18 12:12:24'),
('88', '50', 'products/GUGLlkch49ozBIQQa9B8C4SyM5fYTnBpa9fqFmmb.jpg', '0', '2026-10-18 12:14:18', '2026-10-18 12:14:18'),
('89', '50', 'products/O6Zi3vVgQ7cwM2Ba8bmBvHfIF4bZGlClS6bRuNSC.jpg', '1', '2026-10-18 12:14:18', '2026-10-18 12:14:18'),
('90', '50', 'products/egJ1W8o1PFNASx50EN5qJy6lNod8NGRKoIGNGjQr.jpg', '2', '2026-10-18 12:14:18', '2026-10-18 12:14:18'),
('91', '51', 'products/xHlx7X14k6tLwD1wamtmsvTERv92MuNwvqAYghJR.jpg', '0', '2026-10-18 12:15:57', '2026-10-18 12:15:57'),
('92', '51', 'products/q222TjJci5AnnCBVPKoAOdzmUjgM75po4Iraa1xF.jpg', '1', '2026-10-18 12:15:57', '2026-10-18 12:15:57'),
('93', '51', 'products/kEUs3U6gPgCqps3fKIOBPzk0otTKY7pw1PwMkZJ0.jpg', '2', '2026-10-18 12:15:57', '2026-10-18 12:15:57'),
('94', '52', 'products/nuzCgxscHbENCQmqTna5Lxm1wU8gU6RfXk14d6gv.png', '0', '2026-10-18 12:28:17', '2026-10-18 12:28:17'),
('95', '52', 'products/BE1edXvorpX7nShlwnC1WqSFc9vC9aO06wzRPf8r.png', '1', '2026-10-18 12:28:17', '2026-10-18 12:28:17'),
('96', '53', 'products/oNW3fcWnbBvPfGTcOajJbAAfFKAEnypCbS4c7eNX.png', '0', '2026-10-18 12:34:52', '2026-10-18 12:34:52'),
('97', '53', 'products/SzjUeptOfK03DgLR1nKmdpeuyQRBISmtEmHlDC2m.png', '1', '2026-10-18 12:34:52', '2026-10-18 12:34:52'),
('98', '54', 'products/d6FCSUhWT61il8L9ILil0lHOIhG0wWKqlVGNPwRU.png', '0', '2026-10-18 13:14:18', '2026-10-18 13:14:18'),
('99', '54', 'products/suDz6ezFkIsMRX9AFi5zd7yhSxfgJnm3afb2Wosb.png', '1', '2026-10-18 13:14:18', '2026-10-18 13:14:18'),
('100', '55', 'products/xEWeqpTgnIS8JS86TsPg6GcI5Y4Bw9Y3SdkQuicO.png', '0', '2026-10-18 13:21:48', '2026-10-18 13:21:48'),
('101', '55', 'products/W18OFWnOYq1NGSUTPIrNX98DWOWaj5a7MieJ2TtX.png', '1', '2026-10-18 13:21:48', '2026-10-18 13:21:48'),
('102', '55', 'products/UWoMbTdKUOVqMFJJyO0XZde3yglMMIsiShPNNBeb.png', '2', '2026-10-18 13:21:48', '2026-10-18 13:21:48'),
('103', '55', 'products/yugTqUZvvNaTInacPY5DkmnsJlGhfswPrjf06zYA.png', '3', '2026-10-18 13:21:48', '2026-10-18 13:21:48'),
('104', '56', 'products/6L2q9jNmhhq9f0pfbW2fmHn6A637Qr0iOBjmhZYw.png', '0', '2026-10-18 13:30:03', '2026-10-18 13:30:03'),
('105', '56', 'products/BfNS23cEeKk44JMxYnwC7Q3AMiz5i2mpkoXVWVSu.png', '1', '2026-10-18 13:30:03', '2026-10-18 13:30:03'),
('106', '56', 'products/LaVYkQB8pdBfMD1uApg6ePXm1of7D7QKHEBTObLl.png', '2', '2026-10-18 13:30:03', '2026-10-18 13:30:03'),
('107', '57', 'products/X7z9Cri1KctQh3zkQxwzQC6B3dJRQYz1xzrX5xBb.png', '0', '2026-10-18 13:36:50', '2026-10-18 13:36:50'),
('108', '57', 'products/mL3CoANmWzRkN2y7iNcvIORDOfJ7BWXe3k7n24c2.png', '1', '2026-10-18 13:36:50', '2026-10-18 13:36:50'),
('109', '57', 'products/yoAENwix2wGThecyv5lIPhBLvQPaTINxU5TBOvLN.png', '2', '2026-10-18 13:36:50', '2026-10-18 13:36:50'),
('110', '58', 'products/EAmCbuhqkqCukF02eKeRo6mVThN0p84KqLUphggV.png', '0', '2026-10-20 04:28:23', '2026-10-20 04:28:23'),
('111', '58', 'products/p0wMAVxorUMYaKbkZwR1oFCiey3fGOChwsBeBSr5.png', '1', '2026-10-20 04:28:23', '2026-10-20 04:28:23'),
('112', '58', 'products/M3SMDcwEIOQZ7hZDYlAs9VXwzyH4s2zfKQ1P58MY.png', '2', '2026-10-20 04:28:23', '2026-10-20 04:28:23'),
('113', '59', 'products/n6GWgZ2kr3xMZ0bl0jKqkkj0HRuEnVJGRfX6BaT2.png', '0', '2026-10-20 04:40:11', '2026-10-20 04:40:11'),
('114', '59', 'products/tmQuYkNoTg1erCAZLWEXWVZwFUy981z7JMs0KWXR.png', '1', '2026-10-20 04:40:11', '2026-10-20 04:40:11'),
('115', '59', 'products/PCbyvyO9B5PLvOehrX6WkaasPT58z18vlykGqD8J.png', '2', '2026-10-20 04:40:11', '2026-10-20 04:40:11'),
('116', '60', 'products/oCrAq3YVKlwt4UViB6E4dkhwqW6dsuCedv0KSyJb.png', '0', '2026-10-20 04:49:50', '2026-10-20 04:49:50');

-- --------------------------------------------------------
-- Table structure for table `product_inquiries`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `product_inquiries`;
CREATE TABLE `product_inquiries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `buyer_id` bigint unsigned NOT NULL,
  `seller_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_inquiries_buyer_id_product_id_unique` (`buyer_id`,`product_id`),
  KEY `product_inquiries_product_id_foreign` (`product_id`),
  KEY `product_inquiries_seller_id_index` (`seller_id`),
  KEY `product_inquiries_buyer_id_seller_id_index` (`buyer_id`,`seller_id`),
  CONSTRAINT `product_inquiries_buyer_id_foreign` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_inquiries_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_inquiries_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `product_inquiries` (7 rows)
INSERT INTO `product_inquiries` (`id`, `buyer_id`, `seller_id`, `product_id`, `created_at`, `updated_at`) VALUES
('1', '9', '18', '27', '2026-10-18 11:06:34', '2026-10-18 11:06:34'),
('3', '9', '18', '28', '2026-10-18 11:12:43', '2026-10-18 11:12:43'),
('4', '9', '15', '25', '2026-10-18 11:12:56', '2026-10-18 11:12:56'),
('5', '10', '18', '36', '2026-10-18 11:57:46', '2026-10-18 11:57:46'),
('6', '3', '18', '51', '2026-10-18 12:43:32', '2026-10-18 12:43:32'),
('7', '10', '18', '47', '2026-10-19 03:49:51', '2026-10-19 03:51:11'),
('8', '10', '17', NULL, '2026-10-19 06:21:52', '2026-10-19 06:21:52');

-- --------------------------------------------------------
-- Table structure for table `product_inquiry_messages`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `product_inquiry_messages`;
CREATE TABLE `product_inquiry_messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_inquiry_id` bigint unsigned NOT NULL,
  `sender_id` bigint unsigned NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_inquiry_messages_sender_id_foreign` (`sender_id`),
  KEY `product_inquiry_messages_product_inquiry_id_created_at_index` (`product_inquiry_id`,`created_at`),
  CONSTRAINT `product_inquiry_messages_product_inquiry_id_foreign` FOREIGN KEY (`product_inquiry_id`) REFERENCES `product_inquiries` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_inquiry_messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `product_inquiry_messages` (1 rows)
INSERT INTO `product_inquiry_messages` (`id`, `product_inquiry_id`, `sender_id`, `body`, `delivered_at`, `read_at`, `created_at`, `updated_at`) VALUES
('2', '7', '10', 'dffd', NULL, '2026-10-19 07:17:01', '2026-10-19 03:51:11', '2026-10-19 07:17:01');

-- --------------------------------------------------------
-- Table structure for table `product_variants`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `product_variants`;
CREATE TABLE `product_variants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `options` json DEFAULT NULL,
  `price_minor` bigint unsigned NOT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `weight_grams` int unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_variants_sku_unique` (`sku`),
  KEY `product_variants_product_id_foreign` (`product_id`),
  CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=161 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `product_variants` (129 rows)
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `name`, `image_path`, `options`, `price_minor`, `stock`, `weight_grams`, `is_active`, `deleted_at`, `created_at`, `updated_at`) VALUES
('20', '13', 'SOS-1', 'white / 35', NULL, '{\"size\": \"35\", \"color_name\": \"white\"}', '699952', '60', '0', '1', NULL, '2026-10-16 01:06:04', '2026-10-16 01:06:04'),
('21', '13', 'ADID-5GMANF', 'white / 36', NULL, '{\"size\": \"36\", \"color_name\": \"white\"}', '699952', '50', '0', '1', NULL, '2026-10-16 01:06:04', '2026-10-16 01:06:04'),
('22', '13', 'ADID-OAZY1A', 'white / 37', NULL, '{\"size\": \"37\", \"color_name\": \"white\"}', '699952', '25', '0', '1', NULL, '2026-10-16 01:06:04', '2026-10-16 01:06:04'),
('26', '16', 'HEHE-TM7GGM', 'S', NULL, '{\"size\": \"S\"}', '20000', '3', '0', '1', NULL, '2026-10-17 11:20:41', '2026-10-17 11:20:41'),
('27', '16', 'HEHE-5QVO7M', 'M', NULL, '{\"size\": \"M\"}', '20000', '5', '0', '1', NULL, '2026-10-17 11:20:41', '2026-10-17 11:20:41'),
('28', '16', 'HEHE-DOPCJO', 'L', NULL, '{\"size\": \"L\"}', '20000', '3', '0', '1', NULL, '2026-10-17 11:20:41', '2026-10-17 11:20:41'),
('38', '20', 'HSLJ-01', 'PB & J', NULL, '{\"color_name\": \"PB & J\"}', '39904', '70', '0', '1', NULL, '2026-10-18 03:04:15', '2026-10-18 03:04:15'),
('39', '21', 'HSLJ-02', 'pixie dust', NULL, '{\"color_name\": \"pixie dust\"}', '39904', '75', '0', '1', NULL, '2026-10-18 03:15:06', '2026-10-18 03:15:06'),
('40', '22', 'Tt-5PF', 'Black/Clementine/Amarillo/Infrared 23 / US 7', NULL, '{\"size\": \"US 7\", \"color_name\": \"Black/Clementine/Amarillo/Infrared 23\"}', '799500', '126', '0', '1', NULL, '2026-10-18 03:34:05', '2026-10-18 04:36:14'),
('41', '22', 'TATU-4UP1FW', 'Black/Clementine/Amarillo/Infrared 23 / US 7.5', NULL, '{\"size\": \"US 7.5\", \"color_name\": \"Black/Clementine/Amarillo/Infrared 23\"}', '799500', '60', '0', '1', NULL, '2026-10-18 04:36:14', '2026-10-18 04:36:14'),
('42', '22', 'TATU-OT7D9U', 'Black/Clementine/Amarillo/Infrared 23 / US 12', NULL, '{\"size\": \"US 12\", \"color_name\": \"Black/Clementine/Amarillo/Infrared 23\"}', '799500', '20', '0', '1', NULL, '2026-10-18 04:36:14', '2026-10-18 05:05:21'),
('43', '22', 'TATU-EPD05P', 'Black/Clementine/Amarillo/Infrared 23 / US 11', NULL, '{\"size\": \"US 11\", \"color_name\": \"Black/Clementine/Amarillo/Infrared 23\"}', '799500', '30', '0', '1', NULL, '2026-10-18 04:36:14', '2026-10-18 04:36:14'),
('44', '22', 'TATU-SWEHHT', 'Black/Clementine/Amarillo/Infrared 23 / US 10', NULL, '{\"size\": \"US 10\", \"color_name\": \"Black/Clementine/Amarillo/Infrared 23\"}', '799500', '50', '0', '1', NULL, '2026-10-18 04:36:14', '2026-10-18 04:36:14'),
('45', '22', 'TATU-QJ2FSH', 'Black/Clementine/Amarillo/Infrared 23 / US 10.5', NULL, '{\"size\": \"US 10.5\", \"color_name\": \"Black/Clementine/Amarillo/Infrared 23\"}', '799500', '20', '0', '1', NULL, '2026-10-18 04:36:14', '2026-10-18 05:05:21'),
('46', '23', 'AJ-1LSE', 'Black/White/Light Orewood Brown/Team Red / US 6', NULL, '{\"size\": \"US 6\", \"color_name\": \"Black/White/Light Orewood Brown/Team Red\"}', '729500', '60', '0', '1', NULL, '2026-10-18 05:02:33', '2026-10-18 05:02:33'),
('47', '23', 'AIRJ-S1SSTC', 'Black/White/Light Orewood Brown/Team Red / US 6.5', NULL, '{\"size\": \"US 6.5\", \"color_name\": \"Black/White/Light Orewood Brown/Team Red\"}', '729500', '22', '0', '1', NULL, '2026-10-18 05:02:33', '2026-10-18 05:02:33'),
('48', '23', 'AIRJ-PQZ5CA', 'Black/White/Light Orewood Brown/Team Red / US 7', NULL, '{\"size\": \"US 7\", \"color_name\": \"Black/White/Light Orewood Brown/Team Red\"}', '729500', '55', '0', '1', NULL, '2026-10-18 05:02:33', '2026-10-18 05:02:33'),
('49', '23', 'AIRJ-VUHHII', 'Black/White/Light Orewood Brown/Team Red / US 7.5', NULL, '{\"size\": \"US 7.5\", \"color_name\": \"Black/White/Light Orewood Brown/Team Red\"}', '729500', '26', '0', '1', NULL, '2026-10-18 05:02:33', '2026-10-18 05:02:33'),
('50', '23', 'AIRJ-H3SVBE', 'Black/White/Light Orewood Brown/Team Red / US 8', NULL, '{\"size\": \"US 8\", \"color_name\": \"Black/White/Light Orewood Brown/Team Red\"}', '729500', '35', '0', '1', NULL, '2026-10-18 05:02:33', '2026-10-18 05:02:33'),
('51', '23', 'AIRJ-K7HZNY', 'Black/White/Light Orewood Brown/Team Red / US 8.5', NULL, '{\"size\": \"US 8.5\", \"color_name\": \"Black/White/Light Orewood Brown/Team Red\"}', '729500', '63', '0', '1', NULL, '2026-10-18 05:02:33', '2026-10-18 05:02:33'),
('52', '24', 'NZS-11', 'Hyper Pink/Black/Summit White/Metallic Silver / US 9.5', NULL, '{\"size\": \"US 9.5\", \"color_name\": \"Hyper Pink/Black/Summit White/Metallic Silver\"}', '729500', '30', '0', '1', NULL, '2026-10-18 05:19:52', '2026-10-18 05:19:52'),
('53', '24', 'NIKE-NITBEE', 'Hyper Pink/Black/Summit White/Metallic Silver / US 10', NULL, '{\"size\": \"US 10\", \"color_name\": \"Hyper Pink/Black/Summit White/Metallic Silver\"}', '729500', '30', '0', '1', NULL, '2026-10-18 05:19:52', '2026-10-18 05:19:52'),
('54', '24', 'NIKE-HEVCDO', 'Hyper Pink/Black/Summit White/Metallic Silver / US 10.5', NULL, '{\"size\": \"US 10.5\", \"color_name\": \"Hyper Pink/Black/Summit White/Metallic Silver\"}', '729500', '30', '0', '1', NULL, '2026-10-18 05:19:52', '2026-10-18 05:19:52'),
('55', '24', 'NIKE-V4SLAG', 'Hyper Pink/Black/Summit White/Metallic Silver / US 6', NULL, '{\"size\": \"US 6\", \"color_name\": \"Hyper Pink/Black/Summit White/Metallic Silver\"}', '729500', '30', '0', '1', NULL, '2026-10-18 05:19:52', '2026-10-18 05:19:52'),
('56', '24', 'NIKE-IF6UE8', 'Hyper Pink/Black/Summit White/Metallic Silver / US 6.5', NULL, '{\"size\": \"US 6.5\", \"color_name\": \"Hyper Pink/Black/Summit White/Metallic Silver\"}', '729500', '30', '0', '1', NULL, '2026-10-18 05:19:52', '2026-10-18 05:19:52'),
('57', '24', 'NIKE-WPFXJL', 'Hyper Pink/Black/Summit White/Metallic Silver / US 7', NULL, '{\"size\": \"US 7\", \"color_name\": \"Hyper Pink/Black/Summit White/Metallic Silver\"}', '729500', '30', '0', '1', NULL, '2026-10-18 05:19:52', '2026-10-18 05:19:52'),
('58', '25', 'KS9991', 'Branch / Silver Pebble / Branch / XS', NULL, '{\"size\": \"XS\", \"color_name\": \"Branch / Silver Pebble / Branch\"}', '270000', '23', '0', '1', NULL, '2026-10-18 05:42:18', '2026-10-18 05:42:18'),
('59', '25', 'HEAT-YGXYBR', 'Branch / Silver Pebble / Branch / S', NULL, '{\"size\": \"S\", \"color_name\": \"Branch / Silver Pebble / Branch\"}', '270000', '24', '0', '1', NULL, '2026-10-18 05:42:18', '2026-10-18 05:42:18'),
('60', '25', 'HEAT-XVKWVH', 'Branch / Silver Pebble / Branch / M', NULL, '{\"size\": \"M\", \"color_name\": \"Branch / Silver Pebble / Branch\"}', '270000', '33', '0', '1', NULL, '2026-10-18 05:42:18', '2026-10-18 05:42:18'),
('61', '26', 'LA1504', 'Off White / Crew Blue / Gum / US 10C', NULL, '{\"size\": \"US 10C\", \"color_name\": \"Off White / Crew Blue / Gum\"}', '330000', '29', '0', '1', NULL, '2026-10-18 06:17:29', '2026-10-18 06:17:29'),
('62', '26', 'ADID-IZX7LK', 'Off White / Crew Blue / Gum / US 10.5C', NULL, '{\"size\": \"US 10.5C\", \"color_name\": \"Off White / Crew Blue / Gum\"}', '330000', '80', '0', '1', NULL, '2026-10-18 06:17:29', '2026-10-18 06:17:29'),
('63', '26', 'ADID-G8KTWG', 'Off White / Crew Blue / Gum / US 11C', NULL, '{\"size\": \"US 11C\", \"color_name\": \"Off White / Crew Blue / Gum\"}', '330000', '50', '0', '1', NULL, '2026-10-18 06:17:30', '2026-10-18 06:17:30'),
('64', '26', 'ADID-C64POI', 'Off White / Crew Blue / Gum / US 11.5C', NULL, '{\"size\": \"US 11.5C\", \"color_name\": \"Off White / Crew Blue / Gum\"}', '330000', '30', '0', '1', NULL, '2026-10-18 06:17:30', '2026-10-18 06:17:30'),
('65', '26', 'ADID-PYUAWE', 'Off White / Crew Blue / Gum / US 12C', NULL, '{\"size\": \"US 12C\", \"color_name\": \"Off White / Crew Blue / Gum\"}', '330000', '30', '0', '1', NULL, '2026-10-18 06:17:30', '2026-10-18 06:17:30'),
('66', '26', 'ADID-QJYYGP', 'Off White / Crew Blue / Gum / US 12.5C', NULL, '{\"size\": \"US 12.5C\", \"color_name\": \"Off White / Crew Blue / Gum\"}', '330000', '30', '0', '1', NULL, '2026-10-18 06:17:30', '2026-10-18 06:17:30'),
('67', '26', 'ADID-XMACJN', 'Off White / Crew Blue / Gum / US 13C', NULL, '{\"size\": \"US 13C\", \"color_name\": \"Off White / Crew Blue / Gum\"}', '330000', '50', '0', '1', NULL, '2026-10-18 06:17:30', '2026-10-18 06:17:30'),
('68', '26', 'ADID-MMVTZF', 'Off White / Crew Blue / Gum / US 1Y', NULL, '{\"size\": \"US 1Y\", \"color_name\": \"Off White / Crew Blue / Gum\"}', '330000', '40', '0', '1', NULL, '2026-10-18 06:17:30', '2026-10-18 06:17:30'),
('69', '26', 'ADID-FCFOYI', 'Off White / Crew Blue / Gum / US 1.5Y', NULL, '{\"size\": \"US 1.5Y\", \"color_name\": \"Off White / Crew Blue / Gum\"}', '330000', '30', '0', '1', NULL, '2026-10-18 06:17:30', '2026-10-18 06:18:01'),
('70', '26', 'ADID-BKG5KD', 'Off White / Crew Blue / Gum / US 2Y', NULL, '{\"size\": \"US 2Y\", \"color_name\": \"Off White / Crew Blue / Gum\"}', '330000', '24', '0', '1', NULL, '2026-10-18 06:17:30', '2026-10-18 06:18:01'),
('71', '26', 'ADID-0OYXXH', 'Off White / Crew Blue / Gum / US 2.5Y', NULL, '{\"size\": \"US 2.5Y\", \"color_name\": \"Off White / Crew Blue / Gum\"}', '330000', '25', '0', '1', NULL, '2026-10-18 06:17:30', '2026-10-18 06:18:01'),
('72', '27', 'SCP- M850 L', 'Graphite', NULL, '{\"color_name\": \"Graphite\"}', '114000', '53', '0', '1', NULL, '2026-10-18 07:30:18', '2026-10-18 07:30:18'),
('73', '28', 'MX-54678', 'Graphite', NULL, '{\"color_name\": \"Graphite\"}', '260000', '82', '0', '1', NULL, '2026-10-18 08:14:22', '2026-10-18 08:14:22'),
('74', '29', 'AST-60943', 'Lilac', NULL, '{\"color_name\": \"Lilac\"}', '267499', '38', '0', '1', NULL, '2026-10-18 08:18:03', '2026-10-18 08:18:03'),
('75', '30', 'LOG-MXM3S-GPH', 'Graphite', NULL, '{\"color_name\": \"Graphite\"}', '599500', '20', '0', '1', NULL, '2026-10-18 11:44:07', '2026-10-18 11:44:31'),
('76', '31', 'LOG-MXKEYS-S-GPH', 'Graphite', NULL, '{\"color_name\": \"Graphite\"}', '699500', '10', '0', '1', NULL, '2026-10-18 11:47:56', '2026-10-18 11:47:56'),
('77', '32', 'LOG-BRIO4K-001', 'Black', NULL, '{\"color_name\": \"Black\"}', '949900', '15', '0', '1', NULL, '2026-10-18 11:49:23', '2026-10-18 11:49:23'),
('78', '33', 'LOG-GPROXSL2-BLK', 'Black', NULL, '{\"color_name\": \"Black\"}', '899900', '20', '0', '1', NULL, '2026-10-18 11:51:00', '2026-10-18 11:51:00'),
('79', '34', 'LOG-ZV100-GPH', 'Graphite', NULL, '{\"color_name\": \"Graphite\"}', '549900', '10', '0', '1', NULL, '2026-10-18 11:52:20', '2026-10-18 11:52:20'),
('80', '35', 'LOG-C920S-BLK', 'Black', NULL, '{\"color_name\": \"Black\"}', '349900', '11', '0', '1', NULL, '2026-10-18 11:53:36', '2026-10-18 11:53:36'),
('81', '36', 'LOG-LIFT-GPH', 'Graphite', NULL, '{\"color_name\": \"Graphite\"}', '449900', '45', '0', '1', NULL, '2026-10-18 11:54:48', '2026-10-18 11:54:48'),
('82', '37', 'LOG-M350S-GPH', 'Graphite', NULL, '{\"color_name\": \"Graphite\"}', '149900', '12', '0', '1', NULL, '2026-10-18 11:55:47', '2026-10-18 11:55:47'),
('83', '38', 'LOG-K380S-GPH', 'Graphite', NULL, '{\"color_name\": \"Graphite\"}', '249900', '13', '0', '1', NULL, '2026-10-18 11:58:09', '2026-10-18 11:58:09'),
('84', '39', 'RC-001', 'Obsidian/Dark Iris/Black/Total Orange / US 10', NULL, '{\"size\": \"US 10\", \"color_name\": \"Obsidian/Dark Iris/Black/Total Orange\"}', '789500', '1029', '0', '1', NULL, '2026-10-18 11:58:36', '2026-10-18 11:58:36'),
('85', '39', 'JA4H-YIFSML', 'Obsidian/Dark Iris/Black/Total Orange / US 10.5', NULL, '{\"size\": \"US 10.5\", \"color_name\": \"Obsidian/Dark Iris/Black/Total Orange\"}', '795900', '1029', '0', '1', NULL, '2026-10-18 11:58:36', '2026-10-18 11:58:36'),
('86', '39', 'JA4H-WW5FSL', 'Obsidian/Dark Iris/Black/Total Orange / US 11', NULL, '{\"size\": \"US 11\", \"color_name\": \"Obsidian/Dark Iris/Black/Total Orange\"}', '919900', '1029', '0', '1', NULL, '2026-10-18 11:58:36', '2026-10-18 11:58:36'),
('87', '39', 'JA4H-4BGWIM', 'Obsidian/Dark Iris/Black/Total Orange / US 12', NULL, '{\"size\": \"US 12\", \"color_name\": \"Obsidian/Dark Iris/Black/Total Orange\"}', '975500', '1029', '0', '1', NULL, '2026-10-18 11:58:36', '2026-10-18 11:58:36'),
('88', '40', 'LOG-Z407-BLK', 'Black', NULL, '{\"color_name\": \"Black\"}', '549900', '12', '0', '1', NULL, '2026-10-18 12:00:15', '2026-10-18 12:00:15'),
('89', '41', 'LOG-RALLYBAR-MINI-GPH', 'Graphite', NULL, '{\"color_name\": \"Graphite\"}', '150000', '12', '0', '1', NULL, '2026-10-18 12:01:57', '2026-10-18 12:01:57'),
('90', '42', 'LOG-LITRA-GLOW', 'Black', NULL, '{\"color_name\": \"Black\"}', '399900', '34', '0', '1', NULL, '2026-10-18 12:03:57', '2026-10-18 12:03:57'),
('91', '43', 'LOG-G923-BLK', 'Black', NULL, '{\"color_name\": \"Black\"}', '2299900', '20', '0', '1', NULL, '2026-10-18 12:05:43', '2026-10-18 12:05:43'),
('92', '44', 'LOG-MEVO-START-BLK', 'Black', NULL, '{\"color_name\": \"Black\"}', '3599900', '31', '0', '1', NULL, '2026-10-18 12:06:57', '2026-10-19 02:41:07'),
('93', '45', 'LOG-G-YETIGX-017', 'Black', NULL, '{\"color_name\": \"Black\"}', '59900', '45', '0', '1', NULL, '2026-10-18 12:08:19', '2026-10-18 12:08:19'),
('94', '46', 'LOG-STRCAM-018', 'Black', NULL, '{\"color_name\": \"Black\"}', '49900', '11', '0', '1', NULL, '2026-10-18 12:09:56', '2026-10-19 06:52:55'),
('95', '47', 'LOG-ZW2-019', 'Graphite', NULL, '{\"color_name\": \"Graphite\"}', '109900', '15', '0', '1', NULL, '2026-10-18 12:11:07', '2026-10-18 12:11:07'),
('96', '48', 'NIKE-Z7FKPO', 'Sand Drift/Blur/Light Orewood Brown/Off-Noir / US 6', NULL, '{\"size\": \"US 6\", \"color_name\": \"Sand Drift/Blur/Light Orewood Brown/Off-Noir\"}', '489900', '5767', '0', '1', NULL, '2026-10-18 12:12:23', '2026-10-18 12:13:28'),
('97', '48', 'NIKE-RDFJXY', 'Sand Drift/Blur/Light Orewood Brown/Off-Noir / US 6.5', NULL, '{\"size\": \"US 6.5\", \"color_name\": \"Sand Drift/Blur/Light Orewood Brown/Off-Noir\"}', '519500', '5555', '0', '1', NULL, '2026-10-18 12:12:23', '2026-10-18 12:13:28'),
('98', '48', 'NIKE-6ACQLK', 'Sand Drift/Blur/Light Orewood Brown/Off-Noir / US 7', NULL, '{\"size\": \"US 7\", \"color_name\": \"Sand Drift/Blur/Light Orewood Brown/Off-Noir\"}', '529500', '4736', '0', '1', NULL, '2026-10-18 12:12:23', '2026-10-18 12:13:28'),
('99', '48', 'NIKE-HOWBRS', 'Sand Drift/Blur/Light Orewood Brown/Off-Noir / US 7.5', NULL, '{\"size\": \"US 7.5\", \"color_name\": \"Sand Drift/Blur/Light Orewood Brown/Off-Noir\"}', '529500', '2335', '0', '1', NULL, '2026-10-18 12:12:23', '2026-10-18 12:13:28'),
('100', '48', 'NIKE-TMALXN', 'Sand Drift/Blur/Light Orewood Brown/Off-Noir / US 8', NULL, '{\"size\": \"US 8\", \"color_name\": \"Sand Drift/Blur/Light Orewood Brown/Off-Noir\"}', '539500', '222', '0', '1', NULL, '2026-10-18 12:12:23', '2026-10-18 12:13:28'),
('101', '48', 'NIKE-TLO4YG', 'Sand Drift/Blur/Light Orewood Brown/Off-Noir / US 8.5', NULL, '{\"size\": \"US 8.5\", \"color_name\": \"Sand Drift/Blur/Light Orewood Brown/Off-Noir\"}', '539500', '234', '0', '1', NULL, '2026-10-18 12:12:23', '2026-10-18 12:13:28'),
('102', '49', 'LOG-A50X-020', 'Black', NULL, '{\"color_name\": \"Black\"}', '169900', '32', '0', '1', NULL, '2026-10-18 12:12:24', '2026-10-18 12:12:24'),
('103', '50', 'LOG-ZTW-021', 'Graphite', NULL, '{\"color_name\": \"Graphite\"}', '89900', '20', '0', '1', NULL, '2026-10-18 12:14:18', '2026-10-18 12:14:18'),
('104', '51', 'LOG-RMP-022', 'Graphite', NULL, '{\"color_name\": \"Graphite\"}', '129900', '0', '0', '1', NULL, '2026-10-18 12:15:57', '2026-10-19 08:01:26'),
('105', '52', 'NIKE-HBNDBF', 'Black / US 12', NULL, '{\"size\": \"US 12\", \"color_name\": \"Black\"}', '262500', '345', '0', '1', NULL, '2026-10-18 12:28:17', '2026-10-18 12:28:17'),
('106', '52', 'NIKE-IHPNUG', 'Black / US 10', NULL, '{\"size\": \"US 10\", \"color_name\": \"Black\"}', '259500', '6442', '0', '1', NULL, '2026-10-18 12:28:17', '2026-10-18 12:28:17'),
('107', '52', 'NIKE-J31LF1', 'Black / US 10.5', NULL, '{\"size\": \"US 10.5\", \"color_name\": \"Black\"}', '259500', '19', '0', '1', NULL, '2026-10-18 12:28:17', '2026-10-18 12:28:17'),
('108', '52', 'NIKE-S7PJBL', 'Black / US 11', NULL, '{\"size\": \"US 11\", \"color_name\": \"Black\"}', '259900', '80', '0', '1', NULL, '2026-10-18 12:28:17', '2026-10-18 12:28:17'),
('109', '52', 'NIKE-LJYDWJ', 'Beige / US 6', 'products/variants/Dr2O8zmBnRaWtNWozmAcAgPXt2uxwRpYQEnBZJip.png', '{\"color\": \"#e3d5b8\"}', '286700', '163', '0', '1', NULL, '2026-10-18 12:28:17', '2026-10-18 12:28:17'),
('110', '52', 'NIKE-PPVV7E', 'Beige / US 6.5', 'products/variants/otB5Ixw3V1Byyl4PqAlhcu2OjnLFIMV4SQTnc0vQ.png', '{\"color\": \"#e3d5b8\"}', '286700', '163', '0', '1', NULL, '2026-10-18 12:28:17', '2026-10-18 12:28:17'),
('111', '52', 'NIKE-OHURKP', 'Beige / US 7', 'products/variants/oTheWO01kCUa1CnnBKsfdBI2Wr40zYTVIKjmLb86.png', '{\"color\": \"#e3d5b8\"}', '286700', '163', '0', '1', NULL, '2026-10-18 12:28:17', '2026-10-18 12:28:17'),
('112', '52', 'NIKE-VPCOTH', 'Blue / US 6', 'products/variants/e3sAMBeRVAcxZ25JErzukyjZVuJQ4JRmK9JThyhW.png', '{\"color\": \"#3b6db3\"}', '259044', '544', '0', '1', NULL, '2026-10-18 12:28:17', '2026-10-18 12:29:03'),
('113', '52', 'NIKE-CNBOL8', 'Blue / US 12', 'products/variants/WbB7TI4CRtsc3LsSqGKBjSKYazKed0fLbtYl3eCW.png', '{\"color\": \"#3b6db3\"}', '259044', '330', '0', '1', NULL, '2026-10-18 12:28:17', '2026-10-18 12:29:03'),
('114', '52', 'NIKE-HMGNT3', 'Blue / US 9', 'products/variants/3jzq7BkFOlU603crRmLxzfhpShO4tIrtVyaQg2qV.png', '{\"color\": \"#3b6db3\"}', '259044', '434', '0', '1', NULL, '2026-10-18 12:28:17', '2026-10-18 12:29:03'),
('115', '52', 'NIKE-1U4BIJ', 'Brown / US 6', 'products/variants/TrgrqtRWHsJ8umNTD3j1wDVCDKxWsJ8WuQ7hsinh.png', '{\"color\": \"#7a5536\"}', '249500', '221', '0', '1', NULL, '2026-10-18 12:28:17', '2026-10-18 12:28:17'),
('116', '52', 'NIKE-U5UFJF', 'Brown / US 6.5', 'products/variants/Zryt81ycGQ0XrT0b4GdDMBcxt6BkZEJWOKYkL6be.png', '{\"color\": \"#7a5536\"}', '249500', '221', '0', '1', NULL, '2026-10-18 12:28:17', '2026-10-18 12:28:17'),
('117', '53', 'NIKE-M0ZQ3G', 'Khaki / US 6', NULL, '{\"size\": \"US 6\", \"color_name\": \"Khaki\"}', '463600', '783', '0', '1', NULL, '2026-10-18 12:34:52', '2026-10-18 13:03:34'),
('118', '53', 'NIKE-8BQB3G', 'Khaki / US 7', NULL, '{\"size\": \"US 7\", \"color_name\": \"Khaki\"}', '463600', '357', '0', '1', NULL, '2026-10-18 12:34:52', '2026-10-18 13:03:34'),
('119', '53', 'NIKE-AKY2L0', 'Khaki / US 8', NULL, '{\"size\": \"US 8\", \"color_name\": \"Khaki\"}', '463600', '222', '0', '1', NULL, '2026-10-18 12:34:52', '2026-10-18 13:03:34'),
('120', '54', 'NIKE-RNVJHV', 'Black / S', NULL, '{\"size\": \"S\", \"color_name\": \"Black\"}', '189474', '642', '0', '1', NULL, '2026-10-18 13:14:18', '2026-10-18 13:14:18'),
('121', '54', 'NIKE-ZJTV8G', 'Black / M', NULL, '{\"size\": \"M\", \"color_name\": \"Black\"}', '189843', '375', '0', '1', NULL, '2026-10-18 13:14:18', '2026-10-18 13:14:18'),
('122', '54', 'NIKE-AEN1RU', 'Black / L', NULL, '{\"size\": \"L\", \"color_name\": \"Black\"}', '195500', '353', '0', '1', NULL, '2026-10-18 13:14:18', '2026-10-18 13:14:18'),
('123', '55', 'SL - 001', 'White / S', NULL, '{\"size\": \"S\", \"color_name\": \"White\"}', '199900', '0', '0', '1', NULL, '2026-10-18 13:21:48', '2026-10-20 04:19:24'),
('124', '55', 'WOME-7NHDFC', 'White / M', NULL, '{\"size\": \"M\", \"color_name\": \"White\"}', '229500', '753', '0', '1', NULL, '2026-10-18 13:21:48', '2026-10-19 06:35:20'),
('125', '55', 'WOME-CPPIYQ', 'White / L', NULL, '{\"size\": \"L\", \"color_name\": \"White\"}', '239886', '233', '0', '1', NULL, '2026-10-18 13:21:48', '2026-10-18 13:21:48'),
('126', '56', 'SS - 001', 'Black / S', NULL, '{\"size\": \"S\", \"color_name\": \"Black\"}', '298900', '0', '0', '1', NULL, '2026-10-18 13:29:16', '2026-10-20 04:18:37'),
('127', '56', 'WOME-MMGCJT', 'Black / M', NULL, '{\"size\": \"M\", \"color_name\": \"Black\"}', '309500', '543', '0', '1', NULL, '2026-10-18 13:29:16', '2026-10-18 13:29:16'),
('128', '56', 'WOME-LET1BN', 'Black / L', NULL, '{\"size\": \"L\", \"color_name\": \"Black\"}', '315600', '331', '0', '1', NULL, '2026-10-18 13:29:16', '2026-10-18 13:29:16'),
('129', '57', 'AIRJ-BNQ5TW', 'US 10C', NULL, '{\"size\": \"US 10C\"}', '499408', '0', '0', '1', NULL, '2026-10-18 13:36:50', '2026-10-20 04:05:09'),
('130', '57', 'AIRJ-KH8EJW', 'US 11C', NULL, '{\"size\": \"US 11C\"}', '499466', '344', '0', '1', NULL, '2026-10-18 13:36:50', '2026-10-18 13:38:31'),
('131', '57', 'AIRJ-NB7XDA', 'US 12C', NULL, '{\"size\": \"US 12C\"}', '499484', '53', '0', '1', NULL, '2026-10-18 13:36:50', '2026-10-18 13:38:31'),
('132', '57', 'AIRJ-1R30SG', 'US 13C', NULL, '{\"size\": \"US 13C\"}', '519617', '444', '0', '1', NULL, '2026-10-18 13:36:50', '2026-10-18 13:38:31'),
('133', '58', 'NIKE-KCZPXE', 'White / US 6', NULL, '{\"size\": \"US 6\", \"color_name\": \"White\"}', '589500', '40', '0', '1', NULL, '2026-10-20 04:28:23', '2026-10-20 04:28:23'),
('134', '58', 'NIKE-SZQXHB', 'White / US 6.5', NULL, '{\"size\": \"US 6.5\", \"color_name\": \"White\"}', '589500', '60', '0', '1', NULL, '2026-10-20 04:28:23', '2026-10-20 04:28:23'),
('135', '58', 'NIKE-OT7EVK', 'White / US 7', NULL, '{\"size\": \"US 7\", \"color_name\": \"White\"}', '589500', '55', '0', '1', NULL, '2026-10-20 04:28:23', '2026-10-20 04:28:23'),
('136', '58', 'NIKE-G1HL7Z', 'White / US 7.5', NULL, '{\"size\": \"US 7.5\", \"color_name\": \"White\"}', '589500', '58', '0', '1', NULL, '2026-10-20 04:28:23', '2026-10-20 04:28:23'),
('137', '58', 'NIKE-M04USR', 'White / US 8', NULL, '{\"size\": \"US 8\", \"color_name\": \"White\"}', '589500', '54', '0', '1', NULL, '2026-10-20 04:28:23', '2026-10-20 04:28:23'),
('138', '58', 'NIKE-MHZE8R', 'White / US 10', NULL, '{\"size\": \"US 10\", \"color_name\": \"White\"}', '589500', '93', '0', '1', NULL, '2026-10-20 04:28:23', '2026-10-20 04:28:23'),
('139', '58', 'NIKE-NS18GF', 'White / US 10.5', NULL, '{\"size\": \"US 10.5\", \"color_name\": \"White\"}', '589500', '73', '0', '1', NULL, '2026-10-20 04:28:23', '2026-10-20 04:28:23'),
('140', '58', 'NIKE-QHSVWG', 'White / US 9.5', NULL, '{\"size\": \"US 9.5\", \"color_name\": \"White\"}', '589500', '42', '0', '1', NULL, '2026-10-20 04:28:23', '2026-10-20 04:28:23'),
('141', '58', 'NIKE-1F9ZTP', 'White / US 9', NULL, '{\"size\": \"US 9\", \"color_name\": \"White\"}', '589500', '96', '0', '1', NULL, '2026-10-20 04:28:23', '2026-10-20 04:28:23'),
('142', '58', 'NIKE-6ZNZHE', 'White / US 8.5', NULL, '{\"size\": \"US 8.5\", \"color_name\": \"White\"}', '589500', '80', '0', '1', NULL, '2026-10-20 04:28:23', '2026-10-20 04:28:23'),
('143', '58', 'NIKE-RWMKUK', 'White / US 11', NULL, '{\"size\": \"US 11\", \"color_name\": \"White\"}', '589500', '40', '0', '1', NULL, '2026-10-20 04:28:23', '2026-10-20 04:28:23'),
('144', '58', 'NIKE-WBFDZT', 'White / US 12', NULL, '{\"size\": \"US 12\", \"color_name\": \"White\"}', '589500', '50', '0', '1', NULL, '2026-10-20 04:28:23', '2026-10-20 04:28:23'),
('145', '59', 'NIKE-QKSQRH', 'Black & White / US 6', NULL, '{\"size\": \"US 6\", \"color_name\": \"Black & White\"}', '699500', '65', '0', '1', NULL, '2026-10-20 04:40:11', '2026-10-20 04:40:11'),
('146', '59', 'NIKE-NEOPZ3', 'Black & White / US 6.5', NULL, '{\"size\": \"US 6.5\", \"color_name\": \"Black & White\"}', '699500', '55', '0', '1', NULL, '2026-10-20 04:40:11', '2026-10-20 04:40:11'),
('147', '59', 'NIKE-CISFYP', 'Black & White / US 7', NULL, '{\"size\": \"US 7\", \"color_name\": \"Black & White\"}', '699500', '566', '0', '1', NULL, '2026-10-20 04:40:11', '2026-10-20 04:40:11'),
('148', '59', 'NIKE-TOQGHC', 'Black & White / US 7.5', NULL, '{\"size\": \"US 7.5\", \"color_name\": \"Black & White\"}', '699500', '15', '0', '1', NULL, '2026-10-20 04:40:11', '2026-10-20 04:40:11'),
('149', '59', 'NIKE-YWBEKJ', 'Black & White / US 8', NULL, '{\"size\": \"US 8\", \"color_name\": \"Black & White\"}', '699500', '20', '0', '1', NULL, '2026-10-20 04:40:11', '2026-10-20 04:40:11'),
('150', '59', 'NIKE-VKZGEW', 'Black & White / US 8.5', NULL, '{\"size\": \"US 8.5\", \"color_name\": \"Black & White\"}', '699500', '41', '0', '1', NULL, '2026-10-20 04:40:11', '2026-10-20 04:40:11'),
('151', '59', 'NIKE-5ZZPN7', 'Black & White / US 9', NULL, '{\"size\": \"US 9\", \"color_name\": \"Black & White\"}', '699500', '63', '0', '1', NULL, '2026-10-20 04:40:11', '2026-10-20 04:40:11'),
('152', '59', 'NIKE-NAH3ZO', 'Black & White / US 9.5', NULL, '{\"size\": \"US 9.5\", \"color_name\": \"Black & White\"}', '699500', '53', '0', '1', NULL, '2026-10-20 04:40:11', '2026-10-20 04:40:11'),
('153', '59', 'NIKE-CSI3GW', 'Black & White / US 11', NULL, '{\"size\": \"US 11\", \"color_name\": \"Black & White\"}', '699500', '23', '0', '1', NULL, '2026-10-20 04:40:11', '2026-10-20 04:40:11'),
('154', '59', 'NIKE-JCGRH2', 'Black & White / US 12', NULL, '{\"size\": \"US 12\", \"color_name\": \"Black & White\"}', '699500', '42', '0', '1', NULL, '2026-10-20 04:40:11', '2026-10-20 04:40:11'),
('155', '59', 'NIKE-EBWO4S', 'Black & White / US 10', NULL, '{\"size\": \"US 10\", \"color_name\": \"Black & White\"}', '699500', '28', '0', '1', NULL, '2026-10-20 04:40:11', '2026-10-20 04:40:11'),
('156', '59', 'NIKE-7XECN6', 'Black & White / US 10.5', NULL, '{\"size\": \"US 10.5\", \"color_name\": \"Black & White\"}', '699500', '35', '0', '1', NULL, '2026-10-20 04:40:11', '2026-10-20 04:40:11'),
('157', '60', 'NIKE-NLRJDS', 'Black / S', NULL, '{\"size\": \"S\", \"color_name\": \"Black\"}', '299500', '452', '0', '1', NULL, '2026-10-20 04:49:50', '2026-10-20 04:49:50'),
('158', '60', 'NIKE-R7E2GO', 'Black / M', NULL, '{\"size\": \"M\", \"color_name\": \"Black\"}', '299500', '45', '0', '1', NULL, '2026-10-20 04:49:50', '2026-10-20 04:49:50'),
('159', '60', 'NIKE-XT2AYQ', 'Black / L', NULL, '{\"size\": \"L\", \"color_name\": \"Black\"}', '299500', '52', '0', '1', NULL, '2026-10-20 04:49:50', '2026-10-20 04:49:50'),
('160', '60', 'NIKE-D5BKTT', 'Black / XL', NULL, '{\"size\": \"XL\", \"color_name\": \"Black\"}', '299500', '44', '0', '1', NULL, '2026-10-20 04:49:50', '2026-10-20 04:49:50');

-- --------------------------------------------------------
-- Table structure for table `products`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `seller_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `gender` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT 'unisex',
  `badge` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `low_stock_threshold` int unsigned NOT NULL DEFAULT '5',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `discount_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount_value` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount_starts_at` datetime DEFAULT NULL,
  `discount_ends_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  KEY `products_seller_id_is_active_index` (`seller_id`,`is_active`),
  KEY `products_category_id_is_active_index` (`category_id`,`is_active`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `products_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `products` (43 rows)
INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `slug`, `description`, `gender`, `badge`, `is_active`, `low_stock_threshold`, `deleted_at`, `created_at`, `updated_at`, `discount_type`, `discount_value`, `discount_starts_at`, `discount_ends_at`) VALUES
('13', '1', '6', 'adidas', 'adidas-p2cx075lhk', 'bjgvftdxhgbnmrtyuijklnbvcxsrtyuiknbvcxdsrtyui', 'unisex', 'Authentic 100%', '1', '5', NULL, '2026-10-16 01:06:04', '2026-10-16 01:09:27', 'fixed', '1999.64', '2026-10-16 01:08:00', '2026-10-31 01:08:00'),
('16', '14', '2', 'Bags', 'hehehhehe-gypnw8jkts', 'DELISYOSO', 'unisex', 'Limited Edition', '1', '5', '2026-10-17 13:07:25', '2026-10-17 11:20:41', '2026-10-17 13:07:25', NULL, '0.00', NULL, NULL),
('20', '12', '12', 'Happy skin Lip Jelly', 'happy-skin-lip-jelly-8eqybwetbk', 'Add to Cart

More payment options
A bouncy, plumping, and moisture-filled ultra-staining lip tint, the Happy Skin Lip Jelly coats lips in a lightweight, comfortable yet incredibly budge-proof layer of naturally glossy, juicily pigmented color. Formulated with a priming water-rich gel texture, it smoothens and fills in lips to ensure the dewy stain applies evenly, locks on perfectly, and stays vibrant all day long with its incredible lasting power. Boosted with hydrating Hyaluronic Acid Essence Complex and moisturizing Squalane, it instantly nourishes skin on first swipe to keep pouts soft, supple, and utterly kissable..

HAPPY SKIN SAYS: Hyaluronic Acid Essence Complex powerfully retains water for long-lasting hydration, while Squalane coats lips in plumping moisture.

HOW TO USE: Outline and fill in lips using the applicator.

WHAT\'S IN THE BOX?
- 1 Happy Skin Lip Jelly', 'women', 'Best Seller', '1', '5', NULL, '2026-10-18 03:04:15', '2026-10-18 03:11:44', 'fixed', '100.00', '2026-10-18 03:12:00', '2026-10-31 03:04:00'),
('21', '12', '12', 'Happy skin Lip Jelly', 'happy-skin-lip-jelly-gnvtq3iwzs', 'A bouncy, plumping, and moisture-filled ultra-staining lip tint, the Happy Skin Lip Jelly coats lips in a lightweight, comfortable yet incredibly budge-proof layer of naturally glossy, juicily pigmented color. Formulated with a priming water-rich gel texture, it smoothens and fills in lips to ensure the dewy stain applies evenly, locks on perfectly, and stays vibrant all day long with its incredible lasting power. Boosted with hydrating Hyaluronic Acid Essence Complex and moisturizing Squalane, it instantly nourishes skin on first swipe to keep pouts soft, supple, and utterly kissable..

HAPPY SKIN SAYS: Hyaluronic Acid Essence Complex powerfully retains water for long-lasting hydration, while Squalane coats lips in plumping moisture.

HOW TO USE: Outline and fill in lips using the applicator.

WHAT\'S IN THE BOX?
- 1 Happy Skin Lip Jelly', 'women', 'Best Seller', '1', '5', NULL, '2026-10-18 03:15:06', '2026-10-18 03:15:36', 'fixed', '100.00', '2026-10-18 03:15:00', '2026-10-31 03:15:00'),
('22', '17', '6', 'Tatum 5 PF', 'tatum-5-pf-xe2jv4xw2m', 'Built for the grind, the Tatum 5 is always court-ready. Secure your foot perfectly with the mid-foot strap, an under-lace system that locks down the tongue and secures over the laces. Forefoot Air Zoom cushioning primes you for explosive speed, while the moulded upper and herringbone traction underfoot help keep you stable—so you can focus on perfecting your game. Jayson never stops putting in the work. How great are you willing to be?

Benefits
Air Zoom cushioning helps provide a quick-off-the-ground sensation.
Perforations on the toes and sides add breathability.
Product details
Synthetic leather upper
Foam midsole
Rubber outsole
Flat braided laces
Not intended for use as personal protective equipment (PPE)
Hook-and-loop closure
Pull tab on heel
Colour Shown: Black/Clementine/Amarillo/Infrared 23
Style: IO1745-002
Country/Region of Origin: Vietnam', 'men', 'New Arrival', '1', '5', NULL, '2026-10-18 03:34:04', '2026-10-18 03:34:04', NULL, '0.00', NULL, NULL),
('23', '17', '6', 'Air Jordan 1 Low SE', 'air-jordan-1-low-se-tuz7fjmom0', 'Inspired by the original that debuted in 1985, the Air Jordan 1 Low offers a clean, classic look that\'s familiar yet always fresh. With an iconic design that pairs perfectly with any \'fit, these kicks ensure you\'ll always be on point.

Benefits
Nike Air technology absorbs impact for cushioning with every step.
Perforations on the toes add breathability.
Solid rubber outsole enhances traction on a variety of surfaces.
Product details
Low-cut collar
Classic laces
Colour Shown: Black/White/Light Orewood Brown/Team Red
Style: IQ5490-007
Country/Region of Origin: Indonesia', 'men', 'Limited Edition', '1', '5', NULL, '2026-10-18 05:02:33', '2026-10-18 05:02:33', NULL, '0.00', NULL, NULL),
('24', '17', '6', 'Nike Zoom Skylon 11', 'nike-zoom-skylon-11-p3jv6gglul', 'Step into the next wave of Y2K style with the return of the Zoom Skylon 11. Originally released in the late 2000s, this running shoe was renowned for its breathable mesh and responsive Air Zoom cushioning. Now, its benefits blend comfort with retro style for your everyday wear.

Benefits
Air Zoom cushioning helps provide a quick-off-the-ground sensation.
Cushlon midsole offers soft, responsive cushioning.
Mesh upper with structured overlays is lightweight and supportive.
Product Details
Mesh upper with synthetic leather accents
Rubber outsole
Colour Shown: Hyper Pink/Black/Summit White/Metallic Silver
Style: IU7360-601
Country/Region of Origin: Vietnam', 'women', 'Limited Edition', '1', '5', NULL, '2026-10-18 05:19:52', '2026-10-18 05:20:48', 'fixed', '300.00', '2026-10-18 05:20:00', '2026-10-30 05:20:00'),
('25', '15', '1', 'HEATHER 3S CALI TEE', 'heather-3s-cali-tee-lwhgivmrlh', 'Discover the energy of classic American sport with the Heather 3-Stripes Cali Tee, a bold reimagining of timeless athletic style. Inspired by the vibrant spirit of US ball games and on-pitch traditions, this adidas t-shirt delivers a fresh, confident look.

The slim fit is designed to sit close to your body, giving a streamlined silhouette that works for both active days and laid-back moments. Crafted from a soft knit fabric, it also offers comfort that moves with you.

Pair it with denim or casual outfits, and make your everyday wardrobe stand out with a t-shirt that celebrates sport heritage and modern style.', 'women', 'Limited Edition', '1', '5', NULL, '2026-10-18 05:42:18', '2026-10-18 05:42:18', NULL, '0.00', NULL, NULL),
('26', '15', '6', 'ADIDAS PIXAR TOY STORY SAMBA JANE INFANT SHOES', 'adidas-pixar-toy-story-samba-jane-infant-shoes-wsgwrehfww', 'The adidas Pixar Toy Story Samba Jane Shoes bring playful storytelling into a classic Originals silhouette. Designed for kids and their busy days, it brings the magic of Pixar Toy Story to life.

A regular fit supports comfortable wear as feet grow, while a durable leather upper stands up to active use. Inside, the soft sockliner keeps them comfortable through play and downtime.

Finished with Pixar Toy Story details and clean adidas branding, the design balances fun and function. It\'s a kids\' shoe built for movement, curiosity and daily wear, shaped by a sense of adventure.', 'girls', 'Hot Deal', '1', '5', NULL, '2026-10-18 06:17:29', '2026-10-18 06:17:29', NULL, '0.00', NULL, NULL),
('27', '18', '16', 'Signature Comfort Plus M850 L', 'signature-comfort-plus-m850-l-t2qe05ilkb', 'Wireless Bluetooth mouse featuring palm cushion, cozy side grips, quiet clicks, SmartWheel scrolling, and Easy-Switch between up to 3 devices

Specs and Compatibility

Dimensions

Signature Comfort Plus M850 L (with battery) - receiver not included
Height: 121.8 mm
Width: 74.3 mm
Depth: 44.2 mm
Weight: 107.9 g
Signature Comfort Plus M850 L (without battery) - receiver not included
Height: 121.8 mm
Width: 74.3 mm
Depth: 44.2 mm
Weight: 82.9 g
Technical Specifications

Sensor & Tracking
Sensor Technology: Pluto ONE
Nominal value: 1000 DPI
DPI (Minimal and maximal value): 400-4000 DPI (can be set in increments of 100 DPI)
Tracking technology: Logitech Advanced Optical
Buttons
Number of Buttons: 6 (Left/Right-click, Scroll-wheel middle click, side buttons, DPI button)
Gesture button: No
Number of programmable buttons: 4 (Scroll-wheel middle click, side buttons, DPI button)
Scroll Types
Scroll Wheel: SmartWheel
Horizontal scroll: Virtual
Thumb wheel: No
Features
Customization app: Logi Options+, Logi Tune
Handedness: Right
Battery
Battery type: 1 AA battery (Alkaline Battery)
Battery life: 24 months
Battery life with Logi Bolt USB Receiver: up to 24-months
Battery life with Bluetooth®: up to 24-months
Connectivity
Connection Type: Bluetooth Low Energy Technology
Wireless range: 10 m
Unifying-ready mouse: No
Unifying-ready receiver: No
Multi-device pairing: Up to 3 devices
System Requirements
Bluetooth: Required: Bluetooth Low Energy, Windows 10, 11 or later, macOS 12 or later, Linux*, ChromeOS*, iPadOS* 15 or later, Android™ OS* 12 or later. (*Device basic functions will be supported without software for systems other than Windows and macOS)
Logi Bolt USB receiver: Compatible with Logi Bolt USB Receiver (not included), Not compatible with other Logitech USB receivers.
Customization app: Logi Options+, Logi Tune
Certified for: WWCB, Engineered for Intel Evo laptops', 'unisex', 'New Arrival', '1', '5', NULL, '2026-10-18 07:30:18', '2026-10-18 07:30:18', NULL, '0.00', NULL, NULL),
('28', '18', '21', 'MX Keys S', 'mx-keys-s-xgouh1caqc', 'Specs and Compatibility

Dimensions

MX Keys S
Height: 131.63 mm
Width: 430.2 mm
Depth: 20.5 mm
Weight: 810 g
Logi Bolt USB Receiver
Height: 6.11 mm
Width: 14.4 mm
Depth: 18.65 mm
Weight: 1.68 g
Technical Specifications

Features
Proximity sensor: Hand proximity sensors that turn the backlighting on
Light Sensor: Ambient light sensors that adjust backlighting brightness
Indicator Lights: Caps Lock and Battery indicator lights
Filevault: FileVault is an encryption system available on some Mac computers. When enabled, it might prevent Bluetooth devices from connecting with your computer if you have not yet logged in. If you have FileVault enabled, we advise purchasing the compatible Logi Bolt USB Receiver.
Customization app: Supported by Logi Options+ App for recent versions of Windows and macOS, basic feature support for Googlebook laptops. Basic support in Logi Tune. (Basic customization support is available with Options+ App on Googlebook laptops. Advanced features such as the Actions Ring, Marketplace, Smart Actions, Flow are working only on recent versions of Windows and macOS.)
Compatible with Logitech Flow enabled mouse
Battery
Battery type: Rechargeable Li-Po (1500 mAh) battery
Battery life: Full charge lasts up to 10 days with predefined settings or up to 5 months with backlighting off. * (*Battery life may vary based on user and computing conditions.)
USB-C rechargeable: Yes
Power switch: On/Off switch
Connectivity
Connection Type: Connect via Bluetooth low energy technology or the Logi Bolt USB Receiver*. (*The keyboard is not compatible with other Logitech USB receivers)
Easy-switch: Connect up to three devices and switch between them at the push of a button
Wireless range: 10 meters wireless range*. (*Wireless range may vary depending on operating environment and computer setup)
System Requirements
Bluetooth: Requires Bluetooth Low Energy Technology Engineered for Intel® Evo™ Laptops Windows® 10, 11 or later macOS 10.15 or later Linux®*, ChromeOS and Googlebook*, iPadOS* 14 or later Android™ 8.0 or later. (*Device basic functions will be supported without software for other systems than Windows and macOS)
Logi Bolt USB receiver: Requires available USB Port Windows 10, 11 or later macOS 10.15 or later Linux*, ChromeOS and Googlebook*. (*Device basic functions will be supported without software for other systems than Windows and macOS). Not compatible with other Logitech USB receivers.', 'unisex', 'New Arrival', '1', '5', NULL, '2026-10-18 08:14:22', '2026-10-18 08:14:22', NULL, '0.00', NULL, NULL),
('29', '18', '24', 'Astro A10', 'astro-a10-47myhb6ub4', 'Buckle down for all-night gaming sessions with the Astro A10 gaming headset from Logitech G, featuring 32 mm closed-back dynamic drivers for an immersive experience. Inline volume control allows you to adjust your audio without leaving your seat, so you never have to miss a moment of the action. Keep your callouts clean, lock in, and grind ‘til the sun comes up.

Specs and Compatibility

Dimensions

Headset (without cable)
Weight: 246 g
Technical Specifications

Headphone
Driver: 32 mm Neodynamic
Headphone Frequency Response: 20-20,000 Hz
Headphone Sensitivity: 102 dB SPL/mW @1 kHz
Distortion: <1% @1mW 20-10 kHz
Ear coupling: Over-Ear
Headband Pressure: 3.5 N
Microphone
Microphone type: Uni-directional microphone
Pick up pattern: Uni-directional
Size: 6 mm
Connectivity
Connectivity Connection Type: 3.5 mm 5 Pole Jack', 'unisex', 'Limited Edition', '1', '5', NULL, '2026-10-18 08:18:03', '2026-10-18 08:18:30', 'fixed', '300.00', '2026-10-18 08:18:00', '2026-10-31 08:18:00'),
('30', '18', '16', 'Logitech MX Master 3S Wireless Mouse', 'logitech-mx-master-3s-wireless-mouse-hu7xxtnlss', 'Experience precise, comfortable control with the Logitech MX Master 3S wireless mouse. Features an 8,000 DPI sensor, quiet clicks, MagSpeed electromagnetic scrolling, customizable buttons, Bluetooth connectivity, and a USB-C rechargeable battery. Compatible with supported Windows, macOS, and other devices.', 'unisex', 'New Arrival', '1', '5', NULL, '2026-10-18 11:44:07', '2026-10-18 11:44:07', NULL, '0.00', NULL, NULL),
('31', '18', '16', 'Logitech MX Keys S Wireless Keyboard  Copy', 'logitech-mx-keys-s-wireless-keyboard-copy-uv8tpsx4hz', 'Upgrade your workspace with the Logitech MX Keys S wireless keyboard. Features low-profile keys, smart backlighting, customizable shortcut keys, and the ability to connect to and switch between up to three devices. Supports compatible Windows, macOS, Linux, ChromeOS, and iPadOS devices.
 Copy description', 'unisex', NULL, '1', '5', NULL, '2026-10-18 11:47:56', '2026-10-18 11:47:56', NULL, '0.00', NULL, NULL),
('32', '18', '16', 'Logitech BRIO 4K Ultra HD Webcam  Copy', 'logitech-brio-4k-ultra-hd-webcam-copy-1ab3bvil9o', 'Enhance your video calls and recordings with the Logitech BRIO 4K Ultra HD webcam. Features 4K video at 30 fps, autofocus, RightLight 3 with HDR, adjustable field of view, dual built-in microphones, and an attachable privacy shutter. Supports Windows Hello facial recognition on compatible Windows devices.
 Copy description', 'unisex', NULL, '1', '5', NULL, '2026-10-18 11:49:23', '2026-10-18 11:49:23', NULL, '0.00', NULL, NULL),
('33', '18', '16', 'Logitech G PRO X SUPERLIGHT 2 Wireless Gaming Mouse  Copy', 'logitech-g-pro-x-superlight-2-wireless-gaming-mouse-copy-ehvuo7rzep', 'Level up your gaming setup with the Logitech G PRO X SUPERLIGHT 2 wireless gaming mouse. Features an ultra-lightweight 60 g design, HERO 2 sensor with up to 44,000 DPI, LIGHTSPEED wireless connectivity, LIGHTFORCE hybrid switches, and USB-C charging. Designed for precision and fast response during competitive gaming.
 Copy description', 'unisex', NULL, '1', '5', NULL, '2026-10-18 11:51:00', '2026-10-18 11:51:00', NULL, '0.00', NULL, NULL),
('34', '18', '16', 'Logitech Zone Vibe 100 Wireless Headphones  Copy', 'logitech-zone-vibe-100-wireless-headphones-copy-x38oxaefgf', 'Enjoy comfortable wireless audio with the Logitech Zone Vibe 100 headphones. Features 40 mm speakers, Bluetooth connectivity, a noise-cancelling microphone, a flip-to-mute boom mic, and up to 20 hours of listening time under suitable conditions. Ideal for video meetings, remote work, and everyday listening.', 'unisex', 'Trending', '1', '5', NULL, '2026-10-18 11:52:20', '2026-10-18 11:52:20', NULL, '0.00', NULL, NULL),
('35', '18', '16', 'Logitech C920s PRO HD', 'logitech-c920s-pro-hd-ddm5u9whjt', 'Improve your video calls with the Logitech C920s PRO HD webcam. Features Full HD 1080p video at 30 fps, autofocus, automatic light correction, dual stereo microphones, and a removable privacy shutter. Ideal for online meetings, streaming, and remote work.', 'unisex', 'Hot Deal', '1', '5', NULL, '2026-10-18 11:53:36', '2026-10-18 11:53:36', NULL, '0.00', NULL, NULL),
('36', '18', '16', 'Logitech Lift Vertical Ergonomic Mouse', 'logitech-lift-vertical-ergonomic-mouse-3blokwoa5u', 'Work comfortably with the Logitech Lift Vertical Ergonomic Mouse. Its 57-degree vertical design supports a more natural hand position. Features quiet clicks, customizable buttons, a SmartWheel, Bluetooth and Logi Bolt wireless connectivity, and compatibility with supported Windows, macOS, Linux, and ChromeOS devices.', 'unisex', 'Best Seller', '1', '5', NULL, '2026-10-18 11:54:48', '2026-10-18 11:54:48', NULL, '0.00', NULL, NULL),
('37', '18', '16', 'Logitech Pebble Mouse 2 M350s', 'logitech-pebble-mouse-2-m350s-k4ekvihvg3', 'Keep your workspace simple with the Logitech Pebble Mouse 2 M350s. This slim, portable wireless mouse features quiet clicks, Bluetooth connectivity, a customizable middle button, and Easy-Switch support for up to three compatible devices. Its compact design makes it ideal for work, study, and travel.', 'unisex', 'New Arrival', '1', '5', NULL, '2026-10-18 11:55:47', '2026-10-18 11:55:47', NULL, '0.00', NULL, NULL),
('38', '18', '16', 'Logitech Pebble Keys 2 K380s Wireless Keyboard  Copy', 'logitech-pebble-keys-2-k380s-wireless-keyboard-copy-wlah84p6rf', 'Slim and portable wireless keyboard featuring quiet low-profile keys, customizable shortcut keys, Bluetooth connectivity, and Easy-Switch support for up to three compatible devices. Ideal for laptops, tablets, and everyday productivity.', 'unisex', NULL, '1', '5', NULL, '2026-10-18 11:58:09', '2026-10-18 11:58:09', NULL, '0.00', NULL, NULL),
('39', '17', '13', 'Ja 4 \'Heat Check\' EP Basketball Shoes', 'ja-4-heat-check-ep-basketball-shoes-z9bqdvurme', NULL, 'men', NULL, '1', '5', NULL, '2026-10-18 11:58:36', '2026-10-18 11:58:36', NULL, '0.00', NULL, NULL),
('40', '18', '16', 'Logitech Z407 Bluetooth Computer Speakers with Subwoofer  Copy product name', 'logitech-z407-bluetooth-computer-speakers-with-subwoofer-copy-product-name-bvmk3g2t9b', 'Enhance your desktop audio with the Logitech Z407 2.1 speaker system. Includes two satellite speakers, a subwoofer, and a wireless control pod. Supports Bluetooth, USB, and 3.5 mm audio connections, with up to 80 watts peak power for music, movies, and everyday computer use.', 'unisex', 'New Arrival', '1', '5', NULL, '2026-10-18 12:00:15', '2026-10-18 12:00:15', NULL, '0.00', NULL, NULL),
('41', '18', '16', 'Logitech Rally Bar Mini All-in-One Video Conferencing System', 'logitech-rally-bar-mini-all-in-one-video-conferencing-system-r5ggnr21wp', 'Equip small and medium meeting rooms with the Logitech Rally Bar Mini all-in-one video conferencing system. Features a motorized PTZ camera, AI-powered meeting assistance, integrated speakers and microphones, and support for compatible video conferencing platforms. Designed for professional hybrid meetings and collaboration spaces.', 'unisex', 'Hot Deal', '1', '5', NULL, '2026-10-18 12:01:57', '2026-10-18 12:01:57', NULL, '0.00', NULL, NULL),
('42', '18', '16', 'Logitech Litra Glow Premium Streaming Light', 'logitech-litra-glow-premium-streaming-light-nsrti0xsv3', 'Improve your video calls and streaming setup with the Logitech Litra Glow Premium Streaming Light. Features TrueSoft lighting, adjustable brightness and color temperature, a three-way monitor mount, and USB-powered operation. Designed to provide soft, flattering illumination for video meetings, streaming, and content creation.', 'unisex', NULL, '1', '5', NULL, '2026-10-18 12:03:57', '2026-10-18 12:03:57', NULL, '0.00', NULL, NULL),
('43', '18', '16', 'Logitech G923 TRUEFORCE Racing Wheel and Pedals', 'logitech-g923-trueforce-racing-wheel-and-pedals-6varj801dy', 'Experience immersive racing with the Logitech G923 TRUEFORCE racing wheel and pedal set. Features high-definition TRUEFORCE force feedback, a responsive steering wheel, progressive brake pedal, and integrated game controls. Available in versions for compatible PlayStation/PC or Xbox/PC systems, so select the correct platform version when listing.', 'unisex', 'Limited Edition', '1', '5', NULL, '2026-10-18 12:05:43', '2026-10-18 12:05:43', NULL, '0.00', NULL, NULL),
('44', '18', '15', 'Logitech Mevo Start Wireless Live Streaming Camera', 'logitech-mevo-start-wireless-live-streaming-camera-iawjoeovwg', 'Stream live events, interviews, and presentations with the Logitech Mevo Start wireless live-streaming camera. Features Full HD 1080p video, built-in microphones, Wi-Fi and mobile-device control, and support for compatible live-streaming platforms. Its compact design makes it suitable for creators, event organizers, and remote productions.', 'unisex', NULL, '1', '5', NULL, '2026-10-18 12:06:57', '2026-10-18 12:06:57', NULL, '0.00', NULL, NULL),
('45', '18', '16', 'Logitech G Yeti GX Gaming Microphone', 'logitech-g-yeti-gx-gaming-microphone-4nqimhesz5', 'A premium dynamic USB gaming microphone with LIGHTSYNC RGB lighting, a supercardioid pickup pattern, and intelligent audio features for clear voice capture during gaming and streaming.', 'unisex', 'Best Seller', '1', '5', NULL, '2026-10-18 12:08:19', '2026-10-18 12:08:19', NULL, '0.00', NULL, NULL),
('46', '18', '16', 'Logitech StreamCam Full HD Webcam', 'logitech-streamcam-full-hd-webcam-1upm5uuhwz', 'A premium USB-C webcam featuring Full HD 1080p video at up to 60 fps, smart autofocus, and automatic framing support through compatible software. Designed for video calls, live streaming, and content creation.', 'unisex', 'Hot Deal', '1', '5', NULL, '2026-10-18 12:09:56', '2026-10-18 12:09:56', NULL, '0.00', NULL, NULL),
('47', '18', '16', 'Logitech Zone Wireless 2 Business Headset', 'logitech-zone-wireless-2-business-headset-fk5yyvsam6', 'A professional wireless headset designed for hybrid work, featuring active noise cancellation, noise-reducing microphones, Bluetooth connectivity, and clear audio for calls and meetings. Compatible with supported computers and mobile devices.', 'unisex', 'Best Seller', '1', '5', NULL, '2026-10-18 12:11:07', '2026-10-18 12:11:07', NULL, '0.00', NULL, NULL),
('48', '17', '6', 'Nike V5 RNR Premium', 'nike-v5-rnr-premium-urquqdvtup', 'Tackle the everyday in a shoe that matches comfort with style. Staying true to its Y2K roots, the V5 RNR has a foam midsole that\'s chunky yet lightweight. Plus, metallic details and a 3D Swoosh logo elevate this retro runner.', 'women', 'Hot Deal', '1', '5', NULL, '2026-10-18 12:12:23', '2026-10-18 12:12:23', NULL, '0.00', NULL, NULL),
('49', '18', '16', 'Logitech G ASTRO A50 X LIGHTSPEED Gaming Headset', 'logitech-g-astro-a50-x-lightspeed-gaming-headset-hq8mruyxcv', 'A premium wireless gaming headset featuring LIGHTSPEED wireless technology, a built-in base station, PLAYSYNC switching between compatible gaming systems, and immersive audio. Check console compatibility and required connections before purchase.', 'unisex', 'New Arrival', '1', '5', NULL, '2026-10-18 12:12:24', '2026-10-18 12:12:24', NULL, '0.00', NULL, NULL),
('50', '18', '16', 'Logitech Zone True Wireless Earbuds', 'logitech-zone-true-wireless-earbuds-9t5tacikpv', 'True wireless earbuds designed for professional calls and everyday use, featuring active noise cancellation, multiple microphones, Bluetooth connectivity, and a portable charging case. Suitable for hybrid work and compatible devices.', 'unisex', 'Trending', '1', '10', NULL, '2026-10-18 12:14:18', '2026-10-19 02:42:50', NULL, '0.00', NULL, NULL),
('51', '18', '16', 'Logitech Rally Mic Pod Conference Microphone', 'logitech-rally-mic-pod-conference-microphone-ptbjxk78ag', 'A premium tabletop microphone pod designed for compatible Logitech Rally conference systems. Features multiple microphone elements and RightSound technology to help capture voices clearly during meetings. Requires a compatible Rally setup.', 'unisex', 'Best Seller', '1', '5', NULL, '2026-10-18 12:15:57', '2026-10-18 12:15:57', NULL, '0.00', NULL, NULL),
('52', '17', '27', 'Nike Calm 2.0', 'nike-calm-20-s1vvd2mzx5', 'Enjoy a calm, comfortable experience—wherever your day off takes you. Made from soft yet supportive foam, the minimal design makes these slides easy to style with or without socks. A textured footbed is designed to help keep your feet in place.', 'unisex', NULL, '1', '5', NULL, '2026-10-18 12:28:17', '2026-10-18 12:28:17', NULL, '0.00', NULL, NULL),
('53', '17', '6', 'Nike Air Superfly', 'nike-air-superfly-cmgznsh0p5', 'The Superfly ushers in a new era of running-inspired design. It emerges from our vault with new Air and a new attitude. The low-profile shape and outsole pattern channel its athletics heritage, while textured finishes bring your look up to speed.', 'women', 'Trending', '1', '5', NULL, '2026-10-18 12:34:52', '2026-10-18 12:34:52', NULL, '0.00', NULL, NULL),
('54', '17', '5', 'Nike Dri-FIT Form', 'nike-dri-fit-form-ihnydkrkos', 'Designed for running, training and yoga, these versatile shorts are built to handle those days when you need to shake up your exercise routine. Our sweat-wicking Form Shorts offer a smooth feel and a minimal fit made to keep up with your moves, from tough dead lifts to sweaty hot yoga classes.', 'unisex', 'Hot Deal', '1', '5', NULL, '2026-10-18 13:14:17', '2026-10-18 13:14:17', NULL, '0.00', NULL, NULL),
('55', '17', '1', 'Women\'s Slim Long-Sleeve Sheer Top', 'womens-slim-long-sleeve-sheer-top-i7tnywjir7', 'This hooded top is made with sheer fabric that has a touch of stretch. Its slim fit is designed to fit close to the body with ruching on the sides for a flattering look. Thumbholes add playful style and help prevent bunching when layering.', 'women', NULL, '1', '5', NULL, '2026-10-18 13:21:48', '2026-10-18 13:21:48', NULL, '0.00', NULL, NULL),
('56', '17', '13', 'Women\'s Dri-FIT Pleated Tennis Skirt', 'womens-dri-fit-pleated-tennis-skirt-wsyg8phnnn', 'This timeless skirt is made from lightweight woven fabric for cool comfort on the court. Stretchy, built-in shorts give you secure coverage while pockets on both legs offer easy access to your essentials.', 'women', NULL, '1', '10', NULL, '2026-10-18 13:29:16', '2026-10-19 06:33:37', NULL, '0.00', NULL, NULL),
('57', '17', '3', 'Air Jordan 1 Mid', 'air-jordan-1-mid-9vrqepuu0n', 'This iteration of the AJ1 reimagines Nike\'s first signature model with a fresh mix of colours. Premium materials, soft cushioning and a padded ankle collar offer total support and celebrate the shoe that started it all.', 'unisex', 'Hot Deal', '1', '10', NULL, '2026-10-18 13:36:50', '2026-10-19 06:37:03', NULL, '0.00', NULL, NULL),
('58', '17', '6', 'Nike Air Force 1 \'07 Men\'s Shoe', 'nike-air-force-1-07-mens-shoe-ixp8isdgeh', 'The radiance lives on in the Nike Air Force 1 \'07, the b-ball icon that puts a fresh spin on what you know best: crisp leather, bold colours and the perfect amount of flash to make you shine.', 'unisex', 'New Arrival', '1', '10', NULL, '2026-10-20 04:28:23', '2026-10-20 04:41:06', NULL, '0.00', NULL, NULL),
('59', '17', '6', 'Nike Dunk Low Retro', 'nike-dunk-low-retro-ktjif1g9bo', 'The Nike Dunk Low Retro combines classic basketball style with everyday comfort. It features a durable leather upper, a padded low-cut collar, and a rubber outsole for reliable traction. Its timeless design makes it easy to pair with casual outfits, jeans, shorts, and streetwear. Available in different colorways, this sneaker is a stylish choice for everyday wear.', 'unisex', NULL, '1', '10', NULL, '2026-10-20 04:40:11', '2026-10-20 04:40:11', NULL, '0.00', NULL, NULL),
('60', '17', '5', 'Nike Sportswear Club Fleece Hoodie', 'nike-sportswear-club-fleece-hoodie-tenutnmjne', 'The Nike Sportswear Club Fleece Hoodie is designed for everyday comfort and casual style. Made with soft fleece fabric, it provides warmth and a comfortable feel. It features a classic pullover design, an adjustable hood, long sleeves, and a front kangaroo pocket for convenient storage. Perfect for everyday wear, school, outdoor activities, and relaxed occasions. Available in different colors and sizes to match your style.', 'unisex', NULL, '1', '10', NULL, '2026-10-20 04:49:50', '2026-10-20 04:49:50', NULL, '0.00', NULL, NULL);

-- --------------------------------------------------------
-- Table structure for table `reviews`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `order_item_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `rating` tinyint unsigned NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `seller_reply` text COLLATE utf8mb4_unicode_ci,
  `replied_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reviews_user_id_order_item_id_unique` (`user_id`,`order_item_id`),
  KEY `reviews_order_item_id_foreign` (`order_item_id`),
  KEY `reviews_product_id_foreign` (`product_id`),
  CONSTRAINT `reviews_order_item_id_foreign` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `reviews` (1 rows)
INSERT INTO `reviews` (`id`, `user_id`, `order_item_id`, `product_id`, `rating`, `comment`, `created_at`, `updated_at`, `seller_reply`, `replied_at`) VALUES
('16', '10', '15', '51', '5', 'sgjaydgaqjkd', '2026-10-19 07:34:56', '2026-10-19 07:34:56', NULL, NULL);

-- --------------------------------------------------------
-- Table structure for table `riders`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `riders`;
CREATE TABLE `riders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `logistics_provider_id` bigint unsigned NOT NULL,
  `vehicle_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plate_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','approved','rejected','suspended') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'approved',
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `area` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating` decimal(3,2) NOT NULL DEFAULT '5.00',
  `deliveries_today` int unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `riders_user_id_unique` (`user_id`),
  KEY `riders_logistics_provider_id_foreign` (`logistics_provider_id`),
  CONSTRAINT `riders_logistics_provider_id_foreign` FOREIGN KEY (`logistics_provider_id`) REFERENCES `logistics_providers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `riders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `riders` (10 rows)
INSERT INTO `riders` (`id`, `user_id`, `logistics_provider_id`, `vehicle_type`, `plate_no`, `status`, `rejection_reason`, `area`, `rating`, `deliveries_today`, `is_active`, `created_at`, `updated_at`) VALUES
('1', '7', '1', 'Motorcycle', 'ND-4921', 'approved', NULL, NULL, '5.00', '0', '1', '2026-09-21 06:03:58', '2026-09-21 06:03:58'),
('2', '22', '1', 'Motorcycle', 'NQ-4812', 'approved', NULL, 'Metro South (Laguna & NCR)', '4.95', '9', '1', '2026-10-11 06:51:54', '2026-10-19 07:32:12'),
('3', '23', '1', 'Motorcycle', 'MN-3421', 'approved', NULL, 'Laguna - Calamba', '4.82', '9', '0', '2026-10-11 06:51:55', '2026-10-14 05:21:15'),
('4', '24', '1', 'Motorcycle', 'LK-7829', 'suspended', NULL, 'Batangas - Lipa', '4.60', '0', '0', '2026-10-11 06:51:55', '2026-10-17 05:56:35'),
('5', '25', '1', 'Motorcycle', 'QR-1234', 'approved', NULL, 'Quezon - Lucena', '5.00', '0', '1', '2026-10-11 06:51:56', '2026-10-17 13:38:19'),
('6', '26', '1', 'Motorcycle', 'PH-5678', 'approved', NULL, 'Laguna - San Pablo', '5.00', '0', '1', '2026-10-11 06:51:56', '2026-10-17 13:38:12'),
('7', '27', '1', 'Bicycle', 'N/A', 'approved', NULL, NULL, '5.00', '0', '0', '2026-10-11 06:51:57', '2026-10-19 07:02:46'),
('8', '40', '1', 'car', '123ABC', 'approved', NULL, NULL, '5.00', '0', '1', '2026-10-17 13:05:56', '2026-10-17 13:09:15'),
('9', '43', '1', 'motorcycle', '123BDA', 'approved', NULL, NULL, '5.00', '1', '1', '2026-10-18 07:17:46', '2026-10-18 08:16:29'),
('10', '44', '1', 'motorcycle', 'RYBN123', 'approved', NULL, NULL, '5.00', '0', '1', '2026-10-18 07:22:44', '2026-10-18 07:24:50');

-- --------------------------------------------------------
-- Table structure for table `role_user`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `role_user`;
CREATE TABLE `role_user` (
  `role_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`role_id`,`user_id`),
  KEY `role_user_user_id_foreign` (`user_id`),
  CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `role_user` (54 rows)
INSERT INTO `role_user` (`role_id`, `user_id`, `created_at`, `updated_at`) VALUES
('1', '1', NULL, NULL),
('1', '2', NULL, NULL),
('1', '3', NULL, NULL),
('1', '4', NULL, NULL),
('1', '8', NULL, NULL),
('1', '9', NULL, NULL),
('1', '10', NULL, NULL),
('1', '11', NULL, NULL),
('1', '29', NULL, NULL),
('1', '30', NULL, NULL),
('1', '32', NULL, NULL),
('1', '33', NULL, NULL),
('1', '34', NULL, NULL),
('1', '35', NULL, NULL),
('1', '39', NULL, NULL),
('1', '45', NULL, NULL),
('1', '48', NULL, NULL),
('2', '6', NULL, NULL),
('2', '12', NULL, NULL),
('2', '13', NULL, NULL),
('2', '14', NULL, NULL),
('2', '15', NULL, NULL),
('2', '16', NULL, NULL),
('2', '17', NULL, NULL),
('2', '18', NULL, NULL),
('2', '19', NULL, NULL),
('2', '20', NULL, NULL),
('2', '21', NULL, NULL),
('2', '28', NULL, NULL),
('2', '31', NULL, NULL),
('2', '36', NULL, NULL),
('2', '37', NULL, NULL),
('2', '38', NULL, NULL),
('2', '41', NULL, NULL),
('2', '42', NULL, NULL),
('2', '47', NULL, NULL),
('3', '7', NULL, NULL),
('3', '11', NULL, NULL),
('3', '32', NULL, NULL),
('3', '33', NULL, NULL),
('3', '46', NULL, NULL),
('4', '7', NULL, NULL),
('4', '22', NULL, NULL),
('4', '23', NULL, NULL),
('4', '24', NULL, NULL),
('4', '25', NULL, NULL),
('4', '26', NULL, NULL),
('4', '27', NULL, NULL),
('4', '40', NULL, NULL),
('5', '5', NULL, NULL),
('6', '22', NULL, NULL),
('6', '40', NULL, NULL),
('6', '43', NULL, NULL),
('6', '44', NULL, NULL);

-- --------------------------------------------------------
-- Table structure for table `roles`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `roles` (6 rows)
INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES
('1', 'buyer', '2026-09-21 06:03:56', '2026-09-21 06:03:56'),
('2', 'seller', '2026-09-21 06:03:56', '2026-09-21 06:03:56'),
('3', 'logistics', '2026-09-21 06:03:56', '2026-09-21 06:03:56'),
('4', 'rider', '2026-09-21 06:03:56', '2026-09-21 06:03:56'),
('5', 'admin', '2026-09-21 06:03:56', '2026-09-21 06:03:56'),
('6', 'courier', '2026-10-16 10:08:22', '2026-10-16 10:08:22');

-- --------------------------------------------------------
-- Table structure for table `seller_followers`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `seller_followers`;
CREATE TABLE `seller_followers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `seller_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seller_followers_seller_id_user_id_unique` (`seller_id`,`user_id`),
  KEY `seller_followers_user_id_foreign` (`user_id`),
  CONSTRAINT `seller_followers_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `seller_followers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `seller_messages`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `seller_messages`;
CREATE TABLE `seller_messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `seller_order_id` bigint unsigned NOT NULL,
  `sender_id` bigint unsigned NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `seller_messages_sender_id_foreign` (`sender_id`),
  KEY `seller_messages_seller_order_id_created_at_index` (`seller_order_id`,`created_at`),
  CONSTRAINT `seller_messages_seller_order_id_foreign` FOREIGN KEY (`seller_order_id`) REFERENCES `seller_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `seller_messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `seller_messages` (7 rows)
INSERT INTO `seller_messages` (`id`, `seller_order_id`, `sender_id`, `body`, `delivered_at`, `read_at`, `created_at`, `updated_at`) VALUES
('1', '9', '1', 'Hi Kuya rider! Please leave the package with the building concierge if I am in a meeting.', NULL, NULL, '2026-10-16 10:08:22', '2026-10-16 10:08:22'),
('2', '9', '22', 'Noted ma\'am! Will request an acknowledgment receipt from the lobby guard upon arrival.', NULL, '2026-10-19 04:12:56', '2026-10-16 10:08:22', '2026-10-19 04:12:56'),
('3', '17', '42', 'eyyeyeq', '2026-10-20 04:53:28', '2026-10-19 06:55:51', '2026-10-19 06:55:28', '2026-10-20 04:53:28'),
('4', '20', '48', 'ayaw gumana ng bawas sa quanity, sa cart', NULL, NULL, '2026-10-20 04:05:46', '2026-10-20 04:05:46'),
('5', '20', '48', 'hello', NULL, NULL, '2026-10-20 04:08:41', '2026-10-20 04:08:41'),
('6', '17', '10', 'jotjrt', NULL, NULL, '2026-10-20 04:31:07', '2026-10-20 04:31:07'),
('7', '17', '10', 'tytyt', NULL, NULL, '2026-10-20 04:53:51', '2026-10-20 04:53:51');

-- --------------------------------------------------------
-- Table structure for table `seller_orders`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `seller_orders`;
CREATE TABLE `seller_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `seller_id` bigint unsigned NOT NULL,
  `logistics_provider_id` bigint unsigned DEFAULT NULL,
  `subtotal_minor` bigint unsigned NOT NULL,
  `shipping_fee_minor` bigint unsigned NOT NULL DEFAULT '0',
  `shipping_discount_minor` bigint unsigned NOT NULL DEFAULT '0',
  `voucher_discount_minor` bigint unsigned NOT NULL DEFAULT '0',
  `commission_minor` bigint unsigned NOT NULL DEFAULT '0',
  `status` enum('pending','accepted','packed','ready_to_ship','shipped','delivered','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `delivered_at` timestamp NULL DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `cancellation_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cancellation_reason` text COLLATE utf8mb4_unicode_ci,
  `cancellation_requested_at` timestamp NULL DEFAULT NULL,
  `cancellation_reviewed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seller_orders_order_id_seller_id_unique` (`order_id`,`seller_id`),
  KEY `seller_orders_logistics_provider_id_foreign` (`logistics_provider_id`),
  KEY `seller_orders_seller_id_status_index` (`seller_id`,`status`),
  KEY `seller_orders_cancellation_status_index` (`cancellation_status`),
  CONSTRAINT `seller_orders_logistics_provider_id_foreign` FOREIGN KEY (`logistics_provider_id`) REFERENCES `logistics_providers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `seller_orders_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `seller_orders_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `seller_orders` (22 rows)
INSERT INTO `seller_orders` (`id`, `order_id`, `seller_id`, `logistics_provider_id`, `subtotal_minor`, `shipping_fee_minor`, `shipping_discount_minor`, `voucher_discount_minor`, `commission_minor`, `status`, `delivered_at`, `note`, `created_at`, `updated_at`, `cancellation_status`, `cancellation_reason`, `cancellation_requested_at`, `cancellation_reviewed_at`) VALUES
('1', '1', '2', '1', '28000', '6000', '0', '0', '2800', 'pending', NULL, NULL, '2026-10-10 06:21:01', '2026-10-10 06:38:26', NULL, NULL, NULL, NULL),
('2', '2', '1', '1', '899000', '6000', '0', '0', '89900', 'shipped', NULL, 'Sony WH-1000XM5 Headphones x1', '2026-10-11 06:51:57', '2026-10-11 06:51:57', NULL, NULL, NULL, NULL),
('3', '3', '2', '1', '250000', '6000', '0', '0', '25000', 'shipped', NULL, 'SK-II Facial Essence 160ml x1', '2026-10-11 06:51:57', '2026-10-11 06:51:57', NULL, NULL, NULL, NULL),
('4', '4', '1', '1', '549900', '6000', '0', '0', '54990', 'shipped', NULL, 'Nike Air Max 270 Size 42 x1', '2026-10-11 06:51:57', '2026-10-11 06:51:57', NULL, NULL, NULL, NULL),
('5', '5', '3', '1', '250000', '6000', '0', '0', '25000', 'delivered', '2026-10-10 06:51:57', 'Adidas Running Shoes x1, Sports Bag x1', '2026-10-11 06:51:57', '2026-10-11 06:51:57', NULL, NULL, NULL, NULL),
('6', '6', '1', '1', '8999000', '6000', '0', '0', '899900', 'shipped', NULL, 'Samsung 65\" OLED TV x1', '2026-10-11 06:51:57', '2026-10-11 06:51:57', NULL, NULL, NULL, NULL),
('7', '7', '2', '1', '129900', '6000', '0', '0', '12990', 'ready_to_ship', NULL, 'Skincare Bundle Set x1', '2026-10-11 06:51:57', '2026-10-11 06:51:57', NULL, NULL, NULL, NULL),
('8', '8', '3', '1', '129900', '1000', '0', '0', '12990', 'pending', NULL, NULL, '2026-10-14 08:14:38', '2026-10-14 08:14:38', NULL, NULL, NULL, NULL),
('9', '9', '1', '1', '450000', '6500', '0', '0', '45000', 'delivered', '2026-10-18 08:16:29', 'Sony Wireless Headphones (Midnight Blue) x1', '2026-10-16 10:08:22', '2026-10-18 08:16:29', NULL, NULL, NULL, NULL),
('10', '10', '1', '1', '250000', '6500', '0', '0', '25000', 'shipped', NULL, 'Mechanical Gaming Keyboard & RGB Mousepad x1', '2026-10-16 10:08:22', '2026-10-16 10:08:22', NULL, NULL, NULL, NULL),
('11', '11', '1', '1', '185000', '6500', '0', '0', '18500', 'shipped', NULL, 'Ergonomic Mesh Office Chair Cushion x2', '2026-10-16 10:08:22', '2026-10-16 10:08:22', NULL, NULL, NULL, NULL),
('12', '12', '1', '1', '125000', '6500', '0', '0', '12500', 'delivered', '2026-10-16 07:08:22', 'Smart LED Desk Lamp x1', '2026-10-16 10:08:22', '2026-10-16 10:08:22', NULL, NULL, NULL, NULL),
('13', '13', '1', '1', '150000', '6500', '0', '0', '15000', 'delivered', '2026-10-16 08:08:22', 'Bluetooth Portable Speaker x1', '2026-10-16 10:08:22', '2026-10-16 10:08:22', NULL, NULL, NULL, NULL),
('14', '14', '1', '1', '285000', '6500', '0', '0', '28500', 'delivered', '2026-10-16 09:08:22', 'Thermal Receipt Printer x1', '2026-10-16 10:08:22', '2026-10-16 10:08:22', NULL, NULL, NULL, NULL),
('15', '15', '3', '1', '89000', '6000', '0', '0', '8900', 'pending', NULL, NULL, '2026-10-17 09:26:28', '2026-10-17 10:48:33', 'pending', 'I placed a duplicate order', '2026-10-17 10:48:33', NULL),
('16', '16', '3', '1', '89000', '6000', '0', '0', '8900', 'pending', NULL, NULL, '2026-10-17 10:50:46', '2026-10-17 11:12:04', 'pending', 'I placed a duplicate order', '2026-10-17 11:12:04', NULL),
('17', '17', '18', '1', '49900', '6000', '0', '0', '4990', 'pending', NULL, NULL, '2026-10-19 06:52:55', '2026-10-19 06:52:55', NULL, NULL, NULL, NULL),
('18', '18', '18', '1', '1169100', '1000', '0', '0', '116910', 'delivered', '2026-10-19 07:32:12', 'Requested pickup: 2026-10-19
handle with care', '2026-10-19 07:20:20', '2026-10-19 07:32:12', NULL, NULL, NULL, NULL),
('19', '19', '18', '1', '1169100', '1000', '0', '0', '116910', 'pending', NULL, NULL, '2026-10-19 07:30:00', '2026-10-19 07:30:00', NULL, NULL, NULL, NULL),
('20', '20', '17', '1', '177289840', '1000', '5000', '0', '17728984', 'pending', NULL, NULL, '2026-10-20 04:05:09', '2026-10-20 04:05:09', NULL, NULL, NULL, NULL),
('21', '21', '17', '1', '102821600', '1000', '5000', '0', '10282160', 'pending', NULL, NULL, '2026-10-20 04:18:37', '2026-10-20 04:57:43', 'pending', 'Other reason: ayaw kona', '2026-10-20 04:57:43', NULL),
('22', '22', '17', '1', '91154400', '1000', '5000', '0', '9115440', 'pending', NULL, NULL, '2026-10-20 04:19:24', '2026-10-20 04:19:24', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------
-- Table structure for table `sellers`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `sellers`;
CREATE TABLE `sellers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `logo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banner_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','approved','suspended','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `commission_bps` int unsigned NOT NULL DEFAULT '1000',
  `pickup_address_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sellers_slug_unique` (`slug`),
  KEY `sellers_user_id_foreign` (`user_id`),
  KEY `sellers_pickup_address_id_foreign` (`pickup_address_id`),
  CONSTRAINT `sellers_pickup_address_id_foreign` FOREIGN KEY (`pickup_address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sellers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `sellers` (19 rows)
INSERT INTO `sellers` (`id`, `user_id`, `name`, `slug`, `description`, `logo_path`, `banner_path`, `status`, `rejection_reason`, `commission_bps`, `pickup_address_id`, `created_at`, `updated_at`) VALUES
('1', '6', 'TechZone Official Mall', 'techzone-official-mall', 'Your premier destination for authentic wireless earbuds, smartwatches, and premium accessories.', 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=200&h=200&fit=crop&q=80', 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=1200&h=400&fit=crop&q=80', 'approved', NULL, '1000', NULL, '2026-09-21 06:03:57', '2026-09-21 06:03:57'),
('2', '12', 'Hearth & Home', 'catalog-hearth-home', 'Catalog store for Hearth & Home.', NULL, NULL, 'approved', NULL, '1000', NULL, '2026-10-10 06:38:23', '2026-10-10 06:38:23'),
('3', '13', 'Circuit Hub', 'catalog-circuit-hub', 'Catalog store for Circuit Hub.', NULL, NULL, 'approved', NULL, '1000', NULL, '2026-10-10 06:38:23', '2026-10-10 06:38:23'),
('4', '14', 'Sole Street', 'catalog-sole-street', 'Catalog store for Sole Street.', NULL, NULL, 'approved', NULL, '1000', NULL, '2026-10-10 06:38:24', '2026-10-10 06:38:24'),
('5', '15', 'Everyday Style', 'catalog-everyday-style', 'Catalog store for Everyday Style.', NULL, NULL, 'approved', NULL, '1000', NULL, '2026-10-10 06:38:24', '2026-10-10 06:38:24'),
('6', '16', 'Bloom Beauty', 'catalog-bloom-beauty', 'Catalog store for Bloom Beauty.', NULL, NULL, 'approved', NULL, '1000', NULL, '2026-10-10 06:38:24', '2026-10-10 06:38:24'),
('7', '17', 'Little Sprouts', 'catalog-little-sprouts', 'Catalog store for Little Sprouts.', NULL, NULL, 'approved', NULL, '1000', NULL, '2026-10-10 06:38:25', '2026-10-10 06:38:25'),
('8', '18', 'Handy Home Tools', 'catalog-handy-home', 'Catalog store for Handy Home Tools.', NULL, NULL, 'approved', NULL, '1000', NULL, '2026-10-10 06:38:25', '2026-10-10 06:38:25'),
('9', '19', 'Happy Paws', 'catalog-happy-paws', 'Catalog store for Happy Paws.', NULL, NULL, 'approved', NULL, '1000', NULL, '2026-10-10 06:38:26', '2026-10-10 06:38:26'),
('10', '20', 'Paper & Palette', 'catalog-paper-palette', 'Catalog store for Paper & Palette.', NULL, NULL, 'approved', NULL, '1000', NULL, '2026-10-10 06:38:26', '2026-10-10 06:38:26'),
('11', '21', 'Auto Essentials', 'catalog-auto-essentials', 'Catalog store for Auto Essentials.', NULL, NULL, 'approved', NULL, '1000', NULL, '2026-10-10 06:38:26', '2026-10-10 06:38:26'),
('12', '28', 'happyskinphil', 'happyskinphil', 'Official happyskinphil Store - Authentic skincare and makeup essentials.', NULL, NULL, 'approved', NULL, '1000', '6', '2026-10-11 07:23:16', '2026-10-11 07:23:16'),
('13', '31', 'Maria Clara Santos', 'maria-clara-santos', NULL, NULL, NULL, 'pending', NULL, '1000', NULL, '2026-10-14 00:56:31', '2026-10-14 00:56:31'),
('14', '36', 'Maria Clara Handicrafts PH', 'anya-garcia', NULL, NULL, NULL, 'approved', NULL, '1000', NULL, '2026-10-14 07:37:05', '2026-10-17 10:05:54'),
('15', '37', 'Adidas Official Store', 'adidas', 'Official Adidas Store for authentic running shoes, sneakers, and athletic footwear.', 'https://images.unsplash.com/photo-1518002171953-a080ee817e1f?w=200&h=200&fit=crop&q=80', 'https://images.unsplash.com/photo-1556906781-9a412961c28c?w=1200&h=400&fit=crop&q=80', 'approved', NULL, '1000', '9', '2026-10-15 13:25:00', '2026-10-15 13:25:00'),
('16', '38', 'APLI', 'apli', NULL, NULL, NULL, 'approved', NULL, '1000', '10', '2026-10-15 14:48:24', '2026-10-15 15:50:16'),
('17', '41', 'Nike Official Store', 'nike', 'Official Nike Store for authentic running shoes, sportswear, and athletic lifestyle gear.', 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=200&h=200&fit=crop&q=80', 'https://images.unsplash.com/photo-1556906781-9a412961c28c?w=1200&h=400&fit=crop&q=80', 'approved', NULL, '1000', '24', '2026-10-18 03:25:51', '2026-10-18 03:25:51'),
('18', '42', 'Logitech Official Store', 'logitech', 'Official Logitech Store for high-performance gaming gear, mechanical keyboards, precision mice, webcams, and workspace essentials.', 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=200&h=200&fit=crop&q=80', 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=1200&h=400&fit=crop&q=80', 'approved', NULL, '1000', '25', '2026-10-18 05:23:12', '2026-10-18 05:23:12'),
('19', '47', 'Gaabor Store', 'gaabor', 'Small home and kitchen appliances for everyday living.', NULL, NULL, 'approved', NULL, '1000', '26', '2026-10-18 12:04:55', '2026-10-18 12:04:55');

-- --------------------------------------------------------
-- Table structure for table `sessions`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `shipments`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `shipments`;
CREATE TABLE `shipments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `seller_order_id` bigint unsigned NOT NULL,
  `logistics_provider_id` bigint unsigned NOT NULL,
  `rider_id` bigint unsigned DEFAULT NULL,
  `tracking_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('unassigned','assigned','picked_up','in_transit','out_for_delivery','delivered','failed','returned') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unassigned',
  `delivery_area` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_status` enum('unsorted','sorted','assigned') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unsorted',
  `weight_kg` decimal(8,2) NOT NULL DEFAULT '1.00',
  `received_at` timestamp NULL DEFAULT NULL,
  `sorted_at` timestamp NULL DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `fee_minor` bigint unsigned NOT NULL DEFAULT '0',
  `cod_amount_minor` bigint unsigned NOT NULL DEFAULT '0',
  `cod_collected` tinyint(1) NOT NULL DEFAULT '0',
  `attempts` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shipments_seller_order_id_unique` (`seller_order_id`),
  UNIQUE KEY `shipments_tracking_code_unique` (`tracking_code`),
  KEY `shipments_logistics_provider_id_foreign` (`logistics_provider_id`),
  KEY `shipments_rider_id_foreign` (`rider_id`),
  CONSTRAINT `shipments_logistics_provider_id_foreign` FOREIGN KEY (`logistics_provider_id`) REFERENCES `logistics_providers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shipments_rider_id_foreign` FOREIGN KEY (`rider_id`) REFERENCES `riders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `shipments_seller_order_id_foreign` FOREIGN KEY (`seller_order_id`) REFERENCES `seller_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `shipments` (22 rows)
INSERT INTO `shipments` (`id`, `seller_order_id`, `logistics_provider_id`, `rider_id`, `tracking_code`, `status`, `delivery_area`, `sort_status`, `weight_kg`, `received_at`, `sorted_at`, `assigned_at`, `fee_minor`, `cod_amount_minor`, `cod_collected`, `attempts`, `created_at`, `updated_at`) VALUES
('1', '1', '1', NULL, 'CTZ-VEM2ANKBMO', 'unassigned', NULL, 'unsorted', '1.00', '2026-10-17 07:26:43', NULL, NULL, '6000', '0', '0', '0', '2026-10-10 06:21:01', '2026-10-17 07:26:43'),
('2', '2', '1', '2', 'CTZY-20261011-0001', 'in_transit', 'Laguna - Santa Cruz', 'sorted', '1.50', '2026-10-11 01:51:57', '2026-10-11 02:51:57', '2026-10-11 03:51:57', '6000', '899000', '0', '0', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('3', '3', '1', '3', 'CTZY-20261011-0002', 'out_for_delivery', 'Laguna - San Pablo', 'sorted', '0.40', '2026-10-11 00:51:57', '2026-10-11 01:51:57', '2026-10-11 02:51:57', '6000', '0', '0', '0', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('4', '4', '1', '9', 'CTZY-20261011-0003', 'picked_up', NULL, 'unsorted', '1.20', '2026-10-11 04:51:57', NULL, NULL, '6000', '549900', '0', '0', '2026-10-11 06:51:57', '2026-10-18 08:16:13'),
('5', '5', '1', '4', 'CTZY-20261011-0004', 'delivered', 'Batangas - Lipa', 'assigned', '2.80', '2026-10-09 22:51:57', '2026-10-09 23:51:57', '2026-10-10 00:51:57', '6000', '0', '0', '0', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('6', '6', '1', '9', 'CTZY-20261011-0005', 'picked_up', 'Laguna - Los Baños', 'assigned', '28.50', '2026-10-11 05:51:57', '2026-10-14 05:12:24', '2026-10-17 06:05:01', '6000', '8999000', '0', '0', '2026-10-11 06:51:57', '2026-10-18 08:16:13'),
('7', '7', '1', '3', 'CTZY-20261011-0006', 'failed', 'Laguna - Calamba', 'sorted', '0.90', '2026-10-10 01:51:57', '2026-10-10 02:51:57', '2026-10-10 03:51:57', '6000', '129900', '0', '1', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('8', '8', '1', NULL, 'CTZ-ZQ4U0FQ0UU', 'unassigned', NULL, 'unsorted', '1.00', NULL, NULL, NULL, '1000', '0', '0', '0', '2026-10-14 08:14:38', '2026-10-14 08:14:38'),
('9', '9', '1', '9', 'CTZY-RD-884901', 'delivered', 'Metro South', 'assigned', '1.80', '2026-10-16 06:08:22', '2026-10-16 07:08:22', '2026-10-17 06:09:08', '6500', '450000', '1', '0', '2026-10-16 10:08:22', '2026-10-18 08:16:29'),
('10', '10', '1', '2', 'CTZY-RD-884902', 'out_for_delivery', 'Metro South', 'sorted', '1.80', '2026-10-16 06:08:22', '2026-10-16 07:08:22', '2026-10-16 08:08:22', '6500', '0', '0', '0', '2026-10-16 10:08:22', '2026-10-16 10:08:22'),
('11', '11', '1', '2', 'CTZY-RD-884903', 'in_transit', 'Metro South', 'sorted', '1.80', '2026-10-16 06:08:22', '2026-10-16 07:08:22', '2026-10-16 08:08:22', '6500', '185000', '0', '0', '2026-10-16 10:08:22', '2026-10-16 10:08:22'),
('12', '12', '1', '2', 'CTZY-RD-771021', 'delivered', 'Metro South', 'sorted', '1.20', '2026-10-16 04:08:22', '2026-10-16 05:08:22', '2026-10-16 06:08:22', '6500', '125000', '1', '1', '2026-10-16 10:08:22', '2026-10-16 10:08:22'),
('13', '13', '1', '2', 'CTZY-RD-771022', 'delivered', 'Metro South', 'sorted', '1.20', '2026-10-16 04:08:22', '2026-10-16 05:08:22', '2026-10-16 06:08:22', '6500', '0', '0', '1', '2026-10-16 10:08:22', '2026-10-16 10:08:22'),
('14', '14', '1', '2', 'CTZY-RD-771023', 'delivered', 'Metro South', 'sorted', '1.20', '2026-10-16 04:08:22', '2026-10-16 05:08:22', '2026-10-16 06:08:22', '6500', '285000', '1', '1', '2026-10-16 10:08:22', '2026-10-16 10:08:22'),
('15', '15', '1', NULL, 'CTZ-DJJRQOPUA2', 'unassigned', NULL, 'unsorted', '1.00', NULL, NULL, NULL, '6000', '95000', '0', '0', '2026-10-17 09:26:28', '2026-10-17 09:26:28'),
('16', '16', '1', NULL, 'CTZ-MZACS6BXZK', 'unassigned', NULL, 'unsorted', '1.00', NULL, NULL, NULL, '6000', '95000', '0', '0', '2026-10-17 10:50:46', '2026-10-17 10:50:46'),
('17', '17', '1', NULL, 'CTZ-FSKCBEJGHG', 'unassigned', NULL, 'unsorted', '1.00', NULL, NULL, NULL, '6000', '55900', '0', '0', '2026-10-19 06:52:55', '2026-10-19 06:52:55'),
('18', '18', '1', '2', 'CTZ-IS9QZIXYUJ', 'delivered', 'Laguna - Santa Cruz', 'assigned', '1.00', '2026-10-19 07:24:39', '2026-10-19 07:25:00', '2026-10-19 07:25:31', '1000', '1170100', '1', '0', '2026-10-19 07:20:20', '2026-10-19 07:32:12'),
('19', '19', '1', NULL, 'CTZ-PFBXQU80WR', 'unassigned', NULL, 'unsorted', '1.00', NULL, NULL, NULL, '1000', '1170100', '0', '0', '2026-10-19 07:30:00', '2026-10-19 07:30:00'),
('20', '20', '1', NULL, 'CTZ-BMUTXJYLSA', 'unassigned', NULL, 'unsorted', '1.00', NULL, NULL, NULL, '6000', '177290840', '0', '0', '2026-10-20 04:05:09', '2026-10-20 04:05:09'),
('21', '21', '1', NULL, 'CTZ-PO7F0QBF0B', 'unassigned', NULL, 'unsorted', '1.00', NULL, NULL, NULL, '6000', '102822600', '0', '0', '2026-10-20 04:18:37', '2026-10-20 04:18:37'),
('22', '22', '1', NULL, 'CTZ-1YDYLYJRFK', 'unassigned', NULL, 'unsorted', '1.00', NULL, NULL, NULL, '6000', '91155400', '0', '0', '2026-10-20 04:19:24', '2026-10-20 04:19:24');

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_initial` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sex` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `age` tinyint unsigned DEFAULT NULL,
  `id_photo` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unverified',
  `id_rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `id_verified_at` timestamp NULL DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `google_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `street_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `region` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `province` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `barangay` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tin_number` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dti_permit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `line_of_business` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'buyer',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `is_suspended` tinyint(1) NOT NULL DEFAULT '0',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `seller_type` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sec_certificate` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bir_certificate` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_google_id_unique` (`google_id`),
  UNIQUE KEY `users_telephone_unique` (`telephone`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `users` (48 rows)
INSERT INTO `users` (`id`, `name`, `middle_initial`, `sex`, `birthday`, `age`, `id_photo`, `id_type`, `id_number`, `id_status`, `id_rejection_reason`, `id_verified_at`, `email`, `google_id`, `phone`, `telephone`, `street_address`, `address`, `region`, `province`, `city`, `barangay`, `postal_code`, `business_name`, `tin_number`, `dti_permit`, `line_of_business`, `email_verified_at`, `password`, `role`, `avatar`, `status`, `is_suspended`, `remember_token`, `created_at`, `updated_at`, `seller_type`, `sec_certificate`, `bir_certificate`) VALUES
('1', 'jess Pambago', NULL, 'Female', '2005-12-05', '20', NULL, NULL, NULL, 'unverified', NULL, NULL, 'jessicapambago27@gmail.com', '102943877745797449792', '09773587409', NULL, '1011, purok 4', '1011, purok 4, Brgy. Masapang, Victoria, Laguna, IV-A, 4011', 'IV-A', 'Laguna', 'Victoria', 'Masapang', '4011', NULL, NULL, NULL, NULL, '2026-09-19 09:39:48', '$2y$12$3nOKEW1najIzdZqV2g.KSOp2TnIrXnnwN.m.cXjZeU9htWcSh28tW', 'buyer', 'https://lh3.googleusercontent.com/a/ACg8ocKRnO3z--zfu4NoW4iXG9RuYJ4W7Hc5Ty8Q9LfGYo8PptOyDiQ=s96-c', 'active', '0', 'EB2rd27b7ZX1drhKddrYRIscH45FPQyBmAudrAz2FfE3gEjxYXVIxKnjmCyE', '2026-09-06 10:07:45', '2026-09-19 09:39:48', NULL, NULL, NULL),
('2', 'Jessie Bajamundi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'bajamundijessie2@gmail.com', '105267381396672766135', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-10 05:27:55', '$2y$12$T1E7WJ09XHkR69TZhisBfuxO87Qu4x8gfTpp3e1iHINstiNoyAz/y', 'buyer', 'https://lh3.googleusercontent.com/a/ACg8ocJpogT-VfJhnw40Xyj5tD46nnZ8k-cTondgVTm5pl7QXgtv-FE=s96-c', 'active', '0', 'UyY0OQ7Jb0ucp8Xl5kNmhb9va56wxMFudR9dXmLRwJhMmNPFvswsit0vUbWy', '2026-09-19 09:38:11', '2026-10-10 05:27:55', NULL, NULL, NULL),
('3', 'Leonardo, Tiffany Joy O.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'tiffany.leonardolspu@gmail.com', '102856395965406016321', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-10 05:27:55', '$2y$12$2rPlMMwYfnaULGFzuFg7FOJSONDLg43g3jm/W13JnB9onE.Bl3qb2', 'buyer', 'https://lh3.googleusercontent.com/a/ACg8ocJIBmZGdR_My3EM3rpyD5hHikz_j0zobdBv9aAD8Ts4A9fNJQzz=s96-c', 'active', '0', 'ZNjJJ1HMLZAf0v4SnwYg4eluaaNFoDWIwngBOmrceb84vLjJnvVc2gMw88id', '2026-09-19 10:08:30', '2026-10-10 05:27:55', NULL, NULL, NULL),
('4', 'Pajavera, Nhieckaella Ashley R.', NULL, NULL, '2005-11-14', '20', NULL, NULL, NULL, 'unverified', NULL, NULL, 'nhieckaella05@gmail.com', '109760982595777107804', '09152608445', NULL, 'LSPU Campus', 'LSPU Campus, Brgy. Bagumbayan, Santa Cruz, Laguna, 4009', 'IV-A', 'Laguna', 'Santa Cruz', 'Bagumbayan', '4009', NULL, NULL, NULL, NULL, '2026-10-10 05:27:55', '$2y$12$1rq7GlIrNoH9D8/5.maRFeJpY7YL7dtXCSG7IW0ETH.ZnmrTJuPoK', 'buyer', 'https://lh3.googleusercontent.com/a/ACg8ocLp_Yq1EHEIBYc28poVjnMKVg-6M7ZvC0FVN-PMoSsQMcZQcJpw=s96-c', 'active', '0', '0lLFCsrevfJuhNbPDqzofXbBiZtuETDCiCb9D4iIKAPOegVjRiVXGSetQVQF', '2026-09-19 10:08:50', '2026-10-14 03:21:03', NULL, NULL, NULL),
('5', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'administrationa570@gmail.com', NULL, '09170000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$K.ZOCaZwXJ/eeTCXb4Qj.u.enxvmlXYylGlLZ2D4NRBNmK2QguLOy', 'admin', NULL, 'active', '0', 'G2rrf4nboScirw11pVayrT94ysT6S834DMLLcT6GOCFD2Bnr5UxrzSjKF6aR', '2026-09-19 10:14:45', '2026-09-19 10:14:45', NULL, NULL, NULL),
('6', 'Official Tech Store', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'seller@shopee.ph', NULL, '09171112222', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$2ziGBNBXL5F54/ky87PlK.e8jLoj7laH6f8b10gU7zbvsE2gwE1ca', 'seller', NULL, 'active', '0', NULL, '2026-09-19 10:14:45', '2026-10-02 10:12:52', NULL, NULL, NULL),
('7', 'SPX Rider - Juan', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'courier@shopee.ph', NULL, '09173334444', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$T2HrurgfgDtHGEQdJ37fV.kZS64ItiJx7PVRZVMy.InsNuCbPv4Oi', 'courier', NULL, 'active', '0', NULL, '2026-09-19 10:14:46', '2026-09-19 10:14:46', NULL, NULL, NULL),
('8', 'Maria Dela Cruz', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'buyer@shopee.ph', NULL, '09175556666', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$6TEzJ1W3R1rvfGcDjJT0EujRLAaHR.qJs3En7aRtwnI2PTHkUlSDC', 'buyer', NULL, 'active', '0', NULL, '2026-09-19 10:14:46', '2026-09-19 10:14:46', NULL, NULL, NULL),
('9', 'Jan Reyben Del Mundo', NULL, 'Male', '2005-09-20', '21', 'id_documents/H7b1P328TEM0waoFW8KJXDSrxvVV6eVRuP5oT9m4.png', 'Philippine National ID', '1234-5678-9101-1213', 'pending', NULL, NULL, 'reybendelmundo2005@gmail.com', '100001932505618334922', NULL, NULL, '2100 San Luis Street', '2100 San Luis Street, Brgy. Putho Tuntungin, Los Baños, Laguna, 4030', 'IV-A', 'Laguna', 'Los Baños', 'Putho Tuntungin', '4030', NULL, NULL, NULL, NULL, '2026-10-10 05:27:55', '$2y$12$nIUFGFX5nFSwpK89mDQOlu7MNR.bgexpjXwiQW8m7Du7KcPoLIPFC', 'buyer', 'https://lh3.googleusercontent.com/a/ACg8ocLqMeWtsRY85r-QPVe8hW6nuusyr01ufXW6Au2Kj21L0MeXLdTc=s96-c', 'active', '0', 'bnis0yGXY7ewm6krDjwLetJ87df1dIGDK0mwf89aJZup4IuLuac3b4wHL0WZ', '2026-09-19 10:22:32', '2026-10-17 09:24:27', NULL, NULL, NULL),
('10', 'Jessica Pambago', NULL, NULL, NULL, NULL, NULL, 'National ID', '1234-5678-9012', 'verified', NULL, '2026-10-10 05:54:17', 'jessica.p.bajamundi@gmail.com', '105023496569977781629', NULL, NULL, '1011, purok 4', '1011, purok 4, Brgy. Masapang, Victoria, Laguna, 4011', 'IV-A', 'Laguna', 'Victoria', 'Masapang', '4011', NULL, NULL, NULL, NULL, '2026-10-10 05:27:55', '$2y$12$FKxTiUCZPSseqM/aXmkcO.2CQNKw.WuJl3Tg5ZIVH12mIsvADwQLu', 'buyer', 'https://lh3.googleusercontent.com/a/ACg8ocLySMhHn9faFZJycPSeuiPaf_czFDlfAJT3lJi9SexiP_SZ8Cml=s96-c', 'active', '0', 'Qa4cdBeVFsoqBF3RvpbEpQVL7rm1arRQqBmjIAWFn8CwPYs8mnWhjuycgp4D', '2026-09-23 02:11:29', '2026-10-10 06:04:25', NULL, NULL, NULL),
('11', 'Metro South Sorting & Fulfillment Hub', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'logistics@cartzy.ph', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$cOiZVv5Qz/eNPV68uwG3quimoGQmgy0cTA/GijiXUfHnhSP1mmO5W', 'logistics', NULL, 'active', '0', NULL, '2026-10-04 06:06:10', '2026-10-04 06:06:10', NULL, NULL, NULL),
('12', 'Hearth & Home', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'hearth-home@catalog.cartzy.invalid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$qf.mju.fJHzAFIQYZcVLkuVq5YhDQg6aZVpUYzY7Eves1I1gcwivi', 'seller', NULL, 'active', '0', NULL, '2026-10-10 06:38:23', '2026-10-10 06:38:23', NULL, NULL, NULL),
('13', 'Circuit Hub', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'circuit-hub@catalog.cartzy.invalid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$Q/sFeuONs0jpEdPIRQ3FH.I1fwlkr6A1icLVXPodJthhGABSLpefG', 'seller', NULL, 'active', '0', NULL, '2026-10-10 06:38:23', '2026-10-10 06:38:23', NULL, NULL, NULL),
('14', 'Sole Street', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'sole-street@catalog.cartzy.invalid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$lTSUOasrhgNjVlNie57k1utm79FmnPRW2B0O/Iv5VHzpeAnMXwCwW', 'seller', NULL, 'active', '0', NULL, '2026-10-10 06:38:24', '2026-10-10 06:38:24', NULL, NULL, NULL),
('15', 'Everyday Style', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'everyday-style@catalog.cartzy.invalid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$pfQq.obJAtYIHXVtvNUkW.jaFoxDAPZv/rS3y4JLZ8PQNlJlJeCZK', 'seller', NULL, 'active', '0', NULL, '2026-10-10 06:38:24', '2026-10-10 06:38:24', NULL, NULL, NULL),
('16', 'Bloom Beauty', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'bloom-beauty@catalog.cartzy.invalid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$ompIsf33MQBqKenSK8XwTeoGBzXdEZ.zq5ZJE8FFCkgb/YFQQBUS2', 'seller', NULL, 'active', '0', NULL, '2026-10-10 06:38:24', '2026-10-10 06:38:24', NULL, NULL, NULL),
('17', 'Little Sprouts', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'little-sprouts@catalog.cartzy.invalid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$LtfJJ4bC.eF7dALPove4t.O9oW6iAzsFYtcDdGYr2JsK33kGQdJom', 'seller', NULL, 'active', '0', NULL, '2026-10-10 06:38:25', '2026-10-10 06:38:25', NULL, NULL, NULL),
('18', 'Handy Home Tools', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'handy-home@catalog.cartzy.invalid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$m/vzgJWSgvXBTKhJYRKGdeSizOnfQW5DSilMYReFN86C5/NsKSXd6', 'seller', NULL, 'active', '0', NULL, '2026-10-10 06:38:25', '2026-10-10 06:38:25', NULL, NULL, NULL),
('19', 'Happy Paws', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'happy-paws@catalog.cartzy.invalid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$9zG2Frwk3pc97ABtlmD4c.ngVUqUE.09Aq0y.scA7twIRRIVCimJK', 'seller', NULL, 'active', '0', NULL, '2026-10-10 06:38:26', '2026-10-10 06:38:26', NULL, NULL, NULL),
('20', 'Paper & Palette', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'paper-palette@catalog.cartzy.invalid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$JwhY0ovUbhkQoZq8Gd7/B.Nv9NMrNw4tVEWLOPePhmw3/xrENSZIu', 'seller', NULL, 'active', '0', NULL, '2026-10-10 06:38:26', '2026-10-10 06:38:26', NULL, NULL, NULL),
('21', 'Auto Essentials', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'auto-essentials@catalog.cartzy.invalid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$/X0tdSCsO8lYbXtK9NuSoeXh2F6OKL02//c.iRY0dP9Z7MZghSprm', 'seller', NULL, 'active', '0', NULL, '2026-10-10 06:38:26', '2026-10-10 06:38:26', NULL, NULL, NULL),
('22', 'Arnel Gomez', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'arnel.rider@cartzy.ph', NULL, '09285559876', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-16 11:26:25', '$2y$12$cVineyfVUQM41KydQsnF6.O5o5WXePJDhhgeqe5zE1DoLS1cXkD0K', 'courier', NULL, 'active', '0', NULL, '2026-10-11 06:51:54', '2026-10-16 11:26:26', NULL, NULL, NULL),
('23', 'Carlo Santos', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'carlo.rider@cartzy.ph', NULL, '09178884455', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$U4kIueRiC.achT6ZdoUpPeYrnGTe.MSc/y7fK5OFu8T3dQ0EI.sjK', 'courier', NULL, 'active', '0', NULL, '2026-10-11 06:51:55', '2026-10-11 06:51:55', NULL, NULL, NULL),
('24', 'Rex Villanueva', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'rex.rider@cartzy.ph', NULL, '09271112233', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$q2DutzCrgaW4aaRi5pBf/.SkZF3oGoETQhB1oo9flTMKaKzP2VIGO', 'courier', NULL, 'suspended', '1', NULL, '2026-10-11 06:51:55', '2026-10-17 05:56:35', NULL, NULL, NULL),
('25', 'Jose Mabuhay', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'jose.rider@cartzy.ph', NULL, '09394445566', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$Ve3lWDEtioErgp5SYUC6tuHU6JF5b.5FaKdSzkmnrI6mXuL3q0pP6', 'courier', NULL, 'active', '0', NULL, '2026-10-11 06:51:56', '2026-10-11 06:51:56', NULL, NULL, NULL),
('26', 'Bong Aquino', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'bong.rider@cartzy.ph', NULL, '09451234567', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$tWZcfb/2wTVyUCrbuTDjReO5glkKhbIz0QP8rQSgMf8rW/9C0l4Sa', 'courier', NULL, 'active', '0', NULL, '2026-10-11 06:51:56', '2026-10-11 06:51:56', NULL, NULL, NULL),
('27', 'Noel Torres', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'noel.rider@cartzy.ph', NULL, '09119876543', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$nzhtkUmhmxvf4K5Ei5bqlujpacPE/Q0C9ZFxCyq4UFelEz5.OCr6a', 'courier', NULL, 'suspended', '1', NULL, '2026-10-11 06:51:57', '2026-10-19 07:02:46', NULL, NULL, NULL),
('28', 'happyskinphil', NULL, NULL, NULL, NULL, NULL, 'DTI Business Registration', 'DTI-HSP-2026-0988', 'verified', NULL, '2026-10-11 07:23:16', 'happyskinphil@gmail.com', NULL, '09178889900', NULL, 'High Street Central', 'Bonifacio Global City, Taguig City, Metro Manila', 'NCR', 'Metro Manila', 'Taguig City', 'Fort Bonifacio', '1634', 'happyskinphil', NULL, NULL, NULL, '2026-10-11 07:23:16', '$2y$12$.jB0VXLcswGD9tCw7Aa2o.zLqmZA4S4JHPaQlb.D2H9koKYJXluei', 'seller', NULL, 'active', '0', NULL, '2026-10-11 07:23:16', '2026-10-11 07:23:16', NULL, NULL, NULL),
('29', 'Mark_1123 Kram*sdas', NULL, NULL, '2017-01-01', '9', NULL, NULL, NULL, 'unverified', NULL, NULL, 'trillesmark11@gmail.com', NULL, '09532584860', NULL, '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, '2026-10-12 07:51:18', '$2y$12$6wZhCYWg1ne7Qh60lBK83ud31ZxmbkB9aJ2oMdWggtS6DrST7VZrK', 'buyer', NULL, 'active', '0', NULL, '2026-10-12 07:51:18', '2026-10-12 08:13:40', NULL, NULL, NULL),
('30', 'Mak Mac', NULL, NULL, '2026-01-01', '0', NULL, NULL, NULL, 'unverified', NULL, NULL, 'strayerror.mx@gmail.com', NULL, '09532584', NULL, '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, '2026-10-12 08:02:06', '$2y$12$HomiX5G.P2SflfV3EyVIceCTGo.JTzovgt3TFhPsrUWT94KUDOwHS', 'buyer', NULL, 'active', '0', NULL, '2026-10-12 08:02:07', '2026-10-12 08:02:07', NULL, NULL, NULL),
('31', 'Maria Clara Santos', NULL, NULL, '1984-05-17', '42', NULL, NULL, NULL, 'unverified', NULL, NULL, 'mafah90082@leafflip.com', NULL, '09489911686', NULL, '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, '2026-10-14 00:56:30', '$2y$12$GzEADYygrnStg3/LGKRuZ.PT4NYO7INxxQmv2P287oygcMm0vVmRK', 'seller', NULL, 'pending', '0', NULL, '2026-10-14 00:56:31', '2026-10-14 00:56:31', NULL, NULL, NULL),
('32', 'Jm Pitargs', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'player1zeero@gmail.com', NULL, '09231234141', NULL, '', 'Sta Cruz, Laguna', '', '', '', '', '', 'Nexora Shop', NULL, NULL, NULL, '2026-10-14 01:53:12', '$2y$12$LwIo8zWUKpb9hRzD1MFJu.lZkm1e5fUYZ8XHA90joUhvIcv25oQAO', 'logistics', NULL, 'pending', '0', NULL, '2026-10-14 01:53:13', '2026-10-14 01:53:13', NULL, NULL, NULL),
('33', 'sofie b', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'rejected', 'walang business permit', NULL, 'souppiayaballescas@gmail.com', NULL, '09283492342', NULL, '', 'Quezon City', '', '', '', '', '', 'FireNation', NULL, NULL, NULL, '2026-10-14 02:10:51', '$2y$12$F69wywjzpMFAkj3.tlnCsOYt9WgIdZQA25XVxnLCxJwWeqN7JYBim', 'logistics', NULL, 'rejected', '0', NULL, '2026-10-14 02:10:51', '2026-10-14 03:14:59', NULL, NULL, NULL),
('34', 'Hatdog Itlog', NULL, NULL, '2008-10-14', '18', NULL, NULL, NULL, 'unverified', NULL, NULL, 'dexer92805@leafflip.com', NULL, '09566525116', NULL, '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, '2026-10-14 03:37:30', '$2y$12$UjmVoyHJOIzfscuEXIChouU3tHs.rCTNu9avg5g1qDdfvMzZklVLm', 'buyer', NULL, 'active', '0', NULL, '2026-10-14 03:37:30', '2026-10-14 03:37:30', NULL, NULL, NULL),
('35', 'Ella Rivera', NULL, NULL, '2005-11-14', '20', NULL, NULL, NULL, 'unverified', NULL, NULL, 'earaneta258@gmail.com', NULL, '09152608445', NULL, 'St. Burol', 'St. Burol, Brgy. Magdapio, Pagsanjan, Laguna, 4008', 'IV-A', 'Laguna', 'Pagsanjan', 'Magdapio', '4008', NULL, NULL, NULL, NULL, '2026-10-14 06:14:41', '$2y$12$TrNNFKOdR4Ba2sNQcHL7jOiegZnyzCD.5.y4y1xSZT5GATdm6ZzQq', 'buyer', NULL, 'active', '0', NULL, '2026-10-14 06:14:42', '2026-10-14 11:12:35', NULL, NULL, NULL),
('36', 'anya garcia', NULL, NULL, '2008-10-03', '18', 'id_documents/dz875hs25B7x6lbHn9oE0JI9SdAFsvZF9CKdoFww.png', 'Philippine National ID (PhilSys)', '459678678678678', 'verified', NULL, '2026-10-17 10:05:54', 'anya.r.garcia22@gmail.com', NULL, '08189876543', NULL, '', '', '', '', '', '', '', 'Maria Clara Handicrafts PH', NULL, 'dti_permits/LqTyTim5hTUgnHG9vBqxjrcz4FuLEMDMAdaaugd6.png', 'Home, Furniture & Living', '2026-10-14 07:37:05', '$2y$12$y/Kqa330m240aVxB7h5zgOBS2p1mjC4MDteMLbR0j6vgvkrw30zki', 'seller', NULL, 'active', '0', NULL, '2026-10-14 07:37:05', '2026-10-17 10:05:54', NULL, NULL, NULL),
('37', 'Adidas Philippines', NULL, NULL, NULL, NULL, NULL, 'DTI Business Registration', 'DTI-ADI-2026-8888', 'verified', NULL, '2026-10-18 05:23:11', 'adidas@cartzy.com', NULL, '09178883344', NULL, 'Bonifacio High Street Central, 9th Ave', 'Bonifacio Global City, Taguig City, Metro Manila', 'NCR', 'Metro Manila', 'Taguig City', 'Fort Bonifacio', '1634', 'Adidas Official Store', '123-456-789', NULL, 'Sports, Fitness & Outdoors', '2026-10-18 05:23:11', '$2y$12$IuaXBQBZapu0yLKDVQp2reSKgZQhWc9cyC13PFBJCDdrBgcD7zQoy', 'seller', NULL, 'active', '0', NULL, '2026-10-15 13:25:00', '2026-10-18 05:23:11', 'Corporation', NULL, NULL),
('38', 'Nhieckaella Pajavera', NULL, NULL, NULL, NULL, 'id_photos/LDbQdGh4hmkMFIuy5UrA3xpE7tmz3RYCWlsyEfNW.png', NULL, NULL, 'verified', NULL, '2026-10-15 15:50:16', 'nhieckaella@gmail.com', NULL, '09561736901', NULL, 'St. Burol', 'St. Burol, Brgy. Magdapio, Pagsanjan, Laguna, IV-A, 4008', 'IV-A', 'Laguna', 'Pagsanjan', 'Magdapio', '4008', 'APLI', '333562742852', 'dti_permits/lFPAMinpMr97rqOsh71zBwdVVK1eGlLecfJerUys.png', 'Appliances', '2026-10-15 14:48:24', '$2y$12$Xj7comwyPNXajv1w3ajqqODEu4nAvNy1I8C7lluSIgw4N5HQC9ED6', 'seller', NULL, 'active', '0', NULL, '2026-10-15 14:48:24', '2026-10-15 15:50:16', 'sole_proprietorship', 'seller_documents/ThtZQYlcjtWp88bPuhZ5FqAgsunM3IaXq9kVac1m.png', 'seller_documents/6rg2RY2ULj8HyFqH6EJufkVINe2oG9xYzHzjK7z7.png'),
('39', 'Juan Miguel Jr.', NULL, 'Male', '2006-09-18', '20', 'id_documents/NJ7KpxgXF7DTqB9CKmCgdwk4seegM4sk1pakwqzX.png', 'Philippine National ID', '12321312321', 'verified', NULL, '2026-10-17 13:36:05', 'jmpitargue18@gmail.com', '101003417947158454147', '09171233131', NULL, '14F Pacific Tower', '14F Pacific Tower, Brgy. Makina, Naic, Cavite, 2213', 'IV-A', 'Cavite', 'Naic', 'Makina', '2213', NULL, NULL, NULL, NULL, '2026-10-16 07:42:06', '$2y$12$7oGYeucsF.t8R4zsTceNHeZqCCqT.0CRuBPLrLDbIP4Wl9FXNBkkK', 'buyer', 'https://lh3.googleusercontent.com/a/ACg8ocL1QUu6rIi4kzhEAEAyVUKVBsYUWAfVNwT4ch-n2BL0kPjPiQ=s96-c', 'active', '0', 'QTYsp2GCQn3s1nRjNdz01kjHHYA4vaW9Ph5DbB7V2UDpOElcC6O9GzyRGaN2', '2026-10-16 07:42:07', '2026-10-18 08:24:21', NULL, NULL, NULL),
('40', 'Tiffany Leonardo', NULL, NULL, '2005-12-03', '20', NULL, NULL, NULL, 'unverified', NULL, NULL, 'tiffanyjoyleonardo03@gmail.com', NULL, '09566407337', NULL, '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, '2026-10-17 13:05:55', '$2y$12$8DwDEedj8z5KGRQsH1bDnOsjY5OhyOUtwe8YgzyjwGJqTC7AZ4WnG', 'courier', NULL, 'active', '0', NULL, '2026-10-17 13:05:55', '2026-10-17 13:09:15', NULL, NULL, NULL),
('41', 'Nike Philippines', NULL, NULL, NULL, NULL, NULL, 'DTI Business Registration', 'DTI-NKE-2026-9999', 'verified', NULL, '2026-10-18 05:23:12', 'nike@cartzy.com', NULL, '09179994455', NULL, 'Bonifacio High Street Central, 7th Ave', 'Bonifacio Global City, Taguig City, Metro Manila', 'NCR', 'Metro Manila', 'Taguig City', 'Fort Bonifacio', '1634', 'Nike Official Store', '987-654-321', NULL, 'Sports, Fitness & Outdoors', '2026-10-18 05:23:12', '$2y$12$cGDeJMj4CIrj71U.OkyNmOgjWoWzKw2rWSUYa7STQZ/7Oe0Q372o.', 'seller', NULL, 'active', '0', NULL, '2026-10-18 03:25:51', '2026-10-18 05:23:12', 'Corporation', NULL, NULL),
('42', 'Logitech Philippines', NULL, NULL, NULL, NULL, NULL, 'DTI Business Registration', 'DTI-LOGI-2026-7777', 'verified', NULL, '2026-10-18 05:23:12', 'logitech@cartzy.com', NULL, '09176662233', NULL, 'Cyberzone, SM Megamall, EDSA', 'Ortigas Center, Mandaluyong City, Metro Manila', 'NCR', 'Metro Manila', 'Mandaluyong City', 'Wack-Wack Greenhills', '1550', 'Logitech Official Store', '456-789-101', NULL, 'Computers, Gaming & Electronics', '2026-10-18 05:23:12', '$2y$12$MHI8tEhOExW3AHsB4KxwFu/mOs6zz9Qx1.MrNophfehueK0mgJtq2', 'seller', NULL, 'active', '0', NULL, '2026-10-18 05:23:12', '2026-10-18 05:23:12', 'Corporation', NULL, NULL),
('43', 'John Mark', NULL, NULL, '2006-12-26', '19', NULL, NULL, NULL, 'unverified', NULL, NULL, 'jdump2160@gmail.com', NULL, '09239021391', NULL, '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, '2026-10-18 07:17:46', '$2y$12$eWbage7vcAV8EO6Cdb/3ROTzhO16t51PePXsVuh2/octOaxWFiVx2', 'courier', NULL, 'active', '0', NULL, '2026-10-18 07:17:46', '2026-10-18 07:25:03', NULL, NULL, NULL),
('44', 'Jan Reyben Del Mundo', NULL, NULL, '2005-09-20', '21', NULL, NULL, NULL, 'unverified', NULL, NULL, 'chatbotnimaro@gmail.com', NULL, '09875647324', NULL, '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, '2026-10-18 07:22:43', '$2y$12$kW.s.4buxp2VB6BajL.C9e8OPFO7G9ZGdxsvN1134mdYlapxttni6', 'courier', NULL, 'active', '0', NULL, '2026-10-18 07:22:44', '2026-10-18 07:24:50', NULL, NULL, NULL),
('45', 'Jonnel Araneta', NULL, NULL, '2005-11-14', '20', NULL, NULL, NULL, 'unverified', NULL, NULL, 'nhieckaellarivera@gmail.com', NULL, '09137856768', NULL, '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, '2026-10-18 08:05:14', '$2y$12$dV96c404mRV.pdRecGOLquli2vSI8RbeY1sk.w5/WCXnnZJgA7.De', 'buyer', NULL, 'active', '0', NULL, '2026-10-18 08:05:15', '2026-10-18 08:05:15', NULL, NULL, NULL),
('46', 'hansel gretel', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'johnclaude192004@gmail.com', NULL, '09821391237', NULL, '', 'Manila', '', '', '', '', '', 'LuxuryParadise', NULL, NULL, NULL, '2026-10-18 08:43:01', '$2y$12$ercdehB44eVGLoEsgoYdleBxNr.FNFk0vzcLE/wQMwycSTU6/Oli.', 'logistics', NULL, 'pending', '0', NULL, '2026-10-18 08:43:01', '2026-10-18 08:43:01', NULL, NULL, NULL),
('47', 'Gaabor Store', NULL, NULL, NULL, NULL, NULL, 'DTI Business Registration', 'DTI-GAB-2026-5555', 'verified', NULL, '2026-10-18 12:04:55', 'gaabor@cartzy.com', NULL, '09175551122', NULL, 'Unit 12, Ortigas Avenue', 'Ortigas Center, Pasig City, Metro Manila', 'NCR', 'Metro Manila', 'Pasig City', 'San Antonio', '1605', 'Gaabor Store', '321-654-987', NULL, 'Home Appliances', '2026-10-18 12:04:55', '$2y$12$ISbYBEmnvcKuLvUyzc.fJ.SvNEkJB3EcQdDyHBXg6ZIJBq6onsP.2', 'seller', NULL, 'active', '0', NULL, '2026-10-18 12:04:55', '2026-10-18 12:04:55', 'Corporation', NULL, NULL),
('48', 'buyer tester', NULL, NULL, '2004-07-20', '22', NULL, NULL, NULL, 'unverified', NULL, NULL, 'cancer.mike2@gmail.com', NULL, '09656565656', NULL, 'sitio sampaguita', 'sitio sampaguita, Brgy. Bubukal, Santa Cruz, Laguna, 4010', 'IV-A', 'Laguna', 'Santa Cruz', 'Bubukal', '4010', NULL, NULL, NULL, NULL, '2026-10-20 04:00:04', '$2y$12$/WQW8kOj2s0/IO/kre/PIexy7WNdtnRKlza1MPXBoZSuWVLhE3vZm', 'buyer', NULL, 'active', '0', NULL, '2026-10-20 04:00:05', '2026-10-20 04:04:19', NULL, NULL, NULL);

-- --------------------------------------------------------
-- Table structure for table `voucher_redemptions`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `voucher_redemptions`;
CREATE TABLE `voucher_redemptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `voucher_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `order_id` bigint unsigned NOT NULL,
  `discount_minor` bigint unsigned NOT NULL,
  `status` enum('applied','reversed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'applied',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `voucher_redemptions_user_id_foreign` (`user_id`),
  KEY `voucher_redemptions_order_id_foreign` (`order_id`),
  KEY `voucher_redemptions_voucher_id_user_id_status_index` (`voucher_id`,`user_id`,`status`),
  CONSTRAINT `voucher_redemptions_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `voucher_redemptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `voucher_redemptions_voucher_id_foreign` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `voucher_redemptions` (3 rows)
INSERT INTO `voucher_redemptions` (`id`, `voucher_id`, `user_id`, `order_id`, `discount_minor`, `status`, `created_at`, `updated_at`) VALUES
('1', '1', '48', '20', '5000', 'applied', '2026-10-20 04:05:09', '2026-10-20 04:05:09'),
('2', '1', '10', '21', '5000', 'applied', '2026-10-20 04:18:37', '2026-10-20 04:18:37'),
('3', '1', '9', '22', '5000', 'applied', '2026-10-20 04:19:24', '2026-10-20 04:19:24');

-- --------------------------------------------------------
-- Table structure for table `voucher_user`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `voucher_user`;
CREATE TABLE `voucher_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `voucher_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `source` enum('assigned','code') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `voucher_user_voucher_id_user_id_unique` (`voucher_id`,`user_id`),
  KEY `voucher_user_user_id_foreign` (`user_id`),
  CONSTRAINT `voucher_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `voucher_user_voucher_id_foreign` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `vouchers`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `vouchers`;
CREATE TABLE `vouchers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount_type` enum('fixed','percent','free_shipping') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount_minor` bigint unsigned DEFAULT NULL,
  `percent` tinyint unsigned DEFAULT NULL,
  `max_discount_minor` bigint unsigned DEFAULT NULL,
  `min_spend_minor` bigint unsigned NOT NULL DEFAULT '0',
  `audience` enum('public','new_buyers','assigned','code') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'public',
  `funded_by` enum('platform','seller') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'platform',
  `total_limit` int unsigned DEFAULT NULL,
  `per_user_limit` int unsigned NOT NULL DEFAULT '1',
  `used_count` int unsigned NOT NULL DEFAULT '0',
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vouchers_code_unique` (`code`),
  KEY `vouchers_created_by_foreign` (`created_by`),
  KEY `vouchers_is_active_audience_index` (`is_active`,`audience`),
  CONSTRAINT `vouchers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `vouchers` (1 rows)
INSERT INTO `vouchers` (`id`, `name`, `code`, `description`, `discount_type`, `amount_minor`, `percent`, `max_discount_minor`, `min_spend_minor`, `audience`, `funded_by`, `total_limit`, `per_user_limit`, `used_count`, `starts_at`, `ends_at`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
('1', 'Shipping Saver', NULL, '₱50 off shipping when your items reach ₱1,000.', 'free_shipping', '5000', NULL, NULL, '100000', 'public', 'platform', NULL, '0', '3', NULL, NULL, '1', NULL, '2026-10-20 03:55:03', '2026-10-20 04:19:24');

-- --------------------------------------------------------
-- Table structure for table `wishlists`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `wishlists`;
CREATE TABLE `wishlists` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `ref` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price_minor` bigint unsigned NOT NULL,
  `image` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `variation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wishlists_user_id_ref_unique` (`user_id`,`ref`),
  CONSTRAINT `wishlists_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `wishlists` (2 rows)
INSERT INTO `wishlists` (`id`, `user_id`, `ref`, `name`, `price_minor`, `image`, `variation`, `created_at`, `updated_at`) VALUES
('2', '39', '10', 'Leather Crossbody Sling Bag Women', '74900', 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=500&h=500&fit=crop&q=80', 'Brown', '2026-10-16 07:44:17', '2026-10-16 07:44:17'),
('8', '10', '51', 'Logitech Rally Mic Pod Conference Microphone', '129900', 'https://cartzyy.shop/storage/products/xHlx7X14k6tLwD1wamtmsvTERv92MuNwvqAYghJR.jpg', 'Graphite', '2026-10-19 08:07:02', '2026-10-19 08:07:02');

COMMIT;
