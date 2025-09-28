-- Setup completo para MySQL - Sistema de Logística Leslie
-- Base de datos: fix360_logisticaleslie
-- Usuario: fix360_logisticaleslie
-- Contraseña: Danjohn007!

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

-- Tabla de categorías de productos
CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de productos
CREATE TABLE IF NOT EXISTS products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category_id INT,
    unit_type ENUM('granel', 'pieza', 'paquete') NOT NULL DEFAULT 'pieza',
    unit_weight DECIMAL(8,3),
    price_per_unit DECIMAL(10,2) NOT NULL,
    minimum_stock INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
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
    payment_terms TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de lotes de producción
CREATE TABLE IF NOT EXISTS production_lots (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lot_number VARCHAR(20) UNIQUE NOT NULL,
    product_id INT NOT NULL,
    production_date DATE NOT NULL,
    expiry_date DATE,
    quantity_produced INT NOT NULL,
    production_type ENUM('fresco', 'curado', 'procesado') NOT NULL,
    status ENUM('en_produccion', 'terminado', 'vendido') DEFAULT 'en_produccion',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Tabla de inventario
CREATE TABLE IF NOT EXISTS inventory (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    lot_number VARCHAR(20),
    quantity INT DEFAULT 0,
    location VARCHAR(50),
    expiry_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Tabla de pedidos
CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    customer_id INT NOT NULL,
    order_date DATE NOT NULL,
    delivery_date DATE,
    status ENUM('pending', 'confirmed', 'in_preparation', 'ready', 'delivered', 'cancelled') DEFAULT 'pending',
    total_amount DECIMAL(10,2) DEFAULT 0,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    final_amount DECIMAL(10,2) DEFAULT 0,
    payment_method ENUM('cash', 'credit', 'transfer') DEFAULT 'cash',
    payment_status ENUM('pending', 'partial', 'paid') DEFAULT 'pending',
    notes TEXT,
    qr_code TEXT,
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
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Tabla de rutas de entrega
CREATE TABLE IF NOT EXISTS delivery_routes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    route_name VARCHAR(100) NOT NULL,
    driver_id INT,
    route_date DATE NOT NULL,
    start_time TIME,
    end_time TIME,
    status ENUM('planned', 'in_progress', 'completed', 'cancelled') DEFAULT 'planned',
    total_orders INT DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES users(id)
);

-- Tabla de asignación de pedidos a rutas
CREATE TABLE IF NOT EXISTS route_orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    route_id INT NOT NULL,
    order_id INT NOT NULL,
    sequence_order INT,
    delivery_status ENUM('pending', 'delivered', 'failed') DEFAULT 'pending',
    delivery_time TIMESTAMP NULL,
    notes TEXT,
    FOREIGN KEY (route_id) REFERENCES delivery_routes(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

-- Tabla de ventas directas
CREATE TABLE IF NOT EXISTS direct_sales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sale_number VARCHAR(20) UNIQUE NOT NULL,
    customer_id INT,
    sale_date DATE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'credit', 'transfer') DEFAULT 'cash',
    seller_id INT NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (seller_id) REFERENCES users(id)
);

-- Tabla de detalles de ventas directas
CREATE TABLE IF NOT EXISTS direct_sale_details (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sale_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES direct_sales(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
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

-- Tabla de configuración del sistema
CREATE TABLE IF NOT EXISTS system_config (
    id INT PRIMARY KEY AUTO_INCREMENT,
    config_key VARCHAR(100) UNIQUE NOT NULL,
    config_value TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

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

INSERT INTO inventory (product_id, quantity, lot_number, location) VALUES
(1, 25, 'LT2024001', 'Refrigerador A'),
(2, 18, 'LT2024002', 'Refrigerador A'),
(3, 8, 'LT2024003', 'Refrigerador B'),
(4, 50, 'LT2024004', 'Refrigerador A'),
(5, 30, 'LT2024005', 'Refrigerador C');

INSERT INTO system_config (config_key, config_value, description) VALUES
('company_name', 'Quesos y Productos Leslie', 'Nombre de la empresa'),
('company_address', 'Av. Industria 123, Guadalajara, Jalisco', 'Dirección de la empresa'),
('company_phone', '33-1234-5678', 'Teléfono de la empresa'),
('company_email', 'info@leslie.com', 'Email de contacto'),
('qr_code_size', '200', 'Tamaño de códigos QR en píxeles'),
('session_timeout', '3600', 'Tiempo de sesión en segundos'),
('backup_frequency', 'daily', 'Frecuencia de respaldos'),
('notification_email', 'admin@leslie.com', 'Email para notificaciones del sistema');