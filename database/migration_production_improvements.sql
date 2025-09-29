-- Migración para mejorar tablas de producción
-- Sistema de Logística - Quesos y Productos Leslie

USE fix360_logisticaleslie;

-- Verificar y agregar columnas faltantes en production_lots
SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'production_lots'
      AND column_name = 'quantity_available'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE production_lots ADD COLUMN quantity_available DECIMAL(10,3) NOT NULL DEFAULT 0 AFTER quantity_produced;',
    'SELECT "quantity_available ya existe";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'production_lots'
      AND column_name = 'production_type'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE production_lots ADD COLUMN production_type VARCHAR(50) DEFAULT "fresco" AFTER quantity_available;',
    'SELECT "production_type ya existe";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Actualizar quantity_available donde sea 0 (hacer que sea igual a quantity_produced)
UPDATE production_lots 
SET quantity_available = quantity_produced 
WHERE quantity_available = 0 OR quantity_available IS NULL;

-- Verificar y crear tabla inventory_movements si no existe
CREATE TABLE IF NOT EXISTS inventory_movements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type ENUM('production', 'sale', 'return', 'adjustment', 'transfer') NOT NULL,
    product_id INT NOT NULL,
    lot_id INT,
    quantity DECIMAL(10,3) NOT NULL,
    movement_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reference_id INT,
    reference_type VARCHAR(50),
    notes TEXT,
    created_by INT,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (lot_id) REFERENCES production_lots(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Agregar índices para mejorar performance
SET @idx_exists = (
    SELECT COUNT(1) FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'inventory_movements'
      AND index_name = 'idx_movements_date'
);
SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_movements_date ON inventory_movements(movement_date);',
    'SELECT "idx_movements_date ya existe";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_exists = (
    SELECT COUNT(1) FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'inventory_movements'
      AND index_name = 'idx_movements_type'
);
SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_movements_type ON inventory_movements(type);',
    'SELECT "idx_movements_type ya existe";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_exists = (
    SELECT COUNT(1) FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'production_lots'
      AND index_name = 'idx_production_lots_number'
);
SET @sql = IF(@idx_exists = 0,
    'CREATE UNIQUE INDEX idx_production_lots_number ON production_lots(lot_number);',
    'SELECT "idx_production_lots_number ya existe";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_exists = (
    SELECT COUNT(1) FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'production_lots'
      AND index_name = 'idx_production_lots_product'
);
SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_production_lots_product ON production_lots(product_id);',
    'SELECT "idx_production_lots_product ya existe";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_exists = (
    SELECT COUNT(1) FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'production_lots'
      AND index_name = 'idx_production_lots_production_date'
);
SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_production_lots_production_date ON production_lots(production_date);',
    'SELECT "idx_production_lots_production_date ya existe";'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Mensaje de éxito
SELECT 'Migración de tablas de producción completada exitosamente' as mensaje;