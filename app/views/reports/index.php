<?php
ob_start();
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Reportes del Sistema</h1>
                    <p class="text-muted mb-0">Informes y análisis de datos del negocio</p>
                </div>
                <div>
                    <div class="btn-group" role="group">
                        <a href="<?php echo BASE_URL; ?>reportes/sales" class="btn btn-primary">
                            <i class="fas fa-chart-line me-2"></i>
                            Ventas
                        </a>
                        <a href="<?php echo BASE_URL; ?>reportes/inventory" class="btn btn-success">
                            <i class="fas fa-boxes me-2"></i>
                            Inventario
                        </a>
                        <a href="<?php echo BASE_URL; ?>reportes/financial" class="btn btn-warning">
                            <i class="fas fa-dollar-sign me-2"></i>
                            Financiero
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen de Reportes -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Ventas del Mes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?php echo number_format($sales_summary['monthly_sales'] ?? 0, 2); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Productos en Stock
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $inventory_summary['total_products'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Clientes Activos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $customer_summary['active_customers'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pedidos Pendientes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $sales_summary['pending_orders'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tipos de Reportes -->
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line me-2"></i>
                        Reportes de Ventas
                    </h6>
                </div>
                <div class="card-body">
                    <p class="card-text">Análisis detallado de ventas por período, productos y clientes.</p>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success me-2"></i>Ventas por período</li>
                        <li><i class="fas fa-check text-success me-2"></i>Productos más vendidos</li>
                        <li><i class="fas fa-check text-success me-2"></i>Análisis por cliente</li>
                        <li><i class="fas fa-check text-success me-2"></i>Tendencias de ventas</li>
                    </ul>
                    <a href="<?php echo BASE_URL; ?>reportes/sales" class="btn btn-primary btn-sm">
                        Ver Reportes <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-boxes me-2"></i>
                        Reportes de Inventario
                    </h6>
                </div>
                <div class="card-body">
                    <p class="card-text">Control y seguimiento de inventario y movimientos de stock.</p>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success me-2"></i>Stock actual</li>
                        <li><i class="fas fa-check text-success me-2"></i>Productos con stock bajo</li>
                        <li><i class="fas fa-check text-success me-2"></i>Productos próximos a vencer</li>
                        <li><i class="fas fa-check text-success me-2"></i>Movimientos de inventario</li>
                    </ul>
                    <a href="<?php echo BASE_URL; ?>reportes/inventory" class="btn btn-success btn-sm">
                        Ver Reportes <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-dollar-sign me-2"></i>
                        Reportes Financieros
                    </h6>
                </div>
                <div class="card-body">
                    <p class="card-text">Análisis financiero y de rentabilidad del negocio.</p>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success me-2"></i>Ingresos y gastos</li>
                        <li><i class="fas fa-check text-success me-2"></i>Rentabilidad por producto</li>
                        <li><i class="fas fa-check text-success me-2"></i>Métodos de pago</li>
                        <li><i class="fas fa-check text-success me-2"></i>Flujo de caja</li>
                    </ul>
                    <a href="<?php echo BASE_URL; ?>reportes/financial" class="btn btn-warning btn-sm">
                        Ver Reportes <i class="fas fa-arrow-right ms-1"></i>
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