-- Keepersport DB updates (2026-07-26 → 2026-07-28)
-- Run once on MySQL/MariaDB

-- 1) User addresses (checkout)
CREATE TABLE IF NOT EXISTS `user_addresses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mobile` varchar(32) NOT NULL,
  `country_code` varchar(8) NOT NULL DEFAULT 'SA',
  `country_name` varchar(120) DEFAULT NULL,
  `city` varchar(120) NOT NULL,
  `district` varchar(120) DEFAULT NULL,
  `street` varchar(255) NOT NULL,
  `building` varchar(120) DEFAULT NULL,
  `apartment` varchar(120) DEFAULT NULL,
  `postal_code` varchar(32) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_addresses_user_id_is_default_index` (`user_id`, `is_default`),
  KEY `user_addresses_country_code_index` (`country_code`),
  CONSTRAINT `user_addresses_user_id_foreign`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) Order fulfillment status on payments
SET @col_exists := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'payments'
    AND COLUMN_NAME = 'order_status'
);

SET @sql := IF(
  @col_exists = 0,
  'ALTER TABLE `payments` ADD COLUMN `order_status` varchar(32) NOT NULL DEFAULT ''new'' AFTER `status`',
  'SELECT ''order_status already exists'' AS info'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3) Payment methods
CREATE TABLE IF NOT EXISTS `payment_methods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(64) NOT NULL,
  `title_ar` varchar(255) NOT NULL,
  `title_en` varchar(255) NOT NULL,
  `description_ar` varchar(500) DEFAULT NULL,
  `description_en` varchar(500) DEFAULT NULL,
  `sort_order` smallint unsigned NOT NULL DEFAULT 0,
  `status` varchar(32) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_methods_code_unique` (`code`),
  KEY `payment_methods_status_sort_order_index` (`status`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `payment_methods`
  (`code`, `title_ar`, `title_en`, `description_ar`, `description_en`, `sort_order`, `status`, `created_at`, `updated_at`)
SELECT
  'neoleap',
  'NeoLeap',
  'NeoLeap',
  'بطاقة مدى / فيزا / ماستركارد',
  'Mada / Visa / Mastercard',
  1,
  'active',
  NOW(),
  NOW()
WHERE NOT EXISTS (
  SELECT 1 FROM `payment_methods` WHERE `code` = 'neoleap'
);

-- Optional: mark migrations as run (same batch)
SET @batch := (SELECT IFNULL(MAX(batch), 0) + 1 FROM `migrations`);

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_07_26_000001_create_user_addresses_table', @batch
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM `migrations` WHERE `migration` = '2026_07_26_000001_create_user_addresses_table'
);

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_07_28_000001_add_order_status_to_payments_table', @batch
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM `migrations` WHERE `migration` = '2026_07_28_000001_add_order_status_to_payments_table'
);

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_07_28_000002_create_payment_methods_table', @batch
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM `migrations` WHERE `migration` = '2026_07_28_000002_create_payment_methods_table'
);