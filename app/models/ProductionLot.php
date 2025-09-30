<?php
/**
 * Modelo ProductionLot
 * Sistema de Logística - Quesos y Productos Leslie
 */

class ProductionLot extends Model {
    protected $table = 'production_lots';
    
    public function getAllWithProducts($limit = 20) {
        try {
            $sql = "
                SELECT pl.*, p.name as product_name, p.code as product_code,
                       u.first_name, u.last_name,
                       pl.batch_code as lot_number,
                       pl.expiration_date as expiry_date,
                       CASE 
                           WHEN pl.quality_status = 'good' THEN 'terminado'
                           WHEN pl.quality_status = 'warning' THEN 'en_produccion'
                           WHEN pl.quality_status = 'expired' THEN 'vendido'
                           WHEN pl.quality_status = 'damaged' THEN 'vendido'
                           ELSE 'terminado'
                       END as status
                FROM {$this->table} pl
                JOIN products p ON pl.product_id = p.id
                LEFT JOIN users u ON pl.created_by = u.id
                ORDER BY pl.created_at DESC
                LIMIT ?
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting production lots: " . $e->getMessage());
            return [];
        }
    }
    
    public function findByLotNumber($lotNumber) {
        $sql = "SELECT * FROM {$this->table} WHERE batch_code = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$lotNumber]);
        return $stmt->fetch();
    }
    
    public function findByProduct($productId) {
        return $this->findAll(['product_id' => $productId]);
    }
    
