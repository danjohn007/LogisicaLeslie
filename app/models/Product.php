<?php
/**
 * Modelo Product
 * Sistema de Logística - Quesos y Productos Leslie
 */

class Product extends Model {
    protected $table = 'products';
    
    public function findByCategory($categoryId) {
        $sql = "SELECT * FROM {$this->table} WHERE category_id = ? AND is_active = 1 ORDER BY name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll();
    }
    
    public function findActive() {
        $sql = "SELECT p.*, c.name as category_name 
                FROM {$this->table} p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.is_active = 1 
                ORDER BY p.name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getProductWithStock($productId) {
        $sql = "
            SELECT p.*, c.name as category_name,
                   COALESCE(SUM(i.quantity), 0) as total_stock,
                   COALESCE(SUM(i.reserved_quantity), 0) as reserved_stock,
                   COALESCE(SUM(i.quantity - i.reserved_quantity), 0) as available_stock
            FROM {$this->table} p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN inventory i ON p.id = i.product_id
            WHERE p.id = ?
            GROUP BY p.id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetch();
    }
    
    public function getProductsWithStock() {
        $sql = "
            SELECT p.*, c.name as category_name,
                   COALESCE(SUM(i.quantity), 0) as total_stock,
                   COALESCE(SUM(i.reserved_quantity), 0) as reserved_stock,
                   COALESCE(SUM(i.quantity - i.reserved_quantity), 0) as available_stock,
                   p.price_per_unit,
                   p.unit_type,
                   CASE 
                       WHEN COALESCE(SUM(i.quantity), 0) <= p.minimum_stock THEN 'low'
                       WHEN COALESCE(SUM(i.quantity), 0) <= p.minimum_stock * 1.5 THEN 'medium'
                       ELSE 'good'
                   END as stock_status
            FROM {$this->table} p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN inventory i ON p.id = i.product_id
            WHERE p.is_active = 1
            GROUP BY p.id, p.code, p.name, p.price_per_unit, p.unit_type, p.minimum_stock, p.category_id, c.name
            ORDER BY p.name
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getLowStockProducts() {
        $sql = "
            SELECT p.*, c.name as category_name,
                   COALESCE(SUM(i.quantity), 0) as total_stock
            FROM {$this->table} p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN inventory i ON p.id = i.product_id
            WHERE p.is_active = 1
            GROUP BY p.id
            HAVING total_stock <= p.minimum_stock
            ORDER BY total_stock ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function generateProductCode($categoryId = null) {
        $prefix = 'PRD';
        if ($categoryId) {
            $sql = "SELECT name FROM categories WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$categoryId]);
            $category = $stmt->fetch();
            if ($category) {
                $prefix = strtoupper(substr($category['name'], 0, 3));
            }
        }
        
        $sql = "SELECT MAX(CAST(SUBSTRING(code, 4) AS UNSIGNED)) as max_num 
                FROM {$this->table} 
                WHERE code LIKE ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prefix . '%']);
        $result = $stmt->fetch();
        
        $nextNumber = ($result['max_num'] ?? 0) + 1;
        return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
    
    public function getProductLots($productId) {
        $sql = "
            SELECT pl.*, i.quantity as available_quantity, i.location
            FROM production_lots pl
            LEFT JOIN inventory i ON pl.id = i.lot_id
            WHERE pl.product_id = ? AND pl.quantity_available > 0
            ORDER BY pl.expiry_date ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }
}