-- ==========================================================
-- Cartzy Live Database Dump / Schema
-- Database: `ecommerce_db`
-- Auto-synced at: 2026-10-12 11:51:30
-- ==========================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `ecommerce_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ecommerce_db`;

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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `addresses` (4 rows)
INSERT INTO `addresses` (`id`, `user_id`, `label`, `recipient`, `phone`, `line1`, `barangay`, `city`, `province`, `postal_code`, `is_default`, `created_at`, `updated_at`) VALUES
('1', '1', 'Default Address', 'jess Pambago', '09773587409', '1011, purok 4', 'Masapang', 'Victoria', 'Laguna', '4011', '1', '2026-09-21 06:03:57', '2026-09-21 06:03:57'),
('2', '4', 'Default Address', 'Pajavera, Nhieckaella Ashley R.', '09152608445', 'St. Burol', 'Magdapio', 'Pagsanjan', 'Laguna', '4008', '1', '2026-09-21 06:03:57', '2026-09-21 06:03:57'),
('5', '10', 'Home', 'Jessica Pambago', '09773587409', '1011, purok 4', 'Masapang', 'Victoria', 'Laguna', '4011', '1', '2026-10-10 06:04:29', '2026-10-10 06:04:29'),
('6', '28', 'Main Warehouse & Pickup Hub', 'happyskinphil Warehouse', '09178889900', 'High Street Central, BGC', 'Fort Bonifacio', 'Taguig City', 'Metro Manila', '1634', '1', '2026-10-11 07:23:16', '2026-10-11 07:23:16');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `admin_audit_logs` (1 rows)
INSERT INTO `admin_audit_logs` (`id`, `user_id`, `action`, `description`, `created_at`, `updated_at`) VALUES
('1', '5', 'Product visibility changed', 'Ceramic Coffee Mug Saucer Set — inactive', '2026-10-12 06:26:11', '2026-10-12 06:26:11');

-- --------------------------------------------------------
-- Table structure for table `admin_disputes`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `admin_disputes`;
CREATE TABLE `admin_disputes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `subject` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_disputes_order_id_foreign` (`order_id`),
  KEY `admin_disputes_status_index` (`status`),
  CONSTRAINT `admin_disputes_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
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
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `app_notifications_user_id_foreign` (`user_id`),
  CONSTRAINT `app_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `app_notifications` (1 rows)
