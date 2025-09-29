<?php
/**
 * Modelo Notification
 * Sistema de Logística - Quesos y Productos Leslie
 * Sistema de Notificaciones Automáticas
 */

class Notification extends Model {
    protected $table = 'notifications';
    
    public function createNotification($data) {
        $sql = "
            INSERT INTO notifications (
                type, title, message, user_id, entity_type, 
                entity_id, priority, read_status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 0, CURRENT_TIMESTAMP)
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['type'],
            $data['title'],
            $data['message'],
            $data['user_id'] ?? null,
            $data['entity_type'] ?? null,
            $data['entity_id'] ?? null,
            $data['priority'] ?? 'medium'
        ]);
        
        return $this->db->lastInsertId();
    }
    
    public function checkExpiryAlerts() {
        // Productos que expiran en 7 días
        $sql = "
            SELECT 
                pl.id,
                pl.lot_number,
                pl.expiry_date,
                pl.quantity_available,
                p.name as product_name,
                p.code as product_code,
                DATEDIFF(pl.expiry_date, CURDATE()) as days_to_expiry
            FROM production_lots pl
            JOIN products p ON pl.product_id = p.id
            WHERE pl.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            AND pl.expiry_date >= CURDATE()
            AND pl.quantity_available > 0
            AND NOT EXISTS (
                SELECT 1 FROM notifications n 
                WHERE n.entity_type = 'production_lot' 
                AND n.entity_id = pl.id 
                AND n.type = 'expiry_alert'
                AND DATE(n.created_at) = CURDATE()
            )
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $expiringLots = $stmt->fetchAll();
        
        foreach ($expiringLots as $lot) {
            $priority = $lot['days_to_expiry'] <= 2 ? 'high' : 'medium';
            $message = "El lote {$lot['lot_number']} del producto {$lot['product_name']} expira en {$lot['days_to_expiry']} días. Cantidad disponible: {$lot['quantity_available']}";
            
            $this->createNotification([
                'type' => 'expiry_alert',
                'title' => 'Producto próximo a vencer',
                'message' => $message,
                'entity_type' => 'production_lot',
                'entity_id' => $lot['id'],
                'priority' => $priority
            ]);
            
            // Notificar a usuarios específicos (administradores y warehouse)
            $this->notifyUsersByRole(['admin', 'warehouse'], [
                'type' => 'expiry_alert',
                'title' => 'Producto próximo a vencer',
                'message' => $message,
                'entity_type' => 'production_lot',
                'entity_id' => $lot['id'],
                'priority' => $priority
            ]);
        }
        
        return count($expiringLots);
    }
    
    public function checkLowStockAlerts() {
        $sql = "
            SELECT 
                p.id,
                p.name as product_name,
                p.code as product_code,
                p.minimum_stock,
                COALESCE(SUM(i.quantity), 0) as current_stock
            FROM products p
            LEFT JOIN inventory i ON p.id = i.product_id
            WHERE p.is_active = 1
            GROUP BY p.id
            HAVING current_stock <= p.minimum_stock
            AND NOT EXISTS (
                SELECT 1 FROM notifications n 
                WHERE n.entity_type = 'product' 
                AND n.entity_id = p.id 
                AND n.type = 'low_stock_alert'
                AND DATE(n.created_at) = CURDATE()
            )
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $lowStockProducts = $stmt->fetchAll();
        
        foreach ($lowStockProducts as $product) {
            $priority = $product['current_stock'] == 0 ? 'high' : 'medium';
            $message = "Stock bajo para {$product['product_name']} ({$product['product_code']}). Stock actual: {$product['current_stock']}, Mínimo: {$product['minimum_stock']}";
            
            $this->notifyUsersByRole(['admin', 'warehouse', 'manager'], [
                'type' => 'low_stock_alert',
                'title' => 'Stock bajo',
                'message' => $message,
                'entity_type' => 'product',
                'entity_id' => $product['id'],
                'priority' => $priority
            ]);
        }
        
        return count($lowStockProducts);
    }
    
    public function checkPendingOrders() {
        $sql = "
            SELECT 
                o.id,
                o.order_number,
                o.delivery_date,
                o.total_amount,
                c.business_name as customer_name,
                DATEDIFF(o.delivery_date, CURDATE()) as days_to_delivery
            FROM orders o
            JOIN customers c ON o.customer_id = c.id
            WHERE o.status IN ('pendiente', 'confirmado')
            AND o.delivery_date <= DATE_ADD(CURDATE(), INTERVAL 1 DAY)
            AND NOT EXISTS (
                SELECT 1 FROM notifications n 
                WHERE n.entity_type = 'order' 
                AND n.entity_id = o.id 
                AND n.type = 'pending_order_alert'
                AND DATE(n.created_at) = CURDATE()
            )
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $pendingOrders = $stmt->fetchAll();
        
        foreach ($pendingOrders as $order) {
            $priority = $order['days_to_delivery'] <= 0 ? 'high' : 'medium';
            $status = $order['days_to_delivery'] <= 0 ? 'vencido' : 'próximo a vencer';
            $message = "Pedido {$order['order_number']} de {$order['customer_name']} está {$status}. Fecha de entrega: {$order['delivery_date']}";
            
            $this->notifyUsersByRole(['admin', 'manager', 'seller'], [
                'type' => 'pending_order_alert',
                'title' => 'Pedido pendiente',
                'message' => $message,
                'entity_type' => 'order',
                'entity_id' => $order['id'],
                'priority' => $priority
            ]);
        }
        
        return count($pendingOrders);
    }
    
    public function checkFailedDeliveries() {
        $sql = "
            SELECT 
                ro.id,
                o.order_number,
                c.business_name as customer_name,
                r.route_name,
                ro.delivery_notes
            FROM route_orders ro
            JOIN orders o ON ro.order_id = o.id
            JOIN customers c ON o.customer_id = c.id
            JOIN routes r ON ro.route_id = r.id
            WHERE ro.delivery_status = 'failed'
            AND DATE(ro.actual_arrival) = CURDATE()
            AND NOT EXISTS (
                SELECT 1 FROM notifications n 
                WHERE n.entity_type = 'route_order' 
                AND n.entity_id = ro.id 
                AND n.type = 'failed_delivery_alert'
                AND DATE(n.created_at) = CURDATE()
            )
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $failedDeliveries = $stmt->fetchAll();
        
        foreach ($failedDeliveries as $delivery) {
            $message = "Entrega fallida: Pedido {$delivery['order_number']} para {$delivery['customer_name']} en ruta {$delivery['route_name']}";
            
            $this->notifyUsersByRole(['admin', 'manager'], [
                'type' => 'failed_delivery_alert',
                'title' => 'Entrega fallida',
                'message' => $message,
                'entity_type' => 'route_order',
                'entity_id' => $delivery['id'],
                'priority' => 'high'
            ]);
        }
        
        return count($failedDeliveries);
    }
    
    public function notifyUsersByRole($roles, $notificationData) {
        $rolesList = "'" . implode("','", $roles) . "'";
        $sql = "SELECT id FROM users WHERE user_role IN ($rolesList) AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        foreach ($users as $user) {
            $notificationData['user_id'] = $user['id'];
            $this->createNotification($notificationData);
        }
    }
    
    public function getUserNotifications($userId, $limit = 20, $unreadOnly = false) {
        $where = "user_id = ?";
        $params = [$userId];
        
        if ($unreadOnly) {
            $where .= " AND read_status = 0";
        }
        
        $sql = "
            SELECT * FROM notifications 
            WHERE $where 
            ORDER BY created_at DESC 
            LIMIT ?
        ";
        $params[] = $limit;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function markAsRead($notificationId, $userId = null) {
        $where = "id = ?";
        $params = [$notificationId];
        
        if ($userId) {
            $where .= " AND user_id = ?";
            $params[] = $userId;
        }
        
        $sql = "UPDATE notifications SET read_status = 1, read_at = CURRENT_TIMESTAMP WHERE $where";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function markAllAsRead($userId) {
        $sql = "UPDATE notifications SET read_status = 1, read_at = CURRENT_TIMESTAMP WHERE user_id = ? AND read_status = 0";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId]);
    }
    
    public function getUnreadCount($userId) {
        $sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND read_status = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    public function runDailyAlerts() {
        $results = [
            'expiry_alerts' => $this->checkExpiryAlerts(),
            'low_stock_alerts' => $this->checkLowStockAlerts(),
            'pending_orders' => $this->checkPendingOrders(),
            'failed_deliveries' => $this->checkFailedDeliveries()
        ];
        
        // Log de ejecución
        error_log("Daily alerts executed: " . json_encode($results));
        
        return $results;
    }
    
    public function sendWhatsAppNotification($phoneNumber, $message) {
        // Integración con API de WhatsApp (placeholder)
        // Aquí se implementaría la integración con WhatsApp Business API
        
        try {
            // Ejemplo de estructura para API de WhatsApp
            $data = [
                'phone' => $phoneNumber,
                'message' => $message,
                'timestamp' => time()
            ];
            
            // Simular envío
            error_log("WhatsApp notification sent to $phoneNumber: $message");
            
            return true;
        } catch (Exception $e) {
            error_log("Failed to send WhatsApp notification: " . $e->getMessage());
            return false;
        }
    }
    
    public function sendEmailNotification($email, $subject, $message) {
        // Envío de notificaciones por email
        try {
            // Usar mail() o una librería como PHPMailer
            $headers = "From: sistema@quesosleslie.com\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            
            return mail($email, $subject, $message, $headers);
        } catch (Exception $e) {
            error_log("Failed to send email notification: " . $e->getMessage());
            return false;
        }
    }
}