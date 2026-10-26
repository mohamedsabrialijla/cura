-- FAQ items table
CREATE TABLE IF NOT EXISTS `faq_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(120) NOT NULL,
  `question_ar` varchar(500) NOT NULL,
  `question_en` varchar(500) NOT NULL,
  `answer_ar` text NOT NULL,
  `answer_en` text NOT NULL,
  `sort_order` smallint unsigned NOT NULL DEFAULT 0,
  `status` varchar(32) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `faq_items_category_status_sort_order_index` (`category`, `status`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @batch := (SELECT IFNULL(MAX(batch), 0) + 1 FROM `migrations`);

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_07_31_000001_create_faq_items_table', @batch
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM `migrations` WHERE `migration` = '2026_07_31_000001_create_faq_items_table'
);
