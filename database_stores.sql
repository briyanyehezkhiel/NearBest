-- Tabel untuk Store/Toko Seller
CREATE TABLE IF NOT EXISTS `stores` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `seller_username` VARCHAR(100) NOT NULL,
    `store_name` VARCHAR(255) NOT NULL,
    `store_address` TEXT NOT NULL,
    `store_phone` VARCHAR(50),
    `store_email` VARCHAR(255),
    `store_description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_seller` (`seller_username`),
    INDEX `idx_seller` (`seller_username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Tambahkan kolom seller_username ke tabel orders jika belum ada
ALTER TABLE `orders` 
ADD COLUMN IF NOT EXISTS `seller_store_name` VARCHAR(255) NULL AFTER `seller_username`,
ADD COLUMN IF NOT EXISTS `seller_store_address` TEXT NULL AFTER `seller_store_name`;

