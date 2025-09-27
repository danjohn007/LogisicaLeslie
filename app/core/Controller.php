<?php
/**
 * Clase Base Controller
 * Sistema de Logística - Quesos y Productos Leslie
 */

class Controller {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->startSession();
    }
    
    protected function startSession() {
        if (session_status() == PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_start();
            
            // Verificar tiempo de vida de la sesión
            if (isset($_SESSION['last_activity']) && 
                (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
                session_unset();
                session_destroy();
                session_start();
            }
            $_SESSION['last_activity'] = time();
        }
    }
    
    protected function view($view, $data = []) {
        extract($data);
        
        $viewFile = dirname(__DIR__) . "/views/{$view}.php";
        
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("Vista no encontrada: {$view}");
        }
    }
    
    protected function redirect($url) {
        if (strpos($url, 'http') !== 0) {
            $url = BASE_URL . ltrim($url, '/');
        }
        header("Location: {$url}");
        exit();
    }
    
    protected function isAuthenticated() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    protected function requireAuth() {
        if (!$this->isAuthenticated()) {
            $this->redirect('auth/login');
        }
    }
    
    protected function jsonResponse($data, $httpCode = 200) {
        http_response_code($httpCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
    
    protected function getUserRole() {
        return $_SESSION['user_role'] ?? 'guest';
    }
    
    protected function hasPermission($permission) {
        $userRole = $this->getUserRole();
        $permissions = [
            'admin' => ['all'],
            'manager' => ['production', 'orders', 'routes', 'sales', 'reports'],
            'seller' => ['orders', 'sales', 'routes'],
            'driver' => ['routes', 'deliveries'],
            'warehouse' => ['production', 'inventory']
        ];
        
        return in_array('all', $permissions[$userRole] ?? []) || 
               in_array($permission, $permissions[$userRole] ?? []);
    }
}