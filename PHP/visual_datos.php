<?php
session_start();
require_once '../classes/User.php';

if (!isset($_SESSION['correo'])) {
    header("Location: ../login.html");
    exit();
}

$correo = $_SESSION['correo'];
$user = new User();
$userData = $user->getUserData($correo);

if (!$userData) {
    echo "Error: No se pudieron recuperar los datos del usuario.";
    exit();
}

$nombre = $userData['nombre'];
$apellidos = $userData['apellidos'];
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