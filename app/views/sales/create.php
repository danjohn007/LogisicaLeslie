<?php
// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

// Obtener datos necesarios
$customerModel = new Customer();
$productModel = new Product();
$inventoryModel = new Inventory();

$customers = $customerModel->findActive();
$products = $productModel->findActive();
?>

<div class="container-fluid">
    <form id="newSaleForm">
        <!-- Información general de la venta -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-info-circle"></i> Información de la Venta</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Cliente</label>
                            <select class="form-control" id="customer_id" name="customer_id">
                                <option value="">Cliente General</option>
                                <?php foreach ($customers as $customer): ?>
                                <option value="<?php echo $customer['id']; ?>">
                                    <?php echo htmlspecialchars($customer['business_name']); ?>
                                    <?php if ($customer['contact_name']): ?>
                                        - <?php echo htmlspecialchars($customer['contact_name']); ?>
                                    <?php endif; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">
                                <a href="#" id="addNewCustomer">+ Agregar nuevo cliente</a>
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Método de Pago *</label>
                            <select class="form-control" id="payment_method" name="payment_method" required>
                                <option value="">Seleccionar método</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="tarjeta">Tarjeta</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="credito">Crédito</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notas</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      placeholder="Notas adicionales sobre la venta"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-calculator"></i> Resumen</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <td>Subtotal:</td>
                                <td class="text-end">$<span id="subtotalAmount">0.00</span></td>
                            </tr>
                            <tr>
                                <td>IVA (16%):</td>
                                <td class="text-end">$<span id="taxAmount">0.00</span></td>
                            </tr>
                            <tr class="table-success">
                                <td><strong>Total:</strong></td>
                                <td class="text-end"><strong>$<span id="totalAmount">0.00</span></strong></td>
                            </tr>
                        </table>
                        
                        <div class="mt-3">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg" id="saveSaleBtn">
                                    <i class="fas fa-save"></i> Guardar Venta
                                </button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times"></i> Cancelar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Productos -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6><i class="fas fa-shopping-cart"></i> Productos de la Venta</h6>
                        <button type="button" class="btn btn-primary btn-sm" id="addProductBtn">
                            <i class="fas fa-plus"></i> Agregar Producto
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="productsTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Producto</th>
                                        <th>Disponible</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unit.</th>
                                        <th>Subtotal</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="productsTableBody">
                                    <!-- Filas de productos se agregan dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                        
                        <div id="noProductsMessage" class="text-center text-muted py-4">
                            <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                            <p>No hay productos agregados. Haz clic en "Agregar Producto" para comenzar.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal Agregar Cliente -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addCustomerForm">
                    <div class="mb-3">
                        <label class="form-label">Nombre del Negocio *</label>
                        <input type="text" class="form-control" name="business_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre de Contacto</label>
                        <input type="text" class="form-control" name="contact_name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" name="phone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveCustomerBtn">Guardar Cliente</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let productCounter = 0;
    
    // Agregar producto
    $('#addProductBtn').click(function() {
        addProductRow();
    });

    // Agregar cliente
    $('#addNewCustomer').click(function(e) {
        e.preventDefault();
        $('#addCustomerModal').modal('show');
    });

    // Guardar cliente
    $('#saveCustomerBtn').click(function() {
        const formData = new FormData($('#addCustomerForm')[0]);
        
        $.ajax({
            url: '/customers/create',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Agregar cliente al select
                    const option = new Option(response.data.business_name, response.data.id, false, true);
                    $('#customer_id').append(option);
                    
                    $('#addCustomerModal').modal('hide');
                    $('#addCustomerForm')[0].reset();
                    
                    Swal.fire('Éxito', 'Cliente agregado correctamente', 'success');
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'No se pudo agregar el cliente', 'error');
            }
        });
    });

    // Guardar venta
    $('#newSaleForm').submit(function(e) {
        e.preventDefault();
        
        if (!validateSale()) {
            return;
        }
        
        const saleData = {
            customer_id: $('#customer_id').val(),
            payment_method: $('#payment_method').val(),
            notes: $('#notes').val(),
            products: getProductsData()
        };
        
        $('#saveSaleBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
        
        $.ajax({
            url: '/sales/create',
            method: 'POST',
            data: JSON.stringify(saleData),
            contentType: 'application/json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Venta creada',
                        text: 'La venta ha sido creada correctamente',
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonText: 'Ver venta',
                        cancelButtonText: 'Cerrar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.open('/sales/print/' + response.sale_id, '_blank');
                        }
                        $('#newSaleModal').modal('hide');
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'No se pudo crear la venta', 'error');
            },
            complete: function() {
                $('#saveSaleBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Venta');
            }
        });
    });
});

