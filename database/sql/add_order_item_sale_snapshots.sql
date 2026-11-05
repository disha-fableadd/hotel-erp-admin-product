-- Freeze product name + GST on each sale line so later product edits never rewrite old bills.
-- Run on your live DB (phpMyAdmin or mysql client).

SET @db = DATABASE();

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='order_items' AND COLUMN_NAME='product_name')=0,
  'ALTER TABLE order_items ADD COLUMN product_name VARCHAR(255) NULL AFTER product_id',
  'SELECT 1'));
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='order_items' AND COLUMN_NAME='tax_id')=0,
  'ALTER TABLE order_items ADD COLUMN tax_id BIGINT UNSIGNED NULL AFTER total_amount',
  'SELECT 1'));
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='order_items' AND COLUMN_NAME='tax_name')=0,
  'ALTER TABLE order_items ADD COLUMN tax_name VARCHAR(100) NULL AFTER tax_id',
  'SELECT 1'));
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='order_items' AND COLUMN_NAME='tax_rate')=0,
  'ALTER TABLE order_items ADD COLUMN tax_rate DECIMAL(8,2) NULL AFTER tax_name',
  'SELECT 1'));
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='order_items' AND COLUMN_NAME='tax_inclusive')=0,
  'ALTER TABLE order_items ADD COLUMN tax_inclusive TINYINT(1) NULL AFTER tax_rate',
  'SELECT 1'));
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='orders' AND COLUMN_NAME='tax_breakdown')=0,
  'ALTER TABLE orders ADD COLUMN tax_breakdown JSON NULL AFTER tax_id',
  'SELECT 1'));
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
