<?php
/**
 * Configuración Principal del Sistema
 * Sistema de Logística - Quesos y Productos Leslie
 */

// Configuración de entorno
define('APP_NAME', 'Sistema de Logística - Quesos y Productos Leslie');
define('APP_VERSION', '1.0.0');
define('APP_ENVIRONMENT', 'development'); // development, production
define('DEMO_MODE', true); // Set to true for demo without database

// Configuración de URL Base (se detecta automáticamente)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$script_path = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
define('BASE_URL', $protocol . $host . $script_path);

// Configuración de Base de Datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'logistica_leslie');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configuración de Sesiones
define('SESSION_LIFETIME', 3600); // 1 hora
define('SESSION_NAME', 'logistica_leslie_session');

// Configuración de Archivos
define('UPLOAD_PATH', dirname(__DIR__) . '/public/uploads/');
define('LOGS_PATH', dirname(__DIR__) . '/logs/');

// Configuración de Zona Horaria
date_default_timezone_set('America/Mexico_City');

// Configuración de Errores
if (APP_ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Configuración de QR Codes
define('QR_CODE_SIZE', 200);
define('QR_CODE_PATH', dirname(__DIR__) . '/public/qr_codes/');

// Configuración de WhatsApp API (placeholder)
define('WHATSAPP_API_URL', 'https://api.whatsapp.com/send');
define('WHATSAPP_BUSINESS_NUMBER', '+52XXXXXXXXXX');

// Autoload de clases
spl_autoload_register(function($class) {
    $paths = [
        dirname(__DIR__) . '/app/controllers/' . $class . '.php',
        dirname(__DIR__) . '/app/models/' . $class . '.php',
        dirname(__DIR__) . '/app/core/' . $class . '.php'
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});