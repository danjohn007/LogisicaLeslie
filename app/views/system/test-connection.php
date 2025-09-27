<?php
ob_start();
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-database me-2"></i>
                        <?php echo $title; ?>
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Estado de Conexión -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5>Estado de la Conexión</h5>
                            <?php if ($connection_result['status'] === 'success'): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <?php echo $connection_result['message']; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-times-circle me-2"></i>
                                    <?php echo $connection_result['message']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Información del Sistema -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Información del Sistema</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <td><strong>Aplicación:</strong></td>
                                    <td><?php echo $app_name; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Versión:</strong></td>
                                    <td><?php echo $app_version; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>URL Base:</strong></td>
                                    <td><?php echo $base_url; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>PHP Version:</strong></td>
                                    <td><?php echo $php_version; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Servidor:</strong></td>
                                    <td><?php echo $server_info; ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Configuración de Base de Datos</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <td><strong>Host:</strong></td>
                                    <td><?php echo $database_config['host']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Base de Datos:</strong></td>
                                    <td><?php echo $database_config['database']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Usuario:</strong></td>
                                    <td><?php echo $database_config['user']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Charset:</strong></td>
                                    <td><?php echo $database_config['charset']; ?></td>
                                </tr>
                                <?php if ($connection_result['status'] === 'success'): ?>
                                <tr>
                                    <td><strong>Versión del Servidor:</strong></td>
                                    <td><?php echo $connection_result['server_version'] ?? 'N/A'; ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>

                    <!-- Extensiones PHP Requeridas -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5>Extensiones PHP</h5>
                            <div class="row">
                                <?php 
                                $required_extensions = ['pdo', 'pdo_mysql', 'session', 'json', 'mbstring'];
                                foreach ($required_extensions as $ext): ?>
                                    <div class="col-md-3 mb-2">
                                        <?php if (extension_loaded($ext)): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i><?php echo $ext; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times me-1"></i><?php echo $ext; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="d-flex gap-2">
                                <a href="<?php echo BASE_URL; ?>" class="btn btn-primary">
                                    <i class="fas fa-home me-1"></i>
                                    Volver al Inicio
                                </a>
                                <button onclick="location.reload()" class="btn btn-secondary">
                                    <i class="fas fa-sync-alt me-1"></i>
                                    Actualizar Test
                                </button>
                                <?php if (APP_ENVIRONMENT === 'development'): ?>
                                <a href="<?php echo BASE_URL; ?>system/phpinfo" class="btn btn-info" target="_blank">
                                    <i class="fas fa-info-circle me-1"></i>
                                    PHP Info
                                </a>
                                <?php endif; ?>
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