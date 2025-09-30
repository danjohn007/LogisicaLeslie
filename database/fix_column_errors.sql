-- Fix for column errors in LogisticaLeslie system
-- Run this script to fix missing columns

USE fix360_logisticaleslie;

-- Fix 1: Add delivery_status column to route_orders (rename existing status column)
ALTER TABLE route_orders CHANGE COLUMN status delivery_status ENUM('pending', 'delivered', 'failed') DEFAULT 'pending';

-- Fix 2: Add missing columns to route_orders for full compatibility
ALTER TABLE route_orders 
ADD COLUMN IF NOT EXISTS stop_sequence INT DEFAULT 1,
ADD COLUMN IF NOT EXISTS estimated_arrival TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS actual_arrival TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS delivery_notes TEXT NULL,
ADD COLUMN IF NOT EXISTS delivered_by INT NULL;

-- Fix 3: Add final_amount column to direct_sales
ALTER TABLE direct_sales 
ADD COLUMN IF NOT EXISTS final_amount DECIMAL(10,2) DEFAULT 0,
ADD COLUMN IF NOT EXISTS discount_amount DECIMAL(10,2) DEFAULT 0;

-- Fix 4: Update final_amount with calculated values for existing records
UPDATE direct_sales SET final_amount = total_amount WHERE final_amount = 0;

-- Fix 5: Ensure route_orders references the correct routes table
-- Check if foreign key needs to be updated from delivery_routes to routes
SET FOREIGN_KEY_CHECKS = 0;

-- Drop the old foreign key constraint if it exists
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS 
     WHERE CONSTRAINT_SCHEMA = DATABASE() 
       AND TABLE_NAME = 'route_orders' 
       AND CONSTRAINT_NAME = 'route_orders_ibfk_1') > 0,
    'ALTER TABLE route_orders DROP FOREIGN KEY route_orders_ibfk_1;',
    'SELECT "Foreign key constraint does not exist";'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add the correct foreign key constraint to routes table
ALTER TABLE route_orders 
ADD CONSTRAINT fk_route_orders_route 
FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;