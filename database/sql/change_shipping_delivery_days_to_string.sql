-- Allow delivery_days text like "5-7 أيام عمل"
ALTER TABLE `shipping_methods`
  MODIFY `delivery_days` VARCHAR(120) NOT NULL DEFAULT '1';

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_08_06_000001_change_shipping_delivery_days_to_string', IFNULL((SELECT MAX(batch) FROM `migrations`), 0) + 1
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM `migrations` WHERE `migration` = '2026_08_06_000001_change_shipping_delivery_days_to_string'
);
