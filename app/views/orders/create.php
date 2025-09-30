<?php
ob_start();
?>

<!-- Header -->
<div class="content-header">
    <div class="px-4">
        <div class="row mb-3">
            <div class="col-sm-6">
                <h1 class="h3 mb-0 text-dark">
                    <i class="fas fa-plus-circle text-primary me-2"></i>
                    Nueva Preventa
                </h1>
                <p class="text-muted">Crear un nuevo pedido para cliente</p>
            </div>
            <div class="col-sm-6">
                <div class="d-flex justify-content-end">
                    <a href="<?= BASE_URL ?>/pedidos" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver a Lista
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alerts -->
<?php if ($success): ?>
    <div class="px-4 mb-4">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="px-4 mb-4">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<!-- Order Form -->
<div class="px-4">
    <form method="POST" id="orderForm" class="needs-validation" novalidate>
            <div class="row">
                <!-- Customer Information -->
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-user me-2"></i>
                                Información del Cliente
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="customer_id" class="form-label">Cliente *</label>
                                        <select class="form-select" id="customer_id" name="customer_id" required>
                                            <option value="">Seleccionar cliente...</option>
                                            <?php foreach ($customers as $customer): ?>
                                                <option value="<?= $customer['id'] ?>" 
                                                        data-phone="<?= htmlspecialchars($customer['phone']) ?>"
                                                        data-address="<?= htmlspecialchars($customer['address']) ?>"
                                                        <?= $customer_id == $customer['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($customer['business_name']) ?>
                                                    <?php if ($customer['contact_name']): ?>
                                                        - <?= htmlspecialchars($customer['contact_name']) ?>
                                                    <?php endif; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">
                                            Debe seleccionar un cliente
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="customer_search" class="form-label">Buscar Cliente</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="customer_search" 
                                                   placeholder="Buscar por nombre o teléfono...">
                                            <button class="btn btn-outline-primary" type="button" onclick="openNewCustomerModal()">
                                                <i class="fas fa-plus"></i> Nuevo
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row" id="customer_info" style="display: none;">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Teléfono</label>
                                        <input type="text" class="form-control" id="customer_phone" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Dirección</label>
                                        <input type="text" class="form-control" id="customer_address" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Details -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-shopping-cart me-2"></i>
                                Productos del Pedido
                            </h6>
                            <button type="button" class="btn btn-success btn-sm" onclick="addProduct()">
                                <i class="fas fa-plus"></i> Agregar Producto
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="productsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="35%">Producto</th>
                                            <th width="15%">Stock Disponible</th>
                                            <th width="15%">Cantidad</th>
                                            <th width="15%">Precio Unitario</th>
                                            <th width="15%">Subtotal</th>
                                            <th width="5%">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="products_tbody">
                                        <!-- Los productos se agregan dinámicamente -->
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-info">
                                            <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                            <td><strong id="total_amount">$0.00</strong></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            
                            <div class="alert alert-info" id="no_products" style="display: none;">
                                <i class="fas fa-info-circle me-2"></i>
                                No hay productos agregados. Haga clic en "Agregar Producto" para comenzar.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Configuration -->
                <div class="col-lg-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-cogs me-2"></i>
                                Configuración del Pedido
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="delivery_date" class="form-label">Fecha de Entrega</label>
                                <input type="date" class="form-control" id="delivery_date" name="delivery_date" 
                                       min="<?= date('Y-m-d') ?>">
                            </div>

                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Método de Pago</label>
                                <select class="form-select" id="payment_method" name="payment_method">
                                    <option value="cash">Efectivo</option>
                                    <option value="transfer">Transferencia</option>
                                    <option value="credit">Crédito</option>
                                    <option value="card">Tarjeta</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="discount_amount" class="form-label">Descuento ($)</label>
                                <input type="number" class="form-control" id="discount_amount" name="discount_amount" 
                                       min="0" step="0.01" value="0" onchange="updateTotals()">
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Notas del Pedido</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" 
                                          placeholder="Observaciones especiales, instrucciones de entrega, etc."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-success">
                                <i class="fas fa-calculator me-2"></i>
                                Resumen del Pedido
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <span>Subtotal:</span>
                                </div>
                                <div class="col-6 text-end">
                                    <span id="subtotal_display">$0.00</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <span>Descuento:</span>
                                </div>
                                <div class="col-6 text-end">
                                    <span id="discount_display">$0.00</span>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-6">
                                    <strong>Total Final:</strong>
                                </div>
                                <div class="col-6 text-end">
                                    <strong id="final_total_display">$0.00</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card shadow">
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg" name="action" value="save">
                                    <i class="fas fa-save me-2"></i>
                                    Guardar Pedido
                                </button>
                                <button type="submit" class="btn btn-success" name="action" value="save_and_view">
                                    <i class="fas fa-eye me-2"></i>
                                    Guardar y Ver
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                    <i class="fas fa-undo me-2"></i>
                                    Limpiar Formulario
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

<!-- Product Selection Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalLabel">Seleccionar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="product_search" placeholder="Buscar producto...">
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" id="product_selection_table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Stock</th>
                                <th>Precio</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= htmlspecialchars($product['code']) ?></td>
                                <td><?= htmlspecialchars($product['name']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $product['total_stock'] > 0 ? 'success' : 'danger' ?>">
                                        <?= number_format($product['total_stock'] ?? 0, 2) ?> <?= $product['unit_type'] ?? 'pcs' ?>
                                    </span>
                                </td>
                                <td>$<?= number_format($product['price_per_unit'], 2) ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            onclick="selectProduct(<?= $product['id'] ?>, '<?= htmlspecialchars($product['name']) ?>', <?= $product['price_per_unit'] ?>, <?= $product['total_stock'] ?? 0 ?>)"
                                            <?= ($product['total_stock'] ?? 0) <= 0 ? 'disabled' : '' ?>>
                                        Seleccionar
                                    </button>
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

<!-- New Customer Modal -->
<div class="modal fade" id="newCustomerModal" tabindex="-1" aria-labelledby="newCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newCustomerModalLabel">Nuevo Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="newCustomerForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="new_business_name" class="form-label">Nombre del Negocio *</label>
                        <input type="text" class="form-control" id="new_business_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_contact_name" class="form-label">Nombre de Contacto</label>
                        <input type="text" class="form-control" id="new_contact_name">
                    </div>
                    <div class="mb-3">
                        <label for="new_phone" class="form-label">Teléfono</label>
                        <input type="text" class="form-control" id="new_phone">
                    </div>
                    <div class="mb-3">
                        <label for="new_address" class="form-label">Dirección</label>
                        <textarea class="form-control" id="new_address" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Cliente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let productRowIndex = 0;
let subtotal = 0;

$(document).ready(function() {
    // Mostrar productos vacío al cargar
    showNoProductsMessage();
    
    // Customer selection handler
    $('#customer_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        if (selectedOption.val()) {
            $('#customer_phone').val(selectedOption.data('phone') || '');
            $('#customer_address').val(selectedOption.data('address') || '');
            $('#customer_info').show();
        } else {
            $('#customer_info').hide();
        }
    });
    
    // Customer search
    $('#customer_search').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('#customer_id option').each(function() {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.includes(searchTerm) || $(this).val() === '');
        });
    });
    
    // Product search in modal
    $('#product_search').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('#product_selection_table tbody tr').each(function() {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.includes(searchTerm));
        });
    });
    
    // Form validation
    $('#orderForm').on('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        } else if ($('#products_tbody tr').length === 0) {
            e.preventDefault();
            showAlert('error', 'Debe agregar al menos un producto al pedido');
        }
        
        $(this).addClass('was-validated');
        
        // Set redirect flag
        if ($(document.activeElement).val() === 'save_and_view') {
            $('<input>').attr({
                type: 'hidden',
                name: 'redirect_to_view',
                value: '1'
            }).appendTo(this);
        }
    });
    
    // New customer form
    $('#newCustomerForm').on('submit', function(e) {
        e.preventDefault();
        createNewCustomer();
    });
});

