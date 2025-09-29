<?php
// Verificar sesión y permisos
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

// Obtener estadísticas
$salesModel = new Sale();
$stats = $salesModel->getSalesStats();
$sales = $salesModel->getAllSalesWithDetails();
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-shopping-cart"></i> Gestión de Ventas</h2>
                <button class="btn btn-success" id="newSaleBtn">
                    <i class="fas fa-plus"></i> Nueva Venta
                </button>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>$<?php echo number_format($stats['today_sales'] ?? 0, 2); ?></h4>
                            <small>Ventas Hoy</small>
                        </div>
                        <div>
                            <i class="fas fa-calendar-day fa-2x"></i>
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
                            <h4>$<?php echo number_format($stats['month_sales'] ?? 0, 2); ?></h4>
                            <small>Ventas del Mes</small>
                        </div>
                        <div>
                            <i class="fas fa-calendar-alt fa-2x"></i>
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
                            <h4><?php echo $stats['total_sales'] ?? 0; ?></h4>
                            <small>Total de Ventas</small>
                        </div>
                        <div>
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>$<?php echo number_format($stats['average_sale'] ?? 0, 2); ?></h4>
                            <small>Promedio por Venta</small>
                        </div>
                        <div>
                            <i class="fas fa-calculator fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Buscar por número</label>
                            <input type="text" class="form-control" id="searchNumber" placeholder="Número de venta">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha desde</label>
                            <input type="date" class="form-control" id="dateFrom">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha hasta</label>
                            <input type="date" class="form-control" id="dateTo">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Método de pago</label>
                            <select class="form-control" id="paymentMethod">
                                <option value="">Todos</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="tarjeta">Tarjeta</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="credito">Crédito</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <button class="btn btn-primary" id="filterBtn">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                            <button class="btn btn-secondary" id="clearFiltersBtn">
                                <i class="fas fa-times"></i> Limpiar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de ventas -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list"></i> Lista de Ventas</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="salesTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>Número</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Vendedor</th>
                                    <th>Método Pago</th>
                                    <th>Total</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sales as $sale): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($sale['sale_number']); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($sale['sale_date'])); ?>
                                    </td>
                                    <td>
                                        <?php if ($sale['customer_id']): ?>
                                            <div>
                                                <strong><?php echo htmlspecialchars($sale['customer_business_name']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($sale['customer_contact_name']); ?></small>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">Cliente General</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?php echo htmlspecialchars($sale['seller_name'] . ' ' . $sale['seller_lastname']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo match($sale['payment_method']) {
                                                'efectivo' => 'success',
                                                'tarjeta' => 'primary',
                                                'transferencia' => 'info',
                                                'credito' => 'warning',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?php echo ucfirst($sale['payment_method']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="text-success">$<?php echo number_format($sale['total_amount'], 2); ?></strong>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="viewSale(<?php echo $sale['id']; ?>)" 
                                                    title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success" 
                                                    onclick="printSale(<?php echo $sale['id']; ?>)" 
                                                    title="Imprimir">
                                                <i class="fas fa-print"></i>
                                            </button>
                                            <?php if (date('Y-m-d') == date('Y-m-d', strtotime($sale['sale_date']))): ?>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="cancelSale(<?php echo $sale['id']; ?>)" 
                                                    title="Cancelar">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nueva Venta -->
<div class="modal fade" id="newSaleModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus"></i> Nueva Venta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="saleModalContent">
                    <!-- Contenido se carga dinámicamente -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Venta -->
<div class="modal fade" id="viewSaleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-eye"></i> Detalles de Venta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="viewSaleContent">
                    <!-- Contenido se carga dinámicamente -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Inicializar DataTable
    $('#salesTable').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        },
        order: [[1, 'desc']],
        pageLength: 25,
        responsive: true
    });

    // Nueva venta
    $('#newSaleBtn').click(function() {
        loadNewSaleForm();
    });

    // Filtros
    $('#filterBtn').click(function() {
        applyFilters();
    });

    $('#clearFiltersBtn').click(function() {
        clearFilters();
    });
});

function loadNewSaleForm() {
    $.get('/sales/create', function(data) {
        $('#saleModalContent').html(data);
        $('#newSaleModal').modal('show');
    }).fail(function() {
        Swal.fire('Error', 'No se pudo cargar el formulario de nueva venta', 'error');
    });
}

function viewSale(saleId) {
    $.get('/sales/view/' + saleId, function(data) {
        $('#viewSaleContent').html(data);
        $('#viewSaleModal').modal('show');
    }).fail(function() {
        Swal.fire('Error', 'No se pudo cargar los detalles de la venta', 'error');
    });
}

function printSale(saleId) {
    window.open('/sales/print/' + saleId, '_blank');
}

function cancelSale(saleId) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción cancelará la venta y devolverá el inventario',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('/sales/cancel/' + saleId, function(response) {
                if (response.success) {
                    Swal.fire('Cancelada', 'La venta ha sido cancelada', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            });
        }
    });
}

function applyFilters() {
    const table = $('#salesTable').DataTable();
    
    // Aplicar filtros personalizados
    const searchNumber = $('#searchNumber').val();
    const dateFrom = $('#dateFrom').val();
    const dateTo = $('#dateTo').val();
    const paymentMethod = $('#paymentMethod').val();
    
    // Recargar tabla con filtros
    table.search('').draw();
    
    if (searchNumber) {
        table.column(0).search(searchNumber).draw();
    }
    
    // Para fechas y métodos de pago, necesitaríamos implementar filtros personalizados
    // o usar server-side processing
}

function clearFilters() {
    $('#searchNumber').val('');
    $('#dateFrom').val('');
    $('#dateTo').val('');
    $('#paymentMethod').val('');
    
    const table = $('#salesTable').DataTable();
    table.search('').columns().search('').draw();
}
</script>