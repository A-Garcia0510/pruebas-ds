<?php
/**
 * Archivo de depuración para diagnosticar problemas con el enrutamiento
 */

// Mostrar todos los errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Información de depuración</h1>";

echo "<h2>SERVER Variables</h2>";
echo "<pre>";
print_r($_SERVER);
echo "</pre>";

echo "<h2>Ruta detectada</h2>";
$path = $_SERVER['REQUEST_URI'] ?? '/';
$scriptName = $_SERVER['SCRIPT_NAME'];
$scriptDir = dirname($scriptName);

echo "REQUEST_URI: " . $path . "<br>";
echo "SCRIPT_NAME: " . $scriptName . "<br>";
echo "Script Directory: " . $scriptDir . "<br>";

// Si no estamos en el directorio raíz y el path comienza con ese directorio
if ($scriptDir !== '/' && $scriptDir !== '\\' && strpos($path, $scriptDir) === 0) {
    $path = substr($path, strlen($scriptDir));
}

// Eliminar parámetros de consulta si existen
$position = strpos($path, '?');
if ($position !== false) {
    $path = substr($path, 0, $position);
}

// Asegurarse de que el path comience con /
if (empty($path) || $path[0] !== '/') {
    $path = '/' . $path;
}

echo "Ruta procesada: " . $path . "<br>";

echo "<h2>Directorios en el proyecto</h2>";
$rootDir = dirname(__DIR__);
echo "Directorio raíz: " . $rootDir . "<br>";

// Verificar la existencia de directorios clave
$directories = [
    'app' => $rootDir . '/app',
    'controllers' => $rootDir . '/app/controllers',
    'views' => $rootDir . '/app/views',
    'vendor' => $rootDir . '/vendor',
];

foreach ($directories as $name => $dir) {
    echo "$name: " . (is_dir($dir) ? "✅ Existe" : "❌ No existe") . " ($dir)<br>";
}

// Verificar archivos esenciales
echo "<h2>Archivos esenciales</h2>";
$files = [
    'autoload.php' => $rootDir . '/vendor/autoload.php',
    'config.php' => $rootDir . '/app/config/config.php',
    'App.php' => $rootDir . '/app/core/App.php',
    'Router.php' => $rootDir . '/app/core/Router.php',
    'Request.php' => $rootDir . '/app/core/Request.php',
    'Response.php' => $rootDir . '/app/core/Response.php',
    'layout_main.php' => $rootDir . '/app/views/layouts/main.php',
    'error_404.php' => $rootDir . '/app/views/errors/404.php',
];

foreach ($files as $name => $file) {
    echo "$name: " . (file_exists($file) ? "✅ Existe" : "❌ No existe") . " ($file)<br>";
}

// Verificar autoloading
echo "<h2>Prueba de autoloading</h2>";
if (file_exists($rootDir . '/vendor/autoload.php')) {
    try {
        require_once $rootDir . '/vendor/autoload.php';
        echo "✅ Autoloader cargado correctamente<br>";
        
        // Intentar usar algunas clases
        if (class_exists('\App\Core\App')) {
            echo "✅ Clase App encontrada<br>";
        } else {
            echo "❌ Clase App no encontrada<br>";
        }
        
        if (class_exists('\App\Core\Router')) {
            echo "✅ Clase Router encontrada<br>";
        } else {
            echo "❌ Clase Router no encontrada<br>";
        }
        
        if (class_exists('\App\Controllers\PageController')) {
            echo "✅ Clase PageController encontrada<br>";
        } else {
            echo "❌ Clase PageController no encontrada<br>";
        }
    } catch (Exception $e) {
        echo "❌ Error al cargar el autoloader: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Archivo autoload.php no encontrado<br>";
}