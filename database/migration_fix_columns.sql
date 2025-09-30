-- Migration script to fix missing columns in LogisticaLeslie system
-- This fixes the errors:
-- 1. Missing delivery_status in route_stops/route_orders
-- 2. Missing final_amount and discount_amount in direct_sales
-- Run this on existing database: mysql -u [username] -p [database_name] < migration_fix_columns.sql

USE fix360_logisticaleslie;

-- Fix route_stops table - add missing columns
-- Add stop_sequence column
SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'route_stops'
      AND column_name = 'stop_sequence'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE route_stops ADD COLUMN stop_sequence INT DEFAULT 1 AFTER stop_order;',
    'SELECT "stop_sequence ya existe";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add delivery_status column
SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'route_stops'
      AND column_name = 'delivery_status'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE route_stops ADD COLUMN delivery_status ENUM(''pending'', ''delivered'', ''failed'', ''partial'') DEFAULT ''pending'' AFTER status;',
    'SELECT "delivery_status ya existe";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add delivery_notes column
SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'route_stops'
      AND column_name = 'delivery_notes'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE route_stops ADD COLUMN delivery_notes TEXT AFTER notes;',
    'SELECT "delivery_notes ya existe";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add delivered_by column
SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'route_stops'
      AND column_name = 'delivered_by'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE route_stops ADD COLUMN delivered_by INT AFTER delivery_notes;',
    'SELECT "delivered_by ya existe";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key for delivered_by if it doesn't exist
SET @fk_exists = (
    SELECT COUNT(*) FROM information_schema.key_column_usage
    WHERE table_schema = DATABASE()
      AND table_name = 'route_stops'
      AND constraint_name = 'route_stops_ibfk_delivered_by'
);
SET @sql = IF(@fk_exists = 0 AND @col_exists > 0,
    'ALTER TABLE route_stops ADD CONSTRAINT route_stops_ibfk_delivered_by FOREIGN KEY (delivered_by) REFERENCES users(id);',
    'SELECT "FK delivered_by ya existe o columna no creada";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Fix direct_sales table - add missing columns
-- Add discount_amount column
SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'direct_sales'
      AND column_name = 'discount_amount'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE direct_sales ADD COLUMN discount_amount DECIMAL(10,2) DEFAULT 0.00 AFTER total_amount;',
    'SELECT "discount_amount ya existe";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add final_amount column
SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'direct_sales'
      AND column_name = 'final_amount'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE direct_sales ADD COLUMN final_amount DECIMAL(10,2) DEFAULT 0.00 AFTER discount_amount;',
    'SELECT "final_amount ya existe";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update final_amount for existing records where it's 0
UPDATE direct_sales 
SET final_amount = total_amount - discount_amount 
WHERE final_amount = 0.00 OR final_amount IS NULL;

-- Create or replace route_orders view
DROP VIEW IF EXISTS route_orders;
CREATE VIEW route_orders AS
SELECT 
    id,
    route_id,
    order_id,
    stop_order as stop_sequence,
    estimated_arrival,
    actual_arrival,
    status,
    delivery_status,
    notes,
    delivery_notes,
    delivered_by,
    stop_sequence as sequence_order
FROM route_stops;

SELECT 'Migration completed successfully!' as status;
