<?php
/**
 * Controlador de Clientes
 * Sistema de Logística - Quesos y Productos Leslie
 */

require_once dirname(__DIR__) . '/models/Customer.php';

class CustomersController extends Controller {
    private $customerModel;
    
    public function __construct() {
        parent::__construct();
        $this->customerModel = new Customer();
        $this->requireAuth();
    }
    
    public function index() {
        if (!$this->hasPermission('orders')) {
            $this->redirect('dashboard');
            return;
        }
        
        $data = [
            'title' => 'Gestión de Clientes - ' . APP_NAME,
            'customers' => $this->customerModel->findAll(),
            'customer_stats' => $this->getCustomerStats(),
            'user_name' => $_SESSION['full_name'] ?? $_SESSION['username'],
            'user_role' => $_SESSION['user_role'] ?? 'guest'
        ];
        
        $this->view('customers/index', $data);
    }
    
    public function create() {
        if (!$this->hasPermission('orders')) {
            $this->redirect('dashboard');
            return;
        }
        
        $data = [
            'title' => 'Nuevo Cliente - ' . APP_NAME,
            'success' => null,
            'error' => null
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verificar si es una petición AJAX
            $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                     strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            
            try {
                $customerData = [
                    'code' => trim($_POST['code'] ?? ''),
                    'business_name' => trim($_POST['business_name'] ?? ''),
                    'contact_name' => trim($_POST['contact_name'] ?? ''),
                    'phone' => trim($_POST['phone'] ?? ''),
                    'email' => trim($_POST['email'] ?? ''),
                    'address' => trim($_POST['address'] ?? ''),
                    'city' => trim($_POST['city'] ?? ''),
                    'state' => trim($_POST['state'] ?? ''),
                    'postal_code' => trim($_POST['postal_code'] ?? ''),
                    'credit_limit' => floatval($_POST['credit_limit'] ?? 0),
                    'credit_days' => intval($_POST['credit_days'] ?? 0),
                    'payment_terms' => trim($_POST['payment_terms'] ?? ''),
                    'is_active' => 1
                ];
                
                // Validaciones básicas
                if (empty($customerData['business_name'])) {
                    throw new Exception('El nombre del negocio es requerido');
                }
                
                // Generar código si está vacío
                if (empty($customerData['code'])) {
                    $customerData['code'] = $this->generateCustomerCode();
                }
                
                $customerId = $this->customerModel->create($customerData);
                
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'customer_id' => $customerId,
                        'customer' => $customerData
                    ]);
                    return;
                } else {
                    $data['success'] = 'Cliente creado exitosamente';
                }
            } catch (Exception $e) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    http_response_code(400);
                    echo json_encode(['error' => $e->getMessage()]);
                    return;
                } else {
                    $data['error'] = $e->getMessage();
                }
            }
        }
        
        $this->view('customers/create', $data);
    }
    
    private function generateCustomerCode() {
        $prefix = 'CLI';
        $sql = "SELECT MAX(CAST(SUBSTRING(code, 4) AS UNSIGNED)) as max_num 
                FROM customers 
                WHERE code LIKE ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prefix . '%']);
        $result = $stmt->fetch();
        
        $nextNumber = ($result['max_num'] ?? 0) + 1;
        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
    
    private function getCustomerStats() {
        try {
            $stats = [];
            
            // Total de clientes activos
            $sql = "SELECT COUNT(*) as count FROM customers WHERE is_active = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['active_customers'] = $result['count'] ?? 0;
            
            // Clientes con crédito
            $sql = "SELECT COUNT(*) as count FROM customers WHERE credit_limit > 0 AND is_active = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['credit_customers'] = $result['count'] ?? 0;
            
            return $stats;
        } catch (Exception $e) {
            return [];
        }
    }
}