function addProduct() {
    $('#productModal').modal('show');
}

function selectProduct(productId, productName, price, stockQuantity) {
    // Check if product already exists
    if ($(`input[name="products[${productId}][product_id]"]`).length > 0) {
        showAlert('warning', 'Este producto ya está agregado al pedido');
        return;
    }
    
    const row = `
        <tr id="product_row_${productId}">
            <td>
                <strong>${productName}</strong>
                <input type="hidden" name="products[${productId}][product_id]" value="${productId}">
            </td>
            <td>
                <span class="badge bg-info">${stockQuantity.toFixed(2)}</span>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm" 
                       name="products[${productId}][quantity_ordered]" 
                       min="0.001" max="${stockQuantity}" step="0.001" 
                       value="1" onchange="updateRowTotal(${productId}, ${price})" required>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm" 
                       name="products[${productId}][unit_price]" 
                       value="${price}" step="0.01" min="0" 
                       onchange="updateRowTotal(${productId}, ${price})" required>
            </td>
            <td>
                <span id="subtotal_${productId}">$${price.toFixed(2)}</span>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger" 
                        onclick="removeProduct(${productId})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
    
    $('#products_tbody').append(row);
    $('#no_products').hide();
    $('#productModal').modal('hide');
    
    updateTotals();
}

function removeProduct(productId) {
    $(`#product_row_${productId}`).remove();
    
    if ($('#products_tbody tr').length === 0) {
        showNoProductsMessage();
    }
    
    updateTotals();
}

