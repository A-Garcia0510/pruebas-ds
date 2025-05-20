<?php
/**
 * Archivo de depuración para diagnosticar problemas con el enrutamiento
 */

// Iniciar sesión al principio del archivo
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mostrar todos los errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Función para limpiar el log
function clearLog() {
    file_put_contents(__DIR__ . '/cart_debug.log', '');
}

// Función para escribir en el log
function writeLog($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    file_put_contents(__DIR__ . '/cart_debug.log', $logMessage, FILE_APPEND);
}

// Si se solicita limpiar el log
if (isset($_GET['clear'])) {
    clearLog();
    echo "Log limpiado";
    exit;
}

echo "<h1>Información de depuración</h1>";

// Mostrar información de la sesión
echo "<h2>Información de la Sesión</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Mostrar contenido del log
echo "<h2>Log del Carrito</h2>";
if (file_exists(__DIR__ . '/cart_debug.log')) {
    echo "<pre>";
    echo htmlspecialchars(file_get_contents(__DIR__ . '/cart_debug.log'));
    echo "</pre>";
} else {
    echo "No hay log disponible";
}

// Botón para limpiar el log
echo "<br><a href='?clear=1' style='padding: 10px; background: #f44336; color: white; text-decoration: none; border-radius: 5px;'>Limpiar Log</a>";

// Mostrar información del servidor
echo "<h2>SERVER Variables</h2>";
echo "<pre>";
print_r($_SERVER);
echo "</pre>";

// Mostrar información de la ruta
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

// Verificar la existencia de directorios clave
echo "<h2>Directorios en el proyecto</h2>";
$rootDir = dirname(__DIR__);
echo "Directorio raíz: " . $rootDir . "<br>";

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