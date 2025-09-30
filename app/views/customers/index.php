<?php
ob_start();
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-users me-2 text-primary"></i>
                        Gestión de Clientes
                    </h1>
                    <p class="text-muted mb-0">Administración y control de clientes</p>
                </div>
                <div>
                    <a href="<?php echo BASE_URL; ?>customers/create" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        Nuevo Cliente
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas de Clientes -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Clientes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($customer_stats['total'] ?? 0); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Clientes Activos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($customer_stats['active'] ?? 0); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Nuevos Este Mes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($customer_stats['new_month'] ?? 0); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Deuda Pendiente
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?php echo number_format($customer_stats['pending_debt'] ?? 0, 2); ?>
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

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-filter me-2"></i>
                        Filtros de Búsqueda
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Buscar Cliente</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="search" 
                                   name="search" 
                                   placeholder="Nombre o negocio..."
                                   value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Estado</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Todos</option>
                                <option value="1" <?php echo (($_GET['status'] ?? '') === '1') ? 'selected' : ''; ?>>Activo</option>
                                <option value="0" <?php echo (($_GET['status'] ?? '') === '0') ? 'selected' : ''; ?>>Inactivo</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="city" class="form-label">Ciudad</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="city" 
                                   name="city" 
                                   placeholder="Ciudad..."
                                   value="<?php echo htmlspecialchars($_GET['city'] ?? ''); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="credit_limit" class="form-label">Límite de Crédito</label>
                            <select class="form-select" id="credit_limit" name="credit_limit">
                                <option value="">Todos</option>
                                <option value="0" <?php echo (($_GET['credit_limit'] ?? '') === '0') ? 'selected' : ''; ?>>Sin Crédito</option>
                                <option value="1" <?php echo (($_GET['credit_limit'] ?? '') === '1') ? 'selected' : ''; ?>>Con Crédito</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i>
                                    Filtrar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Clientes -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
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
                                    <th>Negocio</th>
                                    <th>Contacto</th>
                                    <th>Teléfono</th>
                                    <th>Ciudad</th>
                                    <th>Límite Crédito</th>
                                    <th>Estado</th>
                                    <th>Registrado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($customers)): ?>
                                    <?php foreach ($customers as $customer): ?>
                                        <tr>
                                            <td><?= $customer['id'] ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($customer['business_name']) ?></strong>
                                                <?php if (!empty($customer['tax_id'])): ?>
                                                    <br><small class="text-muted">RFC: <?= htmlspecialchars($customer['tax_id']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($customer['contact_name']) ?>
                                                <?php if (!empty($customer['email'])): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($customer['email']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($customer['phone']) ?></td>
                                            <td><?= htmlspecialchars($customer['city']) ?></td>
                                            <td>
                                                <?php if ($customer['credit_limit'] > 0): ?>
                                                    <span class="text-success">$<?= number_format($customer['credit_limit'], 2) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">Sin crédito</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($customer['is_active']): ?>
                                                    <span class="badge bg-success">Activo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Inactivo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($customer['created_at'])) ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?= BASE_URL ?>customers/view/<?= $customer['id'] ?>" 
                                                       class="btn btn-info btn-sm" 
                                                       title="Ver Cliente">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="<?= BASE_URL ?>customers/edit/<?= $customer['id'] ?>" 
                                                       class="btn btn-warning btn-sm" 
                                                       title="Editar Cliente">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="<?= BASE_URL ?>orders/create?customer_id=<?= $customer['id'] ?>" 
                                                       class="btn btn-success btn-sm" 
                                                       title="Nuevo Pedido">
                                                        <i class="fas fa-plus"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                            No hay clientes registrados
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

<!-- Scripts específicos para esta página -->
<script>
$(document).ready(function() {
    $('#customersTable').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        },
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: false, targets: [8] }
        ]
    });
});
</script>

<?php
$content = ob_get_clean();
require_once dirname(__DIR__) . '/layouts/main.php';
?>