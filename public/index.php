<?php
/**
 * Punto de entrada de la aplicación - Versión de depuración
 */

// Habilitar el buffer de salida para capturar cualquier salida temprana
ob_start();

// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inicializar la sesión antes de cualquier salida
session_start();

// Cargar el autoloader
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    die("Error: No se puede encontrar el archivo autoload.php. Verifica que Composer esté instalado correctamente.");
}
require_once $autoloadPath;

// Cargar la configuración
$configPath = __DIR__ . '/../app/config/config.php';
if (!file_exists($configPath)) {
    die("Error: No se puede encontrar el archivo de configuración.");
}
$config = require_once $configPath;

// Configurar visualización de errores según configuración
if (isset($config['app']['display_errors']) && $config['app']['display_errors']) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Log de depuración
$debugMode = isset($config['app']['debug']) && $config['app']['debug'];
if ($debugMode) {
    error_log("Iniciando aplicación...");
    error_log("REQUEST_URI: " . $_SERVER['REQUEST_URI']);
    error_log("SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME']);
}

try {
    // Inicializar la aplicación
    $app = new \App\Core\App($config);
    
    // Definir rutas
    // --- Rutas de autenticación ---
    $app->router->get('/login', [\App\Controllers\AuthController::class, 'showLoginForm']);
    $app->router->post('/login', [\App\Controllers\AuthController::class, 'login']);
    $app->router->get('/registro', [\App\Controllers\AuthController::class, 'showRegisterForm']);
    $app->router->post('/registro', [\App\Controllers\AuthController::class, 'register']);
    $app->router->get('/logout', [\App\Controllers\AuthController::class, 'logout']);
    
    // --- Rutas del dashboard (protegidas) ---
    $app->router->get('/dashboard', [\App\Controllers\DashboardController::class, 'index']);
    
    // --- Rutas de páginas ---
    $app->router->get('/', [\App\Controllers\PageController::class, 'index']);
    
    // Agregar más rutas aquí...
    
    // Ejecutar la aplicación
    if ($debugMode) {
        error_log("Ejecutando la aplicación...");
    }
    
    $app->run();
    
} catch (Exception $e) {
    // Limpiar cualquier salida previa
    ob_clean();
    
    // Mostrar un mensaje de error detallado en modo depuración
    if (isset($config['app']['debug']) && $config['app']['debug']) {
        echo '<h1>Error en la aplicación</h1>';
        echo '<p><strong>Mensaje:</strong> ' . $e->getMessage() . '</p>';
        echo '<p><strong>Archivo:</strong> ' . $e->getFile() . ' (línea ' . $e->getLine() . ')</p>';
        echo '<h2>Stack Trace:</h2>';
        echo '<pre>' . $e->getTraceAsString() . '</pre>';
        
        // Log del error
        error_log('Error en la aplicación: ' . $e->getMessage() . ' en ' . $e->getFile() . ' línea ' . $e->getLine());
    } else {
        // En producción, mostrar un mensaje genérico
        echo '<h1>Ha ocurrido un error</h1>';
        echo '<p>Lo sentimos, ha ocurrido un error. Por favor, inténtelo de nuevo más tarde.</p>';
        
        // Log del error en producción
        error_log('Error en la aplicación: ' . $e->getMessage());
    }
}

// Enviar salida al navegador
ob_end_flush();