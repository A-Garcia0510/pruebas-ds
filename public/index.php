<?php
/**
 * Punto de entrada de la aplicación - Versión de depuración
 */

// Cargar el autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Cargar la configuración
$config = require_once __DIR__ . '/../app/config/config.php';

// Configurar visualización de errores según configuración
if (isset($config['app']['display_errors']) && $config['app']['display_errors']) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
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
    $app->run();
} catch (Exception $e) {
    // Mostrar un mensaje de error detallado en modo depuración
    if (isset($config['app']['debug']) && $config['app']['debug']) {
        echo '<h1>Error en la aplicación</h1>';
        echo '<p>' . $e->getMessage() . '</p>';
        echo '<h2>Stack Trace:</h2>';
        echo '<pre>' . $e->getTraceAsString() . '</pre>';
    } else {
        // En producción, mostrar un mensaje genérico
        echo '<h1>Ha ocurrido un error</h1>';
        echo '<p>Lo sentimos, ha ocurrido un error. Por favor, inténtelo de nuevo más tarde.</p>';
    }
}