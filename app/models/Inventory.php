<?php
/**
 * Modelo Inventory
 * Sistema de Logística - Quesos y Productos Leslie
 */

class Inventory extends Model {
    protected $table = 'inventory';
    
    public function getInventoryWithDetails($includeZero = false) {
        try {
            $whereClause = $includeZero ? '' : 'AND i.quantity > 0';
            
            $sql = "
                SELECT 
                    i.id,
                    i.product_id,
                    i.lot_id,
                    i.quantity,
                    i.reserved_quantity,
                    (i.quantity - i.reserved_quantity) as available_quantity,
                    i.location,
                    i.last_updated,
                    p.code as product_code,
                    p.name as product_name,
                    p.minimum_stock,
                    p.unit_type,
                    p.price_per_unit,
                    pl.lot_number,
                    pl.production_date,
                    pl.expiry_date,
                    pl.production_type,
                    c.name as category_name,
                    CASE 
                        WHEN i.quantity <= 0 THEN 'empty'
                        WHEN i.quantity <= p.minimum_stock THEN 'critical'
                        WHEN i.quantity <= (p.minimum_stock * 1.5) THEN 'low'
                        WHEN i.quantity <= (p.minimum_stock * 2) THEN 'warning'
                        ELSE 'normal'
                    END as stock_status,
                    CASE 
                        WHEN pl.expiry_date IS NULL THEN 'no_expiry'
                        WHEN pl.expiry_date < CURDATE() THEN 'expired'
                        WHEN pl.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 'expires_soon'
                        WHEN pl.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'expires_month'
                        ELSE 'good'
                    END as expiry_status,
                    DATEDIFF(pl.expiry_date, CURDATE()) as days_to_expiry
                FROM {$this->table} i
                JOIN products p ON i.product_id = p.id
                LEFT JOIN production_lots pl ON i.lot_id = pl.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.is_active = 1 {$whereClause}
                ORDER BY 
                    CASE 
                        WHEN i.quantity <= 0 THEN 1
                        WHEN i.quantity <= p.minimum_stock THEN 2
                        WHEN pl.expiry_date < CURDATE() THEN 3
                        WHEN pl.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 4
                        ELSE 5
                    END,
                    p.name, pl.expiry_date ASC
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting inventory with details: " . $e->getMessage());
            return [];
        }
    }
    
    public function getInventorySummary() {
        try {
            $sql = "
                SELECT 
                    p.id as product_id,
                    p.code as product_code,
                    p.name as product_name,
                    p.minimum_stock,
                    p.unit_type,
                    p.price_per_unit,
                    c.name as category_name,
                    COALESCE(SUM(i.quantity), 0) as total_stock,
                    COALESCE(SUM(i.reserved_quantity), 0) as total_reserved,
                    COALESCE(SUM(i.quantity - i.reserved_quantity), 0) as total_available,
                    COUNT(DISTINCT i.lot_id) as total_lots,
                    CASE 
                        WHEN COALESCE(SUM(i.quantity), 0) <= 0 THEN 'empty'
                        WHEN COALESCE(SUM(i.quantity), 0) <= p.minimum_stock THEN 'critical'
                        WHEN COALESCE(SUM(i.quantity), 0) <= (p.minimum_stock * 1.5) THEN 'low'
                        WHEN COALESCE(SUM(i.quantity), 0) <= (p.minimum_stock * 2) THEN 'warning'
                        ELSE 'normal'
                    END as stock_status,
                    COALESCE(SUM(i.quantity * p.price_per_unit), 0) as inventory_value
                FROM products p
                LEFT JOIN {$this->table} i ON p.id = i.product_id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.is_active = 1
                GROUP BY p.id, p.code, p.name, p.minimum_stock, p.unit_type, p.price_per_unit, c.name
                ORDER BY 
                    CASE 
                        WHEN COALESCE(SUM(i.quantity), 0) <= 0 THEN 1
                        WHEN COALESCE(SUM(i.quantity), 0) <= p.minimum_stock THEN 2
                        ELSE 3
                    END,
                    p.name
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting inventory summary: " . $e->getMessage());
            return [];
        }
    }
    
