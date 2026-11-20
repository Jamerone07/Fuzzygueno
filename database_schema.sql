-- ============================================
-- E-commerce Database Schema
-- Fuzzygueño E-commerce System
-- ============================================

-- Create database
CREATE DATABASE IF NOT EXISTS ecommerce_db;
USE ecommerce_db;

-- Users table for authentication
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cart table
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    order_status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample products
INSERT INTO products (name, description, price, image) VALUES
('Single Stem Fuzzywire Flower', 'A beautiful single fuzzywire flower perfect for small gifts or decorations.', 99.00, 'images/99.jpg'),
('Standard Bouquet', 'A lovely arrangement of fuzzywire flowers that will brighten any day.', 299.00, 'images/299.jpg'),
('Big Bouquet', 'An extravagant bouquet perfect for special occasions and celebrations.', 349.00, 'images/349.jpg'),
('Custom Keychain Design 1', 'Personalized keychain with your favorite design and colors.', 35.00, 'images/35.jpg'),
('Custom Keychain Design 2', 'Personalized keychain with your favorite design and colors.', 35.00, 'images/35(2).jpg'),
('Custom Keychain Design 3', 'Personalized keychain with your favorite design and colors.', 35.00, 'images/35(3).jpg'),
('Custom Keychain Design 4', 'Personalized keychain with your favorite design and colors.', 35.00, 'images/35(4).jpg'),
('Custom Keychain Design 5', 'Personalized keychain with your favorite design and colors.', 35.00, 'images/35(5).jpg'),
('Custom Keychain Design 6', 'Personalized keychain with your favorite design and colors.', 35.00, 'images/35(6).jpg');

