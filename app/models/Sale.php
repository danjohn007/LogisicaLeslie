<?php
/**
 * Modelo Sale (Venta Directa)
 * Sistema de Logística - Quesos y Productos Leslie
 */

class Sale extends Model {
    protected $table = 'direct_sales';
    
    /**
     * Crear una nueva venta directa con sus detalles
     */
    public function createSaleWithDetails($saleData, $saleDetails) {
        try {
            $this->db->beginTransaction();
            
            // Generar número de venta único
            $saleNumber = $this->generateSaleNumber();
            
            // Preparar datos de la venta
            $saleData['sale_number'] = $saleNumber;
            $saleData['sale_date'] = date('Y-m-d');
            $saleData['seller_id'] = $_SESSION['user_id'];
            
            // Calcular totales
            $totalAmount = 0;
            foreach ($saleDetails as $detail) {
                $totalAmount += $detail['quantity'] * $detail['unit_price'];
            }
            $saleData['total_amount'] = $totalAmount;
            $saleData['final_amount'] = $totalAmount - ($saleData['discount_amount'] ?? 0);
            
            // Crear la venta principal
            $saleId = $this->create($saleData);
            
            if (!$saleId) {
                throw new Exception('Error al crear la venta');
            }
            
            // Agregar detalles de la venta
            foreach ($saleDetails as $detail) {
                $detailData = [
                    'sale_id' => $saleId,
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'unit_price' => $detail['unit_price'],
                    'subtotal' => $detail['quantity'] * $detail['unit_price']
                ];
                
                $this->createSaleDetail($detailData);
                
                // Reducir inventario usando FIFO
                $this->reduceInventoryFIFO($detail['product_id'], $detail['quantity']);
            }
            
            $this->db->commit();
            return $saleId;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception('Error al crear la venta: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtener ventas con detalles, filtros y paginación
     */
    public function getAllSalesWithDetails($limit = 50, $filters = []) {
        $sql = "
            SELECT 
                ds.*,
                c.business_name as customer_name,
                c.contact_name as customer_contact,
                c.phone as customer_phone,
                u.first_name as seller_name,
                u.last_name as seller_lastname,
                COUNT(dsd.id) as total_items
            FROM direct_sales ds
            LEFT JOIN customers c ON ds.customer_id = c.id
            JOIN users u ON ds.seller_id = u.id
            LEFT JOIN direct_sale_details dsd ON ds.id = dsd.sale_id
        ";
        
        $whereConditions = [];
        $params = [];
        
        // Aplicar filtros
        if (!empty($filters['payment_method'])) {
            $whereConditions[] = "ds.payment_method = ?";
            $params[] = $filters['payment_method'];
        }
        
        if (!empty($filters['seller_id'])) {
            $whereConditions[] = "ds.seller_id = ?";
            $params[] = $filters['seller_id'];
        }
        
        if (!empty($filters['customer_id'])) {
            $whereConditions[] = "ds.customer_id = ?";
            $params[] = $filters['customer_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereConditions[] = "ds.sale_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereConditions[] = "ds.sale_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $whereConditions);
        }
        
        $sql .= " GROUP BY ds.id ORDER BY ds.created_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener una venta con todos sus detalles
     */
    public function getSaleWithDetails($saleId) {
        // Obtener datos principales de la venta
        $sql = "
            SELECT 
                ds.*,
                c.business_name as customer_name,
                c.contact_name as customer_contact,
                c.phone as customer_phone,
                c.address as customer_address,
                u.first_name as seller_name,
                u.last_name as seller_lastname
            FROM direct_sales ds
            LEFT JOIN customers c ON ds.customer_id = c.id
            JOIN users u ON ds.seller_id = u.id
            WHERE ds.id = ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$saleId]);
        $sale = $stmt->fetch();
        
        if (!$sale) {
            return null;
        }
        
        // Obtener detalles de productos
        $sale['details'] = $this->getSaleDetails($saleId);
        
        return $sale;
    }
    
    /**
     * Obtener detalles de productos de una venta
     */
    public function getSaleDetails($saleId) {
        $sql = "
            SELECT 
                dsd.*,
                p.name as product_name,
                p.code as product_code,
                p.unit as product_unit
            FROM direct_sale_details dsd
            JOIN products p ON dsd.product_id = p.id
            WHERE dsd.sale_id = ?
            ORDER BY dsd.id
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$saleId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener estadísticas de ventas
     */
    public function getSalesStats() {
        $stats = [];
        
        // Ventas de hoy
        $sql = "
            SELECT 
                COALESCE(COUNT(*), 0) as count,
                COALESCE(SUM(final_amount), 0) as total
            FROM direct_sales 
            WHERE DATE(sale_date) = CURDATE()
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        $stats['today_sales'] = $result['count'];
        $stats['today_revenue'] = $result['total'];
        
        // Ventas del mes
        $sql = "
            SELECT 
                COALESCE(COUNT(*), 0) as count,
                COALESCE(SUM(final_amount), 0) as total
            FROM direct_sales 
            WHERE MONTH(sale_date) = MONTH(CURDATE()) 
            AND YEAR(sale_date) = YEAR(CURDATE())
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        $stats['month_sales'] = $result['count'];
        $stats['month_revenue'] = $result['total'];
        
        // Total general
        $sql = "
            SELECT 
                COALESCE(COUNT(*), 0) as count,
                COALESCE(SUM(final_amount), 0) as total
            FROM direct_sales
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        $stats['total_sales'] = $result['count'];
        $stats['total_revenue'] = $result['total'];
        
        // Producto más vendido
        $sql = "
            SELECT 
                p.name as product_name,
                SUM(dsd.quantity) as total_quantity
            FROM direct_sale_details dsd
            JOIN products p ON dsd.product_id = p.id
            JOIN direct_sales ds ON dsd.sale_id = ds.id
            WHERE MONTH(ds.sale_date) = MONTH(CURDATE()) 
            AND YEAR(ds.sale_date) = YEAR(CURDATE())
            GROUP BY dsd.product_id
            ORDER BY total_quantity DESC
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        $stats['top_product'] = $result ? $result['product_name'] : 'N/A';
        $stats['top_product_quantity'] = $result ? $result['total_quantity'] : 0;
        
        return $stats;
    }
    
    public function cancelSale($saleId) {
        try {
            $this->db->beginTransaction();
            
            // Obtener detalles de la venta
            $sale = $this->getSaleWithDetails($saleId);
            if (!$sale) {
                throw new Exception("Venta no encontrada");
            }
            
            // Verificar que la venta sea del día actual
            if (date('Y-m-d', strtotime($sale['sale_date'])) !== date('Y-m-d')) {
                throw new Exception("Solo se pueden cancelar ventas del día actual");
            }
            
            // Obtener productos de la venta
            $saleDetails = $this->getSaleDetails($saleId);
            
            // Devolver inventario usando FIFO inverso
            foreach ($saleDetails as $detail) {
                $this->returnInventoryFIFO($detail['product_id'], $detail['quantity']);
            }
            
            // Marcar venta como cancelada
            $sql = "UPDATE direct_sales SET 
                    status = 'cancelled',
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$saleId]);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    private function returnInventoryFIFO($productId, $quantity) {
        // Obtener lotes de inventario ordenados por fecha de vencimiento (FIFO)
        $sql = "
            SELECT i.id, i.quantity, i.lot_id, pl.expiry_date
            FROM inventory i
            LEFT JOIN production_lots pl ON i.lot_id = pl.id
            WHERE i.product_id = ?
            ORDER BY 
                CASE WHEN pl.expiry_date IS NULL THEN 1 ELSE 0 END,
                pl.expiry_date ASC,
                i.created_at ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId]);
        $inventoryItems = $stmt->fetchAll();
        
        $remainingToReturn = $quantity;
        
        foreach ($inventoryItems as $item) {
            if ($remainingToReturn <= 0) break;
            
            $toReturn = min($remainingToReturn, $quantity); // Devolver hasta lo que se puede
            
            $updateSql = "UPDATE inventory SET quantity = quantity + ? WHERE id = ?";
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->execute([$toReturn, $item['id']]);
            
            $remainingToReturn -= $toReturn;
        }
        
        // Si aún queda cantidad por devolver, crear nueva entrada de inventario
        if ($remainingToReturn > 0) {
            $insertSql = "
                INSERT INTO inventory (product_id, quantity, reserved_quantity, location, created_at, last_updated)
                VALUES (?, ?, 0, 'DEVOLUCION', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
            ";
            $insertStmt = $this->db->prepare($insertSql);
            $insertStmt->execute([$productId, $remainingToReturn]);
        }
    }
    
    /**
     * Generar número único de venta
     */
    public function generateSaleNumber() {
        $prefix = 'VTA' . date('Y');
        
        $sql = "SELECT COUNT(*) + 1 as next_number FROM direct_sales WHERE sale_number LIKE ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prefix . '%']);
        $result = $stmt->fetch();
        
        $nextNumber = str_pad($result['next_number'], 4, '0', STR_PAD_LEFT);
        return $prefix . $nextNumber;
    }
    
    /**
     * Crear detalle de venta
     */
    private function createSaleDetail($detailData) {
        $sql = "
            INSERT INTO direct_sale_details (sale_id, product_id, quantity, unit_price, subtotal)
            VALUES (?, ?, ?, ?, ?)
        ";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $detailData['sale_id'],
            $detailData['product_id'],
            $detailData['quantity'],
            $detailData['unit_price'],
            $detailData['subtotal']
        ]);
    }
    
    /**
     * Reducir inventario usando FIFO (First In, First Out)
     */
    private function reduceInventoryFIFO($productId, $quantityToReduce) {
        // Obtener lotes disponibles ordenados por fecha de vencimiento (FIFO)
        $sql = "
            SELECT id, available_quantity, lot_number
            FROM inventory 
            WHERE product_id = ? AND available_quantity > 0
            ORDER BY expiry_date ASC, created_at ASC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId]);
        $inventoryLots = $stmt->fetchAll();
        
