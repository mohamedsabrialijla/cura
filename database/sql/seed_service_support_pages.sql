-- Seed footer service pages (payment / shipping / delivery / returns)
-- Requires pages.page_key column (see add_page_key migration / create SQL)

INSERT INTO `pages` (`page_key`, `image`, `created_at`, `updated_at`, `deleted_at`)
SELECT 'payment', NULL, NOW(), NOW(), NULL
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `pages` WHERE `page_key` = 'payment');

INSERT INTO `pages` (`page_key`, `image`, `created_at`, `updated_at`, `deleted_at`)
SELECT 'shipping', NULL, NOW(), NOW(), NULL
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `pages` WHERE `page_key` = 'shipping');

INSERT INTO `pages` (`page_key`, `image`, `created_at`, `updated_at`, `deleted_at`)
SELECT 'delivery', NULL, NOW(), NOW(), NULL
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `pages` WHERE `page_key` = 'delivery');

INSERT INTO `pages` (`page_key`, `image`, `created_at`, `updated_at`, `deleted_at`)
SELECT 'returns', NULL, NOW(), NOW(), NULL
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `pages` WHERE `page_key` = 'returns');

-- payment EN
INSERT INTO `page_translations` (`page_id`, `locale`, `name`, `slug`, `description`, `key_words`)
SELECT p.id, 'en', 'Payment', 'payment',
  '<p>We accept major credit/debit cards and supported digital wallets at checkout. Once payment succeeds, your order items are reserved and you receive an order confirmation by email.</p><p>Edit this page from the dashboard to add your full payment policy.</p>',
  'payment, billing'
FROM `pages` p
WHERE p.page_key = 'payment'
  AND NOT EXISTS (SELECT 1 FROM `page_translations` t WHERE t.page_id = p.id AND t.locale = 'en');

INSERT INTO `page_translations` (`page_id`, `locale`, `name`, `slug`, `description`, `key_words`)
SELECT p.id, 'ar', 'الدفع', 'الدفع',
  '<p>نقبل بطاقات الائتمان/الخصم والمحافظ الرقمية المدعومة عند إتمام الطلب. بعد نجاح الدفع يتم حجز المنتجات ويصلك تأكيد الطلب على البريد.</p><p>عدّل هذه الصفحة من لوحة التحكم لإضافة سياسة الدفع الكاملة.</p>',
  'دفع, فوترة'
FROM `pages` p
WHERE p.page_key = 'payment'
  AND NOT EXISTS (SELECT 1 FROM `page_translations` t WHERE t.page_id = p.id AND t.locale = 'ar');

-- shipping EN/AR
INSERT INTO `page_translations` (`page_id`, `locale`, `name`, `slug`, `description`, `key_words`)
SELECT p.id, 'en', 'Shipping', 'shipping',
  '<p>Shipping costs are calculated at checkout after you enter your address. Carriers may vary by destination and product availability.</p><p>Edit this page from the dashboard to add your full shipping policy.</p>',
  'shipping, delivery'
FROM `pages` p
WHERE p.page_key = 'shipping'
  AND NOT EXISTS (SELECT 1 FROM `page_translations` t WHERE t.page_id = p.id AND t.locale = 'en');

INSERT INTO `page_translations` (`page_id`, `locale`, `name`, `slug`, `description`, `key_words`)
SELECT p.id, 'ar', 'الشحن', 'الشحن',
  '<p>تُحسب تكلفة الشحن تلقائيًا في صفحة الدفع بعد إدخال العنوان. قد تختلف شركة الشحن حسب الوجهة وتوفر المنتجات.</p><p>عدّل هذه الصفحة من لوحة التحكم لإضافة سياسة الشحن الكاملة.</p>',
  'شحن, توصيل'
FROM `pages` p
WHERE p.page_key = 'shipping'
  AND NOT EXISTS (SELECT 1 FROM `page_translations` t WHERE t.page_id = p.id AND t.locale = 'ar');

-- delivery EN/AR
INSERT INTO `page_translations` (`page_id`, `locale`, `name`, `slug`, `description`, `key_words`)
SELECT p.id, 'en', 'Delivery times', 'delivery-times',
  '<p>In-stock items are usually prepared within 1–2 working days. Delivery often takes around 3–10 business days depending on destination.</p><p>Edit this page from the dashboard to publish exact delivery timelines.</p>',
  'delivery, times'
FROM `pages` p
WHERE p.page_key = 'delivery'
  AND NOT EXISTS (SELECT 1 FROM `page_translations` t WHERE t.page_id = p.id AND t.locale = 'en');

INSERT INTO `page_translations` (`page_id`, `locale`, `name`, `slug`, `description`, `key_words`)
SELECT p.id, 'ar', 'مدة التوصيل', 'مدة-التوصيل',
  '<p>المنتجات المتوفرة تُجهَّز عادة خلال 1–2 يوم عمل، والتوصيل غالبًا خلال 3–10 أيام عمل حسب الوجهة.</p><p>عدّل هذه الصفحة من لوحة التحكم لنشر مدد التوصيل الدقيقة.</p>',
  'مدة, توصيل'
FROM `pages` p
WHERE p.page_key = 'delivery'
  AND NOT EXISTS (SELECT 1 FROM `page_translations` t WHERE t.page_id = p.id AND t.locale = 'ar');

-- returns EN/AR
INSERT INTO `page_translations` (`page_id`, `locale`, `name`, `slug`, `description`, `key_words`)
SELECT p.id, 'en', 'Returns | Withdrawal', 'returns',
  '<p>You can return eligible items according to our return policy after delivery. Contact support if you need help starting a return.</p><p>Edit this page from the dashboard to publish the full returns policy.</p>',
  'returns, withdrawal'
FROM `pages` p
WHERE p.page_key = 'returns'
  AND NOT EXISTS (SELECT 1 FROM `page_translations` t WHERE t.page_id = p.id AND t.locale = 'en');

INSERT INTO `page_translations` (`page_id`, `locale`, `name`, `slug`, `description`, `key_words`)
SELECT p.id, 'ar', 'الإرجاع', 'الإرجاع',
  '<p>يمكنك إرجاع المنتجات المؤهلة وفق سياسة الإرجاع بعد الاستلام. تواصل مع الدعم إذا احتجت مساعدة لبدء طلب الإرجاع.</p><p>عدّل هذه الصفحة من لوحة التحكم لنشر سياسة الإرجاع الكاملة.</p>',
  'إرجاع, استبدال'
FROM `pages` p
WHERE p.page_key = 'returns'
  AND NOT EXISTS (SELECT 1 FROM `page_translations` t WHERE t.page_id = p.id AND t.locale = 'ar');

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_08_02_000001_seed_service_support_pages', IFNULL((SELECT MAX(batch) FROM `migrations`), 0) + 1
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM `migrations` WHERE `migration` = '2026_08_02_000001_seed_service_support_pages'
);
