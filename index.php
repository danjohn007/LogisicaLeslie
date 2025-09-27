<?php
/**
 * Punto de entrada principal
 * Sistema de Logística - Quesos y Productos Leslie
 * 
 * Este archivo es el punto de entrada principal del sistema.
 * Maneja todas las rutas y coordina el flujo MVC.
 */

// Cargar configuración
require_once 'config/config.php';
require_once 'config/database.php';

// Cargar clases del core
require_once 'app/core/Controller.php';
require_once 'app/core/Model.php';
require_once 'app/core/Router.php';

// Crear directorio de logs si no existe
if (!is_dir(LOGS_PATH)) {
    mkdir(LOGS_PATH, 0755, true);
}

// Crear directorio de uploads si no existe
if (!is_dir(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}

// Crear directorio de códigos QR si no existe
if (!is_dir(QR_CODE_PATH)) {
    mkdir(QR_CODE_PATH, 0755, true);
}

try {
    // Inicializar y ejecutar el router
    $router = new Router();
    $router->route();
    
} catch (Exception $e) {
    // Log del error
    error_log(date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n", 3, LOGS_PATH . 'error.log');
    
    // Mostrar error amigable
    if (APP_ENVIRONMENT === 'development') {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
        echo "<h3>Error del Sistema</h3>";
        echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
        echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        echo "</div>";
    } else {
        echo "<div style='text-align: center; margin-top: 50px;'>";
        echo "<h1>Sistema Temporalmente No Disponible</h1>";
        echo "<p>Por favor, intente más tarde.</p>";
        echo "</div>";
    }
}