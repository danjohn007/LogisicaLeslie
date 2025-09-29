<?php require_once __DIR__ . '/../layouts/main.php'; ?>

<div class="content-wrapper">
    <!-- Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-sm-6">
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-clipboard-check text-warning me-2"></i>
                        Registro de Entrega
                    </h1>
                    <p class="text-muted">
                        Pedido: <strong><?= htmlspecialchars($order['order_number']) ?></strong> - 
                        Cliente: <strong><?= htmlspecialchars($order['customer_name']) ?></strong>
                    </p>
                </div>
                <div class="col-sm-6">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= BASE_URL ?>/pedidos/viewOrder/<?= $order['id'] ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Volver al Pedido
                        </a>
                        <a href="<?= BASE_URL ?>/pedidos" class="btn btn-outline-primary">
                            <i class="fas fa-list"></i> Lista de Pedidos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    <?php if ($success): ?>
        <div class="container-fluid mb-4">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="container-fluid mb-4">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Delivery Form -->
            <div class="col-lg-8">
                <form method="POST" id="deliveryForm" class="needs-validation" novalidate>
                    <!-- Customer Verification -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-warning text-dark">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-user-check me-2"></i>
                                Verificación del Cliente
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="mb-1"><?= htmlspecialchars($order['customer_name']) ?></h5>
                                    <?php if (!empty($order['customer_contact'])): ?>
                                        <p class="text-muted mb-1">
                                            <i class="fas fa-user me-1"></i>
                                            <?= htmlspecialchars($order['customer_contact']) ?>
                                        </p>
                                    <?php endif; ?>
                                    <?php if (!empty($order['customer_phone'])): ?>
                                        <p class="text-muted mb-1">
                                            <i class="fas fa-phone me-1"></i>
                                            <a href="tel:<?= htmlspecialchars($order['customer_phone']) ?>">
                                                <?= htmlspecialchars($order['customer_phone']) ?>
                                            </a>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-map-marker-alt text-primary me-1"></i> Dirección de Entrega:</h6>
                                    <p class="mb-0"><?= htmlspecialchars($order['customer_address'] ?? 'No especificada') ?></p>
                                    
                                    <div class="mt-3">
                                        <strong>Fecha de Entrega:</strong> 
                                        <?= $order['delivery_date'] ? date('d/m/Y', strtotime($order['delivery_date'])) : 'No especificada' ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Instrucciones:</strong> Verifique la identidad del cliente y confirme la dirección antes de proceder con la entrega.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Details -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-box-open me-2"></i>
                                Productos a Entregar
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="40%">Producto</th>
                                            <th width="15%" class="text-center">Cantidad Pedida</th>
                                            <th width="15%" class="text-center">Cantidad a Entregar</th>
                                            <th width="15%" class="text-center">Estado</th>
                                            <th width="15%">Observaciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($order['details'])): ?>
                                            <?php foreach ($order['details'] as $detail): ?>
                                            <tr id="product_row_<?= $detail['id'] ?>">
                                                <td>
                                                    <strong><?= htmlspecialchars($detail['product_name']) ?></strong>
                                                    <?php if (!empty($detail['product_code'])): ?>
                                                        <br><small class="text-muted">Código: <?= htmlspecialchars($detail['product_code']) ?></small>
                                                    <?php endif; ?>
                                                    <br><small class="text-info">Precio: $<?= number_format($detail['unit_price'], 2) ?></small>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <span class="badge bg-primary fs-6 p-2">
                                                        <?= number_format($detail['quantity_ordered'], 2) ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="input-group input-group-sm">
                                                        <button class="btn btn-outline-secondary" type="button" 
                                                                onclick="adjustQuantity(<?= $detail['id'] ?>, -0.5)">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                        <input type="number" 
                                                               class="form-control text-center" 
                                                               name="delivery[<?= $detail['id'] ?>][quantity_delivered]" 
                                                               id="qty_<?= $detail['id'] ?>"
                                                               value="<?= $detail['quantity_ordered'] ?>" 
                                                               min="0" 
                                                               max="<?= $detail['quantity_ordered'] ?>" 
                                                               step="0.01" 
                                                               onchange="validateQuantity(<?= $detail['id'] ?>, <?= $detail['quantity_ordered'] ?>)"
                                                               required>
                                                        <button class="btn btn-outline-secondary" type="button" 
                                                                onclick="adjustQuantity(<?= $detail['id'] ?>, 0.5)">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                    <div class="mt-1">
                                                        <button type="button" class="btn btn-link btn-sm p-0" 
                                                                onclick="setFullQuantity(<?= $detail['id'] ?>, <?= $detail['quantity_ordered'] ?>)">
                                                            <small>Cantidad completa</small>
                                                        </button>
                                                    </div>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <span id="status_<?= $detail['id'] ?>" class="badge bg-success">
                                                        Completo
                                                    </span>
                                                </td>
                                                <td>
                                                    <textarea class="form-control form-control-sm" 
                                                              name="delivery[<?= $detail['id'] ?>][notes]" 
                                                              rows="2" 
                                                              placeholder="Observaciones del producto..."></textarea>
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
                                </table>
                            </div>
                            
                            <div class="alert alert-warning mt-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Importante:</strong> Si no se puede entregar la cantidad completa de algún producto, 
                                ajuste la cantidad y agregue observaciones explicando el motivo.
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Notes -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-sticky-note me-2"></i>
                                Notas de la Entrega
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="delivery_notes" class="form-label">Observaciones Generales</label>
                                <textarea class="form-control" id="delivery_notes" name="delivery_notes" rows="4" 
                                          placeholder="Agregue cualquier observación sobre la entrega: condiciones del cliente, problemas encontrados, solicitudes especiales, etc."></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="delivery_time" class="form-label">Hora de Entrega</label>
                                        <input type="time" class="form-control" id="delivery_time" name="delivery_time" 
                                               value="<?= date('H:i') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="received_by" class="form-label">Recibido por</label>
                                        <input type="text" class="form-control" id="received_by" name="received_by" 
                                               placeholder="Nombre de quien recibe" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Signature -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-success">
                                <i class="fas fa-signature me-2"></i>
                                Confirmación del Cliente
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Firma Digital (opcional)</label>
                                <div class="border border-2 border-dashed rounded p-3 text-center" 
                                     id="signature_area" style="min-height: 150px; background-color: #f8f9fa;">
                                    <canvas id="signature_canvas" width="600" height="150" style="cursor: crosshair;"></canvas>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearSignature()">
                                            <i class="fas fa-eraser"></i> Limpiar Firma
                                        </button>
                                    </div>
                                </div>
                                <input type="hidden" name="customer_signature" id="customer_signature">
                            </div>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="confirm_delivery" name="confirm_delivery" required>
                                <label class="form-check-label" for="confirm_delivery">
                                    <strong>Confirmo que he entregado los productos listados al cliente y que el cliente está satisfecho con la entrega.</strong>
                                </label>
                                <div class="invalid-feedback">
                                    Debe confirmar que la entrega fue realizada correctamente
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="card shadow">
                        <div class="card-body">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="button" class="btn btn-outline-secondary me-md-2" onclick="saveDraft()">
                                    <i class="fas fa-save"></i> Guardar Borrador
                                </button>
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-check-circle"></i> Confirmar Entrega
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <!-- Order Info -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-info text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-info-circle me-2"></i>
                            Información del Pedido
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-6"><strong>Número:</strong></div>
                            <div class="col-6"><?= htmlspecialchars($order['order_number']) ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6"><strong>Fecha:</strong></div>
                            <div class="col-6"><?= date('d/m/Y', strtotime($order['order_date'])) ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6"><strong>Estado:</strong></div>
                            <div class="col-6">
                                <span class="badge bg-primary">En Ruta</span>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6"><strong>Total:</strong></div>
                            <div class="col-6"><strong>$<?= number_format($order['final_amount'], 2) ?></strong></div>
                        </div>
                        <div class="row">
                            <div class="col-6"><strong>Pago:</strong></div>
                            <div class="col-6"><?= ucfirst($order['payment_method']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Summary -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-calculator me-2"></i>
                            Resumen de Entrega
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="delivery_summary">
                            <div class="row mb-2">
                                <div class="col-8">Productos completos:</div>
                                <div class="col-4 text-end" id="complete_count"><?= count($order['details']) ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-8">Productos parciales:</div>
                                <div class="col-4 text-end" id="partial_count">0</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-8">Total a cobrar:</div>
                                <div class="col-4 text-end"><strong id="total_amount">$<?= number_format($order['final_amount'], 2) ?></strong></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-bolt me-2"></i>
                            Acciones Rápidas
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="callCustomer()">
                                <i class="fas fa-phone"></i> Llamar Cliente
                            </button>
                            <button type="button" class="btn btn-outline-info btn-sm" onclick="showQR()">
                                <i class="fas fa-qrcode"></i> Mostrar QR
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm" onclick="setAllComplete()">
                                <i class="fas fa-check-double"></i> Marcar Todo Completo
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Emergency Contact -->
                <div class="card shadow">
                    <div class="card-header py-3 bg-warning text-dark">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Contacto de Emergencia
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="small mb-2">
                            Si tiene problemas con la entrega, contacte inmediatamente:
                        </p>
                        <div class="text-center">
                            <button type="button" class="btn btn-danger btn-sm">
                                <i class="fas fa-phone"></i> Supervisión: (xxx) xxx-xxxx
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let canvas, ctx;
let isDrawing = false;

$(document).ready(function() {
    // Initialize signature canvas
    initializeSignature();
    
    // Form validation
    $('#deliveryForm').on('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        // Save signature
        if (canvas) {
            const signatureData = canvas.toDataURL();
            $('#customer_signature').val(signatureData);
        }
        
        $(this).addClass('was-validated');
    });
    
    // Update summary when quantities change
    $('input[name*="quantity_delivered"]').on('input change', function() {
        updateDeliverySummary();
    });
    
    // Initial summary update
    updateDeliverySummary();
});

