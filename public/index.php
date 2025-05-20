<?php
// Modificar el archivo de rutas en public/index.php

/**
 * Punto de entrada principal de la aplicación
 * 
 * Este archivo inicializa la aplicación y configura todas las rutas
 */

// Definir la ruta base del proyecto
define('BASE_PATH', dirname(__DIR__));

// Configurar el manejador de errores personalizado
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $error = [
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => [
            'type' => $errno,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline
        ]
    ];
    
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode($error);
    exit;
});

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
$app->router->get('/inicio', [\App\Controllers\PageController::class, 'index']);

// Rutas de carrito
$app->router->get('/cart', [\App\Controllers\CartController::class, 'index']);
$app->router->get('/carrito', [\App\Controllers\CartController::class, 'index']);

// Rutas API para operaciones del carrito
$app->router->get('/cart/items', [\App\Controllers\CartController::class, 'getItems']);
$app->router->post('/cart/add', [\App\Controllers\CartController::class, 'addItem']);
$app->router->post('/cart/remove', [\App\Controllers\CartController::class, 'removeItem']);
$app->router->post('/cart/checkout', [\App\Controllers\CartController::class, 'checkout']);

// Rutas de productos (en español e inglés para compatibilidad)
$app->router->get('/productos', [\App\Controllers\ProductController::class, 'index']);
$app->router->get('/products', [\App\Controllers\ProductController::class, 'index']);
$app->router->get('/productos/detalle/{id}', [\App\Controllers\ProductController::class, 'detail']);
$app->router->get('/products/detail/{id}', [\App\Controllers\ProductController::class, 'detail']);
$app->router->get('/productos/categoria/{category}', [\App\Controllers\ProductController::class, 'byCategory']);
$app->router->get('/products/category/{category}', [\App\Controllers\ProductController::class, 'byCategory']);
$app->router->get('/api/productos', [\App\Controllers\ProductController::class, 'api']);
$app->router->get('/api/products', [\App\Controllers\ProductController::class, 'api']);
$app->router->post('/api/carrito/agregar', [\App\Controllers\ProductController::class, 'addToCart']);
$app->router->post('/api/cart/add', [\App\Controllers\ProductController::class, 'addToCart']);

// Rutas para otras páginas estáticas
$app->router->get('/servicios', [\App\Controllers\PageController::class, 'services']);
$app->router->get('/ayuda', [\App\Controllers\PageController::class, 'help']);

// Rutas de autenticación
$app->router->get('/login', [\App\Controllers\AuthController::class, 'login']);
$app->router->get('/iniciar-sesion', [\App\Controllers\AuthController::class, 'login']);
$app->router->post('/auth/authenticate', [\App\Controllers\AuthController::class, 'authenticate']);
$app->router->get('/registro', [\App\Controllers\AuthController::class, 'register']);
$app->router->get('/register', [\App\Controllers\AuthController::class, 'register']);
$app->router->post('/auth/store', [\App\Controllers\AuthController::class, 'store']);
$app->router->get('/logout', [\App\Controllers\AuthController::class, 'logout']);
$app->router->get('/cerrar-sesion', [\App\Controllers\AuthController::class, 'logout']);
$app->router->get('/auth/logout', [\App\Controllers\AuthController::class, 'logout']);

// Rutas del dashboard
$app->router->get('/dashboard', [\App\Controllers\DashboardController::class, 'index']);
$app->router->get('/dashboard/', [\App\Controllers\DashboardController::class, 'index']);
$app->router->get('/panel', [\App\Controllers\DashboardController::class, 'index']);

// Aplicar middleware de autenticación
// Definir rutas protegidas
$protectedRoutes = [
    '/dashboard',
    '/dashboard/',
    '/panel',
    '/profile',
    '/perfil',
    '/orders',
    '/pedidos',
    '/cart',
    '/carrito',
    '/cart/items',
    '/cart/remove',
    '/cart/checkout'
    // Agrega aquí otras rutas que requieran autenticación
];

// Verificar si la clase AuthMiddleware existe antes de usarla
if (class_exists('\App\Middleware\AuthMiddleware')) {
    // Registrar middleware de autenticación
    $authMiddleware = new \App\Middleware\AuthMiddleware($protectedRoutes);
} else {
    // Mostrar advertencia si no existe la clase
    error_log('ADVERTENCIA: La clase AuthMiddleware no existe. Rutas protegidas no funcionarán correctamente.');
}

// Configurar manejador para rutas no encontradas
$app->router->setNotFoundHandler(function($request, $response) {
    $response->setStatusCode(404);
    return require_once BASE_PATH . '/app/views/errors/404.php';
});

// -----------------------------------------
// Ejecutar la aplicación
// -----------------------------------------
$app->run();