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
    
    <!-- Sidebar Mobile Styles -->
    <style>
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: -300px;
            width: 300px;
            height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            z-index: 1050;
            transition: left 0.3s ease;
            overflow-y: auto;
        }
        
        .sidebar-overlay.show {
            left: 0;
        }
        
        .sidebar-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1040;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .sidebar-backdrop.show {
            opacity: 1;
            visibility: visible;
        }
        
        .sidebar-header {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-nav {
            padding: 1rem 0;
        }
        
        .sidebar-nav .nav-link {
            color: rgba(255, 255, 255, 0.9);
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            display: block;
            transition: all 0.3s ease;
        }
        
        .sidebar-nav .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .sidebar-nav .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        
        .sidebar-dropdown {
            position: relative;
        }
        
        .sidebar-dropdown .dropdown-menu {
            position: static;
            background: rgba(0, 0, 0, 0.2);
            border: none;
            box-shadow: none;
            margin: 0;
            padding: 0;
            display: none;
        }
        
        .sidebar-dropdown.show .dropdown-menu {
            display: block;
        }
        
        .sidebar-dropdown .dropdown-item {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.5rem 2.5rem;
        }
        
        .sidebar-dropdown .dropdown-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        @media (min-width: 992px) {
            .sidebar-overlay {
                position: static;
                left: 0;
                width: auto;
                height: auto;
                background: none;
                z-index: auto;
                transition: none;
                overflow-y: visible;
            }
            
            .sidebar-backdrop {
                display: none;
            }
            
            .mobile-toggle {
                display: none;
            }
        }
    </style>
    
    <meta name="description" content="Sistema de Logística para Quesos y Productos Leslie">
    <meta name="author" content="Sistema Logística Leslie">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo BASE_URL; ?>public/images/favicon.ico">
</head>
<body>
    <!-- Mobile Sidebar Backdrop -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
    
    <!-- Navigation -->
    <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Top navbar for desktop, mobile toggle for mobile -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container-fluid">
                <!-- Mobile Menu Toggle -->
                <button class="btn btn-outline-light mobile-toggle d-lg-none me-2" type="button" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                    <i class="fas fa-truck me-2"></i>
                    Leslie Logística
                </a>
                
                <!-- Desktop Navigation -->
                <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse d-lg-flex" id="navbarNav">
                    <ul class="navbar-nav me-auto d-none d-lg-flex">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>dashboard">
                                <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-industry me-1"></i>Producción
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>produccion">Gestión de Producción</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>inventario">Inventario</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>pedidos">
                                <i class="fas fa-shopping-cart me-1"></i>Pedidos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>rutas">
                                <i class="fas fa-route me-1"></i>Rutas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>ventas">
                                <i class="fas fa-cash-register me-1"></i>Ventas
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-chart-bar me-1"></i>Reportes
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>reportes">Reportes Generales</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>finanzas">Finanzas</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>clientes">
                                <i class="fas fa-users me-1"></i>Clientes
                            </a>
                        </li>
                    </ul>
                    
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>
                                <?php echo $_SESSION['username'] ?? 'Usuario'; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>profile">Mi Perfil</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>change-password">Cambiar Contraseña</a></li>
                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>settings">Configuración</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>logout">Cerrar Sesión</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Mobile Sidebar -->
        <div class="sidebar-overlay" id="sidebarOverlay">
            <div class="sidebar-header">
                <div class="d-flex justify-content-between align-items-center text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-truck me-2"></i>
                        Leslie Logística
                    </h5>
                    <button class="btn btn-sm btn-outline-light" id="sidebarClose">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <small class="text-light">
                    <i class="fas fa-user me-1"></i>
                    <?php echo $_SESSION['username'] ?? 'Usuario'; ?>
                </small>
            </div>
            
            <nav class="sidebar-nav">
                <a class="nav-link" href="<?php echo BASE_URL; ?>dashboard">
                    <i class="fas fa-tachometer-alt"></i>Dashboard
                </a>
                
                <div class="sidebar-dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="sidebar-dropdown">
                        <i class="fas fa-industry"></i>Producción
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="<?php echo BASE_URL; ?>produccion">Gestión de Producción</a>
                        <a class="dropdown-item" href="<?php echo BASE_URL; ?>inventario">Inventario</a>
                    </div>
                </div>
                
                <a class="nav-link" href="<?php echo BASE_URL; ?>pedidos">
                    <i class="fas fa-shopping-cart"></i>Pedidos
                </a>
                
                <a class="nav-link" href="<?php echo BASE_URL; ?>rutas">
                    <i class="fas fa-route"></i>Rutas
                </a>
                
                <a class="nav-link" href="<?php echo BASE_URL; ?>ventas">
                    <i class="fas fa-cash-register"></i>Ventas
                </a>
                
                <div class="sidebar-dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="sidebar-dropdown">
                        <i class="fas fa-chart-bar"></i>Reportes
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="<?php echo BASE_URL; ?>reportes">Reportes Generales</a>
                        <a class="dropdown-item" href="<?php echo BASE_URL; ?>finanzas">Finanzas</a>
                    </div>
                </div>
                
                <a class="nav-link" href="<?php echo BASE_URL; ?>clientes">
                    <i class="fas fa-users"></i>Clientes
                </a>
                
                <hr style="border-color: rgba(255,255,255,0.2);">
                
                <a class="nav-link" href="<?php echo BASE_URL; ?>profile">
                    <i class="fas fa-user-cog"></i>Mi Perfil
                </a>
                
                <a class="nav-link" href="<?php echo BASE_URL; ?>change-password">
                    <i class="fas fa-key"></i>Cambiar Contraseña
                </a>
                
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <a class="nav-link" href="<?php echo BASE_URL; ?>settings">
                    <i class="fas fa-cog"></i>Configuración
                </a>
                <?php endif; ?>
                
                <a class="nav-link" href="<?php echo BASE_URL; ?>logout">
                    <i class="fas fa-sign-out-alt"></i>Cerrar Sesión
                </a>
            </nav>
        </div>
    <?php endif; ?>

    <!-- Contenido Principal -->
    <main class="container-fluid py-4">
        <?php echo $content ?? ''; ?>
    </main>

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
    
    <!-- Sidebar Mobile JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarClose = document.getElementById('sidebarClose');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const sidebarBackdrop = document.getElementById('sidebarBackdrop');
            
            // Open sidebar
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebarOverlay.classList.add('show');
                    sidebarBackdrop.classList.add('show');
                    document.body.style.overflow = 'hidden';
                });
            }
            
            // Close sidebar
            function closeSidebar() {
                sidebarOverlay.classList.remove('show');
                sidebarBackdrop.classList.remove('show');
                document.body.style.overflow = '';
            }
            
            if (sidebarClose) {
                sidebarClose.addEventListener('click', closeSidebar);
            }
            
            if (sidebarBackdrop) {
                sidebarBackdrop.addEventListener('click', closeSidebar);
            }
            
            // Handle sidebar dropdowns
            const sidebarDropdowns = document.querySelectorAll('[data-bs-toggle="sidebar-dropdown"]');
            sidebarDropdowns.forEach(function(dropdown) {
                dropdown.addEventListener('click', function(e) {
                    e.preventDefault();
                    const parent = this.closest('.sidebar-dropdown');
                    const isOpen = parent.classList.contains('show');
                    
                    // Close all other dropdowns
                    document.querySelectorAll('.sidebar-dropdown.show').forEach(function(openDropdown) {
                        if (openDropdown !== parent) {
                            openDropdown.classList.remove('show');
                        }
                    });
                    
                    // Toggle current dropdown
                    parent.classList.toggle('show');
                });
            });
            
            // Close sidebar when clicking on nav links (except dropdowns)
            const sidebarLinks = document.querySelectorAll('.sidebar-nav .nav-link:not(.dropdown-toggle)');
            sidebarLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 992) {
                        closeSidebar();
                    }
                });
            });
            
            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 992) {
                    closeSidebar();
                }
            });
        });
    </script>
</body>
</html>