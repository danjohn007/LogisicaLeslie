<?php
ob_start();
?>

<div class="content-wrapper">
    <!-- Header -->
    <div class="content-header py-1">
        <div class="container-fluid">
            <div class="row mb-1">
                <div class="col-sm-6">
                    <h1 class="h4 mb-0 text-gray-800">
                        <i class="fas fa-receipt text-primary me-2"></i>
                        Pedido <?= htmlspecialchars($order['order_number']) ?>
                    </h1>
                    <p class="text-muted mb-0 small">
                        Creado el <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                        <?php if (!empty($order['created_by_name'])): ?>
                            por <?= htmlspecialchars($order['created_by_name'] . ' ' . $order['created_by_lastname']) ?>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-sm-6">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= BASE_URL ?>/pedidos" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver a Lista
                        </a>
                        
                        <?php if ($can_edit): ?>
                            <a href="<?= BASE_URL ?>/pedidos/edit/<?= $order['id'] ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                        <?php endif; ?>
                        
                        <button class="btn btn-outline-success btn-sm" onclick="printOrder()">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                        
                        <?php if (!empty($order['qr_code'])): ?>
                            <button class="btn btn-outline-info btn-sm" onclick="showQRCode()">
                                <i class="fas fa-qrcode"></i> Código QR
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid px-2">
        <div class="row">
            <!-- Order Details -->
            <div class="col-lg-8">
                <!-- Customer Information -->
                <div class="card shadow mb-2">
                    <div class="card-header py-1">
                        <h6 class="m-0 font-weight-bold text-primary small">
                            <i class="fas fa-user me-2"></i>
                            Información del Cliente
                        </h6>
                    </div>
                    <div class="card-body py-2">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-1"><?= htmlspecialchars($order['business_name']) ?></h5>
                                <?php if (!empty($order['contact_name'])): ?>
                                    <p class="text-muted mb-1">
                                        <i class="fas fa-user-circle me-1"></i>
                                        Contacto: <?= htmlspecialchars($order['contact_name']) ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($order['phone'])): ?>
                                    <p class="text-muted mb-1">
                                        <i class="fas fa-phone me-1"></i>
                                        <a href="tel:<?= htmlspecialchars($order['phone']) ?>">
                                            <?= htmlspecialchars($order['phone']) ?>
                                        </a>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($order['address'])): ?>
                                    <p class="text-muted mb-0">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?= htmlspecialchars($order['address']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <small class="text-muted">Método de Pago:</small>
                                        <p class="mb-1">
                                            <span class="badge bg-info">
                                                <?= ucfirst(str_replace('_', ' ', $order['payment_method'])) ?>
                                            </span>
                                        </p>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted">Estado de Pago:</small>
                                        <p class="mb-1">
                                            <span class="badge bg-<?= $order['payment_status'] === 'paid' ? 'success' : 'warning' ?>">
                                                <?= ucfirst(str_replace('_', ' ', $order['payment_status'])) ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <?php if (!empty($order['city'])): ?>
                                    <small class="text-muted">Ciudad:</small>
                                    <p class="mb-0"><?= htmlspecialchars($order['city']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="card shadow mb-2">
                    <div class="card-header py-1">
                        <h6 class="m-0 font-weight-bold text-primary small">
                            <i class="fas fa-shopping-cart me-2"></i>
                            Productos del Pedido
                        </h6>
                    </div>
                    <div class="card-body py-2">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th width="12%">Cantidad Pedida</th>
                                        <th width="12%">Cantidad Entregada</th>
                                        <th width="12%">Precio Unitario</th>
                                        <th width="12%">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($order['details'])): ?>
                                        <?php foreach ($order['details'] as $detail): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($detail['product_name']) ?></strong>
                                                <?php if (!empty($detail['product_code'])): ?>
                                                    <br><small class="text-muted">Código: <?= htmlspecialchars($detail['product_code']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?= number_format($detail['quantity_ordered'], 2) ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($order['status'] === 'delivered'): ?>
                                                    <span class="text-success">
                                                        <?= number_format($detail['quantity_delivered'] ?? $detail['quantity_ordered'], 2) ?>
                                                    </span>
                                                <?php elseif ($detail['quantity_delivered'] > 0): ?>
                                                    <span class="text-warning">
                                                        <?= number_format($detail['quantity_delivered'], 2) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">Pendiente</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                $<?= number_format($detail['unit_price'], 2) ?>
                                            </td>
                                            <td class="text-end">
                                                $<?= number_format($detail['subtotal'], 2) ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">
                                                No hay productos en este pedido
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot class="table-info">
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                        <td class="text-end"><strong>$<?= number_format($order['total_amount'], 2) ?></strong></td>
                                    </tr>
                                    <?php if ($order['discount_amount'] > 0): ?>
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Descuento:</strong></td>
                                        <td class="text-end"><strong>-$<?= number_format($order['discount_amount'], 2) ?></strong></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr class="table-success">
                                        <td colspan="4" class="text-end"><strong>Total Final:</strong></td>
                                        <td class="text-end"><strong>$<?= number_format($order['final_amount'], 2) ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Order Notes -->
                <?php if (!empty($order['notes'])): ?>
                <div class="card shadow mb-2">
                    <div class="card-header py-1">
                        <h6 class="m-0 font-weight-bold text-primary small">
                            <i class="fas fa-sticky-note me-2"></i>
                            Notas del Pedido
                        </h6>
                    </div>
                    <div class="card-body py-2">
                        <p class="mb-0"><?= nl2br(htmlspecialchars($order['notes'])) ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Status History -->
                <?php if (!empty($order['status_history'])): ?>
                <div class="card shadow mb-2">
                    <div class="card-header py-1">
                        <h6 class="m-0 font-weight-bold text-primary small">
                            <i class="fas fa-history me-2"></i>
                            Historial de Estados
                        </h6>
                    </div>
                    <div class="card-body py-2">
                        <div class="timeline">
                            <?php foreach ($order['status_history'] as $history): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-<?= $history['status'] === 'delivered' ? 'success' : 'primary' ?>"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1"><?= ucfirst(str_replace('_', ' ', $history['status'])) ?></h6>
                                    <p class="text-muted mb-1"><?= date('d/m/Y H:i', strtotime($history['created_at'])) ?></p>
                                    <?php if (!empty($history['notes'])): ?>
                                        <p class="mb-0"><?= htmlspecialchars($history['notes']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($history['changed_by_name'])): ?>
                                        <small class="text-muted">Por: <?= htmlspecialchars($history['changed_by_name']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Order Status & Actions -->
            <div class="col-lg-4">
                <!-- Current Status -->
                <div class="card shadow mb-2">
                    <div class="card-header py-1">
                        <h6 class="m-0 font-weight-bold text-primary small">
                            <i class="fas fa-info-circle me-2"></i>
                            Estado del Pedido
                        </h6>
                    </div>
                    <div class="card-body text-center py-2">
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
                        <div class="mb-3">
                            <span class="badge bg-<?= $statusColor ?> p-3 fs-6">
                                <?= $statusLabel ?>
                            </span>
                        </div>
                        
                        <!-- Status Progress -->
                        <div class="progress mb-3" style="height: 10px;">
                            <?php
                            $progressMap = [
                                'pending' => 25,
                                'confirmed' => 50,
                                'in_route' => 75,
                                'delivered' => 100,
                                'cancelled' => 0
                            ];
                            $progress = $progressMap[$order['status']] ?? 0;
                            $progressColor = $order['status'] === 'cancelled' ? 'danger' : 'success';
                            ?>
                            <div class="progress-bar bg-<?= $progressColor ?>" role="progressbar" 
                                 style="width: <?= $progress ?>%" aria-valuenow="<?= $progress ?>" 
                                 aria-valuemin="0" aria-valuemax="100"></div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2">
                            <?php if ($order['status'] === 'pending' && $can_confirm): ?>
                                <button class="btn btn-success" onclick="updateStatus('confirmed')">
                                    <i class="fas fa-check"></i> Confirmar Pedido
                                </button>
                            <?php endif; ?>
                            
                            <?php if ($order['status'] === 'confirmed'): ?>
                                <button class="btn btn-primary" onclick="updateStatus('in_route')">
                                    <i class="fas fa-truck"></i> Marcar en Ruta
                                </button>
                            <?php endif; ?>
                            
                            <?php if ($order['status'] === 'in_route'): ?>
                                <a href="<?= BASE_URL ?>/pedidos/delivery/<?= $order['id'] ?>" class="btn btn-warning">
                                    <i class="fas fa-clipboard-check"></i> Registrar Entrega
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($can_cancel): ?>
                                <button class="btn btn-outline-danger" onclick="updateStatus('cancelled')">
                                    <i class="fas fa-times"></i> Cancelar Pedido
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Order Information -->
                <div class="card shadow mb-2">
                    <div class="card-header py-1">
                        <h6 class="m-0 font-weight-bold text-primary small">
                            <i class="fas fa-calendar me-2"></i>
                            Información de Fechas
                        </h6>
                    </div>
                    <div class="card-body py-2">
                        <div class="row mb-2">
                            <div class="col-6">
                                <small class="text-muted">Fecha del Pedido:</small>
                            </div>
                            <div class="col-6 text-end">
                                <strong><?= date('d/m/Y', strtotime($order['order_date'])) ?></strong>
                            </div>
                        </div>
                        
                        <div class="row mb-2">
                            <div class="col-6">
                                <small class="text-muted">Fecha de Entrega:</small>
                            </div>
                            <div class="col-6 text-end">
                                <?php if ($order['delivery_date']): ?>
                                    <strong><?= date('d/m/Y', strtotime($order['delivery_date'])) ?></strong>
                                    <?php
                                    $deliveryTimestamp = strtotime($order['delivery_date']);
                                    $todayTimestamp = strtotime(date('Y-m-d'));
                                    $daysDiff = floor(($deliveryTimestamp - $todayTimestamp) / (60 * 60 * 24));
                                    
                                    if ($daysDiff < 0): ?>
                                        <br><small class="text-danger">Atrasado (<?= abs($daysDiff) ?> días)</small>
                                    <?php elseif ($daysDiff === 0): ?>
                                        <br><small class="text-warning">Hoy</small>
                                    <?php elseif ($daysDiff === 1): ?>
                                        <br><small class="text-info">Mañana</small>
                                    <?php elseif ($daysDiff <= 7): ?>
                                        <br><small class="text-success">En <?= $daysDiff ?> días</small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">Sin definir</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if ($order['status'] === 'delivered' && !empty($order['delivered_at'])): ?>
                        <div class="row mb-2">
                            <div class="col-6">
                                <small class="text-muted">Entregado el:</small>
                            </div>
                            <div class="col-6 text-end">
                                <strong><?= date('d/m/Y H:i', strtotime($order['delivered_at'])) ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Actualizado:</small>
                            </div>
                            <div class="col-6 text-end">
                                <small><?= date('d/m/Y H:i', strtotime($order['updated_at'])) ?></small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="card shadow mb-2">
                    <div class="card-header py-1">
                        <h6 class="m-0 font-weight-bold text-success small">
                            <i class="fas fa-calculator me-2"></i>
                            Resumen Financiero
                        </h6>
                    </div>
                    <div class="card-body py-2">
                        <div class="row mb-2">
                            <div class="col-6">
                                <span>Subtotal:</span>
                            </div>
                            <div class="col-6 text-end">
                                <span>$<?= number_format($order['total_amount'], 2) ?></span>
                            </div>
                        </div>
                        
                        <?php if ($order['discount_amount'] > 0): ?>
                        <div class="row mb-2">
                            <div class="col-6">
                                <span>Descuento:</span>
                            </div>
                            <div class="col-6 text-end">
                                <span class="text-danger">-$<?= number_format($order['discount_amount'], 2) ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <strong>Total Final:</strong>
                            </div>
                            <div class="col-6 text-end">
                                <strong class="text-success fs-5">$<?= number_format($order['final_amount'], 2) ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card shadow">
                    <div class="card-header py-1">
                        <h6 class="m-0 font-weight-bold text-primary small">
                            <i class="fas fa-bolt me-2"></i>
                            Acciones Rápidas
                        </h6>
                    </div>
                    <div class="card-body py-2">
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary btn-sm" onclick="shareOrder()">
                                <i class="fas fa-share"></i> Compartir Pedido
                            </button>
                            
                            <button class="btn btn-outline-info btn-sm" onclick="duplicateOrder()">
                                <i class="fas fa-copy"></i> Duplicar Pedido
                            </button>
                            
                            <a href="<?= BASE_URL ?>/pedidos/create?customer_id=<?= $order['customer_id'] ?>" 
                               class="btn btn-outline-success btn-sm">
                                <i class="fas fa-plus"></i> Nuevo Pedido para Cliente
                            </a>
                            
                            <button class="btn btn-outline-secondary btn-sm" onclick="exportOrder()">
                                <i class="fas fa-download"></i> Exportar PDF
                            </button>
                        </div>
                    </div>
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
                <h5 class="modal-title" id="qrModalLabel">
                    Código QR - Pedido <?= htmlspecialchars($order['order_number']) ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <?php if (!empty($order['qr_code'])): ?>
                    <img src="data:image/png;base64,<?= $order['qr_code'] ?>" class="img-fluid mb-3" style="max-width: 250px;">
                    <p class="text-muted">Escanee este código para verificar el pedido</p>
                    <p class="small">
                        <strong>URL de Verificación:</strong><br>
                        <code><?= BASE_URL ?>/pedidos/verify/<?= $order['order_number'] ?></code>
                    </p>
                <?php else: ?>
                    <p class="text-muted">No hay código QR disponible para este pedido</p>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="printQR()">
                    <i class="fas fa-print"></i> Imprimir QR
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
                    <input type="hidden" id="statusOrderId" name="order_id" value="<?= $order['id'] ?>">
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
function showQRCode() {
    $('#qrModal').modal('show');
}

function updateStatus(newStatus) {
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

function printOrder() {
    window.print();
}

function printQR() {
    const qrContent = document.querySelector('#qrModal .modal-body').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Código QR - Pedido <?= htmlspecialchars($order['order_number']) ?></title>
                <style>
                    body { text-align: center; padding: 20px; font-family: Arial, sans-serif; }
                    .header { margin-bottom: 20px; }
                </style>
            </head>
            <body>
                <div class="header">
                    <h2>Pedido <?= htmlspecialchars($order['order_number']) ?></h2>
                    <p>Cliente: <?= htmlspecialchars($order['customer_name']) ?></p>
                </div>
                ${qrContent}
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
    printWindow.close();
}

function shareOrder() {
    const url = window.location.href;
    if (navigator.share) {
        navigator.share({
            title: 'Pedido <?= htmlspecialchars($order['order_number']) ?>',
            text: 'Pedido para <?= htmlspecialchars($order['customer_name']) ?>',
            url: url
        });
    } else {
        // Fallback para navegadores que no soportan Web Share API
        navigator.clipboard.writeText(url).then(() => {
            showAlert('success', 'URL copiada al portapapeles');
        });
    }
}

function duplicateOrder() {
    if (confirm('¿Desea crear un nuevo pedido basado en este pedido?')) {
        const url = '<?= BASE_URL ?>/pedidos/create?duplicate=<?= $order['id'] ?>';
        window.location.href = url;
    }
}

function exportOrder() {
    const url = '<?= BASE_URL ?>/pedidos/export/<?= $order['id'] ?>?format=pdf';
    window.open(url, '_blank');
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-times-circle';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas ${icon} me-2"></i>
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
/* Ajustes de espaciado general - MÁS COMPACTO */
.content-wrapper {
    padding: 0 !important;
    margin: 0 !important;
}

.content-header {
    margin-bottom: 0.25rem !important;
    padding: 0.25rem 0 !important;
    background: transparent !important;
}

.container-fluid {
    padding-left: 0.75rem !important;
    padding-right: 0.75rem !important;
}

.card.shadow {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15)!important;
    margin-bottom: 0.75rem !important;
}

.card-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    padding: 0.5rem 1rem !important;
}

.card-body {
    padding: 0.75rem !important;
}

/* Espaciado de márgenes entre cards - MÁS PEQUEÑO */
.mb-3 {
    margin-bottom: 0.75rem !important;
}

.mb-2 {
    margin-bottom: 0.5rem !important;
}

.mb-1 {
    margin-bottom: 0.25rem !important;
}

/* Ajustes para botones más compactos */
.btn-sm {
    padding: 0.2rem 0.4rem;
    font-size: 0.8rem;
}

/* Espaciado entre filas - MÁS COMPACTO */
.row {
    margin-left: -0.25rem;
    margin-right: -0.25rem;
}

.row > * {
    padding-left: 0.25rem;
    padding-right: 0.25rem;
}

/* Títulos más compactos */
.h3 {
    margin-bottom: 0.25rem !important;
}

/* Reducir padding en tablas */
.table td, .table th {
    padding: 0.5rem !important;
}

.table-responsive {
    margin-bottom: 0 !important;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -29px;
    top: 17px;
    bottom: -20px;
    width: 2px;
    background-color: #dee2e6;
}

.timeline-item:last-child::before {
    display: none;
}

.progress {
    background-color: #e9ecef;
}

.badge.p-3 {
    padding: 0.75rem 1rem !important;
}

.fs-6 {
    font-size: 1rem !important;
}

.gap-2 {
    gap: 0.5rem !important;
}

@media print {
    .btn, .card-header .btn, .modal, .sidebar, .navbar {
        display: none !important;
    }
    
    .content-wrapper {
        margin: 0 !important;
        padding: 0 !important;
    }
    
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
        margin-bottom: 20px !important;
    }
    
    .card-header {
        background-color: #f8f9fa !important;
        border-bottom: 1px solid #ddd !important;
    }
    
    .col-lg-4 {
        page-break-inside: avoid;
    }
}

@media (max-width: 768px) {
    .timeline {
        padding-left: 20px;
    }
    
    .timeline-marker {
        left: -25px;
    }
    
    .timeline-item::before {
        left: -19px;
    }
    
    .btn-sm {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    
    .container-fluid {
        padding-left: 1rem;
        padding-right: 1rem;
    }
}
</style>

<?php
$content = ob_get_clean();
require_once dirname(__DIR__) . '/layouts/main.php';
?>