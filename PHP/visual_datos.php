<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'autoload.php';

use App\Auth\AuthFactory;
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
    
    // Crear el autenticador
    $authenticator = AuthFactory::createAuthenticator($database);
    
    // Verificar si el usuario está autenticado
    if (!$authenticator->isAuthenticated()) {
        header("Location: ../login.html");
        exit();
    }
    
    // Obtener email del usuario actual
    $correo = $authenticator->getCurrentUserEmail();
    
    // Obtener los datos del usuario
    $userRepository = AuthFactory::createUserRepository($database);
    $user = $userRepository->findByEmail($correo);
    
    if (!$user) {
        echo "Error: No se pudieron recuperar los datos del usuario.";
        exit();
    }
    
    $nombre = $user->getNombre();
    $apellidos = $user->getApellidos();
    
} catch (Exception $e) {
    echo "Error en el sistema: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datos del Usuario</title>
    <link rel="stylesheet" href="../CSS/datos.css">
</head>
<body>
    <header>
        <h1>Datos del Usuario</h1>
    </header>
    
    <div class="container">
        <p class="welcome">Bienvenido, <?php echo htmlspecialchars($nombre); ?>!</p> 
        
        <table>
            <thead>
                <tr>
                    <th>Datos</th>
                    <th>Información</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Nombre:</td>
                    <td><?php echo htmlspecialchars($nombre); ?></td>
                </tr>
                <tr>
                    <td>Apellidos:</td>
                    <td><?php echo htmlspecialchars($apellidos); ?></td> 
                </tr>
                <tr>
                    <td>Correo:</td>
                    <td><?php echo htmlspecialchars($correo); ?></td>
                </tr>
            </tbody>
        </table>
        <p class="logout"><a href="cerrar_sesion.php" class="button">Cerrar sesión</a></p>
        <p class="logout"><a href="../index.php" class="button">Pagina Principal</a></p>
        
    </div>
    
    <footer>
        <p>&copy; 2024 Casino Express. Todos los derechos reservados.</p>
    </footer>
</body>
</html>