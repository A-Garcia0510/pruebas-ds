<?php
// PHP/cerrar_sesion.php
require_once 'autoload.php';

use App\Auth\AuthFactory;
use App\Core\Database\DatabaseConfiguration;
use App\Core\Database\MySQLDatabase;

// Cargar configuración
$config = require_once '../src/Config/config.php';
$dbConfig = new DatabaseConfiguration(
    $config['database']['host'],
    $config['database']['username'],
    $config['database']['password'],
    $config['database']['database']
);

// Crear conexión a la base de datos
try {
    $database = new MySQLDatabase($dbConfig);
    
    // Crear el autenticador
    $authenticator = AuthFactory::createAuthenticator($database);
    
    // Cerrar sesión
    $authenticator->logout();
    
    echo "<script>
              alert('Sesion Cerrada.');
              window.location.href='../login.html';
          </script>";
    exit();
} catch (Exception $e) {
    echo "<script>
            alert('Error al cerrar sesión: " . $e->getMessage() . "'); 
            window.location.href='../login.html';
          </script>";
}