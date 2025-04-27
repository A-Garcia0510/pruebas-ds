<?php
// src/MVC/Routes/routes.php
namespace App\MVC\Routes;

use App\MVC\Controllers\Auth\LoginController;
use App\MVC\Controllers\Auth\RegisterController;
use App\MVC\Controllers\Auth\DashboardController;

$router = new Router();

// Rutas de autenticación
$router->get('/login', 'Auth\\LoginController@showLoginForm');
$router->post('/login', 'Auth\\LoginController@login');
$router->get('/register', 'Auth\\RegisterController@showRegisterForm');
$router->post('/register', 'Auth\\RegisterController@register');
$router->get('/logout', 'Auth\\LoginController@logout');

// Rutas del dashboard
$router->get('/dashboard', 'Auth\\DashboardController@index');
$router->get('/profile', 'Auth\\DashboardController@profile');

// Ruta principal
$router->get('/', function() {
    // Esto simplemente incluye el index.php existente
    include __DIR__ . '/../../../public/index.php';
});

// Resolver la ruta actual
$router->resolve();

return $router;