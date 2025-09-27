-- Datos de ejemplo para Sistema de Logística - Quesos y Productos Leslie
-- Ejecutar después de schema.sql

USE logistica_leslie;

-- Categorías de productos
INSERT INTO categories (name, description) VALUES
('Quesos Frescos', 'Quesos de pasta fresca y suave'),
('Quesos Maduros', 'Quesos con proceso de maduración'),
('Productos Lácteos', 'Crema, mantequilla y otros lácteos'),
('Quesos Especiales', 'Quesos gourmet y especialidades');

-- Productos
INSERT INTO products (code, name, description, category_id, unit_type, unit_weight, price_per_unit, minimum_stock) VALUES
('QF001', 'Queso Fresco 500g', 'Queso fresco tradicional', 1, 'pieza', 0.500, 45.00, 50),
('QF002', 'Queso Panela 1kg', 'Queso panela premium', 1, 'pieza', 1.000, 85.00, 30),
('QF003', 'Queso Oaxaca 250g', 'Queso Oaxaca hebra', 1, 'pieza', 0.250, 35.00, 40),
('QM001', 'Queso Manchego 500g', 'Queso manchego madurado', 2, 'pieza', 0.500, 120.00, 20),
('QM002', 'Queso Gouda 1kg', 'Queso Gouda holandés', 2, 'pieza', 1.000, 180.00, 15),
('PL001', 'Crema Natural 200ml', 'Crema fresca natural', 3, 'pieza', 0.200, 25.00, 60),
('PL002', 'Mantequilla 250g', 'Mantequilla sin sal', 3, 'pieza', 0.250, 55.00, 40),
('QE001', 'Queso Azul 300g', 'Queso azul artesanal', 4, 'pieza', 0.300, 150.00, 10),
('QG001', 'Queso Granel', 'Queso vendido por peso', 1, 'granel', 1.000, 80.00, 100);

