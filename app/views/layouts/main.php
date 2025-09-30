<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? APP_NAME; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- FullCalendar CSS (para futuras implementaciones) -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
    
    <!-- Estilos personalizados -->
    <link href="<?php echo BASE_URL; ?>public/css/styles.css" rel="stylesheet">
    
    <meta name="description" content="Sistema de Logística para Quesos y Productos Leslie">
    <meta name="author" content="Sistema Logística Leslie">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo BASE_URL; ?>public/images/favicon.ico">
</head>
<body>
    <!-- Navigation -->
    <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Top Header Bar -->
        <nav class="navbar navbar-dark bg-primary shadow-sm">
            <div class="container-fluid">
                <!-- Mobile menu toggle -->
                <button class="navbar-toggler d-lg-none" type="button" id="sidebarToggle">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <!-- Brand -->
                <a class="navbar-brand mx-auto mx-lg-0" href="<?php echo BASE_URL; ?>">
                    <i class="fas fa-truck me-2"></i>
                    Leslie Logística
                </a>
                
                <!-- User menu -->
                <div class="dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i>
                        <span class="d-none d-md-inline"><?php echo $_SESSION['username'] ?? 'Usuario'; ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>profile">Mi Perfil</a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>change-password">Cambiar Contraseña</a></li>
                        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>configuracion">Configuración</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>logout">Cerrar Sesión</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Sidebar Overlay (Mobile) -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h5 class="text-white mb-0">
                    <i class="fas fa-truck me-2"></i>
                    Menú Principal
                </h5>
                <button class="btn btn-link text-white d-lg-none" id="sidebarClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="sidebar-content">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>dashboard">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#productionSubmenu">
                            <i class="fas fa-industry me-2"></i>Producción
                        </a>
                        <div class="collapse" id="productionSubmenu">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo BASE_URL; ?>produccion">
                                        <i class="fas fa-cogs me-2"></i>Gestión de Producción
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo BASE_URL; ?>inventario">
                                        <i class="fas fa-boxes me-2"></i>Inventario
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>pedidos">
                            <i class="fas fa-shopping-cart me-2"></i>Pedidos
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>rutas">
                            <i class="fas fa-route me-2"></i>Rutas
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>ventas">
                            <i class="fas fa-cash-register me-2"></i>Ventas
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#reportsSubmenu">
                            <i class="fas fa-chart-bar me-2"></i>Reportes
                        </a>
                        <div class="collapse" id="reportsSubmenu">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo BASE_URL; ?>reportes">
                                        <i class="fas fa-chart-line me-2"></i>Reportes Generales
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo BASE_URL; ?>finanzas">
                                        <i class="fas fa-dollar-sign me-2"></i>Finanzas
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>clientes">
                            <i class="fas fa-users me-2"></i>Clientes
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main Content Wrapper -->
        <div class="content-wrapper" id="contentWrapper">
    <?php endif; ?>

    <!-- Contenido Principal -->
    <main class="main-content py-4">
        <?php echo $content ?? ''; ?>
    </main>

    <?php if (isset($_SESSION['user_id'])): ?>
        </div> <!-- Close content-wrapper -->
    <?php endif; ?>

    <!-- Footer -->
    <footer class="bg-light py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">
                © <?php echo date('Y'); ?> <?php echo APP_NAME; ?> - 
                Versión <?php echo APP_VERSION; ?>
            </p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>
    
    <!-- Scripts personalizados -->
    <script src="<?php echo BASE_URL; ?>public/js/app.js"></script>
    
    <!-- Scripts adicionales por página -->
    <?php if (isset($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?php echo BASE_URL . 'public/js/' . $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>