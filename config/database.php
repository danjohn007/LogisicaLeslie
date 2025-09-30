<?php
/**
 * Clase de Conexión a Base de Datos
 * Sistema de Logística - Quesos y Productos Leslie
 */

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        // Check for demo mode with SQLite
        if (defined('DEMO_MODE') && DEMO_MODE === true) {
            try {
                // Use SQLite for demo
                $dbPath = dirname(__DIR__) . '/database/demo.sqlite';
                $dsn = "sqlite:" . $dbPath;
                $this->connection = new PDO($dsn);
                $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                
                // Initialize demo database if needed
                $this->initializeDemoDatabase();
            } catch (PDOException $e) {
                die("Error de conexión a la base de datos demo: " . $e->getMessage());
            }
        } else {
            // MySQL connection
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
    }
    
    private function initializeDemoDatabase() {
        // Create basic tables for demo
        $sql = "
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            user_role VARCHAR(20) NOT NULL DEFAULT 'user',
            phone VARCHAR(20),
            is_active BOOLEAN DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS routes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            route_name VARCHAR(100) NOT NULL,
            route_date DATE NOT NULL,
            driver_id INTEGER NOT NULL,
            status VARCHAR(20) DEFAULT 'planned',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS route_stops (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            route_id INTEGER NOT NULL,
            order_id INTEGER NOT NULL,
            stop_order INTEGER NOT NULL,
            delivery_status VARCHAR(20) DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS direct_sales (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            sale_number VARCHAR(20) UNIQUE NOT NULL,
            sale_date DATE NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            final_amount DECIMAL(10,2) DEFAULT 0,
            seller_id INTEGER NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS customers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            code VARCHAR(20) UNIQUE NOT NULL,
            business_name VARCHAR(100) NOT NULL,
            contact_name VARCHAR(100),
            phone VARCHAR(20),
            address TEXT,
            city VARCHAR(50),
            credit_limit DECIMAL(10,2) DEFAULT 0,
            is_active BOOLEAN DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE VIEW IF NOT EXISTS route_orders AS 
        SELECT id, route_id, order_id, stop_order as stop_sequence, 
               delivery_status, created_at as actual_arrival
        FROM route_stops;
        ";
        
        $this->connection->exec($sql);
        
        // Insert demo user
        $stmt = $this->connection->prepare("INSERT OR IGNORE INTO users (username, email, password_hash, first_name, last_name, user_role) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['admin', 'admin@leslie.com', password_hash('password', PASSWORD_DEFAULT), 'Admin', 'Leslie', 'admin']);
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