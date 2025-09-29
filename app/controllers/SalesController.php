<?php
/**
 * Controlador de Ventas Directas
 * Sistema de Logística - Quesos y Productos Leslie
 */

require_once dirname(__DIR__) . '/models/Sale.php';
require_once dirname(__DIR__) . '/models/Customer.php';
require_once dirname(__DIR__) . '/models/Product.php';
require_once dirname(__DIR__) . '/models/Inventory.php';

class SalesController extends Controller {
    private $saleModel;
    private $customerModel;
    private $productModel;
    private $inventoryModel;
    
    public function __construct() {
        parent::__construct();
        $this->saleModel = new Sale();
        $this->customerModel = new Customer();
        $this->productModel = new Product();
        $this->inventoryModel = new Inventory();
        $this->requireAuth();
    }
    
    public function index() {
        if (!$this->hasPermission('sales')) {
            $this->redirect('dashboard');
            return;
        }
        
        // Obtener filtros
        $filters = [
            'payment_method' => $_GET['payment_method'] ?? '',
            'seller_id' => intval($_GET['seller_id'] ?? 0),
            'customer_id' => intval($_GET['customer_id'] ?? 0),
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];
        
        $data = [
            'title' => 'Ventas Directas - ' . APP_NAME,
            'sales' => $this->saleModel->getAllSalesWithDetails(50, $filters),
            'sales_stats' => $this->saleModel->getSalesStats(),
            'customers' => $this->customerModel->findAll(['is_active' => 1]),
            'sellers' => $this->getActiveSellers(),
            'filters' => $filters,
            'user_name' => $_SESSION['full_name'] ?? $_SESSION['username'],
            'user_role' => $_SESSION['user_role'] ?? 'guest'
        ];
        
        $this->view('sales/index', $data);
    }
    
    public function create() {
        if (!$this->hasPermission('sales')) {
            $this->redirect('dashboard');
            return;
        }
        
        $data = [
            'title' => 'Nueva Venta Directa - ' . APP_NAME,
            'customers' => $this->customerModel->findAll(['is_active' => 1]),
            'products' => $this->productModel->getProductsWithStock(),
            'success' => null,
            'error' => null,
            'user_name' => $_SESSION['full_name'] ?? $_SESSION['username'],
            'user_role' => $_SESSION['user_role'] ?? 'guest'
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $saleData = [
                    'customer_id' => intval($_POST['customer_id'] ?? 0) ?: null,
                    'payment_method' => $_POST['payment_method'] ?? 'cash',
                    'discount_amount' => floatval($_POST['discount_amount'] ?? 0),
                    'notes' => trim($_POST['notes'] ?? '')
                ];
                
                $saleDetails = $_POST['products'] ?? [];
                
                // Validaciones
                if (empty($saleDetails) || !$this->validateSaleDetails($saleDetails)) {
                    throw new Exception('Debe agregar al menos un producto con cantidad válida');
                }
                
                // Verificar disponibilidad de inventario
                $this->validateInventoryAvailability($saleDetails);
                
                $saleId = $this->saleModel->createSaleWithDetails($saleData, $saleDetails);
                
                if ($saleId) {
                    $sale = $this->saleModel->findById($saleId);
                    $data['success'] = 'Venta registrada exitosamente. Número: ' . $sale['sale_number'];
                    
                    // Redirigir a vista de la venta
                    if (isset($_POST['redirect_to_view'])) {
                        $this->redirect('ventas/viewSale/' . $saleId);
                        return;
                    }
                } else {
                    throw new Exception('Error al registrar la venta');
                }
            } catch (Exception $e) {
                $data['error'] = $e->getMessage();
            }
        }
        
