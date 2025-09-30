<?php
ob_start();
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-sm-6">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-shopping-cart text-primary me-2"></i>
                Gestión de Pedidos (Preventas)
            </h1>
            <p class="text-muted">Sistema de gestión de pedidos con seguimiento de entregas</p>
        </div>
        <div class="col-sm-6">
            <div class="d-flex justify-content-end">
                <a href="<?= BASE_URL ?>/pedidos/create" class="btn btn-primary me-2">
                    <i class="fas fa-plus"></i> Nueva Preventa
                </a>
                <button type="button" class="btn btn-outline-secondary" onclick="refreshData()">
                    <i class="fas fa-sync-alt"></i> Actualizar
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Pedidos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($order_stats['total_orders'] ?? 0) ?>
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
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Pendientes
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= number_format($order_stats['pending_orders'] ?? 0) ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                                    En Ruta
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= number_format($order_stats['in_route_orders'] ?? 0) ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-truck fa-2x text-gray-300"></i>
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
                                    Entregados
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= number_format($order_stats['delivered_orders'] ?? 0) ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>

    <!-- Delivery Alerts -->
    <?php if (!empty($deliveries_today) || !empty($deliveries_tomorrow)): ?>
    <div class="row mb-4">
            <?php if (!empty($deliveries_today)): ?>
            <div class="col-lg-6 mb-4">
                <div class="card border-left-danger shadow h-100">
                    <div class="card-header py-3 d-flex align-items-center">
                        <h6 class="m-0 font-weight-bold text-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Entregas Programadas HOY (<?= date('d/m/Y') ?>)
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php foreach ($deliveries_today as $delivery): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <strong><?= htmlspecialchars($delivery['order_number']) ?></strong> - 
                                <?= htmlspecialchars($delivery['customer_name']) ?>
                            </div>
                            <a href="<?= BASE_URL ?>/pedidos/viewOrder/<?= $delivery['id'] ?>" class="btn btn-sm btn-outline-primary">
                                Ver
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($deliveries_tomorrow)): ?>
            <div class="col-lg-6 mb-4">
                <div class="card border-left-warning shadow h-100">
                    <div class="card-header py-3 d-flex align-items-center">
                        <h6 class="m-0 font-weight-bold text-warning">
                            <i class="fas fa-calendar-alt me-2"></i>
                            Entregas Programadas MAÑANA (<?= date('d/m/Y', strtotime('+1 day')) ?>)
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php foreach ($deliveries_tomorrow as $delivery): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <strong><?= htmlspecialchars($delivery['order_number']) ?></strong> - 
                                <?= htmlspecialchars($delivery['customer_name']) ?>
                            </div>
                            <a href="<?= BASE_URL ?>/pedidos/viewOrder/<?= $delivery['id'] ?>" class="btn btn-sm btn-outline-primary">
                                Ver
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-filter me-2"></i>Filtros de Búsqueda
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?= BASE_URL ?>/pedidos" class="row g-3">
                    <div class="col-md-3">
                        <label for="status" class="form-label">Estado</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Todos los estados</option>
                            <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Pendiente</option>
                            <option value="confirmed" <?= $filters['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmado</option>
                            <option value="in_route" <?= $filters['status'] === 'in_route' ? 'selected' : '' ?>>En Ruta</option>
                            <option value="delivered" <?= $filters['status'] === 'delivered' ? 'selected' : '' ?>>Entregado</option>
                            <option value="cancelled" <?= $filters['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="customer_id" class="form-label">Cliente</label>
                        <select class="form-select" id="customer_id" name="customer_id">
                            <option value="">Todos los clientes</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?= $customer['id'] ?>" <?= $filters['customer_id'] == $customer['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($customer['business_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="date_from" class="form-label">Desde</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" value="<?= $filters['date_from'] ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="date_to" class="form-label">Hasta</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" value="<?= $filters['date_to'] ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                        <a href="<?= BASE_URL ?>/pedidos" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Lista de Pedidos</h6>
                <div class="d-flex">
                    <button class="btn btn-outline-success btn-sm me-2" onclick="exportToExcel()">
                        <i class="fas fa-file-excel"></i> Exportar
                    </button>
                    <button class="btn btn-outline-primary btn-sm" onclick="printList()">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="ordersTable" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th>Número</th>
                                <th>Cliente</th>
                                <th>Fecha Pedido</th>
                                <th>Fecha Entrega</th>
                                <th>Estado</th>
                                <th>Total</th>
                                <th>QR</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($orders)): ?>
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($order['order_number']) ?></strong>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?= htmlspecialchars($order['customer_name']) ?></strong>
                                            <?php if (!empty($order['contact_name'])): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($order['contact_name']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($order['order_date'])) ?></td>
                                    <td>
                                        <?php if ($order['delivery_date']): ?>
                                            <?= date('d/m/Y', strtotime($order['delivery_date'])) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Sin definir</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'confirmed' => 'info',
                                            'in_route' => 'primary',
                                            'delivered' => 'success',
                                            'cancelled' => 'danger'
                                        ];
                                        $statusLabels = [
                                            'pending' => 'Pendiente',
                                            'confirmed' => 'Confirmado',
                                            'in_route' => 'En Ruta',
                                            'delivered' => 'Entregado',
                                            'cancelled' => 'Cancelado'
                                        ];
                                        $statusColor = $statusColors[$order['status']] ?? 'secondary';
                                        $statusLabel = $statusLabels[$order['status']] ?? $order['status'];
                                        ?>
                                        <span class="badge bg-<?= $statusColor ?>"><?= $statusLabel ?></span>
                                    </td>
                                    <td>
                                        <strong>$<?= number_format($order['final_amount'], 2) ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($order['qr_code'])): ?>
                                            <button class="btn btn-sm btn-outline-primary" onclick="showQR('<?= $order['qr_code'] ?>')">
                                                <i class="fas fa-qrcode"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?= BASE_URL ?>/pedidos/viewOrder/<?= $order['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <?php if (in_array($order['status'], ['pending', 'confirmed'])): ?>
                                                <a href="<?= BASE_URL ?>/pedidos/edit/<?= $order['id'] ?>" 
                                                   class="btn btn-sm btn-outline-secondary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if ($order['status'] === 'pending'): ?>
                                                <button class="btn btn-sm btn-outline-success" 
                                                        onclick="updateStatus(<?= $order['id'] ?>, 'confirmed')" 
                                                        title="Confirmar">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($order['status'] === 'confirmed'): ?>
                                                <button class="btn btn-sm btn-outline-info" 
                                                        onclick="updateStatus(<?= $order['id'] ?>, 'in_route')" 
                                                        title="Marcar en ruta">
                                                    <i class="fas fa-truck"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($order['status'] === 'in_route'): ?>
                                                <a href="<?= BASE_URL ?>/pedidos/delivery/<?= $order['id'] ?>" 
                                                   class="btn btn-sm btn-outline-warning" title="Registrar entrega">
                                                    <i class="fas fa-clipboard-check"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if (in_array($order['status'], ['pending', 'confirmed'])): ?>
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        onclick="updateStatus(<?= $order['id'] ?>, 'cancelled')" 
                                                        title="Cancelar">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3"></i>
                                            <p>No hay pedidos registrados con los filtros seleccionados</p>
                                            <a href="<?= BASE_URL ?>/pedidos/create" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Crear Primer Pedido
                                            </a>
                                        </div>
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

<!-- QR Code Modal -->
<div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qrModalLabel">Código QR del Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div id="qrCodeContainer"></div>
                <p class="mt-3 text-muted">Escanee este código para verificar el pedido</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="printQR()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">Actualizar Estado del Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="statusForm">
                <div class="modal-body">
                    <input type="hidden" id="statusOrderId" name="order_id">
                    <input type="hidden" id="statusNewStatus" name="status">
                    
                    <div class="mb-3">
                        <label for="statusNotes" class="form-label">Notas (opcional)</label>
                        <textarea class="form-control" id="statusNotes" name="notes" rows="3" 
                                  placeholder="Agregue cualquier observación sobre el cambio de estado"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Estado</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Inicializar DataTable
    $('#ordersTable').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        },
        pageLength: 25,
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: false, targets: [6, 7] }
        ]
    });
});

