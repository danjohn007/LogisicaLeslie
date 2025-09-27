<?php
/**
 * Modelo Order
 * Sistema de Logística - Quesos y Productos Leslie
 */

class Order extends Model {
    protected $table = 'orders';
    
    public function findByCustomer($customerId) {
        $sql = "SELECT * FROM {$this->table} WHERE customer_id = ? ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId]);
        return $stmt->fetchAll();
    }
    
    public function findByStatus($status) {
        $sql = "SELECT o.*, c.business_name as customer_name 
                FROM {$this->table} o
                JOIN customers c ON o.customer_id = c.id
                WHERE o.status = ? 
                ORDER BY o.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$status]);
        return $stmt->fetchAll();
    }
    
    public function generateOrderNumber() {
        $prefix = 'PED' . date('Y');
        $sql = "SELECT MAX(CAST(SUBSTRING(order_number, 8) AS UNSIGNED)) as max_num 
                FROM {$this->table} 
                WHERE order_number LIKE ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prefix . '%']);
        $result = $stmt->fetch();
        
        $nextNumber = ($result['max_num'] ?? 0) + 1;
        return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
    
    public function getOrderWithDetails($orderId) {
        $sql = "
            SELECT o.*, c.business_name, c.contact_name, c.phone, c.address,
                   u.first_name as created_by_name, u.last_name as created_by_lastname
            FROM {$this->table} o
            JOIN customers c ON o.customer_id = c.id
            LEFT JOIN users u ON o.created_by = u.id
            WHERE o.id = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        
        if ($order) {
            // Obtener detalles del pedido
            $sql = "
                SELECT od.*, p.name as product_name, p.unit_type, pl.lot_number
                FROM order_details od
                JOIN products p ON od.product_id = p.id
                LEFT JOIN production_lots pl ON od.lot_id = pl.id
                WHERE od.order_id = ?
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$orderId]);
            $order['details'] = $stmt->fetchAll();
        }
        
        return $order;
    }
    
    public function updateStatus($orderId, $status, $notes = null) {
        $updateData = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($notes) {
            $updateData['notes'] = $notes;
        }
        
        return $this->update($orderId, $updateData);
    }
    
    public function getTodaysOrders() {
        $sql = "
            SELECT o.*, c.business_name 
            FROM {$this->table} o
            JOIN customers c ON o.customer_id = c.id
            WHERE DATE(o.created_at) = CURDATE()
            ORDER BY o.created_at DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getOrdersByDateRange($startDate, $endDate) {
        $sql = "
            SELECT o.*, c.business_name 
            FROM {$this->table} o
            JOIN customers c ON o.customer_id = c.id
            WHERE DATE(o.created_at) BETWEEN ? AND ?
            ORDER BY o.created_at DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }
}