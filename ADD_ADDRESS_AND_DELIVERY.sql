-- ============================================
-- Add address field to users table
-- Add delivery_method and delivery_address to orders table
-- ============================================

USE ecommerce_db;

-- Add address to users table
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS address TEXT AFTER email;

-- Add delivery fields to orders table
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS delivery_method VARCHAR(20) DEFAULT 'pickup' AFTER total_amount,
ADD COLUMN IF NOT EXISTS delivery_address TEXT AFTER delivery_method;

