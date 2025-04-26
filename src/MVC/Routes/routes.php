<?php
// src/MVC/Routes/routes.php

use App\MVC\Controllers\Auth\LoginController;
use App\MVC\Controllers\Auth\RegisterController;
use App\MVC\Controllers\Shop\ProductController;
use App\MVC\Controllers\Shop\CartController;
use App\MVC\Controllers\HomeController;
use App\MVC\Routes\Router;

/**
 * Define las rutas de la aplicación
 *
 * @param Router $router
 */
return function (Router $router) {
    // Rutas de la página principal
    $router->get('/', [HomeController::class, 'index']);
    $router->get('/index.php', [HomeController::class, 'index']);
    
    // Rutas de autenticación
    $router->get('/login', [LoginController::class, 'showLoginForm']);
    $router->post('/login', [LoginController::class, 'login']);
    $router->get('/register', [RegisterController::class, 'showRegistrationForm']);
    $router->post('/register', [RegisterController::class, 'register']);
    $router->get('/logout', [LoginController::class, 'logout']);
    
    // Rutas de productos
    $router->get('/products', [ProductController::class, 'index']);
    $router->get('/product/details', [ProductController::class, 'details']);
    
    // Rutas de carrito de compras
    $router->get('/cart', [CartController::class, 'show']);
    $router->post('/cart/add', [CartController::class, 'add']);
    $router->post('/cart/remove', [CartController::class, 'remove']);
    $router->post('/cart/checkout', [CartController::class, 'checkout']);
    
    // Definimos qué hacer cuando una ruta no existe
    $router->setNotFoundHandler(function() {
        header("HTTP/1.0 404 Not Found");
        require_once __DIR__ . '/../Views/errors/404.php';
    });
};