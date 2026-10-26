-- إزالة نسبة الزيادة من إعدادات الموقع
-- نفّذ مرة واحدة على السيرفر إذا لم تستخدم php artisan migrate

ALTER TABLE `settings` DROP COLUMN IF EXISTS `price_markup_percent`;
