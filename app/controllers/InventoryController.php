<?php
/**
 * Controlador de Inventario
 * Sistema de Logística - Quesos y Productos Leslie
 */

require_once dirname(__DIR__) . '/models/Product.php';
require_once dirname(__DIR__) . '/models/Inventory.php';
require_once dirname(__DIR__) . '/models/ProductionLot.php';

class InventoryController extends Controller {
    private $productModel;
    private $inventoryModel;
    private $productionLotModel;
    
    public function __construct() {
        parent::__construct();
        $this->productModel = new Product();
        $this->inventoryModel = new Inventory();
        $this->productionLotModel = new ProductionLot();
        $this->requireAuth();
    }
    
    public function index() {
        if (!$this->hasPermission('production') && !$this->hasPermission('warehouse')) {
            $this->redirect('dashboard');
            return;
        }
        
        $data = [
            'title' => 'Control de Inventario - ' . APP_NAME,
            'view_mode' => $_GET['view'] ?? 'summary',
            'inventory_summary' => $this->inventoryModel->getInventorySummary(),
            'inventory_details' => $this->inventoryModel->getInventoryWithDetails(),
            'expiring_products' => $this->inventoryModel->getExpiringProducts(30),
            'inventory_stats' => $this->inventoryModel->getInventoryStats(),
            'recent_movements' => $this->inventoryModel->getMovementHistory(null, 10),
            'user_name' => $_SESSION['full_name'] ?? $_SESSION['username'],
            'user_role' => $_SESSION['user_role'] ?? 'guest'
        ];
        
        $this->view('inventory/index', $data);
    }
    
    public function movement() {
        if (!$this->hasPermission('production') && !$this->hasPermission('warehouse')) {
            $this->redirect('dashboard');
            return;
        }
        
        $data = [
            'title' => 'Movimiento de Inventario - ' . APP_NAME,
            'products' => $this->productModel->findAll(['is_active' => 1]),
            'success' => null,
            'error' => null
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $movementData = [
                'product_id' => intval($_POST['product_id'] ?? 0),
                'movement_type' => $_POST['movement_type'] ?? 'entrada',
                'quantity' => intval($_POST['quantity'] ?? 0),
                'reason' => trim($_POST['reason'] ?? ''),
                'location' => trim($_POST['location'] ?? 'Almacén Principal'),
                'lot_number' => trim($_POST['lot_number'] ?? ''),
                'notes' => trim($_POST['notes'] ?? '')
            ];
            
            if ($movementData['product_id'] <= 0 || $movementData['quantity'] <= 0) {
                $data['error'] = 'Por favor seleccione un producto y especifique una cantidad válida.';
            } else {
                try {
                    if ($this->processInventoryMovement($movementData)) {
                        $data['success'] = 'Movimiento de inventario registrado exitosamente.';
                        unset($_POST);
                    } else {
                        $data['error'] = 'Error al registrar el movimiento de inventario.';
                    }
                } catch (Exception $e) {
                    $data['error'] = 'Error: ' . $e->getMessage();
                }
            }
        }
        
        $this->view('inventory/movement', $data);
    }
    
    public function details($productId = null) {
        if (!$this->hasPermission('production') && !$this->hasPermission('warehouse')) {
            $this->redirect('dashboard');
            return;
        }
        
        if (!$productId) {
            $this->redirect('inventario');
            return;
        }
        
        $product = $this->productModel->findById($productId);
        if (!$product) {
            $this->redirect('inventario');
            return;
        }
        
        $data = [
            'title' => 'Detalle de Inventario - ' . $product['name'] . ' - ' . APP_NAME,
            'product' => $product,
            'availability' => $this->inventoryModel->getProductAvailability($productId),
            'movements' => $this->inventoryModel->getMovementHistory($productId, 30),
            'user_name' => $_SESSION['full_name'] ?? $_SESSION['username'],
            'user_role' => $_SESSION['user_role'] ?? 'guest'
        ];
        
        $this->view('inventory/details', $data);
    }
    
    public function adjust() {
        if (!$this->hasPermission('production') && !$this->hasPermission('warehouse')) {
            $this->redirect('dashboard');
            return;
        }
        
        $data = [
            'title' => 'Ajuste de Inventario - ' . APP_NAME,
            'products' => $this->productModel->findAll(['is_active' => 1]),
            'success' => null,
            'error' => null,
            'user_name' => $_SESSION['full_name'] ?? $_SESSION['username'],
            'user_role' => $_SESSION['user_role'] ?? 'guest'
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $adjustmentData = [
                'product_id' => intval($_POST['product_id'] ?? 0),
                'lot_id' => intval($_POST['lot_id'] ?? 0),
                'adjustment_type' => $_POST['adjustment_type'] ?? 'set',
                'quantity' => floatval($_POST['quantity'] ?? 0),
                'reason' => trim($_POST['reason'] ?? ''),
                'notes' => trim($_POST['notes'] ?? '')
            ];
            
            if ($adjustmentData['product_id'] <= 0 || $adjustmentData['quantity'] < 0) {
                $data['error'] = 'Por favor seleccione un producto y especifique una cantidad válida.';
            } else {
                try {
                    if ($this->processInventoryAdjustment($adjustmentData)) {
                        $data['success'] = 'Ajuste de inventario realizado exitosamente.';
                        unset($_POST);
                    } else {
                        $data['error'] = 'Error al realizar el ajuste de inventario.';
                    }
                } catch (Exception $e) {
                    $data['error'] = 'Error: ' . $e->getMessage();
                }
            }
        }
        
        $this->view('inventory/adjust', $data);
    }
    
