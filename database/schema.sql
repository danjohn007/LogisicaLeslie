-- Base de Datos para Sistema de Logística - Quesos y Productos Leslie
-- MySQL 5.7+

CREATE DATABASE IF NOT EXISTS fix360_logisticaleslie CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fix360_logisticaleslie;

-- Tabla de usuarios del sistema
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    role ENUM('admin', 'manager', 'seller', 'driver', 'warehouse') NOT NULL,
    phone VARCHAR(20),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de productos
CREATE TABLE IF NOT EXISTS products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category_id INT,
    unit_type ENUM('granel', 'pieza', 'paquete') NOT NULL,
    unit_weight DECIMAL(8,3),
    price_per_unit DECIMAL(10,2) NOT NULL,
    minimum_stock INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de categorías de productos
CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de lotes de producción
CREATE TABLE IF NOT EXISTS production_lots (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lot_number VARCHAR(20) UNIQUE NOT NULL,
    product_id INT NOT NULL,
    production_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    quantity_produced DECIMAL(10,3) NOT NULL,
    quantity_available DECIMAL(10,3) NOT NULL,
    unit_cost DECIMAL(10,2),
    quality_status ENUM('excellent', 'good', 'fair', 'rejected') DEFAULT 'good',
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Tabla de inventario
CREATE TABLE IF NOT EXISTS inventory (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    lot_id INT NOT NULL,
    quantity DECIMAL(10,3) NOT NULL,
    reserved_quantity DECIMAL(10,3) DEFAULT 0,
    location VARCHAR(50),
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (lot_id) REFERENCES production_lots(id),
    UNIQUE KEY unique_product_lot (product_id, lot_id)
);

-- Tabla de clientes
CREATE TABLE IF NOT EXISTS customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE NOT NULL,
    business_name VARCHAR(100) NOT NULL,
    contact_name VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    postal_code VARCHAR(10),
    credit_limit DECIMAL(10,2) DEFAULT 0,
    credit_days INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de pedidos
CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    customer_id INT NOT NULL,
    order_date DATE NOT NULL,
    delivery_date DATE,
    status ENUM('pending', 'confirmed', 'in_route', 'delivered', 'cancelled') DEFAULT 'pending',
    total_amount DECIMAL(10,2) DEFAULT 0,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    final_amount DECIMAL(10,2) DEFAULT 0,
    payment_method ENUM('cash', 'card', 'transfer', 'credit') DEFAULT 'cash',
    payment_status ENUM('pending', 'partial', 'paid') DEFAULT 'pending',
    notes TEXT,
    qr_code VARCHAR(255),
    channel_source ENUM('web', 'whatsapp', 'phone', 'email') DEFAULT 'web',
    created_by INT,
    assigned_to INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id)
);

-- Tabla de detalles de pedidos
CREATE TABLE IF NOT EXISTS order_details (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    lot_id INT,
    quantity_ordered DECIMAL(10,3) NOT NULL,
    quantity_delivered DECIMAL(10,3) DEFAULT 0,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (lot_id) REFERENCES production_lots(id)
);

-- Tabla de rutas
CREATE TABLE IF NOT EXISTS routes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    route_name VARCHAR(100) NOT NULL,
    route_date DATE NOT NULL,
    driver_id INT NOT NULL,
    vehicle_id INT,
    status ENUM('planned', 'in_progress', 'completed', 'cancelled') DEFAULT 'planned',
    start_time TIME,
    end_time TIME,
    total_distance DECIMAL(8,2),
    fuel_cost DECIMAL(8,2),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES users(id)
);

-- Tabla de vehículos
CREATE TABLE IF NOT EXISTS vehicles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    plate VARCHAR(20) UNIQUE NOT NULL,
    brand VARCHAR(50),
    model VARCHAR(50),
    year YEAR,
    capacity DECIMAL(8,2),
    fuel_type ENUM('gasoline', 'diesel', 'electric'),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de paradas de ruta
CREATE TABLE IF NOT EXISTS route_stops (
    id INT PRIMARY KEY AUTO_INCREMENT,
    route_id INT NOT NULL,
    order_id INT NOT NULL,
    stop_order INT NOT NULL,
    stop_sequence INT DEFAULT 1,
    estimated_arrival TIME,
    actual_arrival TIME,
    status ENUM('pending', 'arrived', 'delivered', 'failed') DEFAULT 'pending',
    delivery_status ENUM('pending', 'delivered', 'failed', 'partial') DEFAULT 'pending',
    notes TEXT,
    delivery_notes TEXT,
    delivered_by INT,
    FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (delivered_by) REFERENCES users(id)
);

