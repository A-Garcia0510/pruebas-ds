<?php
/**
 * Punto de entrada principal de la aplicación
 */

// Definir el directorio raíz de la aplicación
define('APP_ROOT', dirname(__DIR__));

// Cargar el autoloader de Composer
require_once APP_ROOT . '/vendor/autoload.php';

// Iniciar sesión
session_start();

// Cargar la configuración
$config = require_once APP_ROOT . '/app/config/config.php';

// Inicializar la aplicación
$app = new \App\Core\App($config);

// Definir rutas
$router = $app->router;

// Ruta para la página de inicio
$router->get('/', [\App\Controllers\PageController::class, 'index']);

// Ruta para la página de productos
$router->get('/productos', [\App\Controllers\ProductController::class, 'index']);

// Ruta para detalles de producto
$router->get('/producto/{id}', [\App\Controllers\ProductController::class, 'show']);

// Rutas para servicios
$router->get('/servicios', [\App\Controllers\PageController::class, 'services']);

// Rutas para ayuda
$router->get('/ayuda', [\App\Controllers\PageController::class, 'help']);

// Rutas de autenticación
$router->get('/login', [\App\Controllers\AuthController::class, 'showLoginForm']);
$router->post('/login', [\App\Controllers\AuthController::class, 'login']);
$router->get('/registro', [\App\Controllers\AuthController::class, 'showRegisterForm']);
$router->post('/registro', [\App\Controllers\AuthController::class, 'register']);
$router->get('/logout', [\App\Controllers\AuthController::class, 'logout']);

// Rutas para el perfil
$router->get('/perfil', [\App\Controllers\UserController::class, 'profile']);

// Ejecutar la aplicación
$app->run();