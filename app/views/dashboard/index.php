<?php
ob_start();
?>

<div class="container-fluid">
    <!-- Header del Dashboard -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Dashboard Logístico</h1>
                    <p class="text-muted mb-0">Bienvenido, <?php echo htmlspecialchars($user_name); ?></p>
                </div>
                <div>
                    <span class="badge bg-primary"><?php echo ucfirst($user_role); ?></span>
                    <small class="text-muted ms-2"><?php echo date('d/m/Y H:i'); ?></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas del Sistema -->
    <?php if (!empty($alerts)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-warning">
                    <div class="card-header bg-warning bg-opacity-10">
                        <h5 class="mb-0">
                            <i class="fas fa-bell me-2"></i>
                            Alertas del Sistema
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($alerts as $alert): ?>
                            <div class="alert alert-<?php echo $alert['type']; ?> alert-dismissible fade show" role="alert">
                                <i class="fas <?php echo $alert['icon']; ?> me-2"></i>
                                <?php echo htmlspecialchars($alert['message']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Tarjetas de Estadísticas -->
    <div class="row mb-4">
        <!-- Estadísticas comunes -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Pedidos Hoy
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $stats['orders_today'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
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
                                Ingresos del Mes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?php echo number_format($stats['revenue_month'] ?? 0, 2); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                                <?php echo $stats['total_customers'] ?? 0; ?>
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
                                <?php echo $stats['pending_orders'] ?? 0; ?>
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

    <!-- Estadísticas específicas por rol -->
    <?php if (in_array($user_role, ['admin', 'manager'])): ?>
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Productos Stock Bajo
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo $stats['products_low_stock'] ?? 0; ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-secondary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                    Rutas Hoy
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo $stats['routes_today'] ?? 0; ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-route fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Contenido principal -->
    <div class="row">
        <!-- Gráfica de ventas -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Resumen de Ventas</h6>
                    <div class="dropdown no-arrow">
                        <select class="form-select form-select-sm" id="salesPeriod">
                            <option value="week">Esta Semana</option>
                            <option value="month" selected>Este Mes</option>
                            <option value="year">Este Año</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="salesChart" style="height: 320px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actividades recientes -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actividades Recientes</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_activities)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recent_activities as $activity): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold">
                                            <?php if ($activity['type'] === 'order'): ?>
                                                <i class="fas fa-shopping-cart text-primary me-1"></i>
                                                Pedido <?php echo $activity['reference']; ?>
                                            <?php else: ?>
                                                <i class="fas fa-cash-register text-success me-1"></i>
                                                Venta <?php echo $activity['reference']; ?>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y H:i', strtotime($activity['created_at'])); ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-<?php echo $activity['status'] === 'delivered' || $activity['status'] === 'paid' ? 'success' : 'warning'; ?> rounded-pill">
                                        <?php echo ucfirst($activity['status']); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">No hay actividades recientes</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones rápidas -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Acciones Rápidas</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php if ($this->hasPermission('orders')): ?>
                            <div class="col-md-3 mb-3">
                                <a href="<?php echo BASE_URL; ?>pedidos" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-plus me-2"></i>
                                    Nuevo Pedido
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($this->hasPermission('production')): ?>
                            <div class="col-md-3 mb-3">
                                <a href="<?php echo BASE_URL; ?>produccion" class="btn btn-success btn-lg w-100">
                                    <i class="fas fa-industry me-2"></i>
                                    Registrar Producción
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($this->hasPermission('routes')): ?>
                            <div class="col-md-3 mb-3">
                                <a href="<?php echo BASE_URL; ?>rutas" class="btn btn-info btn-lg w-100">
                                    <i class="fas fa-route me-2"></i>
                                    Planificar Ruta
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($this->hasPermission('reports')): ?>
                            <div class="col-md-3 mb-3">
                                <a href="<?php echo BASE_URL; ?>reportes" class="btn btn-secondary btn-lg w-100">
                                    <i class="fas fa-chart-bar me-2"></i>
                                    Ver Reportes
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Dashboard Charts for Admin -->
    <?php if (in_array($user_role, ['admin', 'manager'])): ?>
    <div class="row mb-4">
        <!-- Production Statistics Chart -->
        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-industry me-2"></i>
                        Producción (7 días)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="productionChart" style="height: 250px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Methods Chart -->
        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-credit-card me-2"></i>
                        Métodos de Pago
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="paymentChart" style="height: 250px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Route Efficiency Chart -->
        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-route me-2"></i>
                        Eficiencia Rutas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="routeChart" style="height: 250px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Configurar gráficas del dashboard
document.addEventListener('DOMContentLoaded', function() {
    // Chart.js configuration
    Chart.defaults.font.family = "'Nunito Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif";
    Chart.defaults.color = '#858796';

    // Sales Chart (existing but with real data)
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($charts_data['sales_by_day']['labels'] ?? ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom']); ?>,
            datasets: [{
                label: 'Ventas ($)',
                data: <?php echo json_encode($charts_data['sales_by_day']['data'] ?? [1200, 1900, 3000, 2500, 2000, 3000, 4500]); ?>,
                borderColor: 'rgb(78, 115, 223)',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    <?php if (in_array($user_role, ['admin', 'manager'])): ?>
    // Production Chart
    const productionCtx = document.getElementById('productionChart').getContext('2d');
    const productionChart = new Chart(productionCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($charts_data['production_stats']['labels'] ?? ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom']); ?>,
            datasets: [{
                label: 'Kg Producidos',
                data: <?php echo json_encode($charts_data['production_stats']['data'] ?? [150, 200, 180, 220, 300, 250, 180]); ?>,
                backgroundColor: 'rgba(28, 200, 138, 0.8)',
                borderColor: 'rgb(28, 200, 138)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value + ' kg';
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Payment Methods Chart
    const paymentCtx = document.getElementById('paymentChart').getContext('2d');
    const paymentData = <?php echo json_encode($charts_data['payment_methods'] ?? []); ?>;
    const paymentChart = new Chart(paymentCtx, {
        type: 'doughnut',
        data: {
            labels: paymentData.map(item => item.label),
            datasets: [{
                data: paymentData.map(item => item.value),
                backgroundColor: [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(255, 205, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)'
                ],
                borderColor: [
                    'rgb(54, 162, 235)',
                    'rgb(255, 99, 132)',
                    'rgb(255, 205, 86)',
                    'rgb(75, 192, 192)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const item = paymentData[context.dataIndex];
                            return context.label + ': ' + context.parsed + ' (' + '$' + item.amount.toLocaleString() + ')';
                        }
                    }
                }
            }
        }
    });

    // Route Efficiency Chart
    const routeCtx = document.getElementById('routeChart').getContext('2d');
    const routeData = <?php echo json_encode($charts_data['route_efficiency'] ?? []); ?>;
    const routeChart = new Chart(routeCtx, {
        type: 'bar',
        data: {
            labels: routeData.labels || ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
            datasets: [{
                label: 'Rutas Totales',
                data: routeData.total || [5, 7, 6, 8, 9, 6, 4],
                backgroundColor: 'rgba(255, 193, 7, 0.6)',
                borderColor: 'rgb(255, 193, 7)',
                borderWidth: 1
            }, {
                label: 'Rutas Completadas',
                data: routeData.completed || [4, 6, 5, 7, 8, 5, 3],
                backgroundColor: 'rgba(40, 167, 69, 0.8)',
                borderColor: 'rgb(40, 167, 69)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                }
            }
        }
    });
    <?php endif; ?>
    
    // Actualizar gráfica según período seleccionado
    document.getElementById('salesPeriod').addEventListener('change', function() {
        // Aquí se puede implementar la lógica para actualizar los datos
        console.log('Cambiar período a:', this.value);
    });
});
</script>

<?php
$content = ob_get_clean();
require_once dirname(__DIR__) . '/layouts/main.php';
?>