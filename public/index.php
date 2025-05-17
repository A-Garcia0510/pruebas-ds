<?php
/**
 * Punto de entrada de la aplicación
 */

// Mostrar errores en desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cargar el autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Cargar la configuración
$config = require_once __DIR__ . '/../app/config/config.php';

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