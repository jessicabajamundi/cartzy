-- ==========================================================
-- Cartzy Live Database Dump / Schema (Milestone 4 Updated)
-- Database: `ecommerce_db`
-- Auto-synced at: 2026-09-21 06:07:35
-- ==========================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `ecommerce_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ecommerce_db`;

SET FOREIGN_KEY_CHECKS = 0;

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `addresses` (2 rows)
INSERT INTO `addresses` (`id`, `user_id`, `label`, `recipient`, `phone`, `line1`, `barangay`, `city`, `province`, `postal_code`, `is_default`, `created_at`, `updated_at`) VALUES ('1', '1', 'Default Address', 'jess Pambago', '09773587409', '1011, purok 4', 'Masapang', 'Victoria', 'Laguna', '4011', '1', '2026-09-21 06:03:57', '2026-09-21 06:03:57');
INSERT INTO `addresses` (`id`, `user_id`, `label`, `recipient`, `phone`, `line1`, `barangay`, `city`, `province`, `postal_code`, `is_default`, `created_at`, `updated_at`) VALUES ('2', '4', 'Default Address', 'Pajavera, Nhieckaella Ashley R.', '09152608445', 'St. Burol', 'Magdapio', 'Pagsanjan', 'Laguna', '4008', '1', '2026-09-21 06:03:57', '2026-09-21 06:03:57');

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
  `product_variant_id` bigint unsigned NOT NULL,
  `quantity` int unsigned NOT NULL DEFAULT '1',
  `selected` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cart_items_cart_id_product_variant_id_unique` (`cart_id`,`product_variant_id`),
  KEY `cart_items_product_variant_id_foreign` (`product_variant_id`),
  CONSTRAINT `cart_items_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `cart_items` (1 rows)
INSERT INTO `cart_items` (`id`, `cart_id`, `product_variant_id`, `quantity`, `selected`, `created_at`, `updated_at`) VALUES ('1', '1', '3', '1', '1', '2026-09-21 06:03:58', '2026-09-21 06:03:58');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `carts` (1 rows)
INSERT INTO `carts` (`id`, `user_id`, `created_at`, `updated_at`) VALUES ('1', '1', '2026-09-21 06:03:58', '2026-09-21 06:03:58');

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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `categories` (6 rows)
INSERT INTO `categories` (`id`, `parent_id`, `name`, `slug`, `position`, `is_active`, `created_at`, `updated_at`) VALUES ('1', NULL, 'Electronics', 'electronics', '1', '1', '2026-09-21 06:03:57', '2026-09-21 06:03:57');
INSERT INTO `categories` (`id`, `parent_id`, `name`, `slug`, `position`, `is_active`, `created_at`, `updated_at`) VALUES ('2', '1', 'Headphones & Audio', 'audio-headphones', '1', '1', '2026-09-21 06:03:57', '2026-09-21 06:03:57');
INSERT INTO `categories` (`id`, `parent_id`, `name`, `slug`, `position`, `is_active`, `created_at`, `updated_at`) VALUES ('3', '1', 'Smart Watches & Wearables', 'smart-wearables', '2', '1', '2026-09-21 06:03:57', '2026-09-21 06:03:57');
INSERT INTO `categories` (`id`, `parent_id`, `name`, `slug`, `position`, `is_active`, `created_at`, `updated_at`) VALUES ('4', NULL, 'Fashion & Apparel', 'fashion', '2', '1', '2026-09-21 06:03:57', '2026-09-21 06:03:57');
INSERT INTO `categories` (`id`, `parent_id`, `name`, `slug`, `position`, `is_active`, `created_at`, `updated_at`) VALUES ('5', '4', 'Sneakers & Footwear', 'sneakers-footwear', '1', '1', '2026-09-21 06:03:57', '2026-09-21 06:03:57');
INSERT INTO `categories` (`id`, `parent_id`, `name`, `slug`, `position`, `is_active`, `created_at`, `updated_at`) VALUES ('6', '4', 'Bags & Accessories', 'bags-accessories', '2', '1', '2026-09-21 06:03:57', '2026-09-21 06:03:57');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `logistics_providers` (1 rows)
INSERT INTO `logistics_providers` (`id`, `user_id`, `name`, `slug`, `status`, `rejection_reason`, `contact_phone`, `created_at`, `updated_at`) VALUES ('1', '7', 'Cartzy Express Logistics Hub', 'cartzy-express-logistics', 'approved', NULL, '09173334444', '2026-09-21 06:03:57', '2026-09-21 06:03:57');

