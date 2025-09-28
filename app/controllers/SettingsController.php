<?php
/**
 * Controlador de Configuración del Sistema
 * Sistema de Logística - Quesos y Productos Leslie
 */

class SettingsController extends Controller {
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
    }
    
    public function index() {
        // Solo administradores pueden acceder a configuración
        if ($_SESSION['user_role'] !== 'admin') {
            $this->redirect('dashboard');
            return;
        }
        
        $data = [
            'title' => 'Configuración del Sistema - ' . APP_NAME,
            'system_config' => $this->getSystemConfig(),
            'users' => $this->getUsers(),
            'success' => null,
            'error' => null
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            switch ($action) {
                case 'update_config':
                    $result = $this->updateSystemConfig($_POST);
                    if ($result) {
                        $data['success'] = 'Configuración actualizada exitosamente.';
                        $data['system_config'] = $this->getSystemConfig(); // Refresh data
                    } else {
                        $data['error'] = 'Error al actualizar la configuración.';
                    }
                    break;
                    
                case 'create_user':
                    try {
                        if ($this->createUser($_POST)) {
                            $data['success'] = 'Usuario creado exitosamente.';
                            $data['users'] = $this->getUsers(); // Refresh data
                        } else {
                            $data['error'] = 'Error al crear el usuario.';
                        }
                    } catch (Exception $e) {
                        $data['error'] = 'Error: ' . $e->getMessage();
                    }
                    break;
            }
        }
        
        $this->view('settings/index', $data);
    }
    
    private function getSystemConfig() {
        try {
            $sql = "SELECT * FROM system_config ORDER BY config_key";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $configs = $stmt->fetchAll();
            
            $configArray = [];
            foreach ($configs as $config) {
                $configArray[$config['config_key']] = $config['config_value'];
            }
            
            // Valores por defecto si no existen
            $defaults = [
                'company_name' => 'Quesos y Productos Leslie',
                'company_address' => 'Av. Industria 123, Guadalajara, Jalisco',
                'company_phone' => '33-1234-5678',
                'company_email' => 'info@leslie.com',
                'qr_code_size' => '200',
                'session_timeout' => '3600',
                'backup_frequency' => 'daily',
                'notification_email' => 'admin@leslie.com'
            ];
            
            return array_merge($defaults, $configArray);
        } catch (Exception $e) {
            error_log("Error getting system config: " . $e->getMessage());
            return [];
        }
    }
    
    private function updateSystemConfig($postData) {
        try {
            $this->db->beginTransaction();
            
            $configFields = [
                'company_name', 'company_address', 'company_phone', 'company_email',
                'qr_code_size', 'session_timeout', 'backup_frequency', 'notification_email'
            ];
            
            foreach ($configFields as $field) {
                if (isset($postData[$field])) {
                    $sql = "
                        INSERT INTO system_config (config_key, config_value) 
                        VALUES (?, ?)
                        ON DUPLICATE KEY UPDATE 
                        config_value = VALUES(config_value),
                        updated_at = CURRENT_TIMESTAMP
                    ";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$field, $postData[$field]]);
                }
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error updating system config: " . $e->getMessage());
            return false;
        }
    }
    
    private function getUsers() {
        try {
            $sql = "
                SELECT id, username, email, first_name, last_name, role, phone, is_active, created_at
                FROM users 
                ORDER BY created_at DESC
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting users: " . $e->getMessage());
            return [];
        }
    }
    
    private function createUser($postData) {
        $userData = [
            'username' => trim($postData['username'] ?? ''),
            'email' => trim($postData['email'] ?? ''),
            'password' => $postData['password'] ?? '',
            'first_name' => trim($postData['first_name'] ?? ''),
            'last_name' => trim($postData['last_name'] ?? ''),
            'role' => $postData['role'] ?? 'seller',
            'phone' => trim($postData['phone'] ?? '')
        ];
        
        // Validaciones básicas
        if (empty($userData['username']) || empty($userData['email']) || 
            empty($userData['password']) || empty($userData['first_name']) || 
            empty($userData['last_name'])) {
            throw new Exception('Todos los campos obligatorios deben ser completados.');
        }
        
        if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('El formato del email no es válido.');
        }
        
        if (strlen($userData['password']) < 6) {
            throw new Exception('La contraseña debe tener al menos 6 caracteres.');
        }
        
        // Verificar que username y email sean únicos
        $sql = "SELECT COUNT(*) as count FROM users WHERE username = ? OR email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userData['username'], $userData['email']]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            throw new Exception('El nombre de usuario o email ya están en uso.');
        }
        
        // Crear usuario
        $sql = "
            INSERT INTO users (username, email, password_hash, first_name, last_name, role, phone)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $userData['username'],
            $userData['email'],
            password_hash($userData['password'], PASSWORD_DEFAULT),
            $userData['first_name'],
            $userData['last_name'],
            $userData['role'],
            $userData['phone']
        ]);
    }
    
    public function backup() {
        if ($_SESSION['user_role'] !== 'admin') {
            $this->redirect('dashboard');
            return;
        }
        
        // Implementar backup de base de datos
        $data = [
            'title' => 'Backup del Sistema - ' . APP_NAME,
            'success' => null,
            'error' => null
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $backupFile = $this->createBackup();
                if ($backupFile) {
                    $data['success'] = "Backup creado exitosamente: {$backupFile}";
                } else {
                    $data['error'] = 'Error al crear el backup.';
                }
            } catch (Exception $e) {
                $data['error'] = 'Error: ' . $e->getMessage();
            }
        }
        
        $this->view('settings/backup', $data);
    }
    
    private function createBackup() {
        try {
            $backupDir = dirname(__DIR__) . '/../backups/';
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = $backupDir . $filename;
            
            $command = sprintf(
                'mysqldump --host=%s --user=%s --password=%s %s > %s',
                escapeshellarg(DB_HOST),
                escapeshellarg(DB_USER),
                escapeshellarg(DB_PASS),
                escapeshellarg(DB_NAME),
                escapeshellarg($filepath)
            );
            
            $output = [];
            $returnVar = 0;
            exec($command, $output, $returnVar);
            
            if ($returnVar === 0 && file_exists($filepath)) {
                return $filename;
            } else {
                throw new Exception('Error ejecutando mysqldump');
            }
        } catch (Exception $e) {
            error_log("Error creating backup: " . $e->getMessage());
            throw $e;
        }
    }
}