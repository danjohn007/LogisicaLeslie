<?php
ob_start();
?>

<div class="hero-section bg-primary text-white py-5 mb-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-4 fw-bold mb-3">
                    <i class="fas fa-truck me-3"></i>
                    Sistema de Logística Leslie
                </h1>
                <p class="lead mb-4">
                    Gestión integral para Quesos y Productos Leslie. 
                    Control total de producción, inventario, pedidos y entregas.
                </p>
                <a href="<?php echo BASE_URL; ?>login" class="btn btn-light btn-lg">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Iniciar Sesión
                </a>
                <a href="<?php echo BASE_URL; ?>test-connection" class="btn btn-outline-light btn-lg ms-3">
                    <i class="fas fa-database me-2"></i>
                    Test de Conexión
                </a>
            </div>
            <div class="col-md-4 text-center">
                <i class="fas fa-industry display-1 opacity-75"></i>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12 mb-5">
            <h2 class="text-center mb-5">Módulos del Sistema</h2>
            
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-industry fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">Producción e Inventario</h5>
                            <p class="card-text">Control de producción en 3 modalidades y gestión de inventario con trazabilidad completa.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-shopping-cart fa-3x text-success mb-3"></i>
                            <h5 class="card-title">Pedidos (Preventas)</h5>
                            <p class="card-text">Captura multicanal de pedidos con validación QR y seguimiento en tiempo real.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-route fa-3x text-warning mb-3"></i>
                            <h5 class="card-title">Rutas y Logística</h5>
                            <p class="card-text">Optimización de rutas, gestión de recursos y monitoreo en tiempo real.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-cash-register fa-3x text-info mb-3"></i>
                            <h5 class="card-title">Ventas y Finanzas</h5>
                            <p class="card-text">Ventas en punto de entrega y control financiero con múltiples métodos de pago.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-undo fa-3x text-danger mb-3"></i>
                            <h5 class="card-title">Control de Retornos</h5>
                            <p class="card-text">Registro de devoluciones con trazabilidad y evaluación de calidad.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-smile fa-3x text-purple mb-3"></i>
                            <h5 class="card-title">Experiencia del Cliente</h5>
                            <p class="card-text">Encuestas de satisfacción multicanal y análisis de feedback.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-chart-bar fa-3x text-secondary mb-3"></i>
                            <h5 class="card-title">Analítica y Reportes</h5>
                            <p class="card-text">Dashboards interactivos con gráficas y reportes especializados.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-users fa-3x text-dark mb-3"></i>
                            <h5 class="card-title">Gestión de Clientes</h5>
                            <p class="card-text">Base de datos centralizada con histórico integral de pedidos.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-5">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Información del Sistema
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Versión:</strong> <?php echo $version; ?></p>
                            <p><strong>URL Base:</strong> <?php echo $base_url; ?></p>
                            <p><strong>Tecnologías:</strong> PHP 7+, MySQL 5.7, Bootstrap 5</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Arquitectura:</strong> MVC (Model-View-Controller)</p>
                            <p><strong>Frontend:</strong> Bootstrap 5, Chart.js, FullCalendar</p>
                            <p><strong>Fecha de Instalación:</strong> <?php echo date('d/m/Y'); ?></p>
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