        $this->view('sales/create', $data);
    }
    
    public function viewSale($saleId = null) {
        if (!$this->hasPermission('sales')) {
            $this->redirect('dashboard');
            return;
        }
        
        if (!$saleId) {
            $this->redirect('ventas');
            return;
        }
        
        $sale = $this->saleModel->getSaleWithDetails($saleId);
        
        if (!$sale) {
            $_SESSION['error'] = 'Venta no encontrada';
            $this->redirect('ventas');
            return;
        }
        
        $data = [
            'title' => 'Venta ' . $sale['sale_number'] . ' - ' . APP_NAME,
            'sale' => $sale,
            'user_name' => $_SESSION['full_name'] ?? $_SESSION['username'],
            'user_role' => $_SESSION['user_role'] ?? 'guest'
        ];
        
        $this->view('sales/view', $data);
    }
    
    public function getProductAvailability() {
        if (!$this->hasPermission('sales')) {
            http_response_code(403);
            echo json_encode(['error' => 'Sin permisos']);
            return;
        }
        
        $productId = intval($_GET['product_id'] ?? 0);
        
        if (!$productId) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de producto requerido']);
            return;
        }
        
        $availability = $this->inventoryModel->getProductAvailability($productId);
        $product = $this->productModel->findById($productId);
        
        echo json_encode([
            'product' => $product,
            'availability' => $availability,
            'total_available' => array_sum(array_column($availability, 'available_quantity'))
        ]);
    }
    
    public function searchCustomers() {
        if (!$this->hasPermission('sales')) {
            http_response_code(403);
            echo json_encode(['error' => 'Sin permisos']);
            return;
        }
        
        $searchTerm = $_GET['q'] ?? '';
        
        if (strlen($searchTerm) < 2) {
            echo json_encode([]);
            return;
        }
        
        $sql = "
            SELECT id, business_name, contact_name, phone, address
            FROM customers
            WHERE is_active = 1 
            AND (business_name LIKE ? OR contact_name LIKE ? OR phone LIKE ?)
            ORDER BY business_name
            LIMIT 10
        ";
        
        $searchPattern = '%' . $searchTerm . '%';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchPattern, $searchPattern, $searchPattern]);
        $customers = $stmt->fetchAll();
        
        echo json_encode($customers);
    }
    
    // Métodos auxiliares privados
    
    private function getActiveSellers() {
        $sql = "
            SELECT DISTINCT u.id, CONCAT(u.first_name, ' ', u.last_name) as name
            FROM users u
            WHERE u.is_active = 1 AND u.user_role IN ('admin', 'seller', 'employee')
            ORDER BY name
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    private function validateSaleDetails($saleDetails) {
        foreach ($saleDetails as $detail) {
            if (empty($detail['product_id']) || 
                !isset($detail['quantity']) || 
                $detail['quantity'] <= 0 ||
                !isset($detail['unit_price']) ||
                $detail['unit_price'] < 0) {
                return false;
            }
        }
        return true;
    }
    
    private function validateInventoryAvailability($saleDetails) {
        foreach ($saleDetails as $detail) {
            $availability = $this->inventoryModel->getProductAvailability($detail['product_id']);
            $totalAvailable = array_sum(array_column($availability, 'available_quantity'));
            
            if ($totalAvailable < $detail['quantity']) {
                $product = $this->productModel->findById($detail['product_id']);
                throw new Exception("Stock insuficiente para {$product['name']}. Disponible: {$totalAvailable}, Requerido: {$detail['quantity']}");
            }
        }
    }
    
    public function getAvailability($productId = null) {
        if (!$productId && isset($_GET['product_id'])) {
            $productId = $_GET['product_id'];
        }
        
        if (!$productId) {
            echo json_encode(['success' => false, 'message' => 'Product ID required']);
            return;
        }
        
        try {
            $inventory = new Inventory();
            $availability = $inventory->getProductAvailability($productId);
            $totalAvailable = array_sum(array_column($availability, 'available_quantity'));
            
            echo json_encode([
                'success' => true,
                'available_quantity' => $totalAvailable
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function print($saleId = null) {
        if (!$saleId) {
            header('Location: /sales');
            exit;
        }
        
        try {
            $sale = $this->saleModel->getSaleWithDetails($saleId);
            $saleItems = $this->saleModel->getSaleDetails($saleId);
            
            if (!$sale) {
                header('Location: /sales');
                exit;
            }
            
            // Cargar vista de impresión
            include dirname(__DIR__) . '/views/sales/print.php';
        } catch (Exception $e) {
            error_log("Error printing sale: " . $e->getMessage());
            header('Location: /sales');
            exit;
        }
    }
    
    public function cancel($saleId = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        if (!$saleId) {
            echo json_encode(['success' => false, 'message' => 'Sale ID required']);
            return;
        }
        
        try {
            $result = $this->saleModel->cancelSale($saleId);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Venta cancelada correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo cancelar la venta']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}