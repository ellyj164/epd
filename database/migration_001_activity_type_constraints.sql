-- Migration: Add NOT NULL and DEFAULT to activity_type column in user_activities table
-- Date: 2024-09-08  
-- Description: Ensure activity_type column cannot be NULL and defaults to 'view_product'
--             for MySQL/MariaDB databases only

-- Update existing NULL values to default
UPDATE user_activities 
SET activity_type = 'view_product' 
WHERE activity_type IS NULL OR activity_type = '';

-- Modify column to add NOT NULL constraint and DEFAULT value
ALTER TABLE user_activities 
MODIFY COLUMN activity_type ENUM('view_product', 'add_to_cart', 'purchase', 'search', 'review') 
NOT NULL DEFAULT 'view_product';