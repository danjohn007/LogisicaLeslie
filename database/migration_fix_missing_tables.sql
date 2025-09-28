-- Migration script to add missing tables for LogisticaLeslie system
-- Run this on existing database to fix missing table errors

USE fix360_logisticaleslie;

-- Create delivery_routes table if it doesn't exist
CREATE TABLE IF NOT EXISTS delivery_routes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    route_name VARCHAR(100) NOT NULL,
    driver_id INT NOT NULL,
    route_date DATE NOT NULL,
    start_time TIME,
    end_time TIME,
    status ENUM('planned', 'in_progress', 'completed', 'cancelled') DEFAULT 'planned',
    notes TEXT,
    total_orders INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES users(id)
);

-- Create route_orders table if it doesn't exist
CREATE TABLE IF NOT EXISTS route_orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    route_id INT NOT NULL,
    order_id INT NOT NULL,
    sequence_order INT NOT NULL,
    status ENUM('pending', 'delivered', 'failed') DEFAULT 'pending',
    delivered_at TIMESTAMP NULL,
    notes TEXT,
    FOREIGN KEY (route_id) REFERENCES delivery_routes(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

-- Add missing indexes if they don't exist
CREATE INDEX IF NOT EXISTS idx_delivery_routes_date ON delivery_routes(route_date);
CREATE INDEX IF NOT EXISTS idx_route_orders_route ON route_orders(route_id);
CREATE INDEX IF NOT EXISTS idx_route_orders_order ON route_orders(order_id);

-- Fix production_lots table - add missing column if needed
ALTER TABLE production_lots ADD COLUMN IF NOT EXISTS production_type VARCHAR(50) DEFAULT 'regular';

-- Add any missing indexes on existing tables
CREATE INDEX IF NOT EXISTS idx_orders_date ON orders(order_date);
CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(status);
CREATE INDEX IF NOT EXISTS idx_inventory_product ON inventory(product_id);
CREATE INDEX IF NOT EXISTS idx_production_lots_expiry ON production_lots(expiry_date);
CREATE INDEX IF NOT EXISTS idx_customers_active ON customers(is_active);