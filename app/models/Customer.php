<?php
/**
 * Modelo Customer
 * Sistema de Logística - Quesos y Productos Leslie
 */

class Customer extends Model {
    protected $table = 'customers';
    
    public function findActive() {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY business_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function findByCity($city) {
        $sql = "SELECT * FROM {$this->table} WHERE city = ? AND is_active = 1 ORDER BY business_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$city]);
        return $stmt->fetchAll();
    }
    
    public function generateCustomerCode() {
        $prefix = 'CLI' . date('Y');
        $sql = "SELECT MAX(CAST(SUBSTRING(code, 8) AS UNSIGNED)) as max_num 
                FROM {$this->table} 
                WHERE code LIKE ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prefix . '%']);
        $result = $stmt->fetch();
        
        $nextNumber = ($result['max_num'] ?? 0) + 1;
        return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
    
    public function getCustomerWithStats($customerId) {
        $sql = "
            SELECT c.*,
                   COUNT(o.id) as total_orders,
                   COALESCE(SUM(o.final_amount), 0) as total_spent,
                   MAX(o.created_at) as last_order_date,
                   AVG(cs.rating) as avg_rating
            FROM {$this->table} c
            LEFT JOIN orders o ON c.id = o.customer_id
            LEFT JOIN customer_surveys cs ON c.id = cs.customer_id
            WHERE c.id = ?
            GROUP BY c.id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId]);
        return $stmt->fetch();
    }
    
    public function getCustomerOrders($customerId, $limit = 10) {
        $sql = "
            SELECT o.*, COUNT(od.id) as total_items
            FROM orders o
            LEFT JOIN order_details od ON o.id = od.order_id
            WHERE o.customer_id = ?
            GROUP BY o.id
            ORDER BY o.created_at DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId, $limit]);
        return $stmt->fetchAll();
    }
    
    public function getCustomerBalance($customerId) {
        // Calcular el balance del cliente (órdenes a crédito pendientes)
        $sql = "
            SELECT COALESCE(SUM(final_amount), 0) as pending_balance
            FROM orders 
            WHERE customer_id = ? 
            AND payment_method = 'credit' 
            AND payment_status IN ('pending', 'partial')
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId]);
        $result = $stmt->fetch();
        return $result['pending_balance'];
    }
    
    public function getCreditAvailable($customerId) {
        $customer = $this->findById($customerId);
        if (!$customer) return 0;
        
        $pendingBalance = $this->getCustomerBalance($customerId);
        return max(0, $customer['credit_limit'] - $pendingBalance);
    }
    
    public function searchCustomers($searchTerm) {
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE is_active = 1 
            AND (
                business_name LIKE ? 
                OR contact_name LIKE ? 
                OR code LIKE ?
                OR phone LIKE ?
            )
            ORDER BY business_name
            LIMIT 20
        ";
        $searchPattern = "%{$searchTerm}%";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchPattern, $searchPattern, $searchPattern, $searchPattern]);
        return $stmt->fetchAll();
    }
    
    public function getTopCustomers($limit = 10) {
        $sql = "
            SELECT c.*, 
                   COUNT(o.id) as total_orders,
                   COALESCE(SUM(o.final_amount), 0) as total_spent
            FROM {$this->table} c
            LEFT JOIN orders o ON c.id = o.customer_id
            WHERE c.is_active = 1
            GROUP BY c.id
            ORDER BY total_spent DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}