<?php
/**
 * Controlador del Dashboard
 * Sistema de Logística - Quesos y Productos Leslie
 */

require_once dirname(__DIR__) . '/models/Order.php';
require_once dirname(__DIR__) . '/models/Product.php';
require_once dirname(__DIR__) . '/models/Customer.php';

class DashboardController extends Controller {
    
    /**
     * Get database-compatible date functions based on the driver
     */
    private function getDateFunction($function, ...$args) {
        $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
        
        switch ($function) {
            case 'CURDATE':
                return $driver === 'sqlite' ? "DATE('now')" : 'CURDATE()';
                
            case 'NOW':
                return $driver === 'sqlite' ? 'CURRENT_TIMESTAMP' : 'NOW()';
                
            case 'DATE':
                $dateField = $args[0] ?? 'created_at';
                return $driver === 'sqlite' ? "DATE({$dateField})" : "DATE({$dateField})";
                
            case 'YEAR':
                $dateField = $args[0] ?? 'created_at';
                return $driver === 'sqlite' ? "strftime('%Y', {$dateField})" : "YEAR({$dateField})";
                
            case 'MONTH':
                $dateField = $args[0] ?? 'created_at';
                return $driver === 'sqlite' ? "strftime('%m', {$dateField})" : "MONTH({$dateField})";
                
            case 'YEARWEEK':
                $dateField = $args[0] ?? 'created_at';
                return $driver === 'sqlite' ? "strftime('%Y%W', {$dateField})" : "YEARWEEK({$dateField})";
                
            case 'DATE_SUB':
                $dateField = $args[0] ?? 'CURDATE()';
                $interval = $args[1] ?? 'INTERVAL 7 DAYS';
                if ($driver === 'sqlite') {
                    // Convert MySQL interval to SQLite format
                    if (strpos($interval, 'INTERVAL') !== false) {
                        preg_match('/INTERVAL\s+(\d+)\s+(\w+)/', $interval, $matches);
                        $num = $matches[1] ?? '7';
                        $unit = $matches[2] ?? 'DAYS';
                        $sqliteUnit = strtolower(rtrim($unit, 's')); // Convert DAYS to day
                        return "DATE({$dateField}, '-{$num} {$sqliteUnit}')";
                    }
                }
                return "DATE_SUB({$dateField}, {$interval})";
                
            case 'DATE_ADD':
                $dateField = $args[0] ?? 'CURDATE()';
                $interval = $args[1] ?? 'INTERVAL 7 DAYS';
                if ($driver === 'sqlite') {
                    // Convert MySQL interval to SQLite format
                    if (strpos($interval, 'INTERVAL') !== false) {
                        preg_match('/INTERVAL\s+(\d+)\s+(\w+)/', $interval, $matches);
                        $num = $matches[1] ?? '7';
                        $unit = $matches[2] ?? 'DAYS';
                        $sqliteUnit = strtolower(rtrim($unit, 's')); // Convert DAYS to day
                        return "DATE({$dateField}, '+{$num} {$sqliteUnit}')";
                    }
                }
                return "DATE_ADD({$dateField}, {$interval})";
                
            default:
                return $function;
        }
    }
    
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
            'alerts' => $this->getSystemAlerts(),
            'charts_data' => $this->getChartsData($userRole)
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
            $dateFunc = $this->getDateFunction('DATE', 'created_at');
            $curDate = $this->getDateFunction('CURDATE');
            $dateSub = $this->getDateFunction('DATE_SUB', 'CURDATE()', 'INTERVAL 7 DAYS');
            
            $sql = "
                SELECT 'order' as type, id, order_number as reference, customer_id, created_at, status
                FROM orders 
                WHERE {$dateFunc} >= {$dateSub}
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
            // Check if tables exist first (for demo mode)
            $tablesExist = $this->checkTablesExist(['products', 'inventory', 'production_lots']);
            
