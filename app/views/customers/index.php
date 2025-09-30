<?php
ob_start();
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Gestión de Clientes</h1>
                    <p class="text-muted mb-0">Administración de clientes y relaciones comerciales</p>
                </div>
                <div>
                    <div class="btn-group" role="group">
                        <a href="<?php echo BASE_URL; ?>clientes/create" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>
                            Nuevo Cliente
                        </a>
                        <a href="<?php echo BASE_URL; ?>clientes/export" class="btn btn-success">
                            <i class="fas fa-download me-2"></i>
                            Exportar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas de Clientes -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Clientes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $customer_stats['total_customers'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                                Clientes Activos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $customer_stats['active_customers'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
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
                                Nuevos Este Mes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $customer_stats['new_this_month'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-plus fa-2x text-gray-300"></i>
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
                                Pedidos Pendientes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $customer_stats['pending_orders'] ?? 0; ?>
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

    <!-- Filtros y Búsqueda -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-filter me-2"></i>
                        Filtros y Búsqueda
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" id="customerFilters">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="search" class="form-label">Buscar Cliente</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           placeholder="Nombre, teléfono o empresa..."
                                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="status" class="form-label">Estado</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="">Todos</option>
                                        <option value="active" <?php echo ($_GET['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Activos</option>
                                        <option value="inactive" <?php echo ($_GET['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactivos</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="city" class="form-label">Ciudad</label>
                                    <select class="form-control" id="city" name="city">
                                        <option value="">Todas</option>
                                        <?php if (!empty($customer_stats['cities'])): ?>
                                            <?php foreach ($customer_stats['cities'] as $city): ?>
                                                <option value="<?php echo htmlspecialchars($city); ?>" 
                                                        <?php echo ($_GET['city'] ?? '') === $city ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($city); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date_range" class="form-label">Registro</label>
                                    <select class="form-control" id="date_range" name="date_range">
                                        <option value="">Cualquier fecha</option>
                                        <option value="today" <?php echo ($_GET['date_range'] ?? '') === 'today' ? 'selected' : ''; ?>>Hoy</option>
                                        <option value="week" <?php echo ($_GET['date_range'] ?? '') === 'week' ? 'selected' : ''; ?>>Esta semana</option>
                                        <option value="month" <?php echo ($_GET['date_range'] ?? '') === 'month' ? 'selected' : ''; ?>>Este mes</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search me-1"></i> Buscar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Clientes -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list me-2"></i>
                        Lista de Clientes
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="customersTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Empresa</th>
                                    <th>Teléfono</th>
                                    <th>Email</th>
                                    <th>Ciudad</th>
                                    <th>Estado</th>
                                    <th>Fecha Registro</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($customers)): ?>
                                    <?php foreach ($customers as $customer): ?>
                                        <tr>
                                            <td><?php echo $customer['id']; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($customer['full_name']); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($customer['company'] ?? '-'); ?></td>
                                            <td>
                                                <a href="tel:<?php echo $customer['phone']; ?>">
                                                    <?php echo htmlspecialchars($customer['phone']); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <a href="mailto:<?php echo $customer['email']; ?>">
                                                    <?php echo htmlspecialchars($customer['email']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars($customer['city'] ?? '-'); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $customer['is_active'] ? 'success' : 'secondary'; ?>">
                                                    <?php echo $customer['is_active'] ? 'Activo' : 'Inactivo'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($customer['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="<?php echo BASE_URL; ?>clientes/view/<?php echo $customer['id']; ?>" 
                                                       class="btn btn-outline-primary" title="Ver">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="<?php echo BASE_URL; ?>clientes/edit/<?php echo $customer['id']; ?>" 
                                                       class="btn btn-outline-warning" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="<?php echo BASE_URL; ?>pedidos/create?customer_id=<?php echo $customer['id']; ?>" 
                                                       class="btn btn-outline-success" title="Nuevo Pedido">
                                                        <i class="fas fa-plus"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            <i class="fas fa-users fa-3x mb-3"></i>
                                            <p>No hay clientes registrados aún.</p>
                                            <a href="<?php echo BASE_URL; ?>clientes/create" class="btn btn-primary">
                                                <i class="fas fa-plus me-2"></i>
                                                Agregar Primer Cliente
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
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar DataTable si existe
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        $('#customersTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
            },
            "pageLength": 25,
            "order": [[ 0, "desc" ]], // Ordenar por ID descendente
            "columnDefs": [
                { "orderable": false, "targets": 8 } // Columna de acciones no ordenable
            ]
        });
    }

    // Auto-submit form on filter change
    document.querySelectorAll('#customerFilters select').forEach(function(select) {
        select.addEventListener('change', function() {
            document.getElementById('customerFilters').submit();
        });
    });
});
</script>

<?php
$content = ob_get_clean();
require_once dirname(__DIR__) . '/layouts/main.php';
?>