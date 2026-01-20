-- SQL Migration: Allow product deletion by modifying foreign key constraint
-- Run this in phpMyAdmin or MySQL command line

-- Step 1: Find and drop the existing foreign key constraint
ALTER TABLE order_items DROP FOREIGN KEY order_items_ibfk_2;

-- Step 2: Add new foreign key with ON DELETE SET NULL
-- This allows deleting products while keeping order history (product_id becomes NULL)
ALTER TABLE order_items 
MODIFY COLUMN product_id INT NULL,
ADD CONSTRAINT order_items_ibfk_2 
FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL;

-- Alternative: If you want to delete order_items when product is deleted (loses history):
-- ALTER TABLE order_items 
-- ADD CONSTRAINT order_items_ibfk_2 
-- FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE;
