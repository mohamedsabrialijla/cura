-- Seed Goalkeeper Day CMS page (editable from dashboard Pages)
INSERT INTO `pages` (`page_key`, `image`, `created_at`, `updated_at`, `deleted_at`)
SELECT 'goalkeeper-day', NULL, NOW(), NOW(), NULL
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `pages` WHERE `page_key` = 'goalkeeper-day');

INSERT INTO `page_translations` (`page_id`, `locale`, `name`, `slug`, `description`, `key_words`)
SELECT p.id, 'en', 'Goalkeeper Day', 'goalkeeper-day',
  '<p>Goalkeeper Day celebrates every keeper — amateurs and pros. Edit this page from the dashboard to publish the full event content, schedule, and media.</p>',
  'goalkeeper day, keepersport'
FROM `pages` p
WHERE p.page_key = 'goalkeeper-day'
  AND NOT EXISTS (SELECT 1 FROM `page_translations` t WHERE t.page_id = p.id AND t.locale = 'en');

INSERT INTO `page_translations` (`page_id`, `locale`, `name`, `slug`, `description`, `key_words`)
SELECT p.id, 'ar', 'يوم حارس المرمى', 'يوم-حارس-المرمى',
  '<p>يوم حارس المرمى يحتفل بكل حارس — هواة ومحترفين. عدّل هذه الصفحة من لوحة التحكم لنشر محتوى الفعالية والجدول والوسائط.</p>',
  'يوم حارس المرمى'
FROM `pages` p
WHERE p.page_key = 'goalkeeper-day'
  AND NOT EXISTS (SELECT 1 FROM `page_translations` t WHERE t.page_id = p.id AND t.locale = 'ar');

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_08_04_000001_seed_goalkeeper_day_page', IFNULL((SELECT MAX(batch) FROM `migrations`), 0) + 1
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM `migrations` WHERE `migration` = '2026_08_04_000001_seed_goalkeeper_day_page'
);
