<?php
/**
 * Modelo Route
 * Sistema de Logística - Quesos y Productos Leslie
 * Módulo de Optimización Logística y Rutas
 */

class Route extends Model {
    protected $table = 'routes';
    
    public function createRoute($data) {
        try {
            $this->db->beginTransaction();
            
            // Generar código de ruta
            $routeCode = $this->generateRouteCode();
            
            $sql = "
                INSERT INTO routes (
                    route_code, route_name, driver_id, vehicle_id, 
                    route_date, start_time, estimated_duration, 
                    status, notes, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'planned', ?, ?)
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $routeCode,
                $data['route_name'],
                $data['driver_id'],
                $data['vehicle_id'] ?? null,
                $data['route_date'],
                $data['start_time'],
                $data['estimated_duration'] ?? null,
                $data['notes'] ?? null,
                $_SESSION['user_id']
            ]);
            
            $routeId = $this->db->lastInsertId();
            
            // Agregar órdenes a la ruta
            if (!empty($data['orders'])) {
                $this->assignOrdersToRoute($routeId, $data['orders']);
            }
            
            $this->db->commit();
            return $routeId;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    public function assignOrdersToRoute($routeId, $orders) {
        $sql = "
            INSERT INTO route_orders (route_id, order_id, stop_sequence, estimated_arrival, delivery_status)
            VALUES (?, ?, ?, ?, 'pending')
        ";
        $stmt = $this->db->prepare($sql);
        
        foreach ($orders as $index => $orderData) {
            $stmt->execute([
                $routeId,
                $orderData['order_id'],
                $index + 1,
                $orderData['estimated_arrival'] ?? null
            ]);
            
            // Actualizar estado del pedido
            $this->updateOrderStatus($orderData['order_id'], 'en_ruta');
        }
    }
    
