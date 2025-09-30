<?php
// Verificar sesión y permisos
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-users"></i> Gestión de Clientes</h2>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newCustomerModal">
                    <i class="fas fa-plus"></i> Nuevo Cliente
                </button>
            </div>
        </div>
    </div>

    <!-- Estadísticas de Clientes -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?php echo $customer_stats['active_customers'] ?? 0; ?></h4>
                            <small>Clientes Activos</small>
                        </div>
                        <div>
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?php echo $customer_stats['credit_customers'] ?? 0; ?></h4>
                            <small>Con Crédito</small>
                        </div>
                        <div>
                            <i class="fas fa-credit-card fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?php echo $customer_stats['new_this_month'] ?? 0; ?></h4>
                            <small>Nuevos Este Mes</small>
                        </div>
                        <div>
                            <i class="fas fa-user-plus fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>$<?php echo number_format($customer_stats['avg_order_value'] ?? 0, 2); ?></h4>
                            <small>Valor Promedio</small>
                        </div>
                        <div>
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form id="customerFilters" class="row g-3">
                        <div class="col-md-3">
                            <label for="searchTerm" class="form-label">Buscar Cliente</label>
                            <input type="text" class="form-control" id="searchTerm" placeholder="Nombre o código...">
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Estado</label>
                            <select class="form-select" id="status">
                                <option value="">Todos</option>
                                <option value="1">Activos</option>
                                <option value="0">Inactivos</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="creditFilter" class="form-label">Crédito</label>
                            <select class="form-select" id="creditFilter">
                                <option value="">Todos</option>
                                <option value="with_credit">Con Crédito</option>
                                <option value="no_credit">Sin Crédito</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="city" class="form-label">Ciudad</label>
                            <select class="form-select" id="city">
                                <option value="">Todas</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid gap-2 d-md-flex">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Limpiar
                                </button>
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
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list"></i> Lista de Clientes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="customersTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>Código</th>
                                    <th>Negocio</th>
                                    <th>Contacto</th>
                                    <th>Teléfono</th>
                                    <th>Ciudad</th>
                                    <th>Crédito</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($customers)): ?>
                                    <?php foreach ($customers as $customer): ?>
                                    <tr>
                                        <td>
                                            <span class="font-monospace"><?php echo htmlspecialchars($customer['code']); ?></span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($customer['business_name']); ?></strong>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($customer['contact_name'] ?? 'N/A'); ?>
                                        </td>
                                        <td>
                                            <a href="tel:<?php echo $customer['phone']; ?>">
                                                <?php echo htmlspecialchars($customer['phone'] ?? 'N/A'); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($customer['city'] ?? 'N/A'); ?>
                                        </td>
                                        <td>
                                            <?php if ($customer['credit_limit'] > 0): ?>
                                                <span class="badge bg-success">
                                                    $<?php echo number_format($customer['credit_limit'], 2); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Sin crédito</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($customer['is_active']): ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" onclick="viewCustomer(<?php echo $customer['id']; ?>)" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-outline-warning" onclick="editCustomer(<?php echo $customer['id']; ?>)" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-outline-info" onclick="customerOrders(<?php echo $customer['id']; ?>)" title="Pedidos">
                                                    <i class="fas fa-shopping-cart"></i>
                                                </button>
                                                <?php if ($customer['is_active']): ?>
                                                <button class="btn btn-outline-danger" onclick="toggleCustomerStatus(<?php echo $customer['id']; ?>, 0)" title="Desactivar">
                                                    <i class="fas fa-user-times"></i>
                                                </button>
                                                <?php else: ?>
                                                <button class="btn btn-outline-success" onclick="toggleCustomerStatus(<?php echo $customer['id']; ?>, 1)" title="Activar">
                                                    <i class="fas fa-user-check"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No hay clientes registrados</p>
                                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newCustomerModal">
                                                <i class="fas fa-plus"></i> Agregar Primer Cliente
                                            </button>
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

<!-- Modal Nuevo Cliente -->
<div class="modal fade" id="newCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> Nuevo Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="newCustomerForm">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="customerCode" class="form-label">Código *</label>
                            <input type="text" class="form-control" id="customerCode" name="code" required>
                        </div>
                        <div class="col-md-6">
                            <label for="businessName" class="form-label">Nombre del Negocio *</label>
                            <input type="text" class="form-control" id="businessName" name="business_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="contactName" class="form-label">Nombre de Contacto</label>
                            <input type="text" class="form-control" id="contactName" name="contact_name">
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                        </div>
                        <div class="col-md-12">
                            <label for="address" class="form-label">Dirección</label>
                            <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="customerCity" class="form-label">Ciudad</label>
                            <input type="text" class="form-control" id="customerCity" name="city">
                        </div>
                        <div class="col-md-6">
                            <label for="creditLimit" class="form-label">Límite de Crédito</label>
                            <input type="number" class="form-control" id="creditLimit" name="credit_limit" step="0.01" value="0">
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="col-md-6">
                            <label for="paymentTerms" class="form-label">Términos de Pago</label>
                            <select class="form-select" id="paymentTerms" name="payment_terms">
                                <option value="immediate">Inmediato</option>
                                <option value="15_days">15 días</option>
                                <option value="30_days">30 días</option>
                                <option value="60_days">60 días</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Guardar Cliente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Funciones JavaScript para gestión de clientes
function viewCustomer(customerId) {
    window.location.href = `/customers/view/${customerId}`;
}

function editCustomer(customerId) {
    window.location.href = `/customers/edit/${customerId}`;
}

function customerOrders(customerId) {
    window.location.href = `/orders?customer_id=${customerId}`;
}

function toggleCustomerStatus(customerId, status) {
    if (confirm(`¿Está seguro de ${status ? 'activar' : 'desactivar'} este cliente?`)) {
        fetch(`/customers/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                customer_id: customerId,
                status: status
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error al actualizar el estado del cliente');
            }
        });
    }
}

// Formulario de nuevo cliente
document.getElementById('newCustomerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/customers/create', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error al crear el cliente');
        }
    })
    .catch(error => {
        alert('Error al procesar la solicitud');
    });
});

// Filtros de búsqueda
document.getElementById('customerFilters').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const searchTerm = document.getElementById('searchTerm').value;
    const status = document.getElementById('status').value;
    const creditFilter = document.getElementById('creditFilter').value;
    const city = document.getElementById('city').value;
    
    // Aquí implementarías la lógica de filtrado
    // Por ahora, simplemente recarga la página con los parámetros
    const params = new URLSearchParams();
    if (searchTerm) params.append('search', searchTerm);
    if (status) params.append('status', status);
    if (creditFilter) params.append('credit', creditFilter);
    if (city) params.append('city', city);
    
    window.location.href = `/customers?${params.toString()}`;
});

// Limpiar filtros
document.querySelector('button[type="reset"]').addEventListener('click', function() {
    window.location.href = '/customers';
});
</script>