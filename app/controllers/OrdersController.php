<?php
/**
 * Controlador de Pedidos
 * Sistema de Logística - Quesos y Productos Leslie
 */

require_once dirname(__DIR__) . '/models/Order.php';
require_once dirname(__DIR__) . '/models/Customer.php';
require_once dirname(__DIR__) . '/models/Product.php';

class OrdersController extends Controller {
    private $orderModel;
    private $customerModel;
    private $productModel;
    
    public function __construct() {
        parent::__construct();
        $this->orderModel = new Order();
        $this->customerModel = new Customer();
        $this->productModel = new Product();
        $this->requireAuth();
    }
    
    public function index() {
        if (!$this->hasPermission('orders')) {
            $this->redirect('dashboard');
            return;
        }
        
        $data = [
            'title' => 'Gestión de Pedidos - ' . APP_NAME,
            'orders' => $this->getOrders(),
            'order_stats' => $this->getOrderStats(),
            'user_name' => $_SESSION['full_name'] ?? $_SESSION['username'],
            'user_role' => $_SESSION['user_role'] ?? 'guest'
        ];
        
        $this->view('orders/index', $data);
    }
    
    public function create() {
        if (!$this->hasPermission('orders')) {
            $this->redirect('dashboard');
            return;
        }
        
        $data = [
            'title' => 'Nuevo Pedido - ' . APP_NAME,
            'customers' => $this->customerModel->findAll(['is_active' => 1]),
            'products' => $this->productModel->findAll(['is_active' => 1]),
            'success' => null,
            'error' => null
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $orderData = [
                    'customer_id' => intval($_POST['customer_id'] ?? 0),
                    'delivery_date' => $_POST['delivery_date'] ?? null,
                    'notes' => trim($_POST['notes'] ?? ''),
                    'products' => $_POST['products'] ?? []
                ];
                
                if ($orderData['customer_id'] <= 0 || empty($orderData['products'])) {
                    $data['error'] = 'Por favor seleccione un cliente y al menos un producto.';
                } else {
                    $orderId = $this->createOrder($orderData);
                    if ($orderId) {
                        $data['success'] = 'Pedido creado exitosamente.';
                        $this->redirect('pedidos/view/' . $orderId);
                        return;
                    } else {
                        $data['error'] = 'Error al crear el pedido.';
                    }
                }
            } catch (Exception $e) {
                $data['error'] = 'Error: ' . $e->getMessage();
            }
        }
        
        $this->view('orders/create', $data);
    }
    
    public function viewOrder($orderId = null) {
        if (!$this->hasPermission('orders')) {
            $this->redirect('dashboard');
            return;
        }
        
        if (!$orderId) {
            $this->redirect('pedidos');
            return;
        }
        
        $order = $this->getOrderDetails($orderId);
        
        if (!$order) {
            $this->redirect('pedidos');
            return;
        }
        
        $data = [
            'title' => 'Pedido ' . $order['order_number'] . ' - ' . APP_NAME,
            'order' => $order,
            'order_items' => $this->getOrderItems($orderId)
        ];
        
        $this->view('orders/view', $data);
    }
    
    private function getOrders() {
        try {
            $sql = "
                SELECT 
                    o.*,
                    c.business_name as customer_name,
                    c.contact_name,
                    u.first_name as created_by_name,
                    u.last_name as created_by_lastname
                FROM orders o
                JOIN customers c ON o.customer_id = c.id
                LEFT JOIN users u ON o.created_by = u.id
                ORDER BY o.created_at DESC
                LIMIT 50
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting orders: " . $e->getMessage());
            return [];
        }
    }
    
    private function getOrderStats() {
        try {
            $stats = [
                'total_orders' => 0,
                'pending_orders' => 0,
                'confirmed_orders' => 0,
                'delivered_orders' => 0,
                'total_revenue' => 0
            ];
            
            // Total de pedidos
            $sql = "SELECT COUNT(*) as count FROM orders";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['total_orders'] = $result['count'] ?? 0;
            
            // Pedidos por estado
            $sql = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $statusCounts = $stmt->fetchAll();
            
            foreach ($statusCounts as $status) {
                $key = $status['status'] . '_orders';
                if (isset($stats[$key])) {
                    $stats[$key] = $status['count'];
                }
            }
            
            // Revenue del mes actual
            $sql = "
                SELECT COALESCE(SUM(final_amount), 0) as revenue 
                FROM orders 
                WHERE MONTH(created_at) = MONTH(CURDATE()) 
                AND YEAR(created_at) = YEAR(CURDATE())
                AND status IN ('confirmed', 'delivered')
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['total_revenue'] = $result['revenue'] ?? 0;
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error getting order stats: " . $e->getMessage());
            return [];
        }
    }
    
    private function createOrder($data) {
        try {
            $this->db->beginTransaction();
            
            // Generar número de pedido
            $orderNumber = $this->generateOrderNumber();
            
            // Crear pedido
            $sql = "
                INSERT INTO orders (order_number, customer_id, order_date, delivery_date, notes, created_by)
                VALUES (?, ?, CURDATE(), ?, ?, ?)
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $orderNumber,
                $data['customer_id'],
                $data['delivery_date'],
                $data['notes'],
                $_SESSION['user_id']
            ]);
            
            $orderId = $this->db->lastInsertId();
            
            // Agregar productos al pedido
            $totalAmount = 0;
            foreach ($data['products'] as $productData) {
                if (empty($productData['product_id']) || empty($productData['quantity'])) {
                    continue;
                }
                
                $productId = intval($productData['product_id']);
                $quantity = intval($productData['quantity']);
                $unitPrice = floatval($productData['unit_price'] ?? 0);
                $subtotal = $quantity * $unitPrice;
                
                $sql = "
                    INSERT INTO order_details (order_id, product_id, quantity, unit_price, subtotal)
                    VALUES (?, ?, ?, ?, ?)
                ";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$orderId, $productId, $quantity, $unitPrice, $subtotal]);
                
                $totalAmount += $subtotal;
            }
            
            // Actualizar total del pedido
            $sql = "UPDATE orders SET total_amount = ?, final_amount = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$totalAmount, $totalAmount, $orderId]);
            
            $this->db->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    private function generateOrderNumber() {
        $prefix = 'PED' . date('Y');
        $sql = "SELECT COUNT(*) + 1 as next_number FROM orders WHERE order_number LIKE ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prefix . '%']);
        $result = $stmt->fetch();
        $nextNumber = str_pad($result['next_number'], 4, '0', STR_PAD_LEFT);
        return $prefix . $nextNumber;
    }
    
    private function getOrderDetails($orderId) {
        try {
            $sql = "
                SELECT 
                    o.*,
                    c.business_name as customer_name,
                    c.contact_name,
                    c.phone as customer_phone,
                    c.address as customer_address,
                    u.first_name as created_by_name,
                    u.last_name as created_by_lastname
                FROM orders o
                JOIN customers c ON o.customer_id = c.id
                LEFT JOIN users u ON o.created_by = u.id
                WHERE o.id = ?
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$orderId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error getting order details: " . $e->getMessage());
            return null;
        }
    }
    
    private function getOrderItems($orderId) {
        try {
            $sql = "
                SELECT 
                    od.*,
                    p.name as product_name,
                    p.code as product_code
                FROM order_details od
                JOIN products p ON od.product_id = p.id
                WHERE od.order_id = ?
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$orderId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting order items: " . $e->getMessage());
            return [];
        }
    }
}