    public function getExpiringProducts($days = 30) {
        try {
            $sql = "
                SELECT 
                    i.id,
                    i.quantity,
                    i.location,
                    p.code as product_code,
                    p.name as product_name,
                    pl.lot_number,
                    pl.expiry_date,
                    DATEDIFF(pl.expiry_date, CURDATE()) as days_to_expiry,
                    CASE 
                        WHEN pl.expiry_date < CURDATE() THEN 'expired'
                        WHEN pl.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 'critical'
                        WHEN pl.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 15 DAY) THEN 'warning'
                        ELSE 'attention'
                    END as urgency_level
                FROM {$this->table} i
                JOIN products p ON i.product_id = p.id
                JOIN production_lots pl ON i.lot_id = pl.id
                WHERE pl.expiry_date IS NOT NULL 
                AND pl.expiry_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
                AND i.quantity > 0
                ORDER BY pl.expiry_date ASC, urgency_level ASC
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$days]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting expiring products: " . $e->getMessage());
            return [];
        }
    }
    
    public function getInventoryStats() {
        try {
            $sql = "
                SELECT 
                    COUNT(DISTINCT p.id) as total_products,
                    COUNT(DISTINCT i.lot_id) as total_lots,
                    COALESCE(SUM(i.quantity), 0) as total_units,
                    COALESCE(SUM(i.quantity * p.price_per_unit), 0) as total_value,
                    COUNT(CASE WHEN i.quantity <= p.minimum_stock THEN 1 END) as low_stock_products,
                    COUNT(CASE WHEN pl.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND pl.expiry_date IS NOT NULL THEN 1 END) as expiring_soon,
                    COUNT(CASE WHEN pl.expiry_date < CURDATE() AND pl.expiry_date IS NOT NULL THEN 1 END) as expired_products
                FROM products p
                LEFT JOIN {$this->table} i ON p.id = i.product_id AND i.quantity > 0
                LEFT JOIN production_lots pl ON i.lot_id = pl.id
                WHERE p.is_active = 1
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error getting inventory stats: " . $e->getMessage());
            return [
                'total_products' => 0,
                'total_lots' => 0,
                'total_units' => 0,
                'total_value' => 0,
                'low_stock_products' => 0,
                'expiring_soon' => 0,
                'expired_products' => 0
            ];
        }
    }
    
    public function getMovementHistory($productId = null, $limit = 50) {
        try {
            $whereClause = '';
            $params = [];
            
            if ($productId) {
                $whereClause = 'WHERE im.product_id = ?';
                $params[] = $productId;
            }
            
            $sql = "
                SELECT 
                    im.*,
                    p.code as product_code,
                    p.name as product_name,
                    pl.lot_number,
                    u.first_name,
                    u.last_name
                FROM inventory_movements im
                JOIN products p ON im.product_id = p.id
                LEFT JOIN production_lots pl ON im.lot_id = pl.id
                LEFT JOIN users u ON im.created_by = u.id
                {$whereClause}
                ORDER BY im.movement_date DESC
                LIMIT ?
            ";
            
            $params[] = $limit;
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting movement history: " . $e->getMessage());
            return [];
        }
    }
    
    public function reserveQuantity($productId, $lotId, $quantity) {
        try {
            $sql = "
                UPDATE {$this->table} 
                SET reserved_quantity = reserved_quantity + ? 
                WHERE product_id = ? AND lot_id = ? AND (quantity - reserved_quantity) >= ?
            ";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$quantity, $productId, $lotId, $quantity]);
        } catch (Exception $e) {
            error_log("Error reserving quantity: " . $e->getMessage());
            return false;
        }
    }
    
    public function releaseReservation($productId, $lotId, $quantity) {
        try {
            $sql = "
                UPDATE {$this->table} 
                SET reserved_quantity = GREATEST(0, reserved_quantity - ?) 
                WHERE product_id = ? AND lot_id = ?
            ";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$quantity, $productId, $lotId]);
        } catch (Exception $e) {
            error_log("Error releasing reservation: " . $e->getMessage());
            return false;
        }
    }
    
    public function getProductAvailability($productId) {
        try {
            $sql = "
                SELECT 
                    i.id,
                    i.lot_id,
                    pl.lot_number,
                    i.quantity,
                    i.reserved_quantity,
                    (i.quantity - i.reserved_quantity) as available_quantity,
                    pl.expiry_date,
                    i.location
                FROM {$this->table} i
                JOIN production_lots pl ON i.lot_id = pl.id
                WHERE i.product_id = ? AND (i.quantity - i.reserved_quantity) > 0
                ORDER BY 
                    CASE WHEN pl.expiry_date IS NULL THEN 1 ELSE 0 END,
                    pl.expiry_date ASC,
                    pl.production_date ASC
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$productId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting product availability: " . $e->getMessage());
            return [];
        }
    }
}
?>