            if ($tablesExist['products'] && $tablesExist['inventory']) {
                // Productos con stock bajo
                $sql = "
                    SELECT p.name, COALESCE(SUM(i.quantity), 0) as stock, p.minimum_stock
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
            }
            
            if ($tablesExist['production_lots']) {
                // Lotes próximos a vencer
                $curDate = $this->getDateFunction('CURDATE');
                $dateAdd = $this->getDateFunction('DATE_ADD', 'CURDATE()', 'INTERVAL 7 DAYS');
                
                $sql = "
                    SELECT pl.lot_number, p.name, pl.expiry_date
                    FROM production_lots pl
                    JOIN products p ON pl.product_id = p.id
                    WHERE pl.expiry_date BETWEEN {$curDate} AND {$dateAdd}
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
            }
            
        } catch (Exception $e) {
            error_log("Error getting system alerts: " . $e->getMessage());
        }
        
        return $alerts;
    }
    
    private function checkTablesExist($tables) {
        $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
        $exists = [];
        
        foreach ($tables as $table) {
            try {
                if ($driver === 'sqlite') {
                    $sql = "SELECT name FROM sqlite_master WHERE type='table' AND name=?";
                } else {
                    $sql = "SHOW TABLES LIKE ?";
                }
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$table]);
                $exists[$table] = $stmt->fetch() !== false;
            } catch (Exception $e) {
                $exists[$table] = false;
            }
        }
        
        return $exists;
    }
    
    // Métodos para obtener estadísticas específicas
    private function getOrdersToday() {
        $dateFunc = $this->getDateFunction('DATE', 'created_at');
        $curDate = $this->getDateFunction('CURDATE');
        $sql = "SELECT COUNT(*) as count FROM orders WHERE {$dateFunc} = {$curDate}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    private function getTotalCustomers() {
        $sql = "SELECT COUNT(*) as count FROM customers WHERE is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    private function getRevenueThisMonth() {
        $yearFunc = $this->getDateFunction('YEAR', 'created_at');
        $monthFunc = $this->getDateFunction('MONTH', 'created_at');
        $curDateYear = $this->getDateFunction('YEAR', $this->getDateFunction('CURDATE'));
        $curDateMonth = $this->getDateFunction('MONTH', $this->getDateFunction('CURDATE'));
        
        $sql = "
            SELECT COALESCE(SUM(final_amount), 0) as revenue 
            FROM orders 
            WHERE {$yearFunc} = {$curDateYear}
            AND {$monthFunc} = {$curDateMonth}
            AND status IN ('delivered', 'confirmed')
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['revenue'] ?? 0;
    }
    
    private function getPendingOrders() {
        $sql = "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    private function getProductsLowStock() {
        // Check if products and inventory tables exist
        $tablesExist = $this->checkTablesExist(['products', 'inventory']);
        if (!$tablesExist['products']) {
            return 0;
        }
        
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
        return $result['count'] ?? 0;
    }
    
    private function getRoutesToday() {
        // Check if routes table exists
        $tablesExist = $this->checkTablesExist(['routes']);
        if (!$tablesExist['routes']) {
            return 0;
        }
        
        $curDate = $this->getDateFunction('CURDATE');
        $sql = "SELECT COUNT(*) as count FROM routes WHERE route_date = {$curDate}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    private function getReturnsPending() {
        // Check if returns table exists
        $tablesExist = $this->checkTablesExist(['returns']);
        if (!$tablesExist['returns']) {
            return 0;
        }
        
        $sql = "SELECT COUNT(*) as count FROM returns WHERE status = 'pending'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    private function getSurveysThisWeek() {
        // Check if customer_surveys table exists
        $tablesExist = $this->checkTablesExist(['customer_surveys']);
        if (!$tablesExist['customer_surveys']) {
            return 0;
        }
        
        $yearWeekFunc = $this->getDateFunction('YEARWEEK', 'survey_date');
        $curDateYearWeek = $this->getDateFunction('YEARWEEK', $this->getDateFunction('CURDATE'));
        $sql = "SELECT COUNT(*) as count FROM customer_surveys WHERE {$yearWeekFunc} = {$curDateYearWeek}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    private function getMyOrders($userId) {
        $dateFunc = $this->getDateFunction('DATE', 'created_at');
        $curDate = $this->getDateFunction('CURDATE');
        $sql = "SELECT COUNT(*) as count FROM orders WHERE created_by = ? AND {$dateFunc} = {$curDate}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    private function getMySales($userId) {
        // Check if direct_sales table exists
        $tablesExist = $this->checkTablesExist(['direct_sales']);
        if (!$tablesExist['direct_sales']) {
            return 0;
        }
        
        $dateFunc = $this->getDateFunction('DATE', 'created_at');
        $curDate = $this->getDateFunction('CURDATE');
        $sql = "SELECT COUNT(*) as count FROM direct_sales WHERE seller_id = ? AND {$dateFunc} = {$curDate}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    private function getMyRoutes($userId) {
        // Check if routes table exists
        $tablesExist = $this->checkTablesExist(['routes']);
        if (!$tablesExist['routes']) {
            return 0;
        }
        
        $curDate = $this->getDateFunction('CURDATE');
        $sql = "SELECT COUNT(*) as count FROM routes WHERE driver_id = ? AND route_date = {$curDate}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    private function getMyDeliveriesToday($userId) {
        // Check if route_stops and routes tables exist
        $tablesExist = $this->checkTablesExist(['route_stops', 'routes']);
        if (!$tablesExist['route_stops'] || !$tablesExist['routes']) {
            return 0;
        }
        
        $curDate = $this->getDateFunction('CURDATE');
        $sql = "
            SELECT COUNT(*) as count 
            FROM route_stops rs
            JOIN routes r ON rs.route_id = r.id
            WHERE r.driver_id = ? AND r.route_date = {$curDate}
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    private function getProductionToday() {
        // Check if production_lots table exists
        $tablesExist = $this->checkTablesExist(['production_lots']);
        if (!$tablesExist['production_lots']) {
            return 0;
        }
        
        $dateFunc = $this->getDateFunction('DATE', 'created_at');
        $curDate = $this->getDateFunction('CURDATE');
        $sql = "SELECT COUNT(*) as count FROM production_lots WHERE {$dateFunc} = {$curDate}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    private function getInventoryMovementsToday() {
        // Check if inventory_movements table exists
        $tablesExist = $this->checkTablesExist(['inventory_movements']);
        if (!$tablesExist['inventory_movements']) {
            return 0;
        }
        
        $dateFunc = $this->getDateFunction('DATE', 'movement_date');
        $curDate = $this->getDateFunction('CURDATE');
        $sql = "SELECT COUNT(*) as count FROM inventory_movements WHERE {$dateFunc} = {$curDate}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get data for dashboard charts
     */
    private function getChartsData($role) {
        $chartsData = [];
        
        try {
            // Chart 1: Production Stats (Last 7 days)
            $chartsData['production_stats'] = $this->getProductionChartData();
            
            // Chart 2: Payment Methods Distribution
            $chartsData['payment_methods'] = $this->getPaymentMethodsChartData();
            
            // Chart 3: Route Efficiency Stats
            $chartsData['route_efficiency'] = $this->getRouteEfficiencyChartData();
            
            // Chart 4: Sales by Day (original chart with real data)
            $chartsData['sales_by_day'] = $this->getSalesByDayChartData();
            
        } catch (Exception $e) {
            error_log("Error getting charts data: " . $e->getMessage());
        }
        
        return $chartsData;
    }

    /**
     * Get production statistics for the last 7 days
     */
    private function getProductionChartData() {
        $tablesExist = $this->checkTablesExist(['production_lots']);
        if (!$tablesExist['production_lots']) {
            return ['labels' => [], 'data' => []];
        }

        $sql = "
            SELECT 
                DATE(production_date) as date,
                COUNT(*) as lots_produced,
                SUM(quantity_produced) as total_quantity
            FROM production_lots 
            WHERE production_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DATE(production_date)
            ORDER BY DATE(production_date)
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        $labels = [];
        $data = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $dayLabel = date('D', strtotime($date));
            $labels[] = $dayLabel;
            
            $found = false;
            foreach ($results as $result) {
                if ($result['date'] === $date) {
                    $data[] = (int)$result['total_quantity'];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $data[] = 0;
            }
        }
        
        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Get payment methods distribution
     */
    private function getPaymentMethodsChartData() {
        $tablesExist = $this->checkTablesExist(['direct_sales', 'orders']);
        $data = [];
        
        if ($tablesExist['direct_sales']) {
            // Get data from direct sales
            $sql = "
                SELECT 
                    payment_method,
                    COUNT(*) as count,
                    SUM(COALESCE(final_amount, total_amount)) as total
                FROM direct_sales 
                WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY payment_method
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            foreach ($results as $result) {
                $method = ucfirst($result['payment_method']);
                if ($method === 'Card') $method = 'Tarjeta';
                if ($method === 'Cash') $method = 'Efectivo';
                if ($method === 'Transfer') $method = 'Transferencia';
                
                $data[] = [
                    'label' => $method,
                    'value' => (int)$result['count'],
                    'amount' => (float)$result['total']
                ];
            }
        }
        
        // If no data, provide default structure
        if (empty($data)) {
            $data = [
                ['label' => 'Efectivo', 'value' => 60, 'amount' => 15000],
                ['label' => 'Tarjeta', 'value' => 30, 'amount' => 8500],
                ['label' => 'Transferencia', 'value' => 10, 'amount' => 3200]
            ];
        }
        
        return $data;
    }

    /**
     * Get route efficiency statistics
     */
    private function getRouteEfficiencyChartData() {
        $tablesExist = $this->checkTablesExist(['routes', 'route_orders']);
        if (!$tablesExist['routes']) {
            return ['labels' => [], 'completed' => [], 'total' => []];
        }

        $sql = "
            SELECT 
                DATE(r.route_date) as date,
                COUNT(DISTINCT r.id) as total_routes,
                COUNT(CASE WHEN r.status = 'completed' THEN 1 END) as completed_routes
            FROM routes r
            WHERE r.route_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DATE(r.route_date)
            ORDER BY DATE(r.route_date)
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        $labels = [];
        $completed = [];
        $total = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $dayLabel = date('D', strtotime($date));
            $labels[] = $dayLabel;
            
            $found = false;
            foreach ($results as $result) {
                if ($result['date'] === $date) {
                    $total[] = (int)$result['total_routes'];
                    $completed[] = (int)$result['completed_routes'];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $total[] = 0;
                $completed[] = 0;
            }
        }
        
        return [
            'labels' => $labels, 
            'completed' => $completed, 
            'total' => $total
        ];
    }

    /**
     * Get sales by day for the last 7 days (real data for existing chart)
     */
    private function getSalesByDayChartData() {
        $tablesExist = $this->checkTablesExist(['direct_sales']);
        if (!$tablesExist['direct_sales']) {
            return ['labels' => [], 'data' => []];
        }

        $sql = "
            SELECT 
                DATE(sale_date) as date,
                COUNT(*) as sales_count,
                SUM(COALESCE(final_amount, total_amount)) as total_revenue
            FROM direct_sales 
            WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DATE(sale_date)
            ORDER BY DATE(sale_date)
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        $labels = [];
        $data = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $dayLabel = date('D', strtotime($date));
            $labels[] = $dayLabel;
            
            $found = false;
            foreach ($results as $result) {
                if ($result['date'] === $date) {
                    $data[] = (float)$result['total_revenue'];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $data[] = 0;
            }
        }
        
        return ['labels' => $labels, 'data' => $data];
    }
}