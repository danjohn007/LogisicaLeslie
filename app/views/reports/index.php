<?php
ob_start();
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-chart-bar me-2 text-primary"></i>
                        Reportes del Sistema
                    </h1>
                    <p class="text-muted mb-0">Panel de reportes y estadísticas generales</p>
                </div>
                <div>
                    <span class="badge bg-primary"><?php echo ucfirst($user_role); ?></span>
                    <small class="text-muted ms-2"><?php echo date('d/m/Y H:i'); ?></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen de Ventas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-success bg-opacity-10">
                    <h5 class="mb-0">
                        <i class="fas fa-dollar-sign me-2 text-success"></i>
                        Resumen de Ventas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-success">$<?php echo number_format($sales_summary['total_revenue'] ?? 0, 2); ?></h4>
                                <p class="text-muted mb-0">Ingresos Totales</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-info"><?php echo number_format($sales_summary['total_sales'] ?? 0); ?></h4>
                                <p class="text-muted mb-0">Ventas Totales</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-warning">$<?php echo number_format($sales_summary['today_revenue'] ?? 0, 2); ?></h4>
                                <p class="text-muted mb-0">Ingresos Hoy</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-primary"><?php echo number_format($sales_summary['today_sales'] ?? 0); ?></h4>
                                <p class="text-muted mb-0">Ventas Hoy</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen de Inventario -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-warning bg-opacity-10">
                    <h5 class="mb-0">
                        <i class="fas fa-boxes me-2 text-warning"></i>
                        Resumen de Inventario
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <h4 class="text-info"><?php echo number_format($inventory_summary['total_products'] ?? 0); ?></h4>
                                <p class="text-muted mb-0">Productos Totales</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h4 class="text-danger"><?php echo number_format($inventory_summary['low_stock'] ?? 0); ?></h4>
                                <p class="text-muted mb-0">Stock Bajo</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h4 class="text-warning"><?php echo number_format($inventory_summary['expiring_soon'] ?? 0); ?></h4>
                                <p class="text-muted mb-0">Por Vencer</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen de Clientes -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-info bg-opacity-10">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2 text-info"></i>
                        Resumen de Clientes
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <h4 class="text-info"><?php echo number_format($customer_summary['total_customers'] ?? 0); ?></h4>
                                <p class="text-muted mb-0">Clientes Totales</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h4 class="text-success"><?php echo number_format($customer_summary['active_customers'] ?? 0); ?></h4>
                                <p class="text-muted mb-0">Clientes Activos</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h4 class="text-warning"><?php echo number_format($customer_summary['new_this_month'] ?? 0); ?></h4>
                                <p class="text-muted mb-0">Nuevos Este Mes</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Accesos Rápidos a Reportes Específicos -->
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <div class="text-success mb-3">
                        <i class="fas fa-chart-line fa-3x"></i>
                    </div>
                    <h5 class="card-title">Reportes de Ventas</h5>
                    <p class="card-text text-muted">Análisis detallado de ventas por período, productos y clientes</p>
                    <a href="<?php echo BASE_URL; ?>reports/sales" class="btn btn-success">
                        <i class="fas fa-chart-line me-1"></i>
                        Ver Reportes
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <div class="text-warning mb-3">
                        <i class="fas fa-warehouse fa-3x"></i>
                    </div>
                    <h5 class="card-title">Reportes de Inventario</h5>
                    <p class="card-text text-muted">Estado del inventario, movimientos y alertas de stock</p>
                    <a href="<?php echo BASE_URL; ?>reports/inventory" class="btn btn-warning">
                        <i class="fas fa-warehouse me-1"></i>
                        Ver Reportes
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <div class="text-info mb-3">
                        <i class="fas fa-calculator fa-3x"></i>
                    </div>
                    <h5 class="card-title">Reportes Financieros</h5>
                    <p class="card-text text-muted">Análisis financiero, ingresos y métodos de pago</p>
                    <a href="<?php echo BASE_URL; ?>reports/financial" class="btn btn-info">
                        <i class="fas fa-calculator me-1"></i>
                        Ver Reportes
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once dirname(__DIR__) . '/layouts/main.php';
?>