INSERT INTO `app_notifications` (`id`, `user_id`, `type`, `title`, `message`, `link`, `is_read`, `created_at`, `updated_at`) VALUES
('1', '10', 'order', 'Order Placed', 'Your order ORD-2026-53254 was successfully placed.', 'http://cartzyy.shop/dashboard?tab=orders', '1', '2026-10-10 06:21:01', '2026-10-10 06:21:08');

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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `buyers` (7 rows)
INSERT INTO `buyers` (`id`, `user_id`, `name`, `middle_initial`, `sex`, `birthday`, `age`, `phone`, `default_address_id`, `id_photo`, `id_type`, `id_number`, `id_status`, `id_rejection_reason`, `id_verified_at`, `loyalty_points`, `membership_tier`, `status`, `notes`, `created_at`, `updated_at`) VALUES
('1', '1', 'jess Pambago', NULL, 'Female', '2005-12-05', '20', '09773587409', '1', NULL, NULL, NULL, 'unverified', NULL, NULL, '0', 'Regular', 'active', NULL, '2026-09-06 10:07:45', '2026-09-19 09:39:48'),
('2', '2', 'Jessie Bajamundi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, '0', 'Regular', 'active', NULL, '2026-09-19 09:38:11', '2026-10-10 05:27:55'),
('3', '3', 'Leonardo, Tiffany Joy O.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, '0', 'Regular', 'active', NULL, '2026-09-19 10:08:30', '2026-10-10 05:27:55'),
('4', '4', 'Pajavera, Nhieckaella Ashley R.', NULL, NULL, '2005-11-14', '20', '09152608445', '2', NULL, NULL, NULL, 'unverified', NULL, NULL, '0', 'Regular', 'active', NULL, '2026-09-19 10:08:50', '2026-10-10 05:27:55'),
('5', '8', 'Maria Dela Cruz', NULL, NULL, NULL, NULL, '09175556666', NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, '0', 'Regular', 'active', NULL, '2026-09-19 10:14:46', '2026-09-19 10:14:46'),
('6', '9', 'Del Mundo, Jan Reyben, D.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, '0', 'Regular', 'active', NULL, '2026-09-19 10:22:32', '2026-10-10 05:27:55'),
('7', '10', 'Jessica Pambago', NULL, NULL, NULL, NULL, NULL, '5', NULL, 'National ID', '1234-5678-9012', 'verified', NULL, '2026-10-10 05:54:17', '0', 'Regular', 'active', NULL, '2026-09-23 02:11:29', '2026-10-10 06:04:25');

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
INSERT INTO `cart_items` (`id`, `cart_id`, `product_variant_id`, `quantity`, `selected`, `created_at`, `updated_at`) VALUES
('1', '1', '3', '1', '1', '2026-09-21 06:03:58', '2026-09-21 06:03:58');

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
INSERT INTO `carts` (`id`, `user_id`, `created_at`, `updated_at`) VALUES
('1', '1', '2026-09-21 06:03:58', '2026-09-21 06:03:58');

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
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `categories` (23 rows)
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
('23', NULL, 'General', 'general', '0', '1', '2026-10-10 06:21:01', '2026-10-10 06:21:01');

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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `delivery_events` (7 rows)
INSERT INTO `delivery_events` (`id`, `shipment_id`, `status`, `attempt`, `user_id`, `note`, `photo_path`, `occurred_at`, `created_at`, `updated_at`) VALUES
('1', '1', 'order_placed', '1', '10', 'Order placed by buyer.', NULL, '2026-10-10 06:21:01', '2026-10-10 06:21:01', '2026-10-10 06:21:01'),
('2', '2', 'in_transit', '1', '11', 'Status update: in_transit', NULL, '2026-10-11 06:51:57', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('3', '3', 'out_for_delivery', '1', '11', 'Status update: out_for_delivery', NULL, '2026-10-11 06:51:57', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('4', '4', 'unassigned', '1', '11', 'Status update: unassigned', NULL, '2026-10-11 06:51:57', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('5', '5', 'delivered', '1', '11', 'Status update: delivered', NULL, '2026-10-11 06:51:57', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('6', '6', 'unassigned', '1', '11', 'Status update: unassigned', NULL, '2026-10-11 06:51:57', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('7', '7', 'failed', '1', '11', 'Status update: failed', NULL, '2026-10-11 06:51:57', '2026-10-11 06:51:57', '2026-10-11 06:51:57');

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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `logistics_messages` (8 rows)
INSERT INTO `logistics_messages` (`id`, `logistics_provider_id`, `contact_user_id`, `sender_type`, `message`, `read_at`, `created_at`, `updated_at`) VALUES
('1', '1', '5', 'contact', 'Hello Hub! When will the rider arrive for parcel collection?', NULL, '2026-10-11 06:26:57', '2026-10-11 06:51:57'),
('2', '1', '5', 'hub', 'Rider has been dispatched. ETA is around 15 to 20 minutes.', NULL, '2026-10-11 06:41:57', '2026-10-11 06:51:57'),
('3', '1', '6', 'contact', 'Hello Hub! When will the rider arrive for parcel collection?', NULL, '2026-10-11 06:26:57', '2026-10-11 06:51:57'),
('4', '1', '6', 'hub', 'Rider has been dispatched. ETA is around 15 to 20 minutes.', NULL, '2026-10-11 06:41:57', '2026-10-11 06:51:57'),
('5', '1', '7', 'contact', 'Hello Hub! When will the rider arrive for parcel collection?', NULL, '2026-10-11 06:26:57', '2026-10-11 06:51:57'),
('6', '1', '7', 'hub', 'Rider has been dispatched. ETA is around 15 to 20 minutes.', NULL, '2026-10-11 06:41:57', '2026-10-11 06:51:57'),
('7', '1', '12', 'contact', 'Hello Hub! When will the rider arrive for parcel collection?', NULL, '2026-10-11 06:26:57', '2026-10-11 06:51:57'),
('8', '1', '12', 'hub', 'Rider has been dispatched. ETA is around 15 to 20 minutes.', NULL, '2026-10-11 06:41:57', '2026-10-11 06:51:57');

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
INSERT INTO `logistics_providers` (`id`, `user_id`, `name`, `slug`, `status`, `rejection_reason`, `contact_phone`, `created_at`, `updated_at`) VALUES
('1', '11', 'Metro South Sorting & Fulfillment Hub', 'cartzy-express-logistics', 'approved', NULL, '09173334444', '2026-09-21 06:03:57', '2026-10-11 06:51:54');

-- --------------------------------------------------------
-- Table structure for table `migrations`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `migrations` (29 rows)
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
('30', '2026_10_12_000003_add_cancellation_review_to_seller_orders', '17');

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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `order_items` (7 rows)
INSERT INTO `order_items` (`id`, `seller_order_id`, `product_id`, `product_variant_id`, `product_name`, `variant_name`, `unit_price_minor`, `quantity`, `created_at`, `updated_at`) VALUES
('1', '1', '3', '4', 'Ceramic Coffee Mug Saucer Set', 'White', '28000', '1', '2026-10-10 06:21:01', '2026-10-10 06:21:01'),
('2', '2', '1', '1', 'Sony WH-1000XM5 Headphones x1', 'Default', '899000', '1', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('3', '3', '1', '1', 'SK-II Facial Essence 160ml x1', 'Default', '250000', '1', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('4', '4', '1', '1', 'Nike Air Max 270 Size 42 x1', 'Default', '549900', '1', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('5', '5', '1', '1', 'Adidas Running Shoes x1, Sports Bag x1', 'Default', '250000', '1', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('6', '6', '1', '1', 'Samsung 65\" OLED TV x1', 'Default', '8999000', '1', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('7', '7', '1', '1', 'Skincare Bundle Set x1', 'Default', '129900', '1', '2026-10-11 06:51:57', '2026-10-11 06:51:57');

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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `orders` (7 rows)
INSERT INTO `orders` (`id`, `buyer_id`, `reference`, `total_minor`, `payment_method`, `payment_status`, `shipping_address`, `created_at`, `updated_at`) VALUES
('1', '10', 'ORD-2026-53254', '34000', 'wallet', 'pending', '{\"phone\": \"09773587409\", \"address\": \"1011, purok 4, Brgy. Masapang, Victoria, Laguna, 4011\", \"recipient\": \"Jessica Pambago\"}', '2026-10-10 06:21:01', '2026-10-10 06:21:01'),
('2', '1', 'ORD-LOG-1000', '899000', 'cod', 'pending', '{\"phone\": \"09171234567\", \"address\": \"45 Magsaysay St, Brgy. 1, Santa Cruz, Laguna\", \"recipient\": \"Juan Dela Cruz\"}', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('3', '1', 'ORD-LOG-1001', '250000', 'wallet', 'paid', '{\"phone\": \"09271234567\", \"address\": \"12 Maharlika Hwy, Brgy. San Jose, San Pablo, Laguna\", \"recipient\": \"Elena Reyes\"}', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('4', '1', 'ORD-LOG-1002', '549900', 'cod', 'pending', '{\"phone\": \"09451234567\", \"address\": \"7 Aguinaldo Ave, Brgy. Canlubang, Calamba, Laguna\", \"recipient\": \"Pedro Garcia\"}', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('5', '1', 'ORD-LOG-1003', '250000', 'wallet', 'paid', '{\"phone\": \"09181234567\", \"address\": \"33 JP Laurel Hwy, Brgy. Marawoy, Lipa, Batangas\", \"recipient\": \"Ana Lim\"}', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('6', '1', 'ORD-LOG-1004', '8999000', 'cod', 'pending', '{\"phone\": \"09921234567\", \"address\": \"88 Bonifacio St, Brgy. Centro 1, Lucena, Quezon\", \"recipient\": \"Mark Torres\"}', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('7', '1', 'ORD-LOG-1005', '129900', 'cod', 'pending', '{\"phone\": \"09311234567\", \"address\": \"14 Lapu-Lapu St, Brgy. Palho, Calamba, Laguna\", \"recipient\": \"Rhea Castillo\"}', '2026-10-11 06:51:57', '2026-10-11 06:51:57');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `payments` (1 rows)
INSERT INTO `payments` (`id`, `order_id`, `method`, `amount_minor`, `status`, `provider_ref`, `created_at`, `updated_at`) VALUES
('1', '1', 'GCash', '34000', 'pending', NULL, '2026-10-10 06:21:01', '2026-10-10 06:21:01');

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `pickup_requests` (3 rows)
INSERT INTO `pickup_requests` (`id`, `logistics_provider_id`, `seller_id`, `seller_order_id`, `parcel_count`, `weight_kg`, `pickup_date`, `pickup_time`, `address`, `status`, `confirmed_at`, `notes`, `created_at`, `updated_at`) VALUES
('1', '1', '1', NULL, '8', '12.40', '2026-10-11', '2:00 PM', '123 Rizal St, Brgy. Poblacion, Santa Cruz, Laguna', 'pending', NULL, 'Fragile electronics. Handle with care.', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('2', '1', '2', NULL, '5', '3.80', '2026-10-11', '3:30 PM', '45 Mabini Ave, Brgy. San Jose, San Pablo, Laguna', 'confirmed', '2026-10-11 04:51:57', NULL, '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('3', '1', '3', NULL, '14', '22.10', '2026-10-12', '10:00 AM', '88 Quezon Blvd, Brgy. Halang, Calamba, Laguna', 'pending', NULL, 'Boxes are large. Bring extra packaging tape.', '2026-10-11 06:51:57', '2026-10-11 06:51:57');

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `product_images` (3 rows)
INSERT INTO `product_images` (`id`, `product_id`, `path`, `position`, `created_at`, `updated_at`) VALUES
('1', '1', 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=600&h=600&fit=crop&q=80', '0', '2026-09-21 06:03:57', '2026-09-21 06:03:57'),
('2', '2', 'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=600&h=600&fit=crop&q=80', '0', '2026-09-21 06:03:57', '2026-09-21 06:03:57'),
('3', '3', 'https://images.unsplash.com/photo-1544816155-12df9643f363?w=500&h=500&fit=crop&q=80', '0', '2026-10-10 06:21:01', '2026-10-10 06:21:01');

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `product_variants` (4 rows)
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `name`, `image_path`, `options`, `price_minor`, `stock`, `weight_grams`, `is_active`, `deleted_at`, `created_at`, `updated_at`) VALUES
('1', '1', 'ANC-BLK', 'Matte Black', NULL, '{\"color\": \"Black\"}', '89000', '50', '250', '1', NULL, '2026-09-21 06:03:57', '2026-09-21 06:03:57'),
('2', '1', 'ANC-WHT', 'Glossy White', NULL, '{\"color\": \"White\"}', '89000', '40', '250', '1', NULL, '2026-09-21 06:03:57', '2026-09-21 06:03:57'),
('3', '2', 'FIT-SMT-01', 'Midnight Black / Standard Strap', NULL, '{\"size\": \"Standard\", \"color\": \"Black\"}', '129900', '35', '180', '1', NULL, '2026-09-21 06:03:57', '2026-09-21 06:03:57'),
('4', '3', 'CAT-5-25a817', 'White', NULL, NULL, '28000', '999', '0', '1', NULL, '2026-10-10 06:21:01', '2026-10-10 06:21:01');

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
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  KEY `products_seller_id_is_active_index` (`seller_id`,`is_active`),
  KEY `products_category_id_is_active_index` (`category_id`,`is_active`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `products_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `products` (3 rows)
INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `slug`, `description`, `gender`, `badge`, `is_active`, `deleted_at`, `created_at`, `updated_at`) VALUES
('1', '1', '2', 'ANC Pro Wireless Noise Cancelling Earphones', 'anc-pro-wireless-noise-cancelling-earphones', 'High-fidelity audio with active noise cancellation, 32-hour battery life, and ultra-low latency gaming mode.', 'unisex', NULL, '1', NULL, '2026-09-21 06:03:57', '2026-09-21 06:03:57'),
('2', '1', '3', 'Smart Fitness Tracker Watch with Blood Oxygen & Heart Rate', 'smart-fitness-tracker-watch-blood-oxygen', 'Track your vitals 24/7 with AMOLED display, IP68 water resistance, and 14-day standby time.', 'unisex', NULL, '1', NULL, '2026-09-21 06:03:57', '2026-09-21 06:03:57'),
('3', '2', '23', 'Ceramic Coffee Mug Saucer Set', 'catalog-5', NULL, 'unisex', NULL, '0', NULL, '2026-10-10 06:21:01', '2026-10-12 06:26:11');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'approved',
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `riders` (7 rows)
INSERT INTO `riders` (`id`, `user_id`, `logistics_provider_id`, `vehicle_type`, `plate_no`, `status`, `rejection_reason`, `area`, `rating`, `deliveries_today`, `is_active`, `created_at`, `updated_at`) VALUES
('1', '7', '1', 'Motorcycle', 'ND-4921', 'approved', NULL, NULL, '5.00', '0', '1', '2026-09-21 06:03:58', '2026-09-21 06:03:58'),
('2', '22', '1', 'Motorcycle', 'NQ-4812', 'approved', NULL, 'Laguna - Santa Cruz', '4.95', '12', '1', '2026-10-11 06:51:54', '2026-10-11 06:51:54'),
('3', '23', '1', 'Motorcycle', 'MN-3421', 'approved', NULL, 'Laguna - Calamba', '4.82', '9', '1', '2026-10-11 06:51:55', '2026-10-11 06:51:55'),
('4', '24', '1', 'Motorcycle', 'LK-7829', 'approved', NULL, 'Batangas - Lipa', '4.60', '0', '0', '2026-10-11 06:51:55', '2026-10-11 06:51:55'),
('5', '25', '1', 'Motorcycle', 'QR-1234', 'pending', NULL, 'Quezon - Lucena', '5.00', '0', '0', '2026-10-11 06:51:56', '2026-10-11 06:51:56'),
('6', '26', '1', 'Motorcycle', 'PH-5678', 'pending', NULL, 'Laguna - San Pablo', '5.00', '0', '0', '2026-10-11 06:51:56', '2026-10-11 06:51:56'),
('7', '27', '1', 'Bicycle', 'N/A', 'rejected', 'Incomplete documents. NBI Clearance expired.', NULL, '5.00', '0', '0', '2026-10-11 06:51:57', '2026-10-11 06:51:57');

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

-- Dumping data for table `role_user` (20 rows)
INSERT INTO `role_user` (`role_id`, `user_id`, `created_at`, `updated_at`) VALUES
('1', '1', NULL, NULL),
('1', '2', NULL, NULL),
('1', '3', NULL, NULL),
('1', '4', NULL, NULL),
('1', '8', NULL, NULL),
('1', '9', NULL, NULL),
('1', '29', NULL, NULL),
('1', '30', NULL, NULL),
('2', '6', NULL, NULL),
('2', '28', NULL, NULL),
('3', '7', NULL, NULL),
('3', '11', NULL, NULL),
('4', '7', NULL, NULL),
('4', '22', NULL, NULL),
('4', '23', NULL, NULL),
('4', '24', NULL, NULL),
('4', '25', NULL, NULL),
('4', '26', NULL, NULL),
('4', '27', NULL, NULL),
('5', '5', NULL, NULL);

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
INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES
('1', 'buyer', '2026-09-21 06:03:56', '2026-09-21 06:03:56'),
('2', 'seller', '2026-09-21 06:03:56', '2026-09-21 06:03:56'),
('3', 'logistics', '2026-09-21 06:03:56', '2026-09-21 06:03:56'),
('4', 'rider', '2026-09-21 06:03:56', '2026-09-21 06:03:56'),
('5', 'admin', '2026-09-21 06:03:56', '2026-09-21 06:03:56');

-- --------------------------------------------------------
-- Table structure for table `seller_messages`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `seller_messages`;
CREATE TABLE `seller_messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `seller_order_id` bigint unsigned NOT NULL,
  `sender_id` bigint unsigned NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `seller_messages_sender_id_foreign` (`sender_id`),
  KEY `seller_messages_seller_order_id_created_at_index` (`seller_order_id`,`created_at`),
  CONSTRAINT `seller_messages_seller_order_id_foreign` FOREIGN KEY (`seller_order_id`) REFERENCES `seller_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `seller_messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `seller_orders` (7 rows)
INSERT INTO `seller_orders` (`id`, `order_id`, `seller_id`, `logistics_provider_id`, `subtotal_minor`, `shipping_fee_minor`, `commission_minor`, `status`, `delivered_at`, `note`, `created_at`, `updated_at`, `cancellation_status`, `cancellation_reason`, `cancellation_requested_at`, `cancellation_reviewed_at`) VALUES
('1', '1', '2', '1', '28000', '6000', '0', 'pending', NULL, NULL, '2026-10-10 06:21:01', '2026-10-10 06:38:26', NULL, NULL, NULL, NULL),
('2', '2', '1', '1', '899000', '6000', '0', 'shipped', NULL, 'Sony WH-1000XM5 Headphones x1', '2026-10-11 06:51:57', '2026-10-11 06:51:57', NULL, NULL, NULL, NULL),
('3', '3', '2', '1', '250000', '6000', '0', 'shipped', NULL, 'SK-II Facial Essence 160ml x1', '2026-10-11 06:51:57', '2026-10-11 06:51:57', NULL, NULL, NULL, NULL),
('4', '4', '1', '1', '549900', '6000', '0', 'shipped', NULL, 'Nike Air Max 270 Size 42 x1', '2026-10-11 06:51:57', '2026-10-11 06:51:57', NULL, NULL, NULL, NULL),
('5', '5', '3', '1', '250000', '6000', '0', 'delivered', '2026-10-10 06:51:57', 'Adidas Running Shoes x1, Sports Bag x1', '2026-10-11 06:51:57', '2026-10-11 06:51:57', NULL, NULL, NULL, NULL),
('6', '6', '1', '1', '8999000', '6000', '0', 'shipped', NULL, 'Samsung 65\" OLED TV x1', '2026-10-11 06:51:57', '2026-10-11 06:51:57', NULL, NULL, NULL, NULL),
('7', '7', '2', '1', '129900', '6000', '0', 'ready_to_ship', NULL, 'Skincare Bundle Set x1', '2026-10-11 06:51:57', '2026-10-11 06:51:57', NULL, NULL, NULL, NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `sellers` (12 rows)
INSERT INTO `sellers` (`id`, `user_id`, `name`, `slug`, `description`, `logo_path`, `banner_path`, `status`, `rejection_reason`, `commission_bps`, `pickup_address_id`, `created_at`, `updated_at`) VALUES
('1', '6', 'TechZone Official Mall', 'techzone-official-mall', 'Your premier destination for authentic wireless earbuds, smartwatches, and premium accessories.', 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=200&h=200&fit=crop&q=80', 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=1200&h=400&fit=crop&q=80', 'approved', NULL, '800', NULL, '2026-09-21 06:03:57', '2026-09-21 06:03:57'),
('2', '12', 'Hearth & Home', 'catalog-hearth-home', 'Catalog store for Hearth & Home.', NULL, NULL, 'approved', NULL, '0', NULL, '2026-10-10 06:38:23', '2026-10-10 06:38:23'),
('3', '13', 'Circuit Hub', 'catalog-circuit-hub', 'Catalog store for Circuit Hub.', NULL, NULL, 'approved', NULL, '0', NULL, '2026-10-10 06:38:23', '2026-10-10 06:38:23'),
('4', '14', 'Sole Street', 'catalog-sole-street', 'Catalog store for Sole Street.', NULL, NULL, 'approved', NULL, '0', NULL, '2026-10-10 06:38:24', '2026-10-10 06:38:24'),
('5', '15', 'Everyday Style', 'catalog-everyday-style', 'Catalog store for Everyday Style.', NULL, NULL, 'approved', NULL, '0', NULL, '2026-10-10 06:38:24', '2026-10-10 06:38:24'),
('6', '16', 'Bloom Beauty', 'catalog-bloom-beauty', 'Catalog store for Bloom Beauty.', NULL, NULL, 'approved', NULL, '0', NULL, '2026-10-10 06:38:24', '2026-10-10 06:38:24'),
('7', '17', 'Little Sprouts', 'catalog-little-sprouts', 'Catalog store for Little Sprouts.', NULL, NULL, 'approved', NULL, '0', NULL, '2026-10-10 06:38:25', '2026-10-10 06:38:25'),
('8', '18', 'Handy Home Tools', 'catalog-handy-home', 'Catalog store for Handy Home Tools.', NULL, NULL, 'approved', NULL, '0', NULL, '2026-10-10 06:38:25', '2026-10-10 06:38:25'),
('9', '19', 'Happy Paws', 'catalog-happy-paws', 'Catalog store for Happy Paws.', NULL, NULL, 'approved', NULL, '0', NULL, '2026-10-10 06:38:26', '2026-10-10 06:38:26'),
('10', '20', 'Paper & Palette', 'catalog-paper-palette', 'Catalog store for Paper & Palette.', NULL, NULL, 'approved', NULL, '0', NULL, '2026-10-10 06:38:26', '2026-10-10 06:38:26'),
('11', '21', 'Auto Essentials', 'catalog-auto-essentials', 'Catalog store for Auto Essentials.', NULL, NULL, 'approved', NULL, '0', NULL, '2026-10-10 06:38:26', '2026-10-10 06:38:26'),
('12', '28', 'happyskinphil', 'happyskinphil', 'Official happyskinphil Store - Authentic skincare and makeup essentials.', NULL, NULL, 'approved', NULL, '800', '6', '2026-10-11 07:23:16', '2026-10-11 07:23:16');

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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `shipments` (7 rows)
INSERT INTO `shipments` (`id`, `seller_order_id`, `logistics_provider_id`, `rider_id`, `tracking_code`, `status`, `delivery_area`, `sort_status`, `weight_kg`, `received_at`, `sorted_at`, `assigned_at`, `fee_minor`, `cod_amount_minor`, `cod_collected`, `attempts`, `created_at`, `updated_at`) VALUES
('1', '1', '1', NULL, 'CTZ-VEM2ANKBMO', 'unassigned', NULL, 'unsorted', '1.00', NULL, NULL, NULL, '6000', '0', '0', '0', '2026-10-10 06:21:01', '2026-10-10 06:21:01'),
('2', '2', '1', '2', 'CTZY-20261011-0001', 'in_transit', 'Laguna - Santa Cruz', 'sorted', '1.50', '2026-10-11 01:51:57', '2026-10-11 02:51:57', '2026-10-11 03:51:57', '6000', '899000', '0', '0', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('3', '3', '1', '3', 'CTZY-20261011-0002', 'out_for_delivery', 'Laguna - San Pablo', 'sorted', '0.40', '2026-10-11 00:51:57', '2026-10-11 01:51:57', '2026-10-11 02:51:57', '6000', '0', '0', '0', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('4', '4', '1', NULL, 'CTZY-20261011-0003', 'unassigned', NULL, 'unsorted', '1.20', '2026-10-11 04:51:57', NULL, NULL, '6000', '549900', '0', '0', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('5', '5', '1', '4', 'CTZY-20261011-0004', 'delivered', 'Batangas - Lipa', 'assigned', '2.80', '2026-10-09 22:51:57', '2026-10-09 23:51:57', '2026-10-10 00:51:57', '6000', '0', '0', '0', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('6', '6', '1', NULL, 'CTZY-20261011-0005', 'unassigned', NULL, 'unsorted', '28.50', '2026-10-11 05:51:57', NULL, NULL, '6000', '8999000', '0', '0', '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('7', '7', '1', '3', 'CTZY-20261011-0006', 'failed', 'Laguna - Calamba', 'sorted', '0.90', '2026-10-10 01:51:57', '2026-10-10 02:51:57', '2026-10-10 03:51:57', '6000', '129900', '0', '1', '2026-10-11 06:51:57', '2026-10-11 06:51:57');

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
  `business_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_google_id_unique` (`google_id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `users` (30 rows)
INSERT INTO `users` (`id`, `name`, `middle_initial`, `sex`, `birthday`, `age`, `id_photo`, `id_type`, `id_number`, `id_status`, `id_rejection_reason`, `id_verified_at`, `email`, `google_id`, `phone`, `street_address`, `address`, `region`, `province`, `city`, `barangay`, `postal_code`, `business_name`, `dti_permit`, `line_of_business`, `email_verified_at`, `password`, `role`, `avatar`, `status`, `is_suspended`, `remember_token`, `created_at`, `updated_at`) VALUES
('1', 'jess Pambago', NULL, 'Female', '2005-12-05', '20', NULL, NULL, NULL, 'unverified', NULL, NULL, 'jessicapambago27@gmail.com', '102943877745797449792', '09773587409', '1011, purok 4', '1011, purok 4, Brgy. Masapang, Victoria, Laguna, IV-A, 4011', 'IV-A', 'Laguna', 'Victoria', 'Masapang', '4011', NULL, NULL, NULL, '2026-09-19 09:39:48', '$2y$12$3nOKEW1najIzdZqV2g.KSOp2TnIrXnnwN.m.cXjZeU9htWcSh28tW', 'buyer', 'https://lh3.googleusercontent.com/a/ACg8ocKRnO3z--zfu4NoW4iXG9RuYJ4W7Hc5Ty8Q9LfGYo8PptOyDiQ=s96-c', 'active', '0', 'EB2rd27b7ZX1drhKddrYRIscH45FPQyBmAudrAz2FfE3gEjxYXVIxKnjmCyE', '2026-09-06 10:07:45', '2026-09-19 09:39:48'),
('2', 'Jessie Bajamundi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'bajamundijessie2@gmail.com', '105267381396672766135', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-10 05:27:55', '$2y$12$T1E7WJ09XHkR69TZhisBfuxO87Qu4x8gfTpp3e1iHINstiNoyAz/y', 'buyer', 'https://lh3.googleusercontent.com/a/ACg8ocJpogT-VfJhnw40Xyj5tD46nnZ8k-cTondgVTm5pl7QXgtv-FE=s96-c', 'active', '0', 'UyY0OQ7Jb0ucp8Xl5kNmhb9va56wxMFudR9dXmLRwJhMmNPFvswsit0vUbWy', '2026-09-19 09:38:11', '2026-10-10 05:27:55'),
('3', 'Leonardo, Tiffany Joy O.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'tiffany.leonardolspu@gmail.com', '102856395965406016321', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-10 05:27:55', '$2y$12$2rPlMMwYfnaULGFzuFg7FOJSONDLg43g3jm/W13JnB9onE.Bl3qb2', 'buyer', 'https://lh3.googleusercontent.com/a/ACg8ocJIBmZGdR_My3EM3rpyD5hHikz_j0zobdBv9aAD8Ts4A9fNJQzz=s96-c', 'active', '0', 'XFRqpG7uRMEKMsl83tNlvNJth2EcYJzKeRj8M5xbZerKBOl8njvEBcVmxr29', '2026-09-19 10:08:30', '2026-10-10 05:27:55'),
('4', 'Pajavera, Nhieckaella Ashley R.', NULL, NULL, '2005-11-14', '20', NULL, NULL, NULL, 'unverified', NULL, NULL, 'nhieckaella05@gmail.com', '109760982595777107804', '09152608445', 'St. Burol', 'St. Burol, Brgy. Magdapio, Pagsanjan, Laguna, IV-A, 4008', 'IV-A', 'Laguna', 'Pagsanjan', 'Magdapio', '4008', NULL, NULL, NULL, '2026-10-10 05:27:55', '$2y$12$1rq7GlIrNoH9D8/5.maRFeJpY7YL7dtXCSG7IW0ETH.ZnmrTJuPoK', 'buyer', 'https://lh3.googleusercontent.com/a/ACg8ocLp_Yq1EHEIBYc28poVjnMKVg-6M7ZvC0FVN-PMoSsQMcZQcJpw=s96-c', 'active', '0', '3nj4tC6v9kUD0XpA9JVCiMozO0Sq1W1gp7pdvfTfXHXGTR1Zb0ePtx1dipzO', '2026-09-19 10:08:50', '2026-10-10 05:27:55'),
('5', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'administrationa570@gmail.com', NULL, '09170000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$K.ZOCaZwXJ/eeTCXb4Qj.u.enxvmlXYylGlLZ2D4NRBNmK2QguLOy', 'admin', NULL, 'active', '0', 'Y6mBkJh4oUnPBULZYpU3HimA6Tm7s74p0jJ2lCUa6KvFBA3YUzbtProyMx2D', '2026-09-19 10:14:45', '2026-09-19 10:14:45'),
('6', 'Official Tech Store', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'seller@shopee.ph', NULL, '09171112222', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$2ziGBNBXL5F54/ky87PlK.e8jLoj7laH6f8b10gU7zbvsE2gwE1ca', 'seller', NULL, 'active', '0', NULL, '2026-09-19 10:14:45', '2026-10-02 10:12:52'),
('7', 'SPX Rider - Juan', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'courier@shopee.ph', NULL, '09173334444', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$T2HrurgfgDtHGEQdJ37fV.kZS64ItiJx7PVRZVMy.InsNuCbPv4Oi', 'courier', NULL, 'active', '0', NULL, '2026-09-19 10:14:46', '2026-09-19 10:14:46'),
('8', 'Maria Dela Cruz', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'buyer@shopee.ph', NULL, '09175556666', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$6TEzJ1W3R1rvfGcDjJT0EujRLAaHR.qJs3En7aRtwnI2PTHkUlSDC', 'buyer', NULL, 'active', '0', NULL, '2026-09-19 10:14:46', '2026-09-19 10:14:46'),
('9', 'Del Mundo, Jan Reyben, D.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'reybendelmundo2005@gmail.com', '100001932505618334922', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-10 05:27:55', '$2y$12$nIUFGFX5nFSwpK89mDQOlu7MNR.bgexpjXwiQW8m7Du7KcPoLIPFC', 'buyer', 'https://lh3.googleusercontent.com/a/ACg8ocLqMeWtsRY85r-QPVe8hW6nuusyr01ufXW6Au2Kj21L0MeXLdTc=s96-c', 'active', '0', '2IuVqqFUn1EdwWxTSW3iOUcHMev16H0fWDpVc52xCr4uOcyBqq7RPgPgsavq', '2026-09-19 10:22:32', '2026-10-10 05:27:55'),
('10', 'Jessica Pambago', NULL, NULL, NULL, NULL, NULL, 'National ID', '1234-5678-9012', 'verified', NULL, '2026-10-10 05:54:17', 'jessica.p.bajamundi@gmail.com', '105023496569977781629', NULL, '1011, purok 4', '1011, purok 4, Brgy. Masapang, Victoria, Laguna, 4011', 'IV-A', 'Laguna', 'Victoria', 'Masapang', '4011', NULL, NULL, NULL, '2026-10-10 05:27:55', '$2y$12$FKxTiUCZPSseqM/aXmkcO.2CQNKw.WuJl3Tg5ZIVH12mIsvADwQLu', 'buyer', 'https://lh3.googleusercontent.com/a/ACg8ocLySMhHn9faFZJycPSeuiPaf_czFDlfAJT3lJi9SexiP_SZ8Cml=s96-c', 'active', '0', 'eYYEzpyvJx5V9kt9c3qgs6VVxl1cQqZor2ZiN2VUFCn0alTL2FQuWng95IhG', '2026-09-23 02:11:29', '2026-10-10 06:04:25'),
('11', 'Metro South Sorting & Fulfillment Hub', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'logistics@cartzy.ph', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$cOiZVv5Qz/eNPV68uwG3quimoGQmgy0cTA/GijiXUfHnhSP1mmO5W', 'logistics', NULL, 'active', '0', NULL, '2026-10-04 06:06:10', '2026-10-04 06:06:10'),
('12', 'Hearth & Home', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'hearth-home@catalog.cartzy.invalid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$qf.mju.fJHzAFIQYZcVLkuVq5YhDQg6aZVpUYzY7Eves1I1gcwivi', 'seller', NULL, 'active', '0', NULL, '2026-10-10 06:38:23', '2026-10-10 06:38:23'),
('13', 'Circuit Hub', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'circuit-hub@catalog.cartzy.invalid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$Q/sFeuONs0jpEdPIRQ3FH.I1fwlkr6A1icLVXPodJthhGABSLpefG', 'seller', NULL, 'active', '0', NULL, '2026-10-10 06:38:23', '2026-10-10 06:38:23'),
('14', 'Sole Street', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'sole-street@catalog.cartzy.invalid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$lTSUOasrhgNjVlNie57k1utm79FmnPRW2B0O/Iv5VHzpeAnMXwCwW', 'seller', NULL, 'active', '0', NULL, '2026-10-10 06:38:24', '2026-10-10 06:38:24'),
('15', 'Everyday Style', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'everyday-style@catalog.cartzy.invalid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$pfQq.obJAtYIHXVtvNUkW.jaFoxDAPZv/rS3y4JLZ8PQNlJlJeCZK', 'seller', NULL, 'active', '0', NULL, '2026-10-10 06:38:24', '2026-10-10 06:38:24'),
('16', 'Bloom Beauty', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'bloom-beauty@catalog.cartzy.invalid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$ompIsf33MQBqKenSK8XwTeoGBzXdEZ.zq5ZJE8FFCkgb/YFQQBUS2', 'seller', NULL, 'active', '0', NULL, '2026-10-10 06:38:24', '2026-10-10 06:38:24'),
('17', 'Little Sprouts', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'little-sprouts@catalog.cartzy.invalid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$LtfJJ4bC.eF7dALPove4t.O9oW6iAzsFYtcDdGYr2JsK33kGQdJom', 'seller', NULL, 'active', '0', NULL, '2026-10-10 06:38:25', '2026-10-10 06:38:25'),
('18', 'Handy Home Tools', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'handy-home@catalog.cartzy.invalid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$m/vzgJWSgvXBTKhJYRKGdeSizOnfQW5DSilMYReFN86C5/NsKSXd6', 'seller', NULL, 'active', '0', NULL, '2026-10-10 06:38:25', '2026-10-10 06:38:25'),
('19', 'Happy Paws', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'happy-paws@catalog.cartzy.invalid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$9zG2Frwk3pc97ABtlmD4c.ngVUqUE.09Aq0y.scA7twIRRIVCimJK', 'seller', NULL, 'active', '0', NULL, '2026-10-10 06:38:26', '2026-10-10 06:38:26'),
('20', 'Paper & Palette', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'paper-palette@catalog.cartzy.invalid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$JwhY0ovUbhkQoZq8Gd7/B.Nv9NMrNw4tVEWLOPePhmw3/xrENSZIu', 'seller', NULL, 'active', '0', NULL, '2026-10-10 06:38:26', '2026-10-10 06:38:26'),
('21', 'Auto Essentials', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'auto-essentials@catalog.cartzy.invalid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$/X0tdSCsO8lYbXtK9NuSoeXh2F6OKL02//c.iRY0dP9Z7MZghSprm', 'seller', NULL, 'active', '0', NULL, '2026-10-10 06:38:26', '2026-10-10 06:38:26'),
('22', 'Arnel Gomez', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'arnel.rider@cartzy.ph', NULL, '09285559876', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$.U4rFL/iAioRnYOHUE31Web/NBj649KGRZvtuqx9Da6C5Z8bnvXqO', 'courier', NULL, 'active', '0', NULL, '2026-10-11 06:51:54', '2026-10-11 06:51:54'),
('23', 'Carlo Santos', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'carlo.rider@cartzy.ph', NULL, '09178884455', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$U4kIueRiC.achT6ZdoUpPeYrnGTe.MSc/y7fK5OFu8T3dQ0EI.sjK', 'courier', NULL, 'active', '0', NULL, '2026-10-11 06:51:55', '2026-10-11 06:51:55'),
('24', 'Rex Villanueva', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'rex.rider@cartzy.ph', NULL, '09271112233', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$q2DutzCrgaW4aaRi5pBf/.SkZF3oGoETQhB1oo9flTMKaKzP2VIGO', 'courier', NULL, 'active', '0', NULL, '2026-10-11 06:51:55', '2026-10-11 06:51:55'),
('25', 'Jose Mabuhay', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'jose.rider@cartzy.ph', NULL, '09394445566', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$Ve3lWDEtioErgp5SYUC6tuHU6JF5b.5FaKdSzkmnrI6mXuL3q0pP6', 'courier', NULL, 'active', '0', NULL, '2026-10-11 06:51:56', '2026-10-11 06:51:56'),
('26', 'Bong Aquino', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'bong.rider@cartzy.ph', NULL, '09451234567', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$tWZcfb/2wTVyUCrbuTDjReO5glkKhbIz0QP8rQSgMf8rW/9C0l4Sa', 'courier', NULL, 'active', '0', NULL, '2026-10-11 06:51:56', '2026-10-11 06:51:56'),
('27', 'Noel Torres', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'unverified', NULL, NULL, 'noel.rider@cartzy.ph', NULL, '09119876543', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$nzhtkUmhmxvf4K5Ei5bqlujpacPE/Q0C9ZFxCyq4UFelEz5.OCr6a', 'courier', NULL, 'active', '0', NULL, '2026-10-11 06:51:57', '2026-10-11 06:51:57'),
('28', 'happyskinphil', NULL, NULL, NULL, NULL, NULL, 'DTI Business Registration', 'DTI-HSP-2026-0988', 'verified', NULL, '2026-10-11 07:23:16', 'happyskinphil@gmail.com', NULL, '09178889900', 'High Street Central', 'Bonifacio Global City, Taguig City, Metro Manila', 'NCR', 'Metro Manila', 'Taguig City', 'Fort Bonifacio', '1634', 'happyskinphil', NULL, NULL, '2026-10-11 07:23:16', '$2y$12$.jB0VXLcswGD9tCw7Aa2o.zLqmZA4S4JHPaQlb.D2H9koKYJXluei', 'seller', NULL, 'active', '0', NULL, '2026-10-11 07:23:16', '2026-10-11 07:23:16'),
('29', 'Mark_1123 Kram*sdas', NULL, NULL, '2017-01-01', '9', NULL, NULL, NULL, 'unverified', NULL, NULL, 'trillesmark11@gmail.com', NULL, '09532584860', '', '', '', '', '', '', '', NULL, NULL, NULL, '2026-10-12 07:51:18', '$2y$12$6wZhCYWg1ne7Qh60lBK83ud31ZxmbkB9aJ2oMdWggtS6DrST7VZrK', 'buyer', NULL, 'active', '0', NULL, '2026-10-12 07:51:18', '2026-10-12 08:13:40'),
('30', 'Mak Mac', NULL, NULL, '2026-01-01', '0', NULL, NULL, NULL, 'unverified', NULL, NULL, 'strayerror.mx@gmail.com', NULL, '09532584', '', '', '', '', '', '', '', NULL, NULL, NULL, '2026-10-12 08:02:06', '$2y$12$HomiX5G.P2SflfV3EyVIceCTGo.JTzovgt3TFhPsrUWT94KUDOwHS', 'buyer', NULL, 'active', '0', NULL, '2026-10-12 08:02:07', '2026-10-12 08:02:07');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;
