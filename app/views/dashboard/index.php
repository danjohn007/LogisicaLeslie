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

    <!-- Nuevas Gráficas Adicionales -->
    <div class="row mt-4">
        <!-- Gráfica de Inventario por Producto -->
        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-boxes me-2"></i>
                        Stock por Producto (Top 10)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="inventoryChart" style="height: 280px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfica de Entregas por Estado -->
        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-truck me-2"></i>
                        Estado de Entregas (Último Mes)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="deliveryChart" style="height: 280px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfica de Tendencia de Ventas por Cliente -->
        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-users me-2"></i>
                        Top 5 Clientes (Este Mes)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="customersChart" style="height: 280px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficas de Análisis Financiero -->
    <div class="row mt-4">
        <!-- Gráfica de Ingresos vs Gastos -->
        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-purple">
                        <i class="fas fa-chart-bar me-2"></i>
                        Análisis Financiero Mensual
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="financialChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfica de Eficiencia de Rutas -->
        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">
                        <i class="fas fa-route me-2"></i>
                        Eficiencia de Rutas (Últimas 2 Semanas)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="routeEfficiencyChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Configurar gráfica de ventas
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    
    const salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
            datasets: [{
                label: 'Ventas ($)',
                data: [1200, 1900, 3000, 2500, 2000, 3000, 4500],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.1
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
    
    // Actualizar gráfica según período seleccionado
    document.getElementById('salesPeriod').addEventListener('change', function() {
        // Aquí se puede implementar la lógica para actualizar los datos
        console.log('Cambiar período a:', this.value);
    });

    // Gráfica de Inventario por Producto (Donut Chart)
    const inventoryCtx = document.getElementById('inventoryChart').getContext('2d');
    const inventoryChart = new Chart(inventoryCtx, {
        type: 'doughnut',
        data: {
            labels: ['Queso Fresco', 'Queso Oaxaca', 'Crema', 'Yogurt', 'Mantequilla', 'Otros'],
            datasets: [{
                data: [45, 30, 15, 25, 12, 8],
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed + ' unidades';
                        }
                    }
                }
            }
        }
    });

    // Gráfica de Estado de Entregas (Pie Chart)
    const deliveryCtx = document.getElementById('deliveryChart').getContext('2d');
    const deliveryChart = new Chart(deliveryCtx, {
        type: 'pie',
        data: {
            labels: ['Entregadas', 'En Ruta', 'Pendientes', 'Fallidas'],
            datasets: [{
                data: [75, 15, 8, 2],
                backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#dc3545'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // Gráfica de Top 5 Clientes (Bar Chart Horizontal)
    const customersCtx = document.getElementById('customersChart').getContext('2d');
    const customersChart = new Chart(customersCtx, {
        type: 'bar',
        data: {
            labels: ['Tienda El Sol', 'Super López', 'Abarrotes María', 'Mini Market', 'Comercial Norte'],
            datasets: [{
                label: 'Ventas ($)',
                data: [12500, 9800, 8200, 7500, 6900],
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
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
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Ventas: $' + context.parsed.x.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Gráfica de Análisis Financiero (Mixed Chart)
    const financialCtx = document.getElementById('financialChart').getContext('2d');
    const financialChart = new Chart(financialCtx, {
        type: 'bar',
        data: {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
            datasets: [{
                label: 'Ingresos',
                data: [45000, 52000, 48000, 61000, 58000, 67000],
                backgroundColor: 'rgba(75, 192, 192, 0.8)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }, {
                label: 'Gastos',
                data: [35000, 38000, 36000, 42000, 41000, 45000],
                backgroundColor: 'rgba(255, 99, 132, 0.8)',
                borderColor: 'rgba(255, 99, 132, 1)',
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
                            return '$' + (value/1000) + 'k';
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': $' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Gráfica de Eficiencia de Rutas (Line Chart)
    const routeCtx = document.getElementById('routeEfficiencyChart').getContext('2d');
    const routeChart = new Chart(routeCtx, {
        type: 'line',
        data: {
            labels: ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5', 'Sem 6', 'Sem 7', 'Sem 8'],
            datasets: [{
                label: 'Entregas Exitosas (%)',
                data: [85, 92, 88, 95, 90, 93, 89, 96],
                borderColor: 'rgba(40, 167, 69, 1)',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.3,
                fill: true
            }, {
                label: 'Puntualidad (%)',
                data: [78, 85, 82, 88, 86, 90, 87, 92],
                borderColor: 'rgba(255, 193, 7, 1)',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
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
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y + '%';
                        }
                    }
                }
            }
        }
    });
});
</script>

<?php
$content = ob_get_clean();
require_once dirname(__DIR__) . '/layouts/main.php';
?>