/**
 * JavaScript Principal
 * Sistema de Logística - Quesos y Productos Leslie
 */

// Configuración global
const App = {
    baseUrl: document.querySelector('base')?.href || window.location.origin + '/',
    
    // Inicializar la aplicación
    init() {
        this.setupEventListeners();
        this.initializeComponents();
        this.setupAjaxDefaults();
        this.initializeSidebar();
    },
    
    // Inicializar sidebar
    initializeSidebar() {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const mainContent = document.getElementById('mainContent');
        
        if (!sidebar || !sidebarToggle || !mainContent) return;
        
        // Cargar estado del sidebar desde localStorage
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('sidebar-collapsed');
        }
        
        // Toggle sidebar
        sidebarToggle.addEventListener('click', () => {
            const isCurrentlyCollapsed = sidebar.classList.contains('collapsed');
            
            if (isCurrentlyCollapsed) {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('sidebar-collapsed');
                localStorage.setItem('sidebarCollapsed', 'false');
            } else {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('sidebar-collapsed');
                localStorage.setItem('sidebarCollapsed', 'true');
            }
        });
        
        // Manejo para móviles
        if (window.innerWidth <= 768) {
            this.setupMobileSidebar();
        }
        
        // Manejar redimensionamiento de ventana
        window.addEventListener('resize', () => {
            if (window.innerWidth <= 768) {
                this.setupMobileSidebar();
            } else {
                this.removeMobileSidebar();
            }
        });
        
        // Agregar tooltips para sidebar colapsado
        this.setupSidebarTooltips();
        
        // Marcar enlace activo
        this.setActiveNavLink();
    },
    
    // Configurar sidebar para móviles
    setupMobileSidebar() {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        
        if (!sidebar || !sidebarToggle) return;
        
        // Crear overlay si no existe
        let overlay = document.querySelector('.sidebar-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay';
            document.body.appendChild(overlay);
        }
        
        // Remover clases de escritorio
        sidebar.classList.remove('collapsed');
        
        // Manejar click en toggle para móvil
        const mobileToggleHandler = (e) => {
            e.stopPropagation();
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        };
        
        // Remover listeners anteriores y agregar nuevo
        sidebarToggle.removeEventListener('click', this.desktopToggleHandler);
        sidebarToggle.addEventListener('click', mobileToggleHandler);
        this.mobileToggleHandler = mobileToggleHandler;
        
        // Cerrar sidebar al hacer click en overlay
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });
        
        // Cerrar sidebar al hacer click en enlaces (móvil)
        sidebar.querySelectorAll('.nav-link:not(.has-submenu)').forEach(link => {
            link.addEventListener('click', () => {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });
        });
    },
    
    // Remover configuración móvil
    removeMobileSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        
        if (sidebar) {
            sidebar.classList.remove('show');
        }
        
        if (overlay) {
            overlay.classList.remove('show');
        }
    },
    
    // Configurar tooltips para sidebar colapsado
    setupSidebarTooltips() {
        const sidebar = document.getElementById('sidebar');
        if (!sidebar) return;
        
        sidebar.querySelectorAll('.nav-link').forEach(link => {
            const textSpan = link.querySelector('.sidebar-text');
            if (textSpan) {
                link.setAttribute('data-tooltip', textSpan.textContent.trim());
            }
        });
    },
    
    // Marcar enlace activo en sidebar
    setActiveNavLink() {
        const currentPath = window.location.pathname;
        const sidebar = document.getElementById('sidebar');
        
        if (!sidebar) return;
        
        sidebar.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
            
            const href = link.getAttribute('href');
            if (href && currentPath.includes(href.replace(this.baseUrl, ''))) {
                link.classList.add('active');
                
                // Si es un submenu, abrir el padre
                const parentSubmenu = link.closest('.submenu');
                if (parentSubmenu) {
                    const parentToggle = sidebar.querySelector(`[data-bs-target="#${parentSubmenu.id}"]`);
                    if (parentToggle) {
                        parentToggle.setAttribute('aria-expanded', 'true');
                        parentSubmenu.classList.add('show');
                    }
                }
            }
        });
    },
    
    // Configurar event listeners globales
    setupEventListeners() {
        // Confirmar eliminaciones
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-delete') || 
                e.target.closest('.btn-delete')) {
                e.preventDefault();
                const message = e.target.dataset.message || '¿Está seguro de que desea eliminar este elemento?';
                if (confirm(message)) {
                    if (e.target.href) {
                        window.location.href = e.target.href;
                    } else {
                        e.target.closest('form')?.submit();
                    }
                }
            }
        });
        
        // Auto-cerrar alerts después de 5 segundos
        document.querySelectorAll('.alert:not(.alert-permanent)').forEach(alert => {
            setTimeout(() => {
                if (alert && alert.parentNode) {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }
            }, 5000);
        });
        
        // Validación de formularios
        document.addEventListener('submit', this.handleFormSubmit);
        
        // Loading states para botones
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-loading')) {
                App.setLoadingState(e.target, true);
            }
        });

        // Sidebar toggle functionality
        this.setupSidebar();
    },

    // Configurar funcionalidad del sidebar
    setupSidebar() {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarClose = document.getElementById('sidebarClose');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        // Función para mostrar sidebar
        const showSidebar = () => {
            sidebar?.classList.add('active');
            sidebarOverlay?.classList.add('active');
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        };

        // Función para ocultar sidebar
        const hideSidebar = () => {
            sidebar?.classList.remove('active');
            sidebarOverlay?.classList.remove('active');
            document.body.style.overflow = ''; // Restore scrolling
        };

        // Event listeners
        sidebarToggle?.addEventListener('click', showSidebar);
        sidebarClose?.addEventListener('click', hideSidebar);
        sidebarOverlay?.addEventListener('click', hideSidebar);

        // Cerrar sidebar al presionar Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar?.classList.contains('active')) {
                hideSidebar();
            }
        });

        // Cerrar sidebar en dispositivos móviles al hacer clic en un enlace
        const sidebarLinks = document.querySelectorAll('.sidebar .nav-link:not(.dropdown-toggle)');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 992) {
                    hideSidebar();
                }
            });
        });

        // Auto-cerrar sidebar al cambiar el tamaño de ventana
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 992) {
                hideSidebar();
                document.body.style.overflow = '';
            }
        });
    },
    
    // Inicializar componentes
    initializeComponents() {
        // Inicializar tooltips de Bootstrap
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipTriggerList.forEach(tooltipTriggerEl => {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Inicializar popovers de Bootstrap
        const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
        popoverTriggerList.forEach(popoverTriggerEl => {
            new bootstrap.Popover(popoverTriggerEl);
        });
        
        // Auto-focus en modales
        document.addEventListener('shown.bs.modal', function(e) {
            const firstInput = e.target.querySelector('input, select, textarea');
            if (firstInput) firstInput.focus();
        });
    },
    
    // Configurar AJAX defaults
    setupAjaxDefaults() {
        // Si se usa fetch, configurar headers por defecto
        window.fetch = new Proxy(window.fetch, {
            apply(target, thisArg, argumentsList) {
                const [resource, config = {}] = argumentsList;
                
                config.headers = {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...config.headers
                };
                
                return target.apply(thisArg, [resource, config]);
            }
        });
    },
    
    // Manejar envío de formularios
    handleFormSubmit(e) {
        const form = e.target;
        
        // Validar campos requeridos
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            App.showAlert('Por favor, complete todos los campos requeridos.', 'warning');
        }
    },
    
    // Mostrar alertas
    showAlert(message, type = 'info', duration = 5000) {
        const alertContainer = document.getElementById('alert-container') || 
                              this.createAlertContainer();
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        alertContainer.appendChild(alertDiv);
        
        if (duration > 0) {
            setTimeout(() => {
                if (alertDiv && alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, duration);
        }
    },
    
    // Crear contenedor de alertas si no existe
    createAlertContainer() {
        const container = document.createElement('div');
        container.id = 'alert-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            min-width: 300px;
        `;
        document.body.appendChild(container);
        return container;
    },
    
    // Establecer estado de carga en botones
    setLoadingState(button, loading) {
        if (loading) {
            button.disabled = true;
            button.dataset.originalText = button.innerHTML;
            button.innerHTML = '<span class="loading-spinner me-2"></span>Cargando...';
        } else {
            button.disabled = false;
            button.innerHTML = button.dataset.originalText || button.innerHTML;
        }
    },
    
    // Realizar petición AJAX
    async request(url, options = {}) {
        try {
            const response = await fetch(this.baseUrl + url, {
                method: 'GET',
                ...options
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('Request error:', error);
            this.showAlert('Error en la comunicación con el servidor', 'danger');
            throw error;
        }
    },
    
    // Formatear fecha
    formatDate(date, format = 'DD/MM/YYYY') {
        const d = new Date(date);
        const day = d.getDate().toString().padStart(2, '0');
        const month = (d.getMonth() + 1).toString().padStart(2, '0');
        const year = d.getFullYear();
        
        return format
            .replace('DD', day)
            .replace('MM', month)
            .replace('YYYY', year);
    },
    
    // Formatear moneda
    formatCurrency(amount, currency = 'MXN') {
        return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: currency
        }).format(amount);
    },
    
    // Debounce function
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};

// Utilidades para Charts
const ChartUtils = {
    // Colores predefinidos
    colors: {
        primary: '#0d6efd',
        secondary: '#6c757d',
        success: '#198754',
        danger: '#dc3545',
        warning: '#ffc107',
        info: '#0dcaf0',
        light: '#f8f9fa',
        dark: '#212529'
    },
    
    // Crear gráfica básica
    createChart(canvasId, type, data, options = {}) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return null;
        
        const defaultOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        };
        
        return new Chart(ctx, {
            type: type,
            data: data,
            options: { ...defaultOptions, ...options }
        });
    },
    
    // Generar datos de ejemplo
    generateSampleData(labels, datasets = 1) {
        const data = [];
        for (let i = 0; i < datasets; i++) {
            data.push({
                label: `Dataset ${i + 1}`,
                data: labels.map(() => Math.floor(Math.random() * 100)),
                backgroundColor: Object.values(this.colors)[i % Object.keys(this.colors).length]
            });
        }
        return { labels, datasets: data };
    }
};

// Inicializar la aplicación cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    App.init();
    
    // Aplicar animaciones fade-in a elementos con la clase
    document.querySelectorAll('.fade-in').forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        setTimeout(() => {
            element.style.transition = 'opacity 0.5s ease-in-out, transform 0.5s ease-in-out';
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, 100);
    });
});