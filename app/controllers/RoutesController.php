<?php
/**
 * Controlador RoutesController
 * Sistema de Logística - Quesos y Productos Leslie
 * Módulo de Optimización Logística y Rutas
 */

class RoutesController extends Controller {
    private $routeModel;
    private $orderModel;
    
    public function __construct() {
        parent::__construct();
        $this->routeModel = new Route();
        $this->orderModel = new Order();
        
        // Verificar autenticación
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    }
    
    public function index() {
        $title = "Gestión de Rutas";
        $filters = [
            'driver_id' => $_GET['driver_id'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
            'status' => $_GET['status'] ?? null
        ];
        
        $routes = $this->routeModel->getAllRoutes($filters);
        $drivers = $this->routeModel->getAvailableDrivers();
        
        $this->view('routes/index', compact('routes', 'drivers', 'filters', 'title'));
    }
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'route_name' => $_POST['route_name'],
                    'driver_id' => $_POST['driver_id'],
                    'vehicle_id' => $_POST['vehicle_id'] ?? null,
                    'route_date' => $_POST['route_date'],
                    'start_time' => $_POST['start_time'],
                    'estimated_duration' => $_POST['estimated_duration'] ?? null,
                    'notes' => $_POST['notes'] ?? null,
                    'orders' => json_decode($_POST['orders'] ?? '[]', true)
                ];
                