function showQR(qrCode) {
    $('#qrCodeContainer').html('<img src="data:image/png;base64,' + qrCode + '" class="img-fluid" style="max-width: 250px;">');
    $('#qrModal').modal('show');
}

function updateStatus(orderId, newStatus) {
    $('#statusOrderId').val(orderId);
    $('#statusNewStatus').val(newStatus);
    
    const statusLabels = {
        'confirmed': 'Confirmar',
        'in_route': 'Marcar en Ruta',
        'delivered': 'Marcar como Entregado',
        'cancelled': 'Cancelar'
    };
    
    $('#statusModalLabel').text(statusLabels[newStatus] + ' Pedido');
    $('#statusModal').modal('show');
}

$('#statusForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    $.ajax({
        url: '<?= BASE_URL ?>/pedidos/updateStatus',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            $('#statusModal').modal('hide');
            showAlert('success', 'Estado actualizado correctamente');
            setTimeout(() => location.reload(), 1500);
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.error || 'Error al actualizar el estado';
            showAlert('error', error);
        }
    });
});

function refreshData() {
    location.reload();
}

function exportToExcel() {
    // Implementar exportación a Excel
    window.open('<?= BASE_URL ?>/pedidos/export?format=excel', '_blank');
}

function printList() {
    window.print();
}

function printQR() {
    const qrContent = document.getElementById('qrCodeContainer').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head><title>Código QR</title></head>
            <body style="text-align: center; padding: 20px;">
                ${qrContent}
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
    printWindow.close();
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    const alertContainer = document.querySelector('.content-wrapper');
    alertContainer.insertAdjacentHTML('afterbegin', alertHtml);
    
    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if (alert) alert.remove();
    }, 5000);
}
</script>

<style>
@media print {
    .btn, .card-header, .sidebar, .navbar {
        display: none !important;
    }
    
    .content-wrapper {
        margin: 0 !important;
        padding: 0 !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}

.border-left-primary {
    border-left: .25rem solid #4e73df!important;
}

.border-left-success {
    border-left: .25rem solid #1cc88a!important;
}

.border-left-info {
    border-left: .25rem solid #36b9cc!important;
}

.border-left-warning {
    border-left: .25rem solid #f6c23e!important;
}

.border-left-danger {
    border-left: .25rem solid #e74a3b!important;
}

.card.shadow {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15)!important;
}
</style>

<?php
$content = ob_get_clean();
require_once dirname(__DIR__) . '/layouts/main.php';
?>