function addProductRow() {
    const row = `
        <tr data-row="${productCounter}">
            <td>
                <select class="form-control product-select" name="products[${productCounter}][product_id]" required>
                    <option value="">Seleccionar producto</option>
                    <?php foreach ($products as $product): ?>
                    <option value="<?php echo $product['id']; ?>" 
                            data-price="<?php echo $product['price']; ?>"
                            data-code="<?php echo htmlspecialchars($product['code']); ?>">
                        <?php echo htmlspecialchars($product['code'] . ' - ' . $product['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <span class="availability-badge badge badge-secondary">-</span>
            </td>
            <td>
                <input type="number" class="form-control quantity-input" 
                       name="products[${productCounter}][quantity]" 
                       min="1" step="1" required>
            </td>
            <td>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control price-input" 
                           name="products[${productCounter}][unit_price]" 
                           min="0" step="0.01" required>
                </div>
            </td>
            <td>
                <strong class="subtotal-display">$0.00</strong>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-row" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
    
    $('#productsTableBody').append(row);
    $('#noProductsMessage').hide();
    
    // Eventos para la nueva fila
    attachRowEvents(productCounter);
    
    productCounter++;
}

function attachRowEvents(rowIndex) {
    const row = $(`tr[data-row="${rowIndex}"]`);
    
    // Cambio de producto
    row.find('.product-select').change(function() {
        const productId = $(this).val();
        const price = $(this).find('option:selected').data('price');
        
        if (productId) {
            // Obtener disponibilidad
            checkAvailability(productId, row);
            
            // Establecer precio
            row.find('.price-input').val(price);
            
            calculateRowSubtotal(row);
        } else {
            row.find('.availability-badge').text('-').removeClass().addClass('badge badge-secondary');
            row.find('.price-input').val('');
            calculateRowSubtotal(row);
        }
    });
    
    // Cambio de cantidad o precio
    row.find('.quantity-input, .price-input').on('input', function() {
        const quantity = parseInt(row.find('.quantity-input').val()) || 0;
        const availableQuantity = parseInt(row.find('.availability-badge').text()) || 0;
        
        if (quantity > availableQuantity && availableQuantity > 0) {
            $(this).addClass('is-invalid');
            Swal.fire('Advertencia', `Solo hay ${availableQuantity} unidades disponibles`, 'warning');
        } else {
            $(this).removeClass('is-invalid');
        }
        
        calculateRowSubtotal(row);
    });
    
    // Eliminar fila
    row.find('.remove-row').click(function() {
        row.remove();
        calculateTotals();
        
        if ($('#productsTableBody tr').length === 0) {
            $('#noProductsMessage').show();
        }
    });
}

function checkAvailability(productId, row) {
    $.get('/inventory/availability/' + productId, function(response) {
        if (response.success) {
            const available = response.available_quantity;
            const badge = row.find('.availability-badge');
            
            badge.text(available);
            
            if (available > 10) {
                badge.removeClass().addClass('badge badge-success');
            } else if (available > 0) {
                badge.removeClass().addClass('badge badge-warning');
            } else {
                badge.removeClass().addClass('badge badge-danger');
            }
        }
    });
}

function calculateRowSubtotal(row) {
    const quantity = parseFloat(row.find('.quantity-input').val()) || 0;
    const price = parseFloat(row.find('.price-input').val()) || 0;
    const subtotal = quantity * price;
    
    row.find('.subtotal-display').text('$' + subtotal.toFixed(2));
    
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    
    $('#productsTableBody tr').each(function() {
        const rowSubtotal = parseFloat($(this).find('.subtotal-display').text().replace('$', '')) || 0;
        subtotal += rowSubtotal;
    });
    
    const tax = subtotal * 0.16;
    const total = subtotal + tax;
    
    $('#subtotalAmount').text(subtotal.toFixed(2));
    $('#taxAmount').text(tax.toFixed(2));
    $('#totalAmount').text(total.toFixed(2));
}

function validateSale() {
    if (!$('#payment_method').val()) {
        Swal.fire('Error', 'Debe seleccionar un método de pago', 'error');
        return false;
    }
    
    if ($('#productsTableBody tr').length === 0) {
        Swal.fire('Error', 'Debe agregar al menos un producto', 'error');
        return false;
    }
    
    // Validar productos
    let isValid = true;
    $('#productsTableBody tr').each(function() {
        const productId = $(this).find('.product-select').val();
        const quantity = $(this).find('.quantity-input').val();
        const price = $(this).find('.price-input').val();
        
        if (!productId || !quantity || !price) {
            isValid = false;
            return false;
        }
    });
    
    if (!isValid) {
        Swal.fire('Error', 'Complete todos los campos de los productos', 'error');
        return false;
    }
    
    return true;
}

function getProductsData() {
    const products = [];
    
    $('#productsTableBody tr').each(function() {
        const productData = {
            product_id: $(this).find('.product-select').val(),
            quantity: $(this).find('.quantity-input').val(),
            unit_price: $(this).find('.price-input').val()
        };
        
        if (productData.product_id && productData.quantity && productData.unit_price) {
            products.push(productData);
        }
    });
    
    return products;
}

// Agregar primera fila al cargar
addProductRow();
</script>