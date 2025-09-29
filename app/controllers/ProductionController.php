<?php
/**
 * Controlador de Producción
 * Sistema de Logística - Quesos y Productos Leslie
 */

require_once dirname(__DIR__) . '/models/Product.php';

class ProductionController extends Controller {
    private $productModel;
    
    public function __construct() {
        parent::__construct();
        $this->productModel = new Product();
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
            'production_lots' => $this->getProductionLots(),
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
            'error' => null
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lotData = [
                'lot_number' => trim($_POST['lot_number'] ?? ''),
                'product_id' => intval($_POST['product_id'] ?? 0),
                'production_date' => $_POST['production_date'] ?? date('Y-m-d'),
                'expiry_date' => $_POST['expiry_date'] ?? null,
                'quantity_produced' => floatval($_POST['quantity_produced'] ?? 0),
                'quantity_available' => floatval($_POST['quantity_available'] ?? $_POST['quantity_produced'] ?? 0),
                'unit_cost' => !empty($_POST['unit_cost']) ? floatval($_POST['unit_cost']) : null,
                'quality_status' => $_POST['quality_status'] ?? 'good',
                'production_type' => $_POST['production_type'] ?? 'fresco',
                'notes' => trim($_POST['notes'] ?? ''),
                'created_by' => $_SESSION['user_id'] ?? null
            ];
            
            // Validaciones
            if (empty($lotData['lot_number']) || $lotData['product_id'] <= 0 || $lotData['quantity_produced'] <= 0) {
                $data['error'] = 'Por favor complete todos los campos obligatorios (número de lote, producto y cantidad producida).';
            } elseif (empty($lotData['expiry_date'])) {
                $data['error'] = 'La fecha de vencimiento es obligatoria.';
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
        
        $this->view('production/create', $data);
    }
    
    private function getProductionLots() {
        try {
            $sql = "
                SELECT pl.*, p.name as product_name, p.code as product_code
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
            // Verificar que el número de lote no exista
            $sql = "SELECT COUNT(*) as count FROM production_lots WHERE lot_number = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$data['lot_number']]);
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                throw new Exception('El número de lote ya existe.');
            }
            
            $sql = "
                INSERT INTO production_lots 
                (lot_number, product_id, production_date, expiry_date, quantity_produced, quantity_available, unit_cost, quality_status, production_type, notes, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['lot_number'],
                $data['product_id'],
                $data['production_date'],
                $data['expiry_date'],
                $data['quantity_produced'],
                $data['quantity_available'],
                $data['unit_cost'],
                $data['quality_status'],
                $data['production_type'],
                $data['notes'],
                $data['created_by']
            ]);
            
            // Actualizar inventario
            if ($result) {
                $this->updateInventory($data['product_id'], $data['quantity_produced'], $data['lot_number']);
            }
            
            return $result;
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
}