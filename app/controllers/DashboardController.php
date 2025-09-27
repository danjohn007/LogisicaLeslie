<?php
/**
 * Controlador del Dashboard
 * Sistema de Logística - Quesos y Productos Leslie
 */

require_once dirname(__DIR__) . '/models/Order.php';
require_once dirname(__DIR__) . '/models/Product.php';
require_once dirname(__DIR__) . '/models/Customer.php';

class DashboardController extends Controller {
    
    public function index() {
        $this->requireAuth();
        
        // Obtener datos para el dashboard según el rol del usuario
        $userRole = $this->getUserRole();
        $data = [
            'title' => 'Dashboard - ' . APP_NAME,
            'user_role' => $userRole,
            'user_name' => $_SESSION['full_name'],
            'stats' => $this->getDashboardStats($userRole),
            'recent_activities' => $this->getRecentActivities($userRole),
            'alerts' => $this->getSystemAlerts()
        ];
        
        $this->view('dashboard/index', $data);
    }
    
    private function getDashboardStats($role) {
        $stats = [];
        
        try {
            // Estadísticas comunes
            $stats['orders_today'] = $this->getOrdersToday();
            $stats['total_customers'] = $this->getTotalCustomers();
            $stats['revenue_month'] = $this->getRevenueThisMonth();
            $stats['pending_orders'] = $this->getPendingOrders();
            
            // Estadísticas específicas por rol
            switch ($role) {
                case 'admin':
                case 'manager':
                    $stats['products_low_stock'] = $this->getProductsLowStock();
                    $stats['routes_today'] = $this->getRoutesToday();
                    $stats['returns_pending'] = $this->getReturnsPending();
                    $stats['surveys_week'] = $this->getSurveysThisWeek();
                    break;
                    
                case 'seller':
                    $stats['my_orders'] = $this->getMyOrders($_SESSION['user_id']);
                    $stats['my_sales'] = $this->getMySales($_SESSION['user_id']);
                    break;
                    
                case 'driver':
                    $stats['my_routes'] = $this->getMyRoutes($_SESSION['user_id']);
                    $stats['deliveries_today'] = $this->getMyDeliveriesToday($_SESSION['user_id']);
                    break;
                    
                case 'warehouse':
                    $stats['production_today'] = $this->getProductionToday();
                    $stats['inventory_movements'] = $this->getInventoryMovementsToday();
                    break;
            }
            
        } catch (Exception $e) {
            error_log("Error getting dashboard stats: " . $e->getMessage());
            $stats['error'] = 'Error al cargar estadísticas';
        }
        
        return $stats;
    }
    
    private function getRecentActivities($role) {
        $activities = [];
        
        try {
            $sql = "
                SELECT 'order' as type, id, order_number as reference, customer_id, created_at, status
                FROM orders 
                WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAYS)
                
                UNION ALL
                
                SELECT 'sale' as type, id, sale_number as reference, customer_id, created_at, payment_status as status
                FROM direct_sales 
                WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAYS)
                
                ORDER BY created_at DESC 
                LIMIT 10
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $activities = $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Error getting recent activities: " . $e->getMessage());
        }
        
        return $activities;
    }
    
    private function getSystemAlerts() {
        $alerts = [];
        
        try {
            // Productos con stock bajo
            $sql = "
                SELECT p.name, SUM(i.quantity) as stock, p.minimum_stock
                FROM products p
                LEFT JOIN inventory i ON p.id = i.product_id
                WHERE p.is_active = 1
                GROUP BY p.id, p.name, p.minimum_stock
                HAVING stock <= p.minimum_stock OR stock IS NULL
                LIMIT 5
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $lowStock = $stmt->fetchAll();
            
            foreach ($lowStock as $item) {
                $alerts[] = [
                    'type' => 'warning',
                    'message' => "Stock bajo: {$item['name']} (Stock: {$item['stock']}, Mínimo: {$item['minimum_stock']})",
                    'icon' => 'fa-exclamation-triangle'
                ];
            }
            
            // Lotes próximos a vencer
            $sql = "
                SELECT pl.lot_number, p.name, pl.expiry_date
                FROM production_lots pl
                JOIN products p ON pl.product_id = p.id
                WHERE pl.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAYS)
                AND pl.quantity_available > 0
                ORDER BY pl.expiry_date
                LIMIT 5
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $expiringLots = $stmt->fetchAll();
            
            foreach ($expiringLots as $lot) {
                $alerts[] = [
                    'type' => 'danger',
                    'message' => "Lote próximo a vencer: {$lot['name']} - Lote {$lot['lot_number']} (Vence: {$lot['expiry_date']})",
                    'icon' => 'fa-clock'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error getting system alerts: " . $e->getMessage());
        }
        
        return $alerts;
    }
    
    // Métodos para obtener estadísticas específicas
    private function getOrdersToday() {
        $sql = "SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    private function getTotalCustomers() {
        $sql = "SELECT COUNT(*) as count FROM customers WHERE is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    private function getRevenueThisMonth() {
        $sql = "
            SELECT COALESCE(SUM(final_amount), 0) as revenue 
            FROM orders 
            WHERE YEAR(created_at) = YEAR(CURDATE()) 
            AND MONTH(created_at) = MONTH(CURDATE())
            AND status IN ('delivered', 'confirmed')
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['revenue'];
    }
    
    private function getPendingOrders() {
        $sql = "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    private function getProductsLowStock() {
        $sql = "
            SELECT COUNT(*) as count 
            FROM (
                SELECT p.id
                FROM products p
                LEFT JOIN inventory i ON p.id = i.product_id
                WHERE p.is_active = 1
                GROUP BY p.id, p.minimum_stock
                HAVING COALESCE(SUM(i.quantity), 0) <= p.minimum_stock
            ) as low_stock
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    private function getRoutesToday() {
        $sql = "SELECT COUNT(*) as count FROM routes WHERE route_date = CURDATE()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    private function getReturnsPending() {
        $sql = "SELECT COUNT(*) as count FROM returns WHERE status = 'pending'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    private function getSurveysThisWeek() {
        $sql = "SELECT COUNT(*) as count FROM customer_surveys WHERE YEARWEEK(survey_date) = YEARWEEK(CURDATE())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    private function getMyOrders($userId) {
        $sql = "SELECT COUNT(*) as count FROM orders WHERE created_by = ? AND DATE(created_at) = CURDATE()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    private function getMySales($userId) {
        $sql = "SELECT COUNT(*) as count FROM direct_sales WHERE seller_id = ? AND DATE(created_at) = CURDATE()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    private function getMyRoutes($userId) {
        $sql = "SELECT COUNT(*) as count FROM routes WHERE driver_id = ? AND route_date = CURDATE()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    private function getMyDeliveriesToday($userId) {
        $sql = "
            SELECT COUNT(*) as count 
            FROM route_stops rs
            JOIN routes r ON rs.route_id = r.id
            WHERE r.driver_id = ? AND r.route_date = CURDATE()
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    private function getProductionToday() {
        $sql = "SELECT COUNT(*) as count FROM production_lots WHERE DATE(created_at) = CURDATE()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    private function getInventoryMovementsToday() {
        $sql = "SELECT COUNT(*) as count FROM inventory_movements WHERE DATE(movement_date) = CURDATE()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'];
    }
}