-- Tabla de ventas directas
CREATE TABLE IF NOT EXISTS direct_sales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sale_number VARCHAR(20) UNIQUE NOT NULL,
    customer_id INT,
    route_id INT,
    sale_date DATE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    final_amount DECIMAL(10,2) DEFAULT 0.00,
    payment_method ENUM('cash', 'card', 'transfer') NOT NULL,
    payment_status ENUM('paid', 'pending') DEFAULT 'paid',
    seller_id INT NOT NULL,
    qr_code VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (route_id) REFERENCES routes(id),
    FOREIGN KEY (seller_id) REFERENCES users(id)
);

-- Tabla de detalles de ventas directas
CREATE TABLE IF NOT EXISTS direct_sale_details (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sale_id INT NOT NULL,
    product_id INT NOT NULL,
    lot_id INT,
    quantity DECIMAL(10,3) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES direct_sales(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (lot_id) REFERENCES production_lots(id)
);

-- Tabla de retornos
CREATE TABLE IF NOT EXISTS returns (
    id INT PRIMARY KEY AUTO_INCREMENT,
    return_number VARCHAR(20) UNIQUE NOT NULL,
    order_id INT,
    sale_id INT,
    return_date DATE NOT NULL,
    reason ENUM('expired', 'damaged', 'quality', 'excess', 'other') NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'processed') DEFAULT 'pending',
    total_amount DECIMAL(10,2) DEFAULT 0,
    processed_by INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (sale_id) REFERENCES direct_sales(id),
    FOREIGN KEY (processed_by) REFERENCES users(id)
);

