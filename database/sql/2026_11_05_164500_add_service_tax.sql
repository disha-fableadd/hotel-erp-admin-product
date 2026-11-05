-- Service tax on whole bill (settings + order snapshot)
-- Safe for MySQL 5.7 / MariaDB without IF NOT EXISTS on columns.
-- Skip any statement that errors with "Duplicate column name".

ALTER TABLE `settings` ADD COLUMN `service_tax_enabled` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `settings` ADD COLUMN `service_tax_rate` DECIMAL(8,2) NULL;
ALTER TABLE `settings` ADD COLUMN `service_tax_name` VARCHAR(100) NULL;

ALTER TABLE `orders` ADD COLUMN `service_tax_rate` DECIMAL(8,2) NULL;
ALTER TABLE `orders` ADD COLUMN `service_tax_amount` DECIMAL(12,2) NULL;
ALTER TABLE `orders` ADD COLUMN `service_tax_name` VARCHAR(100) NULL;
