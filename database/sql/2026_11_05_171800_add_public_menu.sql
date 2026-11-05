-- Public digital menu link + QR (settings)
-- Skip any statement that errors with "Duplicate column name".

ALTER TABLE `settings` ADD COLUMN `menu_public_enabled` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `settings` ADD COLUMN `menu_public_token` VARCHAR(64) NULL;

CREATE INDEX `settings_menu_public_token_index` ON `settings` (`menu_public_token`);