function initializeSignature() {
    canvas = document.getElementById('signature_canvas');
    if (!canvas) return;
    
    ctx = canvas.getContext('2d');
    
    // Mouse events
    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('mouseout', stopDrawing);
    
    // Touch events for mobile
    canvas.addEventListener('touchstart', function(e) {
        e.preventDefault();
        const touch = e.touches[0];
        const mouseEvent = new MouseEvent('mousedown', {
            clientX: touch.clientX,
            clientY: touch.clientY
        });
        canvas.dispatchEvent(mouseEvent);
    });
    
    canvas.addEventListener('touchmove', function(e) {
        e.preventDefault();
        const touch = e.touches[0];
        const mouseEvent = new MouseEvent('mousemove', {
            clientX: touch.clientX,
            clientY: touch.clientY
        });
        canvas.dispatchEvent(mouseEvent);
    });
    
    canvas.addEventListener('touchend', function(e) {
        e.preventDefault();
        const mouseEvent = new MouseEvent('mouseup', {});
        canvas.dispatchEvent(mouseEvent);
    });
}

function startDrawing(e) {
    isDrawing = true;
    draw(e);
}

function draw(e) {
    if (!isDrawing) return;
    
    const rect = canvas.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.strokeStyle = '#000';
    
    ctx.lineTo(x, y);
    ctx.stroke();
    ctx.beginPath();
    ctx.moveTo(x, y);
}

