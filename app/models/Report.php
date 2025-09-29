<?php
/**
 * Modelo Report
 * Sistema de Logística - Quesos y Productos Leslie
 * Módulo de Analítica y Reportes
 */

class Report extends Model {
    
    public function getDailyOperationalSummary($date = null) {
        $date = $date ?: date('Y-m-d');
        
        $summary = [];
        
        // Resumen de ventas
        $sql = "
            SELECT 
                COUNT(*) as total_sales,
                COALESCE(SUM(total_amount), 0) as total_revenue,
                COUNT(CASE WHEN payment_method = 'efectivo' THEN 1 END) as cash_sales,
                COUNT(CASE WHEN payment_method = 'credito' THEN 1 END) as credit_sales
            FROM direct_sales 
            WHERE DATE(sale_date) = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$date]);
        $summary['sales'] = $stmt->fetch();
        
        // Resumen de entregas
        $sql = "
            SELECT 
                COUNT(DISTINCT r.id) as total_routes,
                COUNT(ro.id) as total_deliveries,
                COUNT(CASE WHEN ro.delivery_status = 'delivered' THEN 1 END) as successful_deliveries,
                COUNT(CASE WHEN ro.delivery_status = 'failed' THEN 1 END) as failed_deliveries,
                COUNT(CASE WHEN ro.delivery_status = 'partial' THEN 1 END) as partial_deliveries
            FROM routes r
            LEFT JOIN route_orders ro ON r.id = ro.route_id
            WHERE r.route_date = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$date]);
        $summary['deliveries'] = $stmt->fetch();
        
        // Resumen de retornos
        $sql = "
            SELECT 
                COUNT(*) as total_returns,
                SUM(total_returned_items) as total_returned_items,
                COUNT(CASE WHEN approved_for_resale = 1 THEN 1 END) as approved_returns,
                COUNT(CASE WHEN approved_for_resale = 0 THEN 1 END) as rejected_returns
            FROM returns 
            WHERE return_date = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$date]);
        $summary['returns'] = $stmt->fetch();
        
        // Resumen de producción
        $sql = "
            SELECT 
                COUNT(*) as total_lots,
                SUM(total_quantity) as total_produced,
                COUNT(DISTINCT product_id) as products_produced
            FROM production_lots 
            WHERE DATE(production_date) = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$date]);
        $summary['production'] = $stmt->fetch();
        
        return $summary;
    }
    
    public function getSalesByPeriod($dateFrom, $dateTo, $groupBy = 'day') {
        $dateFormat = match($groupBy) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => '%Y-%m-%d'
        };
        
        $sql = "
            SELECT 
                DATE_FORMAT(sale_date, ?) as period,
                COUNT(*) as total_sales,
                SUM(total_amount) as total_revenue,
                AVG(total_amount) as average_sale,
                COUNT(DISTINCT customer_id) as unique_customers
            FROM direct_sales 
            WHERE sale_date BETWEEN ? AND ?
            GROUP BY period
            ORDER BY period
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dateFormat, $dateFrom, $dateTo]);
        return $stmt->fetchAll();
    }
    
    public function getDeliveryEfficiencyReport($dateFrom, $dateTo) {
        $sql = "
            SELECT 
                r.id as route_id,
                r.route_name,
                r.route_date,
                CONCAT(u.first_name, ' ', u.last_name) as driver_name,
                COUNT(ro.id) as total_stops,
                COUNT(CASE WHEN ro.delivery_status = 'delivered' THEN 1 END) as successful_deliveries,
                COUNT(CASE WHEN ro.delivery_status = 'failed' THEN 1 END) as failed_deliveries,
                ROUND((COUNT(CASE WHEN ro.delivery_status = 'delivered' THEN 1 END) / COUNT(ro.id)) * 100, 2) as success_rate,
                TIMESTAMPDIFF(MINUTE, r.actual_start_time, r.actual_end_time) as actual_duration,
                r.estimated_duration,
                r.total_distance,
                r.fuel_consumed
            FROM routes r
            LEFT JOIN users u ON r.driver_id = u.id
            LEFT JOIN route_orders ro ON r.id = ro.route_id
            WHERE r.route_date BETWEEN ? AND ?
            AND r.status = 'completed'
            GROUP BY r.id
            ORDER BY r.route_date DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dateFrom, $dateTo]);
        return $stmt->fetchAll();
    }
    
    public function getReturnAnalysis($dateFrom, $dateTo) {
        $analysis = [];
        
        // Análisis por tipo de retorno
        $sql = "
            SELECT 
                return_type,
                COUNT(*) as total_returns,
                SUM(total_returned_items) as total_items,
                AVG(total_returned_items) as avg_items_per_return
            FROM returns 
            WHERE return_date BETWEEN ? AND ?
            GROUP BY return_type
            ORDER BY total_returns DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dateFrom, $dateTo]);
        $analysis['by_type'] = $stmt->fetchAll();
        
        // Análisis por producto
        $sql = "
            SELECT 
                p.name as product_name,
                p.code as product_code,
                COUNT(DISTINCT ri.return_id) as return_count,
                SUM(ri.quantity_returned) as total_returned,
                AVG(ri.quantity_returned) as avg_returned_per_incident,
                COUNT(CASE WHEN ri.can_resell = 1 THEN 1 END) as resellable_count,
                COUNT(CASE WHEN ri.can_resell = 0 THEN 1 END) as waste_count
            FROM return_items ri
            JOIN products p ON ri.product_id = p.id
            JOIN returns r ON ri.return_id = r.id
            WHERE r.return_date BETWEEN ? AND ?
            GROUP BY ri.product_id
            ORDER BY total_returned DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dateFrom, $dateTo]);
        $analysis['by_product'] = $stmt->fetchAll();
        
        // Análisis por cliente
        $sql = "
            SELECT 
                c.business_name as customer_name,
                COUNT(*) as return_count,
                SUM(r.total_returned_items) as total_items_returned,
                COUNT(CASE WHEN r.return_type = 'quality_issue' THEN 1 END) as quality_issues,
                COUNT(CASE WHEN r.return_type = 'customer_rejection' THEN 1 END) as rejections
            FROM returns r
            JOIN customers c ON r.customer_id = c.id
            WHERE r.return_date BETWEEN ? AND ?
            GROUP BY r.customer_id
            HAVING return_count > 1
            ORDER BY return_count DESC, total_items_returned DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dateFrom, $dateTo]);
        $analysis['by_customer'] = $stmt->fetchAll();
        
        return $analysis;
    }
    
    public function getSellerPerformance($dateFrom, $dateTo) {
        $sql = "
            SELECT 
                u.id as seller_id,
                CONCAT(u.first_name, ' ', u.last_name) as seller_name,
                COUNT(ds.id) as total_sales,
                SUM(ds.total_amount) as total_revenue,
                AVG(ds.total_amount) as average_sale,
                COUNT(DISTINCT ds.customer_id) as unique_customers,
                COUNT(DISTINCT DATE(ds.sale_date)) as active_days,
                ROUND(SUM(ds.total_amount) / COUNT(DISTINCT DATE(ds.sale_date)), 2) as avg_daily_revenue,
                
                -- Estadísticas de rutas (si es chofer también)
                COUNT(DISTINCT r.id) as routes_completed,
                COUNT(DISTINCT ro.id) as deliveries_made,
                COUNT(CASE WHEN ro.delivery_status = 'delivered' THEN 1 END) as successful_deliveries,
                ROUND((COUNT(CASE WHEN ro.delivery_status = 'delivered' THEN 1 END) / NULLIF(COUNT(ro.id), 0)) * 100, 2) as delivery_success_rate
                
            FROM users u
            LEFT JOIN direct_sales ds ON u.id = ds.seller_id AND ds.sale_date BETWEEN ? AND ?
            LEFT JOIN routes r ON u.id = r.driver_id AND r.route_date BETWEEN ? AND ?
            LEFT JOIN route_orders ro ON r.id = ro.route_id
            WHERE u.user_role IN ('seller', 'seller_driver', 'admin')
            AND u.is_active = 1
            GROUP BY u.id
            ORDER BY total_revenue DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dateFrom, $dateTo, $dateFrom, $dateTo]);
        return $stmt->fetchAll();
    }
    
    public function getLotAnalysis($dateFrom, $dateTo) {
        $analysis = [];
        
        // Análisis de lotes por producto
        $sql = "
            SELECT 
                p.name as product_name,
                pl.lot_number,
                pl.production_date,
                pl.expiry_date,
                pl.total_quantity as produced,
                pl.quantity_available as remaining,
                (pl.total_quantity - pl.quantity_available) as used,
                ROUND(((pl.total_quantity - pl.quantity_available) / pl.total_quantity) * 100, 2) as usage_percentage,
                DATEDIFF(pl.expiry_date, CURDATE()) as days_to_expiry,
                CASE 
                    WHEN pl.expiry_date < CURDATE() THEN 'expired'
                    WHEN pl.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 'expires_soon'
                    ELSE 'good'
                END as expiry_status
            FROM production_lots pl
            JOIN products p ON pl.product_id = p.id
            WHERE pl.production_date BETWEEN ? AND ?
            ORDER BY pl.production_date DESC, p.name
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dateFrom, $dateTo]);
        $analysis['lot_details'] = $stmt->fetchAll();
        
        // Resumen de expiración
        $sql = "
            SELECT 
                COUNT(CASE WHEN expiry_date < CURDATE() THEN 1 END) as expired_lots,
                COUNT(CASE WHEN expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as expiring_soon,
                COUNT(CASE WHEN expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as expiring_month,
                SUM(CASE WHEN expiry_date < CURDATE() THEN quantity_available ELSE 0 END) as expired_quantity,
                SUM(CASE WHEN expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN quantity_available ELSE 0 END) as expiring_soon_quantity
            FROM production_lots
            WHERE production_date BETWEEN ? AND ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dateFrom, $dateTo]);
        $analysis['expiry_summary'] = $stmt->fetch();
        
        return $analysis;
    }
    
    public function getInventoryTurnoverReport($dateFrom, $dateTo) {
        $sql = "
            SELECT 
                p.id as product_id,
                p.code as product_code,
                p.name as product_name,
                p.category_id,
                
                -- Inventario inicial (aproximado)
                COALESCE(AVG(i_start.quantity), 0) as avg_starting_inventory,
                
                -- Inventario actual
                COALESCE(SUM(i.quantity), 0) as current_inventory,
                
                -- Cantidad vendida en el período
                COALESCE(SUM(dsd.quantity), 0) as quantity_sold,
                
                -- Cantidad producida en el período
                COALESCE(SUM(pl.total_quantity), 0) as quantity_produced,
                
                -- Cantidad devuelta
                COALESCE(SUM(ri.quantity_returned), 0) as quantity_returned,
                
                -- Cálculo de rotación (ventas / inventario promedio)
                CASE 
                    WHEN AVG(i_start.quantity) > 0 THEN 
                        ROUND(SUM(dsd.quantity) / AVG(i_start.quantity), 2)
                    ELSE 0 
                END as turnover_ratio,
                
                -- Días de inventario
                CASE 
                    WHEN SUM(dsd.quantity) > 0 THEN 
                        ROUND((AVG(i_start.quantity) / SUM(dsd.quantity)) * DATEDIFF(?, ?), 0)
                    ELSE NULL 
                END as days_of_inventory
                
            FROM products p
            LEFT JOIN inventory i ON p.id = i.product_id
            LEFT JOIN inventory i_start ON p.id = i_start.product_id
            LEFT JOIN direct_sale_details dsd ON p.id = dsd.product_id
            LEFT JOIN direct_sales ds ON dsd.sale_id = ds.id AND ds.sale_date BETWEEN ? AND ?
            LEFT JOIN production_lots pl ON p.id = pl.product_id AND pl.production_date BETWEEN ? AND ?
            LEFT JOIN return_items ri ON p.id = ri.product_id
            LEFT JOIN returns r ON ri.return_id = r.id AND r.return_date BETWEEN ? AND ?
            WHERE p.is_active = 1
            GROUP BY p.id
            ORDER BY turnover_ratio DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dateTo, $dateFrom, $dateFrom, $dateTo, $dateFrom, $dateTo, $dateFrom, $dateTo]);
        return $stmt->fetchAll();
    }
    
    public function getCustomerSegmentationReport() {
        $sql = "
            SELECT 
                c.id,
                c.business_name,
                c.city,
                COUNT(DISTINCT ds.id) as total_purchases,
                SUM(ds.total_amount) as total_spent,
                AVG(ds.total_amount) as average_purchase,
                MAX(ds.sale_date) as last_purchase_date,
                DATEDIFF(CURDATE(), MAX(ds.sale_date)) as days_since_last_purchase,
                COUNT(DISTINCT o.id) as total_orders,
                COUNT(DISTINCT ret.id) as total_returns,
                CASE 
                    WHEN SUM(ds.total_amount) >= 50000 THEN 'Premium'
                    WHEN SUM(ds.total_amount) >= 20000 THEN 'Frequent'
                    WHEN SUM(ds.total_amount) >= 5000 THEN 'Regular'
                    ELSE 'Occasional'
                END as customer_segment,
                CASE 
                    WHEN DATEDIFF(CURDATE(), MAX(ds.sale_date)) <= 30 THEN 'Active'
                    WHEN DATEDIFF(CURDATE(), MAX(ds.sale_date)) <= 90 THEN 'At Risk'
                    ELSE 'Inactive'
                END as activity_status
            FROM customers c
            LEFT JOIN direct_sales ds ON c.id = ds.customer_id
            LEFT JOIN orders o ON c.id = o.customer_id
            LEFT JOIN returns ret ON c.id = ret.customer_id
            WHERE c.is_active = 1
            GROUP BY c.id
            ORDER BY total_spent DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getFinancialSummary($dateFrom, $dateTo) {
        $summary = [];
        
        // Ingresos por canal
        $sql = "
            SELECT 
                'direct_sales' as channel,
                COUNT(*) as transaction_count,
                SUM(total_amount) as total_revenue,
                SUM(CASE WHEN payment_method = 'efectivo' THEN total_amount ELSE 0 END) as cash_revenue,
                SUM(CASE WHEN payment_method = 'credito' THEN total_amount ELSE 0 END) as credit_revenue,
                SUM(CASE WHEN payment_method = 'tarjeta' THEN total_amount ELSE 0 END) as card_revenue,
                SUM(CASE WHEN payment_method = 'transferencia' THEN total_amount ELSE 0 END) as transfer_revenue
            FROM direct_sales 
            WHERE sale_date BETWEEN ? AND ?
            
            UNION ALL
            
            SELECT 
                'deliveries' as channel,
                COUNT(*) as transaction_count,
                SUM(total_amount) as total_revenue,
                SUM(CASE WHEN payment_method = 'efectivo' THEN total_amount ELSE 0 END) as cash_revenue,
                SUM(CASE WHEN payment_method = 'credito' THEN total_amount ELSE 0 END) as credit_revenue,
                SUM(CASE WHEN payment_method = 'tarjeta' THEN total_amount ELSE 0 END) as card_revenue,
                SUM(CASE WHEN payment_method = 'transferencia' THEN total_amount ELSE 0 END) as transfer_revenue
            FROM orders 
            WHERE delivery_date BETWEEN ? AND ?
            AND status = 'entregado'
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dateFrom, $dateTo, $dateFrom, $dateTo]);
        $summary['by_channel'] = $stmt->fetchAll();
        
        // Cuentas por cobrar (créditos pendientes)
        $sql = "
            SELECT 
                c.business_name as customer_name,
                COUNT(*) as pending_transactions,
                SUM(total_amount) as pending_amount,
                MIN(sale_date) as oldest_transaction,
                MAX(sale_date) as newest_transaction
            FROM direct_sales ds
            JOIN customers c ON ds.customer_id = c.id
            WHERE payment_method = 'credito'
            AND payment_status IN ('pending', 'partial')
            GROUP BY ds.customer_id
            ORDER BY pending_amount DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $summary['accounts_receivable'] = $stmt->fetchAll();
        
        return $summary;
    }
    
    public function exportReport($reportType, $data, $format = 'pdf') {
        switch ($format) {
            case 'csv':
                return $this->exportToCSV($data, $reportType);
            case 'excel':
                return $this->exportToExcel($data, $reportType);
            case 'pdf':
                return $this->exportToPDF($data, $reportType);
            default:
                throw new Exception('Formato de exportación no soportado');
        }
    }
    
    private function exportToCSV($data, $reportType) {
        $filename = $reportType . '_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = '/tmp/' . $filename;
        
        $file = fopen($filepath, 'w');
        
        if (!empty($data)) {
            // Escribir encabezados
            fputcsv($file, array_keys($data[0]));
            
            // Escribir datos
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
        }
        
        fclose($file);
        return $filepath;
    }
    
    private function exportToPDF($data, $reportType) {
        // Implementar exportación a PDF usando una librería como TCPDF o FPDF
        // Por ahora retornamos el path donde se guardaría
        $filename = $reportType . '_' . date('Y-m-d_H-i-s') . '.pdf';
        return '/tmp/' . $filename;
    }
    
    private function exportToExcel($data, $reportType) {
        // Implementar exportación a Excel usando PhpSpreadsheet
        // Por ahora retornamos el path donde se guardaría
        $filename = $reportType . '_' . date('Y-m-d_H-i-s') . '.xlsx';
        return '/tmp/' . $filename;
    }
}