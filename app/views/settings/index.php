<?php
ob_start();
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Configuración del Sistema</h1>
                    <p class="text-muted mb-0">Administración y configuración general</p>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($success) && $success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error) && $error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Sistema Information Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Estado del Sistema
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <i class="fas fa-database fa-2x text-success mb-2"></i>
                                <h6>Base de Datos</h6>
                                <span class="badge bg-success">MySQL Configurado</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                <h6>Usuarios</h6>
                                <span class="badge bg-primary"><?php echo count($users ?? []); ?> Registrados</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <i class="fas fa-cogs fa-2x text-warning mb-2"></i>
                                <h6>Módulos</h6>
                                <span class="badge bg-warning">8 Activos</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <i class="fas fa-shield-alt fa-2x text-info mb-2"></i>
                                <h6>Seguridad</h6>
                                <span class="badge bg-info">Configurado</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Módulos del Sistema -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-th-large me-2"></i>
                        Módulos Implementados
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <i class="fas fa-industry fa-2x text-success mb-2"></i>
                                    <h6>Producción</h6>
                                    <span class="badge bg-success">Implementado</span>
                                    <p class="small mt-2">Gestión de lotes y producción</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <i class="fas fa-boxes fa-2x text-success mb-2"></i>
                                    <h6>Inventario</h6>
                                    <span class="badge bg-success">Implementado</span>
                                    <p class="small mt-2">Control de stock y movimientos</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <i class="fas fa-shopping-cart fa-2x text-success mb-2"></i>
                                    <h6>Pedidos</h6>
                                    <span class="badge bg-success">Implementado</span>
                                    <p class="small mt-2">Gestión de pedidos y preventas</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <i class="fas fa-route fa-2x text-success mb-2"></i>
                                    <h6>Rutas</h6>
                                    <span class="badge bg-success">Implementado</span>
                                    <p class="small mt-2">Planificación de entregas</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <i class="fas fa-cash-register fa-2x text-success mb-2"></i>
                                    <h6>Ventas</h6>
                                    <span class="badge bg-success">Implementado</span>
                                    <p class="small mt-2">Ventas directas y POS</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <i class="fas fa-users fa-2x text-success mb-2"></i>
                                    <h6>Clientes</h6>
                                    <span class="badge bg-success">Implementado</span>
                                    <p class="small mt-2">Gestión de clientes</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <i class="fas fa-chart-bar fa-2x text-success mb-2"></i>
                                    <h6>Reportes</h6>
                                    <span class="badge bg-success">Implementado</span>
                                    <p class="small mt-2">Análisis y reportes</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <i class="fas fa-cogs fa-2x text-success mb-2"></i>
                                    <h6>Configuración</h6>
                                    <span class="badge bg-success">Implementado</span>
                                    <p class="small mt-2">Configuración del sistema</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Información de Base de Datos -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-database me-2"></i>
                        Configuración de Base de Datos
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Configuración Actual:</h6>
                            <ul class="list-unstyled">
                                <li><strong>Host:</strong> <?php echo DB_HOST; ?></li>
                                <li><strong>Base de Datos:</strong> <?php echo DB_NAME; ?></li>
                                <li><strong>Usuario:</strong> <?php echo DB_USER; ?></li>
                                <li><strong>Charset:</strong> <?php echo DB_CHARSET; ?></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Estado:</h6>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Sistema configurado para conexión MySQL únicamente.
                                SQLite completamente deshabilitado.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once dirname(__DIR__) . '/layouts/main.php';
?>