    public function getProductLots() {
        if (!$this->hasPermission('production') && !$this->hasPermission('warehouse')) {
            http_response_code(403);
            echo json_encode(['error' => 'Sin permisos']);
            return;
        }
        
        $productId = $_GET['product_id'] ?? 0;
        if (!$productId) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de producto requerido']);
            return;
        }
        
        $lots = $this->inventoryModel->getProductAvailability($productId);
        echo json_encode($lots);
    }
    
    private function getInventoryItems() {
        try {
            $sql = "
                SELECT 
                    i.id,
                    p.code as product_code,
                    p.name as product_name,
                    p.minimum_stock,
                    pl.lot_number,
                    i.quantity,
                    i.location,
                    pl.expiry_date,
                    i.last_updated as updated_at,
                    CASE 
                        WHEN i.quantity <= p.minimum_stock THEN 'low'
                        WHEN i.quantity <= (p.minimum_stock * 1.5) THEN 'warning'
                        ELSE 'normal'
                    END as stock_status
                FROM inventory i
                JOIN products p ON i.product_id = p.id
                JOIN production_lots pl ON i.lot_id = pl.id
                WHERE p.is_active = 1 AND i.quantity > 0
                ORDER BY p.name, pl.lot_number
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting inventory items: " . $e->getMessage());
            return [];
        }
    }
    
    private function getLowStockItems() {
        try {
            $sql = "
                SELECT 
                    p.code,
                    p.name,
                    p.minimum_stock,
                    COALESCE(SUM(i.quantity), 0) as current_stock
                FROM products p
                LEFT JOIN inventory i ON p.id = i.product_id
                WHERE p.is_active = 1
                GROUP BY p.id, p.code, p.name, p.minimum_stock
                HAVING COALESCE(SUM(i.quantity), 0) <= p.minimum_stock
                ORDER BY (COALESCE(SUM(i.quantity), 0) / NULLIF(p.minimum_stock, 0)) ASC
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting low stock items: " . $e->getMessage());
            return [];
        }
    }
    
    private function processInventoryMovement($data) {
        try {
            $this->db->beginTransaction();
            
            // Registrar el movimiento
            $sql = "
                INSERT INTO inventory_movements 
                (product_id, movement_type, quantity, reason, location, lot_number, notes, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['product_id'],
                $data['movement_type'],
                $data['quantity'],
                $data['reason'],
                $data['location'],
                $data['lot_number'],
                $data['notes'],
                $_SESSION['user_id']
            ]);
            
            // Actualizar inventario
            if ($data['movement_type'] === 'entrada') {
                $this->addToInventory($data);
            } else {
                $this->removeFromInventory($data);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    private function addToInventory($data) {
        $sql = "
            INSERT INTO inventory (product_id, quantity, location, lot_number)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            quantity = quantity + VALUES(quantity),
            updated_at = CURRENT_TIMESTAMP
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['product_id'],
            $data['quantity'],
            $data['location'],
            $data['lot_number']
        ]);
    }
    
    private function removeFromInventory($data) {
        // Buscar inventario disponible para reducir
        $sql = "
            SELECT id, quantity FROM inventory 
            WHERE product_id = ? AND quantity > 0
            ORDER BY expiry_date ASC, created_at ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$data['product_id']]);
        $inventoryItems = $stmt->fetchAll();
        
        $remainingToRemove = $data['quantity'];
        
        foreach ($inventoryItems as $item) {
            if ($remainingToRemove <= 0) break;
            
            $toRemove = min($item['quantity'], $remainingToRemove);
            
            $updateSql = "UPDATE inventory SET quantity = quantity - ? WHERE id = ?";
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->execute([$toRemove, $item['id']]);
            
            $remainingToRemove -= $toRemove;
        }
        
        if ($remainingToRemove > 0) {
            throw new Exception("No hay suficiente inventario disponible. Faltan {$remainingToRemove} unidades.");
        }
        
        return true;
    }
    
    private function processInventoryAdjustment($data) {
        try {
            $this->db->beginTransaction();
            
            // Obtener cantidad actual
            $currentInventory = $this->inventoryModel->findById($data['lot_id']);
            if (!$currentInventory && $data['adjustment_type'] !== 'set') {
                throw new Exception('No se encontró el inventario especificado.');
            }
            
            $currentQuantity = $currentInventory ? $currentInventory['quantity'] : 0;
            $newQuantity = 0;
            $adjustmentAmount = 0;
            
            switch ($data['adjustment_type']) {
                case 'set':
                    $newQuantity = $data['quantity'];
                    $adjustmentAmount = $newQuantity - $currentQuantity;
                    break;
                case 'add':
                    $adjustmentAmount = $data['quantity'];
                    $newQuantity = $currentQuantity + $adjustmentAmount;
                    break;
                case 'subtract':
                    $adjustmentAmount = -$data['quantity'];
                    $newQuantity = $currentQuantity - $data['quantity'];
                    if ($newQuantity < 0) {
                        throw new Exception('La cantidad resultante no puede ser negativa.');
                    }
                    break;
            }
            
            // Actualizar inventario
            if ($currentInventory) {
                $this->inventoryModel->update($data['lot_id'], ['quantity' => $newQuantity]);
            } else {
                $this->inventoryModel->create([
                    'product_id' => $data['product_id'],
                    'lot_id' => $data['lot_id'],
                    'quantity' => $newQuantity,
                    'location' => 'Almacén Principal'
                ]);
            }
            
            // Registrar movimiento
            $sql = "
                INSERT INTO inventory_movements 
                (type, product_id, lot_id, quantity, notes, created_by)
                VALUES ('adjustment', ?, ?, ?, ?, ?)
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['product_id'],
                $data['lot_id'],
                $adjustmentAmount,
                "Ajuste: {$data['reason']}. {$data['notes']}",
                $_SESSION['user_id'] ?? 1
            ]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}