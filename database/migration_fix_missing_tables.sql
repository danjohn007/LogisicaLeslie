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

-- Add missing indexes only if they don't exist

-- delivery_routes(route_date)
SET @idx_exists = (
    SELECT COUNT(1) FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'delivery_routes'
      AND index_name = 'idx_delivery_routes_date'
);
SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_delivery_routes_date ON delivery_routes(route_date);',
    'SELECT "idx_delivery_routes_date ya existe";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- route_orders(route_id)
SET @idx_exists = (
    SELECT COUNT(1) FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'route_orders'
      AND index_name = 'idx_route_orders_route'
);
SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_route_orders_route ON route_orders(route_id);',
    'SELECT "idx_route_orders_route ya existe";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- route_orders(order_id)
SET @idx_exists = (
    SELECT COUNT(1) FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'route_orders'
      AND index_name = 'idx_route_orders_order'
);
SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_route_orders_order ON route_orders(order_id);',
    'SELECT "idx_route_orders_order ya existe";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- orders(order_date)
SET @idx_exists = (
    SELECT COUNT(1) FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'orders'
      AND index_name = 'idx_orders_date'
);
SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_orders_date ON orders(order_date);',
    'SELECT "idx_orders_date ya existe";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- orders(status)
SET @idx_exists = (
    SELECT COUNT(1) FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'orders'
      AND index_name = 'idx_orders_status'
);
SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_orders_status ON orders(status);',
    'SELECT "idx_orders_status ya existe";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- inventory(product_id)
SET @idx_exists = (
    SELECT COUNT(1) FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'inventory'
      AND index_name = 'idx_inventory_product'
);
SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_inventory_product ON inventory(product_id);',
    'SELECT "idx_inventory_product ya existe";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- production_lots(expiry_date)
SET @idx_exists = (
    SELECT COUNT(1) FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'production_lots'
      AND index_name = 'idx_production_lots_expiry'
);
SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_production_lots_expiry ON production_lots(expiry_date);',
    'SELECT "idx_production_lots_expiry ya existe";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- customers(is_active)
SET @idx_exists = (
    SELECT COUNT(1) FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'customers'
      AND index_name = 'idx_customers_active'
);
SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_customers_active ON customers(is_active);',
    'SELECT "idx_customers_active ya existe";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Fix production_lots table - add missing column if needed
SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'production_lots'
      AND column_name = 'production_type'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE production_lots ADD COLUMN production_type VARCHAR(50) DEFAULT \'regular\';',
    'SELECT "production_type ya existe";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