function updateRowTotal(productId, defaultPrice) {
    const quantity = parseFloat($(`input[name="products[${productId}][quantity_ordered]"]`).val()) || 0;
    const unitPrice = parseFloat($(`input[name="products[${productId}][unit_price]"]`).val()) || defaultPrice;
    const rowTotal = quantity * unitPrice;
    
    $(`#subtotal_${productId}`).text('$' + rowTotal.toFixed(2));
    updateTotals();
}

function updateTotals() {
    let subtotal = 0;
    
    $('#products_tbody tr').each(function() {
        const subtotalText = $(this).find('span[id^="subtotal_"]').text();
        const amount = parseFloat(subtotalText.replace('$', '')) || 0;
        subtotal += amount;
    });
    
    const discount = parseFloat($('#discount_amount').val()) || 0;
    const finalTotal = Math.max(0, subtotal - discount);
    
    $('#subtotal_display').text('$' + subtotal.toFixed(2));
    $('#discount_display').text('$' + discount.toFixed(2));
    $('#final_total_display').text('$' + finalTotal.toFixed(2));
    $('#total_amount').text('$' + finalTotal.toFixed(2));
}

function showNoProductsMessage() {
    $('#no_products').show();
}

function resetForm() {
    if (confirm('¿Está seguro de que desea limpiar el formulario? Se perderán todos los datos ingresados.')) {
        $('#orderForm')[0].reset();
        $('#products_tbody').empty();
        $('#customer_info').hide();
        showNoProductsMessage();
        updateTotals();
        $('#orderForm').removeClass('was-validated');
    }
}

function openNewCustomerModal() {
    $('#newCustomerModal').modal('show');
}

function createNewCustomer() {
    const customerData = {
        business_name: $('#new_business_name').val(),
        contact_name: $('#new_contact_name').val(),
        phone: $('#new_phone').val(),
        address: $('#new_address').val()
    };
    
    if (!customerData.business_name) {
        showAlert('error', 'El nombre del negocio es requerido');
        return;
    }
    
    $.ajax({
        url: '<?= BASE_URL ?>/clientes/create',
        method: 'POST',
        data: customerData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.success) {
                // Add new customer to select
                const option = new Option(
                    customerData.business_name + (customerData.contact_name ? ' - ' + customerData.contact_name : ''),
                    response.customer_id, 
                    true, 
                    true
                );
                $(option).attr('data-phone', customerData.phone);
                $(option).attr('data-address', customerData.address);
                $('#customer_id').append(option).trigger('change');
                
                $('#newCustomerModal').modal('hide');
                $('#newCustomerForm')[0].reset();
                showAlert('success', 'Cliente creado exitosamente');
            } else {
                showAlert('error', response.error || 'Error al crear el cliente');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON || {};
            showAlert('error', response.error || 'Error de conexión al crear el cliente');
        }
    });
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'warning' ? 'alert-warning' : 'alert-danger';
    const icon = type === 'success' ? 'fa-check-circle' : 
                type === 'warning' ? 'fa-exclamation-triangle' : 'fa-times-circle';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas ${icon} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    const alertContainer = document.querySelector('.content-header') || document.querySelector('.container-fluid');
    if (alertContainer) {
        alertContainer.insertAdjacentHTML('afterend', alertHtml);
    }
    
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

.table th {
    font-weight: 600;
    font-size: 0.875rem;
}

.form-control-sm {
    font-size: 0.875rem;
}

.badge {
    font-size: 0.75rem;
}

#productsTable {
    font-size: 0.875rem;
}

.modal-lg {
    max-width: 800px;
}

.btn-group .btn {
    margin-right: 2px;
}

.alert {
    margin-bottom: 1rem;
}

.content-header {
    background: transparent;
    border-bottom: 1px solid #dee2e6;
    margin-bottom: 1.5rem;
    padding: 1rem 0;
    margin-top: 0;
}

.content-header h1 {
    color: #495057;
    font-weight: 600;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.8rem;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .content-header {
        padding: 0.5rem 0;
        margin-bottom: 1rem;
    }
}
</style>

<?php
$content = ob_get_clean();
require_once dirname(__DIR__) . '/layouts/main.php';
?>