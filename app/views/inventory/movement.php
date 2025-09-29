<?php
ob_start();
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Movimiento de Inventario</h1>
                    <p class="text-muted mb-0">Registrar entradas y salidas de productos</p>
                </div>
                <div>
                    <a href="<?php echo BASE_URL; ?>inventario" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Volver al Inventario
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas -->
    <?php if (isset($success) && $success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error) && $error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Formulario de Movimiento -->
    <div class="row">
        <div class="col-xl-8 col-lg-10 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="m-0">
                        <i class="fas fa-exchange-alt me-2"></i>
                        Registrar Movimiento
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" id="movementForm">
                        <div class="row">
                            <!-- Tipo de Movimiento -->
                            <div class="col-md-6 mb-3">
                                <label for="movement_type" class="form-label">
                                    Tipo de Movimiento <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="movement_type" name="movement_type" required>
                                    <option value="entrada" <?php echo (($_POST['movement_type'] ?? '') == 'entrada') ? 'selected' : ''; ?>>
                                        Entrada (Agregar stock)
                                    </option>
                                    <option value="salida" <?php echo (($_POST['movement_type'] ?? '') == 'salida') ? 'selected' : ''; ?>>
                                        Salida (Reducir stock)
                                    </option>
                                </select>
                            </div>

                            <!-- Producto -->
                            <div class="col-md-6 mb-3">
                                <label for="product_id" class="form-label">
                                    Producto <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="product_id" name="product_id" required>
                                    <option value="">Seleccione un producto</option>
                                    <?php foreach ($products as $product): ?>
                                        <option value="<?php echo $product['id']; ?>"
                                                <?php echo (($_POST['product_id'] ?? '') == $product['id']) ? 'selected' : ''; ?>>
                                            [<?php echo $product['code']; ?>] <?php echo $product['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Cantidad -->
                            <div class="col-md-6 mb-3">
                                <label for="quantity" class="form-label">
                                    Cantidad <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control" 
                                           id="quantity" 
                                           name="quantity" 
                                           value="<?php echo $_POST['quantity'] ?? ''; ?>"
                                           min="0.01" 
                                           step="0.01" 
                                           placeholder="0.00"
                                           required>
                                    <span class="input-group-text">unidades</span>
                                </div>
                            </div>

                            <!-- Ubicación -->
                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">Ubicación</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="location" 
                                       name="location" 
                                       value="<?php echo $_POST['location'] ?? 'Almacén Principal'; ?>"
                                       placeholder="Ej: Almacén Principal, Refrigerador A, etc.">
                            </div>
                        </div>

                        <div class="row">
                            <!-- Motivo/Razón -->
                            <div class="col-md-6 mb-3">
                                <label for="reason" class="form-label">
                                    Motivo <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="reason" name="reason" required>
                                    <option value="">Seleccione un motivo</option>
                                    <optgroup label="Entradas">
                                        <option value="Producción nueva" <?php echo (($_POST['reason'] ?? '') == 'Producción nueva') ? 'selected' : ''; ?>>
                                            Producción nueva
                                        </option>
                                        <option value="Devolución cliente" <?php echo (($_POST['reason'] ?? '') == 'Devolución cliente') ? 'selected' : ''; ?>>
                                            Devolución de cliente
                                        </option>
                                        <option value="Corrección inventario" <?php echo (($_POST['reason'] ?? '') == 'Corrección inventario') ? 'selected' : ''; ?>>
                                            Corrección de inventario
                                        </option>
                                        <option value="Transferencia recibida" <?php echo (($_POST['reason'] ?? '') == 'Transferencia recibida') ? 'selected' : ''; ?>>
                                            Transferencia recibida
                                        </option>
                                    </optgroup>
                                    <optgroup label="Salidas">
                                        <option value="Venta realizada" <?php echo (($_POST['reason'] ?? '') == 'Venta realizada') ? 'selected' : ''; ?>>
                                            Venta realizada
                                        </option>
                                        <option value="Producto vencido" <?php echo (($_POST['reason'] ?? '') == 'Producto vencido') ? 'selected' : ''; ?>>
                                            Producto vencido
                                        </option>
                                        <option value="Producto dañado" <?php echo (($_POST['reason'] ?? '') == 'Producto dañado') ? 'selected' : ''; ?>>
                                            Producto dañado
                                        </option>
                                        <option value="Degustación" <?php echo (($_POST['reason'] ?? '') == 'Degustación') ? 'selected' : ''; ?>>
                                            Degustación/Muestra
                                        </option>
                                        <option value="Transferencia enviada" <?php echo (($_POST['reason'] ?? '') == 'Transferencia enviada') ? 'selected' : ''; ?>>
                                            Transferencia enviada
                                        </option>
                                        <option value="Merma" <?php echo (($_POST['reason'] ?? '') == 'Merma') ? 'selected' : ''; ?>>
                                            Merma
                                        </option>
                                    </optgroup>
                                    <option value="Otro" <?php echo (($_POST['reason'] ?? '') == 'Otro') ? 'selected' : ''; ?>>
                                        Otro
                                    </option>
                                </select>
                            </div>

                            <!-- Número de Lote -->
                            <div class="col-md-6 mb-3">
                                <label for="lot_number" class="form-label">Número de Lote</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="lot_number" 
                                       name="lot_number" 
                                       value="<?php echo $_POST['lot_number'] ?? ''; ?>"
                                       placeholder="Opcional - para salidas específicas">
                                <div class="form-text">Dejar vacío para salidas automáticas FIFO</div>
                            </div>
                        </div>

                        <!-- Notas -->
                        <div class="mb-4">
                            <label for="notes" class="form-label">Notas Adicionales</label>
                            <textarea class="form-control" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3" 
                                      placeholder="Observaciones o detalles adicionales sobre el movimiento..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between">
                            <a href="<?php echo BASE_URL; ?>inventario" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Registrar Movimiento
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Información de Stock Disponible -->
            <div class="card shadow mt-4" id="stockInfo" style="display: none;">
                <div class="card-header bg-light">
                    <h6 class="m-0 text-primary">
                        <i class="fas fa-info-circle me-2"></i>
                        Stock Disponible
                    </h6>
                </div>
                <div class="card-body" id="stockDetails">
                    <!-- Se llenará dinámicamente -->
                </div>
            </div>

            <!-- Información de Ayuda -->
            <div class="card shadow mt-4">
                <div class="card-header bg-warning text-dark">
                    <h6 class="m-0">
                        <i class="fas fa-lightbulb me-2"></i>
                        Información Importante
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-success">Movimientos de Entrada</h6>
                            <ul class="small">
                                <li>Aumentan el stock disponible</li>
                                <li>Se pueden asignar a ubicaciones específicas</li>
                                <li>Requieren especificar el motivo</li>
                                <li>Se registran en el historial</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-danger">Movimientos de Salida</h6>
                            <ul class="small">
                                <li>Reducen el stock disponible</li>
                                <li>Usan método FIFO por defecto</li>
                                <li>Pueden especificar lote particular</li>
                                <li>Validan stock suficiente</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Cargar información de stock al seleccionar producto
    $('#product_id').change(function() {
        const productId = $(this).val();
        if (productId) {
            loadProductStock(productId);
        } else {
            $('#stockInfo').hide();
        }
    });
    
    // Validación del formulario
    $('#movementForm').submit(function(e) {
        const movementType = $('#movement_type').val();
        const productId = $('#product_id').val();
        const quantity = parseFloat($('#quantity').val());
        const reason = $('#reason').val();
        
        if (!productId) {
            alert('Debe seleccionar un producto');
            e.preventDefault();
            return false;
        }
        
        if (!quantity || quantity <= 0) {
            alert('La cantidad debe ser mayor a 0');
            e.preventDefault();
            return false;
        }
        
        if (!reason) {
            alert('Debe seleccionar un motivo');
            e.preventDefault();
            return false;
        }
        
        return true;
    });
    
    // Precargar stock si hay producto seleccionado
    if ($('#product_id').val()) {
        $('#product_id').trigger('change');
    }
});

