-- ============================================
-- Fix: Add total_amount column to orders table
-- ============================================
-- Run this in phpMyAdmin or MySQL command line

USE ecommerce_db;

-- Add total_amount column if it doesn't exist
-- Note: MySQL doesn't support "IF NOT EXISTS" for ALTER TABLE ADD COLUMN
-- So we'll use a procedure or just run it directly
-- If you get "Duplicate column name" error, the column already exists - that's fine!

ALTER TABLE orders 
ADD COLUMN total_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00;

-- Update existing orders to have total_amount calculated from order_items
UPDATE orders o
SET o.total_amount = (
    SELECT COALESCE(SUM(oi.price * oi.quantity), 0)
    FROM order_items oi
    WHERE oi.order_id = o.id
)
WHERE o.total_amount = 0 OR o.total_amount IS NULL;

