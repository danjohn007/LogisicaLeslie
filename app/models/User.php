<?php
/**
 * Modelo User
 * Sistema de Logística - Quesos y Productos Leslie
 */

class User extends Model {
    protected $table = 'users';
    
    private function getNowFunction() {
        // Check if we're using SQLite (demo mode) or MySQL
        $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
        return ($driver === 'sqlite') ? 'CURRENT_TIMESTAMP' : 'NOW()';
    }
    
    public function authenticate($username, $password) {
        $sql = "SELECT * FROM {$this->table} WHERE (username = ? OR email = ?) AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        
        return false;
    }
    
    public function createUser($data) {
        // Verificar que el username y email sean únicos
        if ($this->usernameExists($data['username'])) {
            throw new Exception('El nombre de usuario ya existe');
        }
        
        if ($this->emailExists($data['email'])) {
            throw new Exception('El email ya está registrado');
        }
        
        // Encriptar contraseña
        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        unset($data['password']);
        
        return $this->create($data);
    }
    
    public function usernameExists($username, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE username = ?";
        $params = [$username];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
    
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
    
    public function findByRole($role) {
        $sql = "SELECT * FROM {$this->table} WHERE role = ? AND is_active = 1 ORDER BY first_name, last_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$role]);
        return $stmt->fetchAll();
    }
    
    public function getActiveUsers() {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY role, first_name, last_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function logLogin($userId) {
        $nowFunction = $this->getNowFunction();
        $sql = "INSERT INTO user_sessions (user_id, login_time, ip_address, user_agent) VALUES (?, $nowFunction, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $userId,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    }
    
    public function logLogout($userId) {
        $nowFunction = $this->getNowFunction();
        $sql = "UPDATE user_sessions SET logout_time = $nowFunction WHERE user_id = ? AND logout_time IS NULL ORDER BY login_time DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId]);
    }
    
    public function getUserStats($userId) {
        $stats = [];
        
        // Estadísticas de login
        $sql = "SELECT 
                    COUNT(*) as total_logins,
                    MAX(login_time) as last_login,
                    MIN(login_time) as first_login
                FROM user_sessions 
                WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $stats['login_stats'] = $stmt->fetch();
        
        // Estadísticas de actividad según el rol
        $user = $this->findById($userId);
        if ($user) {
            switch ($user['role']) {
                case 'seller':
                    $stats['orders_created'] = $this->getUserOrdersCount($userId);
                    $stats['sales_made'] = $this->getUserSalesCount($userId);
                    break;
                case 'driver':
                    $stats['routes_completed'] = $this->getUserRoutesCount($userId);
                    break;
                case 'warehouse':
                    $stats['lots_created'] = $this->getUserLotsCount($userId);
                    break;
            }
        }
        
        return $stats;
    }
    
    private function getUserOrdersCount($userId) {
        $sql = "SELECT COUNT(*) as count FROM orders WHERE created_by = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    private function getUserSalesCount($userId) {
        $sql = "SELECT COUNT(*) as count FROM direct_sales WHERE seller_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    private function getUserRoutesCount($userId) {
        $sql = "SELECT COUNT(*) as count FROM routes WHERE driver_id = ? AND status = 'completed'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    private function getUserLotsCount($userId) {
        $sql = "SELECT COUNT(*) as count FROM production_lots WHERE created_by = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['count'];
    }
}