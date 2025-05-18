<?php
/**
 * Punto de entrada principal de la aplicación
 * 
 * Este archivo inicializa la aplicación y configura todas las rutas
 */

// Definir la ruta base del proyecto
define('BASE_PATH', dirname(__DIR__));

// Cargar el autoloader de Composer
require_once BASE_PATH . '/vendor/autoload.php';

// Cargar la configuración
$config = require_once BASE_PATH . '/app/config/config.php';

// Iniciar sesión
session_start();

// Inicializar la aplicación
$app = new \App\Core\App($config);

// -----------------------------------------
// Configurar rutas
// -----------------------------------------

// Rutas para páginas públicas
$app->router->get('/', [\App\Controllers\PageController::class, 'index']);

// Rutas de autenticación
$app->router->get('/login', [\App\Controllers\AuthController::class, 'login']);
$app->router->post('/auth/authenticate', [\App\Controllers\AuthController::class, 'authenticate']);
$app->router->get('/register', [\App\Controllers\AuthController::class, 'register']);
$app->router->post('/auth/store', [\App\Controllers\AuthController::class, 'store']);
$app->router->get('/logout', [\App\Controllers\AuthController::class, 'logout']);

// Aplicar middleware de autenticación
// Definir rutas protegidas
$protectedRoutes = [
    '/dashboard',
    '/profile',
    '/orders'
    // Agrega aquí otras rutas que requieran autenticación
];

// Registrar middleware de autenticación
$authMiddleware = new \App\Middleware\AuthMiddleware($protectedRoutes);

// Configurar manejador para rutas no encontradas
$app->router->setNotFoundHandler(function($request, $response) {
    $response->setStatusCode(404);
    return '<h1>404 - Página no encontrada</h1>';
});

// -----------------------------------------
// Ejecutar la aplicación
// -----------------------------------------
$app->run();