-- ============================================
-- Fix: Add order_status column to orders table
-- ============================================
-- Run this in phpMyAdmin

USE ecommerce_db;

-- Add order_status column
ALTER TABLE orders 
ADD COLUMN order_status VARCHAR(50) DEFAULT 'pending' AFTER total_amount;

