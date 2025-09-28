<?php
/**
 * Controlador de Rutas de Entrega
 * Sistema de Logística - Quesos y Productos Leslie
 */

class RoutesController extends Controller {
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
    }
    
    public function index() {
        if (!$this->hasPermission('routes')) {
            $this->redirect('dashboard');
            return;
        }
        
        $data = [
            'title' => 'Gestión de Rutas - ' . APP_NAME,
            'routes' => $this->getRoutes(),
            'drivers' => $this->getDrivers(),
            'pending_orders' => $this->getPendingOrders(),
            'user_name' => $_SESSION['full_name'] ?? $_SESSION['username'],
            'user_role' => $_SESSION['user_role'] ?? 'guest'
        ];
        
        $this->view('routes/index', $data);
    }
    
    public function create() {
        if (!$this->hasPermission('routes')) {
            $this->redirect('dashboard');
            return;
        }
        
        $data = [
            'title' => 'Nueva Ruta - ' . APP_NAME,
            'drivers' => $this->getDrivers(),
            'pending_orders' => $this->getPendingOrders(),
            'success' => null,
            'error' => null
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $routeData = [
                'route_name' => trim($_POST['route_name'] ?? ''),
                'driver_id' => intval($_POST['driver_id'] ?? 0),
                'route_date' => $_POST['route_date'] ?? date('Y-m-d'),
                'start_time' => $_POST['start_time'] ?? null,
                'notes' => trim($_POST['notes'] ?? ''),
                'selected_orders' => $_POST['selected_orders'] ?? []
            ];
            
            if (empty($routeData['route_name']) || empty($routeData['selected_orders'])) {
                $data['error'] = 'Por favor complete el nombre de la ruta y seleccione al menos un pedido.';
            } else {
                try {
                    if ($this->createRoute($routeData)) {
                        $data['success'] = 'Ruta creada exitosamente.';
                        $this->redirect('rutas');
                        return;
                    } else {
                        $data['error'] = 'Error al crear la ruta.';
                    }
                } catch (Exception $e) {
                    $data['error'] = 'Error: ' . $e->getMessage();
                }
            }
        }
        
        $this->view('routes/create', $data);
    }
    
    private function getRoutes() {
        try {
            $sql = "
                SELECT 
                    dr.*,
                    u.first_name as driver_name,
                    u.last_name as driver_lastname,
                    COUNT(ro.id) as total_orders
                FROM delivery_routes dr
                LEFT JOIN users u ON dr.driver_id = u.id
                LEFT JOIN route_orders ro ON dr.id = ro.route_id
                GROUP BY dr.id
                ORDER BY dr.route_date DESC, dr.created_at DESC
                LIMIT 20
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting routes: " . $e->getMessage());
            return [];
        }
    }
    
    private function getDrivers() {
        try {
            $sql = "
                SELECT id, first_name, last_name, phone
                FROM users 
                WHERE role IN ('driver', 'admin', 'manager') AND is_active = 1
                ORDER BY first_name, last_name
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting drivers: " . $e->getMessage());
            return [];
        }
    }
    
    private function getPendingOrders() {
        try {
            $sql = "
                SELECT 
                    o.id,
                    o.order_number,
                    o.delivery_date,
                    o.final_amount,
                    c.business_name as customer_name,
                    c.address as customer_address,
                    c.city
                FROM orders o
                JOIN customers c ON o.customer_id = c.id
                WHERE o.status IN ('confirmed', 'ready')
                AND o.id NOT IN (
                    SELECT DISTINCT order_id 
                    FROM route_orders ro
                    JOIN delivery_routes dr ON ro.route_id = dr.id
                    WHERE dr.status NOT IN ('cancelled')
                )
                ORDER BY o.delivery_date, o.created_at
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting pending orders: " . $e->getMessage());
            return [];
        }
    }
    
    private function createRoute($data) {
        try {
            $this->db->beginTransaction();
            
            // Crear la ruta
            $sql = "
                INSERT INTO delivery_routes (route_name, driver_id, route_date, start_time, notes, total_orders)
                VALUES (?, ?, ?, ?, ?, ?)
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['route_name'],
                $data['driver_id'],
                $data['route_date'],
                $data['start_time'],
                $data['notes'],
                count($data['selected_orders'])
            ]);
            
            $routeId = $this->db->lastInsertId();
            
            // Asignar pedidos a la ruta
            foreach ($data['selected_orders'] as $index => $orderId) {
                $sql = "
                    INSERT INTO route_orders (route_id, order_id, sequence_order)
                    VALUES (?, ?, ?)
                ";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$routeId, $orderId, $index + 1]);
                
                // Actualizar estado del pedido
                $sql = "UPDATE orders SET status = 'in_preparation' WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$orderId]);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}