    public function getRouteWithDetails($routeId) {
        $sql = "
            SELECT 
                r.*,
                u.first_name as driver_name,
                u.last_name as driver_lastname,
                u.phone as driver_phone,
                v.model as vehicle_model,
                v.license_plate,
                COUNT(ro.id) as total_stops,
                COUNT(CASE WHEN ro.delivery_status = 'delivered' THEN 1 END) as completed_stops,
                COUNT(CASE WHEN ro.delivery_status = 'pending' THEN 1 END) as pending_stops
            FROM routes r
            LEFT JOIN users u ON r.driver_id = u.id
            LEFT JOIN vehicles v ON r.vehicle_id = v.id
            LEFT JOIN route_orders ro ON r.id = ro.route_id
            WHERE r.id = ?
            GROUP BY r.id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$routeId]);
        return $stmt->fetch();
    }
    
    public function getRouteOrders($routeId) {
        $sql = "
            SELECT 
                ro.*,
                o.order_number,
                o.total_amount,
                o.special_instructions,
                c.business_name as customer_name,
                c.contact_name,
                c.phone as customer_phone,
                c.address as customer_address,
                c.city,
                c.coordinates_lat,
                c.coordinates_lng
            FROM route_orders ro
            JOIN orders o ON ro.order_id = o.id
            JOIN customers c ON o.customer_id = c.id
            WHERE ro.route_id = ?
            ORDER BY ro.stop_sequence
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$routeId]);
        return $stmt->fetchAll();
    }
    
    public function updateDeliveryStatus($routeOrderId, $status, $deliveryData = []) {
        try {
            $this->db->beginTransaction();
            
            $sql = "
                UPDATE route_orders 
                SET delivery_status = ?, 
                    actual_arrival = CURRENT_TIMESTAMP,
                    delivery_notes = ?,
                    delivered_by = ?
                WHERE id = ?
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $status,
                $deliveryData['notes'] ?? null,
                $_SESSION['user_id'],
                $routeOrderId
            ]);
            
            // Obtener información del pedido
            $orderSql = "SELECT order_id FROM route_orders WHERE id = ?";
            $orderStmt = $this->db->prepare($orderSql);
            $orderStmt->execute([$routeOrderId]);
            $order = $orderStmt->fetch();
            
            if ($order) {
                // Actualizar estado del pedido
                $newOrderStatus = match($status) {
                    'delivered' => 'entregado',
                    'failed' => 'cancelado',
                    'partial' => 'parcial',
                    default => 'en_ruta'
                };
                
                $this->updateOrderStatus($order['order_id'], $newOrderStatus);
                
                // Si hay ajustes en la entrega
                if (!empty($deliveryData['adjustments'])) {
                    $this->recordDeliveryAdjustments($order['order_id'], $deliveryData['adjustments']);
                }
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    public function recordDeliveryAdjustments($orderId, $adjustments) {
        $sql = "
            INSERT INTO delivery_adjustments (
                order_id, product_id, original_quantity, 
                delivered_quantity, adjustment_reason, 
                adjusted_by, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
        ";
        $stmt = $this->db->prepare($sql);
        
        foreach ($adjustments as $adjustment) {
            $stmt->execute([
                $orderId,
                $adjustment['product_id'],
                $adjustment['original_quantity'],
                $adjustment['delivered_quantity'],
                $adjustment['reason'],
                $_SESSION['user_id']
            ]);
        }
    }
    
    public function getAllRoutes($filters = []) {
        $where = ["1=1"];
        $params = [];
        
        if (!empty($filters['driver_id'])) {
            $where[] = "r.driver_id = ?";
            $params[] = $filters['driver_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = "r.route_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "r.route_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['status'])) {
            $where[] = "r.status = ?";
            $params[] = $filters['status'];
        }
        
        $sql = "
            SELECT 
                r.*,
                CONCAT(u.first_name, ' ', u.last_name) as driver_name,
                COUNT(ro.id) as total_stops,
                COUNT(CASE WHEN ro.delivery_status = 'delivered' THEN 1 END) as completed_stops,
                v.model as vehicle_model,
                v.license_plate
            FROM routes r
            LEFT JOIN users u ON r.driver_id = u.id
            LEFT JOIN vehicles v ON r.vehicle_id = v.id
            LEFT JOIN route_orders ro ON r.id = ro.route_id
            WHERE " . implode(' AND ', $where) . "
            GROUP BY r.id
            ORDER BY r.route_date DESC, r.start_time DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getAvailableDrivers($date = null) {
        $date = $date ?: date('Y-m-d');
        
        $sql = "
            SELECT DISTINCT u.id, CONCAT(u.first_name, ' ', u.last_name) as name, u.phone
            FROM users u
            WHERE u.user_role IN ('driver', 'seller_driver', 'admin')
            AND u.is_active = 1
            AND u.id NOT IN (
                SELECT driver_id FROM routes 
                WHERE route_date = ? AND status IN ('planned', 'in_progress')
            )
            ORDER BY u.first_name, u.last_name
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$date]);
        return $stmt->fetchAll();
    }
    
    public function getAvailableVehicles($date = null) {
        $date = $date ?: date('Y-m-d');
        
        $sql = "
            SELECT v.*
            FROM vehicles v
            WHERE v.is_active = 1
            AND v.id NOT IN (
                SELECT vehicle_id FROM routes 
                WHERE route_date = ? AND status IN ('planned', 'in_progress')
                AND vehicle_id IS NOT NULL
            )
            ORDER BY v.model
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$date]);
        return $stmt->fetchAll();
    }
    
    public function startRoute($routeId) {
        $sql = "
            UPDATE routes 
            SET status = 'in_progress', 
                actual_start_time = CURRENT_TIMESTAMP,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ? AND status = 'planned'
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$routeId]);
    }
    
    public function completeRoute($routeId, $completionData = []) {
        $sql = "
            UPDATE routes 
            SET status = 'completed', 
                actual_end_time = CURRENT_TIMESTAMP,
                completion_notes = ?,
                total_distance = ?,
                fuel_consumed = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $completionData['notes'] ?? null,
            $completionData['distance'] ?? null,
            $completionData['fuel'] ?? null,
            $routeId
        ]);
    }
    
    public function getRouteEfficiencyStats($routeId) {
        $sql = "
            SELECT 
                r.estimated_duration,
                TIMESTAMPDIFF(MINUTE, r.actual_start_time, r.actual_end_time) as actual_duration,
                COUNT(ro.id) as total_stops,
                COUNT(CASE WHEN ro.delivery_status = 'delivered' THEN 1 END) as successful_deliveries,
                COUNT(CASE WHEN ro.delivery_status = 'failed' THEN 1 END) as failed_deliveries,
                AVG(TIMESTAMPDIFF(MINUTE, ro.estimated_arrival, ro.actual_arrival)) as avg_delay
            FROM routes r
            LEFT JOIN route_orders ro ON r.id = ro.route_id
            WHERE r.id = ?
            GROUP BY r.id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$routeId]);
        return $stmt->fetch();
    }
    
    private function generateRouteCode() {
        $prefix = 'RUT' . date('Y');
        $sql = "SELECT COUNT(*) + 1 as next_number FROM routes WHERE route_code LIKE ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prefix . '%']);
        $result = $stmt->fetch();
        $nextNumber = str_pad($result['next_number'], 4, '0', STR_PAD_LEFT);
        return $prefix . $nextNumber;
    }
    
    private function updateOrderStatus($orderId, $status) {
        $sql = "UPDATE orders SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$status, $orderId]);
    }
}