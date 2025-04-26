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
    <title>Café Aroma - Mi Cuenta</title>
    <link rel="stylesheet" href="../CSS/dashboard.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <h1>Ethos<span>Coffe</span></h1>
            </div>
            <ul class="main-menu">
                <li><a href="../index.php">Inicio</a></li>
                <li><a href="productos.php">Menú</a></li>
                <li><a href="../Servicios.html">Sobre Nosotros</a></li>
            </ul>
            <div class="user-actions">
                <div class="icon">👤</div>
                <div class="icon">❤️</div>
                <div class="icon">🛒</div>
            </div>
        </nav>
    </header>
    
    <section class="user-data-section">
        <div class="container">
            <div class="section-title">
                <h2>Mi Cuenta</h2>
            </div>
            <p class="welcome-message">¡Bienvenido, <?php echo htmlspecialchars($nombre); ?>!</p>
            
            <table class="user-data-table">
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
            
            <div class="action-buttons">
                <a href="cerrar_sesion.php" class="btn secondary-btn">Cerrar sesión</a>
                <a href="../index.php" class="btn primary-btn">Página Principal</a>
            </div>
        </div>
    </section>
    
    <footer>
        <div class="footer-content">
            <div class="copyright">
                © 2025 Café Aroma. Todos los derechos reservados.
            </div>
            <div class="footer-links">
                <a href="#">Privacidad</a>
                <a href="#">Términos</a>
                <a href="#">Ayuda</a>
                <a href="#">Contacto</a>
            </div>
        </div>
    </footer>
</body>
</html>