<?php
/**
 * Modelo Production
 * Sistema de Logística - Quesos y Productos Leslie
 */

class Production extends Model {
    protected $table = 'produccion';
    
    public function getProductionLots($limit = 20) {
        $sql = "
            SELECT p.*, pr.name as product_name, pr.code as product_code
            FROM {$this->table} p
            JOIN products pr ON p.product_id = pr.id
            ORDER BY p.created_at DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    public function getProductionLotById($id) {
        $sql = "
            SELECT p.*, pr.name as product_name, pr.code as product_code, pr.unit_type
            FROM {$this->table} p
            JOIN products pr ON p.product_id = pr.id
            WHERE p.id = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getProductionLotByNumber($lotNumber) {
        $sql = "
            SELECT p.*, pr.name as product_name, pr.code as product_code
            FROM {$this->table} p
            JOIN products pr ON p.product_id = pr.id
            WHERE p.lot_number = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$lotNumber]);
        return $stmt->fetch();
    }
    
    public function createLot($data) {
        // Verificar que el número de lote no exista
        if ($this->lotNumberExists($data['lot_number'])) {
            throw new Exception('El número de lote ya existe.');
        }
        
        $sql = "
            INSERT INTO {$this->table} 
            (lot_number, product_id, production_date, expiry_date, quantity_produced, quantity_available, unit_cost, quality_status, production_type, notes, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['lot_number'],
            $data['product_id'],
            $data['production_date'],
            $data['expiry_date'],
            $data['quantity_produced'],
            $data['quantity_available'] ?? $data['quantity_produced'], // Inicialmente igual a quantity_produced
            $data['unit_cost'] ?? null,
            $data['quality_status'] ?? 'good',
            $data['production_type'] ?? 'regular',
            $data['notes'] ?? null,
            $data['created_by'] ?? null
        ]);
    }
    
    public function updateLot($id, $data) {
        $sql = "
            UPDATE {$this->table} 
            SET quantity_available = ?, 
                unit_cost = ?, 
                quality_status = ?, 
                notes = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['quantity_available'],
            $data['unit_cost'],
            $data['quality_status'],
            $data['notes'],
            $id
        ]);
    }
    
    public function updateQuantityAvailable($id, $newQuantity) {
        $sql = "UPDATE {$this->table} SET quantity_available = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$newQuantity, $id]);
    }
    
    public function reduceQuantityAvailable($id, $quantityUsed) {
        $sql = "
            UPDATE {$this->table} 
            SET quantity_available = quantity_available - ?, 
                updated_at = CURRENT_TIMESTAMP 
            WHERE id = ? AND quantity_available >= ?
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$quantityUsed, $id, $quantityUsed]);
    }
    
    public function lotNumberExists($lotNumber, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE lot_number = ?";
        $params = [$lotNumber];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    public function getExpiringLots($days = 7) {
        $sql = "
            SELECT p.*, pr.name as product_name, pr.code as product_code
            FROM {$this->table} p
            JOIN products pr ON p.product_id = pr.id
            WHERE p.expiry_date IS NOT NULL 
            AND p.expiry_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
            AND p.quantity_available > 0
            ORDER BY p.expiry_date ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }
    
    public function getProductionByDateRange($startDate, $endDate) {
        $sql = "
            SELECT p.*, pr.name as product_name, pr.code as product_code
            FROM {$this->table} p
            JOIN products pr ON p.product_id = pr.id
            WHERE p.production_date BETWEEN ? AND ?
            ORDER BY p.production_date DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }
    
    public function getProductionStats() {
        $sql = "
            SELECT 
                COUNT(*) as total_lots,
                SUM(quantity_produced) as total_produced,
                SUM(quantity_available) as total_available,
                AVG(unit_cost) as avg_cost,
                COUNT(CASE WHEN production_date = CURDATE() THEN 1 END) as today_production,
                COUNT(CASE WHEN expiry_date IS NOT NULL AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as expiring_soon
            FROM {$this->table}
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function getProductionByQualityStatus() {
        $sql = "
            SELECT 
                quality_status,
                COUNT(*) as count,
                SUM(quantity_produced) as total_quantity
            FROM {$this->table}
            GROUP BY quality_status
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getAvailableLotsByProduct($productId) {
        $sql = "
            SELECT *
            FROM {$this->table}
            WHERE product_id = ? 
            AND quantity_available > 0
            AND quality_status != 'rejected'
            ORDER BY expiry_date ASC, production_date ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }
}