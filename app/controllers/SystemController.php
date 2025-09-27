<?php
/**
 * Controlador del Sistema
 * Para pruebas de conexión y configuración
 */

class SystemController extends Controller {
    
    public function testConnection() {
        $db = Database::getInstance();
        $result = $db->testConnection();
        
        $data = [
            'title' => 'Test de Conexión a Base de Datos',
            'connection_result' => $result,
            'php_version' => phpversion(),
            'server_info' => $_SERVER['SERVER_SOFTWARE'] ?? 'No disponible',
            'base_url' => BASE_URL,
            'app_name' => APP_NAME,
            'app_version' => APP_VERSION,
            'database_config' => [
                'host' => DB_HOST,
                'database' => DB_NAME,
                'user' => DB_USER,
                'charset' => DB_CHARSET
            ]
        ];
        
        $this->view('system/test-connection', $data);
    }
    
    public function phpinfo() {
        // Solo disponible en desarrollo
        if (APP_ENVIRONMENT !== 'development') {
            http_response_code(403);
            echo "Acceso denegado";
            return;
        }
        
        phpinfo();
    }
}