<?php
/**
 * Clase de Conexión a Base de Datos
 * Sistema de Logística - Quesos y Productos Leslie
 */

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        // MySQL connection only
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Error de conexión a la base de datos MySQL: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function testConnection() {
        try {
            $stmt = $this->connection->query("SELECT 1");
            
            return [
                'status' => 'success',
                'message' => 'Conexión exitosa a la base de datos MySQL',
                'server_info' => $this->connection->getAttribute(PDO::ATTR_SERVER_INFO),
                'server_version' => $this->connection->getAttribute(PDO::ATTR_SERVER_VERSION)
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'error',
                'message' => 'Error en la conexión: ' . $e->getMessage()
            ];
        }
    }
}