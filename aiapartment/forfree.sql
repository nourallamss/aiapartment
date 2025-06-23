-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 24, 2025 at 12:29 AM
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
-- Database: `forfree`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `CleanupSecurityTables` ()   BEGIN
    -- Clean old login attempts (older than 7 days)
    DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 7 DAY);
    
    -- Clean expired remember tokens
    DELETE FROM remember_tokens WHERE expires_at < NOW();
    
    -- Clean expired sessions
    DELETE FROM sessions WHERE expires_at < NOW();
    
    -- Reset locked accounts after lockout period
    UPDATE users SET locked_until = NULL WHERE locked_until < NOW();
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `timestamp` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `details`, `ip_address`, `user_agent`, `timestamp`) VALUES
(1, 15, 'PROFILE_UPDATE', 'Username: Nour, Email: Nourallam304@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 1748442676),
(2, 15, 'PHOTO_UPDATE', 'Profile photo changed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 1748442711),
(3, 17, 'PHOTO_UPDATE', 'Profile photo changed', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1 Edg/136.0.0.0', 1748610238),
(4, 17, 'PHOTO_UPDATE', 'Profile photo changed', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1 Edg/136.0.0.0', 1748610498),
(5, 13, 'PHOTO_UPDATE', 'Profile photo changed', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1 Edg/136.0.0.0', 1748610667),
(6, 13, '2FA_ENABLED', '2FA authentication enabled', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1 Edg/136.0.0.0', 1748610675),
(7, 17, '2FA_ENABLED', '2FA authentication enabled', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 1748611779),
(8, 17, '2FA_ENABLED', '2FA authentication enabled', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 1748611836),
(9, 17, 'PHOTO_UPDATE', 'Profile photo changed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', 1750083148),
(10, 22, 'PHOTO_UPDATE', 'Profile photo changed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', 1750622450);

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_history`
--

CREATE TABLE `cart_history` (
  `id` int(11) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `property_id` int(11) NOT NULL,
  `action` enum('added','removed','quantity_changed') NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `data`
--

CREATE TABLE `data` (
  `id` int(11) NOT NULL,
  `location` varchar(255) NOT NULL,
  `property_type` varchar(100) NOT NULL,
  `rooms` int(11) NOT NULL,
  `bathrooms` int(11) NOT NULL,
  `kitchen` int(11) DEFAULT 0,
  `floor` int(11) DEFAULT 0,
  `reception` int(11) DEFAULT 0,
  `view` int(11) DEFAULT 0,
  `space` decimal(10,2) NOT NULL,
  `sale` decimal(15,2) NOT NULL,
  `totalsale` decimal(15,2) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `images` varchar(500) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `description` text DEFAULT NULL,
  `amenities` text DEFAULT NULL,
  `furnishing` varchar(20) DEFAULT 'Unfurnished',
  `parking` int(11) DEFAULT 0,
  `balcony` int(11) DEFAULT 0,
  `elevator` varchar(5) DEFAULT 'No',
  `garden` varchar(5) DEFAULT 'No',
  `swimming_pool` varchar(5) DEFAULT 'No',
  `gym` varchar(5) DEFAULT 'No',
  `security` varchar(20) DEFAULT 'None',
  `age_years` int(11) DEFAULT 0,
  `condition_status` varchar(20) DEFAULT 'Good',
  `delivery_date` varchar(50) DEFAULT NULL,
  `delivery_type` varchar(30) DEFAULT 'Ready',
  `payment_options` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `data`
--

INSERT INTO `data` (`id`, `location`, `property_type`, `rooms`, `bathrooms`, `kitchen`, `floor`, `reception`, `view`, `space`, `sale`, `totalsale`, `phone`, `email`, `images`, `latitude`, `longitude`, `created_at`, `updated_at`, `description`, `amenities`, `furnishing`, `parking`, `balcony`, `elevator`, `garden`, `swimming_pool`, `gym`, `security`, `age_years`, `condition_status`, `delivery_date`, `delivery_type`, `payment_options`) VALUES
(1, '138, Abd Al Rahman Al Rafei Street, Bakos, Al Dhahriya, Alexandria, 21616, Egypt', 'Apartment', 1, 2, 2, 4, 2, 0, 120.00, 1000.00, 10000.00, '010009871', 'Nourallam305@gmail.com', '', 0.00000000, 0.00000000, '2025-06-22 16:34:56', '2025-06-22 16:36:53', '', '[\"Maid Room\"]', 'Furnished', 2, 2, 'Yes', 'Yes', 'Yes', 'Yes', '24', 2, 'New', '', 'Ready', '[\"Installment\"]'),
(2, '166, Al Tahrir Street, Bab El Louk, Abdin, Cairo, 11513, Egypt', 'Apartment', 1, 2, 2, 1, 2, 0, 160.00, 1000000.00, 10000000.00, '01009087138', 'nour@homeeasy.com', 'uploads/properties/prop_6859533b6ba244.37913095_1750684475.jpg', 30.04479725, 31.23744135, '2025-06-23 13:14:35', '2025-06-23 13:37:02', '&quot;At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat.&quot;', '[\"Central A\\/C & Heating\",\"Private Garden\",\"Concierge Service\",\"Gym Access\"]', 'Furnished', 1, 2, 'Yes', 'Yes', 'Yes', 'Yes', '24', 0, 'New', '', 'Ready', '[\"Installment\"]'),
(3, '11cec', 'Warehouse', 1, 1, 1, 1, 1, 0, 234.00, 1000.00, 19999.00, '098765556666', 'admin@homeeasy.com', '', 0.00000000, 0.00000000, '2025-06-23 15:36:04', '2025-06-23 15:36:04', '4242', '[\"Pool\"]', 'Semi-Furnished', 2, 2, 'Yes', 'Yes', 'Yes', 'Yes', '24', 22, 'New', '', 'Ready', '[]');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `success` tinyint(1) DEFAULT 0,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `payment_method` enum('credit_card','paypal','bank_transfer','crypto') DEFAULT 'credit_card',
  `status` enum('pending','processing','completed','cancelled','refunded') DEFAULT 'pending',
  `order_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `property_id`, `user_email`, `price`, `quantity`, `payment_method`, `status`, `order_notes`, `created_at`, `updated_at`) VALUES
(1, 24, 'Nourallam304@gmail.com', 12.00, 1, 'credit_card', 'pending', NULL, '2025-05-25 15:01:45', '2025-05-25 15:01:45'),
(2, 24, 'Nourallam304@gmail.com', 12.00, 1, 'credit_card', 'pending', NULL, '2025-05-25 15:01:45', '2025-05-25 15:01:45'),
(3, 24, 'Nourallam304@gmail.com', 12.00, 1, 'bank_transfer', 'pending', NULL, '2025-05-25 15:04:09', '2025-05-25 15:04:09'),
(4, 24, 'Nourallam304@gmail.com', 12.00, 1, 'credit_card', 'pending', NULL, '2025-05-25 15:06:00', '2025-05-25 15:06:00'),
(5, 24, 'Nourallam304@gmail.com', 12.00, 1, 'credit_card', 'pending', NULL, '2025-05-25 15:06:56', '2025-05-25 15:06:56'),
(6, 24, 'Nourallam304@gmail.com', 12.00, 1, 'credit_card', 'pending', NULL, '2025-05-25 15:08:36', '2025-05-25 15:08:36'),
(7, 24, 'Nourallam304@gmail.com', 12.00, 1, 'credit_card', 'pending', NULL, '2025-05-25 15:08:54', '2025-05-25 15:08:54'),
(8, 22, 'Nourallam304@gmail.com', 1000000.00, 1, 'credit_card', 'pending', NULL, '2025-05-25 15:10:58', '2025-05-25 15:10:58'),
(9, 24, 'Nourallam304@gmail.com', 12.00, 1, 'credit_card', 'pending', NULL, '2025-05-25 15:14:11', '2025-05-25 15:14:11'),
(10, 24, 'Nourallam304@gmail.com', 12.00, 1, 'credit_card', 'pending', NULL, '2025-05-25 15:14:31', '2025-05-25 15:14:31'),
(11, 24, 'Nourallam304@gmail.com', 12.00, 1, 'credit_card', 'pending', NULL, '2025-05-25 15:14:55', '2025-05-25 15:14:55'),
(12, 24, 'Nourallam304@gmail.com', 12.00, 1, 'paypal', 'pending', NULL, '2025-05-25 15:23:02', '2025-05-25 15:23:02'),
(13, 24, 'Nourallam304@gmail.com', 12.00, 1, 'credit_card', 'pending', NULL, '2025-05-25 15:25:34', '2025-05-25 15:25:34'),
(14, 22, 'Nourallam304@gmail.com', 1000000.00, 1, 'credit_card', 'pending', NULL, '2025-05-25 15:28:28', '2025-05-25 15:28:28');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `property_type` varchar(100) NOT NULL,
  `rooms` int(11) NOT NULL,
  `bathrooms` int(11) NOT NULL,
  `area` decimal(10,2) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property_likes`
--

CREATE TABLE `property_likes` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_likes`
--

INSERT INTO `property_likes` (`id`, `property_id`, `user_email`, `created_at`) VALUES
(53, 2, 'admin@homeeasy.com', '2025-06-23 21:10:30'),
(54, 1, 'admin@homeeasy.com', '2025-06-23 22:04:06');

-- --------------------------------------------------------

--
-- Table structure for table `property_reports`
--

CREATE TABLE `property_reports` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `status` enum('pending','reviewed','resolved') DEFAULT 'pending',
  `reason` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_reports`
--

INSERT INTO `property_reports` (`id`, `property_id`, `user_email`, `status`, `reason`, `admin_notes`, `created_at`, `updated_at`) VALUES
(1, 1, 'support@homeeasy.com', 'pending', 'fake_listing', '', '2025-06-22 19:59:05', '2025-06-22 20:00:17');

-- --------------------------------------------------------

--
-- Table structure for table `rate_limits`
--

CREATE TABLE `rate_limits` (
  `id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `timestamp` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rate_limits`
--

INSERT INTO `rate_limits` (`id`, `action`, `identifier`, `timestamp`) VALUES
(7, 'photo_upload', '::1', 1750622450);

-- --------------------------------------------------------

--
-- Table structure for table `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_hash` varchar(64) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `security_events`
--

CREATE TABLE `security_events` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `event_type` varchar(50) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `security_logs`
--

CREATE TABLE `security_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `event_type` enum('login_success','login_failure','logout','password_change','account_locked','password_reset') NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('Dr9q40Kb7IQJwZyM38YkPQ6s3aygCAWBFgtK7OvJ', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiY1JVeDFWZFdITWMzYTYwN01veDh6QmdYRHJZT0pFcWRyTEJ2TDNHRyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9kYXNoYm9hcmQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1746454227),
('HWPIvEvYchmVx3lMISSUI8IiEWozcZxvKlEVzsFX', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; rv:11.0) like Gecko', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiT3ZqRVhmZFBvSlpNMGg4bm9FU3JTN1BzRFl1RFJ4anNVUlJOZHREcyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1746454070),
('Kq00F0F4Uk0mehPWlqTLi7JMTtlKgR3ynpMbbJT0', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; rv:11.0) like Gecko', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiclF2MlVNN2tRZG9XTDRwZmRQcFFaSTNsS0dvSkhKRjQ3aXdieXlKVyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1746453794);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `failed_login_attempts` int(11) DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `failed_attempts` int(11) DEFAULT 0,
  `last_login` datetime DEFAULT NULL,
  `last_ip` varchar(45) DEFAULT NULL,
  `two_fa_secret` varchar(255) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `is_active`, `failed_login_attempts`, `locked_until`, `last_login_at`, `created_at`, `updated_at`, `failed_attempts`, `last_login`, `last_ip`, `two_fa_secret`, `profile_photo`, `role`) VALUES
(12, 'NourAllam', '$2y$10$diea5V.M10b5IYmijl7/vu2wzzabTk03/9yfPUbHhdkrc.kGZ4L9G', 'nourallam4100@gmail.com', 1, 0, NULL, NULL, '2025-05-26 19:59:11', '2025-05-26 19:59:11', 0, NULL, NULL, NULL, NULL, 'user'),
(13, 'ANAS', '$2y$10$6EERrdJa5T/45bZlcpzDIemOECrJTph/kXCo7JTLKiCfAOxq9bbPO', 'Nourallam303@gmail.com', 1, 0, NULL, NULL, '2025-05-26 19:59:11', '2025-05-30 13:11:15', 0, NULL, NULL, 'B3NNIXUBAQBXOFLVFETDA2BP7HMGX7Q2', 'uploads/profile_photos/profile_13_67c450af02aa51f9178aa54ff955122a781625420cfd1cc47a830fa3232816a4.jpg', 'user'),
(15, 'Nour', '$2y$10$FzQP0vjYt9vO.lxVZVgGe.58Yx.nq4VoaQCuG1U3wJj96AJK5zSOC', 'Nourallam304@gmail.com', 1, 0, NULL, NULL, '2025-05-26 19:59:11', '2025-05-28 14:31:51', 0, NULL, NULL, NULL, 'uploads/profile_photos/profile_15_3320bdf02ed00a7131059bce88eddc531f08b85ce619b5e3d00022e061e19c24.jpg', 'user'),
(17, 'admin', '$2y$10$odzUANzIcpElU.Ruv3EHQu335BCVIE/7xbDBaXXHR0W325ZpWSr/W', 'admin@homeeasy.com', 1, 0, NULL, NULL, '2025-05-26 19:59:11', '2025-06-16 14:12:28', 0, NULL, NULL, '5H7RORPT3G2USYS4XMGYCWWPU3WCVJPA', 'uploads/profile_photos/profile_17_34d6f68a7fb88b841bd22c2b065a1a3f8ad969285968557d409eb79a7f694073.jpeg', 'admin'),
(22, 'support', '$2y$10$8UaVB8EuVLv7HnXAjG8EteZ1JQLHz2w84nEnHdVBm9aKuiRWqiGl6', 'support@homeeasy.com', 1, 0, NULL, NULL, '2025-06-22 19:55:48', '2025-06-22 20:00:50', 0, NULL, NULL, NULL, 'uploads/profile_photos/profile_22_fe718b5c1d40c5b6b2e40aaf09aae79913ef3f2e5d0085dca490878787be6b6e.jpg', 'user'),
(23, 'mvbgvnbdfgbb', '$2y$10$YvsWZz8RGU5qddX5vL7BV.ZrDrGXFrLcgq3p2DIf4iz6IAn2embIO', 'fdgrfgargrs@gmail.com', 1, 0, NULL, NULL, '2025-06-23 15:44:37', '2025-06-23 15:44:37', 0, NULL, NULL, NULL, NULL, 'user');

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

CREATE TABLE `user_preferences` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `preference_key` varchar(100) NOT NULL,
  `preference_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `your_table_name`
--

CREATE TABLE `your_table_name` (
  `id` int(11) NOT NULL,
  `rooms` int(11) DEFAULT NULL,
  `bathrooms` int(11) DEFAULT NULL,
  `kitchen` int(11) DEFAULT NULL,
  `floor` int(11) DEFAULT NULL,
  `reception` int(11) DEFAULT NULL,
  `view` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `space` decimal(10,2) DEFAULT NULL,
  `sale` enum('sale','rent') DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `images` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `property_type` varchar(100) DEFAULT NULL,
  `amenities` text DEFAULT NULL,
  `furnishing` enum('furnished','semi-furnished','unfurnished') DEFAULT NULL,
  `parking` tinyint(1) DEFAULT NULL,
  `balcony` tinyint(1) DEFAULT NULL,
  `elevator` tinyint(1) DEFAULT NULL,
  `garden` tinyint(1) DEFAULT NULL,
  `swimming_pool` tinyint(1) DEFAULT NULL,
  `gym` tinyint(1) DEFAULT NULL,
  `security` tinyint(1) DEFAULT NULL,
  `age_years` int(11) DEFAULT NULL,
  `condition_status` enum('new','used','needs renovation') DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `delivery_type` varchar(50) DEFAULT NULL,
  `payment_options` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_timestamp` (`timestamp`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cart_history`
--
ALTER TABLE `cart_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_email` (`user_email`),
  ADD KEY `idx_property_id` (`property_id`);

--
-- Indexes for table `data`
--
ALTER TABLE `data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_email` (`user_email`),
  ADD KEY `idx_property_id` (`property_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_location` (`location`),
  ADD KEY `idx_property_type` (`property_type`),
  ADD KEY `idx_price` (`price`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `property_likes`
--
ALTER TABLE `property_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`property_id`,`user_email`),
  ADD KEY `idx_property_id` (`property_id`),
  ADD KEY `idx_user_email` (`user_email`);

--
-- Indexes for table `property_reports`
--
ALTER TABLE `property_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_action_identifier` (`action`,`identifier`),
  ADD KEY `idx_timestamp` (`timestamp`);

--
-- Indexes for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_hash` (`token_hash`);

--
-- Indexes for table `security_events`
--
ALTER TABLE `security_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_event_type` (`event_type`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `security_logs`
--
ALTER TABLE `security_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_event` (`user_id`,`event_type`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_preference` (`user_id`,`preference_key`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `your_table_name`
--
ALTER TABLE `your_table_name`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `cart_history`
--
ALTER TABLE `cart_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `data`
--
ALTER TABLE `data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `property_likes`
--
ALTER TABLE `property_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `property_reports`
--
ALTER TABLE `property_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `security_events`
--
ALTER TABLE `security_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `security_logs`
--
ALTER TABLE `security_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `user_preferences`
--
ALTER TABLE `user_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `your_table_name`
--
ALTER TABLE `your_table_name`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `property_likes`
--
ALTER TABLE `property_likes`
  ADD CONSTRAINT `property_likes_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `data` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `property_reports`
--
ALTER TABLE `property_reports`
  ADD CONSTRAINT `property_reports_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `data` (`id`);

--
-- Constraints for table `security_events`
--
ALTER TABLE `security_events`
  ADD CONSTRAINT `security_events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `security_logs`
--
ALTER TABLE `security_logs`
  ADD CONSTRAINT `security_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD CONSTRAINT `user_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