-- Tabla de detalles de retornos
CREATE TABLE IF NOT EXISTS return_details (
    id INT PRIMARY KEY AUTO_INCREMENT,
    return_id INT NOT NULL,
    product_id INT NOT NULL,
    lot_id INT,
    quantity DECIMAL(10,3) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    disposition ENUM('restock', 'discard', 'donate') DEFAULT 'restock',
    FOREIGN KEY (return_id) REFERENCES returns(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (lot_id) REFERENCES production_lots(id)
);

-- Tabla de encuestas de satisfacción
CREATE TABLE IF NOT EXISTS customer_surveys (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    order_id INT,
    sale_id INT,
    survey_date DATE NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    product_quality_rating INT CHECK (product_quality_rating >= 1 AND product_quality_rating <= 5),
    service_rating INT CHECK (service_rating >= 1 AND service_rating <= 5),
    delivery_rating INT CHECK (delivery_rating >= 1 AND delivery_rating <= 5),
    comments TEXT,
    channel ENUM('whatsapp', 'email', 'phone', 'web') DEFAULT 'web',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (sale_id) REFERENCES direct_sales(id)
);

-- Tabla de movimientos de inventario
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

-- Tabla de configuración del sistema
CREATE TABLE IF NOT EXISTS system_config (
    id INT PRIMARY KEY AUTO_INCREMENT,
    config_key VARCHAR(100) UNIQUE NOT NULL,
    config_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de sesiones de usuario
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    logout_time TIMESTAMP NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Agregar foreign keys que faltaron
ALTER TABLE products ADD FOREIGN KEY (category_id) REFERENCES categories(id);
ALTER TABLE routes ADD FOREIGN KEY (vehicle_id) REFERENCES vehicles(id);

-- Vista route_orders para compatibilidad con el código existente
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

-- Índices para mejorar performance (con chequeo previo)
-- orders(order_date)
SET @index_exists := (
  SELECT COUNT(1)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'orders'
    AND index_name = 'idx_orders_date'
);
SET @create_index := IF(@index_exists = 0, 'CREATE INDEX idx_orders_date ON orders(order_date);', 'SELECT "Index already exists";');
PREPARE stmt FROM @create_index; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- orders(status)
SET @index_exists := (
  SELECT COUNT(1)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'orders'
    AND index_name = 'idx_orders_status'
);
SET @create_index := IF(@index_exists = 0, 'CREATE INDEX idx_orders_status ON orders(status);', 'SELECT "Index already exists";');
PREPARE stmt FROM @create_index; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- inventory(product_id)
SET @index_exists := (
  SELECT COUNT(1)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'inventory'
    AND index_name = 'idx_inventory_product'
);
SET @create_index := IF(@index_exists = 0, 'CREATE INDEX idx_inventory_product ON inventory(product_id);', 'SELECT "Index already exists";');
PREPARE stmt FROM @create_index; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- production_lots(expiry_date)
SET @index_exists := (
  SELECT COUNT(1)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'production_lots'
    AND index_name = 'idx_production_lots_expiry'
);
SET @create_index := IF(@index_exists = 0, 'CREATE INDEX idx_production_lots_expiry ON production_lots(expiry_date);', 'SELECT "Index already exists";');
PREPARE stmt FROM @create_index; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- customers(is_active)
SET @index_exists := (
  SELECT COUNT(1)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'customers'
    AND index_name = 'idx_customers_active'
);
SET @create_index := IF(@index_exists = 0, 'CREATE INDEX idx_customers_active ON customers(is_active);', 'SELECT "Index already exists";');
PREPARE stmt FROM @create_index; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- routes(route_date)
SET @index_exists := (
  SELECT COUNT(1)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'routes'
    AND index_name = 'idx_routes_date'
);
SET @create_index := IF(@index_exists = 0, 'CREATE INDEX idx_routes_date ON routes(route_date);', 'SELECT "Index already exists";');
PREPARE stmt FROM @create_index; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- inventory_movements(movement_date)
SET @index_exists := (
  SELECT COUNT(1)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'inventory_movements'
    AND index_name = 'idx_movements_date'
);
SET @create_index := IF(@index_exists = 0, 'CREATE INDEX idx_movements_date ON inventory_movements(movement_date);', 'SELECT "Index already exists";');
PREPARE stmt FROM @create_index; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- customer_surveys(survey_date)
SET @index_exists := (
  SELECT COUNT(1)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'customer_surveys'
    AND index_name = 'idx_surveys_date'
);
SET @create_index := IF(@index_exists = 0, 'CREATE INDEX idx_surveys_date ON customer_surveys(survey_date);', 'SELECT "Index already exists";');
PREPARE stmt FROM @create_index; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- user_sessions(user_id)
SET @index_exists := (
  SELECT COUNT(1)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'user_sessions'
    AND index_name = 'idx_user_sessions_user'
);
SET @create_index := IF(@index_exists = 0, 'CREATE INDEX idx_user_sessions_user ON user_sessions(user_id);', 'SELECT "Index already exists";');
PREPARE stmt FROM @create_index; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- user_sessions(login_time)
SET @index_exists := (
  SELECT COUNT(1)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'user_sessions'
    AND index_name = 'idx_user_sessions_login'
);
SET @create_index := IF(@index_exists = 0, 'CREATE INDEX idx_user_sessions_login ON user_sessions(login_time);', 'SELECT "Index already exists";');
PREPARE stmt FROM @create_index; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Insertar datos iniciales
INSERT INTO categories (name, description) VALUES
('Quesos Frescos', 'Quesos de producción diaria'),
('Quesos Curados', 'Quesos con proceso de maduración'),
('Productos Especiales', 'Productos de temporada y especiales');

INSERT INTO users (username, email, password_hash, first_name, last_name, role, phone) VALUES
('admin', 'admin@leslie.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'Sistema', 'admin', '555-0001'),
('gerente', 'gerente@leslie.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carlos', 'Pérez', 'manager', '555-0002'),
('vendedor1', 'vendedor@leslie.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'María', 'González', 'seller', '555-0003'),
('chofer1', 'chofer@leslie.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'José', 'Martínez', 'driver', '555-0004');

INSERT INTO customers (code, business_name, contact_name, phone, email, address, city, state, credit_limit, credit_days) VALUES
('CLI001', 'Tienda Don Carlos', 'Carlos Ramírez', '555-1001', 'carlos@tienda.com', 'Av. Principal 123', 'México', 'CDMX', 5000.00, 15),
('CLI002', 'Supermercado La Esquina', 'Rosa Hernández', '555-1002', 'rosa@esquina.com', 'Calle 5 de Mayo 456', 'Guadalajara', 'Jalisco', 8000.00, 30),
('CLI003', 'Abarrotes El Buen Precio', 'Luis Torres', '555-1003', 'luis@buenprecio.com', 'Calle Morelos 789', 'Zapopan', 'Jalisco', 3000.00, 15);

INSERT INTO products (code, name, description, category_id, unit_type, price_per_unit, minimum_stock) VALUES
('PRD001', 'Queso Oaxaca 500g', 'Queso Oaxaca tradicional de 500 gramos', 1, 'pieza', 75.00, 20),
('PRD002', 'Queso Panela 400g', 'Queso Panela fresco de 400 gramos', 1, 'pieza', 45.00, 15),
('PRD003', 'Queso Manchego 300g', 'Queso Manchego curado de 300 gramos', 2, 'pieza', 95.00, 10),
('PRD004', 'Crema Ácida 200ml', 'Crema ácida natural', 1, 'pieza', 25.00, 30),
('PRD005', 'Yogurt Natural 1L', 'Yogurt natural sin azúcar', 1, 'pieza', 35.00, 25);

-- Recuerda que para inventory, lot_id debe existir, ajusta lotes y cantidades según tu flujo de producción.

INSERT INTO system_config (config_key, config_value, description) VALUES
('company_name', 'Quesos y Productos Leslie', 'Nombre de la empresa'),
('company_address', 'Av. Industria 123, Guadalajara, Jalisco', 'Dirección de la empresa'),
('company_phone', '33-1234-5678', 'Teléfono de la empresa'),
('company_email', 'info@leslie.com', 'Email de contacto'),
('qr_code_size', '200', 'Tamaño de códigos QR en píxeles'),
('session_timeout', '3600', 'Tiempo de sesión en segundos'),
('backup_frequency', 'daily', 'Frecuencia de respaldos'),
('notification_email', 'admin@leslie.com', 'Email para notificaciones del sistema');
