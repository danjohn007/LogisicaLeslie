<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title><?php echo $title ?? APP_NAME; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js"></script>
    
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
    <!-- Top Navigation Bar -->
    <?php if (isset($_SESSION['user_id'])): ?>
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
            <div class="container-fluid">
                <!-- Sidebar Toggle Button (Hamburger) -->
                <button class="navbar-toggler me-3" type="button" id="sidebarToggle" aria-label="Toggle sidebar">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                    <i class="fas fa-truck me-2"></i>
                    <span class="d-none d-md-inline">Leslie Logística</span>
                    <span class="d-md-none">Leslie</span>
                </a>
                
                <!-- Top Right User Menu -->
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                            <div class="avatar-sm me-2">
                                <span class="avatar-title bg-light text-primary rounded-circle">
                                    <?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
                                </span>
                            </div>
                            <span class="d-none d-lg-inline"><?php echo $_SESSION['username'] ?? 'Usuario'; ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">Configuración</h6></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>profile">
                                <i class="fas fa-user me-2"></i>Mi Perfil
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>change-password">
                                <i class="fas fa-key me-2"></i>Cambiar Contraseña
                            </a></li>
                            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>configuracion">
                                <i class="fas fa-cog me-2"></i>Configuración
                            </a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>logout">
                                <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Sidebar Overlay for Mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Sidebar -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-brand">
                    <i class="fas fa-truck"></i>
                    <span class="sidebar-text">Leslie Logística</span>
                </div>
                <button class="btn btn-link text-white d-lg-none" id="sidebarClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="sidebar-menu">
                <ul class="nav flex-column">
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>dashboard">
                            <i class="fas fa-tachometer-alt"></i>
                            <span class="sidebar-text">Dashboard</span>
                        </a>
                    </li>
                    
                    <!-- Producción -->
                    <li class="nav-item">
                        <a class="nav-link has-submenu" href="#" data-bs-toggle="collapse" data-bs-target="#produccionSubmenu">
                            <i class="fas fa-industry"></i>
                            <span class="sidebar-text">Producción</span>
                            <i class="fas fa-chevron-down submenu-arrow"></i>
                        </a>
                        <div class="collapse submenu" id="produccionSubmenu">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo BASE_URL; ?>produccion">
                                        <i class="fas fa-circle submenu-icon"></i>
                                        <span class="sidebar-text">Gestión de Producción</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo BASE_URL; ?>inventario">
                                        <i class="fas fa-circle submenu-icon"></i>
                                        <span class="sidebar-text">Inventario</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    
                    <!-- Pedidos -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>pedidos">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="sidebar-text">Pedidos</span>
                        </a>
                    </li>
                    
                    <!-- Ventas -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>ventas">
                            <i class="fas fa-cash-register"></i>
                            <span class="sidebar-text">Ventas</span>
                        </a>
                    </li>
                    
                    <!-- Rutas -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>rutas">
                            <i class="fas fa-route"></i>
                            <span class="sidebar-text">Rutas y Logística</span>
                        </a>
                    </li>
                    
                    <!-- Clientes -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>clientes">
                            <i class="fas fa-users"></i>
                            <span class="sidebar-text">Clientes</span>
                        </a>
                    </li>
                    
                    <!-- Reportes -->
                    <li class="nav-item">
                        <a class="nav-link has-submenu" href="#" data-bs-toggle="collapse" data-bs-target="#reportesSubmenu">
                            <i class="fas fa-chart-bar"></i>
                            <span class="sidebar-text">Reportes</span>
                            <i class="fas fa-chevron-down submenu-arrow"></i>
                        </a>
                        <div class="collapse submenu" id="reportesSubmenu">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo BASE_URL; ?>reportes">
                                        <i class="fas fa-circle submenu-icon"></i>
                                        <span class="sidebar-text">Reportes Generales</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo BASE_URL; ?>finanzas">
                                        <i class="fas fa-circle submenu-icon"></i>
                                        <span class="sidebar-text">Finanzas</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    
                    <!-- Administración (solo para admin) -->
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <li class="nav-item mt-3">
                        <div class="sidebar-divider"></div>
                        <a class="nav-link has-submenu" href="#" data-bs-toggle="collapse" data-bs-target="#adminSubmenu">
                            <i class="fas fa-cogs"></i>
                            <span class="sidebar-text">Administración</span>
                            <i class="fas fa-chevron-down submenu-arrow"></i>
                        </a>
                        <div class="collapse submenu" id="adminSubmenu">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo BASE_URL; ?>usuarios">
                                        <i class="fas fa-circle submenu-icon"></i>
                                        <span class="sidebar-text">Usuarios</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo BASE_URL; ?>configuracion">
                                        <i class="fas fa-circle submenu-icon"></i>
                                        <span class="sidebar-text">Configuración</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo BASE_URL; ?>sistema/test-connection">
                                        <i class="fas fa-circle submenu-icon"></i>
                                        <span class="sidebar-text">Test Conexión</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>

        <!-- Main Content Wrapper -->
        <div class="main-content" id="mainContent">
            <main class="container-fluid">
                <?php echo $content ?? ''; ?>
            </main>
        </div>
    <?php else: ?>
        <!-- Login Content -->
        <main class="container-fluid">
            <?php echo $content ?? ''; ?>
        </main>
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
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
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