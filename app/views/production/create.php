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
                    <p class="text-muted mb-0">Registrar un nuevo lote de producción</p>
                </div>
                <div>
                    <a href="<?php echo BASE_URL; ?>produccion" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de Creación -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-plus me-2"></i>
                        Datos del Lote
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Mostrar mensajes -->
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

                    <form method="POST" action="<?php echo BASE_URL; ?>produccion/create" class="needs-validation" novalidate>
                        <div class="row">
                            <!-- Número de Lote -->
                            <div class="col-md-6 mb-3">
                                <label for="lot_number" class="form-label">
                                    <i class="fas fa-hashtag me-1"></i>
                                    Número de Lote <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="lot_number" 
                                       name="lot_number" 
                                       required
                                       value="<?php echo htmlspecialchars($_POST['lot_number'] ?? ''); ?>"
                                       placeholder="Ej: LOT001-2025">
                                <div class="invalid-feedback">
                                    El número de lote es obligatorio
                                </div>
                                <small class="form-text text-muted">
                                    Código único para identificar el lote
                                </small>
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
                                <div class="invalid-feedback">
                                    Debe seleccionar un producto
                                </div>
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
                                       required
                                       value="<?php echo $_POST['production_date'] ?? date('Y-m-d'); ?>">
                                <div class="invalid-feedback">
                                    La fecha de producción es obligatoria
                                </div>
                            </div>

                            <!-- Fecha de Vencimiento -->
                            <div class="col-md-6 mb-3">
                                <label for="expiry_date" class="form-label">
                                    <i class="fas fa-calendar-times me-1"></i>
                                    Fecha de Vencimiento <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       id="expiry_date" 
                                       name="expiry_date" 
                                       required
                                       value="<?php echo htmlspecialchars($_POST['expiry_date'] ?? ''); ?>">
                                <div class="invalid-feedback">
                                    La fecha de vencimiento es obligatoria
                                </div>
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
                                       step="0.001"
                                       min="0"
                                       required
                                       value="<?php echo htmlspecialchars($_POST['quantity_produced'] ?? ''); ?>"
                                       placeholder="0.000">
                                <div class="invalid-feedback">
                                    La cantidad producida es obligatoria y debe ser mayor a 0
                                </div>
                            </div>

                            <!-- Cantidad Disponible -->
                            <div class="col-md-4 mb-3">
                                <label for="quantity_available" class="form-label">
                                    <i class="fas fa-cubes me-1"></i>
                                    Cantidad Disponible
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="quantity_available" 
                                       name="quantity_available" 
                                       step="0.001"
                                       min="0"
                                       value="<?php echo htmlspecialchars($_POST['quantity_available'] ?? $_POST['quantity_produced'] ?? ''); ?>"
                                       placeholder="0.000">
                                <small class="form-text text-muted">
                                    Si se deja vacío, se usará la cantidad producida
                                </small>
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
                                       step="0.01"
                                       min="0"
                                       value="<?php echo htmlspecialchars($_POST['unit_cost'] ?? ''); ?>"
                                       placeholder="0.00">
                                <small class="form-text text-muted">
                                    Costo por unidad (opcional)
                                </small>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Estado de Calidad -->
                            <div class="col-md-6 mb-3">
                                <label for="quality_status" class="form-label">
                                    <i class="fas fa-medal me-1"></i>
                                    Estado de Calidad
                                </label>
                                <select class="form-select" id="quality_status" name="quality_status">
                                    <option value="excellent" <?php echo (isset($_POST['quality_status']) && $_POST['quality_status'] == 'excellent') ? 'selected' : ''; ?>>
                                        Excelente
                                    </option>
                                    <option value="good" <?php echo (!isset($_POST['quality_status']) || $_POST['quality_status'] == 'good') ? 'selected' : ''; ?>>
                                        Buena
                                    </option>
                                    <option value="fair" <?php echo (isset($_POST['quality_status']) && $_POST['quality_status'] == 'fair') ? 'selected' : ''; ?>>
                                        Regular
                                    </option>
                                    <option value="rejected" <?php echo (isset($_POST['quality_status']) && $_POST['quality_status'] == 'rejected') ? 'selected' : ''; ?>>
                                        Rechazada
                                    </option>
                                </select>
                            </div>

                            <!-- Tipo de Producción -->
                            <div class="col-md-6 mb-3">
                                <label for="production_type" class="form-label">
                                    <i class="fas fa-industry me-1"></i>
                                    Tipo de Producción
                                </label>
                                <select class="form-select" id="production_type" name="production_type">
                                    <option value="fresco" <?php echo (!isset($_POST['production_type']) || $_POST['production_type'] == 'fresco') ? 'selected' : ''; ?>>
                                        Fresco
                                    </option>
                                    <option value="curado" <?php echo (isset($_POST['production_type']) && $_POST['production_type'] == 'curado') ? 'selected' : ''; ?>>
                                        Curado
                                    </option>
                                    <option value="semiCurado" <?php echo (isset($_POST['production_type']) && $_POST['production_type'] == 'semiCurado') ? 'selected' : ''; ?>>
                                        Semi-curado
                                    </option>
                                    <option value="especial" <?php echo (isset($_POST['production_type']) && $_POST['production_type'] == 'especial') ? 'selected' : ''; ?>>
                                        Especial
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Notas -->
                        <div class="mb-4">
                            <label for="notes" class="form-label">
                                <i class="fas fa-sticky-note me-1"></i>
                                Notas Adicionales
                            </label>
                            <textarea class="form-control" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3" 
                                      placeholder="Observaciones, comentarios especiales, condiciones de producción..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                        </div>

                        <!-- Botones -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="<?php echo BASE_URL; ?>produccion" class="btn btn-secondary">
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
// Validación de formulario
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();

// Auto-llenar cantidad disponible con la cantidad producida
document.getElementById('quantity_produced').addEventListener('input', function() {
    const quantityAvailable = document.getElementById('quantity_available');
    if (!quantityAvailable.value || quantityAvailable.value == '0') {
        quantityAvailable.value = this.value;
    }
});

// Validar fecha de vencimiento
document.getElementById('expiry_date').addEventListener('change', function() {
    const productionDate = document.getElementById('production_date').value;
    const expiryDate = this.value;
    
    if (productionDate && expiryDate && new Date(expiryDate) <= new Date(productionDate)) {
        alert('La fecha de vencimiento debe ser posterior a la fecha de producción');
        this.focus();
    }
});
</script>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layouts/main.php';
?>