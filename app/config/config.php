<?php
// Configuración actualizada para app/config/config.php

use App\Controllers\CustomCoffeeController;
use App\Controllers\LoyaltyController;
use App\Core\Container;
use App\Core\Database\DatabaseConfiguration;
use App\Core\Database\DatabaseInterface;
use App\Core\Database\MySQLDatabase;
use App\Core\Interfaces\RequestInterface;
use App\Core\Interfaces\ResponseInterface;
use App\Models\CustomCoffee\CoffeeBuilder;
use App\Models\CustomCoffee\CoffeeDirector;
use App\Models\CustomCoffee\CustomCoffeeComponent;
use App\Models\CustomCoffee\CustomCoffeeOrder;
use App\Models\CustomCoffee\CustomCoffeeRecipe;

return [
    'app' => [
        'name' => 'Café-VT',
        'url' => '', // Dejamos vacío para detectar automáticamente
        'env' => 'development', // 'development' o 'production'
        'debug' => true, // Habilitamos el modo debug para ver errores
        'display_errors' => true, // Mostrar errores PHP
    ],
    
    'database' => [
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'ethos_bd'
    ],
    
    // Puedes agregar más configuraciones según sea necesario
    'email' => [
        'host' => 'smtp.example.com',
        'port' => 587,
        'username' => 'noreply@ejemplo.com',
        'password' => 'your-email-password',
        'encryption' => 'tls',
        'from_name' => 'Café-VT',
    ],
    
    'session' => [
        'lifetime' => 7200, // 2 horas en segundos
        'secure' => false, // Cambiar a true en producción con HTTPS
        'httponly' => true,
    ],
    
    // Configuración específica para assets
    'assets' => [
        // Si quieres forzar una URL base para los activos, configúrala aquí
        // De lo contrario, se detectará automáticamente
        'base_url' => '',
        // Por si necesitas una ruta diferente para los CSS
        'css_path' => '/css',
        // Por si necesitas una ruta diferente para los JS
        'js_path' => '/js',
        // Por si necesitas una ruta diferente para las imágenes
        'img_path' => '/img',
    ],
    
    'app_name' => 'Café VT',
    'app_version' => '1.0.0',
    'timezone' => 'America/Mexico_City',
    
    // Configuración de la API de fidelización
    'loyalty_api_url' => 'http://127.0.0.1:8000',
    'loyalty_api_timeout' => 10,
    
    // Configuración de sesión
    'session_lifetime' => 3600,
    'session_name' => 'cafe_vt_session',
    
    // Configuración de rutas
    'default_route' => 'home',
    'error_route' => 'error',
    
    // Configuración de vistas
    'views_path' => __DIR__ . '/../views',
    'layouts_path' => __DIR__ . '/../views/layouts',
    
    // Configuración de assets
    'assets_url' => '/assets',
    'css_path' => '/css',
    'js_path' => '/js',
    'img_path' => '/img',

    'container' => [
        DatabaseInterface::class => function ($container) {
            $config = $container->get('config')['database'];
            $dbConfig = new DatabaseConfiguration($config['host'], $config['username'], $config['password'], $config['database']);
            return new MySQLDatabase($dbConfig);
        },
        // ... (otros servicios)
        CustomCoffeeController::class => function ($container) {
            return new CustomCoffeeController(
                $container->get(RequestInterface::class),
                $container->get(ResponseInterface::class),
                $container,
                $container->get(CustomCoffeeComponent::class),
                $container->get(CustomCoffeeRecipe::class),
                $container->get(CustomCoffeeOrder::class),
                $container->get(CoffeeBuilder::class),
                $container->get(CoffeeDirector::class)
            );
        },
        LoyaltyController::class => function ($container) {
            $config = $container->get('config');
            return new LoyaltyController(
                $container->get(RequestInterface::class),
                $container->get(ResponseInterface::class),
                $container,
                $config['loyalty_api_url'] ?? 'http://localhost:8000/api/v1'
            );
        },
        // ... (otros controladores)
    ],
];