function loadProductStock(productId) {
    $.get('<?php echo BASE_URL; ?>inventario/getProductLots', { product_id: productId })
        .done(function(response) {
            displayStockInfo(response);
        })
        .fail(function() {
            $('#stockInfo').hide();
        });
}

function displayStockInfo(lots) {
    if (!lots || lots.length === 0) {
        $('#stockDetails').html('<p class="text-muted mb-0">No hay stock disponible para este producto.</p>');
        $('#stockInfo').show();
        return;
    }
    
    let html = '<div class="table-responsive"><table class="table table-sm table-striped">';
    html += '<thead><tr><th>Lote</th><th>Cantidad</th><th>Disponible</th><th>Ubicación</th><th>Vencimiento</th></tr></thead><tbody>';
    
    let totalAvailable = 0;
    
    lots.forEach(function(lot) {
        const available = lot.available_quantity || 0;
        totalAvailable += available;
        
        const expiryDate = lot.expiry_date ? new Date(lot.expiry_date).toLocaleDateString('es-ES') : 'Sin vencimiento';
        
        html += `<tr>
            <td><strong>${lot.lot_number}</strong></td>
            <td>${parseFloat(lot.quantity).toFixed(2)}</td>
            <td><strong>${available.toFixed(2)}</strong></td>
            <td>${lot.location || 'N/A'}</td>
            <td>${expiryDate}</td>
        </tr>`;
    });
    
    html += '</tbody></table></div>';
    html += `<div class="mt-2"><strong>Total Disponible: ${totalAvailable.toFixed(2)} unidades</strong></div>`;
    
    $('#stockDetails').html(html);
    $('#stockInfo').show();
}
</script>

<?php
$content = ob_get_clean();
require_once dirname(__DIR__) . '/layouts/main.php';
?>