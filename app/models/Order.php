<?php
/**
 * Modelo Order
 * Sistema de Logística - Quesos y Productos Leslie
 */

require_once dirname(__DIR__) . '/lib/qrlib.php';

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
    
    public function getAllOrdersWithDetails($limit = 50, $filters = []) {
        try {
            $conditions = [];
            $params = [];
            
            if (isset($filters['status']) && $filters['status'] !== '') {
                $conditions[] = "o.status = ?";
                $params[] = $filters['status'];
            }
            
            if (isset($filters['customer_id']) && $filters['customer_id'] > 0) {
                $conditions[] = "o.customer_id = ?";
                $params[] = $filters['customer_id'];
            }
            
            if (isset($filters['date_from']) && $filters['date_from']) {
                $conditions[] = "DATE(o.order_date) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (isset($filters['date_to']) && $filters['date_to']) {
                $conditions[] = "DATE(o.order_date) <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (isset($filters['delivery_date']) && $filters['delivery_date']) {
                $conditions[] = "DATE(o.delivery_date) = ?";
                $params[] = $filters['delivery_date'];
            }
            
            $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
            
            $sql = "
                SELECT 
                    o.id,
                    o.order_number,
                    o.customer_id,
                    o.order_date,
                    o.delivery_date,
                    o.status,
                    o.total_amount,
                    o.discount_amount,
                    o.final_amount,
                    o.payment_method,
                    o.payment_status,
                    o.notes,
                    o.qr_code,
                    COALESCE(o.channel_source, 'web') as channel_source,
                    o.created_at,
                    o.updated_at,
                    c.business_name as customer_name,
                    c.contact_name,
                    c.phone as customer_phone,
                    c.address as customer_address,
                    c.city as customer_city,
                    u.first_name as created_by_name,
                    u.last_name as created_by_lastname,
                    a.first_name as assigned_to_name,
                    a.last_name as assigned_to_lastname,
                    COUNT(od.id) as total_items,
                    SUM(od.quantity_ordered) as total_quantity,
                    CASE 
                        WHEN o.delivery_date < CURDATE() AND o.status IN ('pending', 'confirmed') THEN 'overdue'
                        WHEN o.delivery_date = CURDATE() AND o.status IN ('pending', 'confirmed') THEN 'today'
                        WHEN o.delivery_date <= DATE_ADD(CURDATE(), INTERVAL 1 DAY) AND o.status IN ('pending', 'confirmed') THEN 'tomorrow'
                        ELSE 'future'
                    END as delivery_urgency
                FROM {$this->table} o
                JOIN customers c ON o.customer_id = c.id
                LEFT JOIN users u ON o.created_by = u.id
                LEFT JOIN users a ON o.assigned_to = a.id
                LEFT JOIN order_details od ON o.id = od.order_id
                {$whereClause}
                GROUP BY o.id
                ORDER BY 
                    CASE 
                        WHEN o.status = 'pending' THEN 1
                        WHEN o.status = 'confirmed' THEN 2
                        WHEN o.status = 'in_route' THEN 3
                        WHEN o.status = 'delivered' THEN 4
                        WHEN o.status = 'cancelled' THEN 5
                        ELSE 6
                    END,
                    o.delivery_date ASC,
                    o.created_at DESC
                LIMIT ?
            ";
            
            $params[] = $limit;
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting orders with details: " . $e->getMessage());
            return [];
        }
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
        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
    
    public function getOrderWithDetails($orderId) {
        try {
            $sql = "
                SELECT o.*, 
                       c.business_name, c.contact_name, c.phone, c.address, c.city,
                       c.credit_limit, c.credit_days,
                       u.first_name as created_by_name, u.last_name as created_by_lastname,
                       a.first_name as assigned_to_name, a.last_name as assigned_to_lastname
                FROM {$this->table} o
                JOIN customers c ON o.customer_id = c.id
                LEFT JOIN users u ON o.created_by = u.id
                LEFT JOIN users a ON o.assigned_to = a.id
                WHERE o.id = ?
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();
            
            if ($order) {
                // Obtener detalles del pedido
                $sql = "
                    SELECT od.*, 
                           p.name as product_name, 
                           p.code as product_code,
                           p.unit_type, 
                           p.price_per_unit,
                           pl.lot_number,
                           pl.expiry_date
                    FROM order_details od
                    JOIN products p ON od.product_id = p.id
                    LEFT JOIN production_lots pl ON od.lot_id = pl.id
                    WHERE od.order_id = ?
                    ORDER BY od.id
                ";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$orderId]);
                $order['details'] = $stmt->fetchAll();
            }
            
            return $order;
        } catch (Exception $e) {
            error_log("Error getting order with details: " . $e->getMessage());
            return null;
        }
    }
    
    public function createOrderWithDetails($orderData, $orderDetails) {
        try {
            $this->beginTransaction();
            
            // Generar número de pedido y código QR
            $orderNumber = $this->generateOrderNumber();
            $qrCode = $this->generateQRCode($orderNumber);
            
            // Preparar datos del pedido
            $orderData['order_number'] = $orderNumber;
            $orderData['qr_code'] = $qrCode;
            $orderData['created_by'] = $_SESSION['user_id'] ?? 1;
            $orderData['order_date'] = $orderData['order_date'] ?? date('Y-m-d');
            $orderData['status'] = $orderData['status'] ?? 'pending';
            $orderData['payment_status'] = $orderData['payment_status'] ?? 'pending';
            
            // Crear pedido principal
            $orderId = $this->createOrder($orderData);
            
            if (!$orderId) {
                throw new Exception('Error al crear el pedido');
            }
            
            // Agregar detalles del pedido
            $totalAmount = 0;
            foreach ($orderDetails as $detail) {
                if (empty($detail['product_id']) || $detail['quantity_ordered'] <= 0) {
                    continue;
                }
                
                $subtotal = floatval($detail['quantity_ordered']) * floatval($detail['unit_price']);
                $totalAmount += $subtotal;
                
                $detailData = [
                    'order_id' => $orderId,
                    'product_id' => $detail['product_id'],
                    'lot_id' => $detail['lot_id'] ?? null,
                    'quantity_ordered' => floatval($detail['quantity_ordered']),
                    'unit_price' => floatval($detail['unit_price']),
                    'subtotal' => $subtotal
                ];
                
                $this->createOrderDetail($detailData);
            }
            
            // Calcular totales
            $discountAmount = floatval($orderData['discount_amount'] ?? 0);
            $finalAmount = $totalAmount - $discountAmount;
            
            // Actualizar totales del pedido
            $this->update($orderId, [
                'total_amount' => $totalAmount,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount
            ]);
            
            $this->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    private function createOrder($data) {
        return parent::create($data);
    }
    
    private function createOrderDetail($data) {
        $sql = "
            INSERT INTO order_details (order_id, product_id, lot_id, quantity_ordered, unit_price, subtotal)
            VALUES (?, ?, ?, ?, ?, ?)
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['order_id'],
            $data['product_id'],
            $data['lot_id'] ?? null,
            $data['quantity_ordered'],
            $data['unit_price'],
            $data['subtotal']
        ]);
    }
    
    public function updateStatus($orderId, $newStatus, $notes = '', $userId = null) {
        try {
            $this->beginTransaction();
            
            // Obtener estado actual
            $currentOrder = $this->findById($orderId);
            if (!$currentOrder) {
                throw new Exception('Pedido no encontrado');
            }
            
            $oldStatus = $currentOrder['status'];
            
            // Actualizar estado del pedido
            $updateData = [
                'status' => $newStatus,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($notes) {
                $updateData['notes'] = $notes;
            }
            
            $this->update($orderId, $updateData);
            
            // Acciones específicas según el nuevo estado
            $this->processStatusChange($orderId, $oldStatus, $newStatus);
            
            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    private function processStatusChange($orderId, $oldStatus, $newStatus) {
        // Lógica específica para cada cambio de estado
        switch ($newStatus) {
            case 'confirmed':
                // Reservar inventario
                $this->reserveInventoryForOrder($orderId);
                break;
                
            case 'cancelled':
                // Liberar inventario reservado
                $this->releaseInventoryForOrder($orderId);
                break;
                
            case 'delivered':
                // Actualizar inventario final y liberar reservas
                $this->fulfillOrderInventory($orderId);
                break;
        }
    }
    
    private function reserveInventoryForOrder($orderId) {
        // Implementar lógica de reserva de inventario
        // Se integrará con el modelo Inventory
        $details = $this->getOrderDetails($orderId);
        foreach ($details as $detail) {
            // Reservar cantidad en inventario
            // $inventoryModel->reserveQuantity($detail['product_id'], $detail['lot_id'], $detail['quantity_ordered']);
        }
    }
    
    private function releaseInventoryForOrder($orderId) {
        // Implementar lógica de liberación de inventario
        $details = $this->getOrderDetails($orderId);
        foreach ($details as $detail) {
            // Liberar cantidad reservada
            // $inventoryModel->releaseReservation($detail['product_id'], $detail['lot_id'], $detail['quantity_ordered']);
        }
    }
    
    private function fulfillOrderInventory($orderId) {
        // Implementar lógica de cumplimiento de pedido
        $details = $this->getOrderDetails($orderId);
        foreach ($details as $detail) {
            // Reducir inventario y liberar reserva
            // $inventoryModel->fulfillReservation($detail['product_id'], $detail['lot_id'], $detail['quantity_delivered']);
        }
    }
    
    public function getOrderDetails($orderId) {
        $sql = "SELECT * FROM order_details WHERE order_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }
    
    public function generateQRCode($orderNumber) {
        try {
            // Crear directorio si no existe
            $qrDir = dirname(__DIR__, 2) . '/public/qr_codes/';
            if (!is_dir($qrDir)) {
                mkdir($qrDir, 0755, true);
            }
            
            // Generar nombre único para el archivo QR
            $qrFileName = 'order_' . $orderNumber . '_' . uniqid() . '.png';
            $qrFilePath = $qrDir . $qrFileName;
            
            // Contenido del QR (URL de verificación)
            $qrContent = BASE_URL . 'pedidos/verify/' . $orderNumber;
            
            // Generar código QR
            QRcode::png($qrContent, $qrFilePath, QR_ECLEVEL_M, 8, 2);
            
            return 'qr_codes/' . $qrFileName;
        } catch (Exception $e) {
            error_log("Error generating QR code: " . $e->getMessage());
            return null;
        }
    }
    
    public function updateDeliveryQuantities($orderId, $deliveryData) {
        try {
            $this->beginTransaction();
            
            foreach ($deliveryData as $detailId => $data) {
                $sql = "
                    UPDATE order_details 
                    SET quantity_delivered = ?, delivery_notes = ?
                    WHERE id = ? AND order_id = ?
                ";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $data['quantity_delivered'],
                    $data['delivery_notes'] ?? '',
                    $detailId,
                    $orderId
                ]);
            }
            
            // Actualizar estado si todas las cantidades están entregadas
            $this->checkAndUpdateDeliveryStatus($orderId);
            
            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    private function checkAndUpdateDeliveryStatus($orderId) {
        $sql = "
            SELECT 
                SUM(quantity_ordered) as total_ordered,
                SUM(quantity_delivered) as total_delivered
            FROM order_details 
            WHERE order_id = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        $result = $stmt->fetch();
        
        if ($result['total_delivered'] >= $result['total_ordered']) {
            $this->updateStatus($orderId, 'delivered', 'Entrega completada automáticamente');
        } elseif ($result['total_delivered'] > 0) {
            $this->updateStatus($orderId, 'partially_delivered', 'Entrega parcial');
        }
    }
    
    public function getOrderStats($dateFrom = null, $dateTo = null) {
        try {
            $whereClause = '';
            $params = [];
            
            if ($dateFrom && $dateTo) {
                $whereClause = 'WHERE DATE(order_date) BETWEEN ? AND ?';
                $params = [$dateFrom, $dateTo];
            } elseif ($dateFrom) {
                $whereClause = 'WHERE DATE(order_date) >= ?';
                $params = [$dateFrom];
            } elseif ($dateTo) {
                $whereClause = 'WHERE DATE(order_date) <= ?';
                $params = [$dateTo];
            }
            
            $sql = "
                SELECT 
                    COUNT(*) as total_orders,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_orders,
                    SUM(CASE WHEN status = 'in_route' THEN 1 ELSE 0 END) as in_route_orders,
                    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                    COALESCE(SUM(CASE WHEN status IN ('delivered', 'confirmed') THEN final_amount ELSE 0 END), 0) as total_revenue,
                    COALESCE(AVG(CASE WHEN status IN ('delivered', 'confirmed') THEN final_amount END), 0) as average_order_value,
                    COUNT(CASE WHEN delivery_date = CURDATE() THEN 1 END) as deliveries_today,
                    COUNT(CASE WHEN delivery_date = DATE_ADD(CURDATE(), INTERVAL 1 DAY) THEN 1 END) as deliveries_tomorrow
                FROM {$this->table}
                {$whereClause}
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error getting order stats: " . $e->getMessage());
            return [
                'total_orders' => 0,
                'pending_orders' => 0,
                'confirmed_orders' => 0,
                'in_route_orders' => 0,
                'delivered_orders' => 0,
                'cancelled_orders' => 0,
                'total_revenue' => 0,
                'average_order_value' => 0,
                'deliveries_today' => 0,
                'deliveries_tomorrow' => 0
            ];
        }
    }
    
    public function getOrdersByDeliveryDate($date) {
        $sql = "
            SELECT o.*, c.business_name as customer_name, c.contact_name, c.phone
            FROM {$this->table} o
            JOIN customers c ON o.customer_id = c.id
            WHERE DATE(o.delivery_date) = ? AND o.status IN ('confirmed', 'in_route')
            ORDER BY o.created_at ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$date]);
        return $stmt->fetchAll();
    }
    
    public function searchOrders($searchTerm) {
        $sql = "
            SELECT o.*, c.business_name as customer_name
            FROM {$this->table} o
            JOIN customers c ON o.customer_id = c.id
            WHERE o.order_number LIKE ? 
            OR c.business_name LIKE ?
            OR c.contact_name LIKE ?
            ORDER BY o.created_at DESC
            LIMIT 20
        ";
        $searchPattern = '%' . $searchTerm . '%';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchPattern, $searchPattern, $searchPattern]);
        return $stmt->fetchAll();
    }
}