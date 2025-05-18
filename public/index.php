<?php
// Versión mejorada de public/index.php

/**
 * Punto de entrada de la aplicación - Versión depuración mejorada
 */

// Habilitar el buffer de salida para capturar cualquier salida temprana
ob_start();

// Inicializar la sesión antes de cualquier salida
session_start();

// Configurar reporte de errores predeterminado
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// Configurar la visualización de errores según la configuración
if (isset($config['app']['display_errors'])) {
    ini_set('display_errors', $config['app']['display_errors'] ? 1 : 0);
}

// Determinar si estamos en modo debug
$debugMode = isset($config['app']['debug']) && $config['app']['debug'];

// Habilitar register_shutdown_function para capturar errores fatales en modo debug
if ($debugMode) {
    register_shutdown_function(function() {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            ob_clean(); // Limpiar salida existente
            echo '<html><head><title>Error Fatal</title>';
            echo '<style>body{font-family:sans-serif;line-height:1.5;padding:20px;max-width:800px;margin:0 auto;}
                  h1{color:#e74c3c;}code{background:#f8f9fa;padding:2px 5px;}</style></head>';
            echo '<body><h1>Error Fatal</h1>';
            echo '<p><strong>Mensaje:</strong> ' . htmlspecialchars($error['message']) . '</p>';
            echo '<p><strong>Archivo:</strong> ' . htmlspecialchars($error['file']) . ' (línea ' . $error['line'] . ')</p>';
            echo '</body></html>';
        }
    });
}

// Log de inicio
if ($debugMode) {
    error_log("===============================================");
    error_log("Iniciando aplicación en " . date('Y-m-d H:i:s'));
    error_log("REQUEST_URI: " . $_SERVER['REQUEST_URI']);
    error_log("SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME']);
    error_log("DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT']);
    error_log("HTTP_HOST: " . $_SERVER['HTTP_HOST']);
}

// Inicializar el helper de activos con la nueva versión mejorada
\App\Helpers\AssetHelper::init($config);

try {
    // Inicializar la aplicación
    $app = new \App\Core\App($config);
    
    // Definir rutas - asegúrate de que las clases existan o comenta las que aún no hayas creado
    // --- Rutas principales ---
    $app->router->get('/', [\App\Controllers\PageController::class, 'index']);
    
    // --- Rutas de autenticación ---
    // Comenta estas rutas si aún no has creado los controladores
    /*
    $app->router->get('/login', [\App\Controllers\AuthController::class, 'showLoginForm']);
    $app->router->post('/login', [\App\Controllers\AuthController::class, 'login']);
    $app->router->get('/registro', [\App\Controllers\AuthController::class, 'showRegisterForm']);
    $app->router->post('/registro', [\App\Controllers\AuthController::class, 'register']);
    $app->router->get('/logout', [\App\Controllers\AuthController::class, 'logout']);
    
    // --- Rutas del dashboard (protegidas) ---
    $app->router->get('/dashboard', [\App\Controllers\DashboardController::class, 'index']);
    */
    
    // --- Ruta para depuración ---
    if ($debugMode) {
        $app->router->get('/debug', function($request, $response) use ($config) {
            echo "<h1>Información de Depuración</h1>";
            echo "<h2>URL Base</h2>";
            echo "<p>URL Base: " . \App\Helpers\AssetHelper::getBaseUrl() . "</p>";
            
            echo "<h2>Rutas CSS</h2>";
            echo "<p>CSS Main: " . \App\Helpers\AssetHelper::css('main') . "</p>";
            
            echo "<h2>Variables del Servidor</h2>";
            echo "<pre>";
            print_r($_SERVER);
            echo "</pre>";
            
            echo "<h2>Configuración</h2>";
            echo "<pre>";
            print_r($config);
            echo "</pre>";
        });
    }
    
    // Log antes de ejecutar
    if ($debugMode) {
        error_log("Ejecutando la aplicación...");
        error_log("URL Base detectada: " . \App\Helpers\AssetHelper::getBaseUrl());
    }
    
    // Ejecutar la aplicación
    $app->run();
    
} catch (Exception $e) {
    // Limpiar cualquier salida previa
    ob_clean();
    
    // Mostrar un mensaje de error detallado en modo depuración
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
        
        // Log del error
        error_log('Error en la aplicación: ' . $e->getMessage() . ' en ' . $e->getFile() . ' línea ' . $e->getLine());
    } else {
        // En producción, mostrar un mensaje genérico
        echo '<html><head><title>Error</title>';
        echo '<style>body{font-family:sans-serif;line-height:1.5;padding:20px;max-width:800px;margin:0 auto;text-align:center;}
              h1{color:#e74c3c;}</style></head>';
        echo '<body>';
        echo '<h1>Ha ocurrido un error</h1>';
        echo '<p>Lo sentimos, ha ocurrido un error inesperado. Por favor, inténtelo de nuevo más tarde.</p>';
        echo '</body></html>';
        
        // Log del error en producción
        error_log('Error en la aplicación: ' . $e->getMessage());
    }
}

// Enviar salida al navegador
ob_end_flush();