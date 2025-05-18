<?php
/**
 * Punto de entrada principal de la aplicación
 */

// Inicializar la sesión
session_start();

// Configurar reporte de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Definir constante para la ruta base del proyecto
define('BASE_PATH', dirname(__DIR__));

// Cargar configuración primero
$configPath = BASE_PATH . '/app/config/config.php';
if (!file_exists($configPath)) {
    die("Error: No se puede encontrar el archivo de configuración.");
}
$config = require_once $configPath;

// Configurar la visualización de errores según la configuración
if (isset($config['app']['display_errors'])) {
    ini_set('display_errors', $config['app']['display_errors'] ? 1 : 0);
}

// Determinar si estamos en modo debug
$debugMode = isset($config['app']['debug']) && $config['app']['debug'];

// Cargar los helpers necesarios
require_once BASE_PATH . '/app/helpers/AssetHelper.php';
require_once BASE_PATH . '/app/helpers/ViewHelper.php';

// Cargar el autoloader
$autoloadPath = BASE_PATH . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} else {
    // Si no hay autoloader, cargamos las clases manualmente
    $coreFiles = glob(BASE_PATH . '/app/core/*.php');
    foreach ($coreFiles as $file) {
        require_once $file;
    }
    
    $controllerFiles = glob(BASE_PATH . '/app/controllers/*.php');
    foreach ($controllerFiles as $file) {
        require_once $file;
    }
    
    if ($debugMode) {
        error_log("Autoloader no encontrado. Cargando archivos manualmente.");
    }
}

try {
    // Inicializar la aplicación
    $app = new \App\Core\App($config);
    
    // Configurar rutas
    $app->router->get('/', [\App\Controllers\PageController::class, 'index']);
    
    // --- Rutas de autenticación (comentadas hasta que se implementen) ---
    /*
    $app->router->get('/login', [\App\Controllers\AuthController::class, 'showLoginForm']);
    $app->router->post('/login', [\App\Controllers\AuthController::class, 'login']);
    $app->router->get('/registro', [\App\Controllers\AuthController::class, 'showRegisterForm']);
    $app->router->post('/registro', [\App\Controllers\AuthController::class, 'register']);
    $app->router->get('/logout', [\App\Controllers\AuthController::class, 'logout']);
    */
    
    // Ruta para depuración
    if ($debugMode) {
        $app->router->get('/debug', function($request, $response) use ($config) {
            echo "<h1>Información de Depuración</h1>";
            
            echo "<h2>Rutas CSS</h2>";
            echo "<p>CSS Main: " . AssetHelper::css('main') . "</p>";
            
            echo "<h2>Variables del Servidor</h2>";
            echo "<pre>";
            print_r($_SERVER);
            echo "</pre>";
            
            echo "<h2>Configuración</h2>";
            echo "<pre>";
            print_r($config);
            echo "</pre>";
            
            echo "<h2>Prueba de enlaces CSS</h2>";
            echo "<link rel='stylesheet' href='" . AssetHelper::css('main') . "'>";
            echo "<div style='border: 1px solid black; padding: 20px; margin: 20px;'>
                Si el CSS 'main.css' está correctamente enlazado, este div debería tener estilos aplicados.
            </div>";
        });
    }
    
    // Ejecutar la aplicación
    $app->run();
    
} catch (Exception $e) {
    // Mostrar error detallado en modo debug
    if ($debugMode) {
        echo '<html><head><title>Error en la aplicación</title>';
        echo '<style>body{font-family:sans-serif;line-height:1.5;padding:20px;max-width:800px;margin:0 auto;}
              h1{color:#e74c3c;}pre{background:#f8f9fa;padding:15px;overflow:auto;}</style></head>';
        echo '<body>';
        echo '<h1>Error en la aplicación</h1>';
        echo '<p><strong>Mensaje:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p><strong>Archivo:</strong> ' . htmlspecialchars($e->getFile()) . ' (línea ' . $e->getLine() . ')</p>';
        echo '<h2>Stack Trace:</h2>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        echo '</body></html>';
    } else {
        // Mensaje genérico en producción
        echo '<html><head><title>Error</title>';
        echo '<style>body{font-family:sans-serif;line-height:1.5;padding:20px;max-width:800px;margin:0 auto;text-align:center;}
              h1{color:#e74c3c;}</style></head>';
        echo '<body>';
        echo '<h1>Ha ocurrido un error</h1>';
        echo '<p>Lo sentimos, ha ocurrido un error inesperado. Por favor, inténtelo de nuevo más tarde.</p>';
        echo '</body></html>';
    }
    
    // Log del error
    error_log('Error en la aplicación: ' . $e->getMessage() . ' en ' . $e->getFile() . ' línea ' . $e->getLine());
}