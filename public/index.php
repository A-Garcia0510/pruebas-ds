<?php
// public/index.php

// Definir la constante del directorio raíz
define('ROOT_DIR', dirname(__DIR__));

// Cargar el autoloader
require_once ROOT_DIR . '/vendor/autoload.php';

// Cargar la configuración
$config = require_once ROOT_DIR . '/app/config/config.php';

// Iniciar la sesión
session_start();

// Crear instancia de la aplicación
$app = new \App\Core\App($config);

// Definir rutas (esto se moverá a un archivo de rutas más adelante)
// Por ahora, solo configuramos una ruta de ejemplo y la página 404

// Ejemplo de ruta a página de inicio (se implementará en la siguiente fase)
$app->router->get('/', [\App\Controllers\PageController::class, 'home']);

// Establecer manejador de página no encontrada
$app->router->setNotFoundHandler(function($request, $response) {
    $response->setStatusCode(404);
    return require_once ROOT_DIR . '/app/views/errors/404.php';
});

// Ejecutar la aplicación
$app->run();