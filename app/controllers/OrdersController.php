<?php
/**
 * Controlador de Pedidos
 * Sistema de Logística - Quesos y Productos Leslie
 */

require_once dirname(__DIR__) . '/models/Order.php';
require_once dirname(__DIR__) . '/models/Customer.php';
require_once dirname(__DIR__) . '/models/Product.php';
require_once dirname(__DIR__) . '/models/Inventory.php';

class OrdersController extends Controller {
    private $orderModel;
    private $customerModel;
    private $productModel;
    private $inventoryModel;
    
    public function __construct() {
        parent::__construct();
        $this->orderModel = new Order();
        $this->customerModel = new Customer();
        $this->productModel = new Product();
        $this->inventoryModel = new Inventory();
        $this->requireAuth();
    }
    
    public function index() {
        if (!$this->hasPermission('orders')) {
            $this->redirect('dashboard');
            return;
        }
        
        // Obtener filtros
        $filters = [
            'status' => $_GET['status'] ?? '',
            'customer_id' => intval($_GET['customer_id'] ?? 0),
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'delivery_date' => $_GET['delivery_date'] ?? ''
        ];
        
        $data = [
            'title' => 'Gestión de Pedidos (Preventas) - ' . APP_NAME,
            'orders' => $this->orderModel->getAllOrdersWithDetails(50, $filters),
            'order_stats' => $this->orderModel->getOrderStats(),
            'customers' => $this->customerModel->findAll(['is_active' => 1]),
            'filters' => $filters,
            'deliveries_today' => $this->orderModel->getOrdersByDeliveryDate(date('Y-m-d')),
            'deliveries_tomorrow' => $this->orderModel->getOrdersByDeliveryDate(date('Y-m-d', strtotime('+1 day'))),
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
            'title' => 'Nueva Preventa - ' . APP_NAME,
            'customers' => $this->customerModel->findAll(['is_active' => 1]),
            'products' => $this->productModel->getProductsWithStock(),
            'channel_source' => $_GET['source'] ?? 'web',
            'customer_id' => intval($_GET['customer_id'] ?? 0),
            'success' => null,
            'error' => null,
            'user_name' => $_SESSION['full_name'] ?? $_SESSION['username'],
            'user_role' => $_SESSION['user_role'] ?? 'guest'
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $orderData = [
                    'customer_id' => intval($_POST['customer_id'] ?? 0),
                    'delivery_date' => $_POST['delivery_date'] ?? null,
                    'payment_method' => $_POST['payment_method'] ?? 'cash',
                    'discount_amount' => floatval($_POST['discount_amount'] ?? 0),
                    'notes' => trim($_POST['notes'] ?? ''),
                    'channel_source' => $_POST['channel_source'] ?? 'web'
                ];
                
                $orderDetails = $_POST['products'] ?? [];
                
                // Validaciones
                if ($orderData['customer_id'] <= 0) {
                    throw new Exception('Debe seleccionar un cliente válido');
                }
                
                if (empty($orderDetails) || !$this->validateOrderDetails($orderDetails)) {
                    throw new Exception('Debe agregar al menos un producto con cantidad válida');
                }
                
                // Verificar disponibilidad de inventario
                $this->validateInventoryAvailability($orderDetails);
                
                $orderId = $this->orderModel->createOrderWithDetails($orderData, $orderDetails);
                
                if ($orderId) {
                    $order = $this->orderModel->findById($orderId);
                    $data['success'] = 'Preventa creada exitosamente. Número: ' . $order['order_number'];
                    
                    // Redirigir a vista del pedido
                    if (isset($_POST['redirect_to_view'])) {
                        $this->redirect('pedidos/viewOrder/' . $orderId);
                        return;
                    }
                } else {
                    throw new Exception('Error al crear la preventa');
                }
            } catch (Exception $e) {
                $data['error'] = $e->getMessage();
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
        
        $order = $this->orderModel->getOrderWithDetails($orderId);
        
        if (!$order) {
            $_SESSION['error'] = 'Pedido no encontrado';
            $this->redirect('pedidos');
            return;
        }
        
        $data = [
            'title' => 'Pedido ' . $order['order_number'] . ' - ' . APP_NAME,
            'order' => $order,
            'can_edit' => $this->canEditOrder($order),
            'can_cancel' => $this->canCancelOrder($order),
            'can_confirm' => $this->canConfirmOrder($order),
            'user_name' => $_SESSION['full_name'] ?? $_SESSION['username'],
            'user_role' => $_SESSION['user_role'] ?? 'guest'
        ];
        
        $this->view('orders/view', $data);
    }
    
    public function edit($orderId = null) {
        if (!$this->hasPermission('orders')) {
            $this->redirect('dashboard');
            return;
        }
        
        if (!$orderId) {
            $this->redirect('pedidos');
            return;
        }
        
        $order = $this->orderModel->getOrderWithDetails($orderId);
        
        if (!$order || !$this->canEditOrder($order)) {
            $_SESSION['error'] = 'No se puede editar este pedido';
            $this->redirect('pedidos/viewOrder/' . $orderId);
            return;
        }
        
        $data = [
            'title' => 'Editar Pedido ' . $order['order_number'] . ' - ' . APP_NAME,
            'order' => $order,
            'customers' => $this->customerModel->findAll(['is_active' => 1]),
            'products' => $this->productModel->getProductsWithStock(),
            'success' => null,
            'error' => null,
            'user_name' => $_SESSION['full_name'] ?? $_SESSION['username'],
            'user_role' => $_SESSION['user_role'] ?? 'guest'
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $updateData = [
                    'delivery_date' => $_POST['delivery_date'] ?? null,
                    'payment_method' => $_POST['payment_method'] ?? 'cash',
                    'discount_amount' => floatval($_POST['discount_amount'] ?? 0),
                    'notes' => trim($_POST['notes'] ?? '')
                ];
                
                $this->orderModel->update($orderId, $updateData);
                
                // Actualizar detalles si se proporcionan
                if (isset($_POST['products']) && is_array($_POST['products'])) {
                    $this->updateOrderDetails($orderId, $_POST['products']);
                }
                
                $data['success'] = 'Pedido actualizado exitosamente';
                $data['order'] = $this->orderModel->getOrderWithDetails($orderId);
            } catch (Exception $e) {
                $data['error'] = $e->getMessage();
            }
        }
        
        $this->view('orders/edit', $data);
    }
    
