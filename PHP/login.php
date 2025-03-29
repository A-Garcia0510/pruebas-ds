<?php 
// PHP/login.php
require_once 'autoload.php'; // Asegúrate de tener un autoloader configurado

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
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['correo'];
        $password = $_POST['contra'];
        
        if ($authenticator->authenticate($email, $password)) {
            header("Location:../PHP/visual_datos.php");
            exit();
        } else {
            echo "<script>
                    alert('Datos incorrectos. Por favor, inténtalo de nuevo.'); 
                    window.location.href='../login.html';
                  </script>";
        }
    }
} catch (Exception $e) {
    echo "<script>
            alert('Error en el sistema: " . $e->getMessage() . "'); 
            window.location.href='../login.html';
          </script>";
}