<?php
ob_start();
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Gestión de Producción</h1>
                    <p class="text-muted mb-0">Control de lotes y procesos de producción</p>
                </div>
                <div>
                    <a href="<?php echo BASE_URL; ?>produccion/create" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Nuevo Lote
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas de Producción -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Lotes Activos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo count($production_lots ?? []); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-industry fa-2x text-gray-300"></i>
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
                                Productos Disponibles
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo count($products ?? []); ?>
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
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Producción Hoy
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php 
                                $todayCount = 0;
                                foreach ($production_lots ?? [] as $lot) {
                                    if (date('Y-m-d', strtotime($lot['production_date'])) === date('Y-m-d')) {
                                        $todayCount++;
                                    }
                                }
                                echo $todayCount;
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
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
                                Próximos a Vencer
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php 
                                $expiringCount = 0;
                                $nextWeek = date('Y-m-d', strtotime('+7 days'));
                                foreach ($production_lots ?? [] as $lot) {
                                    if ($lot['expiry_date'] && $lot['expiry_date'] <= $nextWeek) {
                                        $expiringCount++;
                                    }
                                }
                                echo $expiringCount;
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Lotes de Producción -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Lotes de Producción Recientes</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($production_lots)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Número de Lote</th>
                                        <th>Producto</th>
                                        <th>Fecha Producción</th>
                                        <th>Fecha Vencimiento</th>
                                        <th>Cantidad</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($production_lots as $lot): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($lot['lot_number']); ?></strong></td>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($lot['product_code']); ?></span>
                                                <?php echo htmlspecialchars($lot['product_name']); ?>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($lot['production_date'])); ?></td>
                                            <td>
                                                <?php if ($lot['expiry_date']): ?>
                                                    <?php 
                                                    $expiry = strtotime($lot['expiry_date']);
                                                    $today = time();
                                                    $daysLeft = floor(($expiry - $today) / (60 * 60 * 24));
                                                    $badgeClass = $daysLeft <= 7 ? 'bg-danger' : ($daysLeft <= 30 ? 'bg-warning' : 'bg-success');
                                                    ?>
                                                    <span class="badge <?php echo $badgeClass; ?>">
                                                        <?php echo date('d/m/Y', $expiry); ?>
                                                        <?php if ($daysLeft > 0): ?>
                                                            (<?php echo $daysLeft; ?> días)
                                                        <?php elseif ($daysLeft === 0): ?>
                                                            (Hoy)
                                                        <?php else: ?>
                                                            (Vencido)
                                                        <?php endif; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">No aplica</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo number_format($lot['quantity_produced']); ?></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo ucfirst($lot['production_type']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $statusBadges = [
                                                    'en_produccion' => 'bg-warning',
                                                    'terminado' => 'bg-success',
                                                    'vendido' => 'bg-secondary'
                                                ];
                                                $statusNames = [
                                                    'en_produccion' => 'En Producción',
                                                    'terminado' => 'Terminado',
                                                    'vendido' => 'Vendido'
                                                ];
                                                $badgeClass = $statusBadges[$lot['status']] ?? 'bg-secondary';
                                                $statusName = $statusNames[$lot['status']] ?? $lot['status'];
                                                ?>
                                                <span class="badge <?php echo $badgeClass; ?>">
                                                    <?php echo $statusName; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?php echo BASE_URL; ?>produccion/viewLot/<?php echo $lot['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="<?php echo BASE_URL; ?>produccion/edit/<?php echo $lot['id']; ?>" 
                                                       class="btn btn-sm btn-outline-warning" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-industry fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No hay lotes de producción registrados</h5>
                            <p class="text-muted">Comience creando su primer lote de producción.</p>
                            <a href="<?php echo BASE_URL; ?>produccion/create" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>
                                Crear Primer Lote
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Inicializar DataTable si hay datos
<?php if (!empty($production_lots)): ?>
$(document).ready(function() {
    $('#dataTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "order": [[ 2, "desc" ]] // Ordenar por fecha de producción descendente
    });
});
<?php endif; ?>
</script>

<?php
$content = ob_get_clean();
require_once dirname(__DIR__) . '/layouts/main.php';
?>