<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('../conexion/conexion.inc');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger datos del formulario y sanitizar
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $correo = trim($_POST['correo']);
    $contra = $_POST['contra'];

    if (strlen($contra) < 8) {
        echo "<script>alert('La contraseña debe tener al menos 8 caracteres.'); window.history.back();</script>";
        exit();
    }

    // Verificar si el correo ya está registrado
    $stmt =$conn->prepare("SELECT * FROM Usuario WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "<script>alert('El correo ya está registrado.');</script>";
    } else {
        $stmt->close();

        $stmt =$conn->prepare("INSERT INTO Usuario (nombre, apellidos, correo, contraseña) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $apellidos, $correo, $contra);

        if ($stmt->execute()) {
            echo "<script>alert('Registro exitoso. Puedes iniciar sesión ahora.');</script>";

            header("Location: ../login.html");
            exit();
        } else {
            echo "<script>alert('Error al registrar: " . $stmt->error . "');</script>";
        }
    }

    // Cerrar la consulta
    $stmt->close();
}

// Cerrar conexión
$conn->close();
?>
