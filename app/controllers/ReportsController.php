<?php
/**
 * Controlador de Reportes
 * Sistema de Logística - Quesos y Productos Leslie
 */

class ReportsController extends Controller {
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
    }
    
    public function index() {
        if (!$this->hasPermission('reports')) {
            $this->redirect('dashboard');
            return;
        }
        
        $data = [
            'title' => 'Reportes del Sistema - ' . APP_NAME,
            'sales_summary' => $this->getSalesSummary(),
            'inventory_summary' => $this->getInventorySummary(),
            'customer_summary' => $this->getCustomerSummary(),
            'user_name' => $_SESSION['full_name'] ?? $_SESSION['username'],
            'user_role' => $_SESSION['user_role'] ?? 'guest'
        ];
        
        $this->view('reports/index', $data);
    }
    
    public function sales() {
        if (!$this->hasPermission('reports')) {
            $this->redirect('dashboard');
            return;
        }
        
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01'); // Primer día del mes
        $dateTo = $_GET['date_to'] ?? date('Y-m-d'); // Hoy
        
        $data = [
            'title' => 'Reporte de Ventas - ' . APP_NAME,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'sales_data' => $this->getSalesReport($dateFrom, $dateTo),
            'sales_by_product' => $this->getSalesByProduct($dateFrom, $dateTo),
            'sales_by_customer' => $this->getSalesByCustomer($dateFrom, $dateTo)
        ];
        
        $this->view('reports/sales', $data);
    }
    
    public function inventory() {
        if (!$this->hasPermission('reports')) {
            $this->redirect('dashboard');
            return;
        }
        
        $data = [
            'title' => 'Reporte de Inventario - ' . APP_NAME,
            'inventory_data' => $this->getInventoryReport(),
            'low_stock_items' => $this->getLowStockReport(),
            'expiring_items' => $this->getExpiringItemsReport()
        ];
        
        $this->view('reports/inventory', $data);
    }
    
    public function financial() {
        if (!$this->hasPermission('reports')) {
            $this->redirect('dashboard');
            return;
        }
        
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        
        $data = [
            'title' => 'Reporte Financiero - ' . APP_NAME,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'financial_data' => $this->getFinancialReport($dateFrom, $dateTo),
            'revenue_by_month' => $this->getRevenueByMonth(),
            'payment_methods' => $this->getPaymentMethodsReport($dateFrom, $dateTo)
        ];
        
        $this->view('reports/financial', $data);
    }
    
    private function getSalesSummary() {
        try {
            $summary = [];
            
            // Ventas del día
            $sql = "
                SELECT 
                    COUNT(*) as count,
                    COALESCE(SUM(total_amount), 0) as total
                FROM direct_sales 
                WHERE DATE(sale_date) = CURDATE()
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            $summary['today'] = $result;
            
            // Ventas del mes
            $sql = "
                SELECT 
                    COUNT(*) as count,
                    COALESCE(SUM(total_amount), 0) as total
                FROM direct_sales 
                WHERE MONTH(sale_date) = MONTH(CURDATE()) 
                AND YEAR(sale_date) = YEAR(CURDATE())
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            $summary['month'] = $result;
            
            // Pedidos activos
            $sql = "
                SELECT 
                    COUNT(*) as count,
                    COALESCE(SUM(final_amount), 0) as total
                FROM orders 
                WHERE status NOT IN ('delivered', 'cancelled')
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            $summary['active_orders'] = $result;
            
            return $summary;
        } catch (Exception $e) {
            error_log("Error getting sales summary: " . $e->getMessage());
            return [];
        }
    }
    
    private function getInventorySummary() {
        try {
            $summary = [];
            
            // Total de productos
            $sql = "SELECT COUNT(*) as count FROM products WHERE is_active = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            $summary['total_products'] = $result['count'] ?? 0;
            
            // Productos con stock bajo
            $sql = "
                SELECT COUNT(DISTINCT p.id) as count
                FROM products p
                LEFT JOIN inventory i ON p.id = i.product_id
                WHERE p.is_active = 1
                GROUP BY p.id
                HAVING COALESCE(SUM(i.quantity), 0) <= p.minimum_stock
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $summary['low_stock_count'] = $stmt->rowCount();
            
            // Valor total del inventario
            $sql = "
                SELECT COALESCE(SUM(i.quantity * p.price_per_unit), 0) as total_value
                FROM inventory i
                JOIN products p ON i.product_id = p.id
                WHERE i.quantity > 0
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            $summary['inventory_value'] = $result['total_value'] ?? 0;
            
            return $summary;
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getCustomerSummary() {
        try {
            $summary = [];
            
            // Total de clientes activos
            $sql = "SELECT COUNT(*) as count FROM customers WHERE is_active = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            $summary['active_customers'] = $result['count'] ?? 0;
            
            // Clientes con crédito
            $sql = "SELECT COUNT(*) as count FROM customers WHERE credit_limit > 0 AND is_active = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            $summary['credit_customers'] = $result['count'] ?? 0;
            
            return $summary;
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getSalesReport($dateFrom, $dateTo) {
        try {
            $sql = "
                SELECT 
                    DATE(ds.sale_date) as sale_date,
                    COUNT(*) as total_sales,
                    SUM(ds.total_amount) as total_amount,
                    AVG(ds.total_amount) as avg_sale_amount
                FROM direct_sales ds
                WHERE ds.sale_date BETWEEN ? AND ?
                GROUP BY DATE(ds.sale_date)
                ORDER BY ds.sale_date DESC
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$dateFrom, $dateTo]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getSalesByProduct($dateFrom, $dateTo) {
        try {
            $sql = "
                SELECT 
                    p.name as product_name,
                    p.code as product_code,
                    SUM(dsd.quantity) as total_quantity,
                    SUM(dsd.subtotal) as total_amount
                FROM direct_sale_details dsd
                JOIN direct_sales ds ON dsd.sale_id = ds.id
                JOIN products p ON dsd.product_id = p.id
                WHERE ds.sale_date BETWEEN ? AND ?
                GROUP BY p.id, p.name, p.code
                ORDER BY total_amount DESC
                LIMIT 10
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$dateFrom, $dateTo]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getSalesByCustomer($dateFrom, $dateTo) {
        try {
            $sql = "
                SELECT 
                    c.business_name as customer_name,
                    COUNT(ds.id) as total_sales,
                    SUM(ds.total_amount) as total_amount
                FROM direct_sales ds
                LEFT JOIN customers c ON ds.customer_id = c.id
                WHERE ds.sale_date BETWEEN ? AND ?
                GROUP BY c.id, c.business_name
                ORDER BY total_amount DESC
                LIMIT 10
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$dateFrom, $dateTo]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getInventoryReport() {
        try {
            $sql = "
                SELECT 
                    p.code,
                    p.name,
                    COALESCE(SUM(i.quantity), 0) as current_stock,
                    p.minimum_stock,
                    p.price_per_unit,
                    COALESCE(SUM(i.quantity), 0) * p.price_per_unit as stock_value
                FROM products p
                LEFT JOIN inventory i ON p.id = i.product_id
                WHERE p.is_active = 1
                GROUP BY p.id, p.code, p.name, p.minimum_stock, p.price_per_unit
                ORDER BY p.name
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getLowStockReport() {
        try {
            $sql = "
                SELECT 
                    p.code,
                    p.name,
                    COALESCE(SUM(i.quantity), 0) as current_stock,
                    p.minimum_stock
                FROM products p
                LEFT JOIN inventory i ON p.id = i.product_id
                WHERE p.is_active = 1
                GROUP BY p.id, p.code, p.name, p.minimum_stock
                HAVING current_stock <= p.minimum_stock
                ORDER BY (current_stock / NULLIF(p.minimum_stock, 0)) ASC
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getExpiringItemsReport() {
        try {
            $sql = "
                SELECT 
                    p.name as product_name,
                    i.lot_number,
                    i.quantity,
                    i.expiry_date,
                    DATEDIFF(i.expiry_date, CURDATE()) as days_to_expire
                FROM inventory i
                JOIN products p ON i.product_id = p.id
                WHERE i.expiry_date IS NOT NULL 
                AND i.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                AND i.quantity > 0
                ORDER BY i.expiry_date ASC
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getFinancialReport($dateFrom, $dateTo) {
        try {
            $data = [];
            
            // Revenue de ventas directas
            $sql = "
                SELECT 
                    'Ventas Directas' as source,
                    COUNT(*) as transactions,
                    SUM(total_amount) as total_amount
                FROM direct_sales 
                WHERE sale_date BETWEEN ? AND ?
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$dateFrom, $dateTo]);
            $data['direct_sales'] = $stmt->fetch();
            
            // Revenue de pedidos entregados
            $sql = "
                SELECT 
                    'Pedidos Entregados' as source,
                    COUNT(*) as transactions,
                    SUM(final_amount) as total_amount
                FROM orders 
                WHERE status = 'delivered' 
                AND DATE(updated_at) BETWEEN ? AND ?
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$dateFrom, $dateTo]);
            $data['delivered_orders'] = $stmt->fetch();
            
            return $data;
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getRevenueByMonth() {
        try {
            $sql = "
                SELECT 
                    DATE_FORMAT(sale_date, '%Y-%m') as month,
                    SUM(total_amount) as revenue
                FROM direct_sales 
                WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(sale_date, '%Y-%m')
                ORDER BY month ASC
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getPaymentMethodsReport($dateFrom, $dateTo) {
        try {
            $sql = "
                SELECT 
                    payment_method,
                    COUNT(*) as transactions,
                    SUM(total_amount) as total_amount
                FROM direct_sales 
                WHERE sale_date BETWEEN ? AND ?
                GROUP BY payment_method
                ORDER BY total_amount DESC
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$dateFrom, $dateTo]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
}