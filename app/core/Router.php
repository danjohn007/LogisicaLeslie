<?php
/**
 * Clase Router para URLs amigables
 * Sistema de Logística - Quesos y Productos Leslie
 */

class Router {
    private $routes = [];
    private $defaultController = 'Home';
    private $defaultAction = 'index';
    
    public function __construct() {
        $this->setupDefaultRoutes();
    }
    
    private function setupDefaultRoutes() {
        // Rutas principales del sistema
        $this->routes = [
            '' => ['controller' => 'Home', 'action' => 'index'],
            'home' => ['controller' => 'Home', 'action' => 'index'],
            'dashboard' => ['controller' => 'Dashboard', 'action' => 'index'],
            
            // Autenticación
            'login' => ['controller' => 'Auth', 'action' => 'login'],
            'logout' => ['controller' => 'Auth', 'action' => 'logout'],
            'auth/login' => ['controller' => 'Auth', 'action' => 'login'],
            'auth/logout' => ['controller' => 'Auth', 'action' => 'logout'],
            'profile' => ['controller' => 'Auth', 'action' => 'profile'],
            'change-password' => ['controller' => 'Auth', 'action' => 'changePassword'],
            
            // Módulos principales
            'produccion' => ['controller' => 'Production', 'action' => 'index'],
            'production' => ['controller' => 'Production', 'action' => 'index'],
            'produccion/create' => ['controller' => 'Production', 'action' => 'create'],
            'production/create' => ['controller' => 'Production', 'action' => 'create'],
            'inventario' => ['controller' => 'Inventory', 'action' => 'index'],
            'pedidos' => ['controller' => 'Orders', 'action' => 'index'],
            'rutas' => ['controller' => 'Routes', 'action' => 'index'],
            'ventas' => ['controller' => 'Sales', 'action' => 'index'],
            'retornos' => ['controller' => 'Returns', 'action' => 'index'],
            'clientes' => ['controller' => 'Customers', 'action' => 'index'],
            'reportes' => ['controller' => 'Reports', 'action' => 'index'],
            'finanzas' => ['controller' => 'Finance', 'action' => 'index'],
            
            // Configuración del sistema
            'configuracion' => ['controller' => 'Settings', 'action' => 'index'],
            'settings' => ['controller' => 'Settings', 'action' => 'index'],
            
            // Test de conexión
            'test-connection' => ['controller' => 'System', 'action' => 'testConnection']
        ];
    }
    
    public function route() {
        $url = $this->getUrl();
        $route = $this->parseRoute($url);
        
        $controllerName = $route['controller'] . 'Controller';
        $action = $route['action'];
        $params = $route['params'];
        
        // Verificar si existe el controlador
        $controllerFile = dirname(__DIR__) . "/controllers/{$controllerName}.php";
        
        if (!file_exists($controllerFile)) {
            $this->show404();
            return;
        }
        
        require_once $controllerFile;
        
        if (!class_exists($controllerName)) {
            $this->show404();
            return;
        }
        
        $controller = new $controllerName();
        
        if (!method_exists($controller, $action)) {
            $this->show404();
            return;
        }
        
        // Ejecutar la acción
        call_user_func_array([$controller, $action], $params);
    }
    
    private function getUrl() {
        $url = $_GET['url'] ?? '';
        return rtrim($url, '/');
    }
    
    private function parseRoute($url) {
        // Si la ruta está definida exactamente
        if (isset($this->routes[$url])) {
            return array_merge($this->routes[$url], ['params' => []]);
        }
        
        // Parsear URL dinámica (controller/action/params)
        $segments = explode('/', $url);
        
        $controller = !empty($segments[0]) ? ucfirst($segments[0]) : $this->defaultController;
        $action = !empty($segments[1]) ? $segments[1] : $this->defaultAction;
        $params = array_slice($segments, 2);
        
        return [
            'controller' => $controller,
            'action' => $action,
            'params' => $params
        ];
    }
    
    private function show404() {
        http_response_code(404);
        require_once dirname(__DIR__) . '/views/errors/404.php';
    }
    
    public function addRoute($pattern, $controller, $action = 'index') {
        $this->routes[$pattern] = [
            'controller' => $controller,
            'action' => $action
        ];
    }
}
