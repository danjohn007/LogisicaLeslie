-- Agregar tabla de historial de estados de pedidos
CREATE TABLE IF NOT EXISTS order_status_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    old_status ENUM('pending','confirmed','in_route','delivered','cancelled') NULL,
    new_status ENUM('pending','confirmed','in_route','delivered','cancelled') NOT NULL,
    notes TEXT,
    changed_by INT,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_order_id (order_id),
    INDEX idx_changed_at (changed_at)
);