function stopDrawing() {
    if (!isDrawing) return;
    isDrawing = false;
    ctx.beginPath();
}

function clearSignature() {
    if (ctx) {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    }
}

function adjustQuantity(detailId, change) {
    const input = document.getElementById(`qty_${detailId}`);
    const currentValue = parseFloat(input.value) || 0;
    const newValue = Math.max(0, currentValue + change);
    const maxValue = parseFloat(input.max);
    
    if (newValue <= maxValue) {
        input.value = newValue.toFixed(2);
        validateQuantity(detailId, maxValue);
        updateDeliverySummary();
    }
}

function setFullQuantity(detailId, maxQuantity) {
    const input = document.getElementById(`qty_${detailId}`);
    input.value = maxQuantity;
    validateQuantity(detailId, maxQuantity);
    updateDeliverySummary();
}

function validateQuantity(detailId, maxQuantity) {
    const input = document.getElementById(`qty_${detailId}`);
    const statusElement = document.getElementById(`status_${detailId}`);
    const quantity = parseFloat(input.value) || 0;
    
    if (quantity > maxQuantity) {
        input.value = maxQuantity;
        quantity = maxQuantity;
    }
    
    if (quantity === maxQuantity) {
        statusElement.className = 'badge bg-success';
        statusElement.textContent = 'Completo';
    } else if (quantity > 0) {
        statusElement.className = 'badge bg-warning';
        statusElement.textContent = 'Parcial';
    } else {
        statusElement.className = 'badge bg-danger';
        statusElement.textContent = 'No entregado';
    }
}

