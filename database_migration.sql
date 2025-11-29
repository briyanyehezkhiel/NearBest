-- Migration script untuk update struktur tabel orders dan order_items
-- Jalankan script ini di phpMyAdmin untuk menambahkan kolom yang diperlukan

-- 1. Update tabel orders - tambahkan kolom buyer_username dan lainnya
ALTER TABLE `orders` 
ADD COLUMN `buyer_username` VARCHAR(100) NULL AFTER `buyer_id`,
ADD COLUMN `seller_username` VARCHAR(100) DEFAULT 'admin' AFTER `buyer_username`,
ADD COLUMN `seller_store_name` VARCHAR(255) NULL AFTER `seller_username`,
ADD COLUMN `seller_store_address` TEXT NULL AFTER `seller_store_name`,
ADD COLUMN `total_amount` DECIMAL(10,2) NULL AFTER `total`,
ADD COLUMN `notes` TEXT NULL AFTER `status`,
ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- 2. Update data buyer_username dari buyer_id (jika ada data)
UPDATE `orders` o
INNER JOIN `users` u ON o.buyer_id = u.id
SET o.buyer_username = u.username
WHERE o.buyer_username IS NULL;

-- 3. Update total_amount dari total
UPDATE `orders` 
SET `total_amount` = `total`
WHERE `total_amount` IS NULL AND `total` IS NOT NULL;

-- 4. Update tabel order_items - tambahkan kolom yang diperlukan
ALTER TABLE `order_items`
ADD COLUMN `product_name` VARCHAR(255) NULL AFTER `product_id`,
ADD COLUMN `quantity` INT NULL AFTER `qty`,
ADD COLUMN `subtotal` DECIMAL(10,2) NULL AFTER `price`;

-- 5. Update quantity dari qty
UPDATE `order_items`
SET `quantity` = `qty`
WHERE `quantity` IS NULL AND `qty` IS NOT NULL;

-- 6. Update subtotal dari price * qty
UPDATE `order_items`
SET `subtotal` = `price` * `qty`
WHERE `subtotal` IS NULL AND `price` IS NOT NULL AND `qty` IS NOT NULL;

-- 7. Update product_name dari products
UPDATE `order_items` oi
INNER JOIN `products` p ON oi.product_id = p.id
SET oi.product_name = p.name
WHERE oi.product_name IS NULL;

-- 8. Tambahkan index untuk performa
ALTER TABLE `orders`
ADD INDEX `idx_buyer` (`buyer_username`),
ADD INDEX `idx_seller` (`seller_username`),
ADD INDEX `idx_status` (`status`);

ALTER TABLE `order_items`
ADD INDEX `idx_order` (`order_id`),
ADD INDEX `idx_product` (`product_id`);
