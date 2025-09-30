-- SQL fixes for LogisticaLeslie system errors
-- Fix missing columns and add route_orders table compatibility

USE fix360_logisticaleslie;

-- 1. Add final_amount column to direct_sales table
ALTER TABLE direct_sales 
ADD COLUMN IF NOT EXISTS final_amount DECIMAL(10,2) DEFAULT 0 
AFTER total_amount;

-- Update existing records to set final_amount = total_amount where null
UPDATE direct_sales 
SET final_amount = total_amount 
WHERE final_amount IS NULL OR final_amount = 0;

-- 2. Add delivery_status column to route_stops table (which is used as route_orders)
ALTER TABLE route_stops 
ADD COLUMN IF NOT EXISTS delivery_status ENUM('pending', 'delivered', 'failed', 'partial') DEFAULT 'pending'
AFTER status;

-- Update existing records to map status to delivery_status
UPDATE route_stops 
SET delivery_status = CASE 
    WHEN status = 'delivered' THEN 'delivered'
    WHEN status = 'failed' THEN 'failed'
    WHEN status = 'pending' THEN 'pending'
    WHEN status = 'arrived' THEN 'pending'
    ELSE 'pending'
END
WHERE delivery_status = 'pending';

-- 3. Add additional delivery tracking columns to route_stops
ALTER TABLE route_stops 
ADD COLUMN IF NOT EXISTS stop_sequence INT DEFAULT 1 AFTER stop_order,
ADD COLUMN IF NOT EXISTS estimated_arrival_time TIME NULL AFTER estimated_arrival,
ADD COLUMN IF NOT EXISTS delivery_notes TEXT NULL AFTER notes,
ADD COLUMN IF NOT EXISTS delivered_by INT NULL AFTER delivery_notes;

-- Add foreign key for delivered_by
ALTER TABLE route_stops 
ADD CONSTRAINT fk_route_stops_delivered_by 
FOREIGN KEY (delivered_by) REFERENCES users(id);

-- 4. Create a view to provide route_orders compatibility
CREATE OR REPLACE VIEW route_orders AS
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

-- 5. Add discount_amount column to direct_sales if not exists
ALTER TABLE direct_sales 
ADD COLUMN IF NOT EXISTS discount_amount DECIMAL(10,2) DEFAULT 0 
AFTER total_amount;

-- Update final_amount calculation to account for discounts
UPDATE direct_sales 
SET final_amount = total_amount - COALESCE(discount_amount, 0);