function updateDeliverySummary() {
    let completeCount = 0;
    let partialCount = 0;
    let totalAmount = 0;
    
    $('input[name*="quantity_delivered"]').each(function() {
        const detailId = this.name.match(/\[(\d+)\]/)[1];
        const deliveredQty = parseFloat(this.value) || 0;
        const orderedQty = parseFloat(this.max);
        
        if (deliveredQty === orderedQty && deliveredQty > 0) {
            completeCount++;
        } else if (deliveredQty > 0) {
            partialCount++;
        }
        
        // Calculate proportional amount
        const unitPrice = parseFloat($(`#product_row_${detailId} .text-info`).text().replace('Precio: $', '')) || 0;
        totalAmount += deliveredQty * unitPrice;
    });
    
    $('#complete_count').text(completeCount);
    $('#partial_count').text(partialCount);
    $('#total_amount').text('$' + totalAmount.toFixed(2));
}

function setAllComplete() {
    if (confirm('¿Marcar todos los productos como entrega completa?')) {
        $('input[name*="quantity_delivered"]').each(function() {
            const maxValue = parseFloat(this.max);
            this.value = maxValue;
            
            const detailId = this.name.match(/\[(\d+)\]/)[1];
            validateQuantity(detailId, maxValue);
        });
        updateDeliverySummary();
    }
}

function saveDraft() {
    // Implement save draft functionality
    showAlert('info', 'Borrador guardado (funcionalidad pendiente de implementar)');
}

function callCustomer() {
    const phone = '<?= htmlspecialchars($order['customer_phone'] ?? '') ?>';
    if (phone) {
        window.location.href = 'tel:' + phone;
    } else {
        showAlert('warning', 'No hay número de teléfono registrado para este cliente');
    }
}

function showQR() {
    const qrCode = '<?= $order['qr_code'] ?? '' ?>';
    if (qrCode) {
        const qrWindow = window.open('', '_blank', 'width=400,height=500');
        qrWindow.document.write(`
            <html>
                <head><title>QR - Pedido <?= htmlspecialchars($order['order_number']) ?></title></head>
                <body style="text-align: center; padding: 20px;">
                    <h3>Pedido <?= htmlspecialchars($order['order_number']) ?></h3>
                    <img src="data:image/png;base64,${qrCode}" style="max-width: 300px;">
                    <p>Mostrar al cliente para verificación</p>
                </body>
            </html>
        `);
    } else {
        showAlert('warning', 'No hay código QR disponible para este pedido');
    }
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'info' ? 'alert-info' :
                      type === 'warning' ? 'alert-warning' : 'alert-danger';
    const icon = type === 'success' ? 'fa-check-circle' : 
                 type === 'info' ? 'fa-info-circle' :
                 type === 'warning' ? 'fa-exclamation-triangle' : 'fa-times-circle';
    
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
.card.shadow {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15)!important;
}

#signature_canvas {
    border: 1px solid #ddd;
    border-radius: 4px;
    background-color: white;
    width: 100%;
    height: 150px;
}

.input-group-sm .form-control {
    font-size: 0.875rem;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

.badge.fs-6 {
    font-size: 1rem !important;
    padding: 0.5rem 0.75rem;
}

.gap-2 {
    gap: 0.5rem !important;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.8rem;
    }
    
    #signature_canvas {
        height: 120px;
    }
    
    .btn-group .btn {
        font-size: 0.7rem;
        padding: 0.2rem 0.4rem;
    }
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
}
</style>