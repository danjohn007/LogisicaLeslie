<?php
// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

// Obtener ID de venta desde la URL
$saleId = $_GET['id'] ?? null;
if (!$saleId) {
    header('Location: /sales');
    exit;
}

// Obtener detalles de la venta
$saleModel = new Sale();
$sale = $saleModel->getSaleWithDetails($saleId);
$saleItems = $saleModel->getSaleDetails($saleId);

if (!$sale) {
    header('Location: /sales');
    exit;
}
?>

<div class="container-fluid">
    <!-- Header con información principal -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="text-primary mb-3">
                                <i class="fas fa-receipt"></i> 
                                Venta <?php echo htmlspecialchars($sale['sale_number']); ?>
                            </h4>
                            
                            <div class="mb-2">
                                <strong>Fecha:</strong> 
                                <?php echo date('d/m/Y H:i', strtotime($sale['sale_date'])); ?>
                            </div>
                            
                            <div class="mb-2">
                                <strong>Cliente:</strong>
                                <?php if ($sale['customer_id']): ?>
                                    <div class="mt-1">
                                        <span class="fw-bold"><?php echo htmlspecialchars($sale['customer_business_name']); ?></span><br>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($sale['customer_contact_name']); ?>
                                        </small>
                                        <?php if ($sale['customer_phone']): ?>
                                            <br><small class="text-muted">
                                                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($sale['customer_phone']); ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">Cliente General</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-2">
                                <strong>Vendedor:</strong>
                                <span class="badge badge-info">
                                    <?php echo htmlspecialchars($sale['seller_name'] . ' ' . $sale['seller_lastname']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="text-end">
                                <div class="mb-3">
                                    <h2 class="text-success">
                                        $<?php echo number_format($sale['total_amount'], 2); ?>
                                    </h2>
                                </div>
                                
                                <div class="mb-2">
                                    <strong>Método de Pago:</strong>
                                    <span class="badge badge-<?php 
                                        echo match($sale['payment_method']) {
                                            'efectivo' => 'success',
                                            'tarjeta' => 'primary',
                                            'transferencia' => 'info',
                                            'credito' => 'warning',
                                            default => 'secondary'
                                        };
                                    ?>">
                                        <?php echo ucfirst($sale['payment_method']); ?>
                                    </span>
                                </div>
                                
                                <?php if ($sale['notes']): ?>
                                <div class="mb-2">
                                    <strong>Notas:</strong><br>
                                    <small class="text-muted"><?php echo nl2br(htmlspecialchars($sale['notes'])); ?></small>
                                </div>
                                <?php endif; ?>
                                
                                <div class="btn-group">
                                    <button class="btn btn-success" onclick="printSale(<?php echo $sale['id']; ?>)">
                                        <i class="fas fa-print"></i> Imprimir
                                    </button>
                                    <button class="btn btn-secondary" onclick="window.history.back()">
                                        <i class="fas fa-arrow-left"></i> Volver
                                    </button>
                                    <?php if (date('Y-m-d') == date('Y-m-d', strtotime($sale['sale_date']))): ?>
                                    <button class="btn btn-danger" onclick="cancelSale(<?php echo $sale['id']; ?>)">
                                        <i class="fas fa-ban"></i> Cancelar
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalles de productos -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-shopping-cart"></i> Productos Vendidos</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Código</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unit.</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $subtotal = 0;
                                foreach ($saleItems as $item): 
                                    $itemSubtotal = $item['quantity'] * $item['unit_price'];
                                    $subtotal += $itemSubtotal;
                                ?>
                                <tr>
                                    <td>
                                        <span class="font-monospace"><?php echo htmlspecialchars($item['product_code']); ?></span>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary"><?php echo $item['quantity']; ?></span>
                                    </td>
                                    <td>
                                        $<?php echo number_format($item['unit_price'], 2); ?>
                                    </td>
                                    <td>
                                        <strong>$<?php echo number_format($itemSubtotal, 2); ?></strong>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                    <td><strong>$<?php echo number_format($subtotal, 2); ?></strong></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>IVA (16%):</strong></td>
                                    <td><strong>$<?php echo number_format($subtotal * 0.16, 2); ?></strong></td>
                                </tr>
                                <tr class="table-success">
                                    <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                    <td><strong class="text-success">$<?php echo number_format($sale['total_amount'], 2); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Información adicional -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6><i class="fas fa-info-circle"></i> Información de Creación</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Creada:</strong></td>
                            <td><?php echo date('d/m/Y H:i:s', strtotime($sale['created_at'])); ?></td>
                        </tr>
                        <?php if ($sale['updated_at'] != $sale['created_at']): ?>
                        <tr>
                            <td><strong>Modificada:</strong></td>
                            <td><?php echo date('d/m/Y H:i:s', strtotime($sale['updated_at'])); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td><strong>ID de Venta:</strong></td>
                            <td><span class="font-monospace"><?php echo $sale['id']; ?></span></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <?php if ($sale['customer_id']): ?>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6><i class="fas fa-user"></i> Información del Cliente</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Código:</strong></td>
                            <td><span class="font-monospace"><?php echo htmlspecialchars($sale['customer_code']); ?></span></td>
                        </tr>
                        <?php if ($sale['customer_email']): ?>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>
                                <a href="mailto:<?php echo htmlspecialchars($sale['customer_email']); ?>">
                                    <?php echo htmlspecialchars($sale['customer_email']); ?>
                                </a>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($sale['customer_address']): ?>
                        <tr>
                            <td><strong>Dirección:</strong></td>
                            <td><?php echo htmlspecialchars($sale['customer_address']); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                    
                    <div class="mt-3">
                        <a href="/customers/view/<?php echo $sale['customer_id']; ?>" 
                           class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i> Ver Cliente Completo
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function printSale(saleId) {
    window.open('/sales/print/' + saleId, '_blank');
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
            $.post('/sales/cancel/' + saleId, function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Cancelada', 
                        text: 'La venta ha sido cancelada',
                        icon: 'success'
                    }).then(() => {
                        window.location.href = '/sales';
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }).fail(function() {
                Swal.fire('Error', 'No se pudo cancelar la venta', 'error');
            });
        }
    });
}
</script>