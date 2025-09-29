<?php
ob_start();
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Control de Inventario</h1>
                    <p class="text-muted mb-0">Gestión y control de stock de productos</p>
                </div>
                <div>
                    <div class="btn-group" role="group">
                        <a href="<?php echo BASE_URL; ?>inventario/movement" class="btn btn-success">
                            <i class="fas fa-exchange-alt me-2"></i>
                            Movimiento
                        </a>
                        <a href="<?php echo BASE_URL; ?>inventario/adjust" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>
                            Ajustar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas del Inventario -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Productos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $inventory_stats['total_products'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
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
                                Valor Total
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?php echo number_format($inventory_stats['total_value'] ?? 0, 2); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                                Stock Bajo
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $inventory_stats['low_stock_products'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Por Vencer
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $inventory_stats['expiring_soon'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs de Vistas -->
    <div class="row mb-4">
        <div class="col-12">
            <ul class="nav nav-tabs" id="inventoryTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo ($view_mode === 'summary') ? 'active' : ''; ?>" 
                            id="summary-tab" 
                            data-bs-toggle="tab" 
                            data-bs-target="#summary" 
                            type="button">
                        <i class="fas fa-chart-pie me-2"></i>
                        Resumen por Producto
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo ($view_mode === 'details') ? 'active' : ''; ?>" 
                            id="details-tab" 
                            data-bs-toggle="tab" 
                            data-bs-target="#details" 
                            type="button">
                        <i class="fas fa-list me-2"></i>
                        Detalle por Lotes
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo ($view_mode === 'expiring') ? 'active' : ''; ?>" 
                            id="expiring-tab" 
                            data-bs-toggle="tab" 
                            data-bs-target="#expiring" 
                            type="button">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Productos por Vencer
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <!-- Contenido de las Tabs -->
    <div class="tab-content" id="inventoryTabsContent">
        <!-- Resumen por Producto -->
        <div class="tab-pane fade <?php echo ($view_mode === 'summary') ? 'show active' : ''; ?>" 
             id="summary" role="tabpanel">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie me-2"></i>
                        Resumen de Inventario por Producto
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($inventory_summary)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="summaryTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Producto</th>
                                        <th>Categoría</th>
                                        <th>Stock Total</th>
                                        <th>Reservado</th>
                                        <th>Disponible</th>
                                        <th>Stock Mínimo</th>
                                        <th>Lotes</th>
                                        <th>Valor</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($inventory_summary as $item): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($item['product_code']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                            <td><?php echo htmlspecialchars($item['category_name'] ?? 'Sin categoría'); ?></td>
                                            <td>
                                                <span class="fw-bold"><?php echo number_format($item['total_stock'], 2); ?></span>
                                                <small class="text-muted"><?php echo $item['unit_type']; ?></small>
                                            </td>
                                            <td><?php echo number_format($item['total_reserved'], 2); ?></td>
                                            <td><?php echo number_format($item['total_available'], 2); ?></td>
                                            <td><?php echo number_format($item['minimum_stock'], 2); ?></td>
                                            <td><?php echo $item['total_lots']; ?></td>
                                            <td>$<?php echo number_format($item['inventory_value'], 2); ?></td>
                                            <td>
                                                <?php
                                                $statusClasses = [
                                                    'empty' => 'bg-secondary',
                                                    'critical' => 'bg-danger',
                                                    'low' => 'bg-warning',
                                                    'warning' => 'bg-info',
                                                    'normal' => 'bg-success'
                                                ];
                                                $statusNames = [
                                                    'empty' => 'Vacío',
                                                    'critical' => 'Crítico',
                                                    'low' => 'Bajo',
                                                    'warning' => 'Atención',
                                                    'normal' => 'Normal'
                                                ];
                                                $statusClass = $statusClasses[$item['stock_status']] ?? 'bg-secondary';
                                                $statusName = $statusNames[$item['stock_status']] ?? 'Desconocido';
                                                ?>
                                                <span class="badge <?php echo $statusClass; ?>">
                                                    <?php echo $statusName; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?php echo BASE_URL; ?>inventario/details/<?php echo $item['product_id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-success" 
                                                            onclick="quickMovement(<?php echo $item['product_id']; ?>)" 
                                                            title="Movimiento rápido">
                                                        <i class="fas fa-exchange-alt"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No hay productos en inventario</h5>
                            <p class="text-muted">Los productos aparecerán aquí cuando se registren lotes de producción.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Detalle por Lotes -->
        <div class="tab-pane fade <?php echo ($view_mode === 'details') ? 'show active' : ''; ?>" 
             id="details" role="tabpanel">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list me-2"></i>
                        Inventario Detallado por Lotes
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($inventory_details)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="detailsTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Lote</th>
                                        <th>Ubicación</th>
                                        <th>Cantidad</th>
                                        <th>Reservado</th>
                                        <th>Disponible</th>
                                        <th>Producción</th>
                                        <th>Vencimiento</th>
                                        <th>Estado Stock</th>
                                        <th>Estado Venc.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($inventory_details as $item): ?>
                                        <tr>
                                            <td>
                                                <strong>[<?php echo htmlspecialchars($item['product_code']); ?>]</strong><br>
                                                <?php echo htmlspecialchars($item['product_name']); ?>
                                            </td>
                                            <td><strong><?php echo htmlspecialchars($item['lot_number']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($item['location']); ?></td>
                                            <td><?php echo number_format($item['quantity'], 2); ?></td>
                                            <td><?php echo number_format($item['reserved_quantity'], 2); ?></td>
                                            <td><strong><?php echo number_format($item['available_quantity'], 2); ?></strong></td>
                                            <td><?php echo date('d/m/Y', strtotime($item['production_date'])); ?></td>
                                            <td>
                                                <?php if ($item['expiry_date']): ?>
                                                    <?php echo date('d/m/Y', strtotime($item['expiry_date'])); ?>
                                                    <?php if ($item['days_to_expiry'] !== null): ?>
                                                        <br><small class="text-muted">(<?php echo $item['days_to_expiry']; ?> días)</small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Sin vencimiento</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $stockStatusClasses = [
                                                    'empty' => 'bg-secondary',
                                                    'critical' => 'bg-danger',
                                                    'low' => 'bg-warning',
                                                    'warning' => 'bg-info',
                                                    'normal' => 'bg-success'
                                                ];
                                                $stockStatusNames = [
                                                    'empty' => 'Vacío',
                                                    'critical' => 'Crítico',
                                                    'low' => 'Bajo',
                                                    'warning' => 'Atención',
                                                    'normal' => 'Normal'
                                                ];
                                                $stockClass = $stockStatusClasses[$item['stock_status']] ?? 'bg-secondary';
                                                $stockName = $stockStatusNames[$item['stock_status']] ?? 'Desconocido';
                                                ?>
                                                <span class="badge <?php echo $stockClass; ?>">
                                                    <?php echo $stockName; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $expiryStatusClasses = [
                                                    'no_expiry' => 'bg-secondary',
                                                    'expired' => 'bg-danger',
                                                    'expires_soon' => 'bg-warning',
                                                    'expires_month' => 'bg-info',
                                                    'good' => 'bg-success'
                                                ];
                                                $expiryStatusNames = [
                                                    'no_expiry' => 'Sin venc.',
                                                    'expired' => 'Vencido',
                                                    'expires_soon' => 'Vence pronto',
                                                    'expires_month' => 'Vence en mes',
                                                    'good' => 'Bueno'
                                                ];
                                                $expiryClass = $expiryStatusClasses[$item['expiry_status']] ?? 'bg-secondary';
                                                $expiryName = $expiryStatusNames[$item['expiry_status']] ?? 'Desconocido';
                                                ?>
                                                <span class="badge <?php echo $expiryClass; ?>">
                                                    <?php echo $expiryName; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-list fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No hay detalles de inventario</h5>
                            <p class="text-muted">Los lotes aparecerán aquí cuando se registren en el sistema.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Productos por Vencer -->
        <div class="tab-pane fade <?php echo ($view_mode === 'expiring') ? 'show active' : ''; ?>" 
             id="expiring" role="tabpanel">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Productos Próximos a Vencer (30 días)
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($expiring_products)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="expiringTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Urgencia</th>
                                        <th>Producto</th>
                                        <th>Lote</th>
                                        <th>Cantidad</th>
                                        <th>Ubicación</th>
                                        <th>Fecha Vencimiento</th>
                                        <th>Días Restantes</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($expiring_products as $item): ?>
                                        <tr>
                                            <td>
                                                <?php
                                                $urgencyClasses = [
                                                    'expired' => 'bg-danger',
                                                    'critical' => 'bg-warning',
                                                    'warning' => 'bg-info',
                                                    'attention' => 'bg-light'
                                                ];
                                                $urgencyNames = [
                                                    'expired' => 'VENCIDO',
                                                    'critical' => 'CRÍTICO',
                                                    'warning' => 'ALERTA',
                                                    'attention' => 'ATENCIÓN'
                                                ];
                                                $urgencyClass = $urgencyClasses[$item['urgency_level']] ?? 'bg-secondary';
                                                $urgencyName = $urgencyNames[$item['urgency_level']] ?? 'DESCONOCIDO';
                                                ?>
                                                <span class="badge <?php echo $urgencyClass; ?> fw-bold">
                                                    <?php echo $urgencyName; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong>[<?php echo htmlspecialchars($item['product_code']); ?>]</strong><br>
                                                <?php echo htmlspecialchars($item['product_name']); ?>
                                            </td>
                                            <td><strong><?php echo htmlspecialchars($item['lot_number']); ?></strong></td>
                                            <td><strong><?php echo number_format($item['quantity'], 2); ?></strong></td>
                                            <td><?php echo htmlspecialchars($item['location']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($item['expiry_date'])); ?></td>
                                            <td>
                                                <?php if ($item['days_to_expiry'] < 0): ?>
                                                    <span class="text-danger fw-bold">
                                                        Vencido hace <?php echo abs($item['days_to_expiry']); ?> días
                                                    </span>
                                                <?php elseif ($item['days_to_expiry'] == 0): ?>
                                                    <span class="text-warning fw-bold">Vence HOY</span>
                                                <?php else: ?>
                                                    <span class="fw-bold"><?php echo $item['days_to_expiry']; ?> días</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-primary" 
                                                            onclick="promoteProduct(<?php echo $item['id']; ?>)" 
                                                            title="Promocionar">
                                                        <i class="fas fa-bullhorn"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-warning" 
                                                            onclick="adjustProduct(<?php echo $item['id']; ?>)" 
                                                            title="Ajustar stock">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5 class="text-success">¡Excelente!</h5>
                            <p class="text-muted">No hay productos próximos a vencer en los próximos 30 días.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Movimientos Recientes -->
    <?php if (!empty($recent_movements)): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-history me-2"></i>
                        Movimientos Recientes
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm" width="100%">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Producto</th>
                                    <th>Lote</th>
                                    <th>Cantidad</th>
                                    <th>Usuario</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_movements as $movement): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y H:i', strtotime($movement['movement_date'])); ?></td>
                                        <td>
                                            <?php
                                            $typeClasses = [
                                                'production' => 'bg-success',
                                                'sale' => 'bg-primary',
                                                'return' => 'bg-info',
                                                'adjustment' => 'bg-warning',
                                                'transfer' => 'bg-secondary'
                                            ];
                                            $typeNames = [
                                                'production' => 'Producción',
                                                'sale' => 'Venta',
                                                'return' => 'Devolución',
                                                'adjustment' => 'Ajuste',
                                                'transfer' => 'Transferencia'
                                            ];
                                            $typeClass = $typeClasses[$movement['type']] ?? 'bg-secondary';
                                            $typeName = $typeNames[$movement['type']] ?? $movement['type'];
                                            ?>
                                            <span class="badge <?php echo $typeClass; ?>">
                                                <?php echo $typeName; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">[<?php echo $movement['product_code']; ?>]</small><br>
                                            <?php echo htmlspecialchars($movement['product_name']); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($movement['lot_number'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="<?php echo $movement['quantity'] > 0 ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo ($movement['quantity'] > 0 ? '+' : '') . number_format($movement['quantity'], 2); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars(($movement['first_name'] ?? '') . ' ' . ($movement['last_name'] ?? '')); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    // Inicializar DataTables
    if ($('#summaryTable tbody tr').length > 0) {
        $('#summaryTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "order": [[ 9, "asc" ], [ 3, "desc" ]], // Ordenar por estado y stock
            "pageLength": 25
        });
    }
    
    if ($('#detailsTable tbody tr').length > 0) {
        $('#detailsTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "order": [[ 8, "asc" ], [ 7, "asc" ]], // Ordenar por estado de stock y vencimiento
            "pageLength": 25
        });
    }
    
    if ($('#expiringTable tbody tr').length > 0) {
        $('#expiringTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "order": [[ 6, "asc" ]], // Ordenar por días restantes
            "pageLength": 25
        });
    }
});

function quickMovement(productId) {
    window.location.href = '<?php echo BASE_URL; ?>inventario/movement?product_id=' + productId;
}

function promoteProduct(inventoryId) {
    alert('Función de promoción en desarrollo');
}

function adjustProduct(inventoryId) {
    window.location.href = '<?php echo BASE_URL; ?>inventario/adjust?inventory_id=' + inventoryId;
}
</script>

<?php
$content = ob_get_clean();
require_once dirname(__DIR__) . '/layouts/main.php';
?>