                $routeId = $this->routeModel->createRoute($data);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Ruta creada exitosamente',
                    'route_id' => $routeId
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            return;
        }
        
        // Cargar datos para el formulario
        $drivers = $this->routeModel->getAvailableDrivers();
        $vehicles = $this->routeModel->getAvailableVehicles();
        $pendingOrders = $this->getPendingOrdersForRoutes();
        
        $title = "Nueva Ruta";
        $this->view('routes/create', compact('drivers', 'vehicles', 'pendingOrders', 'title'));
    }
    
    public function viewRoute($routeId = null) {
        if (!$routeId) {
            header('Location: /rutas');
            exit;
        }
        
        $route = $this->routeModel->getRouteWithDetails($routeId);
        if (!$route) {
            header('Location: /rutas');
            exit;
        }
        
        $routeOrders = $this->routeModel->getRouteOrders($routeId);
        $efficiency = $this->routeModel->getRouteEfficiencyStats($routeId);
        
        $title = "Ruta: " . $route['route_name'];
        
        $this->view('routes/view', compact('route', 'routeOrders', 'efficiency', 'title'));
    }
    
    public function start($routeId = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        try {
            $result = $this->routeModel->startRoute($routeId);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Ruta iniciada correctamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se pudo iniciar la ruta'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function complete($routeId = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        try {
            $completionData = [
                'notes' => $_POST['completion_notes'] ?? null,
                'distance' => $_POST['total_distance'] ?? null,
                'fuel' => $_POST['fuel_consumed'] ?? null
            ];
            
            $result = $this->routeModel->completeRoute($routeId, $completionData);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Ruta completada correctamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se pudo completar la ruta'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function delivery($routeOrderId = null) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $deliveryData = [
                    'notes' => $_POST['delivery_notes'] ?? null,
                    'adjustments' => json_decode($_POST['adjustments'] ?? '[]', true)
                ];
                
                $status = $_POST['delivery_status'];
                
                $result = $this->routeModel->updateDeliveryStatus($routeOrderId, $status, $deliveryData);
                
                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Estado de entrega actualizado'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'No se pudo actualizar el estado'
                    ]);
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            return;
        }
        
        // Cargar vista de entrega
        $title = "Registrar Entrega";
        $this->view('routes/delivery', compact('routeOrderId', 'title'));
    }
    
    public function tracking($routeId = null) {
        // Vista de seguimiento en tiempo real
        if (!$routeId) {
            header('Location: /rutas');
            exit;
        }
        
        $route = $this->routeModel->getRouteWithDetails($routeId);
        $routeOrders = $this->routeModel->getRouteOrders($routeId);
        
        $title = "Seguimiento - " . $route['route_name'];
        
        $this->view('routes/tracking', compact('route', 'routeOrders', 'title'));
    }
    
    public function getRouteProgress($routeId = null) {
        // API para obtener progreso de ruta en tiempo real
        try {
            $route = $this->routeModel->getRouteWithDetails($routeId);
            $orders = $this->routeModel->getRouteOrders($routeId);
            
            $progress = [
                'route_id' => $routeId,
                'status' => $route['status'],
                'total_stops' => $route['total_stops'],
                'completed_stops' => $route['completed_stops'],
                'pending_stops' => $route['pending_stops'],
                'completion_percentage' => $route['total_stops'] > 0 ? 
                    round(($route['completed_stops'] / $route['total_stops']) * 100, 2) : 0,
                'orders' => $orders
            ];
            
            echo json_encode([
                'success' => true,
                'progress' => $progress
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function optimize() {
        // Funcionalidad para optimizar rutas usando algoritmos de optimización
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        try {
            $orderIds = json_decode($_POST['order_ids'] ?? '[]', true);
            $startLocation = $_POST['start_location'] ?? null;
            
            if (empty($orderIds)) {
                throw new Exception('Se requieren pedidos para optimizar');
            }
            
            // Obtener coordenadas de los pedidos
            $orders = [];
            foreach ($orderIds as $orderId) {
                $order = $this->getOrderDetailsForRoute($orderId);
                if ($order && $order['coordinates_lat'] && $order['coordinates_lng']) {
                    $orders[] = [
                        'order_id' => $orderId,
                        'lat' => $order['coordinates_lat'],
                        'lng' => $order['coordinates_lng'],
                        'customer_name' => $order['customer_name'],
                        'address' => $order['customer_address']
                    ];
                }
            }
            
            // Algoritmo básico de optimización (nearest neighbor)
            $optimizedRoute = $this->optimizeRouteBasic($orders, $startLocation);
            
            echo json_encode([
                'success' => true,
                'optimized_route' => $optimizedRoute,
                'estimated_distance' => $this->calculateTotalDistance($optimizedRoute),
                'estimated_time' => $this->estimateRouteTime($optimizedRoute)
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    // Métodos auxiliares privados
    
    private function optimizeRouteBasic($orders, $startLocation = null) {
        if (empty($orders)) return [];
        
        $optimized = [];
        $remaining = $orders;
        $currentLocation = $startLocation ?: ['lat' => 0, 'lng' => 0];
        
        while (!empty($remaining)) {
            $nearestIndex = 0;
            $minDistance = PHP_FLOAT_MAX;
            
            foreach ($remaining as $index => $order) {
                $distance = $this->calculateDistance(
                    $currentLocation['lat'], $currentLocation['lng'],
                    $order['lat'], $order['lng']
                );
                
                if ($distance < $minDistance) {
                    $minDistance = $distance;
                    $nearestIndex = $index;
                }
            }
            
            $optimized[] = $remaining[$nearestIndex];
            $currentLocation = [
                'lat' => $remaining[$nearestIndex]['lat'],
                'lng' => $remaining[$nearestIndex]['lng']
            ];
            array_splice($remaining, $nearestIndex, 1);
        }
        
        return $optimized;
    }
    
    private function calculateDistance($lat1, $lng1, $lat2, $lng2) {
        $earthRadius = 6371; // km
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng/2) * sin($dLng/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }
    
    private function calculateTotalDistance($route) {
        $total = 0;
        for ($i = 1; $i < count($route); $i++) {
            $total += $this->calculateDistance(
                $route[$i-1]['lat'], $route[$i-1]['lng'],
                $route[$i]['lat'], $route[$i]['lng']
            );
        }
        return round($total, 2);
    }
    
    private function estimateRouteTime($route) {
        $distance = $this->calculateTotalDistance($route);
        $avgSpeed = 40; // km/h promedio en ciudad
        $timePerStop = 15; // minutos por parada
        
        $drivingTime = ($distance / $avgSpeed) * 60; // minutos
        $stopTime = count($route) * $timePerStop;
        
        return round($drivingTime + $stopTime);
    }
    
    private function getPendingOrdersForRoutes() {
        try {
            $sql = "
                SELECT 
                    o.id,
                    o.order_number,
                    o.delivery_date,
                    o.total_amount,
                    c.business_name as customer_name,
                    c.contact_name,
                    c.address as customer_address,
                    c.city,
                    c.coordinates_lat,
                    c.coordinates_lng
                FROM orders o
                JOIN customers c ON o.customer_id = c.id
                WHERE o.status IN ('confirmado', 'listo')
                AND o.id NOT IN (
                    SELECT DISTINCT order_id 
                    FROM route_orders ro
                    JOIN routes r ON ro.route_id = r.id
                    WHERE r.status NOT IN ('cancelled')
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
    
    private function getOrderDetailsForRoute($orderId) {
        try {
            $sql = "
                SELECT 
                    o.*,
                    c.business_name as customer_name,
                    c.contact_name,
                    c.address as customer_address,
                    c.city,
                    c.coordinates_lat,
                    c.coordinates_lng
                FROM orders o
                JOIN customers c ON o.customer_id = c.id
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
}