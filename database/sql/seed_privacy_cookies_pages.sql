-- Ensure privacy + cookies CMS pages exist
INSERT INTO `pages` (`page_key`, `image`, `created_at`, `updated_at`, `deleted_at`)
SELECT 'privacy', NULL, NOW(), NOW(), NULL
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `pages` WHERE `page_key` = 'privacy');

INSERT INTO `pages` (`page_key`, `image`, `created_at`, `updated_at`, `deleted_at`)
SELECT 'cookies', NULL, NOW(), NOW(), NULL
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `pages` WHERE `page_key` = 'cookies');

INSERT INTO `page_translations` (`page_id`, `locale`, `name`, `slug`, `description`, `key_words`)
SELECT p.id, 'en', 'Privacy Policy', 'privacy-policy',
  '<p>Add your privacy policy content from the dashboard.</p>', 'privacy'
FROM `pages` p
WHERE p.page_key = 'privacy'
  AND NOT EXISTS (SELECT 1 FROM `page_translations` t WHERE t.page_id = p.id AND t.locale = 'en');

INSERT INTO `page_translations` (`page_id`, `locale`, `name`, `slug`, `description`, `key_words`)
SELECT p.id, 'ar', 'سياسة الخصوصية', 'سياسة-الخصوصية',
  '<p>أضف نص سياسة الخصوصية من لوحة التحكم.</p>', 'خصوصية'
FROM `pages` p
WHERE p.page_key = 'privacy'
  AND NOT EXISTS (SELECT 1 FROM `page_translations` t WHERE t.page_id = p.id AND t.locale = 'ar');

INSERT INTO `page_translations` (`page_id`, `locale`, `name`, `slug`, `description`, `key_words`)
SELECT p.id, 'en', 'Cookies', 'cookies',
  '<p>This website uses cookies to improve your experience. Edit this page from the dashboard to publish the full cookie policy.</p>',
  'cookies'
FROM `pages` p
WHERE p.page_key = 'cookies'
  AND NOT EXISTS (SELECT 1 FROM `page_translations` t WHERE t.page_id = p.id AND t.locale = 'en');

INSERT INTO `page_translations` (`page_id`, `locale`, `name`, `slug`, `description`, `key_words`)
SELECT p.id, 'ar', 'الكوكيز', 'الكوكيز',
  '<p>يستخدم هذا الموقع ملفات تعريف الارتباط لتحسين تجربتك. عدّل هذه الصفحة من لوحة التحكم لنشر سياسة الكوكيز الكاملة.</p>',
  'كوكيز'
FROM `pages` p
WHERE p.page_key = 'cookies'
  AND NOT EXISTS (SELECT 1 FROM `page_translations` t WHERE t.page_id = p.id AND t.locale = 'ar');

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_08_05_000001_seed_privacy_cookies_pages', IFNULL((SELECT MAX(batch) FROM `migrations`), 0) + 1
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM `migrations` WHERE `migration` = '2026_08_05_000001_seed_privacy_cookies_pages'
);
