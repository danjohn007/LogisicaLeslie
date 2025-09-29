<?php
/**
 * Controlador de Producción
 * Sistema de Logística - Quesos y Productos Leslie
 */

require_once dirname(__DIR__) . '/models/Product.php';
require_once dirname(__DIR__) . '/models/ProductionLot.php';

class ProductionController extends Controller {
    private $productModel;
    private $productionLotModel;
    
    public function __construct() {
        parent::__construct();
        $this->productModel = new Product();
        $this->productionLotModel = new ProductionLot();
        $this->requireAuth();
    }
    
    public function index() {
        // Verificar permisos
        if (!$this->hasPermission('production')) {
            $this->redirect('dashboard');
            return;
        }
        
        $data = [
            'title' => 'Gestión de Producción - ' . APP_NAME,
            'production_lots' => $this->productionLotModel->getAllWithProducts(),
            'products' => $this->productModel->findAll(['is_active' => 1]),
            'user_name' => $_SESSION['full_name'] ?? $_SESSION['username'],
            'user_role' => $_SESSION['user_role'] ?? 'guest'
        ];
        
        $this->view('production/index', $data);
    }
    
    public function create() {
        if (!$this->hasPermission('production')) {
            $this->redirect('dashboard');
            return;
        }
        
        $data = [
            'title' => 'Crear Lote de Producción - ' . APP_NAME,
            'products' => $this->productModel->findAll(['is_active' => 1]),
            'success' => null,
            'error' => null,
            'suggested_lot_number' => null
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lotData = [
                'lot_number' => trim($_POST['lot_number'] ?? ''),
                'product_id' => intval($_POST['product_id'] ?? 0),
                'production_date' => $_POST['production_date'] ?? date('Y-m-d'),
                'expiry_date' => $_POST['expiry_date'] ?? null,
                'quantity_produced' => floatval($_POST['quantity_produced'] ?? 0),
                'production_type' => $_POST['production_type'] ?? 'fresco',
                'notes' => trim($_POST['notes'] ?? '')
            ];
            
            // Validaciones
            if (empty($lotData['lot_number']) || $lotData['product_id'] <= 0 || $lotData['quantity_produced'] <= 0) {
                $data['error'] = 'Por favor complete todos los campos obligatorios.';
            } else {
                try {
                    if ($this->createProductionLot($lotData)) {
                        $data['success'] = 'Lote de producción creado exitosamente.';
                        // Limpiar formulario
                        unset($_POST);
                    } else {
                        $data['error'] = 'Error al crear el lote de producción.';
                    }
                } catch (Exception $e) {
                    $data['error'] = 'Error: ' . $e->getMessage();
                }
            }
        }
        
        // Si se solicita un número de lote automático
        if (isset($_GET['generate_lot']) && isset($_GET['product_id'])) {
            $data['suggested_lot_number'] = $this->productionLotModel->generateLotNumber(intval($_GET['product_id']));
        }
        
        $this->view('production/create', $data);
    }
    
    public function generateLotNumberAjax() {
        if (!$this->hasPermission('production')) {
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
        
        $lotNumber = $this->productionLotModel->generateLotNumber($productId);
        echo json_encode(['lot_number' => $lotNumber]);
    }
    
    public function viewLot($id) {
        if (!$this->hasPermission('production')) {
            $this->redirect('dashboard');
            return;
        }
        
        $lot = $this->productionLotModel->getLotDetails($id);
        if (!$lot) {
            $this->redirect('produccion');
            return;
        }
        
        $data = [
            'title' => 'Detalle del Lote - ' . APP_NAME,
            'lot' => $lot,
            'user_name' => $_SESSION['full_name'] ?? $_SESSION['username'],
            'user_role' => $_SESSION['user_role'] ?? 'guest'
        ];
        
        $this->view('production/view', $data);
    }
    
    public function edit($id) {
        if (!$this->hasPermission('production')) {
            $this->redirect('dashboard');
            return;
        }
        
        $lot = $this->productionLotModel->findById($id);
        if (!$lot) {
            $this->redirect('produccion');
            return;
        }
        
        $data = [
            'title' => 'Editar Lote - ' . APP_NAME,
            'lot' => $lot,
            'products' => $this->productModel->findAll(['is_active' => 1]),
            'success' => null,
            'error' => null,
            'user_name' => $_SESSION['full_name'] ?? $_SESSION['username'],
            'user_role' => $_SESSION['user_role'] ?? 'guest'
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $updateData = [
                'lot_number' => trim($_POST['lot_number'] ?? ''),
                'product_id' => intval($_POST['product_id'] ?? 0),
                'production_date' => $_POST['production_date'] ?? '',
                'expiry_date' => $_POST['expiry_date'] ?? null,
                'quantity_produced' => floatval($_POST['quantity_produced'] ?? 0),
                'production_type' => $_POST['production_type'] ?? 'fresco',
                'notes' => trim($_POST['notes'] ?? '')
            ];
            
            // Validaciones
            if (empty($updateData['lot_number']) || $updateData['product_id'] <= 0 || $updateData['quantity_produced'] <= 0) {
                $data['error'] = 'Por favor complete todos los campos obligatorios.';
            } else {
                try {
                    // Verificar si el número de lote cambió y ya existe
                    if ($updateData['lot_number'] !== $lot['lot_number']) {
                        $existingLot = $this->productionLotModel->findByLotNumber($updateData['lot_number']);
                        if ($existingLot) {
                            $data['error'] = 'El número de lote ya existe.';
                        }
                    }
                    
                    if (!isset($data['error'])) {
                        if ($this->productionLotModel->update($id, $updateData)) {
                            $data['success'] = 'Lote actualizado exitosamente.';
                            $data['lot'] = $this->productionLotModel->findById($id); // Actualizar datos
                        } else {
                            $data['error'] = 'Error al actualizar el lote.';
                        }
                    }
                } catch (Exception $e) {
                    $data['error'] = 'Error: ' . $e->getMessage();
                }
            }
        }
        
        $this->view('production/edit', $data);
    }
    
    private function getProductionLots() {
        try {
            $sql = "
                SELECT pl.*, p.name as product_name, p.code as product_code,
                       'terminado' as status
                FROM production_lots pl
                JOIN products p ON pl.product_id = p.id
                ORDER BY pl.created_at DESC
                LIMIT 20
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting production lots: " . $e->getMessage());
            return [];
        }
    }
    
    private function createProductionLot($data) {
        try {
            // Usar el modelo para crear el lote
            return $this->productionLotModel->create($data);
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    private function updateInventory($productId, $quantity, $lotNumber) {
        try {
            $sql = "
                INSERT INTO inventory (product_id, quantity, lot_number, location)
                VALUES (?, ?, ?, 'Producción')
                ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
            ";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$productId, $quantity, $lotNumber]);
        } catch (Exception $e) {
            error_log("Error updating inventory: " . $e->getMessage());
            return false;
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
}