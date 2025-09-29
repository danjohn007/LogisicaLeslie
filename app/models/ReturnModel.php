<?php
/**
 * Modelo Return (Retorno)
 * Sistema de Logística - Quesos y Productos Leslie
 * Módulo de Control de Retornos y Calidad
 */

class ReturnModel extends Model {
    protected $table = 'returns';
    
    public function createReturn($data) {
        try {
            $this->db->beginTransaction();
            
            // Generar número de retorno
            $returnNumber = $this->generateReturnNumber();
            
            $sql = "
                INSERT INTO returns (
                    return_number, order_id, route_id, customer_id,
                    return_date, return_type, total_returned_items,
                    quality_status, approved_for_resale, processed_by,
                    notes, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $returnNumber,
                $data['order_id'] ?? null,
                $data['route_id'] ?? null,
                $data['customer_id'],
                $data['return_date'] ?? date('Y-m-d'),
                $data['return_type'], // 'no_delivery', 'quality_issue', 'customer_rejection', 'excess_inventory'
                0, // se calculará después
                $data['quality_status'] ?? 'pending_review',
                $data['approved_for_resale'] ?? 0,
                $_SESSION['user_id'],
                $data['notes'] ?? null
            ]);
            
            $returnId = $this->db->lastInsertId();
            
            // Agregar items del retorno
            if (!empty($data['returned_items'])) {
                $this->addReturnItems($returnId, $data['returned_items']);
            }
            
            $this->db->commit();
            return $returnId;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    public function addReturnItems($returnId, $items) {
        $sql = "
            INSERT INTO return_items (
                return_id, product_id, lot_id, quantity_returned,
                original_quantity, condition_status, quality_notes,
                can_resell, disposal_reason
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt = $this->db->prepare($sql);
        
        $totalItems = 0;
        foreach ($items as $item) {
            $stmt->execute([
                $returnId,
                $item['product_id'],
                $item['lot_id'] ?? null,
                $item['quantity_returned'],
                $item['original_quantity'] ?? $item['quantity_returned'],
                $item['condition_status'] ?? 'good', // 'good', 'damaged', 'expired', 'contaminated'
                $item['quality_notes'] ?? null,
                $item['can_resell'] ?? 1,
                $item['disposal_reason'] ?? null
            ]);
            
            $totalItems += $item['quantity_returned'];
            
            // Si está aprobado para reventa, devolver al inventario
            if ($item['can_resell'] ?? 1) {
                $this->returnToInventory($item['product_id'], $item['lot_id'], $item['quantity_returned']);
            } else {
                // Registrar como merma
                $this->recordWaste($item['product_id'], $item['lot_id'], $item['quantity_returned'], $item['disposal_reason'] ?? 'quality_issue');
            }
        }
        
        // Actualizar total de items en el retorno
        $updateSql = "UPDATE returns SET total_returned_items = ? WHERE id = ?";
        $updateStmt = $this->db->prepare($updateSql);
        $updateStmt->execute([$totalItems, $returnId]);
    }
    
    public function processQualityReview($returnId, $reviewData) {
        try {
            $this->db->beginTransaction();
            
            // Actualizar estado del retorno
            $sql = "
                UPDATE returns 
                SET quality_status = ?, 
                    approved_for_resale = ?,
                    quality_reviewer_id = ?,
                    quality_review_date = CURRENT_TIMESTAMP,
                    quality_notes = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $reviewData['quality_status'], // 'approved', 'rejected', 'partial'
                $reviewData['approved_for_resale'] ?? 0,
                $_SESSION['user_id'],
                $reviewData['quality_notes'] ?? null,
                $returnId
            ]);
            
            // Procesar items individuales si hay cambios
            if (!empty($reviewData['item_decisions'])) {
                foreach ($reviewData['item_decisions'] as $itemId => $decision) {
                    $this->updateReturnItemDecision($itemId, $decision);
                }
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    public function updateReturnItemDecision($returnItemId, $decision) {
        $sql = "
            UPDATE return_items 
            SET can_resell = ?,
                disposal_reason = ?,
                quality_notes = ?,
                reviewed_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $decision['can_resell'],
            $decision['disposal_reason'] ?? null,
            $decision['quality_notes'] ?? null,
            $returnItemId
        ]);
        
        // Obtener detalles del item
        $itemSql = "SELECT * FROM return_items WHERE id = ?";
        $itemStmt = $this->db->prepare($itemSql);
        $itemStmt->execute([$returnItemId]);
        $item = $itemStmt->fetch();
        
        if ($item) {
            if ($decision['can_resell']) {
                // Devolver al inventario si no se había hecho antes
                $this->returnToInventory($item['product_id'], $item['lot_id'], $item['quantity_returned']);
            } else {
                // Registrar como merma
                $this->recordWaste($item['product_id'], $item['lot_id'], $item['quantity_returned'], $decision['disposal_reason']);
            }
        }
    }
    
    public function getAllReturns($filters = []) {
        $where = ["1=1"];
        $params = [];
        
        if (!empty($filters['date_from'])) {
            $where[] = "r.return_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "r.return_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['return_type'])) {
            $where[] = "r.return_type = ?";
            $params[] = $filters['return_type'];
        }
        
        if (!empty($filters['quality_status'])) {
            $where[] = "r.quality_status = ?";
            $params[] = $filters['quality_status'];
        }
        
        $sql = "
            SELECT 
                r.*,
                c.business_name as customer_name,
                c.contact_name,
                o.order_number,
                CONCAT(u.first_name, ' ', u.last_name) as processed_by_name,
                CONCAT(qr.first_name, ' ', qr.last_name) as reviewer_name
            FROM returns r
            LEFT JOIN customers c ON r.customer_id = c.id
            LEFT JOIN orders o ON r.order_id = o.id
            LEFT JOIN users u ON r.processed_by = u.id
            LEFT JOIN users qr ON r.quality_reviewer_id = qr.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY r.return_date DESC, r.created_at DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getReturnWithDetails($returnId) {
        $sql = "
            SELECT 
                r.*,
                c.business_name as customer_name,
                c.contact_name,
                c.phone as customer_phone,
                c.address as customer_address,
                o.order_number,
                o.delivery_date,
                CONCAT(u.first_name, ' ', u.last_name) as processed_by_name,
                CONCAT(qr.first_name, ' ', qr.last_name) as reviewer_name
            FROM returns r
            LEFT JOIN customers c ON r.customer_id = c.id
            LEFT JOIN orders o ON r.order_id = o.id
            LEFT JOIN users u ON r.processed_by = u.id
            LEFT JOIN users qr ON r.quality_reviewer_id = qr.id
            WHERE r.id = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$returnId]);
        return $stmt->fetch();
    }
    
    public function getReturnItems($returnId) {
        $sql = "
            SELECT 
                ri.*,
                p.code as product_code,
                p.name as product_name,
                p.unit_type,
                pl.lot_number,
                pl.production_date,
                pl.expiry_date
            FROM return_items ri
            JOIN products p ON ri.product_id = p.id
            LEFT JOIN production_lots pl ON ri.lot_id = pl.id
            WHERE ri.return_id = ?
            ORDER BY p.name
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$returnId]);
        return $stmt->fetchAll();
    }
    
    public function getReturnStats($dateFrom = null, $dateTo = null) {
        $dateFrom = $dateFrom ?: date('Y-m-01');
        $dateTo = $dateTo ?: date('Y-m-d');
        
        $stats = [];
        
        // Total de retornos
        $sql = "
            SELECT 
                COUNT(*) as total_returns,
                SUM(total_returned_items) as total_items,
                COUNT(CASE WHEN quality_status = 'approved' THEN 1 END) as approved_returns,
                COUNT(CASE WHEN quality_status = 'rejected' THEN 1 END) as rejected_returns,
                COUNT(CASE WHEN quality_status = 'pending_review' THEN 1 END) as pending_reviews
            FROM returns 
            WHERE return_date BETWEEN ? AND ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dateFrom, $dateTo]);
        $stats = $stmt->fetch();
        
        // Retornos por tipo
        $sql = "
            SELECT 
                return_type,
                COUNT(*) as count,
                SUM(total_returned_items) as total_items
            FROM returns 
            WHERE return_date BETWEEN ? AND ?
            GROUP BY return_type
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dateFrom, $dateTo]);
        $stats['by_type'] = $stmt->fetchAll();
        
        // Productos más devueltos
        $sql = "
            SELECT 
                p.name as product_name,
                SUM(ri.quantity_returned) as total_returned,
                COUNT(DISTINCT ri.return_id) as return_count
            FROM return_items ri
            JOIN products p ON ri.product_id = p.id
            JOIN returns r ON ri.return_id = r.id
            WHERE r.return_date BETWEEN ? AND ?
            GROUP BY ri.product_id
            ORDER BY total_returned DESC
            LIMIT 10
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dateFrom, $dateTo]);
        $stats['top_returned_products'] = $stmt->fetchAll();
        
        return $stats;
    }
    
    private function returnToInventory($productId, $lotId, $quantity) {
        if ($lotId) {
            // Devolver al lote específico
            $sql = "
                UPDATE inventory 
                SET quantity = quantity + ? 
                WHERE product_id = ? AND lot_id = ?
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$quantity, $productId, $lotId]);
        } else {
            // Crear nueva entrada de inventario general
            $sql = "
                INSERT INTO inventory (product_id, quantity, location, movement_type, notes, created_at)
                VALUES (?, ?, 'returned', 'return', 'Producto devuelto por retorno', CURRENT_TIMESTAMP)
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$productId, $quantity]);
        }
        
        // Registrar movimiento de inventario
        $this->recordInventoryMovement($productId, $lotId, $quantity, 'return', 'Devolución por retorno');
    }
    
    private function recordWaste($productId, $lotId, $quantity, $reason) {
        $sql = "
            INSERT INTO waste_records (
                product_id, lot_id, quantity, waste_reason,
                recorded_by, recorded_at
            ) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId, $lotId, $quantity, $reason, $_SESSION['user_id']]);
        
        // Registrar movimiento de inventario
        $this->recordInventoryMovement($productId, $lotId, $quantity, 'waste', "Merma: $reason");
    }
    
    private function recordInventoryMovement($productId, $lotId, $quantity, $type, $notes) {
        $sql = "
            INSERT INTO inventory_movements (
                product_id, lot_id, movement_type, quantity,
                notes, created_by, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId, $lotId, $type, $quantity, $notes, $_SESSION['user_id']]);
    }
    
    private function generateReturnNumber() {
        $prefix = 'RET' . date('Y');
        $sql = "SELECT COUNT(*) + 1 as next_number FROM returns WHERE return_number LIKE ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prefix . '%']);
        $result = $stmt->fetch();
        $nextNumber = str_pad($result['next_number'], 4, '0', STR_PAD_LEFT);
        return $prefix . $nextNumber;
    }
}