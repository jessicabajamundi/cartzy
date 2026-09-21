-- ==========================================================
-- Cartzy Live Database Dump / Schema
-- Database: `ecommerce_db`
-- Auto-synced at: 2026-09-19 13:48:05
-- ==========================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `ecommerce_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ecommerce_db`;

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
-- Table structure for table `migrations`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `migrations` (9 rows)
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
('1', '0001_01_01_000000_create_users_table', '1'),
('2', '0001_01_01_000001_create_cache_table', '1'),
('3', '0001_01_01_000002_create_jobs_table', '1'),
('4', '2026_08_19_000001_add_role_and_details_to_users_table', '1'),
('5', '2026_08_26_000002_add_address_contact_to_users_table', '1'),
('6', '2026_09_06_000001_add_detailed_address_to_users_table', '2'),
('7', '2026_09_06_000002_add_buyer_fields_to_users_table', '3'),
('8', '2026_09_13_000001_add_id_verification_fields_to_users_table', '4'),
('9', '2026_09_19_000001_add_google_id_to_users_table', '5');

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
  `street_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `region` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `province` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `barangay` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'buyer',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_google_id_unique` (`google_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `users` (9 rows)
INSERT INTO `users` (`id`, `name`, `middle_initial`, `sex`, `birthday`, `age`, `id_photo`, `id_type`, `id_number`, `id_status`, `id_rejection_reason`, `id_verified_at`, `email`, `google_id`, `phone`, `street_address`, `address`, `region`, `province`, `city`, `barangay`, `postal_code`, `email_verified_at`, `password`, `role`, `avatar`, `status`, `remember_token`, `created_at`, `updated_at`) VALUES
('1', 'jess Pambago', NULL, 'Female', '2005-12-05', '20', NULL, NULL, NULL, 'unverified', NULL, NULL, 'jessicapambago27@gmail.com', '102943877745797449792', '09773587409', '1011, purok 4', '1011, purok 4, Brgy. Masapang, Victoria, Laguna, IV-A, 4011', 'IV-A', 'Laguna', 'Victoria', 'Masapang', '4011', '2026-09-19 09:39:48', '$2y$12$3nOKEW1najIzdZqV2g.KSOp2TnIrXnnwN.m.cXjZeU9htWcSh28tW', 'buyer', 'https://lh3.googleusercontent.com/a/ACg8ocKRnO3z--zfu4NoW4iXG9RuYJ4W7Hc5Ty8Q9LfGYo8PptOyDiQ=s96-c', 'active', 'EBhCnTFC1c6rFmBcjPEdJP9hzH0hmrKRy3HsbFs8b2Lqn7zWxgLbJMmv9Vw1', '2026-09-06 10:07:45', '2026-09-19 09:39:48'),
('2', 'Jessie Bajamundi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'bajamundijessie2@gmail.com', '105267381396672766135', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$T1E7WJ09XHkR69TZhisBfuxO87Qu4x8gfTpp3e1iHINstiNoyAz/y', 'buyer', 'https://lh3.googleusercontent.com/a/ACg8ocJpogT-VfJhnw40Xyj5tD46nnZ8k-cTondgVTm5pl7QXgtv-FE=s96-c', 'active', 'UyY0OQ7Jb0ucp8Xl5kNmhb9va56wxMFudR9dXmLRwJhMmNPFvswsit0vUbWy', '2026-09-19 09:38:11', '2026-09-19 09:38:11'),
('3', 'Leonardo, Tiffany Joy O.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'tiffany.leonardolspu@gmail.com', '102856395965406016321', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$2rPlMMwYfnaULGFzuFg7FOJSONDLg43g3jm/W13JnB9onE.Bl3qb2', 'buyer', 'https://lh3.googleusercontent.com/a/ACg8ocJIBmZGdR_My3EM3rpyD5hHikz_j0zobdBv9aAD8Ts4A9fNJQzz=s96-c', 'active', 'phoKm8Bx9yeXpLw8bmOdmOmGZFS6NH8VXoDHbBFMotWrp6uoVeNORb46J1Ax', '2026-09-19 10:08:30', '2026-09-19 10:08:30'),
('4', 'Pajavera, Nhieckaella Ashley R.', NULL, NULL, '2005-11-14', '20', NULL, NULL, NULL, 'unverified', NULL, NULL, 'nhieckaella05@gmail.com', '109760982595777107804', '09152608445', 'St. Burol', 'St. Burol, Brgy. Magdapio, Pagsanjan, Laguna, IV-A, 4008', 'IV-A', 'Laguna', 'Pagsanjan', 'Magdapio', '4008', NULL, '$2y$12$1rq7GlIrNoH9D8/5.maRFeJpY7YL7dtXCSG7IW0ETH.ZnmrTJuPoK', 'buyer', 'https://lh3.googleusercontent.com/a/ACg8ocLp_Yq1EHEIBYc28poVjnMKVg-6M7ZvC0FVN-PMoSsQMcZQcJpw=s96-c', 'active', 'pXQQ7SMwVTxSIlA3Is1wGksBLE32SpcMJ4AQIwdFBzYgNUsYr9uGBprKTDFb', '2026-09-19 10:08:50', '2026-09-19 13:21:10'),
('5', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'administrationa570@gmail.com', NULL, '09170000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$K.ZOCaZwXJ/eeTCXb4Qj.u.enxvmlXYylGlLZ2D4NRBNmK2QguLOy', 'admin', NULL, 'active', NULL, '2026-09-19 10:14:45', '2026-09-19 10:14:45'),
('6', 'Official Tech Store', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'seller@shopee.ph', NULL, '09171112222', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$MVeFMiwQGc70AodeiKOJreP/U/KKFmuMHjLdvTMvHrHZDS8BEbBwW', 'seller', NULL, 'active', NULL, '2026-09-19 10:14:45', '2026-09-19 10:14:45'),
('7', 'SPX Rider - Juan', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'courier@shopee.ph', NULL, '09173334444', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$T2HrurgfgDtHGEQdJ37fV.kZS64ItiJx7PVRZVMy.InsNuCbPv4Oi', 'courier', NULL, 'active', NULL, '2026-09-19 10:14:46', '2026-09-19 10:14:46'),
('8', 'Maria Dela Cruz', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'buyer@shopee.ph', NULL, '09175556666', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$6TEzJ1W3R1rvfGcDjJT0EujRLAaHR.qJs3En7aRtwnI2PTHkUlSDC', 'buyer', NULL, 'active', NULL, '2026-09-19 10:14:46', '2026-09-19 10:14:46'),
('9', 'Del Mundo, Jan Reyben, D.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'reybendelmundo2005@gmail.com', '100001932505618334922', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$nIUFGFX5nFSwpK89mDQOlu7MNR.bgexpjXwiQW8m7Du7KcPoLIPFC', 'buyer', 'https://lh3.googleusercontent.com/a/ACg8ocLqMeWtsRY85r-QPVe8hW6nuusyr01ufXW6Au2Kj21L0MeXLdTc=s96-c', 'active', 'lUE7PfMWYcaphZThZNE9J6nKB30rzR35fP9EdpcwoK2dk6NmyN5KIR1OnXHB', '2026-09-19 10:22:32', '2026-09-19 10:22:32');

COMMIT;