-- --------------------------------------------------------
-- Table structure for table `migrations`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `migrations` (18 rows)
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('1', '0001_01_01_000000_create_users_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('2', '0001_01_01_000001_create_cache_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('3', '0001_01_01_000002_create_jobs_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('4', '2026_08_19_000001_add_role_and_details_to_users_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('5', '2026_08_26_000002_add_address_contact_to_users_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('6', '2026_09_06_000001_add_detailed_address_to_users_table', '2');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('7', '2026_09_06_000002_add_buyer_fields_to_users_table', '3');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('8', '2026_09_13_000001_add_id_verification_fields_to_users_table', '4');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('9', '2026_09_19_000001_add_google_id_to_users_table', '5');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('10', '2026_09_21_000001_add_is_suspended_to_users_table', '6');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('11', '2026_09_21_000002_create_roles_and_role_user_tables', '6');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('12', '2026_09_21_000003_create_addresses_table', '6');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('13', '2026_09_21_000004_create_sellers_table', '6');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('14', '2026_09_21_000005_create_logistics_and_riders_tables', '6');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('15', '2026_09_21_000006_create_categories_table', '6');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('16', '2026_09_21_000007_create_products_variants_images_tables', '6');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('17', '2026_09_21_000008_create_carts_and_cart_items_tables', '6');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('18', '2026_09_21_000009_create_orders_and_fulfillment_tables', '6');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `product_images` (2 rows)
INSERT INTO `product_images` (`id`, `product_id`, `path`, `position`, `created_at`, `updated_at`) VALUES ('1', '1', 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=600&h=600&fit=crop&q=80', '0', '2026-09-21 06:03:57', '2026-09-21 06:03:57');
INSERT INTO `product_images` (`id`, `product_id`, `path`, `position`, `created_at`, `updated_at`) VALUES ('2', '2', 'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=600&h=600&fit=crop&q=80', '0', '2026-09-21 06:03:57', '2026-09-21 06:03:57');

-- --------------------------------------------------------
-- Table structure for table `product_variants`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `product_variants`;
CREATE TABLE `product_variants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `product_variants` (3 rows)
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `name`, `options`, `price_minor`, `stock`, `weight_grams`, `is_active`, `deleted_at`, `created_at`, `updated_at`) VALUES ('1', '1', 'ANC-BLK', 'Matte Black', '{\"color\": \"Black\"}', '89000', '50', '250', '1', NULL, '2026-09-21 06:03:57', '2026-09-21 06:03:57');
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `name`, `options`, `price_minor`, `stock`, `weight_grams`, `is_active`, `deleted_at`, `created_at`, `updated_at`) VALUES ('2', '1', 'ANC-WHT', 'Glossy White', '{\"color\": \"White\"}', '89000', '40', '250', '1', NULL, '2026-09-21 06:03:57', '2026-09-21 06:03:57');
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `name`, `options`, `price_minor`, `stock`, `weight_grams`, `is_active`, `deleted_at`, `created_at`, `updated_at`) VALUES ('3', '2', 'FIT-SMT-01', 'Midnight Black / Standard Strap', '{\"size\": \"Standard\", \"color\": \"Black\"}', '129900', '35', '180', '1', NULL, '2026-09-21 06:03:57', '2026-09-21 06:03:57');

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
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  KEY `products_seller_id_is_active_index` (`seller_id`,`is_active`),
  KEY `products_category_id_is_active_index` (`category_id`,`is_active`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `products_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `products` (2 rows)
INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `slug`, `description`, `is_active`, `deleted_at`, `created_at`, `updated_at`) VALUES ('1', '1', '2', 'ANC Pro Wireless Noise Cancelling Earphones', 'anc-pro-wireless-noise-cancelling-earphones', 'High-fidelity audio with active noise cancellation, 32-hour battery life, and ultra-low latency gaming mode.', '1', NULL, '2026-09-21 06:03:57', '2026-09-21 06:03:57');
INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `slug`, `description`, `is_active`, `deleted_at`, `created_at`, `updated_at`) VALUES ('2', '1', '3', 'Smart Fitness Tracker Watch with Blood Oxygen & Heart Rate', 'smart-fitness-tracker-watch-blood-oxygen', 'Track your vitals 24/7 with AMOLED display, IP68 water resistance, and 14-day standby time.', '1', NULL, '2026-09-21 06:03:57', '2026-09-21 06:03:57');

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
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `riders_user_id_unique` (`user_id`),
  KEY `riders_logistics_provider_id_foreign` (`logistics_provider_id`),
  CONSTRAINT `riders_logistics_provider_id_foreign` FOREIGN KEY (`logistics_provider_id`) REFERENCES `logistics_providers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `riders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `riders` (1 rows)
INSERT INTO `riders` (`id`, `user_id`, `logistics_provider_id`, `vehicle_type`, `plate_no`, `is_active`, `created_at`, `updated_at`) VALUES ('1', '7', '1', 'Motorcycle', 'ND-4921', '1', '2026-09-21 06:03:58', '2026-09-21 06:03:58');

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

-- Dumping data for table `role_user` (10 rows)
INSERT INTO `role_user` (`role_id`, `user_id`, `created_at`, `updated_at`) VALUES ('1', '1', NULL, NULL);
INSERT INTO `role_user` (`role_id`, `user_id`, `created_at`, `updated_at`) VALUES ('1', '2', NULL, NULL);
INSERT INTO `role_user` (`role_id`, `user_id`, `created_at`, `updated_at`) VALUES ('1', '3', NULL, NULL);
INSERT INTO `role_user` (`role_id`, `user_id`, `created_at`, `updated_at`) VALUES ('1', '4', NULL, NULL);
INSERT INTO `role_user` (`role_id`, `user_id`, `created_at`, `updated_at`) VALUES ('1', '8', NULL, NULL);
INSERT INTO `role_user` (`role_id`, `user_id`, `created_at`, `updated_at`) VALUES ('1', '9', NULL, NULL);
INSERT INTO `role_user` (`role_id`, `user_id`, `created_at`, `updated_at`) VALUES ('2', '6', NULL, NULL);
INSERT INTO `role_user` (`role_id`, `user_id`, `created_at`, `updated_at`) VALUES ('3', '7', NULL, NULL);
INSERT INTO `role_user` (`role_id`, `user_id`, `created_at`, `updated_at`) VALUES ('4', '7', NULL, NULL);
INSERT INTO `role_user` (`role_id`, `user_id`, `created_at`, `updated_at`) VALUES ('5', '5', NULL, NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `roles` (5 rows)
INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES ('1', 'buyer', '2026-09-21 06:03:56', '2026-09-21 06:03:56');
INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES ('2', 'seller', '2026-09-21 06:03:56', '2026-09-21 06:03:56');
INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES ('3', 'logistics', '2026-09-21 06:03:56', '2026-09-21 06:03:56');
INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES ('4', 'rider', '2026-09-21 06:03:56', '2026-09-21 06:03:56');
INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES ('5', 'admin', '2026-09-21 06:03:56', '2026-09-21 06:03:56');

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
  `commission_minor` bigint unsigned NOT NULL DEFAULT '0',
  `status` enum('pending','accepted','packed','ready_to_ship','shipped','delivered','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `delivered_at` timestamp NULL DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seller_orders_order_id_seller_id_unique` (`order_id`,`seller_id`),
  KEY `seller_orders_logistics_provider_id_foreign` (`logistics_provider_id`),
  KEY `seller_orders_seller_id_status_index` (`seller_id`,`status`),
  CONSTRAINT `seller_orders_logistics_provider_id_foreign` FOREIGN KEY (`logistics_provider_id`) REFERENCES `logistics_providers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `seller_orders_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `seller_orders_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `commission_bps` int unsigned NOT NULL DEFAULT '800',
  `pickup_address_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sellers_slug_unique` (`slug`),
  KEY `sellers_user_id_foreign` (`user_id`),
  KEY `sellers_pickup_address_id_foreign` (`pickup_address_id`),
  CONSTRAINT `sellers_pickup_address_id_foreign` FOREIGN KEY (`pickup_address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sellers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `sellers` (1 rows)
INSERT INTO `sellers` (`id`, `user_id`, `name`, `slug`, `description`, `logo_path`, `banner_path`, `status`, `rejection_reason`, `commission_bps`, `pickup_address_id`, `created_at`, `updated_at`) VALUES ('1', '6', 'TechZone Official Mall', 'techzone-official-mall', 'Your premier destination for authentic wireless earbuds, smartwatches, and premium accessories.', 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=200&h=200&fit=crop&q=80', 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=1200&h=400&fit=crop&q=80', 'approved', NULL, '800', NULL, '2026-09-21 06:03:57', '2026-09-21 06:03:57');

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
  `is_suspended` tinyint(1) NOT NULL DEFAULT '0',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_google_id_unique` (`google_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `users` (9 rows)
INSERT INTO `users` (`id`, `name`, `middle_initial`, `sex`, `birthday`, `age`, `id_photo`, `id_type`, `id_number`, `id_status`, `id_rejection_reason`, `id_verified_at`, `email`, `google_id`, `phone`, `street_address`, `address`, `region`, `province`, `city`, `barangay`, `postal_code`, `email_verified_at`, `password`, `role`, `avatar`, `status`, `is_suspended`, `remember_token`, `created_at`, `updated_at`) VALUES ('1', 'jess Pambago', NULL, 'Female', '2005-12-05', '20', NULL, NULL, NULL, 'unverified', NULL, NULL, 'jessicapambago27@gmail.com', '102943877745797449792', '09773587409', '1011, purok 4', '1011, purok 4, Brgy. Masapang, Victoria, Laguna, IV-A, 4011', 'IV-A', 'Laguna', 'Victoria', 'Masapang', '4011', '2026-09-19 09:39:48', '$2y$12$3nOKEW1najIzdZqV2g.KSOp2TnIrXnnwN.m.cXjZeU9htWcSh28tW', 'buyer', 'https://lh3.googleusercontent.com/a/ACg8ocKRnO3z--zfu4NoW4iXG9RuYJ4W7Hc5Ty8Q9LfGYo8PptOyDiQ=s96-c', 'active', '0', 'EBhCnTFC1c6rFmBcjPEdJP9hzH0hmrKRy3HsbFs8b2Lqn7zWxgLbJMmv9Vw1', '2026-09-06 10:07:45', '2026-09-19 09:39:48');
INSERT INTO `users` (`id`, `name`, `middle_initial`, `sex`, `birthday`, `age`, `id_photo`, `id_type`, `id_number`, `id_status`, `id_rejection_reason`, `id_verified_at`, `email`, `google_id`, `phone`, `street_address`, `address`, `region`, `province`, `city`, `barangay`, `postal_code`, `email_verified_at`, `password`, `role`, `avatar`, `status`, `is_suspended`, `remember_token`, `created_at`, `updated_at`) VALUES ('2', 'Jessie Bajamundi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'bajamundijessie2@gmail.com', '105267381396672766135', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$T1E7WJ09XHkR69TZhisBfuxO87Qu4x8gfTpp3e1iHINstiNoyAz/y', 'buyer', 'https://lh3.googleusercontent.com/a/ACg8ocJpogT-VfJhnw40Xyj5tD46nnZ8k-cTondgVTm5pl7QXgtv-FE=s96-c', 'active', '0', 'UyY0OQ7Jb0ucp8Xl5kNmhb9va56wxMFudR9dXmLRwJhMmNPFvswsit0vUbWy', '2026-09-19 09:38:11', '2026-09-19 09:38:11');
INSERT INTO `users` (`id`, `name`, `middle_initial`, `sex`, `birthday`, `age`, `id_photo`, `id_type`, `id_number`, `id_status`, `id_rejection_reason`, `id_verified_at`, `email`, `google_id`, `phone`, `street_address`, `address`, `region`, `province`, `city`, `barangay`, `postal_code`, `email_verified_at`, `password`, `role`, `avatar`, `status`, `is_suspended`, `remember_token`, `created_at`, `updated_at`) VALUES ('3', 'Leonardo, Tiffany Joy O.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'tiffany.leonardolspu@gmail.com', '102856395965406016321', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$2rPlMMwYfnaULGFzuFg7FOJSONDLg43g3jm/W13JnB9onE.Bl3qb2', 'buyer', 'https://lh3.googleusercontent.com/a/ACg8ocJIBmZGdR_My3EM3rpyD5hHikz_j0zobdBv9aAD8Ts4A9fNJQzz=s96-c', 'active', '0', 'phoKm8Bx9yeXpLw8bmOdmOmGZFS6NH8VXoDHbBFMotWrp6uoVeNORb46J1Ax', '2026-09-19 10:08:30', '2026-09-19 10:08:30');
INSERT INTO `users` (`id`, `name`, `middle_initial`, `sex`, `birthday`, `age`, `id_photo`, `id_type`, `id_number`, `id_status`, `id_rejection_reason`, `id_verified_at`, `email`, `google_id`, `phone`, `street_address`, `address`, `region`, `province`, `city`, `barangay`, `postal_code`, `email_verified_at`, `password`, `role`, `avatar`, `status`, `is_suspended`, `remember_token`, `created_at`, `updated_at`) VALUES ('4', 'Pajavera, Nhieckaella Ashley R.', NULL, NULL, '2005-11-14', '20', NULL, NULL, NULL, 'unverified', NULL, NULL, 'nhieckaella05@gmail.com', '109760982595777107804', '09152608445', 'St. Burol', 'St. Burol, Brgy. Magdapio, Pagsanjan, Laguna, IV-A, 4008', 'IV-A', 'Laguna', 'Pagsanjan', 'Magdapio', '4008', NULL, '$2y$12$1rq7GlIrNoH9D8/5.maRFeJpY7YL7dtXCSG7IW0ETH.ZnmrTJuPoK', 'buyer', 'https://lh3.googleusercontent.com/a/ACg8ocLp_Yq1EHEIBYc28poVjnMKVg-6M7ZvC0FVN-PMoSsQMcZQcJpw=s96-c', 'active', '0', '3nj4tC6v9kUD0XpA9JVCiMozO0Sq1W1gp7pdvfTfXHXGTR1Zb0ePtx1dipzO', '2026-09-19 10:08:50', '2026-09-19 13:21:10');
INSERT INTO `users` (`id`, `name`, `middle_initial`, `sex`, `birthday`, `age`, `id_photo`, `id_type`, `id_number`, `id_status`, `id_rejection_reason`, `id_verified_at`, `email`, `google_id`, `phone`, `street_address`, `address`, `region`, `province`, `city`, `barangay`, `postal_code`, `email_verified_at`, `password`, `role`, `avatar`, `status`, `is_suspended`, `remember_token`, `created_at`, `updated_at`) VALUES ('5', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'administrationa570@gmail.com', NULL, '09170000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$K.ZOCaZwXJ/eeTCXb4Qj.u.enxvmlXYylGlLZ2D4NRBNmK2QguLOy', 'admin', NULL, 'active', '0', NULL, '2026-09-19 10:14:45', '2026-09-19 10:14:45');
INSERT INTO `users` (`id`, `name`, `middle_initial`, `sex`, `birthday`, `age`, `id_photo`, `id_type`, `id_number`, `id_status`, `id_rejection_reason`, `id_verified_at`, `email`, `google_id`, `phone`, `street_address`, `address`, `region`, `province`, `city`, `barangay`, `postal_code`, `email_verified_at`, `password`, `role`, `avatar`, `status`, `is_suspended`, `remember_token`, `created_at`, `updated_at`) VALUES ('6', 'Official Tech Store', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'seller@shopee.ph', NULL, '09171112222', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$MVeFMiwQGc70AodeiKOJreP/U/KKFmuMHjLdvTMvHrHZDS8BEbBwW', 'seller', NULL, 'active', '0', NULL, '2026-09-19 10:14:45', '2026-09-19 10:14:45');
INSERT INTO `users` (`id`, `name`, `middle_initial`, `sex`, `birthday`, `age`, `id_photo`, `id_type`, `id_number`, `id_status`, `id_rejection_reason`, `id_verified_at`, `email`, `google_id`, `phone`, `street_address`, `address`, `region`, `province`, `city`, `barangay`, `postal_code`, `email_verified_at`, `password`, `role`, `avatar`, `status`, `is_suspended`, `remember_token`, `created_at`, `updated_at`) VALUES ('7', 'SPX Rider - Juan', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'courier@shopee.ph', NULL, '09173334444', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$T2HrurgfgDtHGEQdJ37fV.kZS64ItiJx7PVRZVMy.InsNuCbPv4Oi', 'courier', NULL, 'active', '0', NULL, '2026-09-19 10:14:46', '2026-09-19 10:14:46');
INSERT INTO `users` (`id`, `name`, `middle_initial`, `sex`, `birthday`, `age`, `id_photo`, `id_type`, `id_number`, `id_status`, `id_rejection_reason`, `id_verified_at`, `email`, `google_id`, `phone`, `street_address`, `address`, `region`, `province`, `city`, `barangay`, `postal_code`, `email_verified_at`, `password`, `role`, `avatar`, `status`, `is_suspended`, `remember_token`, `created_at`, `updated_at`) VALUES ('8', 'Maria Dela Cruz', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'buyer@shopee.ph', NULL, '09175556666', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$6TEzJ1W3R1rvfGcDjJT0EujRLAaHR.qJs3En7aRtwnI2PTHkUlSDC', 'buyer', NULL, 'active', '0', NULL, '2026-09-19 10:14:46', '2026-09-19 10:14:46');
INSERT INTO `users` (`id`, `name`, `middle_initial`, `sex`, `birthday`, `age`, `id_photo`, `id_type`, `id_number`, `id_status`, `id_rejection_reason`, `id_verified_at`, `email`, `google_id`, `phone`, `street_address`, `address`, `region`, `province`, `city`, `barangay`, `postal_code`, `email_verified_at`, `password`, `role`, `avatar`, `status`, `is_suspended`, `remember_token`, `created_at`, `updated_at`) VALUES ('9', 'Del Mundo, Jan Reyben, D.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'reybendelmundo2005@gmail.com', '100001932505618334922', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$nIUFGFX5nFSwpK89mDQOlu7MNR.bgexpjXwiQW8m7Du7KcPoLIPFC', 'buyer', 'https://lh3.googleusercontent.com/a/ACg8ocLqMeWtsRY85r-QPVe8hW6nuusyr01ufXW6Au2Kj21L0MeXLdTc=s96-c', 'active', '0', 'lUE7PfMWYcaphZThZNE9J6nKB30rzR35fP9EdpcwoK2dk6NmyN5KIR1OnXHB', '2026-09-19 10:22:32', '2026-09-19 10:22:32');

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
