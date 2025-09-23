
CREATE DATABASE IF NOT EXISTS user_form_db_rakhmanko CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE user_form_db_rakhmanko;

CREATE TABLE IF NOT EXISTS print_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_name VARCHAR(255) NOT NULL,
    print_format VARCHAR(50) NOT NULL,
    copies INT NOT NULL,
    pickup_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pickup_date (pickup_date),
    INDEX idx_created_at (created_at)
);

DESCRIBE print_orders;

