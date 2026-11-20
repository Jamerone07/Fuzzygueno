-- Fix orders table - Add total_amount column if it doesn't exist
USE ecommerce_db;

-- Check and add total_amount column if missing
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS total_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER user_id;

-- If the column already exists but is in wrong position, this won't cause an error
-- If you get an error saying column exists, that's fine - it means it's already there