        if (empty($inventoryLots)) {
            throw new Exception("No hay stock disponible para este producto");
        }
        
        $remainingToReduce = $quantityToReduce;
        $totalAvailable = array_sum(array_column($inventoryLots, 'available_quantity'));
        
        if ($totalAvailable < $quantityToReduce) {
            throw new Exception("Stock insuficiente. Disponible: {$totalAvailable}, Requerido: {$quantityToReduce}");
        }
        
        foreach ($inventoryLots as $lot) {
            if ($remainingToReduce <= 0) break;
            
            $toReduce = min($lot['available_quantity'], $remainingToReduce);
            
            // Actualizar inventario
            $updateSql = "UPDATE inventory SET available_quantity = available_quantity - ? WHERE id = ?";
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->execute([$toReduce, $lot['id']]);
            
            // Registrar movimiento de inventario
            $this->recordInventoryMovement($lot['id'], 'sale', $toReduce, "Venta directa");
            
            $remainingToReduce -= $toReduce;
        }
    }
    
    /**
     * Registrar movimiento de inventario
     */
    private function recordInventoryMovement($inventoryId, $movementType, $quantity, $notes = '') {
        $sql = "
            INSERT INTO inventory_movements (inventory_id, movement_type, quantity, notes, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $inventoryId,
            $movementType,
            $quantity,
            $notes,
            $_SESSION['user_id']
        ]);
    }
    
    /**
     * Obtener ventas por vendedor
     */
    public function getSalesBySeller($sellerId, $dateFrom = null, $dateTo = null) {
        $sql = "
            SELECT 
                ds.*,
                c.business_name as customer_name
            FROM direct_sales ds
            LEFT JOIN customers c ON ds.customer_id = c.id
            WHERE ds.seller_id = ?
        ";
        
        $params = [$sellerId];
        
        if ($dateFrom) {
            $sql .= " AND ds.sale_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND ds.sale_date <= ?";
            $params[] = $dateTo;
        }
        
        $sql .= " ORDER BY ds.sale_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener reporte de ventas por período
     */
    public function getSalesReport($dateFrom, $dateTo, $groupBy = 'day') {
        $dateFormat = match($groupBy) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => '%Y-%m-%d'
        };
        
        $sql = "
            SELECT 
                DATE_FORMAT(sale_date, ?) as period,
                COUNT(*) as total_sales,
                SUM(final_amount) as total_revenue,
                AVG(final_amount) as average_sale
            FROM direct_sales
            WHERE sale_date BETWEEN ? AND ?
            GROUP BY period
            ORDER BY period DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dateFormat, $dateFrom, $dateTo]);
        return $stmt->fetchAll();
    }
}