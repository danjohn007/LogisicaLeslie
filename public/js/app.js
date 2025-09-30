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
    
    // Inicializar sidebar responsive
    initializeSidebar() {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarClose = document.getElementById('sidebarClose');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const mainContent = document.getElementById('mainContent');
        
        console.log('Initializing sidebar...');
        console.log('Sidebar found:', !!sidebar);
        console.log('Toggle found:', !!sidebarToggle);
        console.log('Overlay found:', !!sidebarOverlay);
        console.log('Window width:', window.innerWidth);
        
        if (!sidebar || !sidebarToggle) {
            console.error('Required sidebar elements not found');
            return;
        }
        
        // Cargar estado del sidebar desde localStorage (solo desktop)
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (isCollapsed && window.innerWidth >= 992) {
            sidebar.classList.add('collapsed');
            if (mainContent) mainContent.classList.add('sidebar-collapsed');
        }
        
        // Toggle sidebar function
        const toggleSidebar = (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('Toggle sidebar clicked, window width:', window.innerWidth);
            
            if (window.innerWidth < 992) {
                // Modo móvil - show/hide
                const isOpen = sidebar.classList.contains('show');
                
                if (isOpen) {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                    document.body.classList.remove('sidebar-open');
                    document.body.style.overflow = '';
                } else {
                    sidebar.classList.add('show');
                    sidebarOverlay.classList.add('show');
                    document.body.classList.add('sidebar-open');
                    document.body.style.overflow = 'hidden';
                }
                
                console.log('Mobile sidebar toggled, isOpen:', !isOpen);
            } else {
                // Modo escritorio - collapse/expand
                const isCurrentlyCollapsed = sidebar.classList.contains('collapsed');
                
                if (isCurrentlyCollapsed) {
                    sidebar.classList.remove('collapsed');
                    if (mainContent) mainContent.classList.remove('sidebar-collapsed');
                    localStorage.setItem('sidebarCollapsed', 'false');
                } else {
                    sidebar.classList.add('collapsed');
                    if (mainContent) mainContent.classList.add('sidebar-collapsed');
                    localStorage.setItem('sidebarCollapsed', 'true');
                }
                
                console.log('Desktop sidebar toggled, collapsed:', !isCurrentlyCollapsed);
            }
        };
        
        // Close sidebar function
        const closeSidebar = (e) => {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
            document.body.classList.remove('sidebar-open');
            document.body.style.overflow = '';
            
            console.log('Sidebar closed');
        };
        
        // Event listeners with mobile support
        sidebarToggle.addEventListener('click', toggleSidebar);
        sidebarToggle.addEventListener('touchstart', (e) => {
            e.preventDefault();
            toggleSidebar(e);
        }, { passive: false });
        
        if (sidebarClose) {
            sidebarClose.addEventListener('click', closeSidebar);
            sidebarClose.addEventListener('touchstart', (e) => {
                e.preventDefault();
                closeSidebar(e);
            }, { passive: false });
        }
        
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', closeSidebar);
            sidebarOverlay.addEventListener('touchstart', (e) => {
                e.preventDefault();
                closeSidebar(e);
            }, { passive: false });
        }
        
        // Handle window resize with debounce
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                console.log('Window resized to:', window.innerWidth);
                
                if (window.innerWidth < 992) {
                    // Mobile: Remove collapsed state and restore body scroll
                    sidebar.classList.remove('collapsed');
                    if (mainContent) mainContent.classList.remove('sidebar-collapsed');
                    if (!sidebar.classList.contains('show')) {
                        document.body.classList.remove('sidebar-open');
                        document.body.style.overflow = '';
                    }
                } else {
                    // Desktop: Close mobile sidebar and restore collapsed state
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                    document.body.classList.remove('sidebar-open');
                    document.body.style.overflow = '';
                    
                    // Restore collapsed state from localStorage
                    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                    if (isCollapsed) {
                        sidebar.classList.add('collapsed');
                        if (mainContent) mainContent.classList.add('sidebar-collapsed');
                    }
                }
            }, 250);
        });
        
        // Close sidebar when clicking on nav links in mobile
        sidebar.querySelectorAll('.nav-link:not(.has-submenu)').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 992) {
                    closeSidebar();
                }
            });
        });
        
        // Handle escape key to close sidebar
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && sidebar.classList.contains('show')) {
                closeSidebar();
            }
        });
        
        // Mark active nav link
        this.setActiveNavLink();
        
        // Add tooltips for collapsed sidebar items
        this.addSidebarTooltips();
    },
    
    // Add tooltips to sidebar items when collapsed
    addSidebarTooltips() {
        const sidebar = document.getElementById('sidebar');
        if (!sidebar) return;
        
        sidebar.querySelectorAll('.nav-link').forEach(link => {
            const textElement = link.querySelector('.sidebar-text');
            if (textElement) {
                link.setAttribute('data-tooltip', textElement.textContent.trim());
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
    console.log('DOM Content Loaded');
    
    // Debug mobile information
    if (window.innerWidth < 992) {
        console.log('Mobile device detected');
        console.log('User Agent:', navigator.userAgent);
        console.log('Touch support:', 'ontouchstart' in window);
    }
    
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
    
    // Debug: Check if hamburger button exists and add visual indicator
    setTimeout(() => {
        const hamburger = document.getElementById('sidebarToggle');
        if (hamburger && window.innerWidth < 992) {
            console.log('Hamburger button found in mobile');
            hamburger.style.border = '2px solid yellow'; // Temporary visual debug
            hamburger.title = 'Tap to open menu';
        }
    }, 1000);
});