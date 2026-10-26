-- Social links for footer (run once on server; skip columns that already exist)
ALTER TABLE `settings` ADD COLUMN `instagram` VARCHAR(500) NULL AFTER `whatsapp`;
ALTER TABLE `settings` ADD COLUMN `facebook` VARCHAR(500) NULL AFTER `instagram`;
ALTER TABLE `settings` ADD COLUMN `tiktok` VARCHAR(500) NULL AFTER `facebook`;
ALTER TABLE `settings` ADD COLUMN `snapchat` VARCHAR(500) NULL AFTER `tiktok`;
