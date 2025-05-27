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
$app->router->get('/cart/history', [\App\Controllers\CartController::class, 'history']);
$app->router->post('/cart/undo', [\App\Controllers\CartController::class, 'undoLastAction']);
$app->router->post('/cart/redo', [\App\Controllers\CartController::class, 'redoLastAction']);

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

// Rutas para páginas estáticas
$app->router->get('/servicios', [\App\Controllers\PageController::class, 'services']);
$app->router->get('/services', [\App\Controllers\PageController::class, 'services']);
$app->router->get('/ayuda', [\App\Controllers\PageController::class, 'help']);
$app->router->get('/help', [\App\Controllers\PageController::class, 'help']);

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

// Rutas de café personalizado
$app->router->get('/custom-coffee', [\App\Controllers\CustomCoffeeController::class, 'index']);
$app->router->get('/cafe-personalizado', [\App\Controllers\CustomCoffeeController::class, 'index']);
$app->router->get('/custom-coffee/builder', [\App\Controllers\CustomCoffeeController::class, 'builder']);
$app->router->get('/cafe-personalizado/constructor', [\App\Controllers\CustomCoffeeController::class, 'index']);
$app->router->get('/custom-coffee/recipes', [\App\Controllers\CustomCoffeeController::class, 'recipes']);
$app->router->get('/cafe-personalizado/recetas', [\App\Controllers\CustomCoffeeController::class, 'recipes']);
$app->router->get('/api/custom-coffee/get-components', [\App\Controllers\CustomCoffeeController::class, 'getComponentes']);
$app->router->post('/api/custom-coffee/save-recipe', [\App\Controllers\CustomCoffeeController::class, 'saveRecipe']);
$app->router->post('/api/custom-coffee/place-order', [\App\Controllers\CustomCoffeeController::class, 'placeOrder']);
$app->router->post('/api/custom-coffee/delete-recipe/:id', [\App\Controllers\CustomCoffeeController::class, 'deleteRecipe']);
$app->router->post('/api/custom-coffee/cancel/:id', [\App\Controllers\CustomCoffeeController::class, 'cancel']);
$app->router->get('/custom-coffee/orders', [\App\Controllers\CustomCoffeeController::class, 'orders']);
$app->router->get('/cafe-personalizado/pedidos', [\App\Controllers\CustomCoffeeController::class, 'orders']);
$app->router->get('/custom-coffee/order/{id}', [\App\Controllers\CustomCoffeeController::class, 'orderDetails']);
$app->router->get('/cafe-personalizado/pedido/{id}', [\App\Controllers\CustomCoffeeController::class, 'orderDetails']);

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
    '/cart/checkout',
    // Rutas protegidas del café personalizado
    '/custom-coffee/recipes',
    '/cafe-personalizado/recetas',
    '/custom-coffee/orders',
    '/cafe-personalizado/pedidos',
    '/custom-coffee/order',
    '/cafe-personalizado/pedido',
    '/api/custom-coffee/save-recipe',
    '/api/custom-coffee/place-order',
    '/api/custom-coffee/delete-recipe',
    '/api/custom-coffee/cancel'
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