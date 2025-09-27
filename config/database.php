<?php
/**
 * Clase de Conexión a Base de Datos
 * Sistema de Logística - Quesos y Productos Leslie
 */

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        // Demo mode - use SQLite in memory for testing
        if (defined('DEMO_MODE') && DEMO_MODE) {
            try {
                $this->connection = new PDO('sqlite::memory:');
                $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->setupDemoData();
            } catch (PDOException $e) {
                die("Error de conexión a la base de datos demo: " . $e->getMessage());
            }
        } else {
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
                die("Error de conexión a la base de datos: " . $e->getMessage());
            }
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
            
            if (defined('DEMO_MODE') && DEMO_MODE) {
                return [
                    'status' => 'success',
                    'message' => 'Conexión exitosa a la base de datos demo (SQLite)',
                    'server_info' => 'SQLite Demo Mode',
                    'server_version' => $this->connection->getAttribute(PDO::ATTR_SERVER_VERSION)
                ];
            }
            
            return [
                'status' => 'success',
                'message' => 'Conexión exitosa a la base de datos',
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
    
    private function setupDemoData() {
        // Create basic tables for demo
        $sql = "
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                email TEXT UNIQUE NOT NULL,
                password_hash TEXT NOT NULL,
                first_name TEXT NOT NULL,
                last_name TEXT NOT NULL,
                role TEXT NOT NULL,
                phone TEXT,
                is_active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            
            CREATE TABLE customers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT UNIQUE NOT NULL,
                business_name TEXT NOT NULL,
                contact_name TEXT,
                phone TEXT,
                email TEXT,
                address TEXT,
                city TEXT,
                state TEXT,
                postal_code TEXT,
                credit_limit DECIMAL(10,2) DEFAULT 0,
                credit_days INTEGER DEFAULT 0,
                is_active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            
            CREATE TABLE orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_number TEXT UNIQUE NOT NULL,
                customer_id INTEGER NOT NULL,
                order_date DATE NOT NULL,
                delivery_date DATE,
                status TEXT DEFAULT 'pending',
                total_amount DECIMAL(10,2) DEFAULT 0,
                discount_amount DECIMAL(10,2) DEFAULT 0,
                final_amount DECIMAL(10,2) DEFAULT 0,
                payment_method TEXT DEFAULT 'cash',
                payment_status TEXT DEFAULT 'pending',
                notes TEXT,
                qr_code TEXT,
                created_by INTEGER,
                assigned_to INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            
            CREATE TABLE user_sessions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                login_time DATETIME DEFAULT CURRENT_TIMESTAMP,
                logout_time DATETIME NULL,
                ip_address TEXT,
                user_agent TEXT
            );
        ";
        
        $this->connection->exec($sql);
        
        // Insert demo data
        $demoData = "
            INSERT INTO users (username, email, password_hash, first_name, last_name, role, phone) VALUES
            ('admin', 'admin@leslie.com', '$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'Sistema', 'admin', '555-0001'),
            ('gerente', 'gerente@leslie.com', '$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carlos', 'Pérez', 'manager', '555-0002');
            
            INSERT INTO customers (code, business_name, contact_name, phone, email, address, city, state, credit_limit, credit_days) VALUES
            ('CLI001', 'Tienda Don Carlos', 'Carlos Ramírez', '555-1001', 'carlos@tienda.com', 'Av. Principal 123', 'México', 'CDMX', 5000.00, 15),
            ('CLI002', 'Supermercado La Esquina', 'Rosa Hernández', '555-1002', 'rosa@esquina.com', 'Calle 5 de Mayo 456', 'Guadalajara', 'Jalisco', 8000.00, 30);
            
            INSERT INTO orders (order_number, customer_id, order_date, delivery_date, status, total_amount, final_amount, created_by) VALUES
            ('PED2024001', 1, '2024-01-25', '2024-01-26', 'confirmed', 450.00, 450.00, 1),
            ('PED2024002', 2, '2024-01-25', '2024-01-27', 'pending', 680.00, 680.00, 1);
        ";
        
        $this->connection->exec($demoData);
    }
}