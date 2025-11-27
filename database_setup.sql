-- Tabel untuk Chat Messages
CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_username VARCHAR(100) NOT NULL,
    receiver_username VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read TINYINT(1) DEFAULT 0,
    INDEX idx_sender (sender_username),
    INDEX idx_receiver (receiver_username),
    INDEX idx_created (created_at)
);

-- Tabel untuk Notifications
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_username VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) DEFAULT 'info',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    link VARCHAR(255) DEFAULT NULL,
    INDEX idx_user (user_username),
    INDEX idx_read (is_read),
    INDEX idx_created (created_at)
);

-- Tabel untuk Orders
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_username VARCHAR(100) NOT NULL,
    seller_username VARCHAR(100) DEFAULT 'admin',
    total_amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_buyer (buyer_username),
    INDEX idx_seller (seller_username),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
);

-- Tabel untuk Order Items
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order (order_id),
    INDEX idx_product (product_id)
);

