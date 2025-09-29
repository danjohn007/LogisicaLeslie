<?php
ob_start();
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Crear Nuevo Lote de Producción</h1>
                    <p class="text-muted mb-0">Registre un nuevo lote de producción en el sistema</p>
                </div>
                <div>
                    <a href="<?php echo BASE_URL; ?>produccion" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Volver a Producción
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

    <!-- Formulario de Creación -->
    <div class="row">
        <div class="col-xl-8 col-lg-10 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="m-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        Información del Lote
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" id="createLotForm">
                        <div class="row">
                            <!-- Número de Lote -->
                            <div class="col-md-6 mb-3">
                                <label for="lot_number" class="form-label">
                                    Número de Lote <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control" 
                                           id="lot_number" 
                                           name="lot_number" 
                                           value="<?php echo htmlspecialchars($_POST['lot_number'] ?? $suggested_lot_number ?? ''); ?>"
                                           placeholder="Ej: PRD001-001" 
                                           required>
                                    <button type="button" 
                                            class="btn btn-outline-secondary" 
                                            id="generateLotBtn"
                                            title="Generar número automático">
                                        <i class="fas fa-magic"></i>
                                    </button>
                                </div>
                                <div class="form-text">Identificador único del lote</div>
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
                                                data-code="<?php echo $product['code']; ?>"
                                                <?php echo (($_POST['product_id'] ?? '') == $product['id']) ? 'selected' : ''; ?>>
                                            [<?php echo $product['code']; ?>] <?php echo $product['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Fecha de Producción -->
                            <div class="col-md-6 mb-3">
                                <label for="production_date" class="form-label">
                                    Fecha de Producción <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       id="production_date" 
                                       name="production_date" 
                                       value="<?php echo $_POST['production_date'] ?? date('Y-m-d'); ?>"
                                       max="<?php echo date('Y-m-d'); ?>"
                                       required>
                            </div>

                            <!-- Fecha de Vencimiento -->
                            <div class="col-md-6 mb-3">
                                <label for="expiry_date" class="form-label">
                                    Fecha de Vencimiento
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       id="expiry_date" 
                                       name="expiry_date" 
                                       value="<?php echo $_POST['expiry_date'] ?? ''; ?>"
                                       min="<?php echo date('Y-m-d'); ?>">
                                <div class="form-text">Dejar vacío si el producto no tiene vencimiento</div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Cantidad Producida -->
                            <div class="col-md-6 mb-3">
                                <label for="quantity_produced" class="form-label">
                                    Cantidad Producida <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control" 
                                           id="quantity_produced" 
                                           name="quantity_produced" 
                                           value="<?php echo $_POST['quantity_produced'] ?? ''; ?>"
                                           min="0.01" 
                                           step="0.01" 
                                           placeholder="0.00"
                                           required>
                                    <span class="input-group-text">unidades</span>
                                </div>
                            </div>

                            <!-- Tipo de Producción -->
                            <div class="col-md-6 mb-3">
                                <label for="production_type" class="form-label">
                                    Tipo de Producción
                                </label>
                                <select class="form-select" id="production_type" name="production_type">
                                    <option value="fresco" <?php echo (($_POST['production_type'] ?? 'fresco') == 'fresco') ? 'selected' : ''; ?>>
                                        Fresco
                                    </option>
                                    <option value="curado" <?php echo (($_POST['production_type'] ?? '') == 'curado') ? 'selected' : ''; ?>>
                                        Curado
                                    </option>
                                    <option value="semi-curado" <?php echo (($_POST['production_type'] ?? '') == 'semi-curado') ? 'selected' : ''; ?>>
                                        Semi-curado
                                    </option>
                                    <option value="especial" <?php echo (($_POST['production_type'] ?? '') == 'especial') ? 'selected' : ''; ?>>
                                        Especial
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Notas -->
                        <div class="mb-4">
                            <label for="notes" class="form-label">Notas y Observaciones</label>
                            <textarea class="form-control" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3" 
                                      placeholder="Ingrese observaciones sobre el lote de producción..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between">
                            <a href="<?php echo BASE_URL; ?>produccion" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Crear Lote
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Información Adicional -->
            <div class="card shadow mt-4">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Información Importante
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Número de Lote</h6>
                            <ul class="small">
                                <li>Debe ser único en el sistema</li>
                                <li>Se sugiere usar formato: CODIGO-SECUENCIA</li>
                                <li>Ejemplo: PRD001-001, QUE001-025</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Gestión de Inventario</h6>
                            <ul class="small">
                                <li>El lote se agregará automáticamente al inventario</li>
                                <li>Se registrará el movimiento de producción</li>
                                <li>La cantidad estará disponible para ventas</li>
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
    // Generar número de lote automático
    $('#generateLotBtn').click(function() {
        const productId = $('#product_id').val();
        if (!productId) {
            alert('Por favor seleccione un producto primero');
            return;
        }
        
        // Mostrar loading
        $(this).html('<i class="fas fa-spinner fa-spin"></i>');
        const button = $(this);
        
        // Llamar al servidor para generar número
        $.get('<?php echo BASE_URL; ?>produccion/generateLotNumberAjax', { product_id: productId })
            .done(function(response) {
                if (response.lot_number) {
                    $('#lot_number').val(response.lot_number);
                } else {
                    // Fallback: generar localmente
                    const productCode = $('#product_id option:selected').data('code');
                    const today = new Date();
                    const dateStr = today.getMonth().toString().padStart(2, '0') + 
                                   today.getDate().toString().padStart(2, '0');
                    const timeStr = today.getHours().toString().padStart(2, '0') + 
                                   today.getMinutes().toString().padStart(2, '0');
                    const lotNumber = productCode + '-' + dateStr + timeStr;
                    $('#lot_number').val(lotNumber);
                }
            })
            .fail(function() {
                // Fallback: generar localmente
                const productCode = $('#product_id option:selected').data('code');
                const today = new Date();
                const dateStr = today.getMonth().toString().padStart(2, '0') + 
                               today.getDate().toString().padStart(2, '0');
                const timeStr = today.getHours().toString().padStart(2, '0') + 
                               today.getMinutes().toString().padStart(2, '0');
                const lotNumber = productCode + '-' + dateStr + timeStr;
                $('#lot_number').val(lotNumber);
            })
            .always(function() {
                // Restaurar botón
                button.html('<i class="fas fa-magic"></i>');
            });
    });
    
    // Validación de fechas
    $('#production_date').change(function() {
        const productionDate = new Date($(this).val());
        const today = new Date();
        
        if (productionDate > today) {
            alert('La fecha de producción no puede ser futura');
            $(this).val('<?php echo date('Y-m-d'); ?>');
        }
        
        // Actualizar fecha mínima de vencimiento
        $('#expiry_date').attr('min', $(this).val());
    });
    
    $('#expiry_date').change(function() {
        const expiryDate = new Date($(this).val());
        const productionDate = new Date($('#production_date').val());
        
        if (expiryDate <= productionDate) {
            alert('La fecha de vencimiento debe ser posterior a la fecha de producción');
            $(this).val('');
        }
    });
    
    // Calcular fecha de vencimiento sugerida según tipo de producto
    $('#product_id').change(function() {
        const productName = $(this).find('option:selected').text().toLowerCase();
        const productionDate = new Date($('#production_date').val());
        
        // Sugerir fecha de vencimiento según el tipo de producto
        let daysToAdd = 7; // Por defecto 1 semana
        
        if (productName.includes('fresco') || productName.includes('panela')) {
            daysToAdd = 7; // 1 semana para quesos frescos
        } else if (productName.includes('curado') || productName.includes('manchego')) {
            daysToAdd = 90; // 3 meses para quesos curados
        } else if (productName.includes('crema') || productName.includes('yogurt')) {
            daysToAdd = 14; // 2 semanas para lácteos
        }
        
        const suggestedExpiry = new Date(productionDate);
        suggestedExpiry.setDate(suggestedExpiry.getDate() + daysToAdd);
        
        if (!$('#expiry_date').val()) {
            $('#expiry_date').val(suggestedExpiry.toISOString().split('T')[0]);
        }
    });
    
    // Validación del formulario
    $('#createLotForm').submit(function(e) {
        const lotNumber = $('#lot_number').val().trim();
        const productId = $('#product_id').val();
        const quantity = parseFloat($('#quantity_produced').val());
        
        if (!lotNumber) {
            alert('El número de lote es obligatorio');
            e.preventDefault();
            return false;
        }
        
        if (!productId) {
            alert('Debe seleccionar un producto');
            e.preventDefault();
            return false;
        }
        
        if (!quantity || quantity <= 0) {
            alert('La cantidad producida debe ser mayor a 0');
            e.preventDefault();
            return false;
        }
        
        return true;
    });
    
    // Autocompletar fecha de vencimiento al seleccionar producto
    $('#product_id').trigger('change');
});
</script>

<?php
$content = ob_get_clean();
require_once dirname(__DIR__) . '/layouts/main.php';
?>