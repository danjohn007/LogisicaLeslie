<?php
/**
 * Controlador de Ventas Directas
 * Sistema de Logística - Quesos y Productos Leslie
 */

require_once dirname(__DIR__) . '/models/Customer.php';
require_once dirname(__DIR__) . '/models/Product.php';

class SalesController extends Controller {
    private $customerModel;
    private $productModel;
    
    public function __construct() {
        parent::__construct();
        $this->customerModel = new Customer();
        $this->productModel = new Product();
        $this->requireAuth();
    }
    
    public function index() {
        if (!$this->hasPermission('sales')) {
            $this->redirect('dashboard');
            return;
        }
        
        $data = [
            'title' => 'Ventas Directas - ' . APP_NAME,
            'sales' => $this->getDirectSales(),
            'sales_stats' => $this->getSalesStats(),
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
            'title' => 'Nueva Venta - ' . APP_NAME,
            'customers' => $this->customerModel->findAll(['is_active' => 1]),
            'products' => $this->productModel->findAll(['is_active' => 1]),
            'success' => null,
            'error' => null
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $saleData = [
                    'customer_id' => intval($_POST['customer_id'] ?? 0),
                    'payment_method' => $_POST['payment_method'] ?? 'cash',
                    'notes' => trim($_POST['notes'] ?? ''),
                    'products' => $_POST['products'] ?? []
                ];
                
                if (empty($saleData['products'])) {
                    $data['error'] = 'Por favor seleccione al menos un producto.';
                } else {
                    $saleId = $this->createDirectSale($saleData);
                    if ($saleId) {
                        $data['success'] = 'Venta registrada exitosamente.';
                        $this->redirect('ventas/view/' . $saleId);
                        return;
                    } else {
                        $data['error'] = 'Error al registrar la venta.';
                    }
                }
            } catch (Exception $e) {
                $data['error'] = 'Error: ' . $e->getMessage();
            }
        }
        
        $this->view('sales/create', $data);
    }
    
    public function view($saleId = null) {
        if (!$this->hasPermission('sales')) {
            $this->redirect('dashboard');
            return;
        }
        
        if (!$saleId) {
            $this->redirect('ventas');
            return;
        }
        
        $sale = $this->getSaleDetails($saleId);
        
        if (!$sale) {
            $this->redirect('ventas');
            return;
        }
        
        $data = [
            'title' => 'Venta ' . $sale['sale_number'] . ' - ' . APP_NAME,
            'sale' => $sale,
            'sale_items' => $this->getSaleItems($saleId)
        ];
        
        $this->view('sales/view', $data);
    }
    
    private function getDirectSales() {
        try {
            $sql = "
                SELECT 
                    ds.*,
                    c.business_name as customer_name,
                    c.contact_name,
                    u.first_name as seller_name,
                    u.last_name as seller_lastname
                FROM direct_sales ds
                LEFT JOIN customers c ON ds.customer_id = c.id
                JOIN users u ON ds.seller_id = u.id
                ORDER BY ds.created_at DESC
                LIMIT 50
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting direct sales: " . $e->getMessage());
            return [];
        }
    }
    
    private function getSalesStats() {
        try {
            $stats = [];
            
            // Ventas del día
            $sql = "
                SELECT COALESCE(SUM(total_amount), 0) as total 
                FROM direct_sales 
                WHERE DATE(sale_date) = CURDATE()
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['today_sales'] = $result['total'] ?? 0;
            
            // Ventas del mes
            $sql = "
                SELECT COALESCE(SUM(total_amount), 0) as total 
                FROM direct_sales 
                WHERE MONTH(sale_date) = MONTH(CURDATE()) 
                AND YEAR(sale_date) = YEAR(CURDATE())
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['month_sales'] = $result['total'] ?? 0;
            
            // Total de ventas
            $sql = "SELECT COUNT(*) as count FROM direct_sales";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['total_sales'] = $result['count'] ?? 0;
            
            return $stats;
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function createDirectSale($data) {
        try {
            $this->db->beginTransaction();
            
            // Generar número de venta
            $saleNumber = $this->generateSaleNumber();
            
            // Crear venta
            $sql = "
                INSERT INTO direct_sales (sale_number, customer_id, sale_date, payment_method, notes, seller_id, total_amount)
                VALUES (?, ?, CURDATE(), ?, ?, ?, 0)
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $saleNumber,
                $data['customer_id'] ?: null,
                $data['payment_method'],
                $data['notes'],
                $_SESSION['user_id']
            ]);
            
            $saleId = $this->db->lastInsertId();
            
            // Agregar productos a la venta
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
                    INSERT INTO direct_sale_details (sale_id, product_id, quantity, unit_price, subtotal)
                    VALUES (?, ?, ?, ?, ?)
                ";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$saleId, $productId, $quantity, $unitPrice, $subtotal]);
                
                $totalAmount += $subtotal;
                
                // Reducir inventario
                $this->reduceInventory($productId, $quantity);
            }
            
            // Actualizar total de la venta
            $sql = "UPDATE direct_sales SET total_amount = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$totalAmount, $saleId]);
            
            $this->db->commit();
            return $saleId;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    private function generateSaleNumber() {
        $prefix = 'VTA' . date('Y');
        $sql = "SELECT COUNT(*) + 1 as next_number FROM direct_sales WHERE sale_number LIKE ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prefix . '%']);
        $result = $stmt->fetch();
        $nextNumber = str_pad($result['next_number'], 4, '0', STR_PAD_LEFT);
        return $prefix . $nextNumber;
    }
    
    private function reduceInventory($productId, $quantity) {
        // Implementar reducción de inventario usando FIFO
        $sql = "
            SELECT id, quantity FROM inventory 
            WHERE product_id = ? AND quantity > 0
            ORDER BY expiry_date ASC, created_at ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId]);
        $inventoryItems = $stmt->fetchAll();
        
        $remainingToReduce = $quantity;
        
        foreach ($inventoryItems as $item) {
            if ($remainingToReduce <= 0) break;
            
            $toReduce = min($item['quantity'], $remainingToReduce);
            
            $updateSql = "UPDATE inventory SET quantity = quantity - ? WHERE id = ?";
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->execute([$toReduce, $item['id']]);
            
            $remainingToReduce -= $toReduce;
        }
        
        if ($remainingToReduce > 0) {
            throw new Exception("Inventario insuficiente para el producto. Faltan {$remainingToReduce} unidades.");
        }
    }
    
    private function getSaleDetails($saleId) {
        try {
            $sql = "
                SELECT 
                    ds.*,
                    c.business_name as customer_name,
                    c.contact_name,
                    u.first_name as seller_name,
                    u.last_name as seller_lastname
                FROM direct_sales ds
                LEFT JOIN customers c ON ds.customer_id = c.id
                JOIN users u ON ds.seller_id = u.id
                WHERE ds.id = ?
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$saleId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }
    
    private function getSaleItems($saleId) {
        try {
            $sql = "
                SELECT 
                    dsd.*,
                    p.name as product_name,
                    p.code as product_code
                FROM direct_sale_details dsd
                JOIN products p ON dsd.product_id = p.id
                WHERE dsd.sale_id = ?
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$saleId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
}