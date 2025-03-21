<?php 
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('../conexion/conexion.inc');

$email = $_POST['correo'];
$password = $_POST['contra'];

// Consulta para verificar el usuario
$sql = "SELECT * FROM Usuario WHERE correo = ?";
$stmt =$conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    if ($password === $user['contraseña']) { 
        $_SESSION['correo'] = $email;
        $_SESSION['mensaje'] = "Inicio de sesión exitoso.";
        header("Location:../PHP/visual_datos.php");
        exit();
    } else {
        echo "<script>
                alert('Datos incorrectos. Por favor, inténtalo de nuevo.'); 
                window.location.href='../login.html';
                </script>";
    }
} else {
    echo "<script>
            alert('Datos incorrectos. Por favor, inténtalo de nuevo.'); 
            window.location.href='../login.html';
            </script>";
}

$stmt->close();
$conn->close();
?>