    public function updateStatus() {
        if (!$this->hasPermission('orders')) {
            http_response_code(403);
            echo json_encode(['error' => 'Sin permisos']);
            return;
        }
        
        $orderId = intval($_POST['order_id'] ?? 0);
        $newStatus = $_POST['status'] ?? '';
        $notes = trim($_POST['notes'] ?? '');
        
        try {
            if (!$orderId || !$newStatus) {
                throw new Exception('Datos incompletos');
            }
            
            $order = $this->orderModel->findById($orderId);
            if (!$order) {
                throw new Exception('Pedido no encontrado');
            }
            
            // Validar transición de estado
            if (!$this->isValidStatusTransition($order['status'], $newStatus)) {
                throw new Exception('Transición de estado no válida');
            }
            
            $this->orderModel->updateStatus($orderId, $newStatus, $notes);
            
            echo json_encode([
                'success' => true,
                'message' => 'Estado actualizado correctamente',
                'new_status' => $newStatus
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    public function delivery($orderId = null) {
        if (!$this->hasPermission('orders')) {
            $this->redirect('dashboard');
            return;
        }
        
        if (!$orderId) {
            $this->redirect('pedidos');
            return;
        }
        
        $order = $this->orderModel->getOrderWithDetails($orderId);
        
        if (!$order || $order['status'] !== 'in_route') {
            $_SESSION['error'] = 'Este pedido no está en ruta o no existe';
            $this->redirect('pedidos');
            return;
        }
        
        $data = [
            'title' => 'Entrega - Pedido ' . $order['order_number'] . ' - ' . APP_NAME,
            'order' => $order,
            'success' => null,
            'error' => null,
            'user_name' => $_SESSION['full_name'] ?? $_SESSION['username'],
            'user_role' => $_SESSION['user_role'] ?? 'guest'
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $deliveryData = $_POST['delivery'] ?? [];
                $customerSignature = $_POST['customer_signature'] ?? '';
                $deliveryNotes = trim($_POST['delivery_notes'] ?? '');
                
                // Validar cantidades entregadas
                $this->validateDeliveryQuantities($order['details'], $deliveryData);
                
                // Actualizar cantidades entregadas
                $this->orderModel->updateDeliveryQuantities($orderId, $deliveryData);
                
                // Marcar como entregado
                $this->orderModel->updateStatus(
                    $orderId, 
                    'delivered', 
                    'Entregado - ' . $deliveryNotes,
                    $_SESSION['user_id']
                );
                
                $data['success'] = 'Entrega registrada exitosamente';
                
                // Redirigir a la vista del pedido
                $this->redirect('pedidos/viewOrder/' . $orderId);
                return;
            } catch (Exception $e) {
                $data['error'] = $e->getMessage();
            }
        }
        
        $this->view('orders/delivery', $data);
    }
    
    public function verify($orderNumber = null) {
        // Esta función es pública para permitir verificación con QR
        if (!$orderNumber) {
            http_response_code(400);
            echo "Número de pedido requerido";
            return;
        }
        
        $sql = "SELECT * FROM orders WHERE order_number = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderNumber]);
        $order = $stmt->fetch();
        
        if (!$order) {
            http_response_code(404);
            echo "Pedido no encontrado";
            return;
        }
        
        $data = [
            'title' => 'Verificación de Pedido - ' . APP_NAME,
            'order' => $order,
            'order_details' => $this->orderModel->getOrderDetails($order['id']),
            'customer' => $this->customerModel->findById($order['customer_id'])
        ];
        
        $this->view('orders/verify', $data);
    }
    
    public function whatsappCreate() {
        // Endpoint para crear pedidos desde WhatsApp
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $orderData = [
                'customer_id' => intval($input['customer_id'] ?? 0),
                'delivery_date' => $input['delivery_date'] ?? null,
                'notes' => $input['notes'] ?? '',
                'channel_source' => 'whatsapp',
                'whatsapp_phone' => $input['phone'] ?? '',
                'payment_method' => $input['payment_method'] ?? 'cash'
            ];
            
            $orderDetails = $input['products'] ?? [];
            
            if (!$orderData['customer_id'] || empty($orderDetails)) {
                throw new Exception('Datos de pedido incompletos');
            }
            
            $orderId = $this->orderModel->createOrderWithDetails($orderData, $orderDetails);
            $order = $this->orderModel->findById($orderId);
            
            echo json_encode([
                'success' => true,
                'order_id' => $orderId,
                'order_number' => $order['order_number'],
                'qr_code' => $order['qr_code'],
                'total_amount' => $order['final_amount']
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    public function getProductAvailability() {
        if (!$this->hasPermission('orders')) {
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
        if (!$this->hasPermission('orders')) {
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
    
    private function validateOrderDetails($orderDetails) {
        foreach ($orderDetails as $detail) {
            if (empty($detail['product_id']) || 
                !isset($detail['quantity_ordered']) || 
                $detail['quantity_ordered'] <= 0 ||
                !isset($detail['unit_price']) ||
                $detail['unit_price'] < 0) {
                return false;
            }
        }
        return true;
    }
    
    private function validateInventoryAvailability($orderDetails) {
        foreach ($orderDetails as $detail) {
            $availability = $this->inventoryModel->getProductAvailability($detail['product_id']);
            $totalAvailable = array_sum(array_column($availability, 'available_quantity'));
            
            if ($totalAvailable < $detail['quantity_ordered']) {
                $product = $this->productModel->findById($detail['product_id']);
                throw new Exception("Stock insuficiente para {$product['name']}. Disponible: {$totalAvailable}, Requerido: {$detail['quantity_ordered']}");
            }
        }
    }
    
    private function validateDeliveryQuantities($orderDetails, $deliveryData) {
        foreach ($orderDetails as $detail) {
            $deliveredQty = floatval($deliveryData[$detail['id']]['quantity_delivered'] ?? 0);
            $orderedQty = floatval($detail['quantity_ordered']);
            
            if ($deliveredQty > $orderedQty) {
                throw new Exception("La cantidad entregada no puede ser mayor a la solicitada");
            }
        }
    }
    
    private function canEditOrder($order) {
        return in_array($order['status'], ['pending', 'confirmed']);
    }
    
    private function canCancelOrder($order) {
        return in_array($order['status'], ['pending', 'confirmed']);
    }
    
    private function canConfirmOrder($order) {
        return $order['status'] === 'pending';
    }
    
    private function isValidStatusTransition($currentStatus, $newStatus) {
        $validTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['in_route', 'cancelled'],
            'in_route' => ['delivered', 'cancelled'],
            'delivered' => [], // Estado final
            'cancelled' => [] // Estado final
        ];
        
        return in_array($newStatus, $validTransitions[$currentStatus] ?? []);
    }
    
    private function updateOrderDetails($orderId, $newDetails) {
        // Eliminar detalles existentes
        $sql = "DELETE FROM order_details WHERE order_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        
        // Agregar nuevos detalles
        $totalAmount = 0;
        foreach ($newDetails as $detail) {
            if (empty($detail['product_id']) || $detail['quantity_ordered'] <= 0) {
                continue;
            }
            
            $subtotal = $detail['quantity_ordered'] * $detail['unit_price'];
            $totalAmount += $subtotal;
            
            $sql = "
                INSERT INTO order_details (order_id, product_id, quantity_ordered, unit_price, subtotal)
                VALUES (?, ?, ?, ?, ?)
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $orderId,
                $detail['product_id'],
                $detail['quantity_ordered'],
                $detail['unit_price'],
                $subtotal
            ]);
        }
        
        // Actualizar total del pedido
        $order = $this->orderModel->findById($orderId);
        $discountAmount = $order['discount_amount'] ?? 0;
        $finalAmount = $totalAmount - $discountAmount;
        
        $this->orderModel->update($orderId, [
            'total_amount' => $totalAmount,
            'final_amount' => $finalAmount
        ]);
    }
}