    public function getExpiringLots($days = 7) {
        try {
            $sql = "
                SELECT pl.*, p.name as product_name, p.code as product_code
                FROM {$this->table} pl
                JOIN products p ON pl.product_id = p.id
                WHERE pl.expiration_date IS NOT NULL 
                AND pl.expiration_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
                AND pl.quantity_available > 0
                ORDER BY pl.expiration_date ASC
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$days]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting expiring lots: " . $e->getMessage());
            return [];
        }
    }
    
    public function getExpiredLots() {
        try {
            $sql = "
                SELECT pl.*, p.name as product_name, p.code as product_code
                FROM {$this->table} pl
                JOIN products p ON pl.product_id = p.id
                WHERE pl.expiry_date IS NOT NULL 
                AND pl.expiry_date < CURDATE()
                AND pl.quantity_available > 0
                ORDER BY pl.expiry_date ASC
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting expired lots: " . $e->getMessage());
            return [];
        }
    }
    
    public function getLotsByDateRange($startDate, $endDate) {
        try {
            $sql = "
                SELECT pl.*, p.name as product_name, p.code as product_code
                FROM {$this->table} pl
                JOIN products p ON pl.product_id = p.id
                WHERE pl.production_date BETWEEN ? AND ?
                ORDER BY pl.production_date DESC
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting lots by date range: " . $e->getMessage());
            return [];
        }
    }
    
    public function updateQuantityAvailable($lotId, $quantityUsed) {
        try {
            $sql = "
                UPDATE {$this->table} 
                SET quantity_available = quantity_available - ? 
                WHERE id = ? AND quantity_available >= ?
            ";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$quantityUsed, $lotId, $quantityUsed]);
        } catch (Exception $e) {
            error_log("Error updating lot quantity: " . $e->getMessage());
            return false;
        }
    }
    
    public function getProductionStats($productId = null, $dateFrom = null, $dateTo = null) {
        try {
            $conditions = [];
            $params = [];
            
            if ($productId) {
                $conditions[] = "pl.product_id = ?";
                $params[] = $productId;
            }
            
            if ($dateFrom) {
                $conditions[] = "pl.production_date >= ?";
                $params[] = $dateFrom;
            }
            
            if ($dateTo) {
                $conditions[] = "pl.production_date <= ?";
                $params[] = $dateTo;
            }
            
            $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
            
            $sql = "
                SELECT 
                    COUNT(*) as total_lots,
                    SUM(pl.quantity_produced) as total_produced,
                    SUM(pl.quantity_available) as total_available,
                    AVG(pl.quantity_produced) as avg_quantity,
                    p.name as product_name,
                    p.code as product_code
                FROM {$this->table} pl
                JOIN products p ON pl.product_id = p.id
                {$whereClause}
                GROUP BY pl.product_id
                ORDER BY total_produced DESC
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting production stats: " . $e->getMessage());
            return [];
        }
    }
    
    public function generateLotNumber($productId) {
        try {
            // Obtener información del producto
            $sql = "SELECT code FROM products WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            
            if (!$product) {
                return 'LOT' . date('Ymd') . '001';
            }
            
            // Generar número basado en código de producto y fecha
            $prefix = substr($product['code'], -3) . date('md');
            
            // Buscar el último número de lote con este prefijo
            $sql = "SELECT lot_number FROM {$this->table} WHERE lot_number LIKE ? ORDER BY lot_number DESC LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$prefix . '%']);
            $lastLot = $stmt->fetch();
            
            if ($lastLot) {
                $lastNum = intval(substr($lastLot['lot_number'], -3));
                $newNum = $lastNum + 1;
            } else {
                $newNum = 1;
            }
            
            return $prefix . str_pad($newNum, 3, '0', STR_PAD_LEFT);
        } catch (Exception $e) {
            error_log("Error generating lot number: " . $e->getMessage());
            return 'LOT' . date('Ymd') . rand(100, 999);
        }
    }
    
    public function getLotDetails($lotId) {
        try {
            $sql = "
                SELECT pl.*, p.name as product_name, p.code as product_code,
                       p.unit_type, p.price_per_unit,
                       c.name as category_name,
                       u.first_name, u.last_name,
                       i.quantity as inventory_quantity,
                       i.location as inventory_location
                FROM {$this->table} pl
                JOIN products p ON pl.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN users u ON pl.created_by = u.id
                LEFT JOIN inventory i ON pl.id = i.lot_id
                WHERE pl.id = ?
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$lotId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error getting lot details: " . $e->getMessage());
            return null;
        }
    }
    
    public function create($data) {
        try {
            // Validar que no exista el número de lote
            if ($this->findByLotNumber($data['lot_number'])) {
                throw new Exception('El número de lote ya existe');
            }
            
            // Inicializar transacción
            $this->beginTransaction();
            
            // Agregar datos adicionales
            $data['created_by'] = $_SESSION['user_id'] ?? 1;
            $data['quantity_available'] = $data['quantity_produced'];
            
            // Crear el lote
            $result = parent::create($data);
            
            if ($result) {
                $lotId = $this->db->lastInsertId();
                
                // Actualizar inventario
                $this->updateInventoryForNewLot($data['product_id'], $data['quantity_produced'], $lotId);
                
                // Registrar movimiento de inventario
                $this->recordInventoryMovement(
                    'production',
                    $data['product_id'],
                    $lotId,
                    $data['quantity_produced'],
                    'Producción de nuevo lote: ' . $data['lot_number']
                );
                
                $this->commit();
                return true;
            }
            
            $this->rollback();
            return false;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    private function updateInventoryForNewLot($productId, $quantity, $lotId) {
        try {
            $sql = "
                INSERT INTO inventory (product_id, lot_id, quantity, location)
                VALUES (?, ?, ?, 'Almacén Principal')
            ";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$productId, $lotId, $quantity]);
        } catch (Exception $e) {
            error_log("Error updating inventory for new lot: " . $e->getMessage());
            return false;
        }
    }
    
    private function recordInventoryMovement($type, $productId, $lotId, $quantity, $notes = '') {
        try {
            $sql = "
                INSERT INTO inventory_movements 
                (type, product_id, lot_id, quantity, notes, created_by)
                VALUES (?, ?, ?, ?, ?, ?)
            ";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $type,
                $productId,
                $lotId,
                $quantity,
                $notes,
                $_SESSION['user_id'] ?? 1
            ]);
        } catch (Exception $e) {
            error_log("Error recording inventory movement: " . $e->getMessage());
            return false;
        }
    }
    
    public function update($id, $data) {
        // Si se actualiza la cantidad producida, actualizar también la disponible
        if (isset($data['quantity_produced'])) {
            $currentLot = $this->findById($id);
            if ($currentLot) {
                $difference = $data['quantity_produced'] - $currentLot['quantity_produced'];
                $data['quantity_available'] = $currentLot['quantity_available'] + $difference;
            }
        }
        
        return parent::update($id, $data);
    }
}
?>