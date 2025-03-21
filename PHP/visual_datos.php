<?php
session_start();
include('../conexion/conexion.inc');

if (!isset($_SESSION['correo'])) {
    header("Location: ../login.html");
    exit();
}

$correo = $_SESSION['correo'];

$stmt = $conn->prepare("SELECT nombre, apellidos FROM Usuario WHERE correo = ?");
if (!$stmt) {
    die("Error en la preparación de la consulta: " . $conn->error);
}

$stmt->bind_param("s", $correo);
$stmt->execute();
$stmt->bind_result($nombre, $apellidos); 
$stmt->fetch();

$stmt->close();
$conn->close();
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
