<?php
/**
 * Controlador de Producción
 * Sistema de Logística - Quesos y Productos Leslie
 */

require_once dirname(__DIR__) . '/models/Product.php';
require_once dirname(__DIR__) . '/models/Production.php';

class ProductionController extends Controller {
    private $productModel;
    private $productionModel;

    public function __construct() {
        parent::__construct();
        $this->productModel = new Product();
        $this->productionModel = new Production();
        $this->requireAuth();
    }    public function index() {
        // Verificar permisos
        if (!$this->hasPermission('production')) {
            $this->redirect('dashboard');
            return;
        }
        
        $data = [
            'title' => 'Gestión de Producción - ' . APP_NAME,
            'production_lots' => $this->productionModel->getProductionLots(),
            'products' => $this->productModel->findAll(['is_active' => 1]),
            'production_stats' => $this->productionModel->getProductionStats(),
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
                'quantity_available' => floatval($_POST['quantity_produced'] ?? 0), // Inicialmente igual a quantity_produced
                'unit_cost' => floatval($_POST['unit_cost'] ?? 0),
                'quality_status' => $_POST['quality_status'] ?? 'good',
                'production_type' => $_POST['production_type'] ?? 'regular',
                'notes' => trim($_POST['notes'] ?? ''),
                'created_by' => $_SESSION['user_id'] ?? null
            ];
            
            // Validaciones
            if (empty($lotData['lot_number']) || $lotData['product_id'] <= 0 || $lotData['quantity_produced'] <= 0) {
                $data['error'] = 'Por favor complete todos los campos obligatorios.';
            } else {
                try {
                    if ($this->productionModel->createLot($lotData)) {
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
}