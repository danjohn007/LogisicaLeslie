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
            'user_name' => $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Usuario',
            'stats' => $this->getDashboardStats($userRole),
            'recent_activities' => $this->getRecentActivities($userRole),
            'alerts' => $this->getSystemAlerts(),
            'chart_data' => $this->getChartData($userRole)
        ];
        
        $this->view('dashboard/index', $data);
    }
    
    /**
     * Check if user has permission for a specific module
     */
    public function hasPermission($module) {
        $userRole = $this->getUserRole();
        
        // Define permissions by role
        $permissions = [
            'admin' => ['orders', 'production', 'routes', 'reports', 'customers', 'settings', 'users'],
            'manager' => ['orders', 'production', 'routes', 'reports', 'customers'],
            'seller' => ['orders', 'customers'],
            'driver' => ['routes'],
            'warehouse' => ['production', 'inventory']
        ];
        
        return isset($permissions[$userRole]) && in_array($module, $permissions[$userRole]);
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
            $dateSub = $this->getDateFunction('DATE_SUB', $curDate, 'INTERVAL 7 DAYS');
            
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
     * Get chart data for dashboard
     */
    private function getChartData($role) {
        return [
            'sales_chart' => $this->getSalesChartData(),
            'inventory_chart' => $this->getInventoryChartData(),
            'customers_chart' => $this->getCustomersChartData(),
            'top_products_chart' => $this->getTopProductsChartData()
        ];
    }
    
    /**
     * Get sales data for the chart (last 7 days)
     */
    private function getSalesChartData() {
        $data = [
            'labels' => [],
            'data' => []
        ];
        
        try {
            $dateFunc = $this->getDateFunction('DATE', 'created_at');
            $curDate = $this->getDateFunction('CURDATE');
            
            // Get last 7 days
            for ($i = 6; $i >= 0; $i--) {
                $dateSub = $this->getDateFunction('DATE_SUB', $curDate, "INTERVAL {$i} DAY");
                
                $sql = "
                    SELECT COALESCE(SUM(final_amount), 0) as revenue
                    FROM orders 
                    WHERE {$dateFunc} = {$dateSub}
                    AND status IN ('delivered', 'confirmed')
                ";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetch();
                
                // Format date for display
                $displayDate = date('D', strtotime("-{$i} days"));
                $data['labels'][] = $displayDate;
                $data['data'][] = floatval($result['revenue'] ?? 0);
            }
            
        } catch (Exception $e) {
            error_log("Error getting sales chart data: " . $e->getMessage());
            // Return default data if there's an error
            $data['labels'] = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
            $data['data'] = [0, 0, 0, 0, 0, 0, 0];
        }
        
        return $data;
    }
    
    /**
     * Get inventory data by category for pie chart
     */
    private function getInventoryChartData() {
        $data = [
            'labels' => [],
            'data' => []
        ];
        
        try {
            // Check if tables exist
            $tablesExist = $this->checkTablesExist(['products', 'inventory']);
            if (!$tablesExist['products']) {
                return [
                    'labels' => ['Sin Datos'],
                    'data' => [1]
                ];
            }
            
            $sql = "
                SELECT 
                    CASE 
                        WHEN p.category IS NULL OR p.category = '' THEN 'Sin Categoría'
                        ELSE p.category 
                    END as category,
                    COALESCE(SUM(i.quantity), 0) as total_stock
                FROM products p
                LEFT JOIN inventory i ON p.id = i.product_id
                WHERE p.is_active = 1
                GROUP BY p.category
                HAVING total_stock > 0
                ORDER BY total_stock DESC
                LIMIT 5
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            if (empty($results)) {
                return [
                    'labels' => ['Sin Stock'],
                    'data' => [1]
                ];
            }
            
            foreach ($results as $row) {
                $data['labels'][] = $row['category'];
                $data['data'][] = floatval($row['total_stock']);
            }
            
        } catch (Exception $e) {
            error_log("Error getting inventory chart data: " . $e->getMessage());
            return [
                'labels' => ['Error'],
                'data' => [1]
            ];
        }
        
        return $data;
    }
    
    /**
     * Get new customers per month (last 6 months)
     */
    private function getCustomersChartData() {
        $data = [
            'labels' => [],
            'data' => []
        ];
        
        try {
            $yearFunc = $this->getDateFunction('YEAR', 'created_at');
            $monthFunc = $this->getDateFunction('MONTH', 'created_at');
            
            // Get last 6 months
            for ($i = 5; $i >= 0; $i--) {
                $targetDate = date('Y-m-01', strtotime("-{$i} months"));
                $targetYear = date('Y', strtotime($targetDate));
                $targetMonth = date('n', strtotime($targetDate)); // n = month without leading zeros
                
                $sql = "
                    SELECT COUNT(*) as count
                    FROM customers 
                    WHERE {$yearFunc} = {$targetYear}
                    AND {$monthFunc} = {$targetMonth}
                ";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetch();
                
                $data['labels'][] = date('M', strtotime($targetDate));
                $data['data'][] = intval($result['count'] ?? 0);
            }
            
        } catch (Exception $e) {
            error_log("Error getting customers chart data: " . $e->getMessage());
            // Return default data
            $data['labels'] = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'];
            $data['data'] = [0, 0, 0, 0, 0, 0];
        }
        
        return $data;
    }
    
    /**
     * Get top selling products
     */
    private function getTopProductsChartData() {
        $data = [
            'labels' => [],
            'data' => []
        ];
        
        try {
            // Check if tables exist
            $tablesExist = $this->checkTablesExist(['order_details', 'products']);
            if (!$tablesExist['order_details'] || !$tablesExist['products']) {
                return [
                    'labels' => ['Sin Datos'],
                    'data' => [1]
                ];
            }
            
            $sql = "
                SELECT 
                    p.name,
                    SUM(od.quantity_ordered) as total_sold
                FROM order_details od
                JOIN products p ON od.product_id = p.id
                JOIN orders o ON od.order_id = o.id
                WHERE o.status IN ('delivered', 'confirmed')
                GROUP BY od.product_id, p.name
                ORDER BY total_sold DESC
                LIMIT 5
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            if (empty($results)) {
                return [
                    'labels' => ['Sin Ventas'],
                    'data' => [1]
                ];
            }
            
            foreach ($results as $row) {
                $data['labels'][] = $row['name'];
                $data['data'][] = floatval($row['total_sold']);
            }
            
        } catch (Exception $e) {
            error_log("Error getting top products chart data: " . $e->getMessage());
            return [
                'labels' => ['Error'],
                'data' => [1]
            ];
        }
        
        return $data;
    }
    
    /**
     * AJAX endpoint to get updated chart data
     */
    public function getChartDataAjax() {
        $this->requireAuth();
        
        header('Content-Type: application/json');
        
        $period = $_GET['period'] ?? 'month';
        $chartType = $_GET['chart'] ?? 'sales';
        
        try {
            switch ($chartType) {
                case 'sales':
                    if ($period === 'week') {
                        $data = $this->getSalesChartData();
                    } elseif ($period === 'month') {
                        $data = $this->getSalesChartDataMonth();
                    } else {
                        $data = $this->getSalesChartDataYear();
                    }
                    break;
                    
                default:
                    $data = ['error' => 'Invalid chart type'];
            }
            
            echo json_encode($data);
            
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        
        exit;
    }
    
    /**
     * Get sales data for the current month (by days)
     */
    private function getSalesChartDataMonth() {
        $data = [
            'labels' => [],
            'data' => []
        ];
        
        try {
            $currentMonth = date('Y-m');
            $daysInMonth = date('t');
            
            $dateFunc = $this->getDateFunction('DATE', 'created_at');
            
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $targetDate = sprintf('%s-%02d', $currentMonth, $day);
                
                $sql = "
                    SELECT COALESCE(SUM(final_amount), 0) as revenue
                    FROM orders 
                    WHERE {$dateFunc} = '{$targetDate}'
                    AND status IN ('delivered', 'confirmed')
                ";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetch();
                
                $data['labels'][] = $day;
                $data['data'][] = floatval($result['revenue'] ?? 0);
            }
            
        } catch (Exception $e) {
            error_log("Error getting monthly sales chart data: " . $e->getMessage());
        }
        
        return $data;
    }
    
    /**
     * Get sales data for the current year (by months)
     */
    private function getSalesChartDataYear() {
        $data = [
            'labels' => [],
            'data' => []
        ];
        
        try {
            $currentYear = date('Y');
            $yearFunc = $this->getDateFunction('YEAR', 'created_at');
            $monthFunc = $this->getDateFunction('MONTH', 'created_at');
            
            $monthNames = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 
                          'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            
            for ($month = 1; $month <= 12; $month++) {
                $sql = "
                    SELECT COALESCE(SUM(final_amount), 0) as revenue
                    FROM orders 
                    WHERE {$yearFunc} = {$currentYear}
                    AND {$monthFunc} = {$month}
                    AND status IN ('delivered', 'confirmed')
                ";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetch();
                
                $data['labels'][] = $monthNames[$month - 1];
                $data['data'][] = floatval($result['revenue'] ?? 0);
            }
            
        } catch (Exception $e) {
            error_log("Error getting yearly sales chart data: " . $e->getMessage());
        }
        
        return $data;
    }
}