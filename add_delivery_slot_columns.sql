-- Add delivery slot columns to orders table
-- Run this SQL in phpMyAdmin or MySQL command line

ALTER TABLE orders 
ADD COLUMN delivery_date VARCHAR(50) DEFAULT 'Today' AFTER payment_status,
ADD COLUMN delivery_slot VARCHAR(100) DEFAULT 'Morning (6 AM - 9 AM)' AFTER delivery_date;

-- Verify columns were added
SHOW COLUMNS FROM orders LIKE 'delivery%';
