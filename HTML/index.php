<?php
// public/index.php (nuevo)

// Configurar el entorno
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cargar el autoloader
require_once __DIR__ . '/../PHP/autoload.php';

// Iniciar sesión
session_start();

// Cargar y ejecutar el router
require_once __DIR__ . '/../src/MVC/Routes/Router.php';
require_once __DIR__ . '/../src/MVC/Routes/routes.php';