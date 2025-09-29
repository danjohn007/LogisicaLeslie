-- Migración para crear tabla de produccion
-- Si la tabla ya existe, esto no causará error

CREATE TABLE IF NOT EXISTS `produccion` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `lot_number` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
    `product_id` INT(11) NOT NULL,
    `production_date` DATE NOT NULL,
    `expiry_date` DATE NOT NULL,
    `quantity_produced` DECIMAL(10,3) NOT NULL,
    `quantity_available` DECIMAL(10,3) NOT NULL,
    `unit_cost` DECIMAL(10,2) DEFAULT NULL,
    `quality_status` ENUM('excellent', 'good', 'fair', 'rejected') CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT 'good',
    `notes` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
    `created_by` INT(11) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `production_type` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT 'regular',
    PRIMARY KEY (`id`),
    INDEX `idx_lot_number` (`lot_number`),
    INDEX `idx_product_id` (`product_id`),
    INDEX `idx_expiry_date` (`expiry_date`),
    INDEX `idx_created_by` (`created_by`),
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Agregar algunos datos de ejemplo (solo si la tabla está vacía)
INSERT IGNORE INTO `produccion` 
(`lot_number`, `product_id`, `production_date`, `expiry_date`, `quantity_produced`, `quantity_available`, `unit_cost`, `quality_status`, `production_type`, `notes`, `created_by`) 
VALUES 
('LOT-20250929-001', 1, '2025-09-29', '2025-10-15', 50.000, 50.000, 75.00, 'good', 'regular', 'Lote de prueba de Queso Oaxaca', 1),
('LOT-20250929-002', 2, '2025-09-29', '2025-10-10', 30.000, 25.000, 45.00, 'excellent', 'premium', 'Lote premium de Queso Panela', 1),
('LOT-20250928-003', 3, '2025-09-28', '2025-11-28', 20.000, 20.000, 95.00, 'good', 'regular', 'Queso Manchego curado', 1);