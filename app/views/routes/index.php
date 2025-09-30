<?php
ob_start();
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-sm-6">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-route text-primary me-2"></i>
                Gestión de Rutas
            </h1>
            <p class="text-muted">Optimización y seguimiento de rutas de entrega</p>
        </div>
        <div class="col-sm-6">
            <div class="d-flex justify-content-end">
                <a href="<?= BASE_URL ?>/rutas/create" class="btn btn-primary me-2">
                    <i class="fas fa-plus"></i> Nueva Ruta
                </a>
                <button type="button" class="btn btn-outline-secondary" onclick="refreshData()">
                    <i class="fas fa-sync-alt"></i> Actualizar
                </button>
            </div>
        </div>
    </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="GET" action="<?= BASE_URL ?>/rutas" class="row g-3">
                    <div class="col-md-3">
                        <label for="driver_id" class="form-label">Conductor</label>
                        <select class="form-select" id="driver_id" name="driver_id">
                            <option value="">Todos los conductores</option>
                            <?php if (!empty($drivers)): ?>
                                <?php foreach ($drivers as $driver): ?>
                                    <option value="<?= $driver['id'] ?>" <?= ($filters['driver_id'] ?? '') == $driver['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">Fecha Desde</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" 
                               value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">Fecha Hasta</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" 
                               value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Estado</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Todos los estados</option>
                            <option value="planned" <?= ($filters['status'] ?? '') === 'planned' ? 'selected' : '' ?>>Planificada</option>
                            <option value="in_progress" <?= ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>En Progreso</option>
                            <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completada</option>
                            <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelada</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="<?= BASE_URL ?>/rutas" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Routes Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Lista de Rutas</h6>
                </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="routesTable">
                        <thead class="table-light">
                            <tr>
                                <th>Ruta</th>
                                <th>Conductor</th>
                                <th>Fecha</th>
                                <th>Hora Inicio</th>
                                <th>Estado</th>
                                <th>Pedidos</th>
                                <th>Progreso</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($routes)): ?>
                                <?php foreach ($routes as $route): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($route['route_name'] ?? 'N/A') ?></strong>
                                            <?php if (!empty($route['vehicle_plate'])): ?>
                                                <br><small class="text-muted">
                                                    <i class="fas fa-truck"></i> <?= htmlspecialchars($route['vehicle_plate']) ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($route['driver_name'])): ?>
                                                <i class="fas fa-user"></i> <?= htmlspecialchars($route['driver_name']) ?>
                                            <?php else: ?>
                                                <span class="text-muted">Sin asignar</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($route['route_date'])) ?></td>
                                        <td><?= htmlspecialchars($route['start_time'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php
                                            $statusClass = [
                                                'planned' => 'bg-secondary',
                                                'in_progress' => 'bg-info',
                                                'completed' => 'bg-success',
                                                'cancelled' => 'bg-danger'
                                            ];
                                            $statusText = [
                                                'planned' => 'Planificada',
                                                'in_progress' => 'En Progreso',
                                                'completed' => 'Completada',
                                                'cancelled' => 'Cancelada'
                                            ];
                                            $status = $route['status'] ?? 'planned';
                                            ?>
                                            <span class="badge <?= $statusClass[$status] ?? 'bg-secondary' ?>">
                                                <?= $statusText[$status] ?? ucfirst($status) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">
                                                <?= $route['total_orders'] ?? 0 ?> pedidos
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            $total = $route['total_orders'] ?? 0;
                                            $delivered = $route['delivered_orders'] ?? 0;
                                            $progress = $total > 0 ? round(($delivered / $total) * 100) : 0;
                                            ?>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar <?= $progress == 100 ? 'bg-success' : 'bg-info' ?>" 
                                                     role="progressbar" 
                                                     style="width: <?= $progress ?>%;" 
                                                     aria-valuenow="<?= $progress ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                    <?= $progress ?>%
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="<?= BASE_URL ?>/rutas/viewRoute/<?= $route['id'] ?>" 
                                                   class="btn btn-sm btn-info" 
                                                   title="Ver Detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($route['status'] === 'planned'): ?>
                                                    <button onclick="startRoute(<?= $route['id'] ?>)" 
                                                            class="btn btn-sm btn-success" 
                                                            title="Iniciar Ruta">
                                                        <i class="fas fa-play"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <?php if ($route['status'] === 'in_progress'): ?>
                                                    <button onclick="completeRoute(<?= $route['id'] ?>)" 
                                                            class="btn btn-sm btn-primary" 
                                                            title="Completar Ruta">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <?php if (in_array($route['status'], ['planned', 'in_progress'])): ?>
                                                    <button onclick="cancelRoute(<?= $route['id'] ?>)" 
                                                            class="btn btn-sm btn-danger" 
                                                            title="Cancelar Ruta">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>No hay rutas registradas con los filtros aplicados</p>
                                        <a href="<?= BASE_URL ?>/rutas/create" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Crear Primera Ruta
                                        </a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#routesTable').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        },
        order: [[2, 'desc']],
        pageLength: 25,
        responsive: true
    });
});

function refreshData() {
    location.reload();
}

function startRoute(routeId) {
    Swal.fire({
        title: '¿Iniciar Ruta?',
        text: 'Se marcará la ruta como iniciada',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, iniciar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= BASE_URL ?>/rutas/start/' + routeId,
                method: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Éxito', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'No se pudo iniciar la ruta', 'error');
                }
            });
        }
    });
}

function completeRoute(routeId) {
    Swal.fire({
        title: '¿Completar Ruta?',
        text: 'Se marcará la ruta como completada',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, completar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= BASE_URL ?>/rutas/complete/' + routeId,
                method: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Éxito', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'No se pudo completar la ruta', 'error');
                }
            });
        }
    });
}

function cancelRoute(routeId) {
    Swal.fire({
        title: '¿Cancelar Ruta?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= BASE_URL ?>/rutas/cancel/' + routeId,
                method: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Cancelada', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'No se pudo cancelar la ruta', 'error');
                }
            });
        }
    });
}
</script>

<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.card.shadow {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}
.progress {
    background-color: #e9ecef;
}
</style>

                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once dirname(__DIR__) . '/layouts/main.php';
?>