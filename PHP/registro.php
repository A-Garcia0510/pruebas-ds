<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'autoload.php';

use App\Auth\AuthFactory;
use App\Auth\Models\User;
use App\Core\Database\DatabaseConfiguration;
use App\Core\Database\MySQLDatabase;

// Cargar configuración
$config = require_once '../src/Config/Config.php';
$dbConfig = new DatabaseConfiguration(
    $config['database']['host'],
    $config['database']['username'],
    $config['database']['password'],
    $config['database']['database']
);

try {
    // Crear conexión a la base de datos
    $database = new MySQLDatabase($dbConfig);
    
    // Crear el repositorio de usuarios
    $userRepository = AuthFactory::createUserRepository($database);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Obtener datos del formulario
        $nombre = $_POST['nombre'] ?? '';
        $apellidos = $_POST['apellidos'] ?? '';
        $email = $_POST['correo'] ?? '';
        $password = $_POST['contra'] ?? '';
        
        // Validar que todos los campos están completos
        if (empty($nombre) || empty($apellidos) || empty($email) || empty($password)) {
            echo "<script>
                    alert('Todos los campos son obligatorios.'); 
                    window.location.href='../registro.html';
                  </script>";
            exit();
        }
        
        // Verificar si el email ya existe
        if ($userRepository->emailExists($email)) {
            echo "<script>
                    alert('El correo electrónico ya está registrado.'); 
                    window.location.href='../registro.html';
                  </script>";
            exit();
        }
        
        // Crear nuevo usuario
        $user = new User(null, $nombre, $apellidos, $email, $password);
        
        // Guardar usuario
        if ($userRepository->save($user)) {
            // Crear autenticador y autenticar al usuario
            $authenticator = AuthFactory::createAuthenticator($database);
            $authenticator->authenticate($email, $password);
            
            header("Location:../PHP/visual_datos.php");
            exit();
        } else {
            echo "<script>
                    alert('Error al crear el usuario. Por favor, inténtalo de nuevo.'); 
                    window.location.href='../registro.html';
                  </script>";
        }
    }
} catch (Exception $e) {
    echo "<script>
            alert('Error en el sistema: " . $e->getMessage() . "'); 
            window.location.href='../registro.html';
          </script>";
}