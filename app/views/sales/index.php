<?php
ob_start();
?>

<!-- Contenido principal respetando el sidebar -->
<div class="content-container">
    <!-- Header -->
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="h4 mb-0 text-gray-800">
                <i class="fas fa-shopping-cart text-success me-2"></i>
                Gestión de Ventas
            </h1>
            <p class="text-muted mb-0 small">Sistema de registro y seguimiento de ventas directas</p>
        </div>
        <div class="col-sm-6">
            <div class="d-flex justify-content-end">
                <button class="btn btn-success btn-sm me-2" id="newSaleBtn">
                    <i class="fas fa-plus"></i> Nueva Venta
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="refreshData()">
                    <i class="fas fa-sync-alt"></i> Actualizar
                </button>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-2">
        <div class="col-xl-3 col-md-6 mb-2">
            <div class="card border-left-info shadow h-100 py-1">
                <div class="card-body py-2">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Ventas Hoy
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format($sales_stats['today_sales'] ?? 0, 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-lg text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-2">
            <div class="card border-left-success shadow h-100 py-1">
                <div class="card-body py-2">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Ventas del Mes
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format($sales_stats['month_sales'] ?? 0, 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-lg text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-2">
            <div class="card border-left-warning shadow h-100 py-1">
                <div class="card-body py-2">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total de Ventas
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($sales_stats['total_sales'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-lg text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-2">
            <div class="card border-left-primary shadow h-100 py-1">
                <div class="card-body py-2">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Promedio por Venta
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format($sales_stats['average_sale'] ?? 0, 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calculator fa-lg text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body py-2">
                    <form method="GET" action="<?= BASE_URL ?>/ventas" class="row g-2">
                    <div class="col-md-3">
                        <label for="searchNumber" class="form-label">Buscar por número</label>
                        <input type="text" class="form-control" id="searchNumber" name="search_number" 
                               placeholder="Número de venta" value="<?= htmlspecialchars($filters['search_number'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="dateFrom" class="form-label">Fecha desde</label>
                        <input type="date" class="form-control" id="dateFrom" name="date_from" 
                               value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="dateTo" class="form-label">Fecha hasta</label>
                        <input type="date" class="form-control" id="dateTo" name="date_to" 
                               value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="paymentMethod" class="form-label">Método de pago</label>
                        <select class="form-select" id="paymentMethod" name="payment_method">
                            <option value="">Todos</option>
                            <option value="cash" <?= ($filters['payment_method'] ?? '') === 'cash' ? 'selected' : '' ?>>Efectivo</option>
                            <option value="card" <?= ($filters['payment_method'] ?? '') === 'card' ? 'selected' : '' ?>>Tarjeta</option>
                            <option value="transfer" <?= ($filters['payment_method'] ?? '') === 'transfer' ? 'selected' : '' ?>>Transferencia</option>
                            <option value="credit" <?= ($filters['payment_method'] ?? '') === 'credit' ? 'selected' : '' ?>>Crédito</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-sm" id="filterBtn">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="<?= BASE_URL ?>/ventas" class="btn btn-secondary btn-sm" id="clearFiltersBtn">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="exportSales()">
                            <i class="fas fa-file-excel"></i> Exportar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Tabla de ventas -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-2">
                    <h6 class="m-0 font-weight-bold text-primary">Lista de Ventas</h6>
                </div>
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-sm" id="salesTable">
                        <thead class="table-light">
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
                            <?php if (!empty($sales)): ?>
                                <?php foreach ($sales as $sale): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($sale['sale_number']) ?></strong>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($sale['sale_date'])) ?>
                                        <br><small class="text-muted"><?= date('H:i', strtotime($sale['created_at'])) ?></small>
                                    </td>
                                    <td>
                                        <?php if (!empty($sale['customer_id'])): ?>
                                            <div>
                                                <strong><?= htmlspecialchars($sale['customer_business_name'] ?? 'N/A') ?></strong><br>
                                                <small class="text-muted"><?= htmlspecialchars($sale['customer_contact_name'] ?? '') ?></small>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">Cliente General</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?= htmlspecialchars(($sale['seller_name'] ?? '') . ' ' . ($sale['seller_lastname'] ?? '')) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $paymentBadge = [
                                            'cash' => 'bg-success',
                                            'card' => 'bg-primary',
                                            'transfer' => 'bg-info',
                                            'credit' => 'bg-warning',
                                        ];
                                        $method = $sale['payment_method'] ?? 'cash';
                                        ?>
                                        <span class="badge <?= $paymentBadge[$method] ?? 'bg-secondary' ?>">
                                            <?= ucfirst($method) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="text-success">$<?= number_format($sale['total_amount'], 2) ?></strong>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-info" 
                                                    onclick="viewSale(<?= $sale['id'] ?>)" 
                                                    title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-success" 
                                                    onclick="printSale(<?= $sale['id'] ?>)" 
                                                    title="Imprimir">
                                                <i class="fas fa-print"></i>
                                            </button>
                                            <?php if (date('Y-m-d') == date('Y-m-d', strtotime($sale['sale_date']))): ?>
                                            <button class="btn btn-sm btn-danger" 
                                                    onclick="cancelSale(<?= $sale['id'] ?>)" 
                                                    title="Cancelar">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>No hay ventas registradas con los filtros aplicados</p>
                                        <button class="btn btn-success" id="newSaleBtn2">
                                            <i class="fas fa-plus"></i> Crear Primera Venta
                                        </button>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Final del content-container -->

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

    // Nueva venta - ambos botones
    $('#newSaleBtn, #newSaleBtn2').click(function() {
        loadNewSaleForm();
    });
});

function refreshData() {
    location.reload();
}

function loadNewSaleForm() {
    $.get('<?= BASE_URL ?>/ventas/create', function(data) {
        $('#saleModalContent').html(data);
        $('#newSaleModal').modal('show');
    }).fail(function() {
        Swal.fire('Error', 'No se pudo cargar el formulario de nueva venta', 'error');
    });
}

function viewSale(saleId) {
    $.get('<?= BASE_URL ?>/ventas/viewSale/' + saleId, function(data) {
        $('#viewSaleContent').html(data);
        $('#viewSaleModal').modal('show');
    }).fail(function() {
        Swal.fire('Error', 'No se pudo cargar los detalles de la venta', 'error');
    });
}

function printSale(saleId) {
    window.open('<?= BASE_URL ?>/ventas/print/' + saleId, '_blank');
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
            $.ajax({
                url: '<?= BASE_URL ?>/ventas/cancel/' + saleId,
                method: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Cancelada', 'La venta ha sido cancelada', 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message || 'No se pudo cancelar la venta', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'No se pudo cancelar la venta', 'error');
                }
            });
        }
    });
}

function exportSales() {
    Swal.fire({
        title: 'Exportar Ventas',
        text: 'Se generará un archivo Excel con las ventas',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Exportar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?= BASE_URL ?>/ventas/export';
        }
    });
}
</script>

<style>
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.card.shadow, .card.shadow-sm {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}
.card.shadow-sm {
    box-shadow: 0 0.125rem 0.25rem 0 rgba(58, 59, 69, 0.2) !important;
}

/* Estilos específicos para espaciado compacto */
.content-container .row.mb-2 {
    margin-bottom: 0.5rem !important;
}
.content-container .card-body.py-2 {
    padding-top: 0.5rem !important;
    padding-bottom: 0.5rem !important;
}
.content-container .card-body.p-2 {
    padding: 0.5rem !important;
}
.content-container .card-header.py-2 {
    padding-top: 0.5rem !important;
    padding-bottom: 0.5rem !important;
}
.content-container .table-sm th,
.content-container .table-sm td {
    padding: 0.25rem !important;
}
</style>

<?php
$content = ob_get_clean();
require_once dirname(__DIR__) . '/layouts/main.php';
?>