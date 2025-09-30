<?php
// Verificar sesión y permisos
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

// Verificar permisos para reportes
if (!in_array($_SESSION['user_role'], ['admin', 'manager'])) {
    header('Location: /dashboard');
    exit;
}
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-chart-bar"></i> Reportes del Sistema</h2>
                <div class="btn-group">
                    <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-download"></i> Exportar
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="exportReport('pdf')"><i class="fas fa-file-pdf"></i> PDF</a></li>
                        <li><a class="dropdown-item" href="#" onclick="exportReport('excel')"><i class="fas fa-file-excel"></i> Excel</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen de Reportes -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?php echo $sales_summary['total_sales'] ?? 0; ?></h4>
                            <small>Ventas Totales</small>
                        </div>
                        <div>
                            <i class="fas fa-shopping-cart fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>$<?php echo number_format($sales_summary['total_revenue'] ?? 0, 2); ?></h4>
                            <small>Ingresos Totales</small>
                        </div>
                        <div>
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?php echo $inventory_summary['total_products'] ?? 0; ?></h4>
                            <small>Productos en Stock</small>
                        </div>
                        <div>
                            <i class="fas fa-boxes fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?php echo $customer_summary['total_customers'] ?? 0; ?></h4>
                            <small>Clientes Activos</small>
                        </div>
                        <div>
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tipos de Reportes -->
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-line"></i> Reportes de Ventas</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Análisis completo de ventas, ingresos y tendencias por período.</p>
                    <div class="d-grid gap-2">
                        <a href="/reports/sales" class="btn btn-primary">
                            <i class="fas fa-eye"></i> Ver Reporte
                        </a>
                        <a href="/reports/sales?export=pdf" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-download"></i> Descargar PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-warehouse"></i> Reportes de Inventario</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Control de stock, movimientos y productos por vencer.</p>
                    <div class="d-grid gap-2">
                        <a href="/reports/inventory" class="btn btn-success">
                            <i class="fas fa-eye"></i> Ver Reporte
                        </a>
                        <a href="/reports/inventory?export=pdf" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-download"></i> Descargar PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-dollar-sign"></i> Reportes Financieros</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Estados financieros, flujo de caja y análisis de rentabilidad.</p>
                    <div class="d-grid gap-2">
                        <a href="/reports/financial" class="btn btn-warning">
                            <i class="fas fa-eye"></i> Ver Reporte
                        </a>
                        <a href="/reports/financial?export=pdf" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-download"></i> Descargar PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-truck"></i> Reportes de Logística</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Eficiencia de rutas, entregas y análisis de transporte.</p>
                    <div class="d-grid gap-2">
                        <a href="/reports/logistics" class="btn btn-info">
                            <i class="fas fa-eye"></i> Ver Reporte
                        </a>
                        <a href="/reports/logistics?export=pdf" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-download"></i> Descargar PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-users"></i> Reportes de Clientes</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Análisis de comportamiento y satisfacción de clientes.</p>
                    <div class="d-grid gap-2">
                        <a href="/reports/customers" class="btn btn-secondary">
                            <i class="fas fa-eye"></i> Ver Reporte
                        </a>
                        <a href="/reports/customers?export=pdf" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-download"></i> Descargar PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-cogs"></i> Reportes de Producción</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Análisis de lotes de producción y calidad.</p>
                    <div class="d-grid gap-2">
                        <a href="/reports/production" class="btn btn-dark">
                            <i class="fas fa-eye"></i> Ver Reporte
                        </a>
                        <a href="/reports/production?export=pdf" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-download"></i> Descargar PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros de Fecha -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-filter"></i> Filtros de Reportes</h5>
                </div>
                <div class="card-body">
                    <form id="reportFilters" class="row g-3">
                        <div class="col-md-3">
                            <label for="dateFrom" class="form-label">Fecha Desde</label>
                            <input type="date" class="form-control" id="dateFrom" name="date_from" value="<?php echo date('Y-m-01'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="dateTo" class="form-label">Fecha Hasta</label>
                            <input type="date" class="form-control" id="dateTo" name="date_to" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="reportType" class="form-label">Tipo de Reporte</label>
                            <select class="form-select" id="reportType" name="report_type">
                                <option value="">Todos los reportes</option>
                                <option value="sales">Ventas</option>
                                <option value="inventory">Inventario</option>
                                <option value="financial">Financiero</option>
                                <option value="logistics">Logística</option>
                                <option value="customers">Clientes</option>
                                <option value="production">Producción</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Aplicar Filtros
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportReport(format) {
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    const reportType = document.getElementById('reportType').value;
    
    let url = `/reports/export?format=${format}&date_from=${dateFrom}&date_to=${dateTo}`;
    if (reportType) {
        url += `&type=${reportType}`;
    }
    
    window.open(url, '_blank');
}

document.getElementById('reportFilters').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const params = new URLSearchParams(formData);
    window.location.href = `/reports?${params.toString()}`;
});
</script>