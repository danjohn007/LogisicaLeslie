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
                    <p class="text-muted mb-0">Registre un nuevo lote de producción</p>
                </div>
                <div>
                    <a href="<?php echo BASE_URL; ?>production" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensajes de estado -->
    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Formulario de creación -->
    <div class="row">
        <div class="col-lg-8 col-md-10 mx-auto">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-plus-circle me-2"></i>
                        Información del Lote
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo BASE_URL; ?>production/create" id="createLotForm">
                        <div class="row">
                            <!-- Número de Lote -->
                            <div class="col-md-6 mb-3">
                                <label for="lot_number" class="form-label">
                                    <i class="fas fa-barcode me-1"></i>
                                    Número de Lote <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="lot_number" 
                                       name="lot_number" 
                                       value="<?php echo htmlspecialchars($_POST['lot_number'] ?? ''); ?>"
                                       placeholder="Ej: LOT-001-2024"
                                       required>
                                <div class="form-text">Código único para identificar el lote</div>
                            </div>

                            <!-- Producto -->
                            <div class="col-md-6 mb-3">
                                <label for="product_id" class="form-label">
                                    <i class="fas fa-box me-1"></i>
                                    Producto <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="product_id" name="product_id" required>
                                    <option value="">Seleccione un producto</option>
                                    <?php foreach ($products as $product): ?>
                                        <option value="<?php echo $product['id']; ?>" 
                                                <?php echo (isset($_POST['product_id']) && $_POST['product_id'] == $product['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($product['code'] . ' - ' . $product['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Fecha de Producción -->
                            <div class="col-md-6 mb-3">
                                <label for="production_date" class="form-label">
                                    <i class="fas fa-calendar me-1"></i>
                                    Fecha de Producción <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       id="production_date" 
                                       name="production_date" 
                                       value="<?php echo $_POST['production_date'] ?? date('Y-m-d'); ?>"
                                       required>
                            </div>

                            <!-- Fecha de Vencimiento -->
                            <div class="col-md-6 mb-3">
                                <label for="expiry_date" class="form-label">
                                    <i class="fas fa-calendar-times me-1"></i>
                                    Fecha de Vencimiento
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       id="expiry_date" 
                                       name="expiry_date" 
                                       value="<?php echo htmlspecialchars($_POST['expiry_date'] ?? ''); ?>"
                                       min="<?php echo date('Y-m-d'); ?>">
                                <div class="form-text">Opcional para productos no perecederos</div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Cantidad Producida -->
                            <div class="col-md-4 mb-3">
                                <label for="quantity_produced" class="form-label">
                                    <i class="fas fa-weight me-1"></i>
                                    Cantidad Producida <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="quantity_produced" 
                                       name="quantity_produced" 
                                       value="<?php echo htmlspecialchars($_POST['quantity_produced'] ?? ''); ?>"
                                       step="0.001"
                                       min="0.001"
                                       placeholder="0.000"
                                       required>
                                <div class="form-text">En kg o unidades</div>
                            </div>

                            <!-- Costo Unitario -->
                            <div class="col-md-4 mb-3">
                                <label for="unit_cost" class="form-label">
                                    <i class="fas fa-dollar-sign me-1"></i>
                                    Costo Unitario
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="unit_cost" 
                                       name="unit_cost" 
                                       value="<?php echo htmlspecialchars($_POST['unit_cost'] ?? ''); ?>"
                                       step="0.01"
                                       min="0"
                                       placeholder="0.00">
                                <div class="form-text">Costo por unidad producida</div>
                            </div>

                            <!-- Estado de Calidad -->
                            <div class="col-md-4 mb-3">
                                <label for="quality_status" class="form-label">
                                    <i class="fas fa-star me-1"></i>
                                    Estado de Calidad
                                </label>
                                <select class="form-select" id="quality_status" name="quality_status">
                                    <option value="excellent" <?php echo (isset($_POST['quality_status']) && $_POST['quality_status'] == 'excellent') ? 'selected' : ''; ?>>Excelente</option>
                                    <option value="good" <?php echo (!isset($_POST['quality_status']) || $_POST['quality_status'] == 'good') ? 'selected' : ''; ?>>Bueno</option>
                                    <option value="fair" <?php echo (isset($_POST['quality_status']) && $_POST['quality_status'] == 'fair') ? 'selected' : ''; ?>>Regular</option>
                                    <option value="rejected" <?php echo (isset($_POST['quality_status']) && $_POST['quality_status'] == 'rejected') ? 'selected' : ''; ?>>Rechazado</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Tipo de Producción -->
                            <div class="col-md-6 mb-3">
                                <label for="production_type" class="form-label">
                                    <i class="fas fa-cogs me-1"></i>
                                    Tipo de Producción
                                </label>
                                <select class="form-select" id="production_type" name="production_type">
                                    <option value="regular" <?php echo (!isset($_POST['production_type']) || $_POST['production_type'] == 'regular') ? 'selected' : ''; ?>>Regular</option>
                                    <option value="organic" <?php echo (isset($_POST['production_type']) && $_POST['production_type'] == 'organic') ? 'selected' : ''; ?>>Orgánico</option>
                                    <option value="premium" <?php echo (isset($_POST['production_type']) && $_POST['production_type'] == 'premium') ? 'selected' : ''; ?>>Premium</option>
                                    <option value="special" <?php echo (isset($_POST['production_type']) && $_POST['production_type'] == 'special') ? 'selected' : ''; ?>>Especial</option>
                                </select>
                            </div>
                        </div>

                        <!-- Notas -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">
                                <i class="fas fa-sticky-note me-1"></i>
                                Notas y Observaciones
                            </label>
                            <textarea class="form-control" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3" 
                                      placeholder="Observaciones adicionales sobre el lote..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                        </div>

                        <!-- Botones de acción -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="<?php echo BASE_URL; ?>production" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>
                                        Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>
                                        Crear Lote
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-generar número de lote si está vacío
    const lotNumberField = document.getElementById('lot_number');
    if (!lotNumberField.value) {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        const time = String(today.getHours()).padStart(2, '0') + String(today.getMinutes()).padStart(2, '0');
        lotNumberField.value = `LOT-${year}${month}${day}-${time}`;
    }

    // Validar fechas
    const productionDate = document.getElementById('production_date');
    const expiryDate = document.getElementById('expiry_date');
    
    function validateDates() {
        if (productionDate.value && expiryDate.value) {
            if (new Date(expiryDate.value) <= new Date(productionDate.value)) {
                expiryDate.setCustomValidity('La fecha de vencimiento debe ser posterior a la fecha de producción');
            } else {
                expiryDate.setCustomValidity('');
            }
        }
    }
    
    productionDate.addEventListener('change', validateDates);
    expiryDate.addEventListener('change', validateDates);

    // Validar formulario antes de enviar
    document.getElementById('createLotForm').addEventListener('submit', function(e) {
        const quantityProduced = document.getElementById('quantity_produced').value;
        
        if (parseFloat(quantityProduced) <= 0) {
            e.preventDefault();
            alert('La cantidad producida debe ser mayor a 0');
            return false;
        }
        
        validateDates();
        
        if (!expiryDate.checkValidity()) {
            e.preventDefault();
            alert('La fecha de vencimiento debe ser posterior a la fecha de producción');
            return false;
        }
    });
});
</script>

<?php
$content = ob_get_clean();
require_once dirname(__DIR__) . '/layouts/main.php';
?>