-- Usuarios del sistema
INSERT INTO users (username, email, password_hash, first_name, last_name, role, phone) VALUES
('admin', 'admin@leslie.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'Sistema', 'admin', '555-0001'),
('gerente', 'gerente@leslie.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carlos', 'Pérez', 'manager', '555-0002'),
('vendedor1', 'vendedor1@leslie.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'María', 'González', 'seller', '555-0003'),
('chofer1', 'chofer1@leslie.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan', 'Martínez', 'driver', '555-0004'),
('almacen1', 'almacen@leslie.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ana', 'López', 'warehouse', '555-0005');

-- Vehículos
INSERT INTO vehicles (plate, brand, model, year, capacity, fuel_type) VALUES
('ABC-123', 'Ford', 'Transit', 2020, 1500.00, 'diesel'),
('DEF-456', 'Chevrolet', 'NHR', 2019, 2000.00, 'diesel'),
('GHI-789', 'Nissan', 'NP300', 2021, 1000.00, 'gasoline');

-- Clientes
INSERT INTO customers (code, business_name, contact_name, phone, email, address, city, state, credit_limit, credit_days) VALUES
('CLI001', 'Tienda Don Carlos', 'Carlos Ramírez', '555-1001', 'carlos@tienda.com', 'Av. Principal 123', 'México', 'CDMX', 5000.00, 15),
('CLI002', 'Supermercado La Esquina', 'Rosa Hernández', '555-1002', 'rosa@esquina.com', 'Calle 5 de Mayo 456', 'Guadalajara', 'Jalisco', 8000.00, 30),
('CLI003', 'Restaurante El Buen Sabor', 'Miguel Torres', '555-1003', 'miguel@sabor.com', 'Plaza Central 789', 'Monterrey', 'Nuevo León', 3000.00, 0),
('CLI004', 'Cafetería Luna', 'Laura Sánchez', '555-1004', 'laura@luna.com', 'Av. Reforma 321', 'Puebla', 'Puebla', 2000.00, 15),
('CLI005', 'Hotel Estrella', 'Roberto Kim', '555-1005', 'roberto@estrella.com', 'Blvd. Turístico 654', 'Cancún', 'Quintana Roo', 10000.00, 30);

-- Lotes de producción
INSERT INTO production_lots (lot_number, product_id, production_date, expiry_date, quantity_produced, quantity_available, unit_cost, quality_status, created_by) VALUES
('LT2024001', 1, '2024-01-15', '2024-02-15', 100, 85, 35.00, 'excellent', 5),
('LT2024002', 2, '2024-01-16', '2024-03-16', 50, 45, 65.00, 'good', 5),
('LT2024003', 3, '2024-01-17', '2024-02-17', 80, 75, 28.00, 'excellent', 5),
('LT2024004', 4, '2024-01-18', '2024-04-18', 30, 25, 95.00, 'good', 5),
('LT2024005', 5, '2024-01-19', '2024-05-19', 20, 18, 140.00, 'excellent', 5),
('LT2024006', 9, '2024-01-20', '2024-03-20', 200, 180, 60.00, 'good', 5);

-- Inventario
INSERT INTO inventory (product_id, lot_id, quantity, location) VALUES
(1, 1, 85, 'Almacén A-1'),
(2, 2, 45, 'Almacén A-2'),
(3, 3, 75, 'Almacén A-1'),
(4, 4, 25, 'Almacén B-1'),
(5, 5, 18, 'Almacén B-2'),
(9, 6, 180, 'Almacén C-1');

-- Pedidos
INSERT INTO orders (order_number, customer_id, order_date, delivery_date, status, total_amount, final_amount, created_by) VALUES
('PED2024001', 1, '2024-01-25', '2024-01-26', 'confirmed', 450.00, 450.00, 3),
('PED2024002', 2, '2024-01-25', '2024-01-27', 'pending', 680.00, 680.00, 3),
('PED2024003', 3, '2024-01-26', '2024-01-28', 'in_route', 320.00, 320.00, 3),
('PED2024004', 4, '2024-01-26', '2024-01-29', 'pending', 180.00, 180.00, 3),
('PED2024005', 5, '2024-01-27', '2024-01-30', 'confirmed', 890.00, 890.00, 3);

-- Detalles de pedidos
INSERT INTO order_details (order_id, product_id, lot_id, quantity_ordered, unit_price, subtotal) VALUES
(1, 1, 1, 10, 45.00, 450.00),
(2, 2, 2, 8, 85.00, 680.00),
(3, 3, 3, 6, 35.00, 210.00),
(3, 6, NULL, 4, 25.00, 100.00),
(4, 1, 1, 4, 45.00, 180.00),
(5, 4, 4, 5, 120.00, 600.00),
(5, 5, 5, 2, 180.00, 360.00);

-- Rutas
INSERT INTO routes (route_name, route_date, driver_id, vehicle_id, status, start_time) VALUES
('Ruta Centro', '2024-01-26', 4, 1, 'planned', '08:00:00'),
('Ruta Norte', '2024-01-27', 4, 2, 'planned', '09:00:00'),
('Ruta Sur', '2024-01-28', 4, 1, 'in_progress', '08:30:00');

-- Paradas de ruta
INSERT INTO route_stops (route_id, order_id, stop_order, estimated_arrival) VALUES
(1, 1, 1, '09:00:00'),
(2, 2, 1, '10:00:00'),
(3, 3, 1, '09:30:00'),
(3, 4, 2, '11:00:00');

-- Ventas directas
INSERT INTO direct_sales (sale_number, customer_id, route_id, sale_date, total_amount, payment_method, seller_id) VALUES
('VD2024001', 1, 1, '2024-01-26', 135.00, 'cash', 3),
('VD2024002', 2, 2, '2024-01-27', 240.00, 'card', 3);

-- Detalles de ventas directas
INSERT INTO direct_sale_details (sale_id, product_id, lot_id, quantity, unit_price, subtotal) VALUES
(1, 1, 1, 3, 45.00, 135.00),
(2, 3, 3, 4, 35.00, 140.00),
(2, 6, NULL, 4, 25.00, 100.00);

-- Movimientos de inventario
INSERT INTO inventory_movements (type, product_id, lot_id, quantity, reference_id, reference_type, created_by) VALUES
('production', 1, 1, 100, 1, 'production_lot', 5),
('production', 2, 2, 50, 2, 'production_lot', 5),
('sale', 1, 1, -10, 1, 'order', 3),
('sale', 1, 1, -3, 1, 'direct_sale', 3);

-- Configuración del sistema
INSERT INTO system_config (config_key, config_value, description) VALUES
('company_name', 'Quesos y Productos Leslie', 'Nombre de la empresa'),
('company_address', 'Av. Industrial 123, Col. Láctea', 'Dirección de la empresa'),
('company_phone', '555-0000', 'Teléfono principal'),
('company_email', 'contacto@leslie.com', 'Email de contacto'),
('tax_rate', '0.16', 'Tasa de IVA'),
('currency', 'MXN', 'Moneda del sistema'),
('timezone', 'America/Mexico_City', 'Zona horaria'),
('qr_enabled', '1', 'Códigos QR habilitados'),
('whatsapp_enabled', '1', 'Integración WhatsApp habilitada'),
('auto_lot_assignment', '1', 'Asignación automática de lotes por FIFO');

-- Encuestas de satisfacción de ejemplo
INSERT INTO customer_surveys (customer_id, order_id, survey_date, rating, product_quality_rating, service_rating, delivery_rating, comments, channel) VALUES
(1, 1, '2024-01-27', 5, 5, 4, 5, 'Excelente calidad de productos, muy satisfecho', 'whatsapp'),
(2, 2, '2024-01-28', 4, 4, 4, 3, 'Buenos productos, pero la entrega llegó un poco tarde', 'email'),
(3, 3, '2024-01-29', 5, 5, 5, 5, 'Perfecto servicio, seguiremos comprando', 'web');

-- Algunos retornos de ejemplo
INSERT INTO returns (return_number, order_id, return_date, reason, status, total_amount, notes) VALUES
('RET2024001', 1, '2024-01-28', 'quality', 'pending', 45.00, 'Cliente reporta sabor extraño en una pieza'),
('RET2024002', 2, '2024-01-29', 'excess', 'approved', 85.00, 'Cliente ordenó de más');

INSERT INTO return_details (return_id, product_id, lot_id, quantity, unit_price, subtotal, disposition) VALUES
(1, 1, 1, 1, 45.00, 45.00, 'discard'),
(2, 2, 2, 1